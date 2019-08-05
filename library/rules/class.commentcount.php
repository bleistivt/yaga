<?php if (!defined('APPLICATION')) exit();

use Yaga;

/**
 * This rule awards badges based on a user's comment count
 *
 * @author Zachary Doll
 * @since 1.0
 * @package Yaga
 */
class CommentCount implements YagaRule{

    public function award($sender, $user, $criteria) {
        $result = false;
        switch($criteria->Comparison) {
            case 'gt':
                if ($user->CountComments > $criteria->Target) {
                    $result = true;
                }
                break;
            case 'lt':
                if ($user->CountComments < $criteria->Target) {
                    $result = true;
                }
                break;
            default:
            case 'gte':
                if ($user->CountComments >= $criteria->Target) {
                    $result = true;
                }
                break;
        }

        return $result;
    }

    public function form($form) {
        $comparisons = [
            'gt' => t('More than:'),
            'lt' => t('Less than:'),
            'gte' => t('More than or:')
        ];

        $string = $form->label('Yaga.Rules.CommentCount.Criteria.Head', 'CommentCount');
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
        $description = t('Yaga.Rules.CommentCount.Desc');
        return wrap($description, 'div', ['class' => 'InfoMessage']);
    }

    public function name() {
        return t('Yaga.Rules.CommentCount');
    }

    public function interacts() {
        return false;
    }
}
