<?php if (!defined('APPLICATION')) exit();

use Yaga;

/* Copyright 2013 Zachary Doll */

echo wrap($this->title(), 'h1');
echo '<ul class="DataList Badges">';
foreach($this->data('Badges') as $badge) {
    // Don't show disabled badges
    //if(!$badge->Enabled) {
    //    continue;
    //}
    $row = '';
    $awardDescription = '';
    $readClass = ' Read';

    if ($badge->UserID) {
        $readClass = '';
        $awardDescription = sprintf(Gdn::translate('Yaga.Badge.Earned.Format'), Gdn_Format::date($badge->DateInserted, 'html'), $badge->InsertUserName);
        if ($badge->Reason) {
            $awardDescription .= ': "'.$badge->Reason.'"';
        }
    }

    if ($badge->Photo) {
        $row .= img($badge->Photo, ['class' => 'BadgePhoto']);
    } else {
        $row .= img('plugins/yaga/design/images/default_badge.png', ['class' => 'BadgePhoto']);
    }

    $row .= wrap(
                    wrap(
                                    anchor($badge->Name, 'yaga/badges/'.$badge->BadgeID.'/'.Gdn_Format::url($badge->Name), ['class' => 'Title']), 'div', ['class' => 'Title']
                    ) .
                    wrap(
                                    wrap($badge->Description, 'span', ['class' => 'MItem BadgeDescription']) .
                                    wrap($badge->AwardValue.' points.', 'span', ['class' => 'MItem BadgePoints']) .
                                    wrapIf($awardDescription, 'p'),
                                    'div',
                                    ['class' => 'Meta']),
                    'div',
                    ['class' => 'ItemContent Badge']
    );
    echo wrap($row, 'li', ['class' => 'Item ItemBadge'.$readClass]);
}

echo '</ul>';
