<?php if(!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php $defaultDir = dirname($_SERVER['SCRIPT_FILENAME']) . '/usr/' . uniqid() . '.db'; ?>
<li>
<label class="typecho-label" for="dbFile"><?php _e('Database file path'); ?></label>
<input type="text" class="text" name="dbFile" id="dbFile" value="<?php _v('dbFile', $defaultDir); ?>"/>
<p class="description"><?php _e('"%s" The address is automatically generated for you.', $defaultDir); ?></p>
</li>
