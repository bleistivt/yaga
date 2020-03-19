<?php if (!defined('APPLICATION')) exit();

/* Copyright 2015 Zachary Doll */

use \Vanilla\Formatting\DateTimeFormatter;

$user = (Gdn::session()->User) ?: (object)['RankID' => 0];
// $dateFormatter = Gdn::getContainer()->get(DateTimeFormatter::class);

echo heading($this->title());

echo '<ul class="DataList Ranks">';

foreach ($this->data('Ranks') as $rank) {
    $row = '';

    // Construct the description of requirements only if it has auto enabled
    // TODO: Move this to a helper_functions file
    $metaString = Gdn::translate('Yaga.Ranks.Story.Manual');
    if ($rank->Enabled) {
        $reqs = [];
        $posts = false;
        if ($rank->PostReq > 0) {
            $reqs[] = sprintf(Gdn::translate('Yaga.Ranks.Story.PostReq'), $rank->PostReq);
            $posts = true;
        }
        if ($rank->PointReq > 0) {
            if ($posts) {
                $reqs[] = sprintf(Gdn::translate('Yaga.Ranks.Story.PostAndPointReq'), $rank->PointReq);
            } else {
                $reqs[] = sprintf(Gdn::translate('Yaga.Ranks.Story.PointReq'), $rank->PointReq);
            }
        }
        if ($rank->AgeReq > 0) {
            $reqs[] = sprintf(
                Gdn::translate('Yaga.Ranks.Story.AgeReq'),
                //$dateFormatter->formatSeconds($rank->AgeReq)
                Gdn_Format::seconds($rank->AgeReq)
            );
        }

        switch(count($reqs)) {
            case 3:
                $metaString = sprintf(Gdn::translate('Yaga.Ranks.Story.3Reqs'), $reqs[0], $reqs[1], $reqs[2]);
                break;
            case 2:
                $metaString = sprintf(Gdn::translate('Yaga.Ranks.Story.2Reqs'), $reqs[0], $reqs[1]);
                break;
            case 1:
                $metaString = sprintf(Gdn::translate('Yaga.Ranks.Story.1Reqs'), $reqs[0]);
                break;
            default:
            case 0:
                $metaString = Gdn::translate('Yaga.Ranks.Story.Auto');
                break;
        }
    }

    $readClass = ($user->RankID == $rank->RankID) ? ' ' : ' Read';

    // TODO: Add rank photos
    //if ($rank->Photo) {
    //    $row .= img($rank->Photo, ['class' => 'RankPhoto']);
    //} else {
        $row .= img('plugins/yaga/design/images/default_promotion.png', ['class' => 'RankPhoto']);
    //}

    $row .= wrap(
        wrap(
            $rank->Name, 'div', ['class' => 'Title']
        ).
        wrap($rank->Description, 'div', ['class' => 'Description']).
        wrap(
            wrapIf($metaString, 'span', ['class' => 'MItem RankRequirements']),
            'div',
            ['class' => 'Meta']
        ),
        'div',
        ['class' => 'ItemContent Rank']
    );
    echo wrap($row, 'li', ['class' => 'Item ItemRank'.$readClass]);
}

echo '</ul>';