<?php if (!defined('APPLICATION')) exit();

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
    public function settingsController_yaga_Create(\SettingsController $sender) {
        redirectTo('yaga/settings', 301);
    }

    /**
     * Add Simple stats page to dashboard index
     * @param SettingsController $sender
     */
    public function settingsController_afterRenderAsset_handler(\SettingsController $sender) {
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
        $menu->addLink($section, Gdn::translate('Settings'), 'yaga/settings', 'Garden.Settings.Manage');
        if (Gdn::config('Yaga.Reactions.Enabled')) {
            $menu->addLink($section, Gdn::translate('Yaga.Reactions'), 'action/settings', 'Yaga.Reactions.Manage');
        }
        if (Gdn::config('Yaga.Badges.Enabled')) {
            $menu->addLink($section, Gdn::translate('Yaga.Badges'), 'badge/settings', 'Yaga.Badges.Manage');
        }
        if (Gdn::config('Yaga.Ranks.Enabled')) {
            $menu->addLink($section, Gdn::translate('Yaga.Ranks'), 'rank/settings', 'Yaga.Ranks.Manage');
        }
    }

    /**
     * Add a Best Content item to the discussion filters module
     *
     * @param mixed $sender
     * @return boolean
     */
    public function base_afterDiscussionFilters_handler($sender) {
        if (!Gdn::config('Yaga.Reactions.Enabled')) {
            return false;
        }

        echo wrap(anchor(sprite('SpBestOf', 'SpMod Sprite').' '.Gdn::translate('Yaga.BestContent'), '/best'), 'li', ['class' => $sender->ControllerName == 'bestcontroller' ? 'Best Active' : 'Best']);
    }

    /**
     * Display the reaction counts on the profile page
     * @param ProfileController $sender
     */
    public function profileController_afterUserInfo_handler(\ProfileController $sender) {
        if (!Gdn::config('Yaga.Reactions.Enabled')) {
            return;
        }
        $user = $sender->User;
        $method = $sender->RequestMethod;
        if ($method == 'reactions') {
            $actionID = $sender->RequestArgs[2];
        } else {
            $actionID = -1;
        }
        echo '<div class="Yaga ReactionsWrap">';
        echo wrap(Gdn::translate('Yaga.Reactions', 'Reactions'), 'h2', ['class' => 'H']);

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
    public function userInfoModule_OnBasicInfo_handler(\UserInfoModule $sender) {
        if (Gdn::config('Yaga.Badges.Enabled')) {
            echo '<dt class="Badges">'.Gdn::translate('Yaga.Badges', 'Badges').'</dt> ';
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
    public function profileController_Reactions_Create(\ProfileController $sender, $userReference = '', $username = '', $actionID = '', $page = 0) {
        if (!Gdn::config('Yaga.Reactions.Enabled')) {
            throw notFoundException();
        }

        list($offset, $limit) = offsetLimit($page, Gdn::config('Yaga.ReactedContent.PerPage', 5));
        if (!is_numeric($offset) || $offset < 0) {
            $offset = 0;
        }

        $sender->editMode(false);

        // Tell the ProfileController what tab to load
        $sender->getUserInfo($userReference, $username);
        $sender->setTabView(Gdn::translate('Yaga.Reactions'), 'reactions', 'profile', 'Yaga');

        $sender->addJsFile('jquery.expander.js');
        $sender->addJsFile('reactions.js', 'yaga');
        $sender->addDefinition('ExpandText', Gdn::translate('(more)'));
        $sender->addDefinition('CollapseText', Gdn::translate('(less)'));

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
    public function profileController_Best_Create(\ProfileController $sender, $userReference = '', $username = '', $page = 0) {
        if (!Gdn::config('Yaga.Reactions.Enabled')) {
            return;
        }

        list($offset, $limit) = offsetLimit($page, Gdn::config('Yaga.BestContent.PerPage', 10));
        if (!is_numeric($offset) || $offset < 0) {
            $offset = 0;
        }

        $sender->editMode(false);

        // Tell the ProfileController what tab to load
        $sender->getUserInfo($userReference, $username);
        $sender->_SetBreadcrumbs(Gdn::translate('Yaga.BestContent'), userUrl($sender->User, '', 'best'));
        $sender->setTabView(Gdn::translate('Yaga.BestContent'), 'best', 'profile', 'Yaga');

        $sender->addJsFile('jquery.expander.js');
        $sender->addJsFile('reactions.js', 'yaga');
        $sender->addDefinition('ExpandText', Gdn::translate('(more)'));
        $sender->addDefinition('CollapseText', Gdn::translate('(less)'));

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
    public function profileController_AddProfileTabs_handler(\ProfileController $sender) {
        // Only show this to users who are signed in as this may be duplicate content to crawlers (pioc92).
        if (is_object($sender->User) && $sender->User->UserID > 0 && Gdn::session()->isValid()) {
            $sender->addProfileTab(sprite('SpBestOf', 'SpMod Sprite').' '.Gdn::translate('Yaga.BestContent'), 'profile/best/'.$sender->User->UserID.'/'.urlencode($sender->User->Name), 'Best');
        }
    }

    /**
     * Check for rank progress when the user model gets updated
     *
     * @param UserModel $sender
     */
    public function userModel_afterSetField_handler(\UserModel $sender) {
        // Don't check for promotions if we aren't using ranks
        if (!Gdn::config('Yaga.Ranks.Enabled')) {
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
    public function profileController_afterPreferencesDefined_handler(\ProfileController $sender) {
        if (Gdn::config('Yaga.Badges.Enabled')) {
            $sender->Preferences['Notifications']['Email.BadgeAward'] = Gdn::translate('Yaga.Badges.Notify');
            $sender->Preferences['Notifications']['Popup.BadgeAward'] = Gdn::translate('Yaga.Badges.Notify');
        }

        if (Gdn::config('Yaga.Ranks.Enabled')) {
            $sender->Preferences['Notifications']['Email.RankPromotion'] = Gdn::translate('Yaga.Ranks.Notify');
            $sender->Preferences['Notifications']['Popup.RankPromotion'] = Gdn::translate('Yaga.Ranks.Notify');
        }
    }

    /**
     * Add the Award Badge and Promote options to the profile controller
     *
     * @param ProfileController $sender
     */
    public function profileController_beforeProfileOptions_handler(\ProfileController $sender) {
        if (Gdn::session()->isValid()) {
            if (Gdn::config('Yaga.Badges.Enabled') && checkPermission('Yaga.Badges.Add')) {
                $sender->EventArguments['ProfileOptions'][] = [
                    'Text' => sprite('SpBadge', 'SpMod Sprite').' '.Gdn::translate('Yaga.Badge.Award'),
                    'Url' => '/badge/award/'.$sender->User->UserID,
                    'CssClass' => 'Popup'
                ];
            }

            if (Gdn::config('Yaga.Ranks.Enabled') && checkPermission('Yaga.Ranks.Add')) {
                $sender->EventArguments['ProfileOptions'][] = [
                    'Text' => sprite('SpMod').' '.Gdn::translate('Yaga.Rank.Promote'),
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
    public function discussionController_afterDiscussionBody_handler(\DiscussionController $sender) {
        if (!Gdn::session()->checkPermission('Yaga.Reactions.View') || !Gdn::config('Yaga.Reactions.Enabled')) {
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
    public function discussionController_afterCommentBody_handler(\DiscussionController $sender) {
        if (!Gdn::session()->checkPermission('Yaga.Reactions.View') || !Gdn::config('Yaga.Reactions.Enabled')) {
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
    public function discussionController_afterReactions_handler(\DiscussionController $sender) {
        if (Gdn::config('Yaga.Reactions.Enabled') == false) {
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
        } else {
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
    public function activityController_afterActivityBody_handler(\ActivityController $sender) {
        if (!Gdn::config('Yaga.Reactions.Enabled')) {
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
        } else {
            echo wrap(renderReactionList($iD, $type), 'div', ['class' => 'Reactions']);
        }
    }

    /**
     * Apply any applicable rank perks when the session first starts.
     * @param UserModel $sender
     */
    public function userModel_afterGetSession_handler(\UserModel $sender) {
        if (!Gdn::config('Yaga.Ranks.Enabled')) {
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
            } elseif ($perkType === 'Perm' && $perkValue === 'grant') {
                $this->grantPermission($user, $perkKey);
            } elseif ($perkType === 'Perm' && $perkValue === 'revoke') {
                $this->revokePermission($user, $perkKey);
            } else {
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
        } else {
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
        } else {
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
        Gdn::config()->saveToConfig('Yaga.ConfBackup.'.$name, Gdn::config($name, null), ['Save' => false]);
        Gdn::config()->saveToConfig($name, $value, ['Save' => false]);
    }

    /**
     * Insert JS and CSS files into the appropiate controllers
     *
     * @param ProfileController $sender
     */
    public function profileController_render_before(\ProfileController $sender) {
        $this->addResources($sender);

        if (Gdn::config('Yaga.Badges.Enabled')) {
            $sender->addModule('BadgesModule');
        }
    }

    /**
     * Insert JS and CSS files into the appropiate controllers and fill the reaction cache
     *
     * @param DiscussionController $sender
     */
    public function discussionController_render_before(\DiscussionController $sender) {
        $this->addResources($sender);
        if (Gdn::config('Yaga.Reactions.Enabled')) {
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
    public function discussionsController_render_before(\DiscussionsController $sender) {
        $this->addResources($sender);
    }

    /**
     * Insert JS and CSS files into the appropiate controllers
     *
     * @param CommentController $sender
     */
    public function commentController_render_before(\CommentController $sender) {
        $this->addResources($sender);
    }

    /**
     * Insert JS and CSS files into the appropiate controllers
     *
     * @param ActivityController $sender
     */
    public function activityController_render_before(\ActivityController $sender) {
        $this->addResources($sender);

        if (Gdn::config('Yaga.LeaderBoard.Enabled', false)) {
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
    public function gdn_Dispatcher_appStartup_handler(\Gdn_Dispatcher $sender) {
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
    public function commentModel_afterSaveComment_handler(\CommentModel $sender) {
        Yaga::executeBadgeHooks($sender, __FUNCTION__);
    }

    /**
     * Check for Badge Awards
     *
     * @param DiscussionModel $sender
     */
    public function discussionModel_afterSaveDiscussion_handler(\DiscussionModel $sender) {
        Yaga::executeBadgeHooks($sender, __FUNCTION__);
    }

    /**
     * Check for Badge Awards
     *
     * @param ActivityModel $sender
     */
    public function activityModel_beforeSaveComment_handler(\ActivityModel $sender) {
        Yaga::executeBadgeHooks($sender, __FUNCTION__);
    }

    /**
     * Check for Badge Awards
     *
     * @param CommentModel $sender
     */
    public function commentModel_beforeNotification_handler(\CommentModel $sender) {
        Yaga::executeBadgeHooks($sender, __FUNCTION__);
    }

    /**
     * Check for Badge Awards
     *
     * @param DiscussionModel $sender
     */
    public function discussionModel_beforeNotification_handler(\DiscussionModel $sender) {
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
    public function userModel_afterSave_handler(\UserModel $sender) {
        Yaga::executeBadgeHooks($sender, __FUNCTION__);
    }

    /**
     * Check for Badge Awards
     *
     * @param ReactionModel $sender
     */
    public function reactionModel_afterReactionSave_handler(\ReactionModel $sender) {
        Yaga::executeBadgeHooks($sender, __FUNCTION__);
    }

    /**
     * Check for Badge Awards
     *
     * @param BadgeAwardModel $sender
     */
    public function badgeAwardModel_afterBadgeAward_handler(\Yaga\BadgeAwardModel $sender) {
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
        } else {
            if (Gdn::session()->isValid() && is_object($sender->Menu) && Gdn::config('Yaga.MenuLinks.Show')) {
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
        if (Gdn::config('Yaga.Badges.Enabled')) {
            $menu->addLink('Yaga', Gdn::translate('Badges'), 'yaga/badges');
        }
        if (Gdn::config('Yaga.Ranks.Enabled')) {
            $menu->addLink('Yaga', Gdn::translate('Ranks'), 'yaga/ranks');
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
        $sql = Gdn::sql();

        $deleteMethod = val('DeleteMethod', $options, 'delete');
        if ($deleteMethod == 'delete') {
            // Remove neutral/negative reactions
            $actions = Yaga::actionModel()->getWhere(['AwardValue <' => 1])->result();
            foreach ($actions as $negative) {
                Gdn::userModel()->getDelete('Reaction', ['InsertUserID' => $userID, 'ActionID' => $negative->ActionID], $data);
            }
        } elseif ($deleteMethod == 'wipe') {
            // Completely remove reactions
            Gdn::userModel()->getDelete('Reaction', ['InsertUserID' => $userID], $data);
        } else {
            // Leave reactions
        }

        // Remove the reactions they have received
        Gdn::userModel()->getDelete('Reaction', ['ParentAuthorID' => $userID], $data);

        // Remove their badges
        Gdn::userModel()->getDelete('BadgeAward', ['UserID' => $userID], $data);

        // Blank the user's yaga information
        $sql->update('User')
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
     public function userModel_beforeDeleteUser_handler(\UserModel $sender) {
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
    public function dbaController_CountJobs_handler(\DbaController $sender) {
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
        include(PATH_PLUGINS.'/yaga/settings/structure.php');
        include(PATH_PLUGINS.'/yaga/settings/stub.php');
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
    public function settingsController_render_before(\SettingsController $sender) {
        // If Ranks feature isn't used, there's nothing to do here.
        if (!Gdn::config('Yaga.Ranks.Enabled') == true) {
            return;
        }
        // Restore backed up configs.
        if (Gdn::config('Yaga.ConfBackup')) {
            Gdn::config()->loadArray(Gdn::config('Yaga.ConfBackup'), 'plugins/yaga');
        }
    }
}
