<?php if (!defined('APPLICATION')) exit();

/**
 * A collection of hooks that are enabled when Yaga is.
 * 
 * @package Yaga
 * @since 1.0
 * @copyright (c) 2013-2014, Zachary Doll
 */

/**
 * Writes a discussion out for use in a module
 * 
 * @param stdClass $discussion
 * @param string $px
 */
function writeModuleDiscussion($discussion, $px = 'Bookmark') {
?>
<li id="<?php echo "{$px}_{$discussion->DiscussionID}"; ?>" class="<?php echo cssClass($discussion); ?>">
     <span class="Options">
        <?php
//            echo optionsList($discussion);
        echo bookmarkButton($discussion);
        ?>
     </span>
     <div class="Title"><?php
        echo anchor(Gdn_Format::text($discussion->Name, false), discussionUrl($discussion).($discussion->CountCommentWatch > 0 ? '#Item_'.$discussion->CountCommentWatch : ''), 'DiscussionLink');
     ?></div>
     <div class="Meta">
            <?php
                $last = new stdClass();
                $last->UserID = $discussion->LastUserID;
                $last->Name = $discussion->LastName;

                echo newComments($discussion);

                echo '<span class="MItem">'.Gdn_Format::date($discussion->LastDate, 'html').UserAnchor($last).'</span>';
            ?>
     </div>
</li>
<?php
}

/**
 * Writes a discussion or comment out for use in a module
 * 
 * @staticvar boolean $userPhotoFirst
 * @param array $content
 * @param mixed $sender calling object.
 */
function writePromotedContent($content, $sender) {
    static $userPhotoFirst = null;
    if ($userPhotoFirst === null) {
        $userPhotoFirst = Gdn::config('Vanilla.Comment.UserPhotoFirst', true);
    }

    $contentType = $content['ItemType'];
    $contentID = $content["{$contentType}ID"];
    $author = $content['Author'] ?? false;

    switch (strtolower($contentType)) {
        case 'comment':
            $contentURL = commentUrl($content);
            break;
        case 'discussion':
            $contentURL = discussionUrl($content);
            break;
    }
     $sender->EventArgs['Content'] = $content;
     $sender->EventArgs['ContentUrl'] = $contentURL;
?>
     <div id="<?php echo "Promoted_{$contentType}_{$contentID}"; ?>" class="<?php echo cssClass($content); ?>">
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
                    $sender->fireEvent('AuthorPhoto');
                    ?>
                </span>
                <span class="AuthorInfo">
                    <?php
                    echo ' '.WrapIf(htmlspecialchars($author['Title'] ?? ''), 'span', ['class' => 'MItem AuthorTitle']);
                    echo ' '.WrapIf(htmlspecialchars($author['Location'] ?? ''), 'span', ['class' => 'MItem AuthorLocation']);
                    $sender->fireEvent('AuthorInfo');
                    ?>
                </span>
            </div>
            <div class="Meta CommentMeta CommentInfo">
                <span class="MItem DateCreated">
                    <?php echo anchor(Gdn_Format::date($content['DateInserted'], 'html'), $contentURL, 'Permalink', ['rel' => 'nofollow']); ?>
                </span>
                <?php
                // Include source if one was set
                $source = $content['Source'] ?? false;
                if ($source) {
                   echo wrap(sprintf(Gdn::translate('via %s'), Gdn::translate($source.' Source', $source)), 'span', ['class' => 'MItem Source']);
                }

                $sender->fireEvent('ContentInfo');
                ?>
            </div>
            <div class="Title"><?php echo anchor(Gdn_Format::text($content['Name'], false), $contentURL, 'DiscussionLink'); ?></div>
            <div class="Body">
            <?php
                echo anchor(strip_tags(Gdn_Format::to($content['Body'], $content['Format'])), $contentURL, 'BodyLink');
                $sender->fireEvent('AfterBody'); // seperate event to account for less space.
            ?>
            </div>
     </div>
<?php
}
