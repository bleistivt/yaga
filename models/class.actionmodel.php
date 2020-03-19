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
    private $_actions = null;

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
     * @return Gdn_DataSet
     */
    public function get($orderFields = '', $orderDirection = 'asc', $limit = false, $pageNumber = false) {
        if ($orderFields !== '' || $orderDirection !== 'asc' || $limit !== false || $pageNumber !== false) {
            return parent::get($orderFields, $orderDirection, $limit, $pageNumber);
        }

        // Cache any get() call with default arguments.
        if (empty($this->_actions)) {
            $this->_actions = $this->SQL
                ->select()
                ->from($this->Name)
                ->orderBy('Sort')
                ->get()
                ->result();
        }

        return $this->_actions;
    }

    /**
     * Determine if a specified action exists
     *
     * @param int $actionID
     * @return bool
     */
    public function exists($actionID) {
        return !empty($this->getID($actionID));
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
        if (!$this->exists($actionID)) {
            return false;
        }

        $this->deleteID($actionID);

        // replace the reaction table to move reactions to a new action
        $reactionModel = Gdn::getContainer()->get(ReactionModel::class);

        if ($replacementID && $this->exists($replacementID)) {
            $reactionModel->update(
                ['ActionID' => $replacementID],
                ['ActionID' => $actionID]
            );
        } else {
            $reactionModel->delete(['ActionID' => $actionID]);
        }

        return true;
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
