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
     * Memory cache for getUnobtained()
     * 
     * @var unobtainedCache
     */
    private $unobtainedCache = [];

    /**
     * Defines the related database table name.
     */
    public function __construct() {
        parent::__construct('BadgeAward');
    }

    /**
     * Gets the number of badges that have been awarded with a specific ID
     *
     * @param int $badgeID
     * @return int
     */
    public function getCount($badgeID = false) {
        if ($badgeID) {
            $wheres = ['BadgeID' => $badgeID];
        } else {
            $wheres = [];
        }
        return $this->SQL->getCount('BadgeAward', $wheres);
    }

    /**
     * Gets recently awarded badges with a specific ID
     *
     * @param int $badgeID
     * @param int $limit
     * @return dataset
     */
    public function getRecent($badgeID, $limit = 15) {
        return $this->SQL
            ->select('ba.UserID, ba.DateInserted, u.Name, u.Photo, u.Gender, u.Email')
            ->from('BadgeAward ba')
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
        $badge = Yaga::badgeModel()->getByID($badgeID);
        $this->unobtainedCache[$userID] = null;

        if (!empty($badge)) {
            if (!$this->exists($userID, $badgeID)) {
                $this->SQL->insert('BadgeAward', [
                    'BadgeID' => $badgeID,
                    'UserID' => $userID,
                    'InsertUserID' => $insertUserID,
                    'Reason' => $reason,
                    'DateInserted' => Gdn_Format::toDateTime()
                ]);

                // Record the points for this badge
                Yaga::givePoints($userID, $badge->AwardValue, 'Badge');

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
                    'Photo' => url($badge->Photo, true),
                    'RecordType' => 'Badge',
                    'RecordID' => $badgeID,
                    'Route' => '/yaga/badges/'.$badge->BadgeID.'/'.Gdn_Format::url($badge->Name),
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
        }
    }

    /**
     * Returns how many badges the user has of this particular id. It should only
     * ever be 1 or zero.
     *
     * @param int $userID
     * @param int $badgeID
     * @return int
     */
    public function exists($userID, $badgeID) {
        return $this->SQL
            ->select()
            ->from('BadgeAward')
            ->where('BadgeID', $badgeID)
            ->where('UserID', $userID)
            ->get()
            ->firstRow();
    }

    /**
     * Returns the badges a user has already received
     *
     * @param int $userID
     * @param string $dataType
     * @return mixed
     */
    public function getByUser($userID, $dataType = DATASET_TYPE_ARRAY) {
        return $this->SQL
            ->select()
            ->from('Badge b')
            ->join('BadgeAward ba', 'ba.BadgeID = b.BadgeID', 'left')
            ->where('ba.UserID', $userID)
            ->get()
            ->result($dataType);
    }

    /**
     * Returns the list of unobtained but enabled badges for a specific user
     *
     * @param int $userID
     * @return DataSet
     */
    public function getUnobtained($userID) {
        if (!isset($this->unobtainedCache[$userID])) {
            $px = $this->Database->DatabasePrefix;
            $sql = 'select b.BadgeID, b.Enabled, b.RuleClass, b.RuleCriteria, '
                .'ba.UserID '
                ."from {$px}Badge as b "
                ."left join {$px}BadgeAward as ba ON b.BadgeID = ba.BadgeID and ba.UserID = :UserID ";

            $this->unobtainedCache[$userID] = $this->Database->query($sql, [':UserID' => $userID])->result();
        }
        return $this->unobtainedCache[$userID];
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
                Gdn::database()->query(DBAModel::getCountSQL('count', 'User', 'BadgeAward', 'CountBadges', 'BadgeAwardID', 'UserID', 'UserID', $where));
                break;
            default:
                throw new Gdn_UserException("Unknown column $column");
        }
        return $result;
    }

}
