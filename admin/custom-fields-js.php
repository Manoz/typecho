<?php if(!defined('__TYPECHO_ADMIN__')) exit; ?>
<script>
$(document).ready(function () {
    // Custom Fields
    $('#custom-field-expand').click(function() {
        var btn = $('i', this);
        if (btn.hasClass('i-caret-right')) {
            btn.removeClass('i-caret-right').addClass('i-caret-down');
        } else {
            btn.removeClass('i-caret-down').addClass('i-caret-right');
        }
        $(this).parent().toggleClass('fold');
        return false;
    });

    function attachDeleteEvent (el) {
        $('button.btn-xs', el).click(function () {
            if (confirm('<?php _e('You sure you want to delete this field?'); ?>')) {
                $(this).parents('tr').fadeOut(function () {
                    $(this).remove();
                });
            }
        });
    }

    $('#custom-field table tbody tr').each(function () {
        attachDeleteEvent(this);
    });

    $('#custom-field button.operate-add').click(function () {
        var html = '<tr><td><input type="text" name="fieldNames[]" placeholder="<?php _e('Field Name'); ?>" class="text-s w-100"></td>'
                + '<td><select name="fieldTypes[]" id="">'
                + '<option value="str"><?php _e('Character'); ?></option>'
                + '<option value="int"><?php _e('Integer'); ?></option>'
                + '<option value="float"><?php _e('Decimal'); ?></option>'
                + '</select></td>'
                + '<td><textarea name="fieldValues[]" placeholder="<?php _e('Field Value'); ?>" class="text-s w-100" rows="2"></textarea></td>'
                + '<td><button type="button" class="btn btn-xs"><?php _e('Delete'); ?></button></td></tr>',
            el = $(html).hide().appendTo('#custom-field table tbody').fadeIn();

        attachDeleteEvent(el);
    });
});
</script>
