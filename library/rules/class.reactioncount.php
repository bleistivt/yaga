<?php if (!defined('APPLICATION')) exit();

/**
 * This rule awards badges based on a user's received reactions
 *
 * @author Zachary Doll
 * @since 1.0
 * @package Yaga
 */
class ReactionCount implements YagaRule {

    public function award($sender, $user, $criteria) {
        $actionID = $sender->EventArguments['ActionID'];

        if ($criteria->ActionID != $actionID) {
            return false;
        }

        $reactionModel = new ReactionModel();
        $count = $reactionModel->getUserCount($sender->EventArguments['ParentUserID'], $actionID);

        if ($count >= $criteria->Target) {
            // Award the badge to the user that got the reaction
            return $sender->EventArguments['ParentUserID'];
        } else {
            return false;
        }
    }

    public function form($form) {
        $actionModel = new ActionModel();
        $actions = $actionModel->get();
        $reactions = [];
        foreach ($actions as $action) {
            $reactions[$action->ActionID] = $action->Name;
        }

        $string = $form->label('Yaga.Rules.ReactionCount.Criteria.Head', 'ReactionCount');
        $string .= $form->textbox('Target', ['class' => 'SmallInput']).' ';
        $string .= $form->dropDown('ActionID', $reactions);

        return $string;
    }

    public function validate($criteria, $form) {
        $validation = new Gdn_Validation();
        $validation->applyRule('Target', ['Required', 'Integer']);
        $validation->applyRule('ActionID', ['Required', 'Integer']);
        $validation->validate($criteria);
        $form->setValidationResults($validation->results());
    }

    public function hooks() {
        return ['reactionModel_afterReactionSave'];
    }

    public function description() {
        $description = Gdn::translate('Yaga.Rules.ReactionCount.Desc');
        return wrap($description, 'div', ['class' => 'InfoMessage']);
    }

    public function name() {
        return Gdn::translate('Yaga.Rules.ReactionCount');
    }

    public function interacts() {
        return true;
    }
}
