<?php if(!defined('__TYPECHO_ADMIN__')) exit; ?>
<div class="typecho-foot" role="contentinfo">
    <div class="copyright">
        <a href="http://typecho.org" class="i-logo-s">Typecho</a>
        <p><?php _e('Powered by the <a href="http://typecho.org">%s</a> team. Version %s (%s)', $options->software, $prefixVersion, $suffixVersion); ?></p>
    </div>
    <nav class="resource">
        <a href="http://docs.typecho.org"><?php _e('Documentation'); ?></a> &bull;
        <a href="http://forum.typecho.org"><?php _e('Support Forum'); ?></a> &bull;
        <a href="https://github.com/typecho/typecho/issues"><?php _e('Report an issue'); ?></a> &bull;
        <a href="http://extends.typecho.org"><?php _e('Resources'); ?></a>
    </nav>
</div>
