<?php if(!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<li>
<label class="typecho-label" for="dbHost"><?php _e('Database Address'); ?></label>
<input type="text" class="text" name="dbHost" id="dbHost" value="<?php _v('dbHost', 'localhost'); ?>"/>
<p class="description"><?php _e('You may use "%s"', 'localhost'); ?></p>
</li>
<li>
<label class="typecho-label" for="dbPort"><?php _e('Database port'); ?></label>
<input type="text" class="text" name="dbPort" id="dbPort" value="<?php _v('dbPort', '5432'); ?>"/>
<p class="description"><?php _e('If you do not know the meaning of this option, keep the default settings'); ?></p>
</li>
<li>
<label class="typecho-label" for="dbUser"><?php _e('Database username'); ?></label>
<input type="text" class="text" name="dbUser" id="dbUser" value="<?php _v('dbUser', 'postgres'); ?>" />
<p class="description"><?php _e('You may use "%s"', 'postgres'); ?></p>
</li>
<li>
<label class="typecho-label" for="dbPassword"><?php _e('Database Password'); ?></label>
<input type="password" class="text" name="dbPassword" id="dbPassword" value="<?php _v('dbPassword'); ?>" />
</li>
<li>
<label class="typecho-label" for="dbDatabase"><?php _e('Database name'); ?></label>
<input type="text" class="text" name="dbDatabase" id="dbDatabase" value="<?php _v('dbDatabase', 'typecho'); ?>" />
<p class="description"><?php _e('Please specify the database name'); ?></p>
</li>
<input type="hidden" name="dbCharset" value="<?php _e('utf8'); ?>" />
