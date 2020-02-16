<?php if (!defined('APPLICATION')) exit();

/* Copyright 2013 Zachary Doll */

echo wrap($this->title(), 'h1');
echo wrap(wrap(Gdn::translate('Yaga.Actions.Desc'), 'div'), 'div', ['class' => 'Wrap']);
echo wrap(wrap(Gdn::translate('Yaga.Actions.Settings.Desc'), 'div'), 'div', ['class' => 'Wrap']);
echo wrap(anchor(Gdn::translate('Yaga.Action.Add'), 'action/add', ['class' => 'Popup Button']), 'div', ['class' => 'Wrap']);
?>
<h3><?php echo Gdn::translate('Yaga.Actions.Current'); ?></h3>
<ol id="Actions" class="Sortable">
    <?php
    foreach ($this->data('Actions') as $action) {
        echo renderActionRow($action);
    }
    ?>
</ol>
