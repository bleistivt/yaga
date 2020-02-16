<?php if (!defined('APPLICATION')) exit();

use Yaga;

/* Copyright 2013-2014 Zachary Doll */

echo wrap($this->title(), 'h1');

echo $this->Form->open();
echo $this->Form->errors();
?>
<ul>
    <li>
        <?php
        echo $this->Form->label('Name', 'Name');
        echo $this->Form->textBox('Name');
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
        echo $this->Form->label('Yaga.Ranks.PointsReq', 'PointReq');
        echo $this->Form->textBox('PointReq');
        ?>
    </li>
    <li>
        <?php
        echo $this->Form->label('Yaga.Ranks.PostsReq', 'PostReq');
        echo $this->Form->textBox('PostReq');
        ?>
    </li>
    <li>
        <?php
        echo $this->Form->label('Yaga.Ranks.AgeReq', 'AgeReq');
        echo $this->Form->dropdown('AgeReq', ageArray());
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
    echo wrap(Gdn::translate('Yaga.Perks'), 'h3');
?>
<ul>
    <li>
        <?php
        echo $this->Form->label('Role', 'Role');
        echo $this->Form->dropdown('Role', $this->data('Roles'), ['IncludeNULL' => true]);
        ?>
    </li>
    <li>
    <?php
    echo renderPerkConfigurationForm('Garden.EditContentTimeout', 'Yaga.Perks.EditTimeout', [
        '0' => Gdn::translate('Authors may never edit'),
        '350' => sprintf(Gdn::translate('Authors may edit for %s'), Gdn::translate('5 minutes')),
        '900' => sprintf(Gdn::translate('Authors may edit for %s'), Gdn::translate('15 minutes')),
        '3600' => sprintf(Gdn::translate('Authors may edit for %s'), Gdn::translate('1 hour')),
        '14400' => sprintf(Gdn::translate('Authors may edit for %s'), Gdn::translate('4 hours')),
        '86400' => sprintf(Gdn::translate('Authors may edit for %s'), Gdn::translate('1 day')),
        '604800' => sprintf(Gdn::translate('Authors may edit for %s'), Gdn::translate('1 week')),
        '2592000' => sprintf(Gdn::translate('Authors may edit for %s'), Gdn::translate('1 month')),
        '-1' => Gdn::translate('Authors may always edit')
    ]);
    ?>
    </li>
    <li>
    <?php    
        echo renderPerkPermissionForm('Garden.Curation.Manage', 'Yaga.Perks.Curation');
    ?>
    </li>
    <li>
        <?php
        echo renderPerkPermissionForm('Plugins.Signatures.Edit', 'Yaga.Perks.Signatures');
        ?>
    </li>
    <li>
        <?php
        echo renderPerkPermissionForm('Plugins.Tagging.Add', 'Yaga.Perks.Tags');
        ?>
    </li>
    <li>
        <?php
        echo renderPerkConfigurationForm('Plugins.Emotify.FormatEmoticons', 'Yaga.Perks.Emoticons');
        ?>
    </li>
    <li>
        <?php
        echo renderPerkConfigurationForm('Garden.Format.MeActions', 'Yaga.Perks.MeActions');
        ?>
    </li>
    <?php
    $this->fireEvent('PerkOptions');
    ?>
</ul>
<?php
echo $this->Form->close('Save');
