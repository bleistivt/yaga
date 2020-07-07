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
    public $Uses = ['Form', 'ActionModel', 'BadgeModel', 'RankModel', 'BadgeAwardModel'];

    /**
     * Make this look like a dashboard page and add the resources
     *
     * @since 1.0
     * @access public
     */
    public function initialize() {
        parent::initialize();
        $this->Application = 'Yaga';
        $this->ApplicationFolder = 'yaga';
        Gdn_Theme::section('Dashboard');
        if ($this->Menu) {
            $this->Menu->highlightRoute('/yaga');
        }
        $this->setHighlightRoute('yaga/settings');

        $this->addCssFile('yaga.css', 'plugins/yaga');
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
                'Control' => 'toggle'
            ],
            'Yaga.Badges.Enabled' => [
                'LabelCode' => 'Yaga.Badges.Use',
                'Control' => 'toggle'
            ],
            'Yaga.Ranks.Enabled' => [
                'LabelCode' => 'Yaga.Ranks.Use',
                'Control' => 'toggle'
            ],
            'Yaga.MenuLinks.Show' => [
                'LabelCode' => 'Yaga.MenuLinks.Show',
                'Control' => 'toggle'
            ],
            'Yaga.LeaderBoard.Enabled' => [
                'LabelCode' => 'Yaga.LeaderBoard.Use',
                'Control' => 'toggle'
            ],
            'Yaga.LeaderBoard.Limit' => [
                'LabelCode' => 'Yaga.LeaderBoard.Max',
                'Control' => 'Textbox',
                'Options' => ['type' => 'number']
            ]
        ]);
        $this->ConfigurationModule = $configModule;

        $this->render('settings', '', 'plugins/yaga');
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

        $weeklyModule = new LeaderBoardModule($this);
        $weeklyModule->setSlotType('w');
        $this->addModule($weeklyModule);

        $allTimeModule = new LeaderBoardModule($this);
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
        $this->addCssFile('ranks.css', 'plugins/yaga');
        $this->title(Gdn::translate('Yaga.Ranks.All'));

        // Get list of ranks from the model and pass to the view
        $this->setData('Ranks', $this->RankModel->get());

        $this->render('ranks', '', 'plugins/yaga');
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
        $this->addCssFile('badges.css', 'plugins/yaga');
        $this->addModule('BadgesModule');

        if (is_numeric($badgeID)) {
            return $this->badgeDetail($badgeID, $slug);
        }

        $this->title(Gdn::translate('Yaga.Badges.All'));

        // Get list of badges from the model and pass to the view
        $userID = Gdn::session()->UserID;
        $allBadges = $this->BadgeAwardModel->getWithEarned($userID);
        $this->setData('Badges', $allBadges);

        $this->render('badges', '', 'plugins/yaga');
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
        $badge = $this->BadgeModel->getID($badgeID);

        if (!$badge) {
            throw NotFoundException('Badge');
        }

        $userID = Gdn::session()->UserID;
        $awardCount = $this->BadgeAwardModel->getCount(['BadgeID' => $badgeID]);
        $userBadgeAward = $this->BadgeAwardModel->getWhere(['BadgeID' => $badgeID, 'UserID' => $userID])->firstRow();
        $recentAwards = $this->BadgeAwardModel->getRecent($badgeID);

        $this->setData('AwardCount', $awardCount);
        $this->setData('RecentAwards', $recentAwards);
        $this->setData('UserBadgeAward', $userBadgeAward);
        $this->setData('Badge', $badge);

        $this->title(Gdn::translate('Yaga.Badge.View').$badge->Name);

        $this->render('badgedetail', '', 'plugins/yaga');
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
            $upload->allowFileExtension('zip');
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
            $this->render('transport-success', '', 'plugins/yaga');
        } else {
            $this->render('import', '', 'plugins/yaga');
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
            $this->render('import', '', 'plugins/yaga');
        }
    }

    /**
     * Proxy endpoint for DBA methods. See YagaPlugin::dbaController_countJobs_handler
     *
     * @param string $method
     */
    public function dba($method = '', $from = false, $to = false) {
        $this->permission('Garden.Settings.Manage');
        $data = [];

        if ($method === 'countbadges') {
            $data = Gdn::getContainer()->get(BadgeAwardModel::class)->counts('CountBadges');
        } elseif ($method === 'userpoints') {
            $data = Gdn::getContainer()->get(BadgeAwardModel::class)->counts('Points');
        } elseif ($method === 'latestreaction') {
            $data = Gdn::getContainer()->get(ReactionModel::class)->counts('Latest', $from, $to);
        }

        $this->setData('Result', $data);
        $this->renderData();
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
        $info = [];
        $info['Version'] = Gdn::config('Yaga.Version', '?.?');
        $info['StartDate'] = date('Y-m-d H:i:s');

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
        $info['Config'] = 'configs.json';
        $configs = Gdn::config('Yaga', []);
        unset($configs['Version']);
        $configData = dbencode($configs);
        $fh->addFromString('configs.json', $configData);
        $hashes[] = md5($configData);

        // Add actions
        if ($include['Action']) {
            $info['Action'] = 'actions.json';
            $actions = $this->ActionModel()->get('Sort', 'asc');
            $this->setData('ActionCount', count($actions));
            $actionData = dbencode($actions);
            $fh->addFromString('actions.json', $actionData);
            $hashes[] = md5($actionData);
        }

        // Add ranks and associated image
        if ($include['Rank']) {
            $info['Rank'] = 'ranks.json';
            $ranks = $this->RankModel()->get('Level', 'asc');
            $this->setData('RankCount', count($ranks));
            $rankData = dbencode($ranks);
            $fh->addFromString('ranks.json', $rankData);
            array_push($images, Gdn::config('Yaga.Ranks.Photo'), null);
            $hashes[] = md5($rankData);
        }

        // Add badges and associated images
        if ($include['Badge']) {
            $info['Badge'] = 'badges.json';
            $badges = $this->BadgeModel()->get();
            $this->setData('BadgeCount', count($badges));
            $badgeData = dbencode($badges);
            $fh->addFromString('badges.json', $badgeData);
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
            $image = ltrim($image, '/');
            if ($fh->addFile('./'.$image, 'images/'.$image) === false) {
                $this->Form->addError(sprintf(Gdn::translate('Yaga.Error.AddFile'), $fh->getStatusString()));
                //return false;
            }
            $hashes[] = md5_file('./'.$image);
        }

        // Save all the hashes
        sort($hashes);
        $info['MD5'] = md5(implode(',', array_unique($hashes)));
        $info['EndDate'] = date('Y-m-d H:i:s');

        $endTime = microtime(true);
        $totalTime = $endTime - $startTime;
        $m = floor($totalTime / 60);
        $s = $totalTime - ($m * 60);

        $info['ElapsedTime'] = sprintf('%02d:%02.2f', $m, $s);

        $fh->setArchiveComment(dbencode($info));
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
        $metaData = (array)dbdecode($comment);

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
        $configs = dbdecode(file_get_contents(PATH_UPLOADS.'/import/yaga/'.$info['Config']));
        $configurations = self::_nestedToDotNotation($configs, 'Yaga');
        foreach ($configurations as $name => $value) {
            Gdn::config()->saveToConfig($name, $value);
        }

        // Import model data
        foreach ($include as $key => $value) {
            // Trim "Yaga" prefix to make old imports work.
            $key = StringBeginsWith($key, 'Yaga', false, true);

            if ($value) {
                $data = dbdecode(file_get_contents(PATH_UPLOADS.'/import/yaga/'.$info[$key]));
                Gdn::sql()->emptyTable('Yaga'.$key);
                $modelName = $key.'Model';
                $model = Gdn::getContainer()->get($modelName);
                foreach ($data as $datum) {
                    $model->insert((array)$datum);
                }
                $this->setData($key.'Count', $model->getCount());
            }
        }

        // Import uploaded files
        $path = PATH_UPLOADS.'/import/yaga/images/uploads/';
        if (file_exists($path) && Gdn_FileSystem::copy($path, PATH_UPLOADS.'/') === false) {
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
        $hashes[] = md5_file(PATH_UPLOADS.'/import/yaga/'.$metaData['Config']);

        // Hash the data files
        if (array_key_exists('Action', $metaData)) {
            $hashes[] = md5_file(PATH_UPLOADS.'/import/yaga/'.$metaData['Action']);
        }

        if (array_key_exists('Badge', $metaData)) {
            $hashes[] = md5_file(PATH_UPLOADS.'/import/yaga/'.$metaData['Badge']);
        }

        if (array_key_exists('Rank', $metaData)) {
            $hashes[] = md5_file(PATH_UPLOADS.'/import/yaga/'.$metaData['Rank']);
        }

        // Hash the image files
		$files = self::_getFiles(PATH_UPLOADS.'/import/yaga/images');
        $this->setData('ImageCount', count($files));
		foreach ($files as $file) {
			$hashes[] = md5_file($file);
		}

        sort($hashes);
		$calculatedChecksum = md5(implode(',', array_unique($hashes)));

        return $calculatedChecksum == $metaData['MD5'];
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
