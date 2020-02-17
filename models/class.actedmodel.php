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
     * How long in seconds this table should be cached. Defaults to 10 minutes
     * @var int
     */
    protected $_Expiry = 600;

    /**
     * Convenience function to save some typing. Gets the basic 'best' query set
     * up in an SQL driver and returns it
     * @param string $table Discussion or Comment
     * @return Gdn_SQLDriver
     */
    private function baseSQL($table = 'Discussion') {
        switch($table) {
            case 'Comment':
                $sql = Gdn::sql()
                    ->select('c.Score, c.CommentID, c.InsertUserID, c.DiscussionID, c.DateInserted')
                    ->from('Comment c')
                    ->where('c.Score is not null')
                    ->orderBy('c.Score', 'DESC');
                break;
            default:
            case 'Discussion':
                $sql = Gdn::sql()
                    ->select('d.Score, d.DiscussionID, d.InsertUserID, d.CategoryID, d.DateInserted')
                    ->from('Discussion d')
                    ->where('d.Score is not null')
                    ->orderBy('d.Score', 'DESC');
                break;
        }
        return $sql;
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
    public function get($userID, $actionID, $limit = null, $offset = 0) {
        $cacheKey = "yaga.profile.reactions.{$userID}.{$actionID}";
        $content = Gdn::cache()->get($cacheKey);

        if ($content == Gdn_Cache::CACHEOP_FAILURE) {

            // Get matching Discussions
            $discussions = $this->baseSQL('Discussion')
                ->join('Reaction r', 'd.DiscussionID = r.ParentID')
                ->where('d.InsertUserID', $userID)
                ->where('r.ActionID', $actionID)
                ->where('r.ParentType', 'discussion')
                ->orderBy('r.DateInserted', 'DESC')
                ->get()->resultArray();

            // Get matching Comments
            $comments = $this->baseSQL('Comment')
                ->join('Reaction r', 'c.CommentID = r.ParentID')
                ->where('c.InsertUserID', $userID)
                ->where('r.ActionID', $actionID)
                ->where('r.ParentType', 'comment')
                ->orderBy('r.DateInserted', 'DESC')
                ->get()->resultArray();

            $this->joinCategory($comments);

            $this->EventArguments['UserID'] = $userID;
            $this->EventArguments['ActionID'] = $actionID;
            $this->EventArguments['CustomSections'] = [];
            $this->fireEvent('GetCustom');

            // Interleave
            $content = $this->union('DateInserted', array_merge([
                'Discussion' => $discussions,
                'Comment' => $comments
            ], $this->EventArguments['CustomSections']));

            // Add result to cache
            Gdn::cache()->store($cacheKey, $content, [
                Gdn_Cache::FEATURE_EXPIRY => $this->_Expiry
            ]);
        }

        $this->security($content);
        $this->condenseAndPrep($content, $limit, $offset);
        $this->prepare($content->Content);

        return $content;
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
        $cacheKey = "yaga.profile.actions.{$userID}.{$actionID}";
        $content = Gdn::cache()->get($cacheKey);

        if ($content == Gdn_Cache::CACHEOP_FAILURE) {

            // Get matching Discussions
            $discussions = $this->baseSQL('Discussion')
                ->join('Reaction r', 'd.DiscussionID = r.ParentID')
                ->where('r.InsertUserID', $userID)
                ->where('r.ActionID', $actionID)
                ->where('r.ParentType', 'discussion')
                ->orderBy('r.DateInserted', 'DESC')
                ->get()->resultArray();

            // Get matching Comments
            $comments = $this->baseSQL('Comment')
                ->join('Reaction r', 'c.CommentID = r.ParentID')
                ->where('r.InsertUserID', $userID)
                ->where('r.ActionID', $actionID)
                ->where('r.ParentType', 'comment')
                ->orderBy('r.DateInserted', 'DESC')
                ->get()->resultArray();

            $this->joinCategory($comments);

            $this->EventArguments['UserID'] = $userID;
            $this->EventArguments['ActionID'] = $actionID;
            $this->EventArguments['CustomSections'] = [];
            $this->fireEvent('GetCustomTaken');

            // Interleave
            $content = $this->union('DateInserted', array_merge([
                'Discussion' => $discussions,
                'Comment' => $comments
            ], $this->EventArguments['CustomSections']));

            // Add result to cache
            Gdn::cache()->store($cacheKey, $content, [
                Gdn_Cache::FEATURE_EXPIRY => $this->_Expiry
            ]);
        }

        $this->security($content);
        $this->condenseAndPrep($content, $limit, $offset);
        $this->prepare($content->Content);

        return $content;
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
        $cacheKey = "yaga.best.actions.{$actionID}";
        $content = Gdn::cache()->get($cacheKey);

        if ($content == Gdn_Cache::CACHEOP_FAILURE) {

            // Get matching Discussions
            $discussions = $this->baseSQL('Discussion')
                ->join('Reaction r', 'd.DiscussionID = r.ParentID')
                ->where('r.ActionID', $actionID)
                ->where('r.ParentType', 'discussion')
                ->orderBy('r.DateInserted', 'DESC')
                ->get()->resultArray();

            // Get matching Comments
            $comments = $this->baseSQL('Comment')
                ->join('Reaction r', 'c.CommentID = r.ParentID')
                ->where('r.ActionID', $actionID)
                ->where('r.ParentType', 'comment')
                ->orderBy('r.DateInserted', 'DESC')
                ->get()->resultArray();

            $this->joinCategory($comments);

            $this->EventArguments['ActionID'] = $actionID;
            $this->EventArguments['CustomSections'] = [];
            $this->fireEvent('GetCustomAction');

            // Interleave
            $content = $this->union('DateInserted', array_merge([
                'Discussion' => $discussions,
                'Comment' => $comments
            ], $this->EventArguments['CustomSections']));

            // Add result to cache
            Gdn::cache()->store($cacheKey, $content, [
                Gdn_Cache::FEATURE_EXPIRY => $this->_Expiry
            ]);
        }

        $this->security($content);
        $this->condenseAndPrep($content, $limit, $offset);
        $this->prepare($content->Content);

        return $content;
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
        $cacheKey = "yaga.profile.best.{$userID}";
        $content = Gdn::cache()->get($cacheKey);

        if ($content == Gdn_Cache::CACHEOP_FAILURE) {
            $sql = $this->baseSQL('Discussion');
            if (!is_null($userID)) {
                $sql = $sql->where('d.InsertUserID', $userID);
            }
            $discussions = $sql->get()->resultArray();

            $sql = $this->baseSQL('Comment');
            if (!is_null($userID)) {
                $sql = $sql->where('c.InsertUserID', $userID);
            }
            $comments = $sql->get()->resultArray();

            $this->joinCategory($comments);

            $this->EventArguments['UserID'] = $userID;
            $this->EventArguments['CustomSections'] = [];
            $this->fireEvent('GetCustomBest');

            // Interleave
            $content = $this->union('Score', array_merge([
                'Discussion' => $discussions,
                'Comment' => $comments
            ], $this->EventArguments['CustomSections']));

            Gdn::cache()->store($cacheKey, $content, [
                Gdn_Cache::FEATURE_EXPIRY => $this->_Expiry
            ]);
        }

        $this->security($content);
        $this->condenseAndPrep($content, $limit, $offset);
        $this->prepare($content->Content);

        return $content;
    }

    /**
     * Returns a list of all recent scored posts ordered by date reacted
     *
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getRecent($limit = null, $offset = 0) {
        $cacheKey = 'yaga.best.recent';
        $content = Gdn::cache()->get($cacheKey);

        if ($content == Gdn_Cache::CACHEOP_FAILURE) {

            $discussions = Gdn::sql()->select('d.DiscussionID, d.InsertUserID, d.CategoryID, r.DateInserted as ReactionDate')
                ->from('Reaction r')
                ->where('ParentType', 'discussion')
                ->join('Discussion d', 'r.ParentID = d.DiscussionID')
                ->orderBy('r.DateInserted', 'DESC')
                ->get()
                ->resultArray();

            $comments = Gdn::sql()->select('c.CommentID, c.InsertUserID, c.DiscussionID, r.DateInserted as ReactionDate')
                ->from('Reaction r')
                ->where('ParentType', 'comment')
                ->join('Comment c', 'r.ParentID = c.CommentID')
                ->orderBy('r.DateInserted', 'DESC')
                ->get()
                ->resultArray();

            $this->joinCategory($comments);

            $this->EventArguments['CustomSections'] = [];
            $this->fireEvent('GetCustomRecent');

            // Interleave
            $content = $this->union('ReactionDate', array_merge([
                'Discussion' => $discussions,
                'Comment' => $comments
            ], $this->EventArguments['CustomSections']));

            Gdn::cache()->store($cacheKey, $content, [
                Gdn_Cache::FEATURE_EXPIRY => $this->_Expiry
            ]);
        }

        $this->security($content);
        $this->condenseAndPrep($content, $limit, $offset);
        $this->prepare($content->Content);

        return $content;
    }

    /**
     * Attach CategoryID to Comments
     *
     * @param array $comments
     */
    protected function joinCategory(&$comments) {
        $discussionIDs = [];

        foreach ($comments as &$comment) {
            $discussionIDs[$comment['DiscussionID']] = true;
        }
        $discussionIDs = array_keys($discussionIDs);

        $discussions = Gdn::sql()->select('d.DiscussionID, d.CategoryID, d.Name')
            ->from('Discussion d')
            ->whereIn('DiscussionID', $discussionIDs)
            ->get()->resultArray();

        $discussionsByID = [];
        foreach ($discussions as $discussion) {
            $discussionsByID[$discussion['DiscussionID']] = $discussion;
        }
        unset($discussions);

        foreach ($comments as &$comment) {
            $comment['Discussion'] = $discussionsByID[$comment['DiscussionID']];
            $comment['CategoryID'] = getValueR('Discussion.CategoryID', $comment);
        }
    }

    /**
     * Interleave two or more result arrays by a common field
     *
     * @param string $field
     * @param array $sections Array of result arrays
     * @return array
     */
    protected function union($field, $sections) {
        if (!is_array($sections))
            return;

        $interleaved = [];
        foreach ($sections as $sectionType => $section) {
            if (!is_array($section))
                continue;

            foreach ($section as $item) {
                $interleaved[$item[$field]] = array_merge($item, ['ItemType' => $sectionType]);
            }
        }

        ksort($interleaved);
        $interleaved = array_reverse($interleaved);
        return $interleaved;
    }

    /**
     * Pre-process content into a uniform format for output
     *
     * @param Array $content By reference
     */
    protected function prepare(&$content) {

        foreach ($content as &$contentItem) {
            $contentType = strtolower($contentItem['ItemType']);
            $itemID = $contentItem[ucfirst($contentType).'ID'];

            $contentItem = array_merge($contentItem, $this->getRecord($contentType, $itemID));

            $replacement = [];
            $fields = ['DiscussionID', 'CategoryID', 'DateInserted', 'DateUpdated', 'InsertUserID', 'Body', 'Format', 'ItemType', 'ContentURL'];

            if ($contentType == 'comment' || $contentType == 'discussion') {
                switch($contentType) {
                    case 'comment':
                        $fields = array_merge($fields, ['CommentID']);

                        // Comment specific
                        $replacement['Name'] = getValueR('Discussion.Name', $contentItem);
                        $contentItem['ContentURL'] = commentUrl($contentItem);
                        break;

                    case 'discussion':
                        $fields = array_merge($fields, ['Name', 'Type']);
                        $contentItem['ContentURL'] = discussionUrl($contentItem);
                        break;
                }

                $fields = array_fill_keys($fields, true);
                $common = array_intersect_key($contentItem, $fields);
                $replacement = array_merge($replacement, $common);
                $contentItem = $replacement;
            }

            // Attach User
            $userID = $contentItem['InsertUserID'] ?? false;
            $user = Gdn::userModel()->getID($userID);
            $contentItem['Author'] = $user;
        }
    }

    /**
     * Strip out content that this user is not allowed to see
     *
     * @param array $content Content array, by reference
     */
    protected function security(&$content) {
        if (!is_array($content))
            return;
        $content = array_filter($content, [$this, 'SecurityFilter']);
    }

    /**
     * Checks the view permission on an item
     *
     * @param array $contentItem
     * @return boolean Whether or not the user can see the content item
     */
    protected function securityFilter($contentItem) {
        $categoryID = $contentItem['CategoryID'] ?? null;
        if (is_null($categoryID) || $categoryID === false) {
            return false;
        }

        $category = CategoryModel::categories($categoryID);
        $canView = $category['PermsDiscussionsView'] ?? false;
        if (!$canView) {
            return false;
        }

        return true;
    }

    /**
     * Condense an interleaved content list down to the required size
     *
     * @param array $content
     * @param int $limit
     * @param int $offset
     */
    protected function condenseAndPrep(&$content, $limit, $offset) {
        $content = (object) ['TotalRecords' => count($content), 'Content' => array_slice($content, $offset, $limit)];
    }

    /**
     * Wrapper for getRecord()
     *
     * @since 1.1
     * @param string $recordType
     * @param int $iD
     * @return array
     */
    protected function getRecord($recordType, $iD) {
        if (in_array($recordType, ['discussion', 'comment', 'activity'])) {
            return getRecord($recordType, $iD);
        } else {
            $this->EventArguments['Type'] = $recordType;
            $this->EventArguments['ID'] = $iD;
            $this->EventArguments['Record'] = false;
            $this->fireEvent('GetCustomRecord');
            return $this->EventArguments['Record'];
        }
    }

}
