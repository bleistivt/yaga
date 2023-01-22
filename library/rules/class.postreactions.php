<?php if (!defined("APPLICATION")) {
    exit();
}

/**
 * This rule awards badges to a particular post's owner when it receives the
 * target number of reactions.
 *
 * @author Zachary Doll
 * @since 1.0
 * @package Yaga
 */
class PostReactions implements YagaRule
{
    public function award($sender, $user, $criteria)
    {
        $args = $sender->EventArguments;
        // Check to see if the submitted action is a target
        $prop = "ActionID_" . $sender->EventArguments["ActionID"];
        if (property_exists($criteria, $prop)) {
            $value = $criteria->$prop;
            if ($value <= 0 || $value == false) {
                return false;
            }
        } else {
            return false;
        }

        // Get the reaction counts for this parent item
        $reactionModel = Gdn::getContainer()->get(ReactionModel::class);
        $reactions = $reactionModel->getList(
            $args["ParentID"],
            $args["ParentType"]
        );

        // Squash the dataset into an array
        $counts = [];
        foreach ($reactions as $reaction) {
            $counts["ActionID_" . $reaction->ActionID] = $reaction->Count;
        }

        // Actually check for the reaction counts
        foreach ($criteria as $actionID => $target) {
            if ($counts[$actionID] < $target) {
                return false;
            }
        }

        // The owner should be awarded
        return $args["ParentUserID"];
    }

    public function form($form)
    {
        $actions = Gdn::getContainer()
            ->get(ActionModel::class)
            ->get();

        $string = $form->label(
            "Yaga.Rules.PostReactions.Criteria.Head",
            "ReactionCount"
        );

        $actionList = "";
        foreach ($actions as $action) {
            $actionList .= wrap(
                sprintf(
                    Gdn::translate("Yaga.Rules.PostReactions.LabelFormat"),
                    $action->Name
                ) . $form->textbox("ActionID_" . $action->ActionID),
                "li"
            );
        }

        if ($actionList == "") {
            $string .= Gdn::translate("Yaga.Error.NoActions");
        } else {
            $string .= wrap($actionList, "ul");
        }

        return $string;
    }

    public function validate($criteria, $form)
    {
        $validation = new Gdn_Validation();

        foreach ($criteria as $actionID => $target) {
            $validation->applyRule($actionID, "Integer");
        }

        $validation->validate($criteria);
        $form->setValidationResults($validation->results());
    }

    public function hooks()
    {
        return ["reactionModel_afterReactionSave"];
    }

    public function description()
    {
        $description = Gdn::translate("Yaga.Rules.PostReactions.Desc");
        return wrap($description, "div", [
            "class" => "alert alert-info padded",
        ]);
    }

    public function name()
    {
        return Gdn::translate("Yaga.Rules.PostReactions");
    }

    public function interacts()
    {
        return true;
    }
}
