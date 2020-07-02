<?php if (!defined('APPLICATION')) exit();

/* Copyright 2013 Zachary Doll */

use \Vanilla\Formatting\DateTimeFormatter;

$badge = $this->data('Badge');
$userBadgeAward = $this->data('UserBadgeAward', false);
$recentAwards = $this->data('RecentAwards', false);
$awardCount = $this->data('AwardCount', 0);
$dateFormatter = Gdn::getContainer()->get(DateTimeFormatter::class);

echo wrap(
    img($badge->Photo, ['class' => 'BadgePhotoDisplay']).
    wrap($badge->Name, 'h1').
    wrap($badge->Description, 'p'),
    'div',
    ['class' => 'Badge-Details']
);

echo '<div class="Badge-Earned">';

if ($userBadgeAward) {
    echo wrap(
        userPhoto(Gdn::session()->User).
        Gdn::translate('Yaga.Badge.Earned').' '.
        wrap(
            $dateFormatter->formatDate($userBadgeAward->DateInserted, true),
            'span',
            ['class' => 'DateReceived']
        ),
        'div',
        ['class' => 'EarnedThisBadge']
    );
}

if ($awardCount) {
    echo wrap(plural($awardCount, 'Yaga.Badge.EarnedBySingle', 'Yaga.Badge.EarnedByPlural'), 'p', ['class' => 'BadgeCountDisplay']);
}
else {
    echo wrap(Gdn::translate('Yaga.Badge.EarnedByNone'), 'p');
}

if ($recentAwards) {
    echo wrap(Gdn::translate('Yaga.Badge.RecentRecipients'), 'h2');
    echo '<div class="RecentRecipients">';
    foreach ($recentAwards as $award) {
        $user = userBuilder($award);
        echo wrap(
            wrap(
                userPhoto($user).
                userAnchor($user).' '.
                wrap(
                    $dateFormatter->formatDate($award->DateInserted, true),
                    'span',
                    ['class' => 'DateReceived']
                ),
                'div',
                ['class' => 'Cell']
            ),
            'div',
            ['class' => 'CellWrap']
        );
    }
    echo '</div>';
}
echo '</div>';
