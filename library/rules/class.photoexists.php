<?php if (!defined('APPLICATION')) exit();

use Yaga;

/**
 * This rule awards badges if the user has a profile photo
 *
 * @author Zachary Doll
 * @since 1.0
 * @package Yaga
 */
class PhotoExists implements YagaRule {

    public function award($sender, $user, $criteria) {
        if ($user->Photo) {
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
        return ['gdn_dispatcher_appStartup'];
    }

    public function description() {
        $description = Gdn::translate('Yaga.Rules.PhotoExists.Desc');
        return wrap($description, 'div', ['class' => 'InfoMessage']);
    }

    public function name() {
        return Gdn::translate('Yaga.Rules.PhotoExists');
    }

    public function interacts() {
        return false;
    }
}
