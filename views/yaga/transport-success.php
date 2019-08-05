<?php if (!defined('APPLICATION')) exit();

use Yaga;

/* Copyright 2014 Zachary Doll */

$transportType = $this->data('TransportType');
$filename = $this->data('TransportPath');
$actionCount = $this->data('ActionCount', null);
$badgeCount = $this->data('BadgeCount', null);
$rankCount = $this->data('RankCount', null);
$imageCount = $this->data('ImageCount', null);

echo wrap($this->title(), 'h1');
echo wrap(wrap(sprintf(t("Yaga.$transportType.Success"), $filename), 'div'), 'div', ['class' => 'Wrap']);

$string = '';
if($actionCount) {
    $string .= wrap(t('Yaga.Reactions').': '.$actionCount, 'li');
}
if($badgeCount) {
    $string .= wrap(t('Yaga.Badges').': '.$badgeCount, 'li');
}
if($rankCount) {
    $string .= wrap(t('Yaga.Ranks').': '.$rankCount, 'li');
}
if($imageCount) {
    $string .= wrap(t('Image Files').': '.$imageCount, 'li');
}

echo wrapIf($string, 'ul', ['class' => 'Wrap']);

echo wrap(anchor(t('Yaga.Transport.Return'), 'yaga/settings'), 'div', ['class' => 'Wrap']);
