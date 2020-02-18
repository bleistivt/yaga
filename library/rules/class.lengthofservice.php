<?php if (!defined('APPLICATION')) exit();

/**
 * This rule awards badges based on a user's join date
 *
 * @author Zachary Doll
 * @since 1.0
 * @package Yaga
 */
class LengthOfService implements YagaRule {

    public function award($sender, $user, $criteria) {
        $insertDate = strtotime($user->DateInserted);
        $targetDate = strtotime($criteria->Duration.' '.$criteria->Period.' ago');
        if ($insertDate < $targetDate) {
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

        $string = $form->label('Yaga.Rules.LengthOfService.Criteria.Head', 'LengthOfService');
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
        return ['gdn_dispatcher_appStartup'];
    }

    public function description() {
        $description = Gdn::translate('Yaga.Rules.LengthOfService.Desc');
        return wrap($description, 'div', ['class' => 'InfoMessage']);
    }

    public function name() {
        return Gdn::translate('Yaga.Rules.LengthOfService');
    }

    public function interacts() {
        return false;
    }
}
