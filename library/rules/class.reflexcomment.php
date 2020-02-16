<?php if (!defined('APPLICATION')) exit();

/**
 * This rule awards badges if a comment is placed on a discussion within a short amount of time
 *
 * @author Zachary Doll
 * @since 1.0
 * @package Yaga
 */
class ReflexComment implements YagaRule {

    public function award($sender, $user, $criteria) {
        $discussion = $sender->EventArguments['Discussion'];
	$comment = $sender->EventArguments['Comment'];

	// Don't award a user for commenting on their own discussion
        if ($discussion->InsertUserID == $user->UserID) {
	    return false;
	}
	$discussionDate = strtotime($discussion->DateInserted);
        $commentDate = strtotime($comment['DateInserted']);

        $difference = $commentDate - $discussionDate;

        if ($difference <= $criteria->Seconds) {
            return $user->UserID;
        } else {
            return false;
        }
    }

    public function form($form) {
        $string = $form->label('Yaga.Rules.ReflexComment.Criteria.Head', 'ReflexComment');
        $string .= $form->textbox('Seconds', ['class' => 'SmallInput']);

        return $string;
    }

    public function validate($criteria, $form) {
        $validation = new Gdn_Validation();
        $validation->applyRule('Seconds', ['Required', 'Integer']);
        $validation->validate($criteria);
        $form->setValidationResults($validation->results());
    }

    public function hooks() {
        return ['commentModel_beforeNotification'];
    }

    public function description() {
        $description = Gdn::translate('Yaga.Rules.ReflexComment.Desc');
        return wrap($description, 'div', ['class' => 'InfoMessage']);
    }

    public function name() {
        return Gdn::translate('Yaga.Rules.ReflexComment');
    }

    public function interacts() {
        return false;
    }
}
