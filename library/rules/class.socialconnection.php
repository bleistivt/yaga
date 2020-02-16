<?php if (!defined('APPLICATION')) exit();

/**
 * This rule awards badges when the user connects social accounts
 *
 * @author Zachary Doll
 * @since 1.0
 * @package Yaga
 */
class SocialConnection implements YagaRule {

    public function award($sender, $user, $criteria) {
        $network = $sender->EventArguments['Provider'];

        if ($network == $criteria->SocialNetwork) {
            return true;
        } else {
            return false;
        }
    }

    public function form($form) {
        $socialNetworks = [
            'Twitter' => 'Twitter',
            'Facebook' => 'Facebook'
        ];

        $string = $form->label('Yaga.Rules.SocialConnection.Criteria.Head', 'SocialConnection');
        $string .= $form->dropDown('SocialNetwork', $socialNetworks);

        return $string;
    }

    public function validate($criteria, $form) {
        $validation = new Gdn_Validation();
        $validation->applyRule('SocialNetwork', 'Required');
        $validation->validate($criteria);
        $form->setValidationResults($validation->results());
    }

    public function hooks() {
        return ['base_afterConnection'];
    }

    public function description() {
        $description = Gdn::translate('Yaga.Rules.SocialConnection.Desc');
        return wrap($description, 'div', ['class' => 'InfoMessage']);
    }

    public function name() {
        return Gdn::translate('Yaga.Rules.SocialConnection');
    }

    public function interacts() {
        return false;
    }
}
