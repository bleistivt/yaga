<?php if (!defined('APPLICATION')) exit();

/**
 * This rule awards badges based on a user's discussion count
 *
 * @author Zachary Doll
 * @since 1.0
 * @package Yaga
 */
class DiscussionCount implements YagaRule {

    public function award($sender, $user, $criteria) {
        $result = false;
        switch($criteria->Comparison) {
            case 'gt':
                if ($user->CountDiscussions > $criteria->Target) {
                    $result = true;
                }
                break;
            case 'lt':
                if ($user->CountDiscussions < $criteria->Target) {
                    $result = true;
                }
                break;
            default:
            case 'gte':
                if ($user->CountDiscussions >= $criteria->Target) {
                    $result = true;
                }
                break;
        }

        return $result;
    }

    public function form($form) {
        $comparisons = [
            'gt' => Gdn::translate('More than:'),
            'lt' => Gdn::translate('Less than:'),
            'gte' => Gdn::translate('More than or:')
        ];

        $string = $form->label('Yaga.Rules.DiscussionCount.Criteria.Head', 'DiscussionCount');
        $string .= $form->dropDown('Comparison', $comparisons).' ';
        $string .= $form->textbox('Target', ['class' => 'SmallInput']);

        return $string;
    }

    public function validate($criteria, $form) {
        $validation = new Gdn_Validation();
        $validation->applyRule('Target', ['Required', 'Integer']);
        $validation->applyRule('Comparison', 'Required');
        $validation->validate($criteria);
        $form->setValidationResults($validation->results());
    }

    public function hooks() {
        return ['gdn_dispatcher_appStartup'];
    }

    public function description() {
        $description = Gdn::translate('Yaga.Rules.DiscussionCount.Desc');
        return wrap($description, 'div', ['class' => 'InfoMessage']);
    }

    public function name() {
        return Gdn::translate('Yaga.Rules.DiscussionCount');
    }

    public function interacts() {
        return false;
    }
}
