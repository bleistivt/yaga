<?php if (!defined('APPLICATION')) exit();

/**
 * Manage actions that are available for reactions
 *
 * @since 1.0
 * @package Yaga
 * @copyright (c) 2013-2014, Zachary Doll
 */
class ActionController extends DashboardController {

    /**
     * @var array These objects will be created on instantiation and available via
     * $this->ObjectName
     */
    public $Uses = ['Form', 'ActionModel'];

    /**
     * Make this look like a dashboard page and add the resources
     *
     * @since 1.0
     * @access public
     */
    public function initialize() {
        parent::initialize();
        $this->Application = 'Yaga';
        Gdn_Theme::section('Dashboard');
        if ($this->Menu) {
            $this->Menu->highlightRoute('/action');
        }
        $this->addJsFile('jquery-ui-1.10.0.custom.min.js');
        $this->addJsFile('admin.actions.js');
        $this->addCssFile('reactions.css');
        $this->removeCssFile('magnific-popup.css');
    }

    /**
     * Manage the available actions for reactions
     *
     * @param int $page
     */
    public function settings($page = '') {
        $this->permission('Yaga.Reactions.Manage');
        $this->setHighlightRoute('action/settings');

        $this->title(Gdn::translate('Yaga.Actions.Manage'));

        // Get list of actions from the model and pass to the view
        $this->setData('Actions', $this->ActionModel->get());

        $this->render();
    }

    /**
     * Edit an existing action or add a new one
     *
     * @param int $actionID
     */
    public function edit($actionID = null) {
        $this->permission('Yaga.Reactions.Manage');
        $this->setHighlightRoute('action/settings');
        $this->Form->setModel($this->ActionModel);

        $edit = false;
        $this->title(Gdn::translate('Yaga.Action.Add'));
        if ($actionID) {
            $this->Action = $this->ActionModel->getID($actionID);
            $this->Form->addHidden('ActionID', $actionID);
            $edit = true;
            $this->title(Gdn::translate('Yaga.Action.Edit'));
        }

        // This is just a list of all the images in the action icons folder
        $this->setData('Icons', [
            'Happy',
            'Happy2',
            'Smiley',
            'Smiley2',
            'Tongue',
            'Tongue2',
            'Sad',
            'Sad2',
            'Wink',
            'Wink2',
            'Grin',
            'Shocked',
            'Confused',
            'Confused2',
            'Neutral',
            'Neutral2',
            'Wondering',
            'Wondering2',
            'PointUp',
            'PointRight',
            'PointDown',
            'PointLeft',
            'ThumbsUp',
            'ThumbsUp2',
            'Shocked2',
            'Evil',
            'Evil2',
            'Angry',
            'Angry2',
            'Heart',
            'Heart2',
            'HeartBroken',
            'Star',
            'Star2',
            'Grin2',
            'Cool',
            'Cool2',
            'Question',
            'Notification',
            'Warning',
            'Spam',
            'Blocked',
            'Eye',
            'Eye2',
            'EyeBlocked',
            'Flag',
            'BrightnessMedium',
            'QuotesLeft',
            'Music',
            'Pacman',
            'Bullhorn',
            'Rocket',
            'Fire',
            'Hammer',
            'Target',
            'Lightning',
            'Shield',
            'CheckmarkCircle',
            'Lab',
            'Leaf',
            'Dashboard',
            'Droplet',
            'Feed',
            'Support',
            'Hammer2',
            'Wand',
            'Cog',
            'Gift',
            'Trophy',
            'Magnet',
            'Switch',
            'Globe',
            'Bookmark',
            'Bookmarks',
            'Star3',
            'Info',
            'Info2',
            'CancelCircle',
            'Checkmark',
            'Close'
        ]);

        // Load up all permissions
        $permissions = Gdn::permissionModel()->permissionColumns();
        unset($permissions['PermissionID']);
        $permissionKeys = array_keys($permissions);
        $permissionList = array_combine($permissionKeys, $permissionKeys);
        $this->setData('Permissions', $permissionList);

        if ($this->Form->isPostBack() == false) {
            if (property_exists($this, 'Action')) {
                $this->Form->setData($this->Action);
            } else {
                $this->Form->setData(['Permission' => 'Yaga.Reactions.Add']);
            }
        } else {
            $newID = $this->Form->save();
            if ($newID) {
                $action = $this->ActionModel->getID($newID);

                if ($edit) {
                    $this->informMessage(Gdn::translate('Yaga.ActionUpdated'));
                } else {
                    $this->informMessage(Gdn::translate('Yaga.Action.Added'));
                }

                redirectTo('/action/settings');
            }
        }

        $this->render('edit');
    }

    /**
     * Convenience function for nice URLs
     */
    public function add() {
        $this->edit();
    }

    /**
     * Remove the action via model.
     *
     * @param int $actionID
     * @throws NotFoundException
     */
    public function delete($actionID) {
        $action = $this->ActionModel->getID($actionID);

        if (!$action) {
            throw NotFoundException(Gdn::translate('Yaga.Action'));
        }

        $this->permission('Yaga.Reactions.Manage');

        $actions = $this->ActionModel->get();
        // Cast to array of arrays until vanillaforums/Garden issue #1879 is fixed
        foreach ($actions as $index => $actionObject) {
            $actions[$index] = (array)$actionObject;
        }

        $actions = array_column($actions, 'Name', 'ActionID');
        unset($actions[$actionID]);

        $this->setData('OtherActions', $actions);
        $this->setData('ActionName', $action->Name);

        if ($this->Form->isPostBack()) {
            $formValues = $this->Form->formValues();
            $replacementID = $formValues['Move'] ? $formValues['ReplacementID'] : null;

            //$replacement
            if (!$this->ActionModel->deleteAction($actionID, $replacementID)) {
                $this->Form->addError(sprintf(Gdn::translate('Yaga.Error.DeleteFailed'), Gdn::translate('Yaga.Action')));
            }

            if ($this->Form->errorCount() == 0) {
                if ($this->_DeliveryType === DELIVERY_TYPE_ALL) {
                    redirectTo('action/settings');
                }

                $this->jsonTarget('#ActionID_'.$actionID, null, 'SlideUp');
            }
        }

        $this->setHighlightRoute('action/settings');
        $this->setData('Title', Gdn::translate('Yaga.Action.Delete'));
        $this->render();
    }

    /**
     * This takes in a sort array and updates the action sort order.
     *
     * Renders the Save tree and/or the Result of the sort update.
     */
    public function sort() {
        // Check permission
        $this->permission('Yaga.Reactions.Manage');

        $request = Gdn::request();
        if ($request->isPostBack()) {
            $sortArray = $request->getValue('SortArray', null);
            $saves = $this->ActionModel->saveSort($sortArray);
            $this->setData('Result', true);
            $this->setData('Saves', $saves);
        } else {
            $this->setData('Result', false);
        }

        $this->renderData();
     }
}
