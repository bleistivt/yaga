<?php if (!defined('APPLICATION')) exit();
$controller = Gdn::controller();
$activeFilter = $controller->data('ActiveFilter');

echo wrap($controller->title(), 'h1');
?>
<div class="BoxFilter BoxBestFilter">
    <ul class="FilterMenu">
        <?php
        echo wrap(
            anchor(Gdn::translate('Yaga.BestContent.Recent'), '/best'),
            'li',
            ['class' => $activeFilter == 'Recent' ? 'Recent Active' : 'Recent']
        );
        echo wrap(
            anchor(Gdn::translate('Yaga.BestContent.AllTime'), '/best/alltime'),
            'li',
            ['class' => $activeFilter == 'AllTime' ? 'AllTime Active' : 'AllTime']
        );
        foreach ($this->Data as $reaction) {
            echo wrap(
                anchor($reaction->Name, '/best/action/'.$reaction->ActionID),
                'li',
                ['class' => $activeFilter == $reaction->ActionID ? "Reaction {$reaction->CssClass} Active" : "Reaction {$reaction->CssClass}"]
            );
        }
        ?>
    </ul>
</div>
