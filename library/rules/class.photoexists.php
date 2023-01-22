<?php if (!defined("APPLICATION")) {
    exit();
}

/**
 * This rule awards badges if the user has a profile photo
 *
 * @author Zachary Doll
 * @since 1.0
 * @package Yaga
 */
class PhotoExists implements YagaRule
{
    public function award($sender, $user, $criteria)
    {
        return (bool) $user->Photo;
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
        return ["gdn_dispatcher_appStartup"];
    }

    public function description()
    {
        $description = Gdn::translate("Yaga.Rules.PhotoExists.Desc");
        return wrap($description, "div", [
            "class" => "alert alert-info padded",
        ]);
    }

    public function name()
    {
        return Gdn::translate("Yaga.Rules.PhotoExists");
    }

    public function interacts()
    {
        return false;
    }
}
