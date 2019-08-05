<?php if (!defined('APPLICATION')) exit();

use Yaga;

/* Copyright 2013 Zachary Doll */

$photoString = '';
$delButton = '';
$photo = c('Yaga.Ranks.Photo', false);
if($photo) {
    $photoString = img($photo);
    $delButton = anchor(t('Delete Photo'), combinePaths(['rank/deletephoto', Gdn::session()->transientKey()]), 'Button Danger PopConfirm');
}
$ageArray = ageArray();

echo wrap($this->title(), 'h1');

echo wrap(
    $photoString.
        $this->Form->open(['enctype' => 'multipart/form-data', 'class' => 'Rank']) .
        $this->Form->errors() .
        wrap(
            wrap(
                $this->Form->label('Photo', 'PhotoUpload') .
                    wrap(t('Yaga.Rank.Photo.Desc'), 'div', ['class' => 'Info']) .
                    $delButton .
                    $this->Form->input('PhotoUpload', 'file') .
                    $this->Form->button('Save', ['class' => 'Button']), 'li'),
            'ul'
        ).$this->Form->close('', ' '),
    'div',
    ['class' => 'Aside']
);

echo wrap(
    wrap(t('Yaga.Ranks.Desc'), 'p').
    wrap(t('Yaga.Ranks.Settings.Desc'), 'p') .
    wrap(anchor(t('Yaga.Rank.Add'), 'rank/add', ['class' => 'Button']), 'p'),
    'div',
    ['class' => 'Wrap']
);
?>
<table id="Ranks" class="Sortable AltRows">
    <thead>
        <tr>
            <th><?php echo t('Name'); ?></th>
            <th><?php echo t('Description'); ?></th>
            <th><?php echo t('Yaga.Ranks.PointsReq'); ?></th>
            <th><?php echo t('Yaga.Ranks.PostsReq'); ?></th>
            <th><?php echo t('Yaga.Ranks.AgeReq'); ?></th>
            <th><?php echo t('Auto Award'); ?></th>
            <th><?php echo t('Options'); ?></th>
        </tr>
    </thead>
    <tbody>
        <?php
        $alt = 'Alt';
        foreach ($this->data('Ranks') as $rank) {
            $alt = $alt ? '' : 'Alt';
            $row = '';
            $row .= wrap($rank->Name, 'td');
            $row .= wrap($rank->Description, 'td');
            $row .= wrap($rank->PointReq, 'td');
            $row .= wrap($rank->PostReq, 'td');
            $row .= wrap($ageArray[$rank->AgeReq], 'td');
            $toggleText = ($rank->Enabled) ? t('Enabled') : t('Disabled');
            $activeClass = ($rank->Enabled) ? 'Active' : 'InActive';
            $row .= wrap(wrap(anchor($toggleText, 'rank/toggle/'.$rank->RankID, 'Hijack Button'), 'span', ['class' => "ActivateSlider ActivateSlider-{$activeClass}"]), 'td');
            $row .= wrap(anchor(t('Edit'), 'rank/edit/'.$rank->RankID, ['class' => 'Button']).anchor(t('Delete'), 'rank/delete/'.$rank->RankID, ['class' => 'Danger Popup Button']), 'td');
            echo wrap($row, 'tr', ['id' => 'RankID_'.$rank->RankID, 'data-rankid' => $rank->RankID, 'class' => $alt]);
        }
        ?>
    </tbody>
</table>
