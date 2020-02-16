<?php if (!defined('APPLICATION')) exit();

/* Copyright 2014 Zachary Doll */

$contents = $this->_Content->Content;

echo '<ul class="DataList Compact BlogList">';
foreach ($contents as $content) {
	static $userPhotoFirst = null;
    if ($userPhotoFirst === null) {
        $userPhotoFirst = Gdn::config('Vanilla.Comment.UserPhotoFirst', true);
    }

    $contentType = val('ItemType', $content);
    $contentID = val("{$contentType}ID", $content);
    $author = val('Author', $content);

?>
    <li id="<?php echo "{$contentType}_{$contentID}"; ?>" class="Item">
        <h3><?php echo anchor(Gdn_Format::text($content['Name'], false), $content['ContentURL']); ?></h3>
        <div class="Item-Header">
            <div class="AuthorWrap">
                <span class="Author">
                    <?php
                    if ($userPhotoFirst) {
                        echo userPhoto($author);
                        echo userAnchor($author, 'Username');
                    } else {
                        echo userAnchor($author, 'Username');
                        echo userPhoto($author);
                    }
                    ?>
                </span>
            </div>
            <div class="Meta">
                <span class="MItem DateCreated">
                    <?php echo anchor(Gdn_Format::date($content['DateInserted'], 'html'), $content['ContentURL'], 'Permalink', ['rel' => 'nofollow']); ?>
                </span>
                <?php
                // Include source if one was set
                if ($source = val('Source', $content)) {
                    echo wrap(sprintf(Gdn::translate('via %s'), Gdn::translate($source.' Source', $source)), 'span', ['class' => 'MItem Source']);
                }
                ?>
            </div>
        </div>
        <div class="Item-BodyWrap">
            <div class="Item-Body">
                <div class="Message Expander">
                    <?php echo Gdn_Format::to($content['Body'], $content['Format']); ?>
                </div>
                <?php
                if (Gdn::config('Yaga.Reactions.Enabled') && Gdn::session()->checkPermission('Yaga.Reactions.View')) {
                    echo renderReactionRecord($contentID, strtolower($contentType));
                }
                ?>
            </div>
        </div>
    </li> <?php
}
echo '</ul>';

echo $this->Pager->toString();
