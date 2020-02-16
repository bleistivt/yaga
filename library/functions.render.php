<?php if (!defined('APPLICATION')) exit();

use Yaga;

/**
 * Contains render functions that can be used cross controller
 * 
 * @package Yaga
 * @since 1.0
 * @copyright (c) 2013-2014, Zachary Doll
 */
if (!function_exists('RenderReactionList')) {

    /**
     * Renders a list of available actions that also contains the current count of
     * reactions an item has received if allowed
     *
     * @since 1.0
     * @param int $iD
     * @param string $type 'discussion', 'activity', or 'comment'
     * @return string Rendered list of actions available
     */
    function renderReactionList($iD, $type) {
        $reactions = Yaga::reactionModel()->getList($iD, $type);
        $showCount = Gdn::session()->checkPermission('Yaga.Reactions.View');
        $actionsString = '';
        foreach ($reactions as $action) {
            if (checkPermission($action->Permission)) {
                $countString = ($showCount && $action->Count) ? $action->Count : '';
                $actionsString .= anchor(
                    wrap('&nbsp;', 'span', ['class' => 'ReactSprite React-'.$action->ActionID.' '.$action->CssClass]) .
                    wrapIf($countString, 'span', ['class' => 'Count']) .
                    wrap($action->Name, 'span', ['class' => 'ReactLabel']), 'react/'.$type.'/'.$iD.'/'.$action->ActionID,
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

if (!function_exists('RenderReactionRecord')) {

    /**
     * Renders the reaction record for a specific item
     * 
     * @since 1.0
     * @param int $iD
     * @param string $type 'discussion', 'activity', or 'comment'
     * @return string Rendered list of existing reactions
     */
    function renderReactionRecord($iD, $type) {
        $reactions = Yaga::reactionModel()->getRecord($iD, $type);
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

if (!function_exists('RenderActionRow')) {

    /**
     * Renders an action row used to construct the action admin screen
     * 
     * @since 1.0
     * @param stdClass $action
     * @return string
     */
    function renderActionRow($action) {
        return wrap(
            wrap(
                anchor(Gdn::translate('Edit'), 'action/edit/'.$action->ActionID, ['class' => 'Popup Button']).
                anchor(Gdn::translate('Delete'), 'action/delete/'.$action->ActionID, ['class' => 'Popup Button']),
                'div',
                ['class' => 'Tools']
            ).
            wrap(
                wrap($action->Name, 'h4').
                wrap(
                    wrap($action->Description, 'span').' ' .
                    wrap(plural($action->AwardValue, '%s Point', '%s Points'), 'span'), 'div', ['class' => 'Meta']).
                wrap(
                    wrap('&nbsp;', 'span', ['class' => 'ReactSprite React-'.$action->ActionID.' '.$action->CssClass]).
                    wrapIf(rand(0, 18), 'span', ['class' => 'Count']) .
                    wrap($action->Name, 'span', ['class' => 'ReactLabel']),
                    'div',
                    ['class' => 'Preview Reactions']
                ),
                'div',
                ['class' => 'Action']
            ),
            'li',
            ['id' => 'ActionID_'.$action->ActionID]
        );
    }
}

if (!function_exists('RenderPerkPermissionForm')) {

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

        $string = $form->label($label, $fieldname);
        $string .= $form->dropdown($fieldname, [
            '' => Gdn::translate('Default'),
            'grant' => Gdn::translate('Grant'),
            'revoke' => Gdn::translate('Revoke')
        ]);

        return $string;
    }
}

if (!function_exists('RenderPerkConfigurationForm')) {

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

        $string = $form->label($label, $fieldname);
        $string .= $form->dropdown($fieldname, $options);

        return $string;
    }
}

