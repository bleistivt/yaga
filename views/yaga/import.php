<?php if (!defined('APPLICATION')) exit();

use Yaga;

/* Copyright 2014 Zachary Doll */

$transportType = $this->data('TransportType');
echo wrap($this->title(), 'h1');
echo $this->Form->open(['enctype' => 'multipart/form-data']);
echo $this->Form->errors();

echo wrap(wrap(t("Yaga.$transportType.Desc"), 'div'), 'div', ['class' => 'Wrap']);
?>
<ul>
    <li>
        <?php
        echo $this->Form->label('Yaga.Transport.File', 'FileUpload');
        echo $this->Form->input('FileUpload', 'file');
        ?>
    </li>
    <li>
        <?php
        echo $this->Form->label('Yaga.Reactions', 'Action');
        echo $this->Form->checkbox('Action');
        ?>
    </li>
    <li>
        <?php
        echo $this->Form->label('Yaga.Badges', 'Badge');
        echo $this->Form->checkbox('Badge');
        ?>
    </li>
    <li>
        <?php
        echo $this->Form->label('Yaga.Ranks', 'Rank');
        echo $this->Form->checkbox('Rank');
        ?>
    </li>
</ul>
<?php
echo $this->Form->close($transportType);
