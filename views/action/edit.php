<?php if (!defined('APPLICATION')) exit();

/* Copyright 2013 Zachary Doll */
if (property_exists($this, 'Action')) {
    echo heading(Gdn::translate('Yaga.Action.Edit'));
} else {
    echo heading(Gdn::translate('Yaga.Action.Add'));
}

$originalCssClass = $this->Form->getValue('CssClass');

echo $this->Form->open(['class' => 'Action']);
echo $this->Form->errors();
?>
<ul>
    <li class="form-group">
        <div class="label-wrap">
            <?php echo $this->Form->label('Name', 'Name'); ?>
        </div>
        <div class="input-wrap">
            <?php echo $this->Form->textBox('Name'); ?>
        </div>
    </li>
    <li class="form-group" id="ActionIcons">
        <div class="label-wrap">
            <?php echo $this->Form->label('Icon'); ?>
        </div>
        <div class="input-wrap">
            <?php 
            foreach ($this->data('Icons') as $icon) {
                $class = 'React'.$icon;
                $selected = '';
                if ($originalCssClass == $class) {
                    $selected = 'Selected';
                }
                echo img('plugins/yaga/design/images/action-icons/'.$icon.'.png', ['title' => $icon, 'data-class' => $class, 'class' => $selected]);
            }
            ?>
        </div>
    </li>
    <li class="form-group">
        <div class="label-wrap">
            <?php echo $this->Form->label('Description', 'Description'); ?>
        </div>
        <div class="input-wrap">
            <?php echo $this->Form->textBox('Description'); ?>
        </div>
    </li>
    <li class="form-group">
        <div class="label-wrap">
            <?php echo $this->Form->label('Tooltip', 'Tooltip'); ?>
        </div>
        <div class="input-wrap">
            <?php echo $this->Form->textBox('Tooltip'); ?>
        </div>
    </li>
    <li class="form-group">
        <div class="label-wrap">
            <?php echo $this->Form->label('Award Value', 'AwardValue'); ?>
        </div>
        <div class="input-wrap">
            <?php echo $this->Form->textBox('AwardValue'); ?>
        </div>
    </li>
    <li class="form-group">
        <div class="label-wrap">
            <?php
            echo $this->Form->label('Css Class', 'CssClass');
            echo wrap(Gdn::translate('Optional'), 'div', ['class' => 'info']);
            ?>
        </div>
        <div class="input-wrap">
            <?php echo $this->Form->textBox('CssClass'); ?>
        </div>
    </li>
    <li class="form-group">
        <div class="label-wrap">
            <?php
            echo $this->Form->label('Permission', 'Permission');
            echo wrap(Gdn::translate('Yaga.Action.PermDesc'), 'div', ['class' => 'info']);
            ?>
        </div>
        <div class="input-wrap">
            <?php echo $this->Form->dropdown('Permission', $this->data('Permissions')); ?>
        </div>
    </li>
</ul>

<?php
echo $this->Form->close('Save');
