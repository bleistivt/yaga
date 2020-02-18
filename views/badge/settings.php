<?php if (!defined('APPLICATION')) exit();

/* Copyright 2013 Zachary Doll */

$rules = $this->data('Rules');

echo heading($this->title(), Gdn::translate('Yaga.Badge.Add'), 'badge/add', 'btn btn-primary');

echo helpAsset(Gdn::translate('Yaga.Badges'), Gdn::translate('Yaga.Badges.Desc'));

echo wrap(Gdn::translate('Yaga.Badges.Settings.Desc'), 'div', ['class' => 'padded']);
?>

<div class="table-wrap">
    <table id="Badges" class="table-data Sortable">
        <thead>
            <tr>
                <th class="column-sm"><?php echo Gdn::translate('Image'); ?></th>
                <th><?php echo Gdn::translate('Name'); ?></th>
                <th class="column-lg"><?php echo Gdn::translate('Description'); ?></th>
                <th><?php echo Gdn::translate('Rule'); ?></th>
                <th class="column-sm"><?php echo Gdn::translate('Award Value'); ?></th>
                <th class="column-sm"><?php echo Gdn::translate('Auto Award'); ?></th>
                <th class="options column-sm"></th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach ($this->data('Badges') as $badge) {
                $tr = '<tr id="BadgeID_'.$badge->BadgeID.'" data-badgeid="'.$badge->BadgeID.'">';

                $tr .= wrap(anchor(
                    img($badge->Photo, ['class' => 'BadgePhoto']),
                    '/yaga/badges/'.$badge->BadgeID.'/'.Gdn_Format::url($badge->Name),
                    ['title' => Gdn::translate('Yaga.Badge.DetailLink')]
                ), 'td');

                $tr .= wrap(wrap($badge->Name, 'strong'), 'td');

                $tr .= wrap($badge->Description, 'td');

                $tr .= wrap($rules[$badge->RuleClass] ?? Gdn::translate('Yaga.Rules.UnknownRule'), 'td');

                $tr .= wrap($badge->AwardValue, 'td');

                $tr .= wrap(renderYagaToggle('badge/toggle/'.$badge->BadgeID, $badge->Enabled, $badge->BadgeID), 'td');

                $tr .= '<td class="options"><div class="btn-group">';
                $tr .= anchor(
                    dashboardSymbol('edit'),
                    'badge/edit/'.$badge->BadgeID,
                    'btn btn-icon',
                    ['title' => Gdn::translate('Edit')]
                );
                $tr .= anchor(
                    dashboardSymbol('delete'),
                    'badge/delete/'.$badge->BadgeID,
                    'js-modal-confirm btn btn-icon',
                    ['title' => Gdn::translate('Delete')]
                );
                $tr .= '</div></td>';

                $tr .= '</tr>';

                echo $tr;
            }
            ?>
        </tbody>
    </table>
</div>

<?php
// This page cannot have a pager as this would interfere with sorting.
//PagerModule::write(['Sender' => $this, 'View' => 'pager-dashboard']);
