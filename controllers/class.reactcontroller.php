<?php if (!defined("APPLICATION")) {
    exit();
}

/* Copyright 2013 Zachary Doll */

/**
 * This handles all the AJAX requests to actually react to user generated content.
 *
 * @since 1.0
 * @package Yaga
 */
class ReactController extends Gdn_Controller
{
    /**
     * @var array These objects will be created on instantiation and available via
     * $this->ObjectName
     */
    public $Uses = ["ActionModel", "ReactionModel"];

    /**
     * All requests to this controller must be made via JS.
     *
     * @throws PermissionException
     */
    public function initialize()
    {
        parent::initialize();
        $this->Application = "Yaga";
    }

    /**
     * This determines if the current user can react on this item with this action
     *
     * @param string $type valid options are 'discussion', 'comment', and 'activity'
     * @param int $id
     * @param int $actionID
     * @throws Gdn_UserException
     */
    public function index($type, $id, $actionID)
    {
        if (!$this->Request->isPostBack()) {
            throw permissionException("Javascript");
        }

        $type = strtolower($type);
        $action = $this->ActionModel->getID($actionID);

        // Make sure the action exists and the user is allowed to react
        if (!$action) {
            throw new Gdn_UserException(Gdn::translate("Yaga.Action.Invalid"));
        }

        $this->permission($action->Permission);

        $item = $this->ReactionModel->getReactionItem($type, $id);

        if (empty($item)) {
            throw new Gdn_UserException(
                Gdn::translate("Yaga.Action.InvalidTargetID")
            );
        }

        $anchor = "#" . ucfirst($type) . "_" . $id;
        $userID = Gdn::session()->UserID;

        if ($item["InsertUserID"] == $userID) {
            throw new Gdn_UserException(
                Gdn::translate("Yaga.Error.ReactToOwn")
            );
        }

        if (isset($item["PermissionCategoryID"])) {
            $this->permission(
                "Vanilla.Discussions.View",
                true,
                "Category",
                $item["PermissionCategoryID"]
            );
        }

        // It has passed through the gauntlet
        $this->ReactionModel->set($id, $type, $item, $userID, $actionID);

        $this->jsonTarget(
            $anchor . " .ReactMenu",
            renderReactionList($id, $type),
            "ReplaceWith"
        );
        $this->jsonTarget(
            $anchor . " .ReactionRecord",
            renderReactionRecord($id, $type),
            "ReplaceWith"
        );

        // Don't render anything
        $this->render("blank", "utility", "dashboard");
    }
}
