<?php if (!defined('APPLICATION')) exit();

/* Copyright 2013 Zachary Doll */

/**
 * Handles badge awards
 *
 * Events:
 *
 * @package Yaga
 * @since 1.0
 */
class BadgeAwardModel extends Gdn_Model {

    /**
     * This is used as a cache.
     * @var array
     */
    private $_badgeAwards = [];


    /** @var BadgeModel */
    private $badgeModel;

    /**
     * Defines the related database table name.
     */
    public function __construct(?BadgeModel $badgeModel = null) {
        parent::__construct('YagaBadgeAward');
        $this->PrimaryKey = 'BadgeAwardID';

        // This is required for dba/counts which doesn't use the container (yet).
        // https://github.com/vanilla/vanilla/pull/10620
        if ($badgeModel === null) {
            $badgeModel = Gdn::getContainer()->get(BadgeModel::class);
        }

        $this->badgeModel = $badgeModel;
    }

    /**
     * Gets recently awarded badges with a specific ID
     *
     * @param int $badgeID
     * @param int $limit
     * @return Gdn_DataSet
     */
    public function getRecent($badgeID, $limit = 15) {
        return $this->SQL
            ->select('ba.UserID, ba.DateInserted, u.Name, u.Photo, u.Gender, u.Email')
            ->from($this->Name.' ba')
            ->join('User u', 'ba.UserID = u.UserID')
            ->where('BadgeID', $badgeID)
            ->orderBy('DateInserted', 'Desc')
            ->limit($limit)
            ->get()
            ->result();
    }

    /**
     * Award a badge to a user and record some activity
     *
     * @param int $badgeID
     * @param int $userID This is the user that should get the award
     * @param int $insertUserID This is the user that gave the award
     * @param string $reason This is the reason the giver gave with the award
     */
    public function award($badgeID, $userID, $insertUserID = null, $reason = '') {
        $badge = $this->badgeModel->getID($badgeID);

        if (empty($badge) || $this->exists($userID, $badgeID)) {
            return;
        }

        // Clear the cache.
        unset($this->_badgeAwards[$userID]);

        $this->insert([
            'BadgeID' => $badgeID,
            'UserID' => $userID,
            'InsertUserID' => $insertUserID,
            'Reason' => $reason
        ]);

        // Record the points for this badge
        UserModel::givePoints($userID, $badge->AwardValue, 'Badge');

        // Increment the user's badge count
        $this->SQL->update('User')
            ->set('CountBadges', 'CountBadges + 1', false)
            ->where('UserID', $userID)
            ->put();

        if (is_null($insertUserID)) {
            $insertUserID = Gdn::session()->UserID;
        }

        // Record some activity
        $activityModel = new ActivityModel();

        $activity = [
            'ActivityType' => 'BadgeAward',
            'ActivityUserID' => $userID,
            'RegardingUserID' => $insertUserID,
            'Photo' => asset($badge->Photo, true),
            'RecordType' => 'Badge',
            'RecordID' => $badgeID,
            'Route' => '/yaga/badges/'.$badge->BadgeID.'/'.rawurlencode($badge->Name),
            'HeadlineFormat' => Gdn::translate('Yaga.Badge.EarnedHeadlineFormat'),
            'Data' => [
                'Name' => $badge->Name
            ],
            'Story' => $badge->Description
        ];

        // Create a public record
        $activityModel->queue($activity, false); // TODO: enable the grouped notifications after issue #1776 is resolved , ['GroupBy' => 'Route']);
        // Notify the user of the award
        $activity['NotifyUserID'] = $userID;
        $activityModel->queue($activity, 'BadgeAward', ['Force' => true]);

        // Actually save the activity
        $activityModel->saveQueue();

        $this->EventArguments['UserID'] = $userID;
        $this->fireEvent('AfterBadgeAward');
    }

    /**
     * Returns true if a user has a badge of a particular ID.
     *
     * @param int $userID
     * @param int $badgeID
     * @return bool
     */
    public function exists($userID, $badgeID) {
        return !empty($this->getWhere(['BadgeID' => $badgeID, 'UserID' => $userID])->firstRow());
    }

    /**
     * Returns the badges a user has already received.
     *
     * @param int $userID
     * @param string $dataType
     * @return mixed
     */
    public function getByUser($userID, $dataType = DATASET_TYPE_ARRAY) {
        return $this->SQL
            ->select()
            ->from($this->badgeModel->Name.' b')
            ->join($this->Name.' ba', 'ba.BadgeID = b.BadgeID', 'left')
            ->where('ba.UserID', $userID)
            ->get()
            ->result($dataType);
    }

    /**
     * Returns the badge IDs a user has already received from the memory cache.
     *
     * @param int $userID
     * @return array
     */
    public function getAwards($userID) {
        if (!isset($this->_badgeAwards[$userID])) {
            $this->_badgeAwards[$userID] = array_column($this->getWhere(['UserID' => $userID])->resultArray(), 'BadgeID');
        }
        return $this->_badgeAwards[$userID];
    }

    /**
     * Get the full list of badges joined with the award data for a specific user.
     *
     * @param int $userID
     * @return Gdn_DataSet
     */
    public function getWithEarned($userID) {
        return $this->SQL
            ->select('b.BadgeID, b.Name, b.Description, b.Photo, b.AwardValue')
            ->select('ba.UserID, ba.InsertUserID, ba.Reason, ba.DateInserted')
            ->select('ui.Name', '',  'InsertUserName')
            ->from($this->badgeModel->Name.' b')
            ->join($this->Name.' ba', 'b.BadgeID = ba.BadgeID and ba.UserID = '.intval($userID), 'left')
            ->join('User ui', 'ba.InsertUserID = ui.UserID', 'left')
            ->orderBy('b.Sort')
            ->get()
            ->result();
    }

    /**
     * Used by the DBA controller to update the denormalized badge count on the
     * user table via dba/counts
     * @param string $column
     * @param int $userID
     * @return boolean
     * @throws Gdn_UserException
     */
    public function counts($column, $userID = null) {
        if ($userID) {
            $where = ['UserID' => $userID];
        } else {
            $where = null;
        }

        $result = ['Complete' => true];
        switch($column) {
            case 'CountBadges':
                $this->Database->query(DBAModel::getCountSQL(
                    'count',
                    'User', 'YagaBadgeAward',
                    'CountBadges', 'BadgeAwardID',
                    'UserID', 'UserID',
                    $where
                ));
                break;
            default:
                throw new Gdn_UserException("Unknown column $column");
        }
        return $result;
    }

}
