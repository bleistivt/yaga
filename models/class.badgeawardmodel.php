<?php if (!defined("APPLICATION")) {
    exit();
}

/* Copyright 2013 Zachary Doll */

/**
 * Handles badge awards
 *
 * Events:
 *
 * @package Yaga
 * @since 1.0
 */
class BadgeAwardModel extends Gdn_Model
{
    /**
     * This is used as a cache.
     * @var array
     */
    private $_badgeAwards = [];

    /** @var BadgeModel */
    private $badgeModel;

    /** @var Gdn_Session */
    private $session;

    /**
     * Defines the related database table name.
     */
    public function __construct(BadgeModel $badgeModel, Gdn_Session $session)
    {
        parent::__construct("YagaBadgeAward");
        $this->PrimaryKey = "BadgeAwardID";

        $this->badgeModel = $badgeModel;
        $this->session = $session;
    }

    /**
     * Gets recently awarded badges with a specific ID
     *
     * @param int $badgeID
     * @param int $limit
     * @return Gdn_DataSet
     */
    public function getRecent($badgeID, $limit = 15)
    {
        return $this->SQL
            ->select(
                "ba.UserID, ba.DateInserted, u.Name, u.Photo, u.Gender, u.Email"
            )
            ->from($this->Name . " ba")
            ->join("User u", "ba.UserID = u.UserID")
            ->where("BadgeID", $badgeID)
            ->orderBy("DateInserted", "Desc")
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
    public function award($badgeID, $userID, $insertUserID = null, $reason = "")
    {
        $badge = $this->badgeModel->getID($badgeID);

        if (empty($badge) || $this->exists($userID, $badgeID)) {
            return;
        }

        // Clear the cache.
        unset($this->_badgeAwards[$userID]);

        $this->insert([
            "BadgeID" => $badgeID,
            "UserID" => $userID,
            "InsertUserID" => $insertUserID,
            "Reason" => $reason,
        ]);

        // Record the points for this badge
        UserModel::givePoints($userID, $badge->AwardValue, "Badge");

        // Increment the user's badge count
        $this->SQL
            ->update("User")
            ->set("CountBadges", "CountBadges + 1", false)
            ->where("UserID", $userID)
            ->put();

        if (is_null($insertUserID)) {
            $insertUserID = $this->session->UserID;
        }

        // Record some activity
        $activityModel = new ActivityModel();

        $activity = [
            "ActivityType" => "BadgeAward",
            "ActivityUserID" => $userID,
            "RegardingUserID" => $insertUserID,
            "Photo" => asset($badge->Photo, true),
            "RecordType" => "Badge",
            "RecordID" => $badgeID,
            "Route" =>
                "/yaga/badges/" .
                $badge->BadgeID .
                "/" .
                rawurlencode($badge->Name),
            "HeadlineFormat" => Gdn::translate(
                "Yaga.Badge.EarnedHeadlineFormat"
            ),
            "Data" => [
                "Name" => $badge->Name,
            ],
            "Story" => $badge->Description,
        ];

        // Create a public record
        $activityModel->queue($activity, false); // TODO: enable the grouped notifications after issue #1776 is resolved , ['GroupBy' => 'Route']);
        // Notify the user of the award
        $activity["NotifyUserID"] = $userID;
        $activityModel->queue($activity, "BadgeAward", ["Force" => true]);

        // Actually save the activity
        $activityModel->saveQueue();

        $this->EventArguments["UserID"] = $userID;
        $this->fireEvent("AfterBadgeAward");
    }

    /**
     * Returns true if a user has a badge of a particular ID.
     *
     * @param int $userID
     * @param int $badgeID
     * @return bool
     */
    public function exists($userID, $badgeID)
    {
        if (!isset($this->_badgeAwards[$userID])) {
            $this->_badgeAwards[$userID] = array_column(
                $this->getWhere(["UserID" => $userID])->resultArray(),
                "BadgeID"
            );
        }
        return in_array($badgeID, $this->_badgeAwards[$userID]);
    }

    /**
     * Returns the badges a user has already received.
     *
     * @param int $userID
     * @param string $dataType
     * @return mixed
     */
    public function getByUser($userID, $dataType = DATASET_TYPE_ARRAY)
    {
        return $this->SQL
            ->select()
            ->from($this->badgeModel->Name . " b")
            ->join($this->Name . " ba", "ba.BadgeID = b.BadgeID", "left")
            ->where("ba.UserID", $userID)
            ->get()
            ->result($dataType);
    }

    /**
     * Get the full list of badges joined with the award data for a specific user.
     *
     * @param int $userID
     * @return Gdn_DataSet
     */
    public function getWithEarned($userID)
    {
        return $this->SQL
            ->select("b.BadgeID, b.Name, b.Description, b.Photo, b.AwardValue")
            ->select("ba.UserID, ba.InsertUserID, ba.Reason, ba.DateInserted")
            ->select("ui.Name", "", "InsertUserName")
            ->from($this->badgeModel->Name . " b")
            ->join(
                $this->Name . " ba",
                "b.BadgeID = ba.BadgeID and ba.UserID = " . intval($userID),
                "left"
            )
            ->join("User ui", "ba.InsertUserID = ui.UserID", "left")
            ->orderBy("b.Sort")
            ->get()
            ->result();
    }

    /**
     * Check for outstanding badge awards.
     *
     * @param mixed $sender The sending object
     * @param string $handler The event name to check associated rules for awards
     */
    public function executeHooks($sender, $handler)
    {
        if (!Gdn::config("Yaga.Badges.Enabled") || !$this->session->isValid()) {
            return;
        }

        // Let's us use __FUNCTION__ in the original hook
        $hook = strtolower(str_ireplace("_Handler", "", $handler));

        $badges = $this->badgeModel->get();
        $interactionRules = $this->badgeModel->getInteractionRules();

        $rules = [];
        foreach ($badges as $badge) {
            // The badge award needs to be processed
            $hasInteraction = array_key_exists(
                $badge->RuleClass,
                $interactionRules
            );
            $obtained = $this->exists($this->session->UserID, $badge->BadgeID);
            if (!$badge->Enabled || !($hasInteraction || !$obtained)) {
                continue;
            }

            // Create a rule object if needed
            $class = $badge->RuleClass;
            if (!in_array($class, $rules)) {
                $rules[$class] = $this->badgeModel->createRule($class);
            }
            $rule = $rules[$class];

            // Only check awards for rules that use this hook
            $hooks = array_map("strtolower", $rule->hooks());
            if (!in_array($hook, $hooks)) {
                continue;
            }

            $criteria = (object) dbdecode($badge->RuleCriteria);
            $result = $rule->award($sender, $this->session->User, $criteria);
            if (!$result) {
                continue;
            }

            $awardedUserIDs = [];
            if (is_array($result)) {
                $awardedUserIDs = $result;
            } elseif (is_numeric($result)) {
                $awardedUserIDs[] = $result;
            } else {
                $awardedUserIDs[] = $this->session->UserID;
            }

            $systemUserID = Gdn::userModel()->getSystemUserID();
            foreach ($awardedUserIDs as $awardedUserID) {
                if ($awardedUserID == $systemUserID) {
                    continue;
                }
                $this->award(
                    $badge->BadgeID,
                    $awardedUserID,
                    $this->session->UserID
                );
            }
        }
    }

    /**
     * Used by the DBA controller to update the denormalized badge count on the
     * user table via dba/counts
     * @param string $column
     * @param int $userID
     * @return boolean
     * @throws Gdn_UserException
     */
    public function counts($column, $userID = null)
    {
        if ($column === "CountBadges") {
            $this->Database->query(
                DBAModel::getCountSQL(
                    "count",
                    "User",
                    "YagaBadgeAward",
                    "CountBadges",
                    "BadgeAwardID",
                    "UserID",
                    "UserID",
                    $userID ? ["UserID" => $userID] : null
                )
            );
        } elseif ($column === "Points") {
            $px = $this->Database->DatabasePrefix;

            $reactionTable = Gdn::getContainer()->get(ReactionModel::class)
                ->Name;
            $actionTable = Gdn::getContainer()->get(ActionModel::class)->Name;
            $badgeAwardTable = $this->Name;
            $badgeTable = $this->badgeModel->Name;

            $this->Database->query(
                "
                update {$px}User u set u.Points =
                coalesce((
                  select sum(a.AwardValue) from {$px}{$reactionTable} r
                  inner join {$px}{$actionTable} a on r.ActionID = a.ActionID
                  where u.UserID = r.ParentAuthorID
                ), 0) + coalesce((
                  select sum(b.AwardValue) from {$px}{$badgeAwardTable} ba
                  inner join {$px}{$badgeTable} b on ba.BadgeID = b.BadgeID
                  where u.UserID = ba.UserID
                ), 0)
            " . ($userID ? " where u.UserID = " . intval($userID) : "")
            );
        } else {
            throw new Gdn_UserException("Unknown column $column");
        }

        return ["Complete" => true];
    }
}
