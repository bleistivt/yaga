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

// Rename Reaction, Action, Badge, BadgeAward & Rank

$construct->table('Reaction');
// Differentiate between GDN_reaction (lowercase) and GDN_Reaction on case insensitive systems.
if ($construct->tableExists() && $construct->columnExists('ActionID')) {
    $construct->renameTable($px.'Reaction', $px.'YagaReaction', false);
}

$construct->table('Action');
if ($construct->tableExists()) {
    $construct->renameTable($px.'Action', $px.'YagaAction', false);
}

$construct->table('Badge');
if ($construct->tableExists()) {
    $construct->renameTable($px.'Badge', $px.'YagaBadge', false);
}

$construct->table('BadgeAward');
if ($construct->tableExists()) {
    $construct->renameTable($px.'BadgeAward', $px.'YagaBadgeAward', false);
}

$construct->table('Rank');
if ($construct->tableExists()) {
    $construct->renameTable($px.'Rank', $px.'YagaRank', false);
}

// Delete duplicates from GDN_Reactions that violate the UNIQUE constraint (user reacting to the same content twice).
$construct->table('YagaReaction');
if ($construct->tableExists()) {
    $result = $sql->query("show index from ${px}YagaReaction where Key_name = 'UX_YagaReaction_Reaction'")->result();
    if (!$result) {
        $sql->query("
            delete from ${px}YagaReaction
            where ReactionID in (
                select * from (
                    select max(r.ReactionID)
                    from ${px}YagaReaction as r
                    group by r.InsertUserID, r.ParentID, r.ParentType
                    having count(r.ReactionID) > 1
                ) as r2
        )", 'delete');
    }
}

// Tracks the data associated with reacting to content
$construct->table('YagaReaction')
    ->primaryKey('ReactionID')
    ->column('InsertUserID', 'int', false, ['index', 'unique.Reaction'])
    ->column('ActionID', 'int', false, ['index', 'index.Profile'])
    ->column('ParentID', 'int', false, ['index.Record', 'unique.Reaction'])
    ->column('ParentType', 'varchar(100)', false, ['index.Record', 'unique.Reaction'])
    ->column('ParentAuthorID', 'int', false, ['index', 'index.Profile'])
    ->column('DateInserted', 'datetime', false, 'index.Record')
    ->set($explicit, $drop);

// Describes actions that can be taken on a comment, discussion or activity
$construct->table('YagaAction')
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
$construct->table('YagaBadge')
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
$construct->table('YagaBadgeAward')
    ->primaryKey('BadgeAwardID')
    ->column('BadgeID', 'int', false, 'index.UserBadges')
    ->column('UserID', 'int', false, 'index.UserBadges')
    ->column('InsertUserID', 'int', null)
    ->column('Reason', 'text', null)
    ->column('DateInserted', 'datetime')
    ->set($explicit, $drop);

// Describes a rank and associated values
$construct->table('YagaRank')
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

// Correct the urls to the old default badge icon.
if (!$construct->CaptureOnly) {
    $sql->update('YagaBadge', ['Photo' => 'plugins/yaga/design/images/default_badge.png'], ['Photo' => 'applications/yaga/design/images/default_badge.png'])->put();
}
