<?php if (!defined('APPLICATION')) exit();

/* Copyright 2013 Zachary Doll */
if (property_exists($this, 'Action')) {
    echo heading(Gdn::translate('Yaga.Action.Edit'));
} else {
    echo heading(Gdn::translate('Yaga.Action.Add'));
}

$originalCssClass = $this->Form->getValue('CssClass');

$icons = '';
foreach ($this->data('Icons') as $icon) {
    $class = 'React'.$icon;
    $selected = '';
    if ($originalCssClass == $class) {
        $selected = 'Selected';
    }
    $icons .= img(
        'plugins/yaga/design/images/action-icons/'.$icon.'.png',
        ['title' => $icon, 'data-class' => $class, 'class' => $selected]
    );
}
$icons = wrap($icons, 'div', ['id' => 'ActionIcons']);

echo helpAsset(
    Gdn::translate('Yaga.Action.IconHelpTitle'),
    Gdn::translate('Yaga.Action.IconHelpContent').$icons
);

echo $this->Form->open(['enctype' => 'multipart/form-data', 'class' => 'Action']);
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
    <li class="form-group">
        <div class="label-wrap">
            <?php
            echo $this->Form->label('Photo', 'PhotoUpload');
            echo wrap(Gdn::translate('Yaga.Action.PhotoDesc'), 'div', ['class' => 'info']);
            $photo = $this->Form->getValue('Photo');
            if ($photo) {
                echo '<br />';
                echo img($photo);
                echo '<br />'.anchor(
                    Gdn::translate('Delete Photo'),
                    'action/deletephoto/'.$this->Action->ActionID,
                    'btn btn-primary js-modal-confirm',
                    ['data-body' => sprintf(Gdn::translate('Are you sure you want to delete this %s?'), Gdn::translate('Photo'))]
                );
            }
            ?>
        </div>
        <div class="input-wrap">
            <?php echo $this->Form->input('PhotoUpload', 'file'); ?>
        </div>
    </li>
    <li class="form-group">
        <div class="label-wrap">
            <?php
            echo $this->Form->label('Emoji', 'Emoji');
            echo wrap(Gdn::translate('Yaga.Action.EmojiDesc'), 'div', ['class' => 'info']);
            ?>
        </div>
        <div class="input-wrap">
            <?php echo $this->Form->textBox('Emoji'); ?>
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
            <?php echo $this->Form->textBox('AwardValue', ['type' => 'number']); ?>
        </div>
    </li>
    <li class="form-group">
        <div class="label-wrap">
            <?php
            echo $this->Form->label('Css Class', 'CssClass');
            echo wrap(Gdn::translate('Yaga.Action.CssDesc'), 'div', ['class' => 'info']);
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
