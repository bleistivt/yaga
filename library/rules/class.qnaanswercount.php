<?php if (!defined('APPLICATION')) exit();

use Yaga;

/**
 * This rule awards badges based on a user's answer count from the QnA plugin
 *
 * @author Zachary Doll
 * @since 0.5
 * @package Yaga
 */
class QnAAnserCount implements YagaRule{

    public function award($sender, $user, $criteria) {
        $result = false;
        switch($criteria->Comparison) {
            case 'gt':
                if ($user->CountAcceptedAnswers > $criteria->Target) {
                    $result = true;
                }
                break;
            case 'lt':
                if ($user->CountAcceptedAnswers < $criteria->Target) {
                    $result = true;
                }
                break;
            default:
            case 'gte':
                if ($user->CountAcceptedAnswers >= $criteria->Target) {
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

        $string = $form->label('Yaga.Rules.QnAAnserCount.Criteria.Head', 'QnAAnserCount');
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
        $description = t('Yaga.Rules.QnAAnserCount.Desc');
        return wrap($description, 'div', ['class' => 'InfoMessage']);
    }

    public function name() {
        return t('Yaga.Rules.QnAAnserCount');
    }

    public function interacts() {
        return false;
    }
}
