<?php if (!defined('APPLICATION')) exit();

use Yaga;

/* Copyright 2013-2014 Zachary Doll */

/**
 * A collection of hooks that are enabled when Yaga is.
 *
 * @package Yaga
 * @since 1.0
 */
class YagaPlugin extends Gdn_Plugin {

    /**
     * Redirect any old links to proper settings page permanently
     * @param SettingsController $sender
     */
    public function settingsController_yaga_Create($sender) {
        redirectTo('yaga/settings', 301);
    }

    /**
     * Add Simple stats page to dashboard index
     * @param SettingsController $sender
     */
    public function settingsController_afterRenderAsset_handler($sender) {
        $eventArguments = $sender->EventArguments;
        if ($eventArguments['AssetName'] == 'Content' && $sender->OriginalRequestMethod == 'index') {
            //echo 'Sweet sweet stats!';
            $badgeAwardModel = Yaga::badgeAwardModel();
            $reactionModel = Yaga::reactionModel();

            $badgeCount = $badgeAwardModel->getCount();
            $reactionCount = $reactionModel->getCount();
            echo wrap('Yaga Statistics', 'h1');
            echo wrap(
                wrap(
                    wrap(
                        'Badges'.wrap($badgeCount, 'strong'),
                        'div'
                    ), 'li', ['class' => 'BadgeCount']
                ).wrap(
                    wrap(
                        'Reactions'.wrap($reactionCount, 'strong'),
                        'div'
                    ), 'li', ['class' => 'ReactionCount']
                ),
                'ul',
                ['class' => 'StatsOverview']
            );
        }
    }

    /**
     * Add the settings page links
     *
     * @param Object $sender
     */
    public function base_GetAppSettingsMenuItems_handler($sender) {
        $menu = $sender->EventArguments['SideMenu'];
        $section = 'Gamification';
        $menu->addItem($section, $section);
        $menu->addLink($section, t('Settings'), 'yaga/settings', 'Garden.Settings.Manage');
        if (c('Yaga.Reactions.Enabled')) {
            $menu->addLink($section, t('Yaga.Reactions'), 'action/settings', 'Yaga.Reactions.Manage');
        }
        if (c('Yaga.Badges.Enabled')) {
            $menu->addLink($section, t('Yaga.Badges'), 'badge/settings', 'Yaga.Badges.Manage');
        }
        if (c('Yaga.Ranks.Enabled')) {
            $menu->addLink($section, t('Yaga.Ranks'), 'rank/settings', 'Yaga.Ranks.Manage');
        }
    }

    /**
     * Add a Best Content item to the discussion filters module
     *
     * @param mixed $sender
     * @return boolean
     */
    public function base_afterDiscussionFilters_handler($sender) {
        if (!c('Yaga.Reactions.Enabled')) {
            return false;
        }

        echo wrap(anchor(sprite('SpBestOf', 'SpMod Sprite').' '.t('Yaga.BestContent'), '/best'), 'li', ['class' => $sender->ControllerName == 'bestcontroller' ? 'Best Active' : 'Best']);
    }

    /**
     * Display the reaction counts on the profile page
     * @param ProfileController $sender
     */
    public function profileController_afterUserInfo_handler($sender) {
        if (!c('Yaga.Reactions.Enabled')) {
            return;
        }
        $user = $sender->User;
        $method = $sender->RequestMethod;
        if ($method == 'reactions') {
            $actionID = $sender->RequestArgs[2];
        }
        else {
            $actionID = -1;
        }
        echo '<div class="Yaga ReactionsWrap">';
        echo wrap(t('Yaga.Reactions', 'Reactions'), 'h2', ['class' => 'H']);

        // insert the reaction totals in the profile
        $reactionModel = Yaga::reactionModel();
        $actions = Yaga::actionModel()->get();
        $string = '';
        foreach ($actions as $action) {
            $selected = ($actionID == $action->ActionID) ? ' Selected' : '';
            $count = $reactionModel->getUserCount($user->UserID, $action->ActionID);
            $tempString = wrap(wrap(Gdn_Format::bigNumber($count), 'span', ['title' => $count]), 'span', ['class' => 'Yaga_ReactionCount CountTotal']);
            $tempString .= wrap($action->Name, 'span', ['class' => 'Yaga_ReactionName CountLabel']);

            $string .= wrap(wrap(anchor($tempString, '/profile/reactions/'.$user->UserID.'/'.Gdn_Format::url($user->Name).'/'.$action->ActionID, ['class' => 'Yaga_Reaction TextColor', 'title' => $action->Description]), 'span', ['class' => 'CountItem'.$selected]), 'span', ['class' => 'CountItemWrap']);
        }

        echo wrap($string, 'div', ['class' => 'DataCounts']);
        echo '</div>';
    }

    /**
     * Add the badge count into the user info module
     *
     * @param UserInfoModule $sender
     */
    public function userInfoModule_OnBasicInfo_handler($sender) {
        if (c('Yaga.Badges.Enabled')) {
            echo '<dt class="Badges">'.t('Yaga.Badges', 'Badges').'</dt> ';
            echo '<dd class="Badges">'.$sender->User->CountBadges.'</dd>';
        }
    }

    /**
     * This method shows the latest discussions/comments a user has posted that
     * received the specified action
     *
     * @param ProfileController $sender
     * @param int $userReference
     * @param string $username
     * @param int $actionID
     * @param int $page
     */
    public function profileController_Reactions_Create($sender, $userReference = '', $username = '', $actionID = '', $page = 0) {
        if (!c('Yaga.Reactions.Enabled')) {
            throw notFoundException();
        }

        list($offset, $limit) = offsetLimit($page, c('Yaga.ReactedContent.PerPage', 5));
        if (!is_numeric($offset) || $offset < 0) {
            $offset = 0;
        }

        $sender->editMode(false);

        // Tell the ProfileController what tab to load
        $sender->getUserInfo($userReference, $username);
        $sender->setTabView(T('Yaga.Reactions'), 'reactions', 'profile', 'Yaga');

        $sender->addJsFile('jquery.expander.js');
        $sender->addJsFile('reactions.js', 'yaga');
        $sender->addDefinition('ExpandText', t('(more)'));
        $sender->addDefinition('CollapseText', t('(less)'));

        $model = Yaga::actedModel();
        $data = $model->get($sender->User->UserID, $actionID, $limit, $offset);

        $sender->setData('Content', $data->Content);

        // Set the HandlerType back to normal on the profilecontroller so that it fetches it's own views
        $sender->HandlerType = HANDLER_TYPE_NORMAL;

        // Do not show discussion options
        $sender->ShowOptions = false;

        if ($sender->Head) {
            $sender->Head->addTag('meta', ['name' => 'robots', 'content' => 'noindex,noarchive']);
        }

        // Build a pager
        $baseUrl = 'profile/reactions/'.$sender->User->UserID.'/'.Gdn_Format::url($sender->User->Name).'/'.$actionID;
        $pagerFactory = new Gdn_PagerFactory();
        $sender->Pager = $pagerFactory->getPager('Pager', $sender);
        $sender->Pager->ClientID = 'Pager';
        $sender->Pager->configure(
                        $offset, $limit, $data->TotalRecords, $baseUrl.'/%1$s/'
        );

        // Add the specific action to the breadcrumbs
        $action = Yaga::actionModel()->getID($actionID);
        if ($action) {
            $sender->_SetBreadcrumbs($action->Name, $baseUrl);
        }

        // Render the ProfileController
        $sender->render();
    }

    /**
     * This method shows the highest scoring discussions/comments a user has ever posted
     *
     * @param ProfileController $sender
     * @param int $userReference
     * @param string $username
     * @param int $page
     */
    public function profileController_Best_Create($sender, $userReference = '', $username = '', $page = 0) {
        if (!c('Yaga.Reactions.Enabled')) {
            return;
        }

        list($offset, $limit) = offsetLimit($page, c('Yaga.BestContent.PerPage', 10));
        if (!is_numeric($offset) || $offset < 0) {
            $offset = 0;
        }

        $sender->editMode(false);

        // Tell the ProfileController what tab to load
        $sender->getUserInfo($userReference, $username);
        $sender->_SetBreadcrumbs(t('Yaga.BestContent'), userUrl($sender->User, '', 'best'));
        $sender->setTabView(T('Yaga.BestContent'), 'best', 'profile', 'Yaga');

        $sender->addJsFile('jquery.expander.js');
        $sender->addJsFile('reactions.js', 'yaga');
        $sender->addDefinition('ExpandText', t('(more)'));
        $sender->addDefinition('CollapseText', t('(less)'));

        $model = Yaga::actedModel();
        $data = $model->getBest($sender->User->UserID, $limit, $offset);

        $sender->setData('Content', $data->Content);

        // Set the HandlerType back to normal on the profilecontroller so that it fetches it's own views
        $sender->HandlerType = HANDLER_TYPE_NORMAL;

        // Do not show discussion options
        $sender->ShowOptions = false;

        if ($sender->Head) {
            $sender->Head->addTag('meta', ['name' => 'robots', 'content' => 'noindex,noarchive']);
        }

        // Build a pager
        $pagerFactory = new Gdn_PagerFactory();
        $sender->Pager = $pagerFactory->getPager('Pager', $sender);
        $sender->Pager->ClientID = 'Pager';
        $sender->Pager->configure(
                        $offset, $limit, $data->TotalRecords, 'profile/best/'.$sender->User->UserID.'/'.Gdn_Format::url($sender->User->Name).'/%1$s/'
        );

        // Render the ProfileController
        $sender->render();
    }

    /**
     * Add a best content tab on a user's profile
     * @param ProfileController $sender
     */
    public function profileController_AddProfileTabs_handler($sender) {
        // Only show this to users who are signed in as this may be duplicate content to crawlers (pioc92).
        if (is_object($sender->User) && $sender->User->UserID > 0 && Gdn::session()->isValid()) {
            $sender->addProfileTab(sprite('SpBestOf', 'SpMod Sprite').' '.t('Yaga.BestContent'), 'profile/best/'.$sender->User->UserID.'/'.urlencode($sender->User->Name), 'Best');
        }
    }

    /**
     * Check for rank progress when the user model gets updated
     *
     * @param UserModel $sender
     */
    public function userModel_afterSetField_handler($sender) {
        // Don't check for promotions if we aren't using ranks
        if (!c('Yaga.Ranks.Enabled')) {
            return;
        }

        $fields = $sender->EventArguments['Fields'];
        $fieldHooks = ['Points', 'CountDiscussions', 'CountComments'];

        foreach ($fieldHooks as $fieldHook) {
            if (array_key_exists($fieldHook, $fields)) {
                $userID = $sender->EventArguments['UserID'];
                $this->rankProgression($userID);
                break; // Only need to fire once per event
            }
        }
    }

    /**
     * Update a user's rank id if they qualify
     *
     * @param int $userID
     */
    protected function rankProgression($userID) {
        $userModel = Gdn::userModel();
        $user = $userModel->getID($userID);

        // Don't try to promote if they are frozen
        if (!$user->RankProgression) {
            return;
        }

        $rankModel = Yaga::rankModel();
        $rank = $rankModel->getHighestQualifyingRank($user);

        if ($rank && $rank->RankID != $user->RankID) {
            // Only promote automatically
            $oldRank = $rankModel->getByID($user->RankID);
            if ($oldRank->Sort < $rank->Sort) {
                $rankModel->set($rank->RankID, $userID, true);
            }
        }
    }

    /**
     * Add the badge and rank notification options
     *
     * @param ProfileController $sender
     */
    public function profileController_afterPreferencesDefined_handler($sender) {
        if (c('Yaga.Badges.Enabled')) {
            $sender->Preferences['Notifications']['Email.BadgeAward'] = t('Yaga.Badges.Notify');
            $sender->Preferences['Notifications']['Popup.BadgeAward'] = t('Yaga.Badges.Notify');
        }

        if (c('Yaga.Ranks.Enabled')) {
            $sender->Preferences['Notifications']['Email.RankPromotion'] = t('Yaga.Ranks.Notify');
            $sender->Preferences['Notifications']['Popup.RankPromotion'] = t('Yaga.Ranks.Notify');
        }
    }

    /**
     * Add the Award Badge and Promote options to the profile controller
     *
     * @param ProfileController $sender
     */
    public function profileController_beforeProfileOptions_handler($sender) {
        if (Gdn::session()->isValid()) {
            if (c('Yaga.Badges.Enabled') && checkPermission('Yaga.Badges.Add')) {
                $sender->EventArguments['ProfileOptions'][] = [
                        'Text' => sprite('SpBadge', 'SpMod Sprite').' '.t('Yaga.Badge.Award'),
                        'Url' => '/badge/award/'.$sender->User->UserID,
                        'CssClass' => 'Popup'
                ];
            }

            if (c('Yaga.Ranks.Enabled') && checkPermission('Yaga.Ranks.Add')) {
                $sender->EventArguments['ProfileOptions'][] = [
                        'Text' => sprite('SpMod').' '.t('Yaga.Rank.Promote'),
                        'Url' => '/rank/promote/'.$sender->User->UserID,
                        'CssClass' => 'Popup'
                ];
            }
        }
    }

    /**
     * Display a record of reactions after the first post
     *
     * @param DiscussionController $sender
     */
    public function discussionController_afterDiscussionBody_handler($sender) {
        if (!Gdn::session()->checkPermission('Yaga.Reactions.View') || !c('Yaga.Reactions.Enabled')) {
            return;
        }
        $type = 'discussion';
        $iD = $sender->DiscussionID;
        echo renderReactionRecord($iD, $type);
    }

    /**
     * Display a record of reactions after comments
     * @param DiscussionController $sender
     */
    public function discussionController_afterCommentBody_handler($sender) {
        if (!Gdn::session()->checkPermission('Yaga.Reactions.View') || !c('Yaga.Reactions.Enabled')) {
            return;
        }
        $type = 'comment';
        $iD = $sender->EventArguments['Comment']->CommentID;
        echo renderReactionRecord($iD, $type);
    }

    /**
     * Add action list to discussion items
     * @param DiscussionController $sender
     */
    public function discussionController_afterReactions_handler($sender) {
        if (c('Yaga.Reactions.Enabled') == false) {
            return;
        }

        // check to see if allowed to add reactions
        if (!Gdn::session()->checkPermission('Yaga.Reactions.Add')) {
            return;
        }

        // Users shouldn't be able to react to their own content
        $type = $sender->EventArguments['RecordType'];
        $iD = $sender->EventArguments['RecordID'];

        if (array_key_exists('Author', $sender->EventArguments)) {
            $author = $sender->EventArguments['Author'];
            $authorID = $author->UserID;
        }
        else {
            $discussion = $sender->EventArguments['Discussion'];
            $authorID = $discussion->InsertUserID;
        }

        // Users shouldn't be able to react to their own content
        if (Gdn::session()->UserID != $authorID) {
            echo renderReactionList($iD, $type);
        }
    }

    /**
     * Add the action list to any activity items that can be commented on
     *
     * @param ActivityController $sender
     */
    public function activityController_afterActivityBody_handler($sender) {
        if (!c('Yaga.Reactions.Enabled')) {
            return;
        }
        $activity = $sender->EventArguments['Activity'];
        $currentUserID = Gdn::session()->UserID;
        $type = 'activity';
        $iD = $activity->ActivityID;

        // Only allow reactions on activities that allow comments
        if (!property_exists($activity, 'AllowComments') || $activity->AllowComments == 0) {
            return;
        }

        // check to see if allowed to add reactions
        if (!Gdn::session()->checkPermission('Yaga.Reactions.Add')) {
            return;
        }

        if ($currentUserID == $activity->RegardingUserID) {
            // The current user made this activity item happen
        }
        else {
            echo wrap(renderReactionList($iD, $type), 'div', ['class' => 'Reactions']);
        }
    }

    /**
     * Apply any applicable rank perks when the session first starts.
     * @param UserModel $sender
     */
    public function userModel_afterGetSession_handler($sender) {
        if (!c('Yaga.Ranks.Enabled')) {
            return;
        }

        $user = &$sender->EventArguments['User'];
        $rankID = $user->RankID;
        if (is_null($rankID)) {
            return;
        }

        $rankModel = Yaga::rankModel();
        $perks = $rankModel->getPerks($rankID);

        // Apply all the perks
        foreach ($perks as $perk => $perkValue) {
            $perkType = substr($perk, 0, 4);
            $perkKey = substr($perk, 4);

            if ($perkType === 'Conf') {
                $this->applyCustomConfigs($perkKey, $perkValue);
            }
            else if ($perkType === 'Perm' && $perkValue === 'grant') {
                $this->grantPermission($user, $perkKey);
            }
            else if ($perkType === 'Perm' && $perkValue === 'revoke') {
                $this->revokePermission($user, $perkKey);
            }
            else {
                // Do nothing
                // TODO: look into firing a custom event
            }
        }
    }

    /**
     * Gives the specified permission to a user, regardless of current role.
     * @param type $user
     * @param string $permission
     */
    private function grantPermission($user, $permission = '') {
        if ($permission === '') {
            return;
        }

        if (!is_array($user->Permissions)) {
            $tempPerms = unserialize($user->Permissions);
            if (!in_array($permission, $tempPerms)) {
                $tempPerms[] = $permission;
                $user->Permissions = serialize($tempPerms);
            }
        }
        else {
            $tempPerms =& $user->Permissions;
            $tempPerms[] = $permission;
        }
    }

    /**
     * Removes the specified permission from a user, regardless of current role.
     *
     * Cannot be used to override $user->Admin = 1 permissions
     *
     * @param type $user
     * @param string $permission
     */
    private function revokePermission($user, $permission = '') {
        if ($permission === '') {
            return;
        }

        if (!is_array($user->Permissions)) {
            $tempPerms = unserialize($user->Permissions);
            $key = array_search($permission, $tempPerms);
            if ($key) {
                unset($tempPerms[$key]);
                $user->Permissions = serialize($tempPerms);
            }
        }
        else {
            $tempPerms =& $user->Permissions;
            $key = array_search($permission, $tempPerms);
            if ($key) {
                unset($tempPerms[$key]);
            }
        }
    }

    /**
     * Apply custom configuration from rank perks in memory only.
     * @param string $name
     * @param mixed $value
     */
    private function applyCustomConfigs($name = null, $value = null) {
        saveToConfig('Yaga.ConfBackup.'.$name, c($name, null), ['Save' => false]);
        saveToConfig($name, $value, ['Save' => false]);
    }

    /**
     * Insert JS and CSS files into the appropiate controllers
     *
     * @param ProfileController $sender
     */
    public function profileController_render_before($sender) {
        $this->addResources($sender);

        if (c('Yaga.Badges.Enabled')) {
            $sender->addModule('BadgesModule');
        }
    }

    /**
     * Insert JS and CSS files into the appropiate controllers and fill the reaction cache
     *
     * @param DiscussionController $sender
     */
    public function discussionController_render_before($sender) {
        $this->addResources($sender);
        if (c('Yaga.Reactions.Enabled')) {
            if ($sender->data('Discussion')) {
                Yaga::reactionModel()->prefetch('discussion', $sender->Data['Discussion']->DiscussionID);
            }
            if (isset($sender->Data['Comments'])) {
                $commentIDs = array_column($sender->Data['Comments']->resultArray(), 'CommentID');
                // set the DataSet type back to "object"
                $sender->Data['Comments']->dataSetType(DATASET_TYPE_OBJECT);
                Yaga::reactionModel()->prefetch('comment', $commentIDs);
            }
        }
    }

    /**
     * Insert JS and CSS files into the appropiate controllers
     *
     * @since 1.1
     * @param DiscussionsController $sender
     */
    public function discussionsController_render_before($sender) {
        $this->addResources($sender);
    }

    /**
     * Insert JS and CSS files into the appropiate controllers
     *
     * @param CommentController $sender
     */
    public function commentController_render_before($sender) {
        $this->addResources($sender);
    }

    /**
     * Insert JS and CSS files into the appropiate controllers
     *
     * @param ActivityController $sender
     */
    public function activityController_render_before($sender) {
        $this->addResources($sender);

        if (c('Yaga.LeaderBoard.Enabled', false)) {
            // add leaderboard modules to the activity page
            $module = new LeaderBoardModule();
            $module->SlotType = 'w';
            $sender->addModule($module);
            $module = new LeaderBoardModule();
            $sender->addModule($module);
        }
    }

    /**
     * Check for Badge Awards
     *
     * @param Gdn_Dispatcher $sender
     */
    public function gdn_Dispatcher_appStartup_handler($sender) {
        Yaga::executeBadgeHooks($sender, __FUNCTION__);
    }

    /**
     * Check for Badge Awards
     *
     * @param mixed $sender
     */
    public function base_afterGetSession_handler($sender) {
        Yaga::executeBadgeHooks($sender, __FUNCTION__);
    }

    /**
     * Check for Badge Awards
     *
     * @param CommentModel $sender
     */
    public function commentModel_afterSaveComment_handler($sender) {
        Yaga::executeBadgeHooks($sender, __FUNCTION__);
    }

    /**
     * Check for Badge Awards
     *
     * @param DiscussionModel $sender
     */
    public function discussionModel_afterSaveDiscussion_handler($sender) {
        Yaga::executeBadgeHooks($sender, __FUNCTION__);
    }

    /**
     * Check for Badge Awards
     *
     * @param ActivityModel $sender
     */
    public function activityModel_beforeSaveComment_handler($sender) {
        Yaga::executeBadgeHooks($sender, __FUNCTION__);
    }

    /**
     * Check for Badge Awards
     *
     * @param CommentModel $sender
     */
    public function commentModel_beforeNotification_handler($sender) {
        Yaga::executeBadgeHooks($sender, __FUNCTION__);
    }

    /**
     * Check for Badge Awards
     *
     * @param DiscussionModel $sender
     */
    public function discussionModel_beforeNotification_handler($sender) {
        Yaga::executeBadgeHooks($sender, __FUNCTION__);
    }

    /**
     * Check for Badge Awards
     *
     * @param mixed $sender
     */
    public function base_afterSignIn_handler($sender) {
        Yaga::executeBadgeHooks($sender, __FUNCTION__);
    }

    /**
     * Check for Badge Awards
     *
     * @param UserModel $sender
     */
    public function userModel_afterSave_handler($sender) {
        Yaga::executeBadgeHooks($sender, __FUNCTION__);
    }

    /**
     * Check for Badge Awards
     *
     * @param ReactionModel $sender
     */
    public function reactionModel_afterReactionSave_handler($sender) {
        Yaga::executeBadgeHooks($sender, __FUNCTION__);
    }

    /**
     * Check for Badge Awards
     *
     * @param BadgeAwardModel $sender
     */
    public function badgeAwardModel_afterBadgeAward_handler($sender) {
        Yaga::executeBadgeHooks($sender, __FUNCTION__);
    }

    /**
     * Check for Badge Awards
     *
     * @param mixed $sender
     */
    public function base_afterConnection_handler($sender) {
        Yaga::executeBadgeHooks($sender, __FUNCTION__);
    }

    /**
     * Add the appropriate resources for each controller
     *
     * @param Gdn_Controller $sender
     */
    private function addResources($sender) {
        $sender->addCssFile('reactions.css', 'yaga');
    }

    /**
     * Add global Yaga resources to all dashboard pages
     *
     * @param Gdn_Controller $sender
     */
    public function base_render_before($sender) {
        if ($sender->MasterView == 'admin') {
            $sender->addCssFile('yaga.css', 'yaga');
        }
        else {
            if (Gdn::session()->isValid() && is_object($sender->Menu) && c('Yaga.MenuLinks.Show')) {
                $this->addMenuLinks($sender->Menu);
            }
        }
    }

    /**
     * Adds links to the frontend
     *
     * @since 1.1
     * @param MenuModule $menu
     */
    protected function addMenuLinks($menu) {
        if (c('Yaga.Badges.Enabled')) {
            $menu->addLink('Yaga', t('Badges'), 'yaga/badges');
        }
        if (c('Yaga.Ranks.Enabled')) {
            $menu->addLink('Yaga', t('Ranks'), 'yaga/ranks');
        }
    }

    /**
     * Delete all of the Yaga related information for a specific user.
     *
     * @param int $userID The ID of the user to delete.
     * @param array $options An array of options:
     *    - DeleteMethod: One of delete, wipe, or null
     * @param array $data
     *
     * @since 1.0
     */
     protected function deleteUserData($userID, $options = [], &$data = null) {
        $sQL = Gdn::sql();

        $deleteMethod = val('DeleteMethod', $options, 'delete');
        if ($deleteMethod == 'delete') {
            // Remove neutral/negative reactions
            $actions = Yaga::actionModel()->getWhere(['AwardValue <' => 1])->result();
            foreach ($actions as $negative) {
                Gdn::userModel()->getDelete('Reaction', ['InsertUserID' => $userID, 'ActionID' => $negative->ActionID], $data);
            }
        }
        else if ($deleteMethod == 'wipe') {
            // Completely remove reactions
            Gdn::userModel()->getDelete('Reaction', ['InsertUserID' => $userID], $data);
        }
        else {
            // Leave reactions
        }

        // Remove the reactions they have received
        Gdn::userModel()->getDelete('Reaction', ['ParentAuthorID' => $userID], $data);

        // Remove their badges
        Gdn::userModel()->getDelete('BadgeAward', ['UserID' => $userID], $data);

        // Blank the user's yaga information
        $sQL->update('User')
            ->set([
                'CountBadges' => 0,
                'RankID' => null,
                'RankProgression' => 0
            ])
            ->where('UserID', $userID)
            ->put();

        // Trigger a system wide point recount
        // TODO: Look into point re-calculation
    }

    /**
	 * Remove Yaga data when deleting a user.
        *
        * @since 1.0
        * @package Yaga
        *
        * @param UserModel $sender UserModel.
        */
     public function userModel_beforeDeleteUser_handler($sender) {
            $userID = val('UserID', $sender->EventArguments);
            $options = val('Options', $sender->EventArguments, []);
            $options = is_array($options) ? $options : [];
            $content =& $sender->EventArguments['Content'];

            $this->deleteUserData($userID, $options, $content);
     }

    /**
     * Add update routines to the DBA controller
     *
     * @param DbaController $sender
     */
    public function dbaController_CountJobs_handler($sender) {
        $counts = [
            'BadgeAward' => ['CountBadges']
        ];

        foreach ($counts as $table => $columns) {
            foreach ($columns as $column) {
                $name = "Recalculate $table.$column";
                $url = "/dba/counts.json?".http_build_query(['table' => $table, 'column' => $column]);

                $sender->Data['Jobs'][$name] = $url;
            }
        }
    }

    /**
     * Run the structure and stub scripts if necessary when the application is
     * enabled.
     */
    public function setup() {
        $config = Gdn::factory(Gdn::AliasConfig);
        $drop = false;
        $explicit = true;
        include(PATH_PLUGINS.DS.'yaga'.DS.'settings'.DS.'structure.php');
        include(PATH_PLUGINS.DS.'yaga'.DS.'settings'.DS.'stub.php');
    }

    /**
     * Restore rank specific custom configs to site defaults.
     *
     * The rank feature allows custom config settings. In order to show divergent
     * site settings correctly, those custom config settings have to be replaced
     * by the original ones.
     *
     * @param SettingsController $sender Instance of the calling class.
     *
     * @return void.
     */
    public function settingsController_render_before($sender) {
        // If Ranks feature isn't used, there's nothing to do here.
        if (!c('Yaga.Ranks.Enabled') == true) {
            return;
        }
        // Restore backed up configs.
        if (c('Yaga.ConfBackup')) {
            Gdn::config()->loadArray(c('Yaga.ConfBackup'), 'plugins/yaga');
        }
    }
}
