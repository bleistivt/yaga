<?php if (!defined('APPLICATION')) exit();

/**
 * This rule is selected if the rule class saved in the database is no longer
 * available. It is functionally equivalent to Manual Award.
 *
 * @author Zachary Doll
 * @since 1.1
 * @package Yaga
 */
class UnknownRule implements YagaRule {

    public function award($sender, $user, $criteria) {
        return false;
    }

    public function form($form) {
        return '';
    }

    public function validate($criteria, $form) {
        return;
    }

    public function hooks() {
        return [];
    }

    public function description() {
        $description = Gdn::translate('Yaga.Rules.UnknownRule.Desc');
        return wrap($description, 'div', ['class' => 'AlertMessage']);
    }

    public function name() {
        return Gdn::translate('Yaga.Rules.UnknownRule');
    }

    public function interacts() {
        return false;
    }
}
