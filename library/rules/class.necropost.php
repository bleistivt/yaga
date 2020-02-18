<?php if (!defined('APPLICATION')) exit();

/**
 * This rule awards badges when a user comments on a dead discussion.
 *
 * @author Zachary Doll
 * @since 0.3.4
 * @package Yaga
 */
class NecroPost implements YagaRule {

    public function award($sender, $user, $criteria) {
        $necroDate = strtotime($criteria->Duration.' '.$criteria->Period.' ago');

        // Get the last comment date from the parent discussion
        $args = $sender->EventArguments;
        $discussionID = $args['FormPostValues']['DiscussionID'];
        $discussionModel = new DiscussionModel();
        $discussion = $discussionModel->getID($discussionID);
        $lastCommentDate = strtotime($discussion->DateLastComment);

        if ($discussion->DateLastComment && $lastCommentDate < $necroDate) {
            return true;
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

        $string = $form->label('Yaga.Rules.NecroPost.Criteria.Head', 'NecroPost');
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
        return ['commentModel_afterSaveComment'];
    }

    public function description() {
        $description = Gdn::translate('Yaga.Rules.NecroPost.Desc');
        return wrap($description, 'div', ['class' => 'InfoMessage']);
    }

    public function name() {
        return Gdn::translate('Yaga.Rules.NecroPost');
    }

    public function interacts() {
        return false;
    }
}
