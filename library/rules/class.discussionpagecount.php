<?php if (!defined('APPLICATION')) exit();

/**
 * This rule awards badges if a discussion reaches the target number of pages
 *
 * @author Zachary Doll
 * @since 1.0
 * @package Yaga
 */
class DiscussionPageCount implements YagaRule {

    public function award($sender, $user, $criteria) {
        $discussion = $sender->EventArguments['Discussion'];
        $commentCount = $discussion->CountComments;
        $pageSize = Gdn::config('Vanilla.Comments.PerPage');

        $pageCount = floor($commentCount / $pageSize);

        if ($pageCount >= $criteria->Pages) {
            return $discussion->InsertUserID;
        } else {
            return false;
        }
    }

    public function form($form) {
        $string = $form->label('Yaga.Rules.DiscussionPageCount.Criteria.Head', 'DiscussionPageCount');
        $string .= $form->textbox('Pages');
        return $string;
    }

    public function validate($criteria, $form) {
        $validation = new Gdn_Validation();
        $validation->applyRule('Pages', ['Required', 'Integer']);
        $validation->validate($criteria);
        $form->setValidationResults($validation->results());
    }

    public function hooks() {
        return ['commentModel_beforeNotification'];
    }

    public function description() {
        $description = Gdn::translate('Yaga.Rules.DiscussionPageCount.Desc');
        return wrap($description, 'div', ['class' => 'alert alert-info padded']);
    }

    public function name() {
        return Gdn::translate('Yaga.Rules.DiscussionPageCount');
    }

    public function interacts() {
        return false;
    }
}
