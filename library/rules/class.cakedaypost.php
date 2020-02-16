<?php if (!defined('APPLICATION')) exit();

use Yaga;

/**
 * This rule awards badges if the user posts on the anniversary of their account
 * creation.
 *
 * @author Zachary Doll
 * @since 1.0
 * @package Yaga
 */
class CakeDayPost implements YagaRule {

    public function award($sender, $user, $criteria) {
        // Determine if today is the target day
        $cakeDate = strtotime($user->DateInserted);

        $cakeYear = date('Y', $cakeDate);
        $cakeMonth = date('n', $cakeDate);
        $cakeDay = date('j', $cakeDate);
        $todaysYear = date('Y');
        $todaysMonth = date('n');
        $todaysDay = date('j');

        if ($cakeMonth == $todaysMonth
                        && $cakeDay == $todaysDay
                        && $cakeYear != $todaysYear) {
            return true;
        } else {
            return false;
        }
    }

    public function form($form) {
        return '';
    }

    public function validate($criteria, $form) {
        return;
    }

    public function hooks() {
        return ['discussionModel_afterSaveDiscussion', 'commentModel_afterSaveComment', 'activityModel_beforeSaveComment'];
    }

    public function description() {
        $description = Gdn::translate('Yaga.Rules.CakeDayPost.Desc');
        return wrap($description, 'div', ['class' => 'InfoMessage']);
    }

    public function name() {
        return Gdn::translate('Yaga.Rules.CakeDayPost');
    }

    public function interacts() {
        return false;
    }
}
