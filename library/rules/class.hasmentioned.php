<?php if (!defined('APPLICATION')) exit();

use Yaga;

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
        $hasMentioned    = count($sender->EventArguments['MentionedUsers']);
        if ($hasMentioned) {
            return true;
        } else {
            return false;
        }
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
        return wrap($description, 'div', ['class' => 'InfoMessage']);
    }

    public function name() {
        return Gdn::translate('Yaga.Rules.HasMentioned');
    }

    public function interacts() {
        return false;
    }
}
