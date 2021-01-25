<?php if (!defined('APPLICATION')) exit();

use Vanilla\Formatting\DateTimeFormatter;

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
        $reactions = Gdn::getContainer()->get(ReactionModel::class)->getList($id, $type);
        $showCount = Gdn::session()->checkPermission('Yaga.Reactions.View');
        $actionsString = '';
        foreach ($reactions as $action) {
            if (checkPermission($action->Permission)) {
                $countString = ($showCount && $action->Count) ? $action->Count : '';
                $actionsString .= anchor(
                    renderYagaActionIcon($action, 'React').
                    wrapIf($countString, 'span', ['class' => 'Count']).
                    wrap($action->Name, 'span', ['class' => 'ReactLabel']), 'react/'.$type.'/'.$id.'/'.$action->ActionID,
                    [
                        'class' => 'Hijack ReactButton',
                        'title' => $action->Tooltip,
                        'rel' => 'nofollow'
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
        $reactions = Gdn::getContainer()->get(ReactionModel::class)->getRecord($id, $type);
        $limit = Gdn::config('Yaga.Reactions.RecordLimit');
        $reactionCount = count($reactions);
        $recordsString = '';
        $dateFormatter = Gdn::getContainer()->get(DateTimeFormatter::class);

        foreach ($reactions as $i => $reaction) {
            // Limit the record if there are a lot of reactions
            if ($i < $limit || $limit <= 0) {
                $user = Gdn::userModel()->getID($reaction->UserID);
                $dateTitle = sprintf(
                    Gdn::translate('Yaga.Reactions.RecordFormat'),
                    $user->Name,
                    $reaction->Name,
                    $dateFormatter->formatDate($reaction->DateInserted, false, '%B %e, %Y')
                );
                $string = userPhoto($user, ['Size' => 'Small', 'title' => $dateTitle]);
                $string .= renderYagaActionIcon($reaction, 'React');
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

if (!function_exists('renderYagaActionIcon')) {

    /**
     * Renders an action icon which may be an image or an emoji.
     *
     * @since 2.1
     * @param object $url The action.
     * @param string $id The class determining the display type (button vs avatar).
     */
    function renderYagaActionIcon($action, $class) {
        $content = '';
        $type = 'YagaReactSprite';

        if ($action->Photo) {
            $content = img($action->Photo, ['class' => 'YagaReactionImage']);
            $type = 'YagaReactPhoto';
        } elseif ($action->Emoji) {
            $content = $action->Emoji;
            $type = 'YagaReactEmoji';
        }

        return wrap($content, 'span', ['class' => $type.' '.$class.'-'.$action->ActionID.' '.$action->CssClass]);
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
                'js-modal btn btn-icon',
                ['title' => Gdn::translate('Delete')]
            );
        }

        $options .= '</div>';

        return $options;
    }
}
