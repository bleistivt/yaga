<?php if (!defined('APPLICATION')) exit();

/* Copyright 2013 Zachary Doll */

// Grab the rules so we can render the first criteria form by default
$rules = RulesController::getRules();
$ruleClass = key($rules);

// Use the defined rule class if we are editing
if (property_exists($this, 'Badge')) {
    $ruleClass = $this->Badge->RuleClass;
}

if (class_exists($ruleClass)) {
    $rule = new $ruleClass();
} else {
    $rule = new UnknownRule();
}

echo heading($this->title());

echo $this->Form->open(['enctype' => 'multipart/form-data', 'class' => 'Badge']);
echo $this->Form->errors();
?>

<ul>
    <li class="form-group">
        <div class="label-wrap">
            <?php
            echo $this->Form->label('Photo', 'PhotoUpload');
            $photo = $this->Form->getValue('Photo');
            if ($photo) {
                echo '<br />';
                echo img($photo);
                if ($photo !== Gdn::config('Yaga.Badges.DefaultPhoto')) {
                    echo '<br />'.anchor(
                        Gdn::translate('Delete Photo'),
                        combinePaths(['badge/deletephoto', $this->Badge->BadgeID, Gdn::session()->transientKey()]),
                        'btn btn-primary js-modal-confirm',
                        ['data-body' => sprintf(Gdn::translate('Are you sure you want to delete this %s?'), Gdn::translate('Photo'))]
                    );
                }
            }
            ?>
        </div>
        <div class="input-wrap">
            <?php echo $this->Form->input('PhotoUpload', 'file'); ?>
        </div>
    </li>
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
            <?php echo $this->Form->label('Description', 'Description'); ?>
        </div>
        <div class="input-wrap">
            <?php echo $this->Form->textBox('Description', ['multiline' => true]); ?>
        </div>
    </li>
    <li class="form-group">
        <div class="label-wrap">
            <?php echo $this->Form->label('Rule', 'RuleClass'); ?>
        </div>
        <div class="input-wrap">
            <?php echo $this->Form->dropdown('RuleClass', $rules); ?>
        </div>
    </li>
    <li class="form-group">
        <div class="label-wrap">
            <div id="Rule-Description">
                <?php echo $rule->description(); ?>
            </div>
        </div>
        <div class="input-wrap">
            <div id="Rule-Criteria">
                <?php echo $rule->form($this->Form); ?>
            </div>
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
            <?php echo $this->Form->label('Automatically Award', 'Enabled'); ?>
        </div>
        <div class="input-wrap">
            <?php echo $this->Form->toggle('Enabled'); ?>
        </div>
    </li>
</ul>

<?php
echo $this->Form->close('Save');
