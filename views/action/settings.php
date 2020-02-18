<?php if (!defined('APPLICATION')) exit();

/* Copyright 2013 Zachary Doll */

echo heading($this->title(), Gdn::translate('Yaga.Action.Add'), 'action/add', 'js-modal btn btn-primary');

echo helpAsset(Gdn::translate('Yaga.Action'), Gdn::translate('Yaga.Actions.Desc'));

echo wrap(Gdn::translate('Yaga.Actions.Settings.Desc'), 'div', ['class' => 'padded']);
?>

<div class="table-wrap">
    <table id="Actions" class="table-data Sortable">
        <thead>
            <tr>
                <th><?php echo Gdn::translate('Name'); ?></th>
                <th class="column-lg"><?php echo Gdn::translate('Description'); ?></th>
                <th><?php echo Gdn::translate('Award Value'); ?></th>
                <th><?php echo Gdn::translate('Preview'); ?></th>
                <th class="options"></th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach ($this->data('Actions') as $action) {
                echo renderActionRow($action);
            }
            ?>
        </tbody>
    </table>
</div>
