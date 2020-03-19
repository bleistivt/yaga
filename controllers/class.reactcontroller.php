<?php if (!defined('APPLICATION')) exit();

/* Copyright 2013 Zachary Doll */

/**
 * This handles all the AJAX requests to actually react to user generated content.
 *
 * @since 1.0
 * @package Yaga
 */
class ReactController extends Gdn_Controller {

    /**
     * @var array These objects will be created on instantiation and available via
     * $this->ObjectName
     */
    public $Uses = ['ActionModel', 'ReactionModel'];

    /**
     * All requests to this controller must be made via JS.
     *
     * @throws PermissionException
     */
    public function initialize() {
        parent::initialize();
        $this->Application = 'Yaga';
        if (!$this->Request->isPostBack()) {
            throw PermissionException('Javascript');
        }
    }

    /**
     * This determines if the current user can react on this item with this action
     *
     * @param string $type valid options are 'discussion', 'comment', and 'activity'
     * @param int $id
     * @param int $actionID
     * @throws Gdn_UserException
     */
    public function index($type, $id, $actionID) {
        $type = strtolower($type);
        $action = $this->ActionModel->getID($actionID);

        // Make sure the action exists and the user is allowed to react
        if (!$action) {
            throw new Gdn_UserException(Gdn::translate('Yaga.Action.Invalid'));
        }

        if (!Gdn::session()->checkPermission($action->Permission)) {
            throw PermissionException();
        }

        $item = null;
        $anchorID = '#'.ucfirst($type).'_';
        $itemOwnerID = 0;

        if (in_array($type, ['discussion', 'comment'])) {
            $item = getRecord($type, $id);
        } elseif ($type == 'activity') {
            $model = new ActivityModel();
            $item = $model->getID($id, DATASET_TYPE_ARRAY);
        } else {
            $this->EventArguments = [
                'TypeFound' => false,
                'TargetType' => $type,
                'TargetID' => $id,
                'Item' => &$item,
                'AnchorID' => &$anchorID,
                'ItemOwnerID' => &$itemOwnerID
            ];
            $this->fireEvent('CustomType');

            if (!$this->EventArguments['TypeFound']) {
                throw new Gdn_UserException(Gdn::translate('Yaga.Action.InvalidTargetType'));
            }
        }

        if ($item) {
            $anchor = $anchorID.$id;
        } else {
            throw new Gdn_UserException(Gdn::translate('Yaga.Action.InvalidTargetID'));
        }

        $userID = Gdn::session()->UserID;

        switch($type) {
            case 'comment':
            case 'discussion':
                $itemOwnerID = $item['InsertUserID'];
                break;
            case 'activity':
                $itemOwnerID = $item['RegardingUserID'];
                break;
            default:
                break;
        }

        if ($itemOwnerID == $userID) {
            throw new Gdn_UserException(Gdn::translate('Yaga.Error.ReactToOwn'));
        }

        // It has passed through the gauntlet
        $this->ReactionModel->set($id, $type, $itemOwnerID, $userID, $actionID);

        $this->jsonTarget($anchor.' .ReactMenu', renderReactionList($id, $type), 'ReplaceWith');
        $this->jsonTarget($anchor.' .ReactionRecord', renderReactionRecord($id, $type), 'ReplaceWith');

        // Don't render anything
        $this->render('blank', 'utility', 'dashboard');
    }

}
