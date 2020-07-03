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
        
        $authorID = is_array($item) ? $item['InsertUserID'] : $item;

        $eventArgs = [
            'ParentID' => $id,
            'ParentType' => $type,
            'ParentUserID' => $authorID,
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
                        'ParentID' =>    $id,
                        'ParentType' => $type,
                        'ParentAuthorID' => $authorID,
                        'InsertUserID' => $userID,
                        'DateInserted' => $now
                    ]
                );

            $eventArgs['Exists'] = true;
        }

        // Update the parent item score
        $totalScore = $this->setUserScore($type, $id, $userID, $score);
        $eventArgs['TotalScore'] = $totalScore;

        // Give the user points commesurate with reaction activity
        UserModel::givePoints($authorID, $points, 'Reaction');
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

        if ($type === 'discussion') {
            $row = $container
                ->get(DiscussionModel::class)
                ->getID($id, DATASET_TYPE_ARRAY);

            if ($row) {
                $row['DisplayBest'] = true;
                $row['Url'] = discussionUrl($row);
                $category = CategoryModel::categories($row['CategoryID']);
                $row['PermissionCategoryID'] = $category['PermissionCategoryID'] ?? -1;
            }
        } elseif ($type === 'comment') {
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
        } elseif ($type === 'activity') {
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
            $row = array_pop($this->eventManager->fire('yaga_getReactionItem', $type, $id)) ?? [];
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
     * @return int Total score if request was successful, false if not.
     */
    private function setUserScore($type, $id, $userID, $score) {
        if ($type === 'discussion') {
            return (new DiscussionModel())->setUserScore($id, $userID, $score);
        } elseif ($type === 'comment') {
            return (new CommentModel())->setUserScore($id, $userID, $score);
        } elseif ($type === 'activity') {
            return 0;
        }
        return array_pop($this->eventManager->fire('yaga_setUserScore', $type, $id, $userID, $score)) ?? 0;
    }

}
