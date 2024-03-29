<?php if (!defined("APPLICATION")) {
    exit();
}

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

class ReactionModel extends Gdn_Model
{
    public const TYPE_DISCUSSION = "discussion";
    public const TYPE_COMMENT = "comment";
    public const TYPE_ACTIVITY = "activity";

    public const ITEMS_PROFILE_REACTION = "received"; //index: Profile
    public const ITEMS_PROFILE_BEST = "best"; //index: LatestScore
    public const ITEMS_BEST_REACTION = "action"; //index: Best
    public const ITEMS_BEST_ALL = "bestof"; //index: LatestScore
    public const ITEMS_BEST_RECENT = "recent"; //index: LatestDate

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
    public function __construct(
        ActionModel $actionModel,
        UserModel $userModel,
        EventManager $eventManager
    ) {
        parent::__construct("YagaReaction");
        $this->PrimaryKey = "ReactionID";

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
    public function getList($id, $type)
    {
        $px = $this->Database->DatabasePrefix;
        $reactionTable = $this->Name;
        $actionTable = $this->actionModel->Name;

        // try getting the record count from the cache
        if (array_key_exists($type . $id, $this->_reactions)) {
            $reactions = $this->_reactions[$type . $id];
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

        $sql =
            "select a.*, " .
            "(select count(r.ReactionID) " .
            "from {$px}{$reactionTable} as r " .
            "where r.ParentID = :ParentID and r.ParentType = :ParentType " .
            "and r.ActionID = a.ActionID) as Count " .
            "from {$px}{$actionTable} AS a " .
            "order by a.Sort";

        return $this->Database
            ->query($sql, [":ParentID" => $id, ":ParentType" => $type])
            ->result();
    }

    /**
     * Returns the reaction records associated with the specified user content.
     *
     * @param int $id
     * @param string $type is the kind of ID. Valid: comment, discussion, activity
     * @return mixed DataSet if it exists, null otherwise
     */
    public function getRecord($id, $type)
    {
        // try getting the record from the cache
        if (array_key_exists($type . $id, $this->_reactions)) {
            return $this->_reactions[$type . $id];
        }

        $result = $this->SQL
            ->select("a.*, r.InsertUserID as UserID, r.DateInserted")
            ->from($this->actionModel->Name . " a")
            ->join($this->Name . " r", "a.ActionID = r.ActionID")
            ->where("r.ParentID", $id)
            ->where("r.ParentType", $type)
            ->orderBy("r.DateInserted")
            ->get()
            ->result();

        $this->_reactions[$type . $id] = $result;
        return $result;
    }

    /**
     * Return the count of reactions received by a user
     *
     * @param int $userID
     * @param int $actionID
     * @return Gdn_DataSet
     */
    public function getUserCount($userID, $actionID)
    {
        return $this->SQL
            ->select("ReactionID", "count", "RowCount")
            ->from($this->Name)
            ->where(["ActionID" => $actionID, "ParentAuthorID" => $userID])
            ->get()
            ->firstRow()->RowCount;
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
    public function set($id, $type, $item, $userID, $actionID)
    {
        // clear the cache
        unset($this->_reactions[$type . $id]);

        if (!is_array($item)) {
            $item = ["InsertUserID" => $item, "DisplayBest" => false];
        }

        $eventArgs = [
            "ParentID" => $id,
            "ParentType" => $type,
            "ParentUserID" => $item["InsertUserID"],
            "InsertUserID" => $userID,
            "ActionID" => $actionID,
        ];

        $newAction = $this->actionModel->getID($actionID);
        $points = $score = $newAction->AwardValue;

        $currentReaction = $this->getWhere([
            "ParentID" => $id,
            "ParentType" => $type,
            "InsertUserID" => $userID,
        ])->firstRow();
        $eventArgs["CurrentReaction"] = $currentReaction;

        $this->fireEvent("BeforeReactionSave", $eventArgs);
        $now = DateTimeFormatter::timeStampToDateTime(time());

        if ($currentReaction) {
            $oldAction = $this->actionModel->getID($currentReaction->ActionID);

            if ($actionID == $currentReaction->ActionID) {
                // remove the record
                $reaction = $this->SQL->delete($this->Name, [
                    "ParentID" => $id,
                    "ParentType" => $type,
                    "InsertUserID" => $userID,
                    "ActionID" => $actionID,
                ]);
                $eventArgs["Exists"] = false;
                $score = 0;
                $points = -1 * $oldAction->AwardValue;
            } else {
                // update the record
                $reaction = $this->SQL
                    ->update($this->Name)
                    ->set("ActionID", $actionID)
                    ->set("DateInserted", $now)
                    ->where("ParentID", $id)
                    ->where("ParentType", $type)
                    ->where("InsertUserID", $userID)
                    ->put();

                $eventArgs["Exists"] = true;
                $points = -1 * ($oldAction->AwardValue - $points);
            }
        } else {
            // insert a record
            $reaction = $this->SQL->insert($this->Name, [
                "ActionID" => $actionID,
                "ParentID" => $id,
                "ParentType" => $type,
                "ParentAuthorID" => $item["InsertUserID"],
                "InsertUserID" => $userID,
                "DateInserted" => $now,
            ]);

            $eventArgs["Exists"] = true;
        }

        // Update the parent item score
        $item["Score"] = $this->setUserScore(
            $type,
            $id,
            $userID,
            $score,
            $points
        );
        $eventArgs["TotalScore"] = $item["Score"];

        // Set the "latest" flag.
        $this->setLatestItem($type, $id, $item);

        // Give the user points commesurate with reaction activity
        $this->userModel::givePoints(
            $item["InsertUserID"],
            $points,
            "Reaction"
        );
        $eventArgs["Points"] = $points;

        $this->fireEvent("AfterReactionSave", $eventArgs);

        return $reaction;
    }

    /**
     * Fills the memory cache with the specified reaction records
     *
     * @since 1.1
     * @param string $type
     * @param array $ids
     */
    public function prefetch($type, $ids)
    {
        if (!is_array($ids)) {
            $ids = (array) $ids;
        }

        if (!empty($ids)) {
            $result = $this->SQL
                ->select(
                    "a.*, r.InsertUserID as UserID, r.DateInserted, r.ParentID"
                )
                ->from($this->actionModel->Name . " a")
                ->join($this->Name . " r", "a.ActionID = r.ActionID")
                ->whereIn("r.ParentID", $ids)
                ->where("r.ParentType", $type)
                ->orderBy("r.DateInserted")
                ->get()
                ->result();

            foreach ($ids as $id) {
                $this->_reactions[$type . $id] = [];
            }

            $userIDs = [];
            // fill the cache
            foreach ($result as $reaction) {
                $userIDs[] = $reaction->UserID;
                $this->_reactions[$type . $reaction->ParentID][] = $reaction;
            }

            // Prime the user cache
            $this->userModel->getIDs($userIDs);
        }
    }

    /**
     * Fetch "best" content by various criteria
     *
     * @param string $method
     * @param int $actionID
     * @param int $userID
     * @param int $limit
     * @param int $offset
     * @return object
     */
    public function getBest(
        $method,
        $limit,
        $offset,
        $actionID = false,
        $userID = false
    ) {
        $session = Gdn::session();
        $permissions = $session->getPermissionsArray()[
            "Vanilla.Discussions.View"
        ] ?? [0];
        $inProfile =
            $method === self::ITEMS_PROFILE_REACTION ||
            $method === self::ITEMS_PROFILE_BEST;

        // Add the global junction ID.
        $permissions = array_merge($permissions, [-1]);

        $this->SQL
            ->from($this->Name)
            ->whereIn("ParentPermissionCategoryID", $permissions);

        // Is this on a profile page (user context)?
        if ($inProfile) {
            $this->SQL->where("ParentAuthorID", $userID);
        }

        // Apply a threshold for all public aggregated "best of" pages.
        if (
            $method === self::ITEMS_BEST_ALL ||
            $method === self::ITEMS_BEST_RECENT
        ) {
            $this->SQL->where(
                "ParentScore >=",
                Gdn::config("Yaga.BestContent.Threshold")
            );
        }

        // Group by specific reaction or any reacton?
        if (
            $method === self::ITEMS_PROFILE_REACTION ||
            $method === self::ITEMS_BEST_REACTION
        ) {
            $this->SQL->where("Latest >", 0);
            $this->SQL->where("ActionID", $actionID);
        } else {
            $this->SQL->where("Latest", 2);
        }

        if (
            $method === self::ITEMS_PROFILE_REACTION ||
            $method === self::ITEMS_BEST_RECENT
        ) {
            $this->SQL->orderBy("ParentDateInserted", "desc");
        } else {
            $this->SQL
                ->orderBy("ParentScore", "desc")
                ->orderBy("ParentDateInserted", "desc");
        }

        $records = $this->SQL
            ->select("ParentType, ParentID, ParentAuthorID")
            ->limit($limit, $offset)
            ->get()
            ->resultArray();

        // Repeat the query for the total count.
        if ($inProfile && Gdn::config("Yaga.Profile.FullPagers")) {
            $this->SQL
                ->from($this->Name)
                ->whereIn("ParentPermissionCategoryID", $permissions);

            $this->SQL->where("ParentAuthorID", $userID);

            if ($method === self::ITEMS_PROFILE_REACTION) {
                $this->SQL->where("Latest >", 0);
                $this->SQL->where("ActionID", $actionID);
            } else {
                $this->SQL->where("Latest", 2);
            }

            $total = $this->SQL
                ->select("ReactionID", "count", "RowCount")
                ->get()
                ->firstRow()->RowCount;
        }

        $content = [];

        // Prime the user cache.
        $this->userModel->getIDs(array_column($records, "ParentAuthorID"));
        $prefetch = [];

        foreach ($records as $record) {
            $item = $this->getReactionItem(
                $record["ParentType"],
                $record["ParentID"]
            );

            // Check the permission again in case the cached version is outdated.
            $hasPermission = $session->checkPermission(
                "Vanilla.Discussions.View",
                true,
                "Category",
                $item["PermissionCategoryID"]
            );
            if (empty($item) || !$hasPermission) {
                $this->setLatestItem(
                    $record["ParentType"],
                    $record["ParentID"],
                    $item
                );
                continue;
            }

            if (isset($prefetch[$record["ParentType"]])) {
                $prefetch[$record["ParentType"]][] = $record["ParentID"];
            } else {
                $prefetch[$record["ParentType"]] = [$record["ParentID"]];
            }

            $item["ItemType"] = $record["ParentType"];
            $item["ContentID"] = $record["ParentID"];
            $item["ContentURL"] = $item["Url"];

            // Attach User
            $item["Author"] = $this->userModel->getID(
                $item["InsertUserID"] ?? false
            );

            $content[] = $item;
        }

        // Fill the reaction cache to reduce the amount of queries.
        foreach ($prefetch as $type => $ids) {
            $this->prefetch($type, $ids);
        }

        return (object) [
            "Content" => $content,
            "TotalRecords" => $total ?? false,
        ];
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
    public function getReactionItem($type, $id)
    {
        //$container = Gdn::getContainer();
        $row = [];

        if ($type === self::TYPE_DISCUSSION) {
            //$row = $container->get(DiscussionModel::class)->getID($id, DATASET_TYPE_ARRAY);
            $row = $this->SQL
                ->getWhere("Discussion", ["DiscussionID" => $id])
                ->firstRow(DATASET_TYPE_ARRAY);

            if ($row) {
                // Titles are escaped in the view.
                $row["Name"] = htmlspecialchars_decode($row["Name"]);
                $row["DisplayBest"] = true;
                $row["Url"] = discussionUrl($row);
                $category = CategoryModel::categories($row["CategoryID"]);
                $row["PermissionCategoryID"] =
                    $category["PermissionCategoryID"] ?? -1;
            }
        } elseif ($type === self::TYPE_COMMENT) {
            //$row = $container->get(CommentModel::class)->getID($id, DATASET_TYPE_ARRAY);
            $row = $this->SQL
                ->select("d.CategoryID, d.Name, c.*")
                ->from("Comment c")
                ->where("c.CommentID", $id)
                ->join("Discussion d", "c.DiscussionID = d.DiscussionID")
                ->get()
                ->firstRow(DATASET_TYPE_ARRAY);

            if ($row) {
                $row["Name"] = htmlspecialchars_decode($row["Name"] ?? "");
                $row["DisplayBest"] = true;
                $row["Url"] = url(
                    "/discussion/comment/{$id}#Comment_{$id}",
                    true
                );
                $category = CategoryModel::categories($row["CategoryID"]);
                $row["PermissionCategoryID"] =
                    $category["PermissionCategoryID"] ?? -1;
            }
        } elseif ($type === self::TYPE_ACTIVITY) {
            //$row = $container->get(ActivityModel::class)->getID($id, DATASET_TYPE_ARRAY);
            $row = $this->SQL
                ->getWhere("Activity", ["ActivityID" => $id])
                ->firstRow(DATASET_TYPE_ARRAY);

            if ($row) {
                $row["InsertUserID"] = $row["RegardingUserID"];
                $row["DisplayBest"] = false;
            }
        }
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
        return $this->eventManager->fireFilter(
            "yaga_getReactionItem",
            $row,
            $type,
            $id
        );
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
     * @param int $change The increment/decrement represented by this score change
     * @return int Total score if request was successful, false if not.
     */
    private function setUserScore($type, $id, $userID, $score, $change)
    {
        $total = 0;
        if ($type === self::TYPE_DISCUSSION) {
            $total = (new DiscussionModel())->setUserScore(
                $id,
                $userID,
                $score
            );
        } elseif ($type === self::TYPE_COMMENT) {
            $total = (new CommentModel())->setUserScore($id, $userID, $score);
        }
        return $this->eventManager->fireFilter(
            "yaga_setUserScore",
            $total,
            $type,
            $id,
            $userID,
            $score,
            $change
        );
    }

    /**
     * Atomically set the "latest" flag on a reaction record group.
     *
     * @param string $type The type of the item
     * @param int $id The items ID
     * @param array $item The item
     */
    public function setLatestItem($type, $id, $item = [])
    {
        $table = $this->Database->DatabasePrefix . $this->Name;

        // Fetch the item if none was supplied.
        if (empty($item)) {
            $item = $this->getReactionItem($type, $id);
        }

        // Should this be shown on "best" pages?
        if (empty($item) || $item["DisplayBest"] === false) {
            $this->SQL->put(
                $this->Name,
                ["Latest" => 0],
                ["ParentID" => $id, "ParentType" => $type]
            );
            return;
        }

        $this->Database->beginTransaction();

        // Selecting for update locks the corresponding rows for reads on InnoDB.
        $sql =
            "select ReactionID, ActionID from {$table} " .
            "where ParentID = :ParentID and ParentType = :ParentType " .
            "order by DateInserted desc for update";

        $result = $this->Database
            ->query($sql, [":ParentID" => $id, ":ParentType" => $type])
            ->resultArray();

        // Find the latest reaction of each action type.
        $latest = [];
        $actionIDs = [];
        foreach ($result as $reaction) {
            if (!in_array($reaction["ActionID"], $actionIDs)) {
                $latest[] = $reaction["ReactionID"];
                $actionIDs[] = $reaction["ActionID"];
            }
        }

        if (!empty($latest)) {
            $this->SQL->put(
                $this->Name,
                [
                    "Latest" => 0,
                    "ParentPermissionCategoryID" => null,
                    "ParentDateInserted" => null,
                    "ParentScore" => null,
                ],
                ["ParentID" => $id, "ParentType" => $type]
            );

            // 1 = latest reaction of an action
            $this->SQL->put(
                $this->Name,
                [
                    "Latest" => 1,
                    "ParentPermissionCategoryID" =>
                        $item["PermissionCategoryID"],
                    "ParentDateInserted" => $item["DateInserted"],
                    "ParentScore" => $item["Score"],
                ],
                ["ReactionID" => $latest]
            );

            // 2 = latest reaction overall
            $this->SQL->put(
                $this->Name,
                ["Latest" => 2],
                ["ReactionID" => $latest[0]]
            );
        }

        $this->Database->commitTransaction();
    }

    /**
     * Used by the DBA controller to update denormalized reaction data via dba/counts
     *
     * @param string $column
     * @param int $userID
     * @return boolean
     * @throws Gdn_UserException
     */
    public function counts($column, $from = false, $to = false)
    {
        if ($column !== "Latest") {
            throw new Gdn_UserException("Unknown column $column");
        }

        // Because this is a quite expensive operation, only process 1000 records at once.
        $chunk = (int) (DBAModel::$ChunkSize / 10);

        // We cannot use DBAModel::primaryKeyRange here because of DI problems.
        $range = $this->SQL
            ->select($this->PrimaryKey, "min", "MinValue")
            ->select($this->PrimaryKey, "max", "MaxValue")
            ->from($this->Name)
            ->get()
            ->firstRow();
        $min = $range->MinValue ?? 0;
        $max = $range->MaxValue ?? 0;

        if (!$from) {
            $from = $min;
            $to = $min + $chunk - 1;
        }
        $from = (int) $from;
        $to = (int) $to;

        $items = $this->SQL
            ->select("ParentID, ParentType")
            ->from($this->Name)
            ->where("ReactionID >=", $from)
            ->where("ReactionID <=", $to)
            ->groupBy("ParentID, ParentType")
            ->get()
            ->resultArray();

        foreach ($items as $item) {
            $this->setLatestItem($item["ParentType"], $item["ParentID"]);
        }

        return [
            "Complete" => $to >= $max,
            "Percent" => min(round(($to * 100) / $max), 100) . "%",
            "Args" => [
                "from" => $to + 1,
                "to" => $to + $chunk,
            ],
        ];
    }

    /**
     * Add multi-dimensional reaction data to an array.
     * Users should make sure that ReationModel::prefetch is called before fetching a large number of reactions.
     *
     * @param array|iterable $rows Results we need to associate user data with.
     * @param array $columns Database columns containing IDs and types to get data for.
     *    A value of ['ContentID', 'ItemType'] will use the second field for the type.
     *    A value of ['CommentID'] will recognize "comment" as the type.
     */
    public function expandYagaReactions(&$rows, array $columns)
    {
        if (count($rows) === 0 || count($columns) === 0) {
            return;
        }

        reset($rows);
        $single = !($rows instanceof Traversable) && is_string(key($rows));

        $type =
            count($columns) === 1
                ? strtolower(stringEndsWith($columns[0], "ID", true, true))
                : "";

        if ($single) {
            $rows["yagaReactions"] = [];
            foreach (
                $this->getRecord(
                    $rows[$columns[0]],
                    $type ?: $rows[$columns[1]]
                )
                as $reaction
            ) {
                $reaction->InsertUserID = $reaction->UserID;
                // UserModel::expandUsers requires an array.
                $rows["yagaReactions"][] = (array) $reaction;
            }
            $this->userModel->expandUsers($row["yagaReactions"], [
                "InsertUserID",
            ]);
        } else {
            foreach ($rows as &$row) {
                $row["yagaReactions"] = [];
                foreach (
                    $this->getRecord(
                        $row[$columns[0]],
                        $type ?: $row[$columns[1]]
                    )
                    as $reaction
                ) {
                    $reaction->InsertUserID = $reaction->UserID;
                    $row["yagaReactions"][] = (array) $reaction;
                }
                $this->userModel->expandUsers($row["yagaReactions"], [
                    "InsertUserID",
                ]);
            }
        }
    }
}
