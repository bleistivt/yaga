<?php if (!defined('APPLICATION')) exit();

/**
 * This rule awards badges when a user mentions another user in a discussion,
 * comment, or activity
 *
 * @author Zachary Doll
 * @since 1.0
 * @package Yaga
 */
class HasMentioned implements YagaRule {

    public function award($sender, $user, $criteria) {
        return count($sender->EventArguments['MentionedUsers']) > 0;
    }

    public function form($form) {
        return '';
    }

    public function validate($criteria, $form) {
        return;
    }

    public function hooks() {
        return ['commentModel_beforeNotification', 'discussionModel_beforeNotification'];
    }

    public function description() {
        $description = Gdn::translate('Yaga.Rules.HasMentioned.Desc');
        return wrap($description, 'div', ['class' => 'alert alert-info padded']);
    }

    public function name() {
        return Gdn::translate('Yaga.Rules.HasMentioned');
    }

    public function interacts() {
        return false;
    }
}
