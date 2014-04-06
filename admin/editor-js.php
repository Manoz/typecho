<?php if(!defined('__TYPECHO_ADMIN__')) exit; ?>
<?php $content = !empty($post) ? $post : $page; if ($options->markdown): ?>
<script src="<?php $options->adminUrl('js/pagedown.js?v=' . $suffixVersion); ?>"></script>
<script src="<?php $options->adminUrl('js/pagedown-extra.js?v=' . $suffixVersion); ?>"></script>
<script src="<?php $options->adminUrl('js/diff.js?v=' . $suffixVersion); ?>"></script>
<script>
$(document).ready(function () {
    var textarea = $('#text'),
        toolbar = $('<div class="editor" id="wmd-button-bar" />').insertBefore(textarea.parent())
        preview = $('<div id="wmd-preview" class="wmd-hidetab" />').insertAfter('.editor');

    var options = {}, isMarkdown = <?php echo intval($content->isMarkdown || !$content->have()); ?>;

    options.strings = {
        bold: '<?php _e('Bold'); ?> <strong> Ctrl+B',
        boldexample: '<?php _e('Bold text'); ?>',

        italic: '<?php _e('Italic'); ?> <em> Ctrl+I',
        italicexample: '<?php _e('Italic text'); ?>',

        link: '<?php _e('Link'); ?> <a> Ctrl+L',
        linkdescription: '<?php _e('Enter link description'); ?>',

        quote:  '<?php _e('Quote'); ?> <blockquote> Ctrl+Q',
        quoteexample: '<?php _e('Quoted text'); ?>',

        code: '<?php _e('Code'); ?> <pre><code> Ctrl+K',
        codeexample: '<?php _e('Please enter the code'); ?>',

        image: '<?php _e('Image'); ?> <img> Ctrl+G',
        imagedescription: '<?php _e('Please enter a description'); ?>',

        olist: '<?php _e('Ordered list'); ?> <ol> Ctrl+O',
        ulist: '<?php _e('Unordered list'); ?> <ul> Ctrl+U',
        litem: '<?php _e('List items'); ?>',

        heading: '<?php _e('Title'); ?> <h1>/<h2> Ctrl+H',
        headingexample: '<?php _e('Title text'); ?>',

        hr: '<?php _e('Dividing line'); ?> <hr> Ctrl+R',
        more: '<?php _e('Read more tag'); ?> <!--more--> Ctrl+M',

        undo: '<?php _e('Undo'); ?> - Ctrl+Z',
        redo: '<?php _e('Redo'); ?> - Ctrl+Y',
        redomac: '<?php _e('Redo'); ?> - Ctrl+Shift+Z',

        fullscreen: '<?php _e('Fullscreen'); ?> - Ctrl+J',
        exitFullscreen: '<?php _e('Exit Fullscreen'); ?> - Ctrl+E',
        fullscreenUnsupport: '<?php _e('This browser does not support fullscreen operation.'); ?>',

        imagedialog: '<p><b><?php _e('Insert Picture'); ?></b></p><p><?php _e('Please fill in the input box below to insert the remote image address'); ?></p><p><?php _e('You can also use the attachments feature to insert local upload images'); ?></p>',
        linkdialog: '<p><b><?php _e('Insert Link'); ?></b></p><p><?php _e('Please fill in the input box below to insert the link address'); ?></p>',

        ok: '<?php _e('Send'); ?>',
        cancel: '<?php _e('Cancel'); ?>',

        help: '<?php _e('Markdown syntax help'); ?>'
    };

    var converter = new Markdown.Converter(),
        editor = new Markdown.Editor(converter, '', options),
        diffMatch = new diff_match_patch(), last = '', preview = $('#wmd-preview'),
        mark = '@mark' + Math.ceil(Math.random() * 100000000) + '@',
        span = '<span class="diff" />',
        cache = {};

    // Set markdown
    Markdown.Extra.init(converter, {
        extensions  :   ["tables", "fenced_code_gfm", "def_list", "attr_list", "footnotes"]
    });

    // Automatically follow
    converter.hooks.chain('postConversion', function (html) {
        // clear special html tags
        html = html.replace(/<\/?(\!doctype|html|head|body|link|title|input|select|button|textarea|style|noscript)[^>]*>/ig, function (all) {
            return all.replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/'/g, '&#039;')
                .replace(/"/g, '&quot;');
        });

        // clear hard breaks
        html = html.replace(/\s*((?:<br>\n)+)\s*(<\/?(?:p|div|h[1-6]|blockquote|pre|table|dl|ol|ul|address|form|fieldset|iframe|hr|legend|article|section|nav|aside|hgroup|header|footer|figcaption|li|dd|dt)[^\w])/gm, '$2');

        if (html.indexOf('<!--more-->') > 0) {
            var parts = html.split(/\s*<\!\-\-more\-\->\s*/),
                summary = parts.shift(),
                details = parts.join('');

            html = '<div class="summary">' + summary + '</div>'
                + '<div class="details">' + details + '</div>';
        }


        var diffs = diffMatch.diff_main(last, html);
        last = html;

        if (diffs.length > 0) {
            var stack = [], markStr = mark;

            for (var i = 0; i < diffs.length; i ++) {
                var diff = diffs[i], op = diff[0], str = diff[1]
                    sp = str.lastIndexOf('<'), ep = str.lastIndexOf('>');

                if (op != 0) {
                    if (sp >=0 && sp > ep) {
                        if (op > 0) {
                            stack.push(str.substring(0, sp) + markStr + str.substring(sp));
                        } else {
                            var lastStr = stack[stack.length - 1], lastSp = lastStr.lastIndexOf('<');
                            stack[stack.length - 1] = lastStr.substring(0, lastSp) + markStr + lastStr.substring(lastSp);
                        }
                    } else {
                        if (op > 0) {
                            stack.push(str + markStr);
                        } else {
                            stack.push(markStr);
                        }
                    }

                    markStr = '';
                } else {
                    stack.push(str);
                }
            }

            html = stack.join('');

            if (!markStr) {
                var pos = html.indexOf(mark), prev = html.substring(0, pos),
                    next = html.substr(pos + mark.length),
                    sp = prev.lastIndexOf('<'), ep = prev.lastIndexOf('>');

                if (sp >= 0 && sp > ep) {
                    html = prev.substring(0, sp) + span + prev.substring(sp) + next;
                } else {
                    html = prev + span + next;
                }
            }
        }

        // Replace img
        html = html.replace(/<(img)\s+([^>]*)\s*src="([^"]+)"([^>]*)>/ig, function (all, tag, prefix, src, suffix) {
            if (!cache[src]) {
                cache[src] = false;
            } else {
                return '<span class="cache" data-width="' + cache[src][0] + '" data-height="' + cache[src][1] + '" '
                    + 'style="background:url(' + src + ') no-repeat left top; width:'
                    + cache[src][0] + 'px; height:' + cache[src][1] + 'px; display: inline-block; max-width: 100%;'
                    + '-webkit-background-size: contain;-moz-background-size: contain;-o-background-size: contain;background-size: contain;" />';
            }

            return all;
        });

        // Replace block
        html = html.replace(/<(iframe|embed)\s+([^>]*)>/ig, function (all, tag, src) {
            if (src[src.length - 1] == '/') {
                src = src.substring(0, src.length - 1);
            }

            return '<div style="background: #ddd; height: 40px; overflow: hidden; line-height: 40px; text-align: center; font-size: 12px; color: #777">'
                + tag + ' : ' + $.trim(src) + '</div>';
        });

        return html;
    });

    function cacheResize() {
        var t = $(this), w = parseInt(t.data('width')), h = parseInt(t.data('height')),
            ow = t.width();

        t.height(h * ow / w);
    }

    editor.hooks.chain('onPreviewRefresh', function () {
        var diff = $('.diff', preview), scrolled = false;

        $('img', preview).load(function () {
            var t = $(this), src = t.attr('src');

            if (scrolled) {
                preview.scrollTo(diff, {
                    offset  :   - 50
                });
            }

            if (!!src && !cache[src]) {
                cache[src] = [this.width, this.height];
            }
        });

        $('.cache', preview).resize(cacheResize).each(cacheResize);

        if (diff.length > 0) {
            var p = diff.position(), lh = diff.parent().css('line-height');
            lh = !!lh ? parseInt(lh) : 0;

            if (p.top < 0 || p.top > preview.height() - lh) {
                preview.scrollTo(diff, {
                    offset  :   - 50
                });
                scrolled = true;
            }
        }
    });

    <?php Typecho_Plugin::factory('admin/editor-js.php')->markdownEditor($content); ?>

    var input = $('#text'), th = textarea.height(), ph = preview.height(),
        uploadBtn = $('<button type="button" id="btn-fullscreen-upload" class="btn btn-link">'
            + '<i class="i-upload"><?php _e('Attachment'); ?></i></button>')
            .prependTo('.submit .right')
            .click(function() {
                $('a', $('.typecho-option-tabs li').not('.active')).trigger('click');
                return false;
            });

    $('.typecho-option-tabs li').click(function () {
        uploadBtn.find('i').toggleClass('i-upload-active',
            $('#tab-files-btn', this).length > 0);
    });

    editor.hooks.chain('enterFakeFullScreen', function () {
        th = textarea.height();
        ph = preview.height();
        $(document.body).addClass('fullscreen');
        var h = $(window).height() - toolbar.outerHeight();

        textarea.css('height', h);
        preview.css('height', h);
    });

    editor.hooks.chain('enterFullScreen', function () {
        $(document.body).addClass('fullscreen');

        var h = window.screen.height - toolbar.outerHeight();
        textarea.css('height', h);
        preview.css('height', h);
    });

    editor.hooks.chain('exitFullScreen', function () {
        $(document.body).removeClass('fullscreen');
        textarea.height(th);
        preview.height(ph);
    });

    function initMarkdown() {
        editor.run();

        var imageButton = $('#wmd-image-button'),
            linkButton = $('#wmd-link-button');

        Typecho.insertFileToEditor = function (file, url, isImage) {
            var button = isImage ? imageButton : linkButton;

            options.strings[isImage ? 'imagename' : 'linkname'] = file;
            button.trigger('click');

            var checkDialog = setInterval(function () {
                if ($('.wmd-prompt-dialog').length > 0) {
                    $('.wmd-prompt-dialog input').val(url).select();
                    clearInterval(checkDialog);
                    checkDialog = null;
                }
            }, 10);
        };

        Typecho.uploadComplete = function (file) {
            Typecho.insertFileToEditor(file.title, file.url, file.isImage);
        };

        // Edit preview switch
        var edittab = $('.editor').prepend('<div class="wmd-edittab"><a href="#wmd-editarea" class="active"><?php _e('Write'); ?></a><a href="#wmd-preview"><?php _e('Preview'); ?></a></div>'),
            editarea = $(textarea.parent()).attr("id", "wmd-editarea");

        $(".wmd-edittab a").click(function() {
            $(".wmd-edittab a").removeClass('active');
            $(this).addClass("active");
            $("#wmd-editarea, #wmd-preview").addClass("wmd-hidetab");

            var selected_tab = $(this).attr("href"),
                selected_el = $(selected_tab).removeClass("wmd-hidetab");

            // Editor button to hide the preview
            if (selected_tab == "#wmd-preview") {
                $("#wmd-button-row").addClass("wmd-visualhide");
            } else {
                $("#wmd-button-row").removeClass("wmd-visualhide");
            }

            // Highly consistent preview and edit window
            $("#wmd-preview").outerHeight($("#wmd-editarea").innerHeight());

            return false;
        });
    }

    if (isMarkdown) {
        initMarkdown();
    } else {
        var notice = $('<div class="message notice"><?php _e('This article is not created with the Markdown syntax, Edit it to continue to use Markdown?'); ?> '
            + '<button class="btn btn-xs primary yes"><?php _e('是'); ?></button> '
            + '<button class="btn btn-xs no"><?php _e('否'); ?></button></div>')
            .hide().insertBefore(textarea).slideDown();

        $('.yes', notice).click(function () {
            notice.remove();
            $('<input type="hidden" name="markdown" value="1" />').appendTo('.submit');
            initMarkdown();
        });

        $('.no', notice).click(function () {
            notice.remove();
        });
    }
});
</script>
<?php endif; ?>

