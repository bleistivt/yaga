<?php if (!defined('APPLICATION')) exit();

/**
 * Contains render functions that can be used cross controller
 *
 * @package Yaga
 * @since 1.0
 * @copyright (c) 2013-2014, Zachary Doll
 */
if (!function_exists('renderReactionList')) {

    /**
     * Renders a list of available actions that also contains the current count of
     * reactions an item has received if allowed
     *
     * @since 1.0
     * @param int $id
     * @param string $type 'discussion', 'activity', or 'comment'
     * @return string Rendered list of actions available
     */
    function renderReactionList($id, $type) {
        $reactions = Yaga::reactionModel()->getList($id, $type);
        $showCount = Gdn::session()->checkPermission('Yaga.Reactions.View');
        $actionsString = '';
        foreach ($reactions as $action) {
            if (checkPermission($action->Permission)) {
                $countString = ($showCount && $action->Count) ? $action->Count : '';
                $actionsString .= anchor(
                    wrap('&nbsp;', 'span', ['class' => 'ReactSprite React-'.$action->ActionID.' '.$action->CssClass]) .
                    wrapIf($countString, 'span', ['class' => 'Count']) .
                    wrap($action->Name, 'span', ['class' => 'ReactLabel']), 'react/'.$type.'/'.$id.'/'.$action->ActionID,
                    [
                        'class' => 'Hijack ReactButton',
                        'title' => $action->Tooltip
                    ]
                );
            }
        }

        return wrap($actionsString, 'span', ['class' => 'ReactMenu']);
    }

}

if (!function_exists('renderReactionRecord')) {

    /**
     * Renders the reaction record for a specific item
     *
     * @since 1.0
     * @param int $id
     * @param string $type 'discussion', 'activity', or 'comment'
     * @return string Rendered list of existing reactions
     */
    function renderReactionRecord($id, $type) {
        $reactions = Yaga::reactionModel()->getRecord($id, $type);
        $limit = Gdn::config('Yaga.Reactions.RecordLimit');
        $reactionCount = count($reactions);
        $recordsString = '';

        foreach ($reactions as $i => $reaction) {
            // Limit the record if there are a lot of reactions
            if ($i < $limit || $limit <= 0) {
                $user = Gdn::userModel()->getID($reaction->UserID);
                $dateTitle = sprintf(Gdn::translate('Yaga.Reactions.RecordFormat'), $user->Name, $reaction->Name, Gdn_Format::date($reaction->DateInserted, '%B %e, %Y'));
                $string = userPhoto($user, ['Size' => 'Small', 'title' => $dateTitle]);
                $string .= '<span class="ReactSprite Reaction-'.$reaction->ActionID.' '.$reaction->CssClass.'"></span>';
                $wrapttributes = ['class' => 'UserReactionWrap', 'data-userid' => $user->UserID, 'title' => $dateTitle];
                $recordsString .= wrap($string, 'span', $wrapttributes);
            }
            // Display the 'and x more' message if there is a limit
            if ($limit > 0 && $i == $limit && $reactionCount > $limit) {
                $recordsString .= plural($reactionCount - $limit, 'Yaga.Reactions.RecordLimit.Single', 'Yaga.Reactions.RecordLimit.Plural');
            }
        }

        return wrap($recordsString, 'div', ['class' => 'ReactionRecord']);
    }

}

if (!function_exists('renderActionRow')) {

    /**
     * Renders an action row used to construct the action admin screen
     *
     * @since 1.0
     * @param stdClass $action
     * @return string
     */
    function renderActionRow($action) {
        $tr = '<tr id="ActionID_'.$action->ActionID.'">';

        $tr .= '<td><strong>'.$action->Name.'</strong></td>';

        $tr .= '<td>'.$action->Description.'</td>';

        $tr .= '<td>'.plural($action->AwardValue, '%s Point', '%s Points').'</td>';

        $tr .= '<td>';
        $tr .= wrap(
            wrap('&nbsp;', 'span', ['class' => 'ReactSprite React-'.$action->ActionID.' '.$action->CssClass])
                .wrapIf(rand(1, 18), 'span', ['class' => 'Count'])
                .wrap($action->Name, 'span', ['class' => 'ReactLabel']),
            'div',
            ['class' => 'Preview Reactions']
        );
        $tr .= '</td>';

        $tr .= '<td class="options">';
        $tr .= renderYagaOptionButtons('action/edit/'.$action->ActionID, 'action/delete/'.$action->ActionID);
        $tr .= '</td>';

        $tr .= '</tr>';

        return $tr;
    }
}

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

if (!function_exists('renderYagaToggle')) {

    /**
     * Renders a toggle slider to toggle badges or ranks.
     *
     * @since 2.0
     * @param string $url The url to POST to.
     * @param bool $enabled The sliders state.
     * @param string $id The #ID of the slider.
     */
    function renderYagaToggle($url, $enabled = false, $id = '') {
        $slider = $id ? '<div id="toggle-'.$id.'">' : '<div>';
        $slider .= wrap(
            anchor(
                '<div class="toggle-well"></div><div class="toggle-slider"></div>',
                $url,
                'Hijack',
                ['title' => Gdn::translate($enabled ? 'Enabled' : 'Disabled')]
            ),
            'span',
            ['class' => 'toggle-wrap toggle-wrap-'.($enabled ? 'on' : 'off')]
        );
        $slider .= '</div>';

        return $slider;
    }
}

if (!function_exists('renderYagaOptionButtons')) {

    /**
     * Renders the edit/delete buttons for Yaga dashboard pages.
     *
     * @since 2.0
     * @param string $editUrl The url to the edit page.
     * @param string $deleteUrl The url to the delete page.
     * @param bool $editPopup Should the edit page open in a popup?
     */
    function renderYagaOptionButtons($editUrl = '', $deleteUrl = '', $editPopup = true) {
        $options = '<div class="btn-group">';

        if ($editUrl) {
            $options .= anchor(
                dashboardSymbol('edit'),
                $editUrl,
                ($editPopup ? 'js-modal ': '').'btn btn-icon',
                ['title' => Gdn::translate('Edit')]
            );
        }

        if ($deleteUrl) {
            $options .= anchor(
                dashboardSymbol('delete'),
                $deleteUrl,
                'js-modal-confirm btn btn-icon',
                ['title' => Gdn::translate('Delete')]
            );
        }

        $options .= '</div>';

        return $options;
    }
}
