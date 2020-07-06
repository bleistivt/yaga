<?php if (!defined('APPLICATION')) exit();

/* Copyright 2013-2014 Zachary Doll */

/**
 * Describe the user content that has been acted upon
 *
 * Events:
 *
 * @package Yaga
 * @since 1.0
 */
class ActedModel extends Gdn_Model {

    /**
     * How long in seconds this table should be cached. Defaults to 10 minutes.
     * @var int
     */
    protected $expiry = 600;

    /** @var ReactionModel */
    private $reactionModel;

    /**
     * Defines the related database table name.
     */
    public function __construct(ReactionModel $reactionModel) {
        parent::__construct();

        $this->reactionModel = $reactionModel;
    }

    /**
     * Fetch content items by grouping reaction records.
     *
     * @param string $method received|taken|action|best|recent
     * @param int $userID
     * @param int $actionID
     * @param int $limit
     * @param int $offset
     * @return array
     */
    private function getItems($method, $limit, $offset, $userID = false, $actionID = false) {
        $px = $this->SQL->Database->DatabasePrefix;
        $reactionTable = $this->reactionModel->Name;

        $this->EventArguments['Method'] = $method;
        $this->EventArguments['DatabasePrefix'] = $px;
        $this->EventArguments['Joins'] = [];
        // Add the default category permission as a fallback for items without a category set.
        $this->EventArguments['PermissionColumns'] = [CategoryModel::defaultCategory()['PermissionCategoryID']];
        $this->EventArguments['DateColumns'] = [];
        $this->EventArguments['ScoreColumns'] = [];

        $this->fireEvent('beforeGetItems');

        $where = '';
        if ($method === 'received' || $method === 'taken') {
            $where = 'where '.($method === 'received' ? 'ParentAuthorID' : 'InsertUserID')
                .' = '.intval($userID).' and ActionID = '.intval($actionID);
        } else if ($method === 'action') {
            $where = 'where ActionID = '.intval($actionID);
        } else if ($method === 'best' && $userID) {
            $where = 'where ParentAuthorID = '.intval($userID);
        }

        $joins = implode(' ', $this->EventArguments['Joins']);

        $permissionColumns = implode(', ', array_merge(
            ['cd.PermissionCategoryID', 'cc.PermissionCategoryID'],
            $this->EventArguments['PermissionColumns']
        ));

        $permissions = Gdn::session()->getPermissionsArray()['Vanilla.Discussions.View'] ?? [0];
        $permissionsIn = implode(', ', array_map('intval', $permissions));

        $dateColumns = implode(', ', array_merge(
            ['d.DateInserted', 'c.DateInserted'],
            $this->EventArguments['DateColumns']
        ));

        $scoreColumns = implode(', ', array_merge(
            ['d.Score', 'c.Score'],
            $this->EventArguments['ScoreColumns']
        ));

        $permissionColumns = implode(', ', array_merge(
            ['cd.PermissionCategoryID', 'cc.PermissionCategoryID'],
            $this->EventArguments['PermissionColumns']
        ));

        // Items are usually sorted by their creation date.
        // An exception are the "best" (sorted by score) and "recent" (sorted by last reaction date) views.
        $order = 'coalesce('.($method === 'best' ? $scoreColumns : $dateColumns).')';
        $order = ($method === 'recent' ? 'r.DateInserted' : $order);

        $offset = abs(intval($offset));
        $limit = abs(intval($limit));

        return $this->SQL->query("
            select r.*

            from (
                select ParentID, ParentType, max(DateInserted) as DateInserted
                from {$px}{$reactionTable} {$where}
                group by ParentID, ParentType
                order by null
            ) as r

            left join {$px}Discussion d
                on (r.ParentType = 'discussion' and r.ParentID = d.DiscussionID)
            left join {$px}Category cd
                on (r.ParentType = 'discussion' and d.CategoryID = cd.CategoryID)

            left join {$px}Comment c
                on (r.ParentType = 'comment' and r.ParentID = c.CommentID)
            left join {$px}Discussion td
                on (r.ParentType = 'comment' and c.DiscussionID = td.DiscussionID)
            left join {$px}Category cc
                on (r.ParentType = 'comment' and td.CategoryID = cc.CategoryID)

            {$joins}

            where coalesce({$permissionColumns}) in ({$permissionsIn})

            order by {$order} desc

            limit {$offset}, {$limit}
        ")->resultArray();
    }

    /**
     * Returns a list of all posts by a specific user that has received at least
     * one of the specified actions.
     *
     * @param int $userID
     * @param int $actionID
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getReceived($userID, $actionID, $limit = null, $offset = 0) {
        $cacheKey = "yaga.profile.reactions.{$userID}.{$actionID}.{$limit}.{$offset}";
        $content = Gdn::cache()->get($cacheKey);

        if ($content == Gdn_Cache::CACHEOP_FAILURE) {
            $content = $this->getItems('received', $limit, $offset, $userID, $actionID);

            // Add result to cache only if guest permissions apply.
            if (!Gdn::session()->isValid()) {
                Gdn::cache()->store($cacheKey, $content, [Gdn_Cache::FEATURE_EXPIRY => $this->expiry]);
            }
        }

        return $this->process($content);
    }

    /**
     * Returns a list of all posts of which a specific user has taken the
     * specified action.
     *
     * @param int $userID
     * @param int $actionID
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getTaken($userID, $actionID, $limit = null, $offset = 0) {
        $cacheKey = "yaga.profile.actions.{$userID}.{$actionID}.{$limit}.{$offset}";
        $content = Gdn::cache()->get($cacheKey);

        if ($content == Gdn_Cache::CACHEOP_FAILURE) {
            $content = $this->getItems('taken', $limit, $offset, $userID, $actionID);

            if (!Gdn::session()->isValid()) {
                Gdn::cache()->store($cacheKey, $content, [Gdn_Cache::FEATURE_EXPIRY => $this->expiry]);
            }
        }

        return $this->process($content);
    }

    /**
     * Returns a list of all posts that has received at least one of the
     * specified actions.
     *
     * @param int $actionID
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getAction($actionID, $limit = null, $offset = 0) {
        $cacheKey = "yaga.best.actions.{$actionID}.{$limit}.{$offset}";
        $content = Gdn::cache()->get($cacheKey);

        if ($content == Gdn_Cache::CACHEOP_FAILURE) {
            $content = $this->getItems('action', $limit, $offset, $userID = false, $actionID);

            if (!Gdn::session()->isValid()) {
                Gdn::cache()->store($cacheKey, $content, [Gdn_Cache::FEATURE_EXPIRY => $this->expiry]);
            }
        }

        return $this->process($content);
    }

    /**
     * Returns a list of all posts by a specific user ordered by highest score
     *
     * @param int $userID
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getBest($userID = null, $limit = null, $offset = 0) {
        $cacheKey = "yaga.profile.best.{$userID}.{$limit}.{$offset}";
        $content = Gdn::cache()->get($cacheKey);

        if ($content == Gdn_Cache::CACHEOP_FAILURE) {
            $content = $this->getItems('best', $limit, $offset, $userID);

            if (!Gdn::session()->isValid()) {
                Gdn::cache()->store($cacheKey, $content, [Gdn_Cache::FEATURE_EXPIRY => $this->expiry]);
            }
        }

        return $this->process($content);
    }

    /**
     * Returns a list of all recent scored posts ordered by date reacted
     *
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getRecent($limit = null, $offset = 0) {
        $cacheKey = "yaga.best.recent.{$limit}.{$offset}";
        $content = Gdn::cache()->get($cacheKey);

        if ($content == Gdn_Cache::CACHEOP_FAILURE) {
            $content = $this->getItems('recent', $limit, $offset);

            if (!Gdn::session()->isValid()) {
                Gdn::cache()->store($cacheKey, $content, [Gdn_Cache::FEATURE_EXPIRY => $this->expiry]);
            }
        }

        return $this->process($content);
    }

    /**
     * Process content items into a uniform format for output.
     *
     * @since 2.0
     * @param array $records Array of record types and IDs to resolve.
     * @return array
     */
    protected function process($records) {
        $content = [];

        foreach ($records as $record) {
            $item = $this->reactionModel->getReactionItem($record['ParentType'], $record['ParentID']);

            if (empty($item)) {
                // Item not found or no active handler for this item.
                continue;
            }

            // Fill the reaction cache to reduce the amount of queries.
            $this->reactionModel->prefetch($record['ParentType'], $record['ParentID']);

            $item['ItemType'] = $record['ParentType'];
            $item['ContentID'] = $record['ParentID'];
            $item['ContentURL'] = $item['Url'];

            // Titles are escaped in the view.
            $item['Name'] = htmlspecialchars_decode($item['Name']);

            // Attach User
            $item['Author'] = Gdn::userModel()->getID($item['InsertUserID'] ?? false);

            $content[] = $item;
        }

        return (object)['Content' => $content, 'TotalRecords' => false];
    }

}
