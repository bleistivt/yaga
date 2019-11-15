<?php if (!defined('APPLICATION')) exit();

use Yaga;

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
    private static $_reactions = [];

    /**
     * Defines the related database table name.
     */
    public function __construct() {
        parent::__construct('Reaction');
    }

    /**
     * Returns all available actions along with the current count specified by
     * the $iD and $type of content.
     *
     * @param int $iD
     * @param string $type
     * @return DataSet
     */
    public function getList($iD, $type) {
        $px = $this->Database->DatabasePrefix;

        // try getting the record count from the cache
        if (array_key_exists($type.$iD, self::$_reactions)) {
            $reactions = self::$_reactions[$type.$iD];
            $actions = Yaga::actionModel()->get();
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
           ."from {$px}Reaction as r "
           ."where r.ParentID = :ParentID and r.ParentType = :ParentType "
           ."and r.ActionID = a.ActionID) as Count "
           ."from {$px}Action AS a "
           ."order by a.Sort";

        return $this->Database->query($sql, [':ParentID' => $iD, ':ParentType' => $type])->result();
    }

    /**
     * Returns the reaction records associated with the specified user content.
     *
     * @param int $iD
     * @param string $type is the kind of ID. Valid: comment, discussion, activity
     * @return mixed DataSet if it exists, null otherwise
     */
    public function getRecord($iD, $type) {
        // try getting the record from the cache
        if (array_key_exists($type.$iD, self::$_reactions)) {
            return self::$_reactions[$type.$iD];
        }
        else {
            $result = $this->SQL
                ->select('a.*, r.InsertUserID as UserID, r.DateInserted')
                ->from('Action a')
                ->join('Reaction r', 'a.ActionID = r.ActionID')
                ->where('r.ParentID', $iD)
                ->where('r.ParentType', $type)
                ->orderBy('r.DateInserted')
                ->get()
                ->result();
            self::$_reactions[$type.$iD] = $result;
            return $result;
        }
    }

    /**
     * Return a list of reactions a user has received
     *
     * @param int $iD
     * @param string $type activity, comment, discussion
     * @param int $userID
     * @return DataSet
     */
    public function getByUser($iD, $type, $userID) {
        return $this->SQL
            ->select()
            ->from('Reaction')
            ->where('ParentID', $iD)
            ->where('ParentType', $type)
            ->where('InsertUserID', $userID)
            ->get()
            ->firstRow();
    }

    /**
     * Return the count of reactions received by a user
     *
     * @param int $userID
     * @param int $actionID
     * @return DataSet
     */
    public function getUserCount($userID, $actionID) {
        return $this->SQL
            ->select('ReactionID', 'count', 'RowCount')
            ->from('Reaction')
            ->where(['ActionID' => $actionID, 'ParentAuthorID' => $userID])
            ->get()
            ->firstRow()
            ->RowCount;
    }

    /**
     * Return the count of actions taken by a user
     *
     * @param int $userID
     * @param int $actionID
     * @return DataSet
     */
    public function getUserTakenCount($userID, $actionID) {
        return $this->SQL
            ->select('ReactionID', 'count', 'RowCount')
            ->from('Reaction')
            ->where(['ActionID' => $actionID, 'InsertUserID' => $userID])
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
     * @param int $iD
     * @param string $type activity, comment, discussion
     * @param int $authorID
     * @param int $userID
     * @param int $actionID
     * @return DataSet
     */
    public function set($iD, $type, $authorID, $userID, $actionID) {
        // clear the cache
        unset(self::$_reactions[$type.$iD]);

        $eventArgs = ['ParentID' => $iD, 'ParentType' => $type, 'ParentUserID' => $authorID, 'InsertUserID' => $userID, 'ActionID' => $actionID];
        $actionModel = Yaga::actionModel();
        $newAction = $actionModel->getByID($actionID);
        $points = $score = $newAction->AwardValue;
        $currentReaction = $this->getByUser($iD, $type, $userID);
        $eventArgs['CurrentReaction'] = $currentReaction;
        $this->fireEvent('BeforeReactionSave', $eventArgs);

        if ($currentReaction) {
            $oldAction = $actionModel->getByID($currentReaction->ActionID);

            if ($actionID == $currentReaction->ActionID) {
                // remove the record
                $reaction = $this->SQL->delete(
                    'Reaction',
                    [
                        'ParentID' => $iD,
                        'ParentType' => $type,
                        'InsertUserID' => $userID,
                        'ActionID' => $actionID
                    ]
                );
                $eventArgs['Exists'] = false;
                $score = 0;
                $points = -1 * $oldAction->AwardValue;
            }
            else {
                // update the record
                $reaction = $this->SQL
                    ->update('Reaction')
                    ->set('ActionID', $actionID)
                    ->set('DateInserted', Gdn_Format::toDateTime())
                    ->where('ParentID', $iD)
                    ->where('ParentType', $type)
                    ->where('InsertUserID', $userID)
                    ->put();
                $eventArgs['Exists'] = true;
                $points = -1 * ($oldAction->AwardValue - $points);
            }
        }
        else {
            // insert a record
            $reaction = $this->SQL
                ->insert(
                    'Reaction',
                    [
                        'ActionID' => $actionID,
                        'ParentID' =>    $iD,
                        'ParentType' => $type,
                        'ParentAuthorID' => $authorID,
                        'InsertUserID' => $userID,
                        'DateInserted' => Gdn_Format::toDateTime()
                    ]
                );
            $eventArgs['Exists'] = true;
        }

        // Update the parent item score
        $totalScore = $this->setUserScore($iD, $type, $userID, $score);
        $eventArgs['TotalScore'] = $totalScore;
        // Give the user points commesurate with reaction activity
        Yaga::givePoints($authorID, $points, 'Reaction');
        $eventArgs['Points'] = $points;
        $this->fireEvent('AfterReactionSave', $eventArgs);
        return $reaction;
    }

    /**
     * Fills the memory cache with the specified reaction records
     *
     * @since 1.1
     * @param string $type
     * @param array $iDs
     */
    public function prefetch($type, $iDs) {
        if (!is_array($iDs)) {
                $iDs = (array)$iDs;
        }

        if (!empty($iDs)) {
            $result = $this->SQL
                ->select('a.*, r.InsertUserID as UserID, r.DateInserted, r.ParentID')
                ->from('Action a')
                ->join('Reaction r', 'a.ActionID = r.ActionID')
                ->whereIn('r.ParentID', $iDs)
                ->where('r.ParentType', $type)
                ->orderBy('r.DateInserted')
                ->get()
                ->result();

            foreach ($iDs as $iD) {
                self::$_reactions[$type.$iD] = [];
            }

            $userIDs = [];
            // fill the cache
            foreach ($result as $reaction) {
                $userIDs[] = $reaction->UserID;
                self::$_reactions[$type.$reaction->ParentID][] = $reaction;
            }

            // Prime the user cache
            Gdn::userModel()->getIDs($userIDs);
        }
    }

    /**
     * This updates the items score for future use in ranking and a best of controller
     *
     * @param int $iD The items ID
     * @param string $type The type of the item (only supports 'discussion' and 'comment'
     * @param int $userID The user that is scoring the item
     * @param int $score What they give it
     * @return int Total score if request was successful, false if not.
     */
    private function setUserScore($iD, $type, $userID, $score) {
        $model = false;
        switch($type) {
            default:
                return false;
            case 'discussion':
                $model = new DiscussionModel();
                break;
            case 'comment':
                $model = new CommentModel();
                break;
        }

        if ($model) {
            return $model->setUserScore($iD, $userID, $score);
        }
        else {
            return false;
        }
    }
}
