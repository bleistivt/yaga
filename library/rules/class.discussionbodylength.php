<?php if (!defined('APPLICATION')) exit();

/**
 * This rule awards badges if a discussion body reaches the target length
 *
 * @author Zachary Doll
 * @since 1.0
 * @package Yaga
 */
class DiscussionBodyLength implements YagaRule {

    public function award($sender, $user, $criteria) {
        $discussion = $sender->EventArguments['Discussion'];
        $length = strlen($discussion->Body);

        if ($length >= $criteria->Length) {
            return $discussion->InsertUserID;
        } else {
            return false;
        }
    }

    public function form($form) {
        $string = $form->label('Yaga.Rules.DiscussionBodyLength.Criteria.Head', 'DiscussionBodyLength');
        $string .= $form->textbox('Length');
        return $string;
    }

    public function validate($criteria, $form) {
        $validation = new Gdn_Validation();
        $validation->applyRule('Length', ['Required', 'Integer']);
        $validation->validate($criteria);
        $form->setValidationResults($validation->results());
    }

    public function hooks() {
        return ['discussionModel_afterSaveDiscussion'];
    }

    public function description() {
        $description = sprintf(Gdn::translate('Yaga.Rules.DiscussionBodyLength.Desc'), Gdn::config('Vanilla.Comment.MaxLength'));
        return wrap($description, 'div', ['class' => 'alert alert-info padded']);
    }

    public function name() {
        return Gdn::translate('Yaga.Rules.DiscussionBodyLength');
    }

    public function interacts() {
        return false;
    }
}
