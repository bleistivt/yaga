<?php if (!defined('APPLICATION')) exit();

use Yaga;

/* Copyright 2013 Zachary Doll */

$rules = $this->data('Rules');

echo wrap($this->title(), 'h1');
echo wrap(wrap(t('Yaga.Badges.Desc'), 'div'), 'div', ['class' => 'Wrap']);
echo wrap(wrap(t('Yaga.Badges.Settings.Desc'), 'div'), 'div', ['class' => 'Wrap']);
echo wrap(anchor(t('Yaga.Badge.Add'), 'badge/add', ['class' => 'Button']), 'div', ['class' => 'Wrap']);

?>
<table id="Badges" class="AltRows Sortable">
    <thead>
        <tr>
            <th><?php echo t('Image'); ?></th>
            <th><?php echo t('Name'); ?></th>
            <th><?php echo t('Description'); ?></th>
            <th><?php echo t('Rule'); ?></th>
            <th><?php echo t('Award Value'); ?></th>
            <th><?php echo t('Auto Award'); ?></th>
            <th><?php echo t('Options'); ?></th>
        </tr>
    </thead>
    <tbody>
        <?php
        $alt = 'Alt';
        foreach ($this->data('Badges') as $badge) {
            $alt = $alt ? '' : 'Alt';
            $row = '';

            $badgePhoto = img($badge->Photo, ['class' => 'BadgePhoto']);

            $row .= wrap(anchor($badgePhoto, '/yaga/badges/'.$badge->BadgeID.'/'.Gdn_Format::url($badge->Name), ['title' => t('Yaga.Badge.DetailLink')]), 'td');
            $row .= wrap($badge->Name, 'td');
            $row .= wrap($badge->Description, 'td');
            $ruleName = t('Yaga.Rules.UnknownRule');
            if (array_key_exists($badge->RuleClass, $rules)) {
                $ruleName = $rules[$badge->RuleClass];
            }
            $row .= wrap($ruleName, 'td');
            $row .= wrap($badge->AwardValue, 'td');
            $toggleText = ($badge->Enabled) ? t('Enabled') : t('Disabled');
            $activeClass = ($badge->Enabled) ? 'Active' : 'InActive';
            $row .= wrap(wrap(anchor($toggleText, 'badge/toggle/'.$badge->BadgeID, 'Hijack Button'), 'span', ['class' => "ActivateSlider ActivateSlider-{$activeClass}"]), 'td');
            $row .= wrap(anchor(t('Edit'), 'badge/edit/'.$badge->BadgeID, ['class' => 'Button']).anchor(t('Delete'), 'badge/delete/'.$badge->BadgeID, ['class' => 'Danger Popup Button']), 'td');
            echo wrap($row, 'tr', ['id' => 'BadgeID_'.$badge->BadgeID, 'data-badgeid' => $badge->BadgeID, 'class' => $alt]);
        }
        ?>
    </tbody>
</table>
<?php PagerModule::write(['Sender' => $this]);
