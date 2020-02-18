<?php if (!defined('APPLICATION')) exit();

/**
 * This rule awards badges based on the sum of a user's discussions & comments count
 *
 * @author Robin Jurinka
 * @since 1.0
 * @package Yaga
 */
class PostCount implements YagaRule {

    public function award($sender, $user, $criteria) {
        $result = false;
        $countPosts = $user->CountDiscussions + $user->CountComments;
        switch($criteria->Comparison) {
            case 'gt':
                if ($countPosts > $criteria->Target) {
                    $result = true;
                }
                break;
            case 'lt':
                if ($countPosts < $criteria->Target) {
                    $result = true;
                }
                break;
            default:
            case 'gte':
                if ($countPosts >= $criteria->Target) {
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

        $string = $form->label('Yaga.Rules.PostCount.Criteria.Head', 'PostCount');
        $string .= $form->dropDown('Comparison', $comparisons).' ';
        $string .= $form->textbox('Target');

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
        $description = Gdn::translate('Yaga.Rules.PostCount.Desc');
        return wrap($description, 'div', ['class' => 'InfoMessage']);
    }

    public function name() {
        return Gdn::translate('Yaga.Rules.PostCount');
    }

    public function interacts() {
        return false;
    }
}
