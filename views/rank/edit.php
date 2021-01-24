<?php if (!defined('APPLICATION')) exit();

/* Copyright 2013-2014 Zachary Doll */

if (!function_exists('renderPerkPermissionForm')) {

    /**
     * Render a simple permission perk form
     *
     * @since 1.0
     * @param string $perm The permission you want to grant/revoke
     * @param string $label Translation code used on the form
     */
    function renderPerkPermissionForm($perm, $label) {
        $form = Gdn::controller()->Form;
        $fieldname = 'Perm'.$perm;

        $string = '<div class="label-wrap">';
        $string .= $form->label($label, $fieldname);
        $string .= '</div><div class="input-wrap">';
        $string .= $form->dropdown($fieldname, [
            '' => Gdn::translate('Default'),
            'grant' => Gdn::translate('Grant'),
            'revoke' => Gdn::translate('Revoke')
        ]);
        $string .= '</div>';

        return $string;
    }
}

if (!function_exists('renderPerkConfigurationForm')) {

    /**
     * Render a perk form for the specified configuration
     *
     * @since 1.0
     * @param string $config The configuration you want to override (i.e. 'Vanilla.EditTimeout')
     * @param string $label Translation code used on the form
     * @param array $options The options you want shown instead of default/enable/disable.
     */
    function renderPerkConfigurationForm($config, $label, $options = null) {
        if (is_null($options)) {
            // Default to a true/false/default array
            $options = [
                '' => Gdn::translate('Default'),
                1 => Gdn::translate('Enabled'),
                0 => Gdn::translate('Disabled')
            ];
        }
        // Add a default option
        $options = ['' => Gdn::translate('Default')] + $options;
        $form = Gdn::controller()->Form;
        $fieldname = 'Conf'.$config;

        $string = '<div class="label-wrap">';
        $string .= $form->label($label, $fieldname);
        $string .= '</div><div class="input-wrap">';
        $string .= $form->dropdown($fieldname, $options);
        $string .= '</div>';

        return $string;
    }
}

echo heading($this->title());

echo $this->Form->open();
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
            <?php echo $this->Form->label('Description', 'Description'); ?>
        </div>
        <div class="input-wrap">
            <?php echo $this->Form->textBox('Description'); ?>
        </div>
    </li>
    <li class="form-group">
        <div class="label-wrap">
            <?php echo $this->Form->label('Yaga.Ranks.PointsReq', 'PointReq'); ?>
        </div>
        <div class="input-wrap">
            <?php echo $this->Form->textBox('PointReq', ['type' => 'number']); ?>
        </div>
    </li>
    <li class="form-group">
        <div class="label-wrap">
            <?php echo $this->Form->label('Yaga.Ranks.PostsReq', 'PostReq'); ?>
        </div>
        <div class="input-wrap">
            <?php echo $this->Form->textBox('PostReq', ['type' => 'number']); ?>
        </div>
    </li>
    <li class="form-group">
        <div class="label-wrap">
            <?php echo $this->Form->label('Yaga.Ranks.AgeReq', 'AgeReq'); ?>
        </div>
        <div class="input-wrap">
            <?php echo $this->Form->dropdown('AgeReq', ageArray()); ?>
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
    echo subheading(Gdn::translate('Yaga.Perks'));
?>
<ul>
    <li class="form-group">
        <div class="label-wrap">
            <?php echo $this->Form->label('Role', 'Role'); ?>
        </div>
        <div class="input-wrap">
            <?php echo $this->Form->dropdown('Role', $this->data('Roles'), ['IncludeNull' => true]); ?>
        </div>
    </li>
    <li class="form-group">
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
    <li class="form-group">
        <?php    
        echo renderPerkPermissionForm('Garden.Curation.Manage', 'Yaga.Perks.Curation');
        ?>
    </li>
    <li class="form-group">
        <?php
        echo renderPerkPermissionForm('Plugins.Signatures.Edit', 'Yaga.Perks.Signatures');
        ?>
    </li>
    <li class="form-group">
        <?php
        echo renderPerkPermissionForm('Plugins.Tagging.Add', 'Yaga.Perks.Tags');
        ?>
    </li>
    <li class="form-group">
        <?php
        echo renderPerkConfigurationForm('Plugins.Emotify.FormatEmoticons', 'Yaga.Perks.Emoticons');
        ?>
    </li>
    <li class="form-group">
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
