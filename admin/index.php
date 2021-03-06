<?php
include 'common.php';
include 'header.php';
include 'menu.php';

$stat = Typecho_Widget::widget('Widget_Stat');
?>
<div class="main">
    <div class="container typecho-dashboard">
        <?php include 'page-title.php'; ?>
        <div class="row typecho-page-main">
            <div class="col-mb-12 welcome-board" role="main">
                <p><?php _e('There are <em>%s</em> articles, <em>%s</em> comments and <em>%s</em> categorie(s).',
                $stat->myPublishedPostsNum, $stat->myPublishedCommentsNum, $stat->categoriesNum); ?>
                <br><?php _e('Click the links below the Quick Start:'); ?></p>

                <ul id="start-link" class="clearfix">
                    <?php if($user->pass('contributor', true)): ?>
                    <li><a href="<?php $options->adminUrl('write-post.php'); ?>"><?php _e('Write a new post'); ?></a></li>
                    <?php if($user->pass('editor', true) && 'on' == $request->get('__typecho_all_comments') && $stat->waitingCommentsNum > 0): ?>
                        <li><a href="<?php $options->adminUrl('manage-comments.php?status=waiting'); ?>"><?php _e('Pending reviews'); ?></a>
                        <span class="balloon"><?php $stat->waitingCommentsNum(); ?></span>
                        </li>
                    <?php elseif($stat->myWaitingCommentsNum > 0): ?>
                        <li><a href="<?php $options->adminUrl('manage-comments.php?status=waiting'); ?>"><?php _e('Pending review'); ?></a>
                        <span class="balloon"><?php $stat->myWaitingCommentsNum(); ?></span>
                        </li>
                    <?php endif; ?>
                    <?php if($user->pass('editor', true) && 'on' == $request->get('__typecho_all_comments') && $stat->spamCommentsNum > 0): ?>
                        <li><a href="<?php $options->adminUrl('manage-comments.php?status=spam'); ?>"><?php _e('Spam'); ?></a>
                        <span class="balloon"><?php $stat->spamCommentsNum(); ?></span>
                        </li>
                    <?php elseif($stat->mySpamCommentsNum > 0): ?>
                        <li><a href="<?php $options->adminUrl('manage-comments.php?status=spam'); ?>"><?php _e('Spam'); ?></a>
                        <span class="balloon"><?php $stat->mySpamCommentsNum(); ?></span>
                        </li>
                    <?php endif; ?>
                    <?php if($user->pass('administrator', true)): ?>
                    <li><a href="<?php $options->adminUrl('themes.php'); ?>"><?php _e('Change appearance'); ?></a></li>
                    <li><a href="<?php $options->adminUrl('plugins.php'); ?>"><?php _e('Plugins manager'); ?></a></li>
                    <li><a href="<?php $options->adminUrl('options-general.php'); ?>"><?php _e('System settings'); ?></a></li>
                    <?php endif; ?>
                    <?php endif; ?>
                    <!--<li><a href="<?php $options->adminUrl('profile.php'); ?>"><?php _e('Update my profile'); ?></a></li>-->
                </ul>
                <?php $version = Typecho_Cookie::get('__typecho_check_version'); ?>
                <?php if ($version && $version['available']): ?>
                <div class="update-check">
                    <p class="message notice">
                        <?php _e('You are currently using version'); ?> <?php echo $version['current']; ?> &rarr;
                        <strong><a href="<?php echo $version['link']; ?>"><?php _e('The latest official version is'); ?> <?php echo $version['latest']; ?></a></strong>
                    </p>
                </div>
                <?php endif; ?>
            </div>

            <div class="col-mb-12 col-tb-4" role="complementary">
                <section class="latest-link">
                    <h3><?php _e('Recently published articles'); ?></h3>
                    <?php Typecho_Widget::widget('Widget_Contents_Post_Recent', 'pageSize=10')->to($posts); ?>
                    <ul>
                    <?php if($posts->have()): ?>
                    <?php while($posts->next()): ?>
                        <li>
                            <span><?php $posts->date('n.j'); ?></span>
                            <a href="<?php $posts->permalink(); ?>" class="title"><?php $posts->title(); ?></a>
                        </li>
                    <?php endwhile; ?>
                    <?php else: ?>
                        <li><em><?php _e('There is no article'); ?></em></li>
                    <?php endif; ?>
                    </ul>
                </section>
            </div>

            <div class="col-mb-12 col-tb-4" role="complementary">
                <section class="latest-link">
                    <h3><?php _e('Recently received a reply'); ?></h3>
                    <ul>
                        <?php Typecho_Widget::widget('Widget_Comments_Recent', 'pageSize=10')->to($comments); ?>
                        <?php if($comments->have()): ?>
                        <?php while($comments->next()): ?>
                        <li>
                            <span><?php $comments->date('n.j'); ?></span>
                            <a href="<?php $comments->permalink(); ?>" class="title"><?php $comments->author(true); ?></a>:
                            <?php $comments->excerpt(35, '...'); ?>
                        </li>
                        <?php endwhile; ?>
                        <?php else: ?>
                        <li><?php _e('No reply'); ?></li>
                        <?php endif; ?>
                    </ul>
                </section>
            </div>

            <div class="col-mb-12 col-tb-4" role="complementary">
                <section class="latest-link">
                    <h3><?php _e('The latest official news'); ?></h3>
                    <div id="typecho-message">
                        <ul>
                            <li><?php _e('Loading...'); ?></li>
                        </ul>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>

<?php
include 'copyright.php';
include 'common-js.php';
?>

<script>
$(document).ready(function () {
    var ul = $('#typecho-message ul'), cache = window.sessionStorage,
        html = cache ? cache.getItem('feed') : '',
        update = cache ? cache.getItem('update') : '';

    if (!!html) {
        ul.html(html);
    } else {
        html = '';
        $.get('<?php $options->index('/action/ajax?do=feed'); ?>', function (o) {
            for (var i = 0; i < o.length; i ++) {
                var item = o[i];
                html += '<li><span>' + item.date + '</span> <a href="' + item.link + '" target="_blank">' + item.title
                    + '</a></li>';
            }

            ul.html(html);
            cache.setItem('feed', html);
        }, 'json');
    }

    function applyUpdate(update) {
        if (update.available) {
            $('<div class="update-check"><p>'
                + '<?php _e('You are currently using version %s'); ?>'.replace('%s', update.current) + '<br />'
                + '<strong><a href="' + update.link + '" target="_blank">'
                + '<?php _e('The latest official version is %s'); ?>'.replace('%s', update.latest) + '</a></strong></p></div>')
            .appendTo('.welcome-board').effect('highlight');
        }
    }

    if (!!update) {
        applyUpdate($.parseJSON(update));
    } else {
        update = '';
        $.get('<?php $options->index('/action/ajax?do=checkVersion'); ?>', function (o, status, resp) {
            applyUpdate(o);
            cache.setItem('update', resp.responseText);
        }, 'json');
    }
});

</script>
<?php include 'footer.php'; ?>
