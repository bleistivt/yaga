<?php if (!defined('APPLICATION')) exit();

/* Copyright 2013 Zachary Doll */

/**
 * Describe the available actions one can react with to other user content.
 *
 * Events:
 *
 * @package Yaga
 * @since 1.0
 */

class ActionModel extends Gdn_Model {

    /**
     * This is used as a cache.
     * @var object
     */
    private static $_actions = null;

    /**
     * Defines the related database table name.
     */
    public function __construct() {
        parent::__construct('YagaAction');
        $this->PrimaryKey = 'ActionID';
    }

    /**
     * Returns a list of all available actions
     *
     * @return dataset
     */
    public function get($orderFields = '', $orderDirection = 'asc', $limit = false, $pageNumber = false) {
        if (empty(self::$_actions)) {
            self::$_actions = $this->SQL
                ->select()
                ->from('YagaAction')
                ->orderBy('Sort')
                ->get()
                ->result();
        }
        return self::$_actions;
    }

    /**
     * Returns data for a specific action
     *
     * @param int $actionID
     * @return dataset
     */
    public function getByID($actionID) {
        $action = $this->SQL
            ->select()
            ->from('YagaAction')
            ->where('ActionID', $actionID)
            ->get()
            ->firstRow();
        return $action;
    }

    /**
     * Determine if a specified action exists
     *
     * @param int $actionID
     * @return bool
     */
    public function exists($actionID) {
        $temp = $this->getByID($actionID);
        return !empty($temp);
    }

    /**
     * Remove an action from the db
     *
     * @param int $actionID
     * @param int $replacementID what action ID existing reactions should report
     * to. null will delete the associated reactions.
     * @return boolean Whether or not the deletion was successful
     */
    public function deleteAction($actionID, $replacementID = null) {
        if ($this->exists($actionID)) {
            $this->SQL->delete('YagaAction', ['ActionID' => $actionID]);

            // replace the reaction table to move reactions to a new action
            if ($replacementID && $this->exists($replacementID)) {
                $this->SQL->update('YagaReaction')
                    ->set('ActionID', $replacementID)
                    ->where('ActionID', $actionID)
                    ->put();
            } else {
                $this->SQL->delete('YagaReaction', ['ActionID' => $actionID]);
            }
            return true;
        }
        return false;
    }

    /**
     * Updates the sort field for each action in the sort array
     *
     * @param array $sortArray
     * @return boolean
     */
    public function saveSort($sortArray) {
        foreach ($sortArray as $index => $action) {
            // remove the 'ActionID_' prefix
            $actionID = substr($action, 9);
            $this->setField($actionID, 'Sort', $index);
        }
        return true;
    }

}
