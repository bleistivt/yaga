<?php if (!defined('APPLICATION')) exit();

Gdn::getContainer()
    ->rule(ActedModel::class)
        ->setShared(true)
    ->rule(ActionModel::class)
        ->setShared(true)
    ->rule(ReactionModel::class)
        ->setShared(true)
    ->rule(BadgeModel::class)
        ->setShared(true)
    ->rule(BadgeAwardModel::class)
        ->setShared(true)
    ->rule(RankModel::class)
        ->setShared(true);

require_once(PATH_PLUGINS.'/yaga/library/functions.render.php');
