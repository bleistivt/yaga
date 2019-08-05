<?php if (!defined('APPLICATION')) exit();

use Yaga;

/* Copyright 2013 Zachary Doll */

/**
 * Manages the building of a rules cache and is provides admin functions for
 * managing badges in the dashboard.
 *
 * @since 1.0
 * @package Yaga
 */
class RulesController extends Gdn_Controller {

    /**
     * May be used in the future.
     *
     * @since 1.0
     * @access public
     */
    public function initialize() {
        parent::initialize();
        $this->Application = 'Yaga';
    }

    /**
     * This checks the cache for current rule set and expires once a day.
     * It loads all php files in the rules folder and selects only those that
     * implement the 'YagaRule' interface.
     *
     * @return array Rules that are currently available to use. The class names
     * are keys and the friendly names are values.
     */
    public static function getRules() {
        $rules = Gdn::cache()->get('Yaga.Badges.Rules');

        // rule files must always be loaded
         foreach (glob(PATH_PLUGINS.DS.'yaga'.DS.'library'.DS.'rules'.DS.'*.php') as $filename) {
             include_once $filename;
         }

        if ($rules === Gdn_Cache::CACHEOP_FAILURE) {
            $tempRules = [];
            foreach (get_declared_classes() as $className) {
                if (in_array('YagaRule', class_implements($className))) {
                    $rule = new $className();
                    $tempRules[$className] = $rule->name();
                }
            }

            // TODO: Don't reuse badge model?
            $model = Yaga::badgeModel();
            $model->EventArguments['Rules'] = &$tempRules;
            $model->FireAs = 'Yaga';
            $model->fireEvent('AfterGetRules');

            asort($tempRules);
            if (empty($tempRules)) {
                $rules = serialize(false);
            }
            else{
                $rules = serialize($tempRules);
            }
            Gdn::cache()->store('Yaga.Badges.Rules', $rules, [Gdn_Cache::FEATURE_EXPIRY => c('Yaga.Rules.CacheExpire', 86400)]);
        }

        return unserialize($rules);
    }

    /**
     * This checks the cache for current rule set that can be triggered for a user
     * by another user. It loads all rules and selects only those that return true
     * on its `Interacts()` method.
     *
     * @return array Rules that are currently available to use that are interactive.
     */
    public static function getInteractionRules() {
        $rules = Gdn::cache()->get('Yaga.Badges.InteractionRules');
        if ($rules === Gdn_Cache::CACHEOP_FAILURE) {
            $allRules = RulesController::getRules();

            $tempRules = [];
            foreach ($allRules as $className => $name) {
                $rule = new $className();
                if ($rule->interacts()) {
                    $tempRules[$className] = $name;
                }
            }
            if (empty($tempRules)) {
                $rules = serialize(false);
            }
            else{
                $rules = serialize($tempRules);
            }

            Gdn::cache()->store('Yaga.Badges.InteractionRules', $rules, [Gdn_Cache::FEATURE_EXPIRY => c('Yaga.Rules.CacheExpire', 86400)]);
        }

        return unserialize($rules);
    }

    /**
     * This creates a new rule object in a safe way and renders its criteria form.
     *
     * @param string $ruleClass
     */
    public function getCriteriaForm($ruleClass) {
        if (class_exists($ruleClass) && in_array('YagaRule', class_implements($ruleClass))) {
            $rule = new $ruleClass();
            $form = Gdn::factory('Form');
            $formString = $rule->form($form);
            $description = $rule->description();
            $name = $rule->name();

            $data = ['CriteriaForm' => $formString, 'RuleClass' => $ruleClass, 'Name' => $name, 'Description' => $description];
            $this->renderData($data);
        }
        else {
            $this->renderException(new Gdn_UserException(t('Yaga.Error.Rule404')));
        }
    }
}
