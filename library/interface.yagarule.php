<?php if (!defined('APPLICATION')) exit();

use Yaga;

/**
 * Describes the functions required to create a new rule for badges in Yaga.
 *
 * @author Zachary Doll
 * @since 1.0
 * @package Yaga
 */
interface YagaRule {
    /**
     * This performs the grunt work of an award rule. Given an expected criteria,
     * it determines if a specific user meets muster.
     *
     * @since 1.0
     * @param mixed $sender The object calling the award method.
     * @param UserObject $user the user object of the calling user
     * @param stdClass $criteria This is a standard object with properties that
     * match the criteria that were previously rendered
     * @return int Represents the user that gets the award criteria. You may use
     * true as a shortcut to award the user that did the check. false will not
     * award any user
     */
    public function award($sender, $user, $criteria);

    /**
     * This determines what hook(s) the rule should be checked on.
     * 
     * @since 1.0
     * @return array The hook name(s) in lower case to fire our calculations on
     */
    public function hooks();

    /**
     * Returns the needed criteria form for this rule's criteria.
     *
     * @since 1.0
     * @param Gdn_Form $form
     * @return string The fully rendered form.
     */
    public function form($form);

    /**
     * This validates the submitted criteria and does what it wants with the form
     *
     * @since 1.0
     * @param array $criteria
     * @param Gdn_Form $form
     */
    public function validate($criteria, $form);

    /**
     * Returns a string representing a user friendly name of this rule.
     *
     * @since 1.0
     * @return string Name shown on forms
     */
    public function name();

    /**
     * Returns a string representing the in depth description of how to use this rule.
     *
     * @since 1.0
     * @return string The description
     */
    public function description();

    /**
     * Returns a bool representing whether the Award function can award a user
     * other than the calling user. Rules that depend on interaction should return 
     * true.
     * 
     * @since 1.0
     * @return bool Whether or not interactions need to be checked
     */
    public function interacts();
}
