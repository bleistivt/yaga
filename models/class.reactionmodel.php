<?php if (!defined('APPLICATION')) exit();

use Garden\EventManager;
use Vanilla\Formatting\DateTimeFormatter;

/* Copyright 2013 Zachary Doll */

/**
 * Reactions are the actions a user takes against another user's content
 *
 * Events: AfterReactionSave
 *
 * @package Yaga
 * @since 1.0
 */

class ReactionModel extends Gdn_Model {

    public const TYPE_DISCUSSION = 'discussion';
    public const TYPE_COMMENT = 'comment';
    public const TYPE_ACTIVITY = 'activity';

    public const ITEMS_PROFILE_REACTION = 'received';
    public const ITEMS_PROFILE_BEST = 'best';
    public const ITEMS_BEST_REACTION = 'action';
    public const ITEMS_BEST_ALL = 'bestof';
    public const ITEMS_BEST_RECENT = 'recent';

    /**
     * Used to cache the reactions
     * @var array
     */
    private $_reactions = [];

    /** @var ActionModel */
    private $actionModel;

    /** @var UserModel */
    private $userModel;

    /** @var EventManager */
    private $eventManager;

    /**
     * Defines the related database table name.
     */
    public function __construct(ActionModel $actionModel, UserModel $userModel, EventManager $eventManager) {
        parent::__construct('YagaReaction');
        $this->PrimaryKey = 'ReactionID';

        $this->actionModel = $actionModel;
        $this->userModel = $userModel;
        $this->eventManager = $eventManager;
    }

    /**
     * Returns all available actions along with the current count specified by
     * the $id and $type of content.
     *
     * @param int $id
     * @param string $type
     * @return Gdn_DataSet
     */
    public function getList($id, $type) {
        $px = $this->Database->DatabasePrefix;
        $reactionTable = $this->Name;
        $actionTable = $this->actionModel->Name;

        // try getting the record count from the cache
        if (array_key_exists($type.$id, $this->_reactions)) {
            $reactions = $this->_reactions[$type.$id];
            $actions = $this->actionModel->get();
            // add the count
            foreach ($actions as &$action) {
                $action->Count = 0;
                foreach ($reactions as $reaction) {
                    if ($reaction->ActionID == $action->ActionID) {
                        $action->Count++;
                    }
                }
            }
            return $actions;
        }

        $sql = "select a.*, "
           ."(select count(r.ReactionID) "
           ."from {$px}{$reactionTable} as r "
           ."where r.ParentID = :ParentID and r.ParentType = :ParentType "
           ."and r.ActionID = a.ActionID) as Count "
           ."from {$px}{$actionTable} AS a "
           ."order by a.Sort";

        return $this->Database->query($sql, [':ParentID' => $id, ':ParentType' => $type])->result();
    }

    /**
     * Returns the reaction records associated with the specified user content.
     *
     * @param int $id
     * @param string $type is the kind of ID. Valid: comment, discussion, activity
     * @return mixed DataSet if it exists, null otherwise
     */
    public function getRecord($id, $type) {
        // try getting the record from the cache
        if (array_key_exists($type.$id, $this->_reactions)) {
            return $this->_reactions[$type.$id];
        }

        $result = $this->SQL
            ->select('a.*, r.InsertUserID as UserID, r.DateInserted')
            ->from($this->actionModel->Name.' a')
            ->join($this->Name.' r', 'a.ActionID = r.ActionID')
            ->where('r.ParentID', $id)
            ->where('r.ParentType', $type)
            ->orderBy('r.DateInserted')
            ->get()
            ->result();

        $this->_reactions[$type.$id] = $result;
        return $result;
    }

    /**
     * Return the count of reactions received by a user
     *
     * @param int $userID
     * @param int $actionID
     * @return Gdn_DataSet
     */
    public function getUserCount($userID, $actionID) {
        return $this->SQL
            ->select('ReactionID', 'count', 'RowCount')
            ->from($this->Name)
            ->where(['ActionID' => $actionID, 'ParentAuthorID' => $userID])
            ->get()
            ->firstRow()
            ->RowCount;
    }

    /**
     * Sets a users reaction against another user's content. A user can only react
     * in one way to each unique piece of content. This function makes sure to
     * enforce this rule
     *
     * Events: AfterReactionSave
     *
     * @param int $id
     * @param string $type activity, comment, discussion
     * @param array|int $item The item to react to or just the InsertUserID
     * @param int $userID
     * @param int $actionID
     * @return Gdn_DataSet
     */
    public function set($id, $type, $item, $userID, $actionID) {
        // clear the cache
        unset($this->_reactions[$type.$id]);

        if (!is_array($item)) {
            $item = ['InsertUserID' => $item, 'DisplayBest' => false];
        }

        $eventArgs = [
            'ParentID' => $id,
            'ParentType' => $type,
            'ParentUserID' => $item['InsertUserID'],
            'InsertUserID' => $userID,
            'ActionID' => $actionID
        ];

        $newAction = $this->actionModel->getID($actionID);
        $points = $score = $newAction->AwardValue;

        $currentReaction = $this->getWhere([
            'ParentID' => $id,
            'ParentType' => $type,
            'InsertUserID' => $userID
        ])->firstRow();
        $eventArgs['CurrentReaction'] = $currentReaction;

        $this->fireEvent('BeforeReactionSave', $eventArgs);
        $now = DateTimeFormatter::timeStampToDateTime(time());

        if ($currentReaction) {
            $oldAction = $this->actionModel->getID($currentReaction->ActionID);

            if ($actionID == $currentReaction->ActionID) {
                // remove the record
                $reaction = $this->SQL->delete(
                    $this->Name,
                    [
                        'ParentID' => $id,
                        'ParentType' => $type,
                        'InsertUserID' => $userID,
                        'ActionID' => $actionID
                    ]
                );
                $eventArgs['Exists'] = false;
                $score = 0;
                $points = -1 * $oldAction->AwardValue;

            } else {
                // update the record
                $reaction = $this->SQL
                    ->update($this->Name)
                    ->set('ActionID', $actionID)
                    ->set('DateInserted', $now)
                    ->where('ParentID', $id)
                    ->where('ParentType', $type)
                    ->where('InsertUserID', $userID)
                    ->put();

                $eventArgs['Exists'] = true;
                $points = -1 * ($oldAction->AwardValue - $points);
            }
        } else {
            // insert a record
            $reaction = $this->SQL
                ->insert(
                    $this->Name,
                    [
                        'ActionID' => $actionID,
                        'ParentID' => $id,
                        'ParentType' => $type,
                        'ParentAuthorID' => $item['InsertUserID'],
                        'InsertUserID' => $userID,
                        'DateInserted' => $now
                    ]
                );

            $eventArgs['Exists'] = true;
        }

        // Update the parent item score
        $item['Score'] = $this->setUserScore($type, $id, $userID, $score, $points);
        $eventArgs['TotalScore'] = $item['Score'];

        // Set the "latest" flag.
        $this->setLatestItem($type, $id, $item);

        // Give the user points commesurate with reaction activity
        UserModel::givePoints($item['InsertUserID'], $points, 'Reaction');
        $eventArgs['Points'] = $points;

        $this->fireEvent('AfterReactionSave', $eventArgs);

        return $reaction;
    }

    /**
     * Fills the memory cache with the specified reaction records
     *
     * @since 1.1
     * @param string $type
     * @param array $ids
     */
    public function prefetch($type, $ids) {
        if (!is_array($ids)) {
            $ids = (array)$ids;
        }

        if (!empty($ids)) {
            $result = $this->SQL
                ->select('a.*, r.InsertUserID as UserID, r.DateInserted, r.ParentID')
                ->from($this->actionModel->Name.' a')
                ->join($this->Name.' r', 'a.ActionID = r.ActionID')
                ->whereIn('r.ParentID', $ids)
                ->where('r.ParentType', $type)
                ->orderBy('r.DateInserted')
                ->get()
                ->result();

            foreach ($ids as $id) {
                $this->_reactions[$type.$id] = [];
            }

            $userIDs = [];
            // fill the cache
            foreach ($result as $reaction) {
                $userIDs[] = $reaction->UserID;
                $this->_reactions[$type.$reaction->ParentID][] = $reaction;
            }

            // Prime the user cache
            $this->userModel->getIDs($userIDs);
        }
    }

    /**
     * This function fetches items that users can react to.
     *
     * Events: yaga_getReactionItem
     *
     * @param string $type The type of the item
     * @param int $id The items ID
     * @return array
     */
    public function getReactionItem($type, $id) {
        $container = Gdn::getContainer();
        $row = [];

        if ($type === self::TYPE_DISCUSSION) {
            $row = $container
                ->get(DiscussionModel::class)
                ->getID($id, DATASET_TYPE_ARRAY);

            if ($row) {
                $row['DisplayBest'] = true;
                $row['Url'] = discussionUrl($row);
                $category = CategoryModel::categories($row['CategoryID']);
                $row['PermissionCategoryID'] = $category['PermissionCategoryID'] ?? -1;
            }
        } elseif ($type === self::TYPE_COMMENT) {
            $row = $container
                ->get(CommentModel::class)
                ->getID($id, DATASET_TYPE_ARRAY);

            if ($row) {
                $row['DisplayBest'] = true;
                $row['Url'] = url("/discussion/comment/{$id}#Comment_{$id}", true);

                $discussion = $container
                    ->get(DiscussionModel::class)
                    ->getID($row['DiscussionID'], DATASET_TYPE_ARRAY);

                $row['Name'] = $discussion['Name'] ?? '';
                $category = CategoryModel::categories($discussion['CategoryID']);
                $row['PermissionCategoryID'] = $category['PermissionCategoryID'] ?? -1;
            }
        } elseif ($type === self::TYPE_ACTIVITY) {
            $row = $container
                ->get(ActivityModel::class)
                ->getID($id, DATASET_TYPE_ARRAY);

            if ($row) {
                $row['InsertUserID'] = $row['RegardingUserID'];
                $row['DisplayBest'] = false;
            }
        } else {
            /**
             * In order to extend reactions, if the $type can be handled, a plugin should handle
             * the "yaga_getReactionItem" event by returning an array structured as follows:
             *
             * int InsertUserID: The ID of the user who should receive reactions and points for this item
             * bool DisplayBest: If set to true, this item will be shown on "best" and "reactions" pages.
             *
             * If "DisplayBest" is set to true, these additional fields are required:
             *
             * string Url: The URL to this item
             * string Name: The title of this item
             * datetime DateInserted: The date of this item
             * string Body: The content of this item
             * string Format: The formatter to use for the "Body" field
             * int PermissionCategoryID: The permission category ID of this item (view permissions required)
             * float Score: The current score of this item (see "yaga_setUserScore" event)
             */
            $row = $this->eventManager->fire('yaga_getReactionItem', $type, $id)[0] ?? [];
        }

        return $row;
    }

    /**
     * This updates the items score.
     *
     * Events: yaga_setUserScore
     *
     * @param string $type The type of the item
     * @param int $id The items ID
     * @param int $userID The user that is scoring the item
     * @param int $score What they give it
     * @param int $change The increment/decrement represented by this score change
     * @return int Total score if request was successful, false if not.
     */
    private function setUserScore($type, $id, $userID, $score, $change) {
        if ($type === self::TYPE_DISCUSSION) {
            return (new DiscussionModel())->setUserScore($id, $userID, $score);
        } elseif ($type === self::TYPE_COMMENT) {
            return (new CommentModel())->setUserScore($id, $userID, $score);
        } elseif ($type === self::TYPE_ACTIVITY) {
            return 0;
        }
        return $this->eventManager->fire('yaga_setUserScore', $type, $id, $userID, $score, $change)[0] ?? 0;
    }

    /**
     * Atomically set the "latest" flag on a reaction record group.
     *
     * @param string $type The type of the item
     * @param int $id The items ID
     * @param array $item The item
     * @return array The modified rows
     */
    private function setLatestItem($type, $id, $item = []) {
        $table = $this->Database->DatabasePrefix.$this->Name;

        $null = [
            'Latest' => 0,
            'ParentPermissionCategoryID' => null,
            'ParentDateInserted' => null,
            'ParentScore' => null
        ];

        $insert = ['Latest' => 1];
        if (!empty($item) && $item['DisplayBest']) {
            $insert['ParentPermissionCategoryID'] = $item['PermissionCategoryID'];
            $insert['ParentDateInserted'] = $item['DateInserted'];
            $insert['ParentScore'] = $item['Score'];
        }

        $this->Database->beginTransaction();

        // Selecting for update locks the corresponding rows for reads on InnoDB.
        $sql = "select ReactionID, ActionID from {$table} "
           ."where ParentID = :ParentID and ParentType = :ParentType "
           ."order by DateInserted desc for update";

        $result = $this->Database
            ->query($sql, [':ParentID' => $id, ':ParentType' => $type])
            ->resultArray();

        $latest = [];
        $actionIDs = [];
        foreach ($result as $reaction) {
            if (!in_array($reaction['ActionID'], $actionIDs)) {
                $latest[] = $reaction['ReactionID'];
                $actionIDs[] = $reaction['ActionID'];
            }
        }

        if (!empty($latest)) {
            // 1 = latest reaction of an action, 2 = latest reaction overall
            $this->SQL->put($this->Name, $null, ['ParentID' => $id, 'ParentType' => $type]);
            $this->SQL->put($this->Name, $insert, ['ReactionID' => $latest]);
            $this->SQL->put($this->Name, ['Latest' => 2], ['ReactionID' => $latest[0]]);
        }

        $this->Database->commitTransaction();

        return $result;
    }

    /**
     * Used by the DBA controller to update denormalized reaction data via dba/counts
     *
     * @param string $column
     * @param int $userID
     * @return boolean
     * @throws Gdn_UserException
     */
    public function counts($column, $from = false, $to = false) {
        if ($column !== 'Latest') {
            return;
        }

        $chunk = DBAModel::$ChunkSize;
        list($min, $max) = (new DBAModel())->primaryKeyRange($this->Name);
        if (!$from) {
            $from = $min;
            $to = $min + $chunk - 1;
        }
        $from = (int)$from;
        $to = (int)$to;

        $items = $this->SQL
            ->select('ParentID, ParentType')
            ->from($this->Name)
            ->where('ReactionID >=', $from)
            ->where('ReactionID <=', $to)
            ->groupBy('ParentID, ParentType')
            ->get()
            ->resultArray();

        foreach($items as $item) {
            $this->setLatestItem(
                $item['ParentType'],
                $item['ParentID'],
                $this->getReactionItem($item['ParentType'], $item['ParentID']);
            );
        }

        return [
            'Complete' => $to >= $max,
            'Percent' => min(round($to * 100 / $max), 100).'%',
            'Args' => [
                'from' => $to + 1,
                'to' => $to + $chunk
            ]
        ];
    }

}
