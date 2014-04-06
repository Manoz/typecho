<?php
include 'common.php';
include 'header.php';
include 'menu.php';

Typecho_Widget::widget('Widget_Metas_Category_Admin')->to($categories);
?>

<div class="main">
    <div class="body container">
        <?php include 'page-title.php'; ?>
        <div class="row typecho-page-main manage-metas">

                <div class="col-mb-12" role="main">

                    <form method="post" name="manage_categories" class="operate-form">
                    <div class="typecho-list-operate clearfix">
                        <div class="operate">
                            <label><i class="sr-only"><?php _e('Select'); ?></i><input type="checkbox" class="typecho-table-select-all" /></label>
                            <div class="btn-group btn-drop">
                                <button class="btn dropdown-toggle btn-s" type="button"><i class="sr-only"><?php _e('Operate'); ?></i><?php _e('Selected items'); ?> <i class="i-caret-down"></i></button>
                                <ul class="dropdown-menu">
                                    <li><a lang="<?php _e('All contents of this category will be deleted, are you sure you want to delete these categories?'); ?>" href="<?php $security->index('/action/metas-category-edit?do=delete'); ?>"><?php _e('Delete'); ?></a></li>
                                    <li><a lang="<?php _e('Refresh categories may need to wait a long time, are you sure you want to refresh these categories?'); ?>" href="<?php $security->index('/action/metas-category-edit?do=refresh'); ?>"><?php _e('Refresh'); ?></a></li>
                                    <li class="multiline">
                                        <button type="button" class="btn merge btn-s" rel="<?php $security->index('/action/metas-category-edit?do=merge'); ?>"><?php _e('Merge into'); ?></button>
                                        <select name="merge">
                                            <?php $categories->parse('<option value="{mid}">{name}</option>'); ?>
                                        </select>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="search" role="search">
                            <?php $categories->backLink(); ?>
                        </div>
                    </div>

                    <div class="typecho-table-wrap">
                        <table class="typecho-list-table">
                            <colgroup>
                                <col width="20"/>
                                <col width="30%"/>
                                <col width="15%"/>
                                <col width="25%"/>
                                <col width=""/>
                                <col width="10%"/>
                            </colgroup>
                            <thead>
                                <tr class="nodrag">
                                    <th> </th>
                                    <th><?php _e('Name'); ?></th>
                                    <th><?php _e('Subcategory'); ?></th>
                                    <th><?php _e('Abbreviated name'); ?></th>
                                    <th> </th>
                                    <th><?php _e('Posts'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if($categories->have()): ?>
                                <?php while ($categories->next()): ?>
                                <tr id="mid-<?php $categories->theId(); ?>">
                                    <td><input type="checkbox" value="<?php $categories->mid(); ?>" name="mid[]"/></td>
                                    <td><a href="<?php $options->adminUrl('category.php?mid=' . $categories->mid); ?>"><?php $categories->name(); ?></a>
                                    <a href="<?php $categories->permalink(); ?>" title="<?php _e('Browse %s', $categories->name); ?>"><i class="i-exlink"></i></a>
                                    </td>
                                    <td>

                                    <?php if (count($categories->children) > 0): ?>
                                    <a href="<?php $options->adminUrl('manage-categories.php?parent=' . $categories->mid); ?>"><?php echo _n('Categorie', '%d categories', count($categories->children)); ?></a>
                                    <?php else: ?>
                                    <a href="<?php $options->adminUrl('category.php?parent=' . $categories->mid); ?>"><?php echo _e('New'); ?></a>
                                    <?php endif; ?>
                                    </td>
                                    <td><?php $categories->slug(); ?></td>
                                    <td>
                                    <?php if ($options->defaultCategory == $categories->mid): ?>
                                    <?php _e('Default'); ?>
                                    <?php else: ?>
                                    <a class="hidden-by-mouse" href="<?php $security->index('/action/metas-category-edit?do=default&mid=' . $categories->mid); ?>" title="<?php _e('Set as default'); ?>"><?php _e('Default'); ?></a>
                                    <?php endif; ?>
                                    </td>
                                    <td><a class="balloon-button left size-<?php echo Typecho_Common::splitByCount($categories->count, 1, 10, 20, 50, 100); ?>" href="<?php $options->adminUrl('manage-posts.php?category=' . $categories->mid); ?>"><?php $categories->count(); ?></a></td>
                                </tr>
                                <?php endwhile; ?>
                                <?php else: ?>
                                <tr>
                                    <td colspan="6"><h6 class="typecho-list-table-title"><?php _e('There is no categories'); ?></h6></td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    </form>

                </div>
        </div>
    </div>
</div>

<?php
include 'copyright.php';
include 'common-js.php';
?>

<script type="text/javascript">
(function () {
    $(document).ready(function () {
        var table = $('.typecho-list-table').tableDnD({
            onDrop : function () {
                var ids = [];

                $('input[type=checkbox]', table).each(function () {
                    ids.push($(this).val());
                });

                $.post('<?php $security->index('/action/metas-category-edit?do=sort'); ?>',
                    $.param({mid : ids}));

                $('tr', table).each(function (i) {
                    if (i % 2) {
                        $(this).addClass('even');
                    } else {
                        $(this).removeClass('even');
                    }
                });
            }
        });

        table.tableSelectable({
            checkEl     :   'input[type=checkbox]',
            rowEl       :   'tr',
            selectAllEl :   '.typecho-table-select-all',
            actionEl    :   '.dropdown-menu a'
        });

        $('.btn-drop').dropdownMenu({
            btnEl       :   '.dropdown-toggle',
            menuEl      :   '.dropdown-menu'
        });

        $('.dropdown-menu button.merge').click(function () {
            var btn = $(this);
            btn.parents('form').attr('action', btn.attr('rel')).submit();
        });

        <?php if (isset($request->mid)): ?>
        $('.typecho-mini-panel').effect('highlight', '#AACB36');
        <?php endif; ?>
    });
})();
</script>
<?php include 'footer.php'; ?>

