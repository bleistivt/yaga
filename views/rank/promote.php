<?php if (!defined('APPLICATION')) exit();

use Yaga;

/* Copyright 2013 Zachary Doll */

$ranks = $this->data('Ranks');
$username = $this->data('Username', 'Unknown');

echo '<div id="UserRankForm">';
echo wrap(sprintf(t('Yaga.Rank.Promote.Format'), $username), 'h1');
echo $this->Form->open();
echo $this->Form->errors();

echo wrap(
            wrap(
                $this->Form->label('Yaga.Rank', 'RankID') .
                $this->Form->dropdown('RankID', $ranks),
                'li') .
            wrap(
                $this->Form->label('Activity', 'RecordActivity') .
                $this->Form->checkBox('RecordActivity', 'Yaga.Rank.RecordActivity'),
                'li') .
            wrap(
                $this->Form->label('Yaga.Rank.Progression', 'RankProgression') .
                $this->Form->checkBox('RankProgression', 'Yaga.Rank.Progression.Desc', ['Value' => 1, 'Checked' => 'checked']),
                'li') .
            wrap(
                            anchor(t('Cancel'), 'rank/settings'),
                            'li'),
                'ul'
);

echo $this->Form->close('Save');

echo '</div>';
