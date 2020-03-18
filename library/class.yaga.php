<?php if (!defined('APPLICATION')) exit();

/* Copyright 2013 Zachary Doll */

/**
 * This contains static functions to get models and objects related to Yaga
 * 
 * @package Yaga
 * @since 1.0
 * @deprecated
 */
class Yaga {

    /**
     * Get a reference to the acted model
     * @since 1.1
     * @return ActedModel
     */
    public static function actedModel() {
        deprecated(__FUNCTION__, 'Gdn::getContainer');
        return Gdn::getContainer()->get(ActedModel::class);
    }

    /**
     * Get a reference to the action model
     * @since 1.0
     * @return ActionModel
     */
    public static function actionModel() {
        deprecated(__FUNCTION__, 'Gdn::getContainer');
        return Gdn::getContainer()->get(ActionModel::class);
    }

    /**
     * Get a reference to the reaction model
     * @since 1.0
     * @return ReactionModel
     */
    public static function reactionModel() {
        deprecated(__FUNCTION__, 'Gdn::getContainer');
        return Gdn::getContainer()->get(ReactionModel::class);
    }

    /**
     * Get a reference to the badge model
     * @since 1.0
     * @return BadgeModel
     */
    public static function badgeModel() {
        deprecated(__FUNCTION__, 'Gdn::getContainer');
        return Gdn::getContainer()->get(BadgeModel::class);
    }

     /**
     * Get a reference to the badge award model
     * @since 1.0
     * @return BadgeAwardModel
     */
    public static function badgeAwardModel() {
        deprecated(__FUNCTION__, 'Gdn::getContainer');
        return Gdn::getContainer()->get(BadgeAwardModel::class);
    }

    /**
     * Get a reference to the rank model
     * @since 1.0
     * @return RankModel
     */
    public static function rankModel() {
        deprecated(__FUNCTION__, 'Gdn::getContainer');
        return Gdn::getContainer()->get(RankModel::class);
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
        deprecated(__FUNCTION__, 'UserModel::givePoints');
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
        deprecated(__FUNCTION__, 'YagaPlugin::executeBadgeHooks');
        YagaPlugin::executeBadgeHooks($sender, $handler);
    }
}
