<?php if (!defined('APPLICATION')) exit();

/**
 * This rule awards badges if a discussion is posted in the right category
 *
 * @author Jan Hoos
 * @since 1.0
 * @package Yaga
 */
class DiscussionCategory implements YagaRule {

    public function award($sender, $user, $criteria) {
        $discussion = $sender->EventArguments['Discussion'];
        $id = ($discussion->CategoryID);
        if ($id == $criteria->CategoryID) {
            return $discussion->InsertUserID;
        } else {
            return false;
        }
    }

    public function form($form) {
        $string = $form->label('Yaga.Rules.DiscussionCategory.Criteria.Head', 'DiscussionCategory');
        $string .= $form->categoryDropDown('CategoryID');
        return $string;
    }

    public function validate($criteria, $form) {
        $validation = new Gdn_Validation();
        $validation->applyRule('CategoryID', ['Required', 'Integer']);
        $validation->validate($criteria);
        $form->setValidationResults($validation->results());
    }

    public function hooks() {
        return ['discussionModel_afterSaveDiscussion'];
    }

    public function description() {
        return wrap(Gdn::translate('Yaga.Rules.DiscussionCategory.Desc'), 'div', ['class' => 'alert alert-info padded']);
    }

    public function name() {
        return Gdn::translate('Yaga.Rules.DiscussionCategory');
    }

    public function interacts() {
        return false;
    }

}
