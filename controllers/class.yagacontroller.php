<?php if (!defined('APPLICATION')) exit();

/* Copyright 2013 Zachary Doll */

/**
 * Manage the yaga application including configuration and import/export
 *
 * @since 1.0
 * @package Yaga
 */
class YagaController extends DashboardController {

    /**
     * @var array These objects will be created on instantiation and available via
     * $this->ObjectName
     */
    public $Uses = ['Form'];

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
            $this->Menu->highlightRoute('/yaga');
        }
        $this->setHighlightRoute('yaga/settings');

        $this->addCssFile('yaga.css');
        $this->removeCssFile('magnific-popup.css');
    }

    /**
     * Redirect to settings by default
     *
     * @since 1.0
     */
    public function index() {
        $this->settings();
    }

    /**
     * This handles all the core settings for the gamification application.
     *
     * @since 1.0
     */
    public function settings() {
        $this->permission('Garden.Settings.Manage');
        $this->title(Gdn::translate('Yaga.Settings'));

        // Get list of actions from the model and pass to the view
        $configModule = new ConfigurationModule($this);

        $configModule->initialize([
            'Yaga.Reactions.Enabled' => [
                'LabelCode' => 'Yaga.Reactions.Use',
                'Control' => 'Checkbox'
            ],
            'Yaga.Badges.Enabled' => [
                'LabelCode' => 'Yaga.Badges.Use',
                'Control' => 'Checkbox'
            ],
            'Yaga.Ranks.Enabled' => [
                'LabelCode' => 'Yaga.Ranks.Use',
                'Control' => 'Checkbox'
            ],
            'Yaga.MenuLinks.Show' => [
                'LabelCode' => 'Yaga.MenuLinks.Show',
                'Control' => 'Checkbox'
            ],
            'Yaga.LeaderBoard.Enabled' => [
                'LabelCode' => 'Yaga.LeaderBoard.Use',
                'Control' => 'Checkbox'
            ],
            'Yaga.LeaderBoard.Limit' => [
                'LabelCode' => 'Yaga.LeaderBoard.Max',
                'Control' => 'Textbox',
                'Options' => [
                    'Size' => 45
                ]
            ]
        ]);
        $this->ConfigurationModule = $configModule;

        $this->render('settings');
    }

    /**
     * Performs the necessary functions to change a backend controller into a
     * frontend controller
     *
     * @since 1.1
     */
    private function frontendStyle() {
        $this->removeCssFile('admin.css');
        unset($this->Assets['Panel']['SideMenuModule']);
        $this->addCssFile('style.css');
        $this->MasterView = 'default';

        $weeklyModule = new LeaderBoardModule();
        $weeklyModule->SlotType = 'w';
        $this->addModule($weeklyModule);
        $allTimeModule = new LeaderBoardModule();
        $this->addModule($allTimeModule);
    }

    /**
     * Displays a summary of ranks currently configured on a frontend page to help
     * users understand what is valued in this community
     *
     * @since 1.1
     */
    public function ranks() {
        $this->permission('Yaga.Ranks.View');
        $this->frontendStyle();
        $this->addCssFile('ranks.css');
        $this->title(Gdn::translate('Yaga.Ranks.All'));

        // Get list of ranks from the model and pass to the view
        $this->setData('Ranks', Yaga::rankModel()->get());

        $this->render('ranks');
    }

    /**
     * Displays a summary of badges currently configured on a frontend page to
     * help users understand what is valued in this community.
     *
     * Also provides a convenience redirect to badge details
     *
     * @param int $badgeID The badge ID you want to see details
     * @param string $slug The badge slug you want to see details
     * @since 1.1
     */
    public function badges($badgeID = false, $slug = null) {
        $this->permission('Yaga.Badges.View');
        $this->frontendStyle();
        $this->addCssFile('badges.css');
        $this->addModule('BadgesModule');

        if (is_numeric($badgeID)) {
            return $this->badgeDetail($badgeID, $slug);
        }

        $this->title(Gdn::translate('Yaga.Badges.All'));

        // Get list of badges from the model and pass to the view
        $userID = Gdn::session()->UserID;
        $allBadges = Yaga::badgeModel()->getWithEarned($userID);
        $this->setData('Badges', $allBadges);

        $this->render('badges');
    }

    /**
     * Displays information about the specified badge including recent recipients
     * of the badge.
     *
     * @param int $badgeID
     * @param string $slug
     */
    public function badgeDetail($badgeID, $slug = null) {
        $this->permission('Yaga.Badges.View');
        $badge = Yaga::badgeModel()->getByID($badgeID);

        if (!$badge) {
            throw NotFoundException('Badge');
        }

        $userID = Gdn::session()->UserID;
        $badgeAwardModel = Yaga::badgeAwardModel();
        $awardCount = $badgeAwardModel->getCount(['BadgeID' => $badgeID]);
        $userBadgeAward = $badgeAwardModel->exists($userID, $badgeID);
        $recentAwards = $badgeAwardModel->getRecent($badgeID);

        $this->setData('AwardCount', $awardCount);
        $this->setData('RecentAwards', $recentAwards);
        $this->setData('UserBadgeAward', $userBadgeAward);
        $this->setData('Badge', $badge);

        $this->title(Gdn::translate('Yaga.Badge.View').$badge->Name);

        $this->render('badgedetail');
    }

    /**
     * Import a Yaga transport file
     *
     * @since 1.0
     */
    public function import() {
        $this->title(Gdn::translate('Yaga.Import'));
        $this->setData('TransportType', 'Import');

        if (!class_exists('ZipArchive')) {
            $this->Form->addError(Gdn::translate('Yaga.Error.TransportRequirements'));
        }

        if ($this->Form->isPostBack() == true) {
            // Handle the file upload
            $upload = new Gdn_Upload();
            $tmpZip = $upload->validateUpload('FileUpload', false);

            $zipFile = false;
            if ($tmpZip) {
                // Generate the target name
                $targetFile = $upload->generateTargetName(PATH_UPLOADS, 'zip');
                $baseName = pathinfo($targetFile, PATHINFO_BASENAME);

                // Save the uploaded zip
                $parts = $upload->saveAs($tmpZip, $baseName);
                $zipFile = PATH_UPLOADS.'/'.$parts['SaveName'];
                $this->setData('TransportPath', $zipFile);
            }

            $include = $this->_findIncludes();
            if (count($include)) {
                $info = $this->_extractZip($zipFile);
                $this->_importData($info, $include);
                Gdn_FileSystem::removeFolder(PATH_UPLOADS.'/import/yaga');
            } else {
                $this->Form->addError(Gdn::translate('Yaga.Error.Includes'));
            }
        }

        if ($this->Form->errorCount() == 0 && $this->Form->isPostBack()) {
            $this->render('transport-success');
        } else {
            $this->render();
        }
    }

    /**
     * Create a Yaga transport file
     *
     * @since 1.0
     */
    public function export() {
        $this->title(Gdn::translate('Yaga.Export'));
        $this->setData('TransportType', 'Export');

        if (!class_exists('ZipArchive')) {
            $this->Form->addError(Gdn::translate('Yaga.Error.TransportRequirements'));
        }

        if ($this->Form->isPostBack()) {
            $include = $this->_findIncludes();
            if (count($include)) {
                $filename = $this->_exportData($include);
                $this->setData('TransportPath', $filename);
            } else {
                $this->Form->addError(Gdn::translate('Yaga.Error.Includes'));
            }
        }

        if ($this->Form->errorCount() == 0 && $this->Form->isPostBack()) {
            $this->render('transport-success');
        } else {
            $this->render('import');
        }
    }

    /**
     * This searches through the submitted checkboxes and constructs an array of
     * Yaga sections to be included in the transport file.
     *
     * @return array
     * @since 1.0
     */
    protected function _findIncludes() {
        $formValues = $this->Form->formValues();
        $sections = $formValues['Checkboxes'];

        // Figure out which boxes were checked
        $include = [];
        foreach ($sections as $section) {
            $include[$section] = $formValues[$section];
        }
        return $include;
    }

    /**
     * Creates a transport file for easily transferring Yaga configurations across
     * installs
     *
     * @param array An array containing the config areas to transfer
     * @param string Where to save the transport file
     * @return mixed false on failure, the path to the transport file on success
     * @since 1.0
     */
    protected function _exportData($include = [], $path = null) {
        $startTime = microtime(true);
        $info = new stdClass();
        $info->Version = Gdn::config('Yaga.Version', '?.?');
        $info->StartDate = date('Y-m-d H:i:s');

        if (is_null($path)) {
            $path = PATH_UPLOADS.'/export'.date('Y-m-d-His').'.yaga.zip';
        }
        $fh = new ZipArchive();
        $images = [];
        $hashes = [];

        if ($fh->open($path, ZipArchive::CREATE) !== true) {
            $this->Form->addError(sprintf(Gdn::translate('Yaga.Error.ArchiveCreate'), $fh->getStatusString()));
            return false;
        }

        // Add configuration items
        $info->Config = 'configs.yaga';
        $configs = Gdn::config('Yaga', []);
        unset($configs['Version']);
        $configData = serialize($configs);
        $fh->addFromString('configs.yaga', $configData);
        $hashes[] = md5($configData);

        // Add actions
        if ($include['Action']) {
            $info->Action = 'actions.yaga';
            $actions = Yaga::actionModel()->get('Sort', 'asc');
            $this->setData('ActionCount', count($actions));
            $actionData = serialize($actions);
            $fh->addFromString('actions.yaga', $actionData);
            $hashes[] = md5($actionData);
        }

        // Add ranks and associated image
        if ($include['Rank']) {
            $info->Rank = 'ranks.yaga';
            $ranks = Yaga::rankModel()->get('Level', 'asc');
            $this->setData('RankCount', count($ranks));
            $rankData = serialize($ranks);
            $fh->addFromString('ranks.yaga', $rankData);
            array_push($images, Gdn::config('Yaga.Ranks.Photo'), null);
            $hashes[] = md5($rankData);
        }

        // Add badges and associated images
        if ($include['Badge']) {
            $info->Badge = 'badges.yaga';
            $badges = Yaga::badgeModel()->get();
            $this->setData('BadgeCount', count($badges));
            $badgeData = serialize($badges);
            $fh->addFromString('badges.yaga', $badgeData);
            $hashes[] = md5($badgeData);
            foreach ($badges as $badge) {
                array_push($images, $badge->Photo);
            }
        }

        // Add in any images
        $filteredImages = array_filter($images);
        $imageCount = count($filteredImages);
        $this->setData('ImageCount', $imageCount);
        if ($imageCount > 0) {
            $fh->addEmptyDir('images');
        }

        foreach ($filteredImages as $image) {
            if ($fh->addFile('.'.$image, 'images/'.$image) === false) {
                $this->Form->addError(sprintf(Gdn::translate('Yaga.Error.AddFile'), $fh->getStatusString()));
                //return false;
            }
            $hashes[] = md5_file('.'.$image);
        }

        // Save all the hashes
        sort($hashes);
        $info->MD5 = md5(implode(',', $hashes));
        $info->EndDate = date('Y-m-d H:i:s');

        $endTime = microtime(true);
        $totalTime = $endTime - $startTime;
        $m = floor($totalTime / 60);
        $s = $totalTime - ($m * 60);

        $info->ElapsedTime = sprintf('%02d:%02.2f', $m, $s);

        $fh->setArchiveComment(serialize($info));
        if ($fh->close()) {
            return $path;
        } else {
            $this->Form->addError(sprintf(Gdn::translate('Yaga.Error.ArchiveSave'), $fh->getStatusString()));
            return false;
        }
    }

    /**
     * Extract the transport file and validate
     *
     * @param string The transport file path
     * @return boolean Whether or not the transport file was extracted successfully
     * @since 1.0
     */
    protected function _extractZip($filename) {
        if (!file_exists($filename)) {
            $this->Form->addError(Gdn::translate('Yaga.Error.FileDNE'));
			return false;
		}

        $zipFile = new ZipArchive();
        $result = $zipFile->open($filename);
        if ($result !== true) {
            $this->Form->addError(Gdn::translate('Yaga.Error.ArchiveOpen'));
            return false;
        }

        // Get the metadata from the comment
        $comment = $zipFile->comment;
        $metaData = unserialize($comment);

        $result = $zipFile->extractTo(PATH_UPLOADS.'/import/yaga');
        if ($result !== true) {
            $this->Form->addError(Gdn::translate('Yaga.Error.ArchiveExtract'));
            return false;
        }

        $zipFile->close();

        // Validate checksum
        if ($this->_validateChecksum($metaData) === true) {
            return $metaData;
        } else {
            $this->Form->addError(Gdn::translate('Yaga.Error.ArchiveChecksum'));
            return false;
        }
    }

    /**
     * Overwrites Yaga configurations, dumps Yaga db tables, inserts data via the
     * model, and copies uploaded files to the server
     *
     * @param stdClass The info object read in from the archive
     * @param array Which tables should be overwritten
     * @return bool Pass/Fail on the import being executed. Errors can exist on the
     * form with a passing return value.
     * @since 1.0
     */
    protected function _importData($info, $include) {
        if (!$info) {
            return false;
        }

        // Import Configs
        $configs = unserialize(file_get_contents(PATH_UPLOADS.'/import/yaga/'.$info->Config));
        $configurations = self::_nestedToDotNotation($configs, 'Yaga');
        foreach ($configurations as $name => $value) {
            Gdn::config()->saveToConfig($name, $value);
        }

        // Import model data
        foreach ($include as $key => $value) {
            // Trim "Yaga" prefix to make old imports work.
            $key = StringBeginsWith($key, 'Yaga', false, true);

            if ($value) {
                $data = unserialize(file_get_contents(PATH_UPLOADS.'/import/yaga/'.$info->$key));
                Gdn::sql()->emptyTable('Yaga'.$key);
                $modelName = $key.'Model';
                $model = Yaga::$modelName();
                foreach ($data as $datum) {
                    $model->insert((array)$datum);
                }
                $this->setData($key.'Count', $model->getCount());
            }
        }

        // Import uploaded files
        if (Gdn_FileSystem::copy(PATH_UPLOADS.'/import/yaga/images/uploads/', PATH_UPLOADS.'/') === false) {
            $this->Form->addError(Gdn::translate('Yaga.Error.TransportCopy'));
        }

        return true;
    }

    /**
     * Converted a nest config array into an array where indexes are the configuration
     * strings and the value is the value
     *
     * @param array The nested array
     * @param string What should the configuration strings be prefixed with
     * @return array
     * @since 1.0
     */
    protected static function _nestedToDotNotation($configs, $prefix = '') {
        $configStrings = [];

        foreach ($configs as $name => $value) {
            if (is_array($value)) {
                $configStrings = array_merge($configStrings, self::_nestedToDotNotation($value, "$prefix.$name"));
            } else {
                $configStrings["$prefix.$name"] = $value;
            }
        }

        return $configStrings;
    }

    /**
     * Inspects the Yaga transport files and calculates a checksum
     *
     * @param stdClass The metadata object read in from the transport file
     * @return boolean Whether or not the checksum is valid
     * @since 1.0
     */
    protected function _validateChecksum($metaData) {
        $hashes = [];

        // Hash the config file
        $hashes[] = md5_file(PATH_UPLOADS.'/import/yaga/'.$metaData->Config);

        // Hash the data files
        if (property_exists($metaData, 'Action')) {
            $hashes[] = md5_file(PATH_UPLOADS.'/import/yaga/'.$metaData->Action);
        }

        if (property_exists($metaData, 'Badge')) {
            $hashes[] = md5_file(PATH_UPLOADS.'/import/yaga/'.$metaData->Badge);
        }

        if (property_exists($metaData, 'Rank')) {
            $hashes[] = md5_file(PATH_UPLOADS.'/import/yaga/'.$metaData->Rank);
        }

        // Hash the image files
		$files = self::_getFiles(PATH_UPLOADS.'/import/yaga/images');
        $this->setData('ImageCount', count($files));
		foreach($files as $file) {
			$hashes[] = md5_file($file);
		}

        sort($hashes);
		$calculatedChecksum = md5(implode(',', $hashes));

        if ($calculatedChecksum != $metaData->MD5) {
            return false;
		}
		else {
            return true;
		}
    }

    /**
     * Returns a list of all files in a directory, recursively (Thanks @businessdad)
     *
     * @param string Directory The directory to scan for files
     * @return array A list of Files and, optionally, Directories.
     * @since 1.0
     */
    protected static function _getFiles($directory) {
        $files = array_diff(scandir($directory), ['.', '..']);
        $result = [];
        foreach ($files as $file) {
            $fileName = $directory.'/'.$file;
            if (is_dir($fileName)) {
                $result = array_merge($result, self::_getFiles($fileName));
                continue;
            }
            $result[] = $fileName;
        }
        return $result;
    }

}
