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
     * Memory cache for getInteractionRules()
     * 
     * @var interactionRulesCache
     */
    private $_interactionRulesCache = null;

    /** @var EventManager */
    private $eventManager;

    /**
     * Defines the related database table name.
     */
    public function __construct(EventManager $eventManager) {
        parent::__construct('YagaBadge');
        $this->PrimaryKey = 'BadgeID';

        $this->eventManager = $eventManager;
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

    /**
     * This checks the cache for current rule set and expires once a day.
     * It loads all php files in the rules folder and selects only those that
     * implement the 'YagaRule' interface.
     *
     * @return array Rules that are currently available to use. The class names
     * are keys and the friendly names are values.
     */
    public function getRules() {
        $rules = Gdn::cache()->get('Yaga.Badges.Rules');

        // rule files must always be loaded
        foreach (glob(PATH_PLUGINS.'/yaga/library/rules/*.php') as $filename) {
            include_once $filename;
        }

        if ($rules === Gdn_Cache::CACHEOP_FAILURE) {
            $tempRules = [];
            foreach (get_declared_classes() as $className) {
                if (in_array('YagaRule', class_implements($className))) {
                    $rule = new $className();
                    $tempRules[$className] = $rule->name();
                }
            }

            $tempRules = $this->eventManager->fireFilter('yaga_getRules', $tempRules);

            $this->EventArguments['Rules'] = &$tempRules;
            $this->fireAs('Yaga')->fireEvent('AfterGetRules');

            asort($tempRules);
            $rules = dbencode(empty($tempRules) ? false : $tempRules);
            Gdn::cache()->store('Yaga.Badges.Rules', $rules, [Gdn_Cache::FEATURE_EXPIRY => Gdn::config('Yaga.Rules.CacheExpire', 86400)]);
        }

        return dbdecode($rules);
    }

    /**
     * Create a new rule object in a safe way.
     *
     * @return YagaRule
     */
    public function createRule($class) {
        if (class_exists($class)) {
            return new $class();
        } else {
            return new UnknownRule();
        }
    }

    /**
     * This checks the cache for current rule set that can be triggered for a user
     * by another user. It loads all rules and selects only those that return true
     * on its `Interacts()` method.
     *
     * @return array Rules that are currently available to use that are interactive.
     */
    public function getInteractionRules() {
        if ($this->_interactionRulesCache === null) {
            $rules = Gdn::cache()->get('Yaga.Badges.InteractionRules');
            if ($rules === Gdn_Cache::CACHEOP_FAILURE) {
                $allRules = $this->getRules();

                $tempRules = [];
                foreach ($allRules as $className => $name) {
                    $rule = new $className();
                    if ($rule->interacts()) {
                        $tempRules[$className] = $name;
                    }
                }
                $rules = dbencode(empty($tempRules) ? false : $tempRules);
                Gdn::cache()->store('Yaga.Badges.InteractionRules', $rules, [Gdn_Cache::FEATURE_EXPIRY => Gdn::config('Yaga.Rules.CacheExpire', 86400)]);
            }

            $this->_interactionRulesCache = dbdecode($rules);
        }
        
        return $this->_interactionRulesCache;
    }

}
