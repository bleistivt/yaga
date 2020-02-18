<?php if (!defined('APPLICATION')) exit();

/* Copyright 2013 Zachary Doll */

$photoString = '';
$delButton = '';
$photo = Gdn::config('Yaga.Ranks.Photo', false);
if ($photo) {
    $photoString = img($photo);
    $delButton = anchor(Gdn::translate('Delete Photo'), combinePaths(['rank/deletephoto', Gdn::session()->transientKey()]), 'Button Danger PopConfirm');
}
$ageArray = ageArray();

echo heading($this->title());

echo wrap(
    $photoString.
    $this->Form->open(['enctype' => 'multipart/form-data', 'class' => 'Rank']).
    $this->Form->errors().
    wrap(
        wrap(
        $this->Form->label('Photo', 'PhotoUpload').
        wrap(Gdn::translate('Yaga.Rank.Photo.Desc'), 'div', ['class' => 'Info']).
        $delButton .
        $this->Form->input('PhotoUpload', 'file').
        $this->Form->button('Save', ['class' => 'Button']), 'li'),
        'ul'
    ).
    $this->Form->close('', ' '),
    'div',
    ['class' => 'Aside']
);

echo wrap(
    wrap(Gdn::translate('Yaga.Ranks.Desc'), 'p').
    wrap(Gdn::translate('Yaga.Ranks.Settings.Desc'), 'p').
    wrap(anchor(Gdn::translate('Yaga.Rank.Add'), 'rank/add', ['class' => 'Button']), 'p'),
    'div',
    ['class' => 'Wrap']
);
?>
<table id="Ranks" class="Sortable AltRows">
    <thead>
        <tr>
            <th><?php echo Gdn::translate('Name'); ?></th>
            <th><?php echo Gdn::translate('Description'); ?></th>
            <th><?php echo Gdn::translate('Yaga.Ranks.PointsReq'); ?></th>
            <th><?php echo Gdn::translate('Yaga.Ranks.PostsReq'); ?></th>
            <th><?php echo Gdn::translate('Yaga.Ranks.AgeReq'); ?></th>
            <th><?php echo Gdn::translate('Auto Award'); ?></th>
            <th><?php echo Gdn::translate('Options'); ?></th>
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
            $toggleText = ($rank->Enabled) ? Gdn::translate('Enabled') : Gdn::translate('Disabled');
            $activeClass = ($rank->Enabled) ? 'Active' : 'InActive';
            $row .= wrap(wrap(anchor($toggleText, 'rank/toggle/'.$rank->RankID, 'Hijack Button'), 'span', ['class' => "ActivateSlider ActivateSlider-{$activeClass}"]), 'td');
            $row .= wrap(anchor(Gdn::translate('Edit'), 'rank/edit/'.$rank->RankID, ['class' => 'Button']).anchor(Gdn::translate('Delete'), 'rank/delete/'.$rank->RankID, ['class' => 'Danger Popup Button']), 'td');
            echo wrap($row, 'tr', ['id' => 'RankID_'.$rank->RankID, 'data-rankid' => $rank->RankID, 'class' => $alt]);
        }
        ?>
    </tbody>
</table>
