<?php if (!defined('APPLICATION')) exit();

/**
 * This rule awards badges if a comment is placed on a new member's first discussion
 *
 * @author Zachary Doll
 * @since 1.0
 * @package Yaga
 */
class NewbieComment implements YagaRule {

    public function award($sender, $user, $criteria) {
        $discussion = $sender->EventArguments['Discussion'];
        $newbUserID = $discussion->InsertUserID;

        // Don't award to the newb on his own discussion
        if ($newbUserID == $user->UserID) {
            return false;
        }

        $currentDiscussionID = $discussion->DiscussionID;
        $targetDate = strtotime($criteria->Duration.' '.$criteria->Period.' ago');

        $sql = Gdn::sql();
        $firstDiscussion = $sql->select('DiscussionID, DateInserted')
            ->from('Discussion')
            ->where('InsertUserID', $newbUserID)
            ->orderBy('DateInserted')
            ->get()
            ->firstRow();

        $insertDate = strtotime($firstDiscussion->DateInserted);

        if ($currentDiscussionID == $firstDiscussion->DiscussionID && $insertDate > $targetDate) {
            return $user->UserID;
        } else {
            return false;
        }
    }

    public function form($form) {
        $lengths = [
            'day' => Gdn::translate('Days'),
            'week' => Gdn::translate('Weeks'),
            'year' => Gdn::translate('Years')
        ];

        $string = $form->label('Yaga.Rules.NewbieComment.Criteria.Head', 'NewbieComment');
        $string .= $form->textbox('Duration');
        $string .= $form->dropDown('Period', $lengths);

        return $string;
    }

    public function validate($criteria, $form) {
        $validation = new Gdn_Validation();
        $validation->applyRule('Duration', ['Required', 'Integer']);
        $validation->applyRule('Period', 'Required');
        $validation->validate($criteria);
        $form->setValidationResults($validation->results());
    }

    public function hooks() {
        return ['commentModel_beforeNotification'];
    }

    public function description() {
        $description = Gdn::translate('Yaga.Rules.NewbieComment.Desc');
        return wrap($description, 'div', ['class' => 'alert alert-info padded']);
    }

    public function name() {
        return Gdn::translate('Yaga.Rules.NewbieComment');
    }

    public function interacts() {
        return false;
    }
}
