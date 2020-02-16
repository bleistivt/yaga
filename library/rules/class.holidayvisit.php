<?php if (!defined('APPLICATION')) exit();

use Yaga;

/**
 * This rule awards badges based on a user's sign in date
 *
 * @author Zachary Doll
 * @since 1.0
 * @package Yaga
 */
class HolidayVisit implements YagaRule {

    public function award($sender, $user, $criteria) {
        // Determine if today is the target day
        $month = date('n');
        $day = date('j');

        if ($criteria->Month == $month
                        && $criteria->Day == $day) {
            return true;
        } else {
            return false;
        }
    }

    public function form($form) {
        $months = [];
        $days = [];
        for ($i = 1; $i <= 12; $i++) {
            $months[$i] = date('F', mktime(0,0,0,$i));
        }
        for ($i = 1; $i <= 31; $i++) {
            $days[$i] = $i;
        }

        $string = $form->label('Yaga.Rules.HolidayVisit.Criteria.Head', 'HolidayVisit');
        $string .= $form->dropDown('Month', $months).' ';
        $string .= $form->dropDown('Day', $days);
        return $string;
    }

    public function validate($criteria, $form) {
        $validation = new Gdn_Validation();
        $validation->applyRule('Month', ['Required', 'Integer']);
        $validation->applyRule('Day', ['Required', 'Integer']);
        $validation->validate($criteria);
        $form->setValidationResults($validation->results());
    }

    public function hooks() {
        return ['gdn_dispatcher_appStartup'];
    }

    public function description() {
        $description = Gdn::translate('Yaga.Rules.HolidayVisit.Desc');
        return wrap($description, 'div', ['class' => 'InfoMessage']);
    }

    public function name() {
        return Gdn::translate('Yaga.Rules.HolidayVisit');
    }

    public function interacts() {
        return false;
    }
}
