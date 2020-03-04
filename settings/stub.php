<?php if (!defined('APPLICATION')) exit();

/* Copyright 2013-2016 Zachary Doll */

$sql = Gdn::database()->sql();

// Only insert default actions if none exist
$row = $sql->get('YagaAction', '', 'asc', 1)->firstRow(DATASET_TYPE_ARRAY);
if (!$row) {
    $sql->insert('YagaAction', [
        'ActionID' => 1,
        'Name' => 'Promote',
        'Description' => 'This post deserves to be featured on the best of page!',
        'Tooltip' => 'Click me if this content should be featured.',
        'CssClass' => 'ReactPointUp',
        'AwardValue' => 5,
        'Permission' => 'Garden.Curation.Manage',
        'Sort' => 0
    
    ]);
    $sql->insert('YagaAction', [
        'ActionID' => 2,
        'Name' => 'Insightful',
        'Description' => 'This post brings new meaning to the discussion.',
        'Tooltip' => 'Insightful',
        'CssClass' => 'ReactEye2',
        'AwardValue' => 1,
        'Permission' => 'Yaga.Reactions.Add',
        'Sort' => 1
    ]);
    $sql->insert('YagaAction', [
        'ActionID' => 3,
        'Name' => 'Awesome',
        'Description' => 'This post is made of pure win.',
        'Tooltip' => 'Awesome',
        'CssClass' => 'ReactHeart',
        'AwardValue' => 1,
        'Permission' => 'Yaga.Reactions.Add',
        'Sort' => 2
    ]);
    $sql->insert('YagaAction', [
        'ActionID' => 4,
        'Name' => 'LOL',
        'Description' => 'This post is funny.',
        'Tooltip' => 'LOL',
        'CssClass' => 'ReactWink',
        'AwardValue' => 1,
        'Permission' => 'Yaga.Reactions.Add',
        'Sort' => 3
    ]);
    $sql->insert('YagaAction', [
        'ActionID' => 5,
        'Name' => 'WTF',
        'Description' => 'This post is all sorts of shocking.',
        'Tooltip' => 'WTF',
        'CssClass' => 'ReactShocked',
        'AwardValue' => 1,
        'Permission' => 'Yaga.Reactions.Add',
        'Sort' => 4
    ]);
    $sql->insert('YagaAction', [
        'ActionID' => 6,
        'Name' => 'Spam',
        'Description' => 'This post is spam.',
        'Tooltip' => 'Spam',
        'CssClass' => 'ReactWarning',
        'AwardValue' => -5,
        'Permission' => 'Garden.Curation.Manage',
        'Sort' => 5
    ]);
}

// Only insert default badges if none exist
$row = $sql->get('YagaBadge', '', 'asc', 1)->firstRow(DATASET_TYPE_ARRAY);
if (!$row) {
    $sql->insert('YagaBadge', [
        'Name' => 'First Anniversary',
        'Description' => 'Has it been a year already?',
        'Photo' => '/plugins/yaga/design/images/default_badges.svg#anniversary-1',
        'RuleClass' => 'LengthOfService',
        'RuleCriteria' => '{"Duration":"1","Period":"year"}',
        'AwardValue' => 5
    ]);
    $sql->insert('YagaBadge', [
        'Name' => 'Second Anniversary',
        'Description' => 'Thanks for sticking with us for 2 years.',
        'Photo' => '/plugins/yaga/design/images/default_badges.svg#anniversary-2',
        'RuleClass' => 'LengthOfService',
        'RuleCriteria' => '{"Duration":"2","Period":"year"}',
        'AwardValue' => 5
    ]);
    $sql->insert('YagaBadge', [
        'Name' => 'Third Anniversary',
        'Description' => 'That\'s three years you have been here!',
        'Photo' => '/plugins/yaga/design/images/default_badges.svg#anniversary-3',
        'RuleClass' => 'LengthOfService',
        'RuleCriteria' => '{"Duration":"3","Period":"year"}',
        'AwardValue' => 5
    ]);
    $sql->insert('YagaBadge', [
        'Name' => 'Fourth Anniversary',
        'Description' => 'You might have graduated from college considering how long you have been here.',
        'Photo' => '/plugins/yaga/design/images/default_badges.svg#anniversary-4',
        'RuleClass' => 'LengthOfService',
        'RuleCriteria' => '{"Duration":"4","Period":"year"}',
        'AwardValue' => 5
    ]);
    $sql->insert('YagaBadge', [
        'Name' => 'Fifth Anniversary',
        'Description' => 'Five years ago, you created your account. Thanks for sticking around!',
        'Photo' => '/plugins/yaga/design/images/default_badges.svg#anniversary-5',
        'RuleClass' => 'LengthOfService',
        'RuleCriteria' => '{"Duration":"5","Period":"year"}',
        'AwardValue' => 5
    ]);
    $sql->insert('YagaBadge', [
        'Name' => 'Final Anniversary',
        'Description' => 'You have reached your final form. OK, not really, but you are still awesome in my book!',
        'Photo' => '/plugins/yaga/design/images/default_badges.svg#anniversary-longtime',
        'RuleClass' => 'LengthOfService',
        'RuleCriteria' => '{"Duration":"6","Period":"year"}',
        'AwardValue' => 10
    ]);
    $sql->insert('YagaBadge', [
        'Name' => '5 Awesomes',
        'Description' => 'You have received 5 awesomes. Not a bad start!',
        'Photo' => '/plugins/yaga/design/images/default_badges.svg#awesome-1',
        'RuleClass' => 'ReactionCount',
        'RuleCriteria' => '{"Target":"5","ActionID":"3"}',
        'AwardValue' => 10
    ]);
    $sql->insert('YagaBadge', [
        'Name' => '25 Awesomes',
        'Description' => 'You keep posting great content. Nice!',
        'Photo' => '/plugins/yaga/design/images/default_badges.svg#awesome-2',
        'RuleClass' => 'ReactionCount',
        'RuleCriteria' => '{"Target":"25","ActionID":"3"}',
        'AwardValue' => 10
    ]);
    $sql->insert('YagaBadge', [
        'Name' => '100 Awesomes',
        'Description' => 'Your posts are what good forums are made of!',
        'Photo' => '/plugins/yaga/design/images/default_badges.svg#awesome-3',
        'RuleClass' => 'ReactionCount',
        'RuleCriteria' => '{"Target":"100","ActionID":"3"}',
        'AwardValue' => 10
    ]);
    $sql->insert('YagaBadge', [
        'Name' => '250 Awesomes',
        'Description' => 'We definitely want you to keep doing what you are doing.',
        'Photo' => '/plugins/yaga/design/images/default_badges.svg#awesome-4',
        'RuleClass' => 'ReactionCount',
        'RuleCriteria' => '{"Target":"250","ActionID":"3"}',
        'AwardValue' => 25
    ]);
    $sql->insert('YagaBadge', [
        'Name' => '500 Awesomes',
        'Description' => 'We\'re lucky to have you here. Amazing!',
        'Photo' => '/plugins/yaga/design/images/default_badges.svg#awesome-5',
        'RuleClass' => 'ReactionCount',
        'RuleCriteria' => '{"Target":"500","ActionID":"3"}',
        'AwardValue' => 50
    ]);
    $sql->insert('YagaBadge', [
        'Name' => 'Have Some Cake',
        'Description' => 'Thanks for posting on your anniversary. It means so much to us that you remembered!',
        'Photo' => '/plugins/yaga/design/images/default_badges.svg#cakeday',
        'RuleClass' => 'CakeDayPost',
        'RuleCriteria' => '{}',
        'AwardValue' => 10
    ]);
    $sql->insert('YagaBadge', [
        'Name' => 'First Post',
        'Description' => 'You are in there and getting involved. Have some free points!',
        'Photo' => '/plugins/yaga/design/images/default_badges.svg#comment-1',
        'RuleClass' => 'PostCount',
        'RuleCriteria' => '{"Comparison":"gte","Target":"1"}',
        'AwardValue' => 2
    ]);
    $sql->insert('YagaBadge', [
        'Name' => '10 Posts',
        'Description' => 'This is how you get to places. Keep up your posting.',
        'Photo' => '/plugins/yaga/design/images/default_badges.svg#comment-2',
        'RuleClass' => 'PostCount',
        'RuleCriteria' => '{"Comparison":"gte","Target":"10"}',
        'AwardValue' => 5
    ]);
    $sql->insert('YagaBadge', [
        'Name' => '100 Posts',
        'Description' => 'Thanks for driving discussions!',
        'Photo' => '/plugins/yaga/design/images/default_badges.svg#comment-3',
        'RuleClass' => 'PostCount',
        'RuleCriteria' => '{"Comparison":"gte","Target":"100"}',
        'AwardValue' => 10
    ]);
    $sql->insert('YagaBadge', [
        'Name' => '500 Posts',
        'Description' => 'You have given the gift of gab to this community.',
        'Photo' => '/plugins/yaga/design/images/default_badges.svg#comment-4',
        'RuleClass' => 'PostCount',
        'RuleCriteria' => '{"Comparison":"gte","Target":"500"}',
        'AwardValue' => 15
    ]);
    $sql->insert('YagaBadge', [
        'Name' => '1000 Posts',
        'Description' => 'When you are here, you\'re family!',
        'Photo' => '/plugins/yaga/design/images/default_badges.svg#comment-5',
        'RuleClass' => 'PostCount',
        'RuleCriteria' => '{"Comparison":"gte","Target":"1000"}',
        'AwardValue' => 20
    ]);
    $sql->insert('YagaBadge', [
        'Name' => 'Book Connection',
        'Description' => 'See how many likes you get with these shares. Am I doing this right?',
        'Photo' => '/plugins/yaga/design/images/default_badges.svg#fb-connector',
        'RuleClass' => 'SocialConnection',
        'RuleCriteria' => '{"SocialNetwork":"Facebook"}',
        'AwardValue' => 10
    ]);
    $sql->insert('YagaBadge', [
        'Name' => '5 Insightfuls',
        'Description' => 'You have received 5 insightfuls. Not a bad start!',
        'Photo' => '/plugins/yaga/design/images/default_badges.svg#insightful-1',
        'RuleClass' => 'ReactionCount',
        'RuleCriteria' => '{"Target":"5","ActionID":"2"}',
        'AwardValue' => 10
    ]);
    $sql->insert('YagaBadge', [
        'Name' => '25 Insightfuls',
        'Description' => 'You keep posting great content. Nice!',
        'Photo' => '/plugins/yaga/design/images/default_badges.svg#insightful-2',
        'RuleClass' => 'ReactionCount',
        'RuleCriteria' => '{"Target":"25","ActionID":"2"}',
        'AwardValue' => 10
    ]);
    $sql->insert('YagaBadge', [
        'Name' => '100 Insightfuls',
        'Description' => 'Your posts are what good forums are made of!',
        'Photo' => '/plugins/yaga/design/images/default_badges.svg#insightful-3',
        'RuleClass' => 'ReactionCount',
        'RuleCriteria' => '{"Target":"100","ActionID":"2"}',
        'AwardValue' => 10
    ]);
    $sql->insert('YagaBadge', [
        'Name' => '250 Insightfuls',
        'Description' => 'We definitely want you to keep doing what you are doing.',
        'Photo' => '/plugins/yaga/design/images/default_badges.svg#insightful-4',
        'RuleClass' => 'ReactionCount',
        'RuleCriteria' => '{"Target":"250","ActionID":"2"}',
        'AwardValue' => 25
    ]);
    $sql->insert('YagaBadge', [
        'Name' => '500 Insightfuls',
        'Description' => 'We\'re lucky to have you here. Amazing!',
        'Photo' => '/plugins/yaga/design/images/default_badges.svg#insightful-5',
        'RuleClass' => 'ReactionCount',
        'RuleCriteria' => '{"Target":"500","ActionID":"2"}',
        'AwardValue' => 50
    ]);
    $sql->insert('YagaBadge', [
        'Name' => 'Paging Mr. F',
        'Description' => 'Mentioning someone in a post is a great way to direct comments.',
        'Photo' => '/plugins/yaga/design/images/default_badges.svg#mention',
        'RuleClass' => 'HasMentioned',
        'RuleCriteria' => '{}',
        'AwardValue' => 5
    ]);
    $sql->insert('YagaBadge', [
        'Name' => 'We Have Touchdown',
        'Description' => 'Today is the anniversary of the first human moon-walk. Celebrate!',
        'Photo' => '/plugins/yaga/design/images/default_badges.svg#moon-landing',
        'RuleClass' => 'HolidayVisit',
        'RuleCriteria' => '{"Month":"7","Day":"20"}',
        'AwardValue' => 15
    ]);
    $sql->insert('YagaBadge', [
        'Name' => 'You Look Familiar',
        'Description' => 'Thanks for sharing yourself with the community.',
        'Photo' => '/plugins/yaga/design/images/default_badges.svg#portrait-upload',
        'RuleClass' => 'PhotoExists',
        'RuleCriteria' => '{}',
        'AwardValue' => 10
    ]);
    $sql->insert('YagaBadge', [
        'Name' => '1 Promote',
        'Description' => 'You have received your first promote. This is a great start!',
        'Photo' => '/plugins/yaga/design/images/default_badges.svg#promote-1',
        'RuleClass' => 'ReactionCount',
        'RuleCriteria' => '{"Target":"1","ActionID":"1"}',
        'AwardValue' => 10
    ]);
    $sql->insert('YagaBadge', [
        'Name' => '5 Promotes',
        'Description' => 'You keep posting great content. Nice!',
        'Photo' => '/plugins/yaga/design/images/default_badges.svg#promote-2',
        'RuleClass' => 'ReactionCount',
        'RuleCriteria' => '{"Target":"5","ActionID":"1"}',
        'AwardValue' => 10
    ]);
    $sql->insert('YagaBadge', [
        'Name' => '25 Promotes',
        'Description' => 'Your posts are what good forums are made of!',
        'Photo' => '/plugins/yaga/design/images/default_badges.svg#promote-3',
        'RuleClass' => 'ReactionCount',
        'RuleCriteria' => '{"Target":"25","ActionID":"1"}',
        'AwardValue' => 25
    ]);
    $sql->insert('YagaBadge', [
        'Name' => '50 Promotes',
        'Description' => 'We definitely want you to keep doing what you are doing.',
        'Photo' => '/plugins/yaga/design/images/default_badges.svg#promote-4',
        'RuleClass' => 'ReactionCount',
        'RuleCriteria' => '{"Target":"50","ActionID":"1"}',
        'AwardValue' => 50
    ]);
    $sql->insert('YagaBadge', [
        'Name' => '100 Promotes',
        'Description' => 'We\'re lucky to have you here. Amazing!',
        'Photo' => '/plugins/yaga/design/images/default_badges.svg#promote-5',
        'RuleClass' => 'ReactionCount',
        'RuleCriteria' => '{"Target":"100","ActionID":"1"}',
        'AwardValue' => 100
    ]);
    $sql->insert('YagaBadge', [
        'Name' => 'c-c-COMBO BREAKER!',
        'Description' => 'You are doing so much stuff today. Have another badge!',
        'Photo' => '/plugins/yaga/design/images/default_badges.svg#achievement-combo',
        'RuleClass' => 'AwardCombo',
        'RuleCriteria' => '{"Target":"5","Duration":"1","Period":"day"}',
        'AwardValue' => 20
    ]);
    $sql->insert('YagaBadge', [
        'Name' => 'Post Marathon',
        'Description' => 'I am tired just looking at all the stuff you are doing!',
        'Photo' => '/plugins/yaga/design/images/default_badges.svg#running-man',
        'RuleClass' => 'CommentMarathon',
        'RuleCriteria' => '{"Target":"25","Duration":"1","Period":"day"}',
        'AwardValue' => 10
    ]);
    $sql->insert('YagaBadge', [
        'Name' => '5 WTFs',
        'Description' => 'You have received 5 WTFs. Not a bad start!',
        'Photo' => '/plugins/yaga/design/images/default_badges.svg#shock-1',
        'RuleClass' => 'ReactionCount',
        'RuleCriteria' => '{"Target":"5","ActionID":"5"}',
        'AwardValue' => 10
    ]);
    $sql->insert('YagaBadge', [
        'Name' => '25 WTFs',
        'Description' => 'You keep posting great content. Nice!',
        'Photo' => '/plugins/yaga/design/images/default_badges.svg#shock-2',
        'RuleClass' => 'ReactionCount',
        'RuleCriteria' => '{"Target":"25","ActionID":"5"}',
        'AwardValue' => 10
    ]);
    $sql->insert('YagaBadge', [
        'Name' => '100 WTFs',
        'Description' => 'Your posts are what good forums are made of!',
        'Photo' => '/plugins/yaga/design/images/default_badges.svg#shock-3',
        'RuleClass' => 'ReactionCount',
        'RuleCriteria' => '{"Target":"100","ActionID":"5"}',
        'AwardValue' => 10
    ]);
    $sql->insert('YagaBadge', [
        'Name' => '250 WTFs',
        'Description' => 'We definitely want you to keep doing what you are doing.',
        'Photo' => '/plugins/yaga/design/images/default_badges.svg#shock-4',
        'RuleClass' => 'ReactionCount',
        'RuleCriteria' => '{"Target":"250","ActionID":"5"}',
        'AwardValue' => 25
    ]);
    $sql->insert('YagaBadge', [
        'Name' => '500 WTFs',
        'Description' => 'We\'re lucky to have you here. Amazing!',
        'Photo' => '/plugins/yaga/design/images/default_badges.svg#shock-5',
        'RuleClass' => 'ReactionCount',
        'RuleCriteria' => '{"Target":"500","ActionID":"5"}',
        'AwardValue' => 50
    ]);
    $sql->insert('YagaBadge', [
        'Name' => 'Threadshot!',
        'Description' => 'You have super fast reflexes to have responded so quickly!',
        'Photo' => '/plugins/yaga/design/images/default_badges.svg#threadshot-reticle',
        'RuleClass' => 'ReflexComment',
        'RuleCriteria' => '{"Seconds":"60"}',
        'AwardValue' => 15
    ]);
    $sql->insert('YagaBadge', [
        'Name' => 'Twitterpated',
        'Description' => 'Make way for the retweets, hash tags, and restrictive message length. It is refreshing to see well crafted messages make every character cou',
        'Photo' => '/plugins/yaga/design/images/default_badges.svg#twitter-connector',
        'RuleClass' => 'SocialConnection',
        'RuleCriteria' => '{"SocialNetwork":"Twitter"}',
        'AwardValue' => 10
    ]);
    $sql->insert('YagaBadge', [
        'Name' => 'I\'m Not Dead Yet!',
        'Description' => 'You should try to bring humans back to life now that you have brought this discussion back from the dead.',
        'Photo' => '/plugins/yaga/design/images/default_badges.svg#unimpressed-necropost',
        'RuleClass' => 'NecroPost',
        'RuleCriteria' => '{"Duration":"26","Period":"week"}',
        'AwardValue' => 10
    ]);
    $sql->insert('YagaBadge', [
        'Name' => 'Welcome Committee',
        'Description' => 'Thanks for posting on a new member\'s first discussion. I know you made them feel at home.',
        'Photo' => '/plugins/yaga/design/images/default_badges.svg#welcome-committee',
        'RuleClass' => 'NewbieComment',
        'RuleCriteria' => '{"Duration":"2","Period":"day"}',
        'AwardValue' => 10
    ]);
    $sql->insert('YagaBadge', [
        'Name' => '5 LOLs',
        'Description' => 'You have received 5 LOLs. Not a bad start!',
        'Photo' => '/plugins/yaga/design/images/default_badges.svg#wink-1',
        'RuleClass' => 'ReactionCount',
        'RuleCriteria' => '{"Target":"5","ActionID":"4"}',
        'AwardValue' => 10
    ]);
    $sql->insert('YagaBadge', [
        'Name' => '25 LOLs',
        'Description' => 'You keep posting great content. Nice!',
        'Photo' => '/plugins/yaga/design/images/default_badges.svg#wink-2',
        'RuleClass' => 'ReactionCount',
        'RuleCriteria' => '{"Target":"25","ActionID":"4"}',
        'AwardValue' => 10
    ]);
    $sql->insert('YagaBadge', [
        'Name' => '100 LOLs',
        'Description' => 'Your posts are what good forums are made of!',
        'Photo' => '/plugins/yaga/design/images/default_badges.svg#wink-3',
        'RuleClass' => 'ReactionCount',
        'RuleCriteria' => '{"Target":"100","ActionID":"4"}',
        'AwardValue' => 10
    ]);
    $sql->insert('YagaBadge', [
        'Name' => '250 LOLs',
        'Description' => 'We definitely want you to keep doing what you are doing.',
        'Photo' => '/plugins/yaga/design/images/default_badges.svg#wink-4',
        'RuleClass' => 'ReactionCount',
        'RuleCriteria' => '{"Target":"250","ActionID":"4"}',
        'AwardValue' => 25
    ]);
    $sql->insert('YagaBadge', [
        'Name' => '500 LOLs',
        'Description' => 'We\'re lucky to have you here. Amazing!',
        'Photo' => '/plugins/yaga/design/images/default_badges.svg#wink-5',
        'RuleClass' => 'ReactionCount',
        'RuleCriteria' => '{"Target":"500","ActionID":"4"}',
        'AwardValue' => 50
    ]);
}

// Only insert default ranks if none exist
$row = $sql->get('YagaRank', '', 'asc', 1)->firstRow(DATASET_TYPE_ARRAY);
if (!$row) {
    $sql->insert('YagaRank', [
        'RankID' => 1,
        'Name' => 'Level 1',
        'Description' => 'You are at the lowest level. Build up your points to unlock new features!',
        'Sort' => 1,
        'PointReq' => 0,
        'PostReq' => 0,
        'AgeReq' => 0,
        'Perks' => '{"ConfGarden.EditContentTimeout":"0","PermGarden.Curation.Manage":"revoke","PermPlugins.Signatures.Edit":"revoke","PermPlugins.Tagging.Add":"revoke","ConfPlugins.Emotify.FormatEmoticons":"0","ConfGarden.Format.MeActions":"0"}'
    ]);
    $sql->insert('YagaRank', [
        'RankID' => 2,
        'Name' => 'Level 2',
        'Description' => 'Level up!',
        'Sort' => 2,
        'PointReq' => 0,
        'PostReq' => 5,
        'AgeReq' => 86400,
        'Perks' => '{"ConfGarden.EditContentTimeout":"0","PermGarden.Curation.Manage":"revoke","PermPlugins.Signatures.Edit":"revoke","PermPlugins.Tagging.Add":"revoke"}'
    ]);
    $sql->insert('YagaRank', [
        'RankID' => 3,
        'Name' => 'Level 3',
        'Description' => 'Building your reputation has unlocked emoticons!',
        'Sort' => 3,
        'PointReq' => 15,
        'PostReq' => 50,
        'AgeReq' => 604800,
        'Perks' => '{"ConfGarden.EditContentTimeout":"0","ConfPlugins.Emotify.FormatEmoticons":"1","ConfGarden.Format.MeActions":"1"}'
    ]);
    $sql->insert('YagaRank', [
        'RankID' => 4,
        'Name' => 'Level 4',
        'Description' => 'Your pen now has an eraser! You can edit your posts for up to a week after making them.',
        'Sort' => 4,
        'PointReq' => 75,
        'PostReq' => 200,
        'AgeReq' => 2678400,
        'Perks' => '{"ConfGarden.EditContentTimeout":"604800","PermPlugins.Signatures.Edit":"grant","ConfPlugins.Emotify.FormatEmoticons":"1","ConfGarden.Format.MeActions":"1"}'
    ]);
    $sql->insert('YagaRank', [
        'RankID' => 5,
        'Name' => 'Level 5',
        'Description' => 'Holy batman, you are awesome. Have some more reactions!',
        'Sort' => 5,
        'PointReq' => 250,
        'PostReq' => 400,
        'AgeReq' => 7776000,
        'Perks' => '{"ConfGarden.EditContentTimeout":"2592000","PermGarden.Curation.Manage":"grant","PermPlugins.Signatures.Edit":"grant","PermPlugins.Tagging.Add":"grant","ConfPlugins.Emotify.FormatEmoticons":"1","ConfGarden.Format.MeActions":"1"}'
    ]);
    $sql->insert('YagaRank', [
        'RankID' => 6,
        'Name' => 'Moderator',
        'Description' => 'You can now moderate content. Welcome aboard!',
        'Sort' => 6,
        'PointReq' => 1000,
        'PostReq' => 1000,
        'AgeReq' => 31536000,
        'Perks' => '{"Role":"32","PermGarden.Curation.Manage":"grant"}'
    ]);
    $sql->insert('YagaRank', [
        'RankID' => 7,
        'Name' => 'Administrator',
        'Description' => 'With great power comes great responsibility.',
        'Sort' => 7,
        'PointReq' => 10000,
        'PostReq' => 10000,
        'AgeReq' => 157766400,
        'Perks' => '{"Role":"16","ConfGarden.EditContentTimeout":"-1","PermGarden.Curation.Manage":"grant","PermPlugins.Signatures.Edit":"grant","PermPlugins.Tagging.Add":"grant","ConfPlugins.Emotify.FormatEmoticons":"1","ConfGarden.Format.MeActions":"1"}',
        'Enabled' => 0
    ]);
}