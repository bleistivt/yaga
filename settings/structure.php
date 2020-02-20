<?php if (!defined('APPLICATION')) exit();

/* Copyright 2013 Zachary Doll */

if(!isset($drop)) {
    $drop = false; // Safe default - Set to true to drop the table if it already exists.
}

if (!isset($explicit)) {
    $explicit = false; // Safe default - Set to true to remove all other columns from table.
}

$database = Gdn::database();
$sql = $database->sql(); // To run queries.
$construct = $database->structure(); // To modify and add database tables.
$px = $database->DatabasePrefix;

// Tracks the data associated with reacting to content
$construct->table('Reaction')
    ->primaryKey('ReactionID')
    ->column('InsertUserID', 'int', false, ['index', 'unique.Reaction'])
    ->column('ActionID', 'int', false, ['index', 'index.Profile'])
    ->column('ParentID', 'int', false, ['index.Record', 'unique.Reaction'])
    ->column('ParentType', 'varchar(100)', false, ['index.Record', 'unique.Reaction'])
    ->column('ParentAuthorID', 'int', false, ['index', 'index.Profile'])
    ->column('DateInserted', 'datetime', false, 'index.Record')
    ->set($explicit, $drop);

/*$result = $sql->query("SHOW INDEX FROM ${Px}Reaction WHERE Key_name = 'IX_ParentID_ParentType'")->result(); 
if (!$result && !$construct->CaptureOnly) {
    $sql->query("ALTER TABLE ${Px}Reaction ADD INDEX IX_ParentID_ParentType (ParentID, ParentType)");
}*/

// Describes actions that can be taken on a comment, discussion or activity
$construct->table('Action')
    ->primaryKey('ActionID')
    ->column('Name', 'varchar(140)')
    ->column('Description', 'varchar(255)')
    ->column('Tooltip', 'varchar(255)')
    ->column('CssClass', 'varchar(255)')
    ->column('AwardValue', 'int', 1)
    ->column('Permission', 'varchar(255)', 'Yaga.Reactions.Add')
    ->column('Sort', 'int', true)
    ->set($explicit, $drop);

// Describes a badge and the associated rule criteria
$construct->table('Badge')
    ->primaryKey('BadgeID')
    ->column('Name', 'varchar(140)')
    ->column('Description', 'varchar(255)', null)
    ->column('Photo', 'varchar(255)', null)
    ->column('RuleClass', 'varchar(255)')
    ->column('RuleCriteria', 'text', true)
    ->column('AwardValue', 'int', 0)
    ->column('Enabled', 'tinyint(1)', '1')
    ->column('Sort', 'int', true)
    ->set($explicit, $drop);

// Tracks the actual awarding of badges
$construct->table('BadgeAward')
    ->primaryKey('BadgeAwardID')
    ->column('BadgeID', 'int', false, 'index.UserBadges')
    ->column('UserID', 'int', false, 'index.UserBadges')
    ->column('InsertUserID', 'int', null)
    ->column('Reason', 'text', null)
    ->column('DateInserted', 'datetime')
    ->set($explicit, $drop);

// Describes a rank and associated values
$construct->table('Rank')
    ->primaryKey('RankID')
    ->column('Name', 'varchar(140)')
    ->column('Description', 'varchar(255)', null)
    ->column('Sort', 'int', true)
    ->column('PointReq', 'int', 0)
    ->column('PostReq', 'int', 0)
    ->column('AgeReq', 'int', 0)
    ->column('Perks', 'text', true)
    ->column('Enabled', 'tinyint(1)', '1')
    ->set($explicit, $drop);

// Tracks the current rank a user has
$construct->table('User')
    ->column('CountBadges', 'int', 0)
    ->column('RankID', 'int', true)
    ->column('RankProgression', 'tinyint(1)', '1')
    ->set();

// Add activity types for Badge and Rank awards
if ($sql->getWhere('ActivityType', ['Name' => 'BadgeAward'])->numRows() == 0  && !$construct->CaptureOnly) {
    $sql->insert('ActivityType', ['AllowComments' => '1', 'Name' => 'BadgeAward', 'FullHeadline' => '%1$s earned a badge.', 'ProfileHeadline' => '%1$s earned a badge.', 'Notify' => 1]);
}
if ($sql->getWhere('ActivityType', ['Name' => 'RankPromotion'])->numRows() == 0 && !$construct->CaptureOnly) {
    $sql->insert('ActivityType', ['AllowComments' => '1', 'Name' => 'RankPromotion', 'FullHeadline' => '%1$s was promoted.', 'ProfileHeadline' => '%1$s was promoted.', 'Notify' => 1]);
}

// Correct the url of the old default badge icon.
if (!$construct->CaptureOnly) {
    $sql->update('Badge', ['Photo' => 'plugins/yaga/design/images/default_badge.png'], ['Photo' => 'applications/yaga/design/images/default_badge.png'])->put();
}
