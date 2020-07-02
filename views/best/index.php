<?php if (!defined('APPLICATION')) exit();

use Vanilla\Formatting\DateTimeFormatter;

/* Copyright 2014 Zachary Doll */

$contents = $this->_content->Content;
$dateFormatter = Gdn::getContainer()->get(DateTimeFormatter::class);
$formatService = Gdn::formatService();

echo '<ul class="DataList Compact BlogList">';
foreach ($contents as $content) {
	static $userPhotoFirst = null;
    if ($userPhotoFirst === null) {
        $userPhotoFirst = Gdn::config('Vanilla.Comment.UserPhotoFirst', true);
    }

    $contentType = $content['ItemType'];
    $contentID = $content['ContentID'];
    $author = $content['Author'] ?? false;

?>
    <li id="<?php echo "{$contentType}_{$contentID}"; ?>" class="Item">
        <h3><?php echo anchor(htmlspecialchars($content['Name']), $content['ContentURL']); ?></h3>
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
                    <?php
                        echo anchor(
                            $dateFormatter->formatDate($content['DateInserted'], true),
                            $content['ContentURL'],
                            'Permalink',
                            ['rel' => 'nofollow']
                        );
                    ?>
                </span>
                <?php
                // Include source if one was set
                $source = $content['Source'] ?? false;
                if ($source) {
                    echo wrap(sprintf(Gdn::translate('via %s'), Gdn::translate($source.' Source', $source)), 'span', ['class' => 'MItem Source']);
                }
                ?>
            </div>
        </div>
        <div class="Item-BodyWrap">
            <div class="Item-Body">
                <div class="Message Expander">
                    <?php echo $formatService->renderHTML($content['Body'], $content['Format']); ?>
                </div>
                <?php
                if (Gdn::config('Yaga.Reactions.Enabled') && Gdn::session()->checkPermission('Yaga.Reactions.View')) {
                    echo renderReactionRecord($contentID, $contentType);
                }
                ?>
            </div>
        </div>
    </li> <?php
}
echo '</ul>';

echo $this->Pager->toString();
