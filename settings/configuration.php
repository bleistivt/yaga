<?php if (!defined('APPLICATION')) exit();

use Yaga;
// DO NOT EDIT THIS FILE
// All of the settings defined here can be overridden in the /conf/config.php file.

$configuration['Yaga']['Reactions']['Enabled'] = true; // Are reactions functional?
$configuration['Yaga']['Badges']['Enabled'] = true; // Are badges functional?
$configuration['Yaga']['Ranks']['Enabled'] = true; // Are ranks functional?

$configuration['Preferences']['Popup']['BadgeAward'] = '1'; // Default Badge Award notifications to popup only
$configuration['Preferences']['Email']['BadgeAward'] = '0';
$configuration['Preferences']['Popup']['RankPromotion'] = '1'; // Default Rank Promotion notifications to popup and email
$configuration['Preferences']['Email']['RankPromotion'] = '1';

// Defined module sort order for Yaga controllers
$configuration['Modules']['Yaga']['Panel'] = ['MeModule', 'UserBoxModule', 'ActivityFilterModule', 'UserPhotoModule', 'ProfileFilterModule', 'SideMenuModule', 'UserInfoModule', 'GuestModule', 'Ads'];
$configuration['Modules']['Yaga']['Content'] = ['MessageModule', 'MeModule', 'UserBoxModule', 'ProfileOptionsModule', 'Notices', 'ActivityFilterModule', 'ProfileFilterModule', 'BestFilterModule', 'Content', 'Ads'];

$configuration['Yaga']['BestContent']['PerPage'] = 10; // Per page limit on the best of page
$configuration['Yaga']['Ranks']['Photo'] = 'plugins/yaga/design/images/default_promotion.png'; // Default photo used for ranks activity items
$configuration['Yaga']['Badges']['DefaultPhoto'] = 'plugins/yaga/design/images/default_badge.png'; // Default photo used for badges pages
$configuration['Yaga']['Reactions']['RecordLimit'] = 10; // The number of user avatars to show before collapsing them in the reaction record