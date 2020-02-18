<?php if (!defined('APPLICATION')) exit();

/* Copyright 2014 Zachary Doll */

$transportType = $this->data('TransportType');

echo heading($this->title());

echo wrap(Gdn::translate("Yaga.$transportType.Desc"), 'div', ['class' => 'padded']);

echo $this->Form->open(['enctype' => 'multipart/form-data']);
echo $this->Form->errors();

?>
<ul>
    <?php if ($transportType === 'Import') { ?>
        <li class="form-group">
            <div class="label-wrap">
                <?php echo $this->Form->label('Yaga.Transport.File', 'FileUpload'); ?>
            </div>
            <div class="input-wrap">
                <?php echo $this->Form->input('FileUpload', 'file'); ?>
            </div>
        </li>
    <?php } ?>
    <li class="form-group">
        <div class="label-wrap">
            <?php echo $this->Form->label('Yaga.Reactions', 'Action'); ?>
        </div>
        <div class="input-wrap">
            <?php echo $this->Form->checkbox('Action'); ?>
        </div>
    </li>
    <li class="form-group">
        <div class="label-wrap">
            <?php echo $this->Form->label('Yaga.Badges', 'Badge'); ?>
        </div>
        <div class="input-wrap">
            <?php echo $this->Form->checkbox('Badge'); ?>
        </div>
    </li>
    <li class="form-group">
        <div class="label-wrap">
            <?php echo $this->Form->label('Yaga.Ranks', 'Rank'); ?>
        </div>
        <div class="input-wrap">
            <?php echo $this->Form->checkbox('Rank'); ?>
        </div>
    </li>
</ul>

<?php
echo $this->Form->close($transportType);
