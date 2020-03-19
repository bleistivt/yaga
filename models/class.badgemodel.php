<?php if (!defined('APPLICATION')) exit();

/* Copyright 2013 Zachary Doll */

/**
 * Describes badges and the associated rule criteria
 *
 * Events:
 *
 * @package Yaga
 * @since 1.0
 */

class BadgeModel extends Gdn_Model {

    /**
     * Used as a cache
     * @var DataSet
     */
    private $_badges = null;

    /**
     * Defines the related database table name.
     */
    public function __construct() {
        parent::__construct('YagaBadge');
        $this->PrimaryKey = 'BadgeID';
    }

    /**
     * Returns a list of all badges
     *
     * @return Gdn_DataSet
     */
    public function get($orderFields = '', $orderDirection = 'asc', $limit = false, $pageNumber = false) {
        if ($orderFields !== '' || $orderDirection !== 'asc' || $limit !== false || $pageNumber !== false) {
            return parent::get($orderFields, $orderDirection, $limit, $pageNumber);
        }

        // Cache any get() call with default arguments.
        if (empty($this->_badges)) {
            $this->_badges = $this->SQL
                ->select()
                ->from($this->Name)
                ->orderBy('Sort')
                ->get()
                ->result();
        }

        return $this->_badges;
    }

    /**
     * Enable or disable a badge
     *
     * @param int $badgeID
     * @param bool $enable
     */
    public function enable($badgeID, $enable) {
        $enable = (!$enable) ? 0 : 1;
        $this->update(
            ['Enabled' => $enable],
            ['BadgeID' => $badgeID]
        );

        $this->EventArguments['BadgeID'] = $badgeID;
        $this->EventArguments['Enable'] = (bool)$enable;
        $this->fireEvent('BadgeEnable');
    }

    /**
     * Remove a badge and associated awards
     *
     * @param int $badgeID
     * @throws Exception
     * @return boolean
     */
    public function deleteID($badgeID, $options = []) {
        $badge = $this->getID($badgeID);
        if (empty($badge)) {
            return false;
        }

        try {
            $this->Database->beginTransaction();
            // Delete the badge
            parent::deleteID($badgeID);

            $badgeAwardModel = Gdn::getContainer()->get(BadgeAwardModel::class);

            // Find the affected users
            $userIDSet = $badgeAwardModel
                ->getWhere(['BadgeID' => $badgeID])
                ->resultArray();

            $userIDs = array_column($userIDSet, 'UserID');

            // Decrement their badge count
            $this->SQL
                ->update('User')
                ->set('CountBadges', 'CountBadges - 1', false)
                ->where('UserID', $userIDs)
                ->put();

            // Remove their points
            foreach ($userIDs as $userID) {
                UserModel::givePoints($userID, -1 * $badge->AwardValue, 'Badge');
            }
            // Remove the award rows
            $badgeAwardModel->delete(['BadgeID' => $badgeID]);

            $this->Database->commitTransaction();
        } catch(Exception $ex) {
            $this->Database->rollbackTransaction();
            throw $ex;
        }

        return true;
    }

    /**
     * Updates the sort field for each badge in the sort array
     *
     * @since 1.1
     * @param array $sortArray
     * @return boolean
     */
    public function saveSort($sortArray) {
        foreach ($sortArray as $index => $badge) {
            // remove the 'BadgeID_' prefix
            $badgeID = substr($badge, 8);
            $this->setField($badgeID, 'Sort', $index);
        }
        return true;
    }

}
