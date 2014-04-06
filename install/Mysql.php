<?php if(!defined('__TYPECHO_ROOT_DIR__')) exit; ?>

<?php
$engine = '';

if (defined('SAE_MYSQL_DB')) {
    $engine = 'SAE';
} else if (!!getenv('HTTP_BAE_ENV_ADDR_SQL_IP')) {
    $engine = 'BAE';
} else if (ini_get('acl.app_id') && class_exists('Alibaba')) {
    $engine = 'ACE';
} else if (isset($_SERVER['SERVER_SOFTWARE']) && strpos($_SERVER['SERVER_SOFTWARE'],'Google App Engine') !== false) {
    $engine = 'GAE';
}
?>

<?php if (!empty($engine)): ?>
<h3 class="warning"><?php _e('The system will automatically match your installation %s environment', $engine); ?></h3>
<?php endif; ?>

<?php if ('SAE' == $engine): ?>
<!-- SAE -->
    <input type="hidden" name="config" value="array (
    'host'      =>  SAE_MYSQL_HOST_M,
    'user'      =>  SAE_MYSQL_USER,
    'password'  =>  SAE_MYSQL_PASS,
    'charset'   =>  '<?php _e('utf8'); ?>',
    'port'      =>  SAE_MYSQL_PORT,
    'database'  =>  SAE_MYSQL_DB
)" />
    <input type="hidden" name="dbHost" value="<?php echo SAE_MYSQL_HOST_M; ?>" />
    <input type="hidden" name="dbPort" value="<?php echo SAE_MYSQL_PORT; ?>" />
    <input type="hidden" name="dbUser" value="<?php echo SAE_MYSQL_USER; ?>" />
    <input type="hidden" name="dbPassword" value="<?php echo SAE_MYSQL_PASS; ?>" />
    <input type="hidden" name="dbDatabase" value="<?php echo SAE_MYSQL_DB; ?>" />
<?php elseif ('BAE' == $engine):
$baeDbUser = "getenv('HTTP_BAE_ENV_AK')";
$baeDbPassword = "getenv('HTTP_BAE_ENV_SK')";
?>
<!-- BAE -->
    <?php if (!getenv('HTTP_BAE_ENV_AK')): $baeDbUser = "'{user}'"; ?>
    <li>
        <label class="typecho-label" for="dbUser"><?php _e('Application API Key'); ?></label>
        <input type="text" class="text" name="dbUser" id="dbUser" value="<?php _v('dbUser'); ?>" />
    </li>
    <?php else: ?>
    <input type="hidden" name="dbUser" value="<?php echo getenv('HTTP_BAE_ENV_AK'); ?>" />
    <?php endif; ?>

    <?php if (!getenv('HTTP_BAE_ENV_SK')): $baeDbPassword = "'{password}'"; ?>
    <li>
        <label class="typecho-label" for="dbPassword"><?php _e('Application Secret Key'); ?></label>
        <input type="text" class="text" name="dbPassword" id="dbPassword" value="<?php _v('dbPassword'); ?>" />
    </li>
    <?php else: ?>
    <input type="hidden" name="dbPassword" value="<?php echo getenv('HTTP_BAE_ENV_SK'); ?>" />
    <?php endif; ?>

    <li>
        <label class="typecho-label" for="dbDatabase"><?php _e('Database name'); ?></label>
        <input type="text" class="text" id="dbDatabase" name="dbDatabase" value="<?php _v('dbDatabase'); ?>" />
        <p class="description"><?php _e('You can see the name of the database you created in MySQL service management page'); ?></p>
    </li>
    <input type="hidden" name="config" value="array (
    'host'      =>  getenv('HTTP_BAE_ENV_ADDR_SQL_IP'),
    'user'      =>  <?php echo $baeDbUser; ?>,
    'password'  =>  <?php echo $baeDbPassword; ?>,
    'charset'   =>  '<?php _e('utf8'); ?>',
    'port'      =>  getenv('HTTP_BAE_ENV_ADDR_SQL_PORT'),
    'database'  =>  '{database}'
)" />
    <input type="hidden" name="dbHost" value="<?php echo getenv('HTTP_BAE_ENV_ADDR_SQL_IP'); ?>" />
    <input type="hidden" name="dbPort" value="<?php echo getenv('HTTP_BAE_ENV_ADDR_SQL_PORT'); ?>" />
<?php elseif ('ACE' == $engine): ?>
<!-- ACE -->

    <li>
        <label class="typecho-label" for="dbHost"><?php _e('Database Address'); ?></label>
        <input type="text" class="text" name="dbHost" id="dbHost" value="<?php _v('dbHost', 'localhost'); ?>"/>
        <p class="description"><?php _e('You can access the RDS console for details'); ?></p>
    </li>
    <li>
        <label class="typecho-label" for="dbPort"><?php _e('Database port'); ?></label>
        <input type="text" class="text" name="dbPort" id="dbPort" value="<?php _v('dbPort', 3306); ?>"/>
    </li>
    <li>
        <label class="typecho-label" for="dbUser"><?php _e('Database username'); ?></label>
        <input type="text" class="text" name="dbUser" id="dbUser" value="<?php _v('dbUser'); ?>" />
    </li>
    <li>
        <label class="typecho-label" for="dbPassword"><?php _e('Database password'); ?></label>
        <input type="password" class="text" name="dbPassword" id="dbPassword" value="<?php _v('dbPassword'); ?>" />
    </li>
    <li>
        <label class="typecho-label" for="dbDatabase"><?php _e('Database name'); ?></label>
        <input type="text" class="text" name="dbDatabase" id="dbDatabase" value="<?php _v('dbDatabase', 'typecho'); ?>" />
    </li>

<?php elseif ('GAE' == $engine): ?>
<!-- GAE -->
    <h3 class="warning"><?php _e('he system will automatically match your installation option %s environment', 'GAE'); ?></h3>
    <li>
        <label class="typecho-label" for="dbPort"><?php _e('Database instance name'); ?></label>
        <input type="text" class="text" name="dbPort" id="dbPort" value="<?php _v('dbPort'); ?>"/>
        <p class="description"><?php _e('lease fill in the name of the database instance that you created in Cloud SQL panel'); ?></p>
    </li>
    <li>
        <label class="typecho-label" for="dbUser"><?php _e('Database username'); ?></label>
        <input type="text" class="text" name="dbUser" id="dbUser" value="<?php _v('dbUser'); ?>" />
    </li>
    <li>
        <label class="typecho-label" for="dbPassword"><?php _e('Database Password'); ?></label>
        <input type="password" class="text" name="dbPassword" id="dbPassword" value="<?php _v('dbPassword'); ?>" />
    </li>
    <li>
        <label class="typecho-label" for="dbDatabase"><?php _e('Database name'); ?></label>
        <input type="text" class="text" name="dbDatabase" id="dbDatabase" value="<?php _v('dbDatabase', 'typecho'); ?>" />
        <p class="description"><?php _e('Please fill in the name of the database you created in the instance Cloud SQL'); ?></p>
    </li>

<?php if (0 === strpos($adapter, 'Pdo_')): ?>
    <input type="hidden" name="config" value="array (
    'dsn'       =>  'mysql:dbname={database};unix_socket=/cloudsql/{host}:{port};charset=<?php _e('utf8'); ?>',
    'user'      =>  '{user}',
    'password'  =>  '{password}'
)" />
<?php else: ?>
    <input type="hidden" name="config" value="array (
    'host'      =>  ':/cloudsql/{host}:{port}',
    'database'  =>  '{database}',
    'user'      =>  '{user}',
    'password'  =>  '{password}'
)" />
<?php endif; ?>
    <input type="hidden" name="dbHost" value="<?php echo $_SERVER['APPLICATION_ID'] ?>" />
<?php  else: ?>
    <li>
        <label class="typecho-label" for="dbHost"><?php _e('Database Address'); ?></label>
        <input type="text" class="text" name="dbHost" id="dbHost" value="<?php _v('dbHost', 'localhost'); ?>"/>
        <p class="description"><?php _e('You may use "%s"', 'localhost'); ?></p>
    </li>
    <li>
        <label class="typecho-label" for="dbPort"><?php _e('Database port'); ?></label>
        <input type="text" class="text" name="dbPort" id="dbPort" value="<?php _v('dbPort', '3306'); ?>"/>
        <p class="description"><?php _e('If you do not know the meaning of this option, keep the default settings'); ?></p>
    </li>
    <li>
        <label class="typecho-label" for="dbUser"><?php _e('Database username'); ?></label>
        <input type="text" class="text" name="dbUser" id="dbUser" value="<?php _v('dbUser', 'root'); ?>" />
        <p class="description"><?php _e('You may use "%s"', 'root'); ?></p>
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

<?php  endif; ?>
<input type="hidden" name="dbCharset" value="<?php _e('utf8'); ?>" />

