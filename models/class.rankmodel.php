<?php if (!defined("APPLICATION")) {
    exit();
}

/* Copyright 2013 Zachary Doll */

/**
 * Describes ranks and their associated requirements/rewards
 *
 * Events:
 *
 * @package Yaga
 * @since 1.0
 */

class RankModel extends Gdn_Model
{
    /**
     * Used as a cache
     * @var DataSet
     */
    private static $_ranks = null;

    /**
     * Used as a cache
     * @var DataSet
     */
    private static $_perks = [];

    /**
     * Defines the related database table name.
     */
    public function __construct()
    {
        parent::__construct("YagaRank");
        $this->PrimaryKey = "RankID";
    }

    /**
     * Returns a list of all ranks
     *
     * @return Gdn_DataSet
     */
    public function get(
        $orderFields = "",
        $orderDirection = "asc",
        $limit = false,
        $pageNumber = false
    ) {
        if (
            $orderFields !== "" ||
            $orderDirection !== "asc" ||
            $limit !== false ||
            $pageNumber !== false
        ) {
            return parent::get(
                $orderFields,
                $orderDirection,
                $limit,
                $pageNumber
            );
        }

        // Cache any get() call with default arguments.
        if (empty(self::$_ranks)) {
            self::$_ranks = $this->SQL
                ->select()
                ->from($this->Name)
                ->orderBy("Sort")
                ->get()
                ->result();
        }

        return self::$_ranks;
    }

    /**
     * Returns the highest rank a user can currently achieve
     *
     * @param object $user
     * @return mixed null if no qualifying ranks are found, Rank object otherwise
     */
    public function getHighestQualifyingRank($user)
    {
        $points = $user->Points;
        $posts = $user->CountDiscussions + $user->CountComments;
        $startDate = strtotime($user->DateInserted);

        $ranks = $this->get();

        $highestRank = null;
        foreach ($ranks as $rank) {
            // skip disabled ranks
            if (!$rank->Enabled) {
                continue;
            }

            $targetDate = time() - $rank->AgeReq;
            if (
                $points >= $rank->PointReq &&
                $posts >= $rank->PostReq &&
                $startDate <= $targetDate
            ) {
                $highestRank = $rank;
            } else {
                // Don't continue if we do not qualify
                break;
            }
        }

        return $highestRank;
    }

    /**
     * Get a list of perks associated with the specified Rank ID
     *
     * @param int $rankID
     * @return array
     */
    public function getPerks($rankID)
    {
        if (!array_key_exists($rankID, self::$_perks)) {
            $ranks = $this->get();
            foreach ($ranks as $rank) {
                self::$_perks[$rank->RankID] = dbdecode($rank->Perks);

                if (self::$_perks[$rank->RankID] === false) {
                    self::$_perks[$rank->RankID] = [];
                }
            }
        }

        return array_key_exists($rankID, self::$_perks)
            ? self::$_perks[$rankID]
            : [];
    }

    /**
     * Returns all role IDs the specified rank confers as a perk
     *
     * @param int $rankID
     * @return array
     */
    public function getPerkRoleIDs($rankID)
    {
        $roleIDs = [];

        $perks = $this->getPerks($rankID);

        if (empty($perks)) {
            return $roleIDs;
        }

        foreach ($perks as $perk => $value) {
            if (substr($perk, 0, 4) === "Role") {
                $roleIDs[] = $value;
            }
        }

        return $roleIDs;
    }

    /**
     * Enable or disable a rank
     *
     * @param int $rankID
     * @param bool $enable
     */
    public function enable($rankID, $enable)
    {
        $enable = !$enable ? 0 : 1;
        $this->update(["Enabled" => $enable], ["RankID" => $rankID]);
    }

    /**
     * Set a user's rank and record some activity if it was a promotion
     *
     * @param int $rankID
     * @param int $userID This is the user that should get the award
     * @param bool $activity Whether or not to insert an activity record.
     */
    public function set($rankID, $userID, $activity = false)
    {
        $rank = $this->getID($rankID);
        $userModel = Gdn::userModel();
        $oldRankID = $userModel->getID($userID)->RankID;

        // Don't bother setting a rank that they already have
        if ($rank->RankID == $oldRankID) {
            return;
        }

        if ($activity) {
            // Throw up a promotion activity
            $activityModel = new ActivityModel();

            $activity = [
                "ActivityType" => "RankPromotion",
                "ActivityUserID" => $userID,
                "RegardingUserID" => $userID,
                "Photo" => Gdn::config("Yaga.Ranks.Photo"),
                "RecordType" => "Rank",
                "RecordID" => $rank->RankID,
                "HeadlineFormat" => Gdn::translate(
                    "Yaga.Rank.PromotedHeadlineFormat"
                ),
                "Data" => [
                    "Name" => $rank->Name,
                ],
                "Story" => $rank->Description,
            ];

            // Create a public record
            $activityModel->queue($activity, false); // TODO: enable the grouped notifications after issue #1776 is resolved , ['GroupBy' => 'Story']);

            // Notify the user of the award
            $activity["NotifyUserID"] = $userID;
            $activityModel->queue($activity, "RankPromotion", [
                "Force" => true,
            ]);

            $activityModel->saveQueue();
        }

        $userModel->setField($userID, "RankID", $rank->RankID);

        // Update the roles if necessary
        $this->_updateUserRoles($userID, $oldRankID, $rank->RankID);

        $this->EventArguments["Rank"] = $rank;
        $this->EventArguments["UserID"] = $userID;
        $this->fireEvent("AfterRankChange");
    }

    /**
     * Ensure a rank is always saved with a sort.
     *
     * @param array $formPostValues
     * @param array $settings
     * @return boolean
     */
    public function save($formPostValues, $settings = false)
    {
        if (
            !isset($formPostValues[$this->PrimaryKey]) &&
            !isset($formPostValues["Sort"])
        ) {
            $max =
                $this->SQL
                    ->select("Sort", "max", "MaxValue")
                    ->from($this->Name)
                    ->get()
                    ->firstRow(DATASET_TYPE_ARRAY)["MaxValue"] ?? 0;

            $formPostValues["Sort"] = $max + 1;
        }

        return parent::save($formPostValues, $settings);
    }

    /**
     * Updates the sort field for each rank in the sort array
     *
     * @param array $sortArray
     * @return boolean
     */
    public function saveSort($sortArray)
    {
        foreach ($sortArray as $index => $rank) {
            // remove the 'RankID_' prefix
            $rankID = substr($rank, 7);
            $this->setField($rankID, "Sort", $index);
        }
        return true;
    }

    /**
     * Updates a user roles by removing role perks from the old rank and adding the
     * roles from the new rank
     * @param int $userID
     * @param int $oldRankID
     * @param int $newRankID
     */
    private function _updateUserRoles($userID, $oldRankID, $newRankID)
    {
        $userModel = Gdn::userModel();

        // Get the user's current roles
        $currentRoleData = $userModel->getRoles($userID);
        $currentRoleIDs = array_column(
            $currentRoleData->resultArray(),
            "RoleID"
        );

        // Get the associated role perks
        $oldPerkRoles = $this->getPerkRoleIDs($oldRankID);
        $newPerkRoles = $this->getPerkRoleIDs($newRankID);

        // Remove any role perks the old rank had
        $tempRoleIDs = array_diff($currentRoleIDs, $oldPerkRoles);

        // Add our selected roles
        $newRoleIDs = array_unique(array_merge($tempRoleIDs, $newPerkRoles));

        // Set the combined roles
        if ($newRoleIDs != $currentRoleIDs) {
            $userModel->saveRoles($userID, $newRoleIDs, false);
        }
    }
}
