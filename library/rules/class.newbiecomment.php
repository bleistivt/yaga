<?php if (!defined('APPLICATION')) exit();

use Yaga;

/**
 * This rule awards badges if a comment is placed on a new member's first discussion
 *
 * @author Zachary Doll
 * @since 1.0
 * @package Yaga
 */
class NewbieComment implements YagaRule{

    public function award($sender, $user, $criteria) {
        $discussion = $sender->EventArguments['Discussion'];
        $newbUserID = $discussion->InsertUserID;

        // Don't award to the newb on his own discussion
        if ($newbUserID == $user->UserID) {
            return false;
        }

        $currentDiscussionID = $discussion->DiscussionID;
        $targetDate = strtotime($criteria->Duration.' '.$criteria->Period.' ago');

        $sQL = Gdn::sql();
        $firstDiscussion = $sQL->select('DiscussionID, DateInserted')
            ->from('Discussion')
            ->where('InsertUserID', $newbUserID)
            ->orderBy('DateInserted')
            ->get()
            ->firstRow();

        $insertDate = strtotime($firstDiscussion->DateInserted);

        if ($currentDiscussionID == $firstDiscussion->DiscussionID
                        && $insertDate > $targetDate) {
            return $user->UserID;
        }
        else {
            return false;
        }
    }

    public function form($form) {
        $lengths = [
            'day' => t('Days'),
            'week' => t('Weeks'),
            'year' => t('Years')
        ];

        $string = $form->label('Yaga.Rules.NewbieComment.Criteria.Head', 'NewbieComment');
        $string .= $form->textbox('Duration', ['class' => 'SmallInput']).' ';
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
        $description = t('Yaga.Rules.NewbieComment.Desc');
        return wrap($description, 'div', ['class' => 'InfoMessage']);
    }

    public function name() {
        return t('Yaga.Rules.NewbieComment');
    }

    public function interacts() {
        return false;
    }
}
