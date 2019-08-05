<?php if (!defined('APPLICATION')) exit();

use Yaga;

/* Copyright 2013 Zachary Doll */

/**
 * This contains static functions to get models and objects related to Yaga
 * 
 * @package Yaga
 * @since 1.0
 */
class Yaga {

    /**
     * A single copy of ActedModel available to plugins and hooks files.
     * 
     * @since 1.1
     * @var ActedModel
     */
    protected static $_actedModel = null;

    /**
     * A single copy of ActionModel available to plugins and hooks files.
     * 
     * @var ActionModel
     */
    protected static $_actionModel = null;

    /**
     * A single copy of ReactionModel available to plugins and hooks files.
     * 
     * @var ReactionModel
     */
    protected static $_reactionModel = null;

    /**
     * A single copy of BadgeModel available to plugins and hooks files.
     * 
     * @var BadgeModel
     */
    protected static $_badgeModel = null;

    /**
     * A single copy of RankModel available to plugins and hooks files.
     * 
     * @var RankModel
     */
    protected static $_rankModel = null;

    /**
     * A single copy of BadgeAwardModel available to plugins and hooks files.
     * 
     * @var BadgeAwardModel
     */
    protected static $_badgeAwardModel = null;

    /**
     * Get a reference to the acted model
     * @since 1.1
     * @return ActedModel
     */
    public static function actedModel() {
            if (is_null(self::$_actedModel)) {
                 self::$_actedModel = new ActedModel();
            }
            return self::$_actedModel;
     }

    /**
     * Get a reference to the action model
     * @since 1.0
     * @return ActionModel
     */
    public static function actionModel() {
            if (is_null(self::$_actionModel)) {
                 self::$_actionModel = new ActionModel();
            }
            return self::$_actionModel;
     }

    /**
     * Get a reference to the reaction model
     * @since 1.0
     * @return ReactionModel
     */
    public static function reactionModel() {
            if (is_null(self::$_reactionModel)) {
                 self::$_reactionModel = new ReactionModel();
            }
            return self::$_reactionModel;
     }

    /**
     * Get a reference to the badge model
     * @since 1.0
     * @return BadgeModel
     */
    public static function badgeModel() {
            if (is_null(self::$_badgeModel)) {
                 self::$_badgeModel = new BadgeModel();
            }
            return self::$_badgeModel;
     }

     /**
     * Get a reference to the badge award model
     * @since 1.0
     * @return BadgeAwardModel
     */
    public static function badgeAwardModel() {
            if (is_null(self::$_badgeAwardModel)) {
                 self::$_badgeAwardModel = new BadgeAwardModel();
            }
            return self::$_badgeAwardModel;
     }

    /**
     * Get a reference to the rank model
     * @since 1.0
     * @return RankModel
     */
    public static function rankModel() {
            if (is_null(self::$_rankModel)) {
                 self::$_rankModel = new RankModel();
            }
            return self::$_rankModel;
     }

     /**
        * Alias for UserModel::givePoints()
        * 
        * May be expanded in future versions.
        * 
        * @since 1.1
        * @param int $userID
        * @param int $value
        * @param string $source
        * @param int $timestamp
        */
     public static function givePoints($userID, $value, $source = 'Other', $timestamp = false) {
         if ($userID == Gdn::userModel()->getSystemUserID()) {
                 return;
         }
         UserModel::givePoints($userID, $value, $source, $timestamp);
     }

     /**
     * This is the dispatcher to check badge awards
     *
     * @param mixed $sender The sending object
     * @param string $handler The event handler to check associated rules for awards
     * (e.g. BadgeAwardModel_AfterBadgeAward_Handler or Base_AfterConnection)
     * @since 1.1
     */
     public static function executeBadgeHooks($sender, $handler) {
        $session = Gdn::session();
        if (!c('Yaga.Badges.Enabled') || !$session->isValid()) {
            return;
        }

        // Let's us use __FUNCTION__ in the original hook
        $hook = strtolower(str_ireplace('_Handler', '', $handler));

        $userID = $session->UserID;
        $user = $session->User;

        $badgeAwardModel = Yaga::badgeAwardModel();
        $badges = $badgeAwardModel->getUnobtained($userID);

        $interactionRules = RulesController::getInteractionRules();

        $rules = [];
        foreach ($badges as $badge) {
            // The badge award needs to be processed
            if (($badge->Enabled && $badge->UserID != $userID)
                 || array_key_exists($badge->RuleClass, $interactionRules)) {
                // Create a rule object if needed
                $class = $badge->RuleClass;
                if (!in_array($class, $rules) && class_exists($class)) {
                    $rule = new $class();
                    $rules[$class] = $rule;
                }
                else {
                    if (!array_key_exists('UnknownRule', $rules)) {
                        $rules['UnkownRule'] = new UnknownRule();
                    }
                    $rules[$class] = $rules['UnkownRule'];
                }

                $rule = $rules[$class];

                // Only check awards for rules that use this hook
                $hooks = array_map('strtolower',$rule->hooks());
                if (in_array($hook, $hooks)) {
                    $criteria = (object) unserialize($badge->RuleCriteria);
                    $result = $rule->award($sender, $user, $criteria);
                    if ($result) {
                        $awardedUserIDs = [];
                        if (is_array($result)) {
                            $awardedUserIDs = $result;
                        }
                        else if (is_numeric($result)) {
                            $awardedUserIDs[] = $result;
                        }
                        else {
                            $awardedUserIDs[] = $userID;
                        }

                        $systemUserID = Gdn::userModel()->getSystemUserID();
                        foreach ($awardedUserIDs as $awardedUserID) {
                            if ($awardedUserID == $systemUserID) {
                                    continue;
                            }
                            $badgeAwardModel->award($badge->BadgeID, $awardedUserID, $userID);
                        }
                    }
                }
            }
        }
    }
}
