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
    private static $_badges = null;

    /**
     * Defines the related database table name.
     */
    public function __construct() {
        parent::__construct('Badge');
    }

    /**
     * Returns a list of all badges
     *
     * @return DataSet
     */
    public function get($orderFields = '', $orderDirection = 'asc', $limit = false, $pageNumber = false) {
        if (empty(self::$_badges)) {
            self::$_badges = $this->SQL
                ->select()
                ->from('Badge')
                ->orderBy('Sort')
                ->get()
                ->result();
        }
        return self::$_badges;
    }

    /**
     * Gets the badge list with an optional limit and offset
     *
     * @param int $limit
     * @param int $offset
     * @return DataSet
     */
    public function getLimit($limit = false, $offset = false) {
        return $this->SQL
            ->select()
            ->from('Badge')
            ->orderBy('Sort')
            ->limit($limit, $offset)
            ->get()
            ->result();
    }

    /**
     * Total number of badges in the system
     * @return int
     */
    public function getCount($wheres = '') {
        return count($this->get());
    }

    /**
     * Returns data for a specific badge
     *
     * @param int $badgeID
     * @return DataSet
     */
    public function getByID($badgeID) {
        $badge = $this->SQL
            ->select()
            ->from('Badge')
            ->where('BadgeID', $badgeID)
            ->get()
            ->firstRow();
        return $badge;
    }

    /**
     * Enable or disable a badge
     *
     * @param int $badgeID
     * @param bool $enable
     */
    public function enable($badgeID, $enable) {
        $enable = (!$enable) ? false : true;
        $this->SQL
            ->update('Badge')
            ->set('Enabled', $enable)
            ->where('BadgeID', $badgeID)
            ->put();
        $this->EventArguments['BadgeID'] = $badgeID;
        $this->EventArguments['Enable'] = $enable;
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
        $badge = $this->getByID($badgeID);
        if (!empty($badge)) {
            try {
                $this->Database->beginTransaction();
                // Delete the badge
                $this->SQL->delete('Badge', ['BadgeID' => $badgeID]);

                // Find the affected users
                $userIDSet = $this->SQL
                    ->select('UserID')
                    ->from('BadgeAward')
                    ->where('BadgeID', $badgeID)
                    ->get()
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
                    Yaga::givePoints($userID, -1 * $badge->AwardValue, 'Badge');
                }
                // Remove the award rows
                $this->SQL->delete('BadgeAward', ['BadgeID' => $badgeID]);

                $this->Database->commitTransaction();
            } catch(Exception $ex) {
                $this->Database->rollbackTransaction();
                throw $ex;
            }
            return true;
        }
        return false;
    }

    /**
     * Get the full list of badges joined with the award data for a specific user
     * This shouldn't really be here, but I can't think of a good place to put it
     *
     * @param int $userID
     * @return DataSet
     */
    public function getWithEarned($userID) {
        $px = $this->Database->DatabasePrefix;
        $sql = 'select b.BadgeID, b.Name, b.Description, b.Photo, b.AwardValue, '
            .'ba.UserID, ba.InsertUserID, ba.Reason, ba.DateInserted, '
            .'ui.Name AS InsertUserName '
            ."from {$px}Badge as b "
            ."left join {$px}BadgeAward as ba ON b.BadgeID = ba.BadgeID and ba.UserID = :UserID "
            ."left join {$px}User as ui on ba.InsertUserID = ui.UserID "
            .'order by b.Sort';

        return $this->Database->query($sql, [':UserID' => $userID])->result();
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
