<?php if (!defined("APPLICATION")) {
    exit();
}

/* Copyright 2013 Zachary Doll */

/**
 * Renders a user's badges in a nice grid in the panel
 *
 * @package Yaga
 * @since 1.0
 */
class BadgesModule extends Gdn_Module
{
    /**
     * Retrieves the user's badgelist upon construction of the module object.
     *
     * @param string $sender
     */
    public function __construct($sender = "")
    {
        parent::__construct($sender);

        // default to the user object on the controller/the currently logged in user
        if (property_exists($sender, "User") && $sender->User) {
            $userID = $sender->User->UserID;
        } else {
            $userID = Gdn::session()->UserID;
        }

        if (Gdn::session()->UserID == $userID) {
            $this->Title = Gdn::translate("Yaga.Badges.Mine");
        } else {
            $this->Title = Gdn::translate("Yaga.Badges");
        }

        $badgeAwardModel = Gdn::getContainer()->get(BadgeAwardModel::class);
        $this->Data = $badgeAwardModel->getByUser($userID);
    }

    /**
     * Specifies the asset this module should be rendered to.
     *
     * @return string
     */
    public function assetTarget()
    {
        return "Panel";
    }

    /**
     * Renders a badge list in a nice little box.
     *
     * @return string
     */
    public function toString()
    {
        if ($this->Data) {
            if ($this->Visible) {
                $viewPath = $this->fetchViewLocation("badges", "plugins/yaga");
                $string = "";
                ob_start();
                include $viewPath;
                $string = ob_get_contents();
                @ob_end_clean();
                return $string;
            }
        }
        return "";
    }
}
