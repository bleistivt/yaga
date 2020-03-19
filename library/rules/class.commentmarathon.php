<?php if (!defined('APPLICATION')) exit();

use \Vanilla\Formatting\DateTimeFormatter;

/**
 * This rule awards badges based on a user's comment count withing a specified time frame.
 *
 * @author Zachary Doll
 * @since 1.0
 * @package Yaga
 */
class CommentMarathon implements YagaRule {

    public function award($sender, $user, $criteria) {
        //$targetDate = DateTimeFormatter::timeStampToDateTime((int)strtotime($criteria->Duration.' '.$criteria->Period.' ago'));
        $targetDate = Gdn_Format::toDateTime(strtotime($criteria->Duration.' '.$criteria->Period.' ago'));

        $count = Gdn::sql()
            ->select('count(CommentID) as Count')
            ->from('Comment')
            ->where('InsertUserID', $user->UserID)
            ->where('DateInserted >=', $targetDate)
            ->get()
            ->firstRow();

        return $count->Count >= $criteria->Target;
    }

    public function form($form) {
        $lengths = [
            'day' => Gdn::translate('Days'),
            'week' => Gdn::translate('Weeks'),
            'year' => Gdn::translate('Years')
        ];

        $string = $form->label('Yaga.Rules.CommentMarathon.Criteria.Head', 'CommentMarathon');
        $string .= $form->textbox('Target');
        $string .= $form->label('Time Frame');
        $string .= $form->textbox('Duration');
        $string .= $form->dropDown('Period', $lengths);

        return $string;
    }

    public function validate($criteria, $form) {
        $validation = new Gdn_Validation();
        $validation->applyRule('Target', ['Required', 'Integer']);
        $validation->applyRule('Duration', ['Required', 'Integer']);
        $validation->applyRule('Period', 'Required');
        $validation->validate($criteria);
        $form->setValidationResults($validation->results());
    }

    public function hooks() {
        return ['commentModel_afterSaveComment'];
    }

    public function description() {
        $description = Gdn::translate('Yaga.Rules.CommentMarathon.Desc');
        return wrap($description, 'div', ['class' => 'alert alert-info padded']);
    }

    public function name() {
        return Gdn::translate('Yaga.Rules.CommentMarathon');
    }

    public function interacts() {
        return false;
    }
}
