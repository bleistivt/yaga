<?php if (!defined('APPLICATION')) exit();

use Yaga;

/**
 * This rule awards badges based on a user's comment count withing a specified time frame.
 *
 * @author Zachary Doll
 * @since 1.0
 * @package Yaga
 */
class CommentMarathon implements YagaRule {

    public function award($sender, $user, $criteria) {
        $target = $criteria->Target;
        $targetDate = Gdn_Format::toDateTime(strtotime($criteria->Duration.' '.$criteria->Period.' ago'));

        $sql = Gdn::sql();
        $count = $sql->select('count(CommentID) as Count')
                 ->from('Comment')
                 ->where('InsertUserID', $user->UserID)
                 ->where('DateInserted >=', $targetDate)
                 ->get()
                        ->firstRow();

        if ($count->Count >= $target) {
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

        $string = $form->label('Yaga.Rules.CommentMarathon.Criteria.Head', 'CommentMarathon');
        $string .= $form->textbox('Target', ['class' => 'SmallInput']);
        $string .= $form->label('Time Frame');
        $string .= $form->textbox('Duration', ['class' => 'SmallInput']).' ';
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
        return wrap($description, 'div', ['class' => 'InfoMessage']);
    }

    public function name() {
        return Gdn::translate('Yaga.Rules.CommentMarathon');
    }

    public function interacts() {
        return false;
    }
}
