<?php if (!defined('APPLICATION')) exit();

/**
 * This rule awards badges based on a user's badge awards.
 *
 * Meta, I know
 *
 * @author Zachary Doll
 * @since 1.0
 * @package Yaga
 */
class AwardCombo implements YagaRule {

    public function award($sender, $user, $criteria) {
        $userID = $sender->EventArguments['UserID'];
        $target = $criteria->Target;

        $badgeAwardModel = Yaga::badgeAwardModel();
        $targetDate = strtotime($criteria->Duration.' '.$criteria->Period.' ago');
        $badges = $badgeAwardModel->getByUser($userID);

        $types = [];
        foreach ($badges as $badge) {
            if (strtotime($badge['DateInserted']) >= $targetDate) {
                $types[$badge['RuleClass']] = true;
            }
        }

        if (count($types) >= $target) {
            return $userID;
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

        $string = $form->label('Yaga.Rules.AwardCombo.Criteria.Head', 'AwardCombo');
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
        return ['badgeAwardModel_afterBadgeAward'];
    }

    public function description() {
        $description = Gdn::translate('Yaga.Rules.AwardCombo.Desc');
        return wrap($description, 'div', ['class' => 'InfoMessage']);
    }

    public function name() {
        return Gdn::translate('Yaga.Rules.AwardCombo');
    }

    public function interacts() {
        return true;
    }
}
