<?php if (!defined('APPLICATION')) exit();

/* Copyright 2013 Zachary Doll */

/**
 * Contains management code for creating badges.
 *
 * @since 1.0
 * @package Yaga
 */
class BadgeController extends DashboardController {

    /**
     * @var array These objects will be created on instantiation and available via
     * $this->ObjectName
     */
    public $Uses = ['Form', 'BadgeModel', 'BadgeAwardModel'];

    private $editFormFields = [
        'TransientKey',
        'BadgeID',
        'Name',
        'Description',
        'RuleClass',
        'AwardValue',
        'Checkboxes',
        'Save',
        'Enabled',
        'Photo',
        'PhotoUpload'
    ];

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
            $this->Menu->highlightRoute('/badge');
        }
        $this->addJsFile('jquery-ui-1.10.0.custom.min.js');
        $this->addJsFile('admin.badges.js');
        $this->addCssFile('badges.css');
        $this->removeCssFile('magnific-popup.css');

        $this->editFormFields[] = Gdn::config('Garden.Forms.HoneypotName');
    }

    /**
     * Manage the current badges and add new ones
     *
     * @param int $page
     */
    public function settings($page = '') {
        $this->permission('Yaga.Badges.Manage');
        $this->setHighlightRoute('badge/settings');

        $this->title(Gdn::translate('Yaga.Badges.Manage'));

        // Get list of badges from the model and pass to the view
        list($offset, $limit) = offsetLimit($page, PagerModule::$DefaultPageSize);

        // This page cannot have a pager as this would interfere with sorting.
        $this->setData('Badges', $this->BadgeModel->get());
        $this->setData('Rules', $this->BadgeModel->getRules());

        $this->render();
    }

    /**
     * Edit an existing badge or add a new one
     *
     * @param int $badgeID
     * @throws ForbiddenException if no proper rules are found
     */
    public function edit($badgeID = null) {
        $this->permission('Yaga.Badges.Manage');
        $this->setHighlightRoute('badge/settings');
        $this->Form->setModel($this->BadgeModel);

        // Only allow editing if some rules exist
        if (!$this->BadgeModel->getRules()) {
            throw new Gdn_UserException(Gdn::translate('Yaga.Error.NoRules'));
        }

        $edit = false;
        if ($badgeID) {
            $this->title(Gdn::translate('Yaga.Badge.Edit'));
            $this->Badge = $this->BadgeModel->getID($badgeID);
            $this->Form->addHidden('BadgeID', $badgeID);
            $edit = true;
        } else {
            $this->title(Gdn::translate('Yaga.Badge.Add'));
        }

        if ($this->Form->isPostBack() == false) {
            if (property_exists($this, 'Badge')) {
                // Manually merge the criteria into the badge object
                $criteria = (array)dbdecode($this->Badge->RuleCriteria);
                $badgeArray = (array)$this->Badge;

                $data = array_merge($badgeArray, $criteria);
                $this->Form->setData($data);
            }
        } else {
            // Handle the photo upload
            $upload = new Gdn_Upload();
            $upload->allowFileExtension('svg');
            $tmpImage = $upload->validateUpload('PhotoUpload', false);

            if ($tmpImage) {
                // Generate the target image name
                $targetImage = $upload->generateTargetName(PATH_UPLOADS, false);
                $imageBaseName = pathinfo($targetImage, PATHINFO_BASENAME);

                // Save the uploaded image
                $parts = $upload->saveAs($tmpImage, 'yaga/'.$imageBaseName);
                $assetRoot = Gdn::request()->urlDomain(true).Gdn::request()->assetRoot();
                $relativeUrl = stringBeginsWith($parts['Url'], $assetRoot, true, true);

                $this->Form->setFormValue('Photo', $relativeUrl);
            } elseif (!$edit) {
                // Use default photo from config if this is a new badge
                $this->Form->setFormValue('Photo', Gdn::config('Yaga.Badges.DefaultPhoto'));
            }

            // Find the rule criteria
            $formValues = $this->Form->formValues();
            $criteria = array_diff_key($formValues, array_flip($this->editFormFields));

            // Validate the criteria
            $ruleClass = $formValues['RuleClass'];
            $rule = new $ruleClass();

            $rule->validate($criteria, $this->Form);

            $serializedCriteria = dbencode($criteria);
            $this->Form->setFormValue('RuleCriteria', $serializedCriteria);
            if ($this->Form->save()) {
                if ($edit) {
                    $this->informMessage(Gdn::translate('Yaga.Badge.Updated'));
                } else {
                    $this->informMessage(Gdn::translate('Yaga.Badge.Added'));
                }
                redirectTo('/badge/settings');
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
     * Remove the badge via model.
     *
     * @param int $badgeID
     * @throws NotFoundException
     */
    public function delete($badgeID) {
        $badge = $this->BadgeModel->getID($badgeID);

        if (!$badge) {
            throw NotFoundException(Gdn::translate('Yaga.Badge'));
        }

        $this->permission('Yaga.Badges.Manage');

        if ($this->Form->isPostBack()) {
            if (!$this->BadgeModel->deleteID($badgeID)) {
                $this->Form->addError(sprintf(Gdn::translate('Yaga.Error.DeleteFailed'), Gdn::translate('Yaga.Badge')));
            }

            if ($this->Form->errorCount() == 0) {
                if ($this->_DeliveryType === DELIVERY_TYPE_ALL) {
                    redirectTo('badge/settings');
                }

                $this->jsonTarget('#BadgeID_'.$badgeID, null, 'SlideUp');
            }
        }

        $this->setHighlightRoute('badge/settings');
        $this->setData('Title', Gdn::translate('Yaga.Badge.Delete'));
        $this->render();
    }

    /**
     * Toggle the enabled state of a badge. Must be done via JS.
     *
     * @param int $badgeID
     * @throws PermissionException
     */
    public function toggle($badgeID) {
        if (!$this->Request->isPostBack()) {
            throw new Gdn_UserException(Gdn::translate('Yaga.Error.NeedJS'));
        }
        $this->permission('Yaga.Badges.Manage');
        $this->setHighlightRoute('badge/settings');

        $badge = $this->BadgeModel->getID($badgeID);

        $badge->Enabled = !$badge->Enabled;
        $this->BadgeModel->enable($badgeID, $badge->Enabled);

        $slider = renderYagaToggle('badge/toggle/'.$badge->BadgeID, $badge->Enabled, $badge->BadgeID);
        $this->jsonTarget('#toggle-'.$badge->BadgeID, $slider, 'ReplaceWith');
        $this->render('blank', 'utility', 'dashboard');
    }

    /**
     * Remove the photo association of a badge. This does not remove the actual file
     *
     * @param int $badgeID
     * @param string $transientKey
     */
    public function deletePhoto($badgeID = false) {
            // Check permission
            $this->permission('Yaga.Badges.Manage');

            $redirectUrl = 'badge/edit/'.$badgeID;

            if (Gdn::request()->isAuthenticatedPostBack(true)) {
                $this->BadgeModel->setField($badgeID, 'Photo', Gdn::config('Yaga.Badges.DefaultPhoto'));
                $this->informMessage(Gdn::translate('Yaga.Badge.PhotoDeleted'));
            }

            if ($this->_DeliveryType == DELIVERY_TYPE_ALL) {
                redirectTo($redirectUrl);
            } else {
                $this->RedirectUrl = url($redirectUrl);
                $this->render('blank', 'utility', 'dashboard');
            }
     }

    /**
     * You can manually award badges to users for special cases
     *
     * @param int $userID
     * @throws Gdn_UserException
     */
    public function award($userID) {
        // Check permission
        $this->permission('Yaga.Badges.Add');
        $this->setHighlightRoute('badge/settings');

        // Only allow awarding if some badges exist
        if (!$this->BadgeModel->getCount()) {
            throw new Gdn_UserException(Gdn::translate('Yaga.Error.NoBadges'));
        }

        $userModel = Gdn::userModel();
        $user = $userModel->getID($userID);

        $this->setData('Username', $user->Name);

        $badges = $this->BadgeModel->get();
        $badgelist = [];
        foreach ($badges as $badge) {
            $badgelist[$badge->BadgeID] = $badge->Name;
        }
        $this->setData('Badges', $badgelist);

        if ($this->Form->isPostBack() == false) {
            // Add the user id field
            $this->Form->addHidden('UserID', $user->UserID);
        } else {
            $validation = new Gdn_Validation();
            $validation->applyRule('UserID', 'ValidateRequired');
            $validation->applyRule('BadgeID', 'ValidateRequired');
            if ($validation->validate($this->Request->post())) {
                $formValues = $this->Form->formValues();
                if ($this->BadgeAwardModel->exists($formValues['UserID'], $formValues['BadgeID'])) {
                    $this->Form->addError(sprintf(Gdn::translate('Yaga.Badge.AlreadyAwarded'), $user->Name), 'BadgeID');
                    // Need to respecify the user id
                    $this->Form->addHidden('UserID', $user->UserID);
                }

                if ($this->Form->errorCount() == 0) {
                    $this->BadgeAwardModel->award($formValues['BadgeID'], $formValues['UserID'], Gdn::session()->UserID, $formValues['Reason']);

                    if ($this->Request->get('Target')) {
                        $this->RedirectUrl = $this->Request->get('Target');
                    } elseif ($this->deliveryType() == DELIVERY_TYPE_ALL) {
                        $this->RedirectUrl = url(userUrl($user));
                    } else {
                        $this->jsonTarget('', '', 'Refresh');
                    }
                }
            } else {
                $this->Form->setValidationResults($validation->results());
            }
        }

        $this->render();
    }

    /**
     * This takes in a sort array and updates the badge sort order.
     *
     * Renders the Save tree and/or the Result of the sort update.
     *
     * @since 1.1
     */
    public function sort() {
        // Check permission
        $this->permission('Yaga.Badges.Manage');

        $request = Gdn::request();
        if ($request->isPostBack()) {
            $sortArray = $request->getValue('SortArray', null);
            $saves = $this->BadgeModel->saveSort($sortArray);
            $this->setData('Result', true);
            $this->setData('Saves', $saves);
        } else {
            $this->setData('Result', false);
        }

        $this->renderData();
    }

    /**
     * This creates a new rule object in a safe way and renders its criteria form.
     *
     * @param string $ruleClass
     */
    public function ruleCriteriaForm($ruleClass) {
        if (class_exists($ruleClass) && in_array('YagaRule', class_implements($ruleClass))) {
            $rule = new $ruleClass();
            $this->Form->setStyles('bootstrap');

            $this->renderData([
                'CriteriaForm' => $rule->form($this->Form),
                'RuleClass' => $ruleClass,
                'Name' => $rule->name(),
                'Description' => $rule->description()
            ]);
        } else {
            $this->renderException(new Gdn_UserException(Gdn::translate('Yaga.Error.Rule404')));
        }
    }

}
