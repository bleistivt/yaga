<?php if (!defined('APPLICATION')) exit();

use Yaga;

/* Copyright 2013 Zachary Doll */
if(property_exists($this, 'Action')) {
    echo wrap(Gdn::translate('Yaga.Action.Edit'), 'h1');
}
else {
    echo wrap(Gdn::translate('Yaga.Action.Add'), 'h1');
}

$originalCssClass = $this->Form->getValue('CssClass');

echo $this->Form->open(['class' => 'Action']);
echo $this->Form->errors();
?>
<ul>
    <li>
        <?php
        echo $this->Form->label('Name', 'Name');
        echo $this->Form->textBox('Name');
        ?>
    </li>
    <li id="ActionIcons">
        <?php
        echo $this->Form->label('Icon');
        foreach ($this->data('Icons') as $icon) {
            $class = 'React'.$icon;
            $selected = '';
            if ($originalCssClass == $class) {
                $selected = 'Selected';
            }
            echo img('plugins/yaga/design/images/action-icons/'.$icon.'.png', ['title' => $icon, 'data-class' => $class, 'class' => $selected]);
        }
        ?>
    </li>
    <li>
        <?php
        echo $this->Form->label('Description', 'Description');
        echo $this->Form->textBox('Description');
        ?>
    </li>
    <li>
        <?php
        echo $this->Form->label('Tooltip', 'Tooltip');
        echo $this->Form->textBox('Tooltip');
        ?>
    </li>
    <li>
        <?php
        echo $this->Form->label('Award Value', 'AwardValue');
        echo $this->Form->textBox('AwardValue');
        ?>
    </li>
    <li id="AdvancedActionSettings">
        <span><?php echo Gdn::translate('Advanced Settings'); ?></span>
        <div>
                <?php
                echo $this->Form->label('Css Class', 'CssClass');
                echo $this->Form->textBox('CssClass');
                ?>
            </div>
            <div>
                <?php
                echo $this->Form->label('Permission', 'Permission');
                echo wrap(Gdn::translate('Yaga.Action.PermDesc'), 'p');
                echo $this->Form->dropdown('Permission', $this->data('Permissions'));
                ?>
            </div>
    </li>
</ul>
<?php
echo $this->Form->close('Save');
