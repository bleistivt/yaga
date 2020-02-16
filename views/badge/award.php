<?php if (!defined('APPLICATION')) exit();

/* Copyright 2013 Zachary Doll */

$badges = $this->data('Badges');
$username = $this->data('Username', 'Unknown');

$this->Form->setStyles('legacy');

echo '<div id="UserBadgeForm">';
echo wrap(sprintf(Gdn::translate('Yaga.Badge.GiveTo'), $username), 'h1');
echo $this->Form->open();
echo $this->Form->errors();

echo wrap(
    wrap(
        $this->Form->label('Yaga.Badge', 'BadgeID').
        $this->Form->dropdown('BadgeID', $badges),
        'li').

    wrap(
        $this->Form->label('Yaga.Badge.Reason', 'Reason').
        $this->Form->textBox('Reason', ['Multiline' => true]),
        'li').
    wrap(
        anchor(Gdn::translate('Cancel'), 'badge/settings'),
        'li'),
    'ul'
);

echo $this->Form->close('Yaga.Badge.Award');

echo '</div>';
