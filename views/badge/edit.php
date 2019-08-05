<?php if (!defined('APPLICATION')) exit();

use Yaga;

/* Copyright 2013 Zachary Doll */

// Grab the rules so we can render the first criteria form by default
$rules = RulesController::getRules();
$ruleClass = key($rules);

// Use the defined rule class if we are editing
if(property_exists($this, 'Badge')) {
    $ruleClass = $this->Badge->RuleClass;
}

if(class_exists($ruleClass)) {
    $rule = new $ruleClass();
}
else {
    $rule = new UnknownRule();
}

echo wrap($this->title(), 'h1');

echo $this->Form->open(['enctype' => 'multipart/form-data', 'class' => 'Badge']);
echo $this->Form->errors();
?>
<ul>
    <li>
        <?php
        echo $this->Form->label('Photo', 'PhotoUpload');
        $photo = $this->Form->getValue('Photo');
        if ($photo) {
            echo img($photo);
            echo '<br />'.Anchor(
                t('Delete Photo'),
                combinePaths(['badge/deletephoto', $this->Badge->BadgeID, Gdn::session()->transientKey()]),
                'Button Danger PopConfirm'
            );
        }
        echo $this->Form->input('PhotoUpload', 'file');
        ?>
    </li>
    <li>
        <?php
        echo $this->Form->label('Name', 'Name');
        echo $this->Form->textBox('Name');
        ?>
    </li>
    <li>
        <?php
        echo $this->Form->label('Description', 'Description');
        echo $this->Form->textBox('Description', ['multiline' => true]);
        ?>
    </li>
    <li>
        <?php
        echo $this->Form->label('Rule', 'RuleClass');
        echo $this->Form->dropdown('RuleClass', $rules);
        ?>
    </li>
    <li id="Rule-Description">
        <?php
        echo $rule->description();
        ?>
    </li>
    <li id="Rule-Criteria">
        <?php
        echo $rule->form($this->Form);
        ?>
    </li>
    <li>
        <?php
        echo $this->Form->label('Award Value', 'AwardValue');
        echo $this->Form->textBox('AwardValue');
        ?>
    </li>
    <li>
        <?php
        echo $this->Form->label('Automatically Award', 'Enabled');
        echo $this->Form->checkBox('Enabled');
        ?>
    </li>

</ul>
<?php
echo $this->Form->close('Save');
