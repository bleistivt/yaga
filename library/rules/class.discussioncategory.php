<?php if (!defined('APPLICATION')) exit();

use Yaga;

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
        $iD = ($discussion->CategoryID);
        if ($iD == $criteria->CategoryID) {
            return $discussion->InsertUserID;
        }
        else {
            return false;
        }
    }

    public function form($form) {
        $string    = $form->label('Yaga.Rules.DiscussionCategory.Criteria.Head', 'DiscussionCategory');
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
        return wrap(t('Yaga.Rules.DiscussionCategory.Desc'), 'div', ['class' => 'InfoMessage']);
    }

    public function name() {
        return t('Yaga.Rules.DiscussionCategory');
    }

    public function interacts() {
        return false;
    }

}
