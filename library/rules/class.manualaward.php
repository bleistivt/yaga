<?php if (!defined("APPLICATION")) {
    exit();
}

/**
 * This rule never awards badges. It can safely be used for special badges that
 * only need to be manually awarded
 *
 * @author Zachary Doll
 * @since 1.0
 * @package Yaga
 */
class ManualAward implements YagaRule
{
    public function award($sender, $user, $criteria)
    {
        return false;
    }

    public function form($form)
    {
        return "";
    }

    public function validate($criteria, $form)
    {
        return;
    }

    public function hooks()
    {
        return [];
    }

    public function description()
    {
        $description = Gdn::translate("Yaga.Rules.ManualAward.Desc");
        return wrap($description, "div", ["class" => "AlertMessage"]);
    }

    public function name()
    {
        return Gdn::translate("Yaga.Rules.ManualAward");
    }

    public function interacts()
    {
        return false;
    }
}
