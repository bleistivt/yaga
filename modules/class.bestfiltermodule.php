<?php if (!defined('APPLICATION')) exit();

/* Copyright 2014 Zachary Doll */

/**
 * This shows the different filters you can apply to the entire forums scored content
 *
 * @package Yaga
 * @since 1.0
 */
class BestFilterModule extends Gdn_Module {

    /**
     * Load up the action list.
     * 
     * @param string $sender
     */
    public function __construct($sender = '') {
        parent::__construct($sender);

        if ($sender) {
            $actions = $sender->ActionModel->get();

            foreach ($actions as $index => $action) {
                if ($action->AwardValue < 0) {
                    unset($actions[$index]);
                }
            }

            $this->Data = $actions;
        }
    }

    /**
     * Specifies the asset this module should be rendered to.
     * 
     * @return string
     */
    public function assetTarget() {
        return 'Content';
    }

    /**
     * Renders an action list.
     * 
     * @return string
     */
    public function toString() {
        return parent::toString();
    }

}
