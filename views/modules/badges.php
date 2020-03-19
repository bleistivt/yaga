<?php if (!defined('APPLICATION')) exit();

/* Copyright 2013 Zachary Doll */

echo '<div id="Badges" class="Box Badges">';
echo '<h4>'.$this->Title.'</h4>';
echo '<div class="PhotoGrid">';

foreach ($this->Data as $badge) {
    echo anchor(
        img(
            $badge['Photo'],
            ['class' => 'ProfilePhoto ProfilePhotoSmall']
        ),
        'yaga/badges/'.$badge['BadgeID'].'/'.rawurlencode($badge['Name']),
        ['title' => $badge['Name']]
    );
}
echo '</div>';
echo '</div>';
