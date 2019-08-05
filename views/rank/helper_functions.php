<?php if (!defined('APPLICATION')) exit();

use Yaga;

/**
 * Helper functions used by the rank views.
 * 
 * @package Yaga
 * @since 1.0
 * @copyright (c) 2014, Zachary Doll
 */

if(!function_exists('AgeArray')) {
    /**
     * Defines the age options array for use in ranks
     * 
     * @return array
     */
    function ageArray() {
        return [
            strtotime('0 seconds', 0) => t('Yaga.Ranks.RequiredAgeDNC'),
            strtotime('1 hour', 0) => sprintf(t('Yaga.Ranks.RequiredAgeFormat'), t('1 hour')),
            strtotime('1 day', 0) => sprintf(t('Yaga.Ranks.RequiredAgeFormat'), t('1 day')),
            strtotime('1 week', 0) => sprintf(t('Yaga.Ranks.RequiredAgeFormat'), t('1 week')),
            strtotime('1 month', 0) => sprintf(t('Yaga.Ranks.RequiredAgeFormat'), t('1 month')),
            strtotime('3 months', 0) => sprintf(t('Yaga.Ranks.RequiredAgeFormat'), t('3 months')),
            strtotime('6 months', 0) => sprintf(t('Yaga.Ranks.RequiredAgeFormat'), t('6 months')),
            strtotime('1 year', 0) => sprintf(t('Yaga.Ranks.RequiredAgeFormat'), t('1 year')),
            strtotime('5 years', 0) => sprintf(t('Yaga.Ranks.RequiredAgeFormat'), t('5 years'))
        ];
    }
}
