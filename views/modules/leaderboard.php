<?php if (!defined('APPLICATION')) exit();

use Yaga;

/* Copyright 2013 Zachary Doll */

echo '<div class="Box Leaderboard">';
echo '<h4>'.$this->Title.'</h4>';
echo '<ul class="PanelInfo">';
foreach($this->Data as $leader) {

    // Don't show users that have 0 or negative points
    if ($leader->Points <= 0) {
        break;
    }
    echo wrap(
        userPhoto($leader).' '.
        userAnchor($leader).' '.
        wrap(
            wrap(plural($leader->YagaPoints, '%s Point', '%s Points'), 'span', ['class' => 'Count']),
            'span',
            ['class' => 'Aside']
        ),
        'li'
    );
}
echo '</ul>';
echo '</div>';
