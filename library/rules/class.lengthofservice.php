<?php if (!defined('APPLICATION')) exit();

use Yaga;

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

        $string = $form->label('Yaga.Rules.LengthOfService.Criteria.Head', 'LengthOfService');
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
        return ['gdn_dispatcher_appStartup'];
    }

    public function description() {
        $description = t('Yaga.Rules.LengthOfService.Desc');
        return wrap($description, 'div', ['class' => 'InfoMessage']);
    }

    public function name() {
        return t('Yaga.Rules.LengthOfService');
    }

    public function interacts() {
        return false;
    }
}
