<?php if (!defined('APPLICATION')) exit();

use Yaga;

/* Copyright 2013 Zachary Doll */

/**
 * Contains management code for designing ranks.
 *
 * @since 1.0
 * @package Yaga
 */
class RankController extends DashboardController {

    /**
     * @var array These objects will be created on instantiation and available via
     * $this->ObjectName
     */
    public $uses = ['Form', 'RankModel'];

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
            $this->Menu->highlightRoute('/rank');
        }
        $this->addJsFile('jquery-ui-1.10.0.custom.min.js');
        $this->addJsFile('admin.ranks.js');
        $this->removeCssFile('magnific-popup.css');
    }

    /**
     * Manage the current ranks and add new ones
     */
    public function settings() {
        $this->permission('Yaga.Ranks.Manage');
        $this->setHighlightRoute('rank/settings');

        $this->title(Gdn::translate('Yaga.Ranks.Manage'));

        // Get list of ranks from the model and pass to the view
        $this->setData('Ranks', $this->RankModel->get());

        if ($this->Form->isPostBack() == true) {
            // Handle the photo upload
            $upload = new Gdn_Upload();
            $tmpImage = $upload->validateUpload('PhotoUpload', false);

            if ($tmpImage) {
                // Generate the target image name
                $targetImage = $upload->generateTargetName(PATH_UPLOADS, false);
                $imageBaseName = pathinfo($targetImage, PATHINFO_BASENAME);

                // Save the uploaded image
                $parts = $upload->saveAs($tmpImage, 'yaga/'.$imageBaseName);
                $assetRoot = Gdn::request()->urlDomain(true).Gdn::request()->assetRoot();
                $relativeUrl = stringBeginsWith($parts['Url'], $assetRoot, true, true);
                Gdn::config()->saveToConfig('Yaga.Ranks.Photo', $relativeUrl);

                if (Gdn::config('Yaga.Ranks.Photo') == $parts['SaveName']) {
                    $this->informMessage(Gdn::translate('Yaga.Rank.PhotoUploaded'));
                }
            }
        }

        include_once $this->fetchViewLocation('helper_functions', 'rank');
        $this->render();
    }

    /**
     * Edit an existing rank or add a new one
     *
     * @param int $rankID
     * @throws ForbiddenException if no proper rules are found
     */
    public function edit($rankID = null) {
        $this->permission('Yaga.Ranks.Manage');
        $this->setHighlightRoute('rank/settings');
        $this->Form->setModel($this->RankModel);

        $edit = false;
        if ($rankID) {
            $this->title(Gdn::translate('Yaga.Rank.Edit'));
            $this->Rank = $this->RankModel->getByID($rankID);
            $this->Form->addHidden('RankID', $rankID);
            $edit = true;
        } else {
            $this->title(Gdn::translate('Yaga.Rank.Add'));
        }

        // Load up all roles
        $roleModel = new RoleModel();
        $roles = $roleModel->getArray();
        $this->setData('Roles', $roles);

        if ($this->Form->isPostBack() != true) {
            if (property_exists($this, 'Rank')) {
                $perkOptions = (array) unserialize($this->Rank->Perks);
                $rankArray = (array) $this->Rank;

                $data = array_merge($rankArray, $perkOptions);
                $this->Form->setData($data);
            }
        } else {
            // Find the perk options
            $perkOptions = array_intersect_key(
                $this->Form->formValues(),
                array_flip([
                    'ConfGarden.EditContentTimeout',
                    'ConfGarden.Format.MeActions',
                    'ConfPlugins.Emotify.FormatEmoticons',
                    'ConfYaga.Perks.EditTimeout',
                    'ConfYaga.Perks.Emoticons',
                    'ConfYaga.Perks.MeActions',
                    'PermGarden.Curation.Manage',
                    'PermPlugins.Signatures.Edit',
                    'PermPlugins.Tagging.Add',
                    'PermYaga.Perks.Curation',
                    'PermYaga.Perks.Signatures',
                    'PermYaga.Perks.Tags',
                    'Role'
                ])
            );

            // Fire event for validating perk options
            $this->EventArguments['PerkOptions'] =& $perkOptions;
            $this->fireEvent('BeforeValidation');

            $this->Form->setFormValue('Perks', serialize($perkOptions));

            if ($this->Form->save()) {
                if ($edit) {
                    $this->informMessage(Gdn::translate('Yaga.Rank.Updated'));
                } else {
                    $this->informMessage(Gdn::translate('Yaga.Rank.Added'));
                }
                redirectTo('/rank/settings');
            }
        }

        include_once $this->fetchViewLocation('helper_functions', 'rank');
        $this->render('edit');
    }

    /**
     * Convenience function for nice URLs
     */
    public function add() {
        $this->edit();
    }

    /**
     * Remove the rank via model.
     *
     * @param int $rankID
     * @throws NotFoundException
     */
    public function delete($rankID) {
        $rank = $this->RankModel->getByID($rankID);

        if (!$rank) {
            throw NotFoundException(Gdn::translate('Yaga.Rank'));
        }

        $this->permission('Yaga.Ranks.Manage');

        if ($this->Form->isPostBack()) {
            if (!$this->RankModel->deleteID($rankID)) {
                $this->Form->addError(sprintf(Gdn::translate('Yaga.Error.DeleteFailed'), Gdn::translate('Yaga.Rank')));
            }

            if ($this->Form->errorCount() == 0) {
                if ($this->_DeliveryType === DELIVERY_TYPE_ALL) {
                    redirectTo('rank/settings');
                }

                $this->jsonTarget('#RankID_'.$rankID, null, 'SlideUp');
            }
        }

        $this->setHighlightRoute('rank/settings');
        $this->setData('Title', Gdn::translate('Yaga.Rank.Delete'));
        $this->render();
    }

    /**
     * Toggle the enabled state of a rank. Must be done via JS.
     *
     * @param int $rankID
     * @throws PermissionException
     */
    public function toggle($rankID) {
        if (!$this->Request->isPostBack()) {
            throw PermissionException('Javascript');
        }
        $this->permission('Yaga.Ranks.Manage');
        $this->setHighlightRoute('rank/settings');

        $rank = $this->RankModel->getByID($rankID);

        if ($rank->Enabled) {
            $enable = false;
            $toggleText = Gdn::translate('Disabled');
            $activeClass = 'InActive';
        } else {
            $enable = true;
            $toggleText = Gdn::translate('Enabled');
            $activeClass = 'Active';
        }

        $slider = wrap(wrap(anchor($toggleText, 'rank/toggle/'.$rank->RankID, 'Hijack Button'), 'span', ['class' => "ActivateSlider ActivateSlider-{$activeClass}"]), 'td');
        $this->RankModel->enable($rankID, $enable);
        $this->jsonTarget('#RankID_'.$rankID.' td:nth-child(6)', $slider, 'ReplaceWith');
        $this->render('blank', 'utility', 'dashboard');
    }

    /**
     * Remove the photo association of rank promotions. This does not remove the
     * actual file.
     *
     * @param string $transientKey
     */
    public function deletePhoto($transientKey = '') {
            // Check permission
            $this->permission('Yaga.Ranks.Manage');

            $redirectUrl = 'rank/settings';

            if (Gdn::session()->validateTransientKey($transientKey)) {
                 Gdn::config()->saveToConfig('Yaga.Ranks.Photo', null, ['RemoveEmpty' => true]);
                $this->informMessage(Gdn::translate('Yaga.Rank.PhotoDeleted'));
            }

            if ($this->_DeliveryType == DELIVERY_TYPE_ALL) {
                redirectTo($redirectUrl);
            } else {
                $this->ControllerName = 'Home';
                $this->View = 'FileNotFound';
                $this->RedirectUrl = url($redirectUrl);
                $this->render();
            }
     }

     /**
        * You can manually award ranks to users for special cases
        *
        * @param int $userID
        * @throws Gdn_UserException
        */
     public function promote($userID) {
        // Check permission
        $this->permission('Yaga.Ranks.Add');
        $this->setHighlightRoute('rank/settings');

        // Only allow awarding if some ranks exist
        if (!$this->RankModel->getCount()) {
            throw new Gdn_UserException(Gdn::translate('Yaga.Error.NoRanks'));
        }

        $userModel = Gdn::userModel();
        $user = $userModel->getID($userID);

        $this->setData('Username', $user->Name);

        $ranks = $this->RankModel->get();
        $ranklist = [];
        foreach ($ranks as $rank) {
            $ranklist[$rank->RankID] = $rank->Name;
        }
        $this->setData('Ranks', $ranklist);

        if ($this->Form->isPostBack() == false) {
            // Add the user id field
            $this->Form->addHidden('UserID', $user->UserID);
        } else {
            $validation = new Gdn_Validation();
            $validation->applyRule('UserID', 'ValidateRequired');
            $validation->applyRule('RankID', 'ValidateRequired');
            if ($validation->validate($this->Request->post())) {
                $formValues = $this->Form->formValues();
                if ($this->Form->errorCount() == 0) {
                    $this->RankModel->set($formValues['RankID'], $formValues['UserID'], $formValues['RecordActivity']);
                    $userModel->setField($userID, 'RankProgression', $formValues['RankProgression']);
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
     * This takes in a sort array and updates the rank sort order.
     *
     * Renders the Save tree and/or the Result of the sort update.
     */
    public function sort() {
        // Check permission
        $this->permission('Yaga.Ranks.Manage');

        $request = Gdn::request();
        if ($request->isPostBack()) {
            $sortArray = $request->getValue('SortArray', null);
            $saves = $this->RankModel->saveSort($sortArray);
            $this->setData('Result', true);
            $this->setData('Saves', $saves);
        } else {
            $this->setData('Result', false);
        }

        $this->renderData();
    }
}
