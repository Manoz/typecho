<?php
/**
 * Typecho Blog Platform
 *
 * @copyright  Copyright (c) 2008 Typecho team (http://www.typecho.org)
 * @license    GNU General Public License 2.0
 * @version    $Id$
 */

/** Define the root directory */
define('__TYPECHO_ROOT_DIR__', dirname(__FILE__));

/** Defined plugin directory (relative path) */
define('__TYPECHO_PLUGIN_DIR__', '/usr/plugins');

/** Custom themes directory (relative path) */
define('__TYPECHO_THEME_DIR__', '/usr/themes');

/** Admin path (relative path) */
define('__TYPECHO_ADMIN_DIR__', '/admin/');

/** Settings include path */
@set_include_path(get_include_path() . PATH_SEPARATOR .
__TYPECHO_ROOT_DIR__ . '/var' . PATH_SEPARATOR .
__TYPECHO_ROOT_DIR__ . __TYPECHO_PLUGIN_DIR__);

/** Load API */
require_once 'Typecho/Common.php';

/** Load Response */
require_once 'Typecho/Response.php';

/** Load configuration */
require_once 'Typecho/Config.php';

/** Load Exceptions */
require_once 'Typecho/Exception.php';

/** Load plugin */
require_once 'Typecho/Plugin.php';

/** Load Lang */
require_once 'Typecho/I18n.php';

/** Load database */
require_once 'Typecho/Db.php';

/** Load routers */
require_once 'Typecho/Router.php';

/** Program init */
Typecho_Common::init();

ob_start();

session_start();

// Determine if it has been installed
if (!isset($_GET['finish']) && file_exists(__TYPECHO_ROOT_DIR__ . '/config.inc.php') && empty($_SESSION['typecho'])) {
    exit;
}

// Block off a possible cross-site request
if (!empty($_GET) || !empty($_POST)) {
    if (empty($_SERVER['HTTP_REFERER'])) {
        exit;
    }

    $parts = parse_url($_SERVER['HTTP_REFERER']);
    if (empty($parts['host']) || $_SERVER['HTTP_HOST'] != $parts['host']) {
        exit;
    }
}

/**
 * Get pass parameters
 *
 * @param string $name Parameter name
 * @param string $default The default value
 * @return string
 */
function _r($name, $default = NULL) {
    return isset($_REQUEST[$name]) ?
        (is_array($_REQUEST[$name]) ? $default : $_REQUEST[$name]) : $default;
}

/**
 * Get multiple pass parameters
 *
 * @return array
 */
function _rFrom() {
    $result = array();
    $params = func_get_args();

    foreach ($params as $param) {
        $result[$param] = isset($_REQUEST[$param]) ?
            (is_array($_REQUEST[$param]) ? NULL : $_REQUEST[$param]) : NULL;
    }

    return $result;
}

/**
 * Output pass parameters
 *
 * @param string $name Parameter name
 * @param string $default The default value
 * @return string
 */
function _v($name, $default = '') {
    echo _r($name, $default);
}

/**
 * Determine whether an environment compatible
 *
 * @param string $adapter Adapter
 * @return boolean
 */
function _p($adapter) {
    switch ($adapter) {
        case 'Mysql':
            return Typecho_Db_Adapter_Mysql::isAvailable();
        case 'Pdo_Mysql':
            return Typecho_Db_Adapter_Pdo_Mysql::isAvailable();
        case 'SQLite':
            return Typecho_Db_Adapter_SQLite::isAvailable();
        case 'Pdo_SQLite':
            return Typecho_Db_Adapter_Pdo_SQLite::isAvailable();
        case 'Pgsql':
            return Typecho_Db_Adapter_Pgsql::isAvailable();
        case 'Pdo_Pgsql':
            return Typecho_Db_Adapter_Pdo_Pgsql::isAvailable();
        default:
            return false;
    }
}

/**
 * Get url address
 *
 * @return string
 */
function _u() {
    $url = "http://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
    if (isset($_SERVER["QUERY_STRING"])) {
        $url = str_replace("?" . $_SERVER["QUERY_STRING"], "", $url);
    }

    return dirname($url);
}


$options = new stdClass();
$options->generator = 'Typecho ' . Typecho_Common::VERSION;
list($soft, $currentVersion) = explode(' ', $options->generator);

$options->software = $soft;
$options->version = $currentVersion;

list($prefixVersion, $suffixVersion) = explode('/', $currentVersion);

?><!DOCTYPE HTML>
<html xmlns="http://www.w3.org/1999/xhtml">
<head lang="zh-CN">
    <meta charset="<?php _e('UTF-8'); ?>" />
	<title><?php _e('Typecho Setup'); ?></title>
    <link rel="stylesheet" type="text/css" href="admin/css/normalize.css" />
    <link rel="stylesheet" type="text/css" href="admin/css/grid.css" />
    <link rel="stylesheet" type="text/css" href="admin/css/style.css" />
</head>
<body>
<div class="typecho-install-patch">
    <h1>Typecho</h1>
    <ol class="path">
        <li<?php if (!isset($_GET['finish']) && !isset($_GET['config'])) : ?> class="current"<?php endif; ?>><span>1</span><?php _e('Welcome'); ?></li>
        <li<?php if (isset($_GET['config'])) : ?> class="current"<?php endif; ?>><span>2</span><?php _e('Initial Configuration'); ?></li>
        <li<?php if (isset($_GET['start'])) : ?> class="current"<?php endif; ?>><span>3</span><?php _e('To start the installation'); ?></li>
        <li<?php if (isset($_GET['finish'])) : ?> class="current"<?php endif; ?>><span>4</span><?php _e('Successful installation'); ?></li>
    </ol>
</div>
<div class="container">
    <div class="row">
        <div class="col-mb-12 col-tb-8 col-tb-offset-2">
            <div class="column-14 start-06 typecho-install">
            <?php if (isset($_GET['finish'])) : ?>
                <?php if (!@file_exists(__TYPECHO_ROOT_DIR__ . '/config.inc.php')) : ?>
                <h1 class="typecho-install-title"><?php _e('Installation failed!'); ?></h1>
                <div class="typecho-install-body">
                    <form method="post" action="?config" name="config">
                    <p class="message error"><?php _e('You do not have uploaded config.inc.php file. Please re-install!'); ?> <button class="btn primary" type="submit"><?php _e('Reinstall &raquo;'); ?></button></p>
                    </form>
                </div>
                <?php elseif (!Typecho_Cookie::get('__typecho_config')): ?>
                <h1 class="typecho-install-title"><?php _e('Not installed!'); ?></h1>
                <div class="typecho-install-body">
                    <form method="post" action="?config" name="config">
                    <p class="message error"><?php _e('You do not perform the installation steps, you need to reinstall!'); ?> <button class="btn primary" type="submit"><?php _e('Reinstall &raquo;'); ?></button></p>
                    </form>
                </div>
                <?php else : ?>
                <?php
                    $config = unserialize(base64_decode(Typecho_Cookie::get('__typecho_config')));
                    Typecho_Cookie::delete('__typecho_config');
                    $db = new Typecho_Db($config['adapter'], $config['prefix']);
                    $db->addServer($config, Typecho_Db::READ | Typecho_Db::WRITE);
                    Typecho_Db::set($db);
                ?>
                <h1 class="typecho-install-title"><?php _e('Successful installation!'); ?></h1>
                <div class="typecho-install-body">
                    <div class="message success">
                    <?php if(isset($_GET['use_old']) ) : ?>
                    <?php _e('You chose to use the original data, your username and password and the original agreement'); ?>
                    <?php else : ?>
                        <?php if (isset($_REQUEST['user']) && isset($_REQUEST['password'])): ?>
                            <?php _e('Your username is'); ?>: <strong class="mono"><?php echo htmlspecialchars(_r('user')); ?></strong><br>
                            <?php _e('Your password is'); ?>: <strong class="mono"><?php echo htmlspecialchars(_r('password')); ?></strong>
                        <?php endif;?>
                    <?php endif;?>
                    </div>

                    <div class="p message notice">
                    <a target="_blank" href="http://spreadsheets.google.com/viewform?key=pd1Gl4Ur_pbniqgebs5JRIg&amp;hl=en">Users participating in the survey to help us improve the product</a>
                    </div>

                    <div class="session">
                    <p><?php _e('You can save the following two links to your favorites'); ?>:</p>
                    <ul>
                    <?php
                        if (isset($_REQUEST['user']) && isset($_REQUEST['password'])) {
                            $loginUrl = _u() . '/index.php/action/login?name=' . urlencode(_r('user')) . '&password='
                            . urlencode(_r('password')) . '&referer=' . _u() . '/admin/index.php';
                            $loginUrl = Typecho_Widget::widget('Widget_Security')->getTokenUrl($loginUrl);
                        } else {
                            $loginUrl = _u() . '/admin/index.php';
                        }
                    ?>
                        <li><a href="<?php echo $loginUrl; ?>"><?php _e('Click here to access your control panel'); ?></a></li>
                        <li><a href="<?php echo _u(); ?>/index.php"><?php _e('Click here to view your Blog'); ?></a></li>
                    </ul>
                    </div>

                    <p><?php _e('We hope you can enjoy the fun of Typecho!'); ?></p>
                </div>
                <?php endif;?>
            <?php elseif (isset($_GET['start'])): ?>
                <?php if (!@file_exists(__TYPECHO_ROOT_DIR__ . '/config.inc.php')) : ?>
                <h1 class="typecho-install-title"><?php _e('Installation failed!'); ?></h1>
                <div class="typecho-install-body">
                    <form method="post" action="?config" name="config">
                    <p class="message error"><?php _e('You do not have uploaded config.inc.php file. Please re-install!'); ?> <button class="btn primary" type="submit"><?php _e('Reinstall &raquo;'); ?></button></p>
                    </form>
                </div>
                <?php else : ?>
            <?php
                                    $config = unserialize(base64_decode(Typecho_Cookie::get('__typecho_config')));
                                    $type = explode('_', $config['adapter']);
                                    $type = array_pop($type);

                                    try {
                                        $installDb = new Typecho_Db($config['adapter'], $config['prefix']);
                                        $installDb->addServer($config, Typecho_Db::READ | Typecho_Db::WRITE);

                                        /** Initialize the database structure */
                                        $scripts = file_get_contents ('./install/' . $type . '.sql');
                                        $scripts = str_replace('typecho_', $config['prefix'], $scripts);

                                        if (isset($config['charset'])) {
                                            $scripts = str_replace('%charset%', $config['charset'], $scripts);
                                        }

                                        $scripts = explode(';', $scripts);
                                        foreach ($scripts as $script) {
                                            $script = trim($script);
                                            if ($script) {
                                                $installDb->query($script, Typecho_Db::WRITE);
                                            }
                                        }

                                        /** Global Variables */
                                        $installDb->query($installDb->insert('table.options')->rows(array('name' => 'theme', 'user' => 0, 'value' => 'default')));
                                        $installDb->query($installDb->insert('table.options')->rows(array('name' => 'theme:default', 'user' => 0, 'value' => 'a:2:{s:7:"logoUrl";N;s:12:"sidebarBlock";a:5:{i:0;s:15:"ShowRecentPosts";i:1;s:18:"ShowRecentComments";i:2;s:12:"ShowCategory";i:3;s:11:"ShowArchive";i:4;s:9:"ShowOther";}}')));
                                        $installDb->query($installDb->insert('table.options')->rows(array('name' => 'timezone', 'user' => 0, 'value' => _t('28800'))));
                                        $installDb->query($installDb->insert('table.options')->rows(array('name' => 'charset', 'user' => 0, 'value' => 'UTF-8')));
                                        $installDb->query($installDb->insert('table.options')->rows(array('name' => 'contentType', 'user' => 0, 'value' => 'text/html')));
                                        $installDb->query($installDb->insert('table.options')->rows(array('name' => 'gzip', 'user' => 0, 'value' => 0)));
                                        $installDb->query($installDb->insert('table.options')->rows(array('name' => 'generator', 'user' => 0, 'value' => $options->generator)));
                                        $installDb->query($installDb->insert('table.options')->rows(array('name' => 'title', 'user' => 0, 'value' => 'Hello World')));
                                        $installDb->query($installDb->insert('table.options')->rows(array('name' => 'description', 'user' => 0, 'value' => 'Just So So ...')));
                                        $installDb->query($installDb->insert('table.options')->rows(array('name' => 'keywords', 'user' => 0, 'value' => 'typecho,php,blog')));
                                        $installDb->query($installDb->insert('table.options')->rows(array('name' => 'rewrite', 'user' => 0, 'value' => 0)));
                                        $installDb->query($installDb->insert('table.options')->rows(array('name' => 'frontPage', 'user' => 0, 'value' => 'recent')));
                                        $installDb->query($installDb->insert('table.options')->rows(array('name' => 'frontArchive', 'user' => 0, 'value' => 0)));
                                        $installDb->query($installDb->insert('table.options')->rows(array('name' => 'commentsRequireMail', 'user' => 0, 'value' => 1)));
                                        $installDb->query($installDb->insert('table.options')->rows(array('name' => 'commentsWhitelist', 'user' => 0, 'value' => 0)));
                                        $installDb->query($installDb->insert('table.options')->rows(array('name' => 'commentsRequireURL', 'user' => 0, 'value' => 0)));
                                        $installDb->query($installDb->insert('table.options')->rows(array('name' => 'commentsRequireModeration', 'user' => 0, 'value' => 0)));
                                        $installDb->query($installDb->insert('table.options')->rows(array('name' => 'plugins', 'user' => 0, 'value' => 'a:0:{}')));
                                        $installDb->query($installDb->insert('table.options')->rows(array('name' => 'commentDateFormat', 'user' => 0, 'value' => 'F jS, Y \a\t h:i a')));
                                        $installDb->query($installDb->insert('table.options')->rows(array('name' => 'siteUrl', 'user' => 0, 'value' => $config['siteUrl'])));
                                        $installDb->query($installDb->insert('table.options')->rows(array('name' => 'defaultCategory', 'user' => 0, 'value' => 1)));
                                        $installDb->query($installDb->insert('table.options')->rows(array('name' => 'allowRegister', 'user' => 0, 'value' => 0)));
                                        $installDb->query($installDb->insert('table.options')->rows(array('name' => 'defaultAllowComment', 'user' => 0, 'value' => 1)));
                                        $installDb->query($installDb->insert('table.options')->rows(array('name' => 'defaultAllowPing', 'user' => 0, 'value' => 1)));
                                        $installDb->query($installDb->insert('table.options')->rows(array('name' => 'defaultAllowFeed', 'user' => 0, 'value' => 1)));
                                        $installDb->query($installDb->insert('table.options')->rows(array('name' => 'pageSize', 'user' => 0, 'value' => 5)));
                                        $installDb->query($installDb->insert('table.options')->rows(array('name' => 'postsListSize', 'user' => 0, 'value' => 10)));
                                        $installDb->query($installDb->insert('table.options')->rows(array('name' => 'commentsListSize', 'user' => 0, 'value' => 10)));
                                        $installDb->query($installDb->insert('table.options')->rows(array('name' => 'commentsHTMLTagAllowed', 'user' => 0, 'value' => NULL)));
                                        $installDb->query($installDb->insert('table.options')->rows(array('name' => 'postDateFormat', 'user' => 0, 'value' => 'Y-m-d')));
                                        $installDb->query($installDb->insert('table.options')->rows(array('name' => 'feedFullText', 'user' => 0, 'value' => 1)));
                                        $installDb->query($installDb->insert('table.options')->rows(array('name' => 'editorSize', 'user' => 0, 'value' => 350)));
                                        $installDb->query($installDb->insert('table.options')->rows(array('name' => 'autoSave', 'user' => 0, 'value' => 0)));
                                        $installDb->query($installDb->insert('table.options')->rows(array('name' => 'markdown', 'user' => 0, 'value' => 1)));
                                        $installDb->query($installDb->insert('table.options')->rows(array('name' => 'commentsMaxNestingLevels', 'user' => 0, 'value' => 5)));
                                        $installDb->query($installDb->insert('table.options')->rows(array('name' => 'commentsPostTimeout', 'user' => 0, 'value' => 24 * 3600 * 30)));
                                        $installDb->query($installDb->insert('table.options')->rows(array('name' => 'commentsUrlNofollow', 'user' => 0, 'value' => 1)));
                                        $installDb->query($installDb->insert('table.options')->rows(array('name' => 'commentsShowUrl', 'user' => 0, 'value' => 1)));
                                        $installDb->query($installDb->insert('table.options')->rows(array('name' => 'commentsMarkdown', 'user' => 0, 'value' => 0)));
                                        $installDb->query($installDb->insert('table.options')->rows(array('name' => 'commentsPageBreak', 'user' => 0, 'value' => 0)));
                                        $installDb->query($installDb->insert('table.options')->rows(array('name' => 'commentsThreaded', 'user' => 0, 'value' => 1)));
                                        $installDb->query($installDb->insert('table.options')->rows(array('name' => 'commentsPageSize', 'user' => 0, 'value' => 20)));
                                        $installDb->query($installDb->insert('table.options')->rows(array('name' => 'commentsPageDisplay', 'user' => 0, 'value' => 'last')));
                                        $installDb->query($installDb->insert('table.options')->rows(array('name' => 'commentsOrder', 'user' => 0, 'value' => 'ASC')));
                                        $installDb->query($installDb->insert('table.options')->rows(array('name' => 'commentsCheckReferer', 'user' => 0, 'value' => 1)));
                                        $installDb->query($installDb->insert('table.options')->rows(array('name' => 'commentsAutoClose', 'user' => 0, 'value' => 0)));
                                        $installDb->query($installDb->insert('table.options')->rows(array('name' => 'commentsPostIntervalEnable', 'user' => 0, 'value' => 1)));
                                        $installDb->query($installDb->insert('table.options')->rows(array('name' => 'commentsPostInterval', 'user' => 0, 'value' => 60)));
                                        $installDb->query($installDb->insert('table.options')->rows(array('name' => 'commentsShowCommentOnly', 'user' => 0, 'value' => 0)));
                                        $installDb->query($installDb->insert('table.options')->rows(array('name' => 'commentsAvatar', 'user' => 0, 'value' => 1)));
                                        $installDb->query($installDb->insert('table.options')->rows(array('name' => 'commentsAvatarRating', 'user' => 0, 'value' => 'G')));
                                        $installDb->query($installDb->insert('table.options')->rows(array('name' => 'routingTable', 'user' => 0, 'value' => 'a:25:{s:5:"index";a:3:{s:3:"url";s:1:"/";s:6:"widget";s:14:"Widget_Archive";s:6:"action";s:6:"render";}s:7:"archive";a:3:{s:3:"url";s:6:"/blog/";s:6:"widget";s:14:"Widget_Archive";s:6:"action";s:6:"render";}s:2:"do";a:3:{s:3:"url";s:22:"/action/[action:alpha]";s:6:"widget";s:9:"Widget_Do";s:6:"action";s:6:"action";}s:4:"post";a:3:{s:3:"url";s:24:"/archives/[cid:digital]/";s:6:"widget";s:14:"Widget_Archive";s:6:"action";s:6:"render";}s:10:"attachment";a:3:{s:3:"url";s:26:"/attachment/[cid:digital]/";s:6:"widget";s:14:"Widget_Archive";s:6:"action";s:6:"render";}s:8:"category";a:3:{s:3:"url";s:17:"/category/[slug]/";s:6:"widget";s:14:"Widget_Archive";s:6:"action";s:6:"render";}s:3:"tag";a:3:{s:3:"url";s:12:"/tag/[slug]/";s:6:"widget";s:14:"Widget_Archive";s:6:"action";s:6:"render";}s:6:"author";a:3:{s:3:"url";s:22:"/author/[uid:digital]/";s:6:"widget";s:14:"Widget_Archive";s:6:"action";s:6:"render";}s:6:"search";a:3:{s:3:"url";s:19:"/search/[keywords]/";s:6:"widget";s:14:"Widget_Archive";s:6:"action";s:6:"render";}s:10:"index_page";a:3:{s:3:"url";s:21:"/page/[page:digital]/";s:6:"widget";s:14:"Widget_Archive";s:6:"action";s:6:"render";}s:12:"archive_page";a:3:{s:3:"url";s:26:"/blog/page/[page:digital]/";s:6:"widget";s:14:"Widget_Archive";s:6:"action";s:6:"render";}s:13:"category_page";a:3:{s:3:"url";s:32:"/category/[slug]/[page:digital]/";s:6:"widget";s:14:"Widget_Archive";s:6:"action";s:6:"render";}s:8:"tag_page";a:3:{s:3:"url";s:27:"/tag/[slug]/[page:digital]/";s:6:"widget";s:14:"Widget_Archive";s:6:"action";s:6:"render";}s:11:"author_page";a:3:{s:3:"url";s:37:"/author/[uid:digital]/[page:digital]/";s:6:"widget";s:14:"Widget_Archive";s:6:"action";s:6:"render";}s:11:"search_page";a:3:{s:3:"url";s:34:"/search/[keywords]/[page:digital]/";s:6:"widget";s:14:"Widget_Archive";s:6:"action";s:6:"render";}s:12:"archive_year";a:3:{s:3:"url";s:18:"/[year:digital:4]/";s:6:"widget";s:14:"Widget_Archive";s:6:"action";s:6:"render";}s:13:"archive_month";a:3:{s:3:"url";s:36:"/[year:digital:4]/[month:digital:2]/";s:6:"widget";s:14:"Widget_Archive";s:6:"action";s:6:"render";}s:11:"archive_day";a:3:{s:3:"url";s:52:"/[year:digital:4]/[month:digital:2]/[day:digital:2]/";s:6:"widget";s:14:"Widget_Archive";s:6:"action";s:6:"render";}s:17:"archive_year_page";a:3:{s:3:"url";s:38:"/[year:digital:4]/page/[page:digital]/";s:6:"widget";s:14:"Widget_Archive";s:6:"action";s:6:"render";}s:18:"archive_month_page";a:3:{s:3:"url";s:56:"/[year:digital:4]/[month:digital:2]/page/[page:digital]/";s:6:"widget";s:14:"Widget_Archive";s:6:"action";s:6:"render";}s:16:"archive_day_page";a:3:{s:3:"url";s:72:"/[year:digital:4]/[month:digital:2]/[day:digital:2]/page/[page:digital]/";s:6:"widget";s:14:"Widget_Archive";s:6:"action";s:6:"render";}s:12:"comment_page";a:3:{s:3:"url";s:53:"[permalink:string]/comment-page-[commentPage:digital]";s:6:"widget";s:14:"Widget_Archive";s:6:"action";s:6:"render";}s:4:"feed";a:3:{s:3:"url";s:20:"/feed[feed:string:0]";s:6:"widget";s:14:"Widget_Archive";s:6:"action";s:4:"feed";}s:8:"feedback";a:3:{s:3:"url";s:31:"[permalink:string]/[type:alpha]";s:6:"widget";s:15:"Widget_Feedback";s:6:"action";s:6:"action";}s:4:"page";a:3:{s:3:"url";s:12:"/[slug].html";s:6:"widget";s:14:"Widget_Archive";s:6:"action";s:6:"render";}}')));
                                        $installDb->query($installDb->insert('table.options')->rows(array('name' => 'actionTable', 'user' => 0, 'value' => 'a:0:{}')));
                                        $installDb->query($installDb->insert('table.options')->rows(array('name' => 'panelTable', 'user' => 0, 'value' => 'a:0:{}')));
                                        $installDb->query($installDb->insert('table.options')->rows(array('name' => 'attachmentTypes', 'user' => 0, 'value' => '@image@')));
                                        $installDb->query($installDb->insert('table.options')->rows(array('name' => 'secret', 'user' => 0, 'value' => Typecho_Common::randString(32, true))));

                                        /** The initial category */
                                        $installDb->query($installDb->insert('table.metas')->rows(array('name' => _t('Default Category'), 'slug' => 'default', 'type' => 'category', 'description' => _t('Just a default category'),
                                        'count' => 1, 'order' => 1)));

                                        /** The initial relationship */
                                        $installDb->query($installDb->insert('table.relationships')->rows(array('cid' => 1, 'mid' => 1)));

                                        /** The initial content */
                                        $installDb->query($installDb->insert('table.contents')->rows(array('title' => _t('Welcome on Typecho'), 'slug' => 'start', 'created' => Typecho_Date::gmtTime(), 'modified' => Typecho_Date::gmtTime(),
                                        'text' => '<!--markdown-->' . _t('If you see this article, it means that your blog has been successfully installed.'), 'authorId' => 1, 'type' => 'post', 'status' => 'publish', 'commentsNum' => 1, 'allowComment' => 1,
                                        'allowPing' => 1, 'allowFeed' => 1, 'parent' => 0)));

                                        $installDb->query($installDb->insert('table.contents')->rows(array('title' => _t('About'), 'slug' => 'start-page', 'created' => Typecho_Date::gmtTime(), 'modified' => Typecho_Date::gmtTime(),
                                        'text' => '<!--markdown-->' . _t('This page was created by Typecho, this is just a test page.'), 'authorId' => 1, 'order' => 0, 'type' => 'page', 'status' => 'publish', 'commentsNum' => 0, 'allowComment' => 1,
                                        'allowPing' => 1, 'allowFeed' => 1, 'parent' => 0)));

                                        /** Initial comments */
                                        $installDb->query($installDb->insert('table.comments')->rows(array('cid' => 1, 'created' => Typecho_Date::gmtTime(), 'author' => 'Typecho', 'ownerId' => 1, 'url' => 'http://typecho.org',
                                        'ip' => '127.0.0.1', 'agent' => $options->generator, 'text' => 'Welcome to the big family of Typecho', 'type' => 'comment', 'status' => 'approved', 'parent' => 0)));

                                        /** Initial user */
                                        $password = empty($config['userPassword']) ? substr(uniqid(), 7) : $config['userPassword'];

                                        $installDb->query($installDb->insert('table.users')->rows(array('name' => $config['userName'], 'password' => Typecho_Common::hash($password), 'mail' => $config['userMail'],
                                        'url' => 'http://www.typecho.org', 'screenName' => $config['userName'], 'group' => 'administrator', 'created' => Typecho_Date::gmtTime())));

                                        unset($_SESSION['typecho']);
                                        header('Location: ./install.php?finish&user=' . urlencode($config['userName'])
                                            . '&password=' . urlencode($password));
                                    } catch (Typecho_Db_Exception $e) {
                                        $success = false;
                                        $code = $e->getCode();
?>
<h1 class="typecho-install-title"><?php _e('Installation failed!'); ?></h1>
                <div class="typecho-install-body">
                    <form method="post" action="?start" name="check">
<?php
                                        if(('Mysql' == $type && (1050 == $code || '42S01' == $code)) ||
                                        ('SQLite' == $type && ('HY000' == $code || 1 == $code)) ||
                                        ('Pgsql' == $type && '42P07' == $code)) {
                                            if(_r('delete')) {
                                                // Delete the original data
                                                $dbPrefix = $config['prefix'];
                                                $tableArray = array($dbPrefix . 'comments', $dbPrefix . 'contents', $dbPrefix . 'fields', $dbPrefix . 'metas', $dbPrefix . 'options', $dbPrefix . 'relationships', $dbPrefix . 'users',);
                                                foreach($tableArray as $table) {
                                                    if($type == 'Mysql') {
                                                        $installDb->query("DROP TABLE IF EXISTS `{$table}`");
                                                    } elseif($type == 'Pgsql') {
                                                        $installDb->query("DROP TABLE {$table}");
                                                    } elseif($type == 'SQLite') {
                                                        $installDb->query("DROP TABLE {$table}");
                                                    }
                                                }
                                                echo '<p class="message success">' . _t('The original data has been deleted') . '<br /><br /><button class="btn primary" type="submit" class="primary">'
                                                    . _t('Continue the installation &raquo;') . '</button></p>';
                                            } elseif (_r('goahead')) {
                                                // Use of the original data
                                                // However, you want to update the site users
                                                $installDb->query($installDb->update('table.options')->rows(array('value' => $config['siteUrl']))->where('name = ?', 'siteUrl'));
                                                unset($_SESSION['typecho']);
                                                header('Location: ./install.php?finish&use_old');
                                                exit;
                                            } else {
                                                 echo '<p class="message error">' . _t('The installer checks to the original data table already exists.')
                                                    . '<br /><br />' . '<button type="submit" name="delete" value="1" class="btn btn-warn">' . _t('Delete the original data') . '</button> '
                                                    . _t('Or') . ' <button type="submit" name="goahead" value="1" class="btn primary">' . _t('Use the original data') . '</button></p>';
                                            }
                                        } else {
                                            echo '<p class="message error">' . _t('Setup captured the following error: "%s". Installation terminated, please check your configuration information.',$e->getMessage()) . '</p>';
                                        }
                                        ?>
                    </form>
                </div>
                                        <?php
                                    }
            ?>
                <?php endif;?>
            <?php elseif (isset($_GET['config'])): ?>
            <?php
                    $adapters = array('Mysql', 'Pdo_Mysql', 'SQLite', 'Pdo_SQLite', 'Pgsql', 'Pdo_Pgsql');
                    foreach ($adapters as $firstAdapter) {
                        if (_p($firstAdapter)) {
                            break;
                        }
                    }
                    $adapter = _r('dbAdapter', $firstAdapter);
                    $type = explode('_', $adapter);
                    $type = array_pop($type);
            ?>
                <form method="post" action="?config" name="config">
                    <h1 class="typecho-install-title"><?php _e('Confirm your configuration'); ?></h1>
                    <div class="typecho-install-body">
                        <h2><?php _e('Database Configuration'); ?></h2>
                        <?php
                            if ('config' == _r('action')) {
                                $success = true;

                                if (_r('created') && !file_exists('./config.inc.php')) {
                                    echo '<p class="message error">' . _t('We did not detect the configuration file. Please check again or create it manually.') . '</p>';
                                    $success = false;
                                } else {
                                    if (NULL == _r('userUrl')) {
                                        $success = false;
                                        echo '<p class="message error">' . _t('Please fill in your website address') . '</p>';
                                    } else if (NULL == _r('userName')) {
                                        $success = false;
                                        echo '<p class="message error">' . _t('Please fill in your username') . '</p>';
                                    } else if (NULL == _r('userMail')) {
                                        $success = false;
                                        echo '<p class="message error">' . _t('Please fill in your email address') . '</p>';
                                    } else if (32 < strlen(_r('userName'))) {
                                        $success = false;
                                        echo '<p class="message error">' . _t('Username length exceeds the limit, do not exceed 32 characters') . '</p>';
                                    } else if (200 < strlen(_r('userMail'))) {
                                        $success = false;
                                        echo '<p class="message error">' . _t('Email address length exceeds the limit, do not exceed 200 characters') . '</p>';
                                    }
                                }

                                $_dbConfig = _rFrom('dbHost', 'dbUser', 'dbPassword', 'dbCharset', 'dbPort', 'dbDatabase', 'dbFile', 'dbDsn');

                                $_dbConfig = array_filter($_dbConfig);
                                $dbConfig = array();
                                foreach ($_dbConfig as $key => $val) {
                                    $dbConfig[strtolower (substr($key, 2))] = $val;
                                }

                                // Specific processing during installation on a specific server
                                if (_r('config')) {
                                    $replace = array_keys($dbConfig);
                                    foreach ($replace as &$key) {
                                        $key = '{' . $key . '}';
                                    }

                                    $config = str_replace($replace, array_values($dbConfig), _r('config'));
                                }

                                if (!isset($config) && $success && !_r('created')) {
                                    $installDb = new Typecho_Db ($adapter, _r('dbPrefix'));
                                    $installDb->addServer($dbConfig, Typecho_Db::READ | Typecho_Db::WRITE);


                                    /** Detection Database Configuration */
                                    try {
                                        $installDb->query('SELECT 1=1');
                                    } catch (Typecho_Db_Adapter_Exception $e) {
                                        $success = false;
                                        echo '<p class="message error">'
                                        . _t('Sorry, we can not connect to the database. Check the configuration again and continue with the installation') . '</p>';
                                    } catch (Typecho_Db_Exception $e) {
                                        $success = false;
                                        echo '<p class="message error">'
                                        . _t('Setup captured the following error: " %s ". installation is terminated, please check your configuration information.',$e->getMessage()) . '</p>';
                                    }
                                }

                                if($success) {
                                    Typecho_Cookie::set('__typecho_config', base64_encode(serialize(array_merge(array(
                                        'prefix'    =>  _r('dbPrefix'),
                                        'userName'  =>  _r('userName'),
                                        'userPassword'  =>  _r('userPassword'),
                                        'userMail'  =>  _r('userMail'),
                                        'adapter'   =>  $adapter,
                                        'siteUrl'   =>  _r('userUrl')
                                    ), $dbConfig))));

                                    if (_r('created')) {
                                        header('Location: ./install.php?start');
                                        exit;
                                    }

                                    /** Initial Configuration File */
                                    $lines = array_slice(file(__FILE__), 0, 52);
                                    $lines[] = "
/** Define the database parameters */
\$db = new Typecho_Db('{$adapter}', '" . _r('dbPrefix') . "');
\$db->addServer(" . (!isset($config) ? var_export($dbConfig, true) : $config) . ", Typecho_Db::READ | Typecho_Db::WRITE);
Typecho_Db::set(\$db);
";
                                    $contents = implode('', $lines);
                                    if (!Typecho_Common::isAppEngine()) {
                                        @file_put_contents('./config.inc.php', $contents);
                                    }

                                    // Create a temporary file used to identify this
                                    $_SESSION['typecho'] = 1;

                                    if (!file_exists('./config.inc.php')) {
                                    ?>
<div class="message notice"><p><?php _e('Setup can not automatically create the <strong>config.inc.php</strong> file.'); ?><br />
<?php _e('You can manually create the <strong>config.inc.php</strong> file in the root directory, and copy the following code inside.'); ?></p>
<p><textarea rows="5" onmouseover="this.select();" class="w-100 mono" readonly><?php echo htmlspecialchars($contents); ?></textarea></p>
<p><button name="created" value="1" type="submit" class="btn primary">Created, continue installation &raquo;</button></p></div>
                                    <?php
                                    } else {
                                        header('Location: ./install.php?start');
                                        exit;
                                    }
                                }

                                // Installation is not successful, delete the config file
                                if($success != true && file_exists(__TYPECHO_ROOT_DIR__ . '/config.inc.php')) {
                                    unlink(__TYPECHO_ROOT_DIR__ . '/config.inc.php');
                                }
                            }
                        ?>
                        <ul class="typecho-option">
                            <li>
                            <label for="dbAdapter" class="typecho-label"><?php _e('Database Adapter'); ?></label>
                            <select name="dbAdapter" id="dbAdapter">
                                <?php if (_p('Mysql')): ?><option value="Mysql"<?php if('Mysql' == $adapter): ?> selected=true<?php endif; ?>><?php _e('Mysql Native function adapter') ?></option><?php endif; ?>
                                <?php if (_p('SQLite')): ?><option value="SQLite"<?php if('SQLite' == $adapter): ?> selected=true<?php endif; ?>><?php _e('SQLite Native function adapter (SQLite 2.x)') ?></option><?php endif; ?>
                                <?php if (_p('Pgsql')): ?><option value="Pgsql"<?php if('Pgsql' == $adapter): ?> selected=true<?php endif; ?>><?php _e('Pgsql Native function adapter') ?></option><?php endif; ?>
                                <?php if (_p('Pdo_Mysql')): ?><option value="Pdo_Mysql"<?php if('Pdo_Mysql' == $adapter): ?> selected=true<?php endif; ?>><?php _e('Pdo Mysql adapter driver') ?></option><?php endif; ?>
                                <?php if (_p('Pdo_SQLite')): ?><option value="Pdo_SQLite"<?php if('Pdo_SQLite' == $adapter): ?> selected=true<?php endif; ?>><?php _e('Pdo SQLite adapter driver (SQLite 3.x)') ?></option><?php endif; ?>
                                <?php if (_p('Pdo_Pgsql')): ?><option value="Pdo_Pgsql"<?php if('Pdo_Pgsql' == $adapter): ?> selected=true<?php endif; ?>><?php _e('Pdo PostgreSql adapter driver') ?></option><?php endif; ?>
                            </select>
                            <p class="description"><?php _e('Please select the appropriate adapter for your database type.'); ?></p>
                            </li>
                            <?php require_once './install/' . $type . '.php'; ?>
                            <li>
                            <label class="typecho-label" for="dbPrefix"><?php _e('Database prefix'); ?></label>
                            <input type="text" class="text" name="dbPrefix" id="dbPrefix" value="<?php _v('dbPrefix', 'typecho_'); ?>" />
                            <p class="description"><?php _e('The default prefix is "typecho_"'); ?></p>
                            </li>
                        </ul>

                        <script>
                        var _select = document.config.dbAdapter;
                        _select.onchange = function() {
                            setTimeout("window.location.href = 'install.php?config&dbAdapter=" + this.value + "'; ",0);
                        }
                        </script>

                        <h2><?php _e('Create your administrator account'); ?></h2>
                        <ul class="typecho-option">
                            <li>
                            <label class="typecho-label" for="userUrl"><?php _e('Web site address'); ?></label>
                            <input type="text" name="userUrl" id="userUrl" class="text" value="<?php _v('userUrl', _u()); ?>" />
                            <p class="description"><?php _e('This is the site path automatically matched, if it\'s not correct modify it'); ?></p>
                            </li>
                            <li>
                            <label class="typecho-label" for="userName"><?php _e('Username'); ?></label>
                            <input type="text" name="userName" id="userName" class="text" value="<?php _v('userName', 'admin'); ?>" />
                            <p class="description"><?php _e('Please fill in your username'); ?></p>
                            </li>
                            <li>
                            <label class="typecho-label" for="userPassword"><?php _e('Password'); ?></label>
                            <input type="password" name="userPassword" id="userPassword" class="text" value="<?php _v('userPassword'); ?>" />
                            <p class="description"><?php _e('Please fill in your password, if left blank the system will give you a randomly generated'); ?></p>
                            </li>
                            <li>
                            <label class="typecho-label" for="userMail"><?php _e('Email address'); ?></label>
                            <input type="text" name="userMail" id="userMail" class="text" value="<?php _v('userMail', 'webmaster@yourdomain.com'); ?>" />
                            <p class="description"><?php _e('Please fill in your email address'); ?></p>
                            </li>
                        </ul>
                    </div>
                    <input type="hidden" name="action" value="config" />
                    <p class="submit"><button type="submit" class="btn primary"><?php _e('Confirm to start the installation &raquo;'); ?></button></p>
                </form>
            <?php  else: ?>
                <form method="post" action="?config">
                <h1 class="typecho-install-title"><?php _e('Welcome to Typecho'); ?></h1>
                <div class="typecho-install-body">
                <h2><?php _e('Installation Instructions'); ?></h2>
                <p><strong><?php _e('The setup will automatically detect if your server environment meets the minimum configuration requirements. If not, a message will appear at the top. Please follow the prompts on your host configuration information to check it. If the server environment meets the requirements, a button "Start" will appear at the bottom. Click this button to step to the complete installation.'); ?></strong></p>
                <h2><?php _e('Licenses and agreements'); ?></h2>
                <p><?php _e('Typecho is based on <a href="http://www.gnu.org/copyleft/gpl.html">GPL</a> release agreement, we allow users within the scope of the GPL license to use, copy, modify and distribute the program.'); ?>
                <?php _e('Within the scope of the GPL license, you are free to use it for commercial and non-commercial use.'); ?></p>
                <p><?php _e('Typecho software support the community by providing support to develop the core. Development team responsible for maintaining the daily development work program as well as new features.'); ?>
                <?php _e('If you encounter problems in the use of program BUG, and expectations of the new features, we welcome you to contribute directly to the exchange or the code in the community.'); ?>
                <?php _e('For outstanding contributions, your name will appear in the list of contributors.'); ?></p>
                </div>
                <p class="submit"><button type="submit" class="btn primary"><?php _e('I\'m ready to start the next step &raquo;'); ?></button></p>
                </form>
            <?php endif; ?>

            </div>
        </div>
    </div>
</div>
<?php
include 'admin/copyright.php';
include 'admin/footer.php';
?>
