<?php
if (!defined('__DIR__')) {
    define('__DIR__', dirname(__FILE__));
}

define('__TYPECHO_ADMIN__', true);

/** Load profile */
if (!@include_once __DIR__ . '/../config.inc.php') {
    file_exists(__DIR__ . '/../install.php') ? header('Location: ../install.php') : print('Missing Config File');
    exit;
}

/** Initialization Components */
Typecho_Widget::widget('Widget_Init');

/** Register a plugin initialization */
Typecho_Plugin::factory('admin/common.php')->begin();

Typecho_Widget::widget('Widget_Options')->to($options);
Typecho_Widget::widget('Widget_User')->to($user);
Typecho_Widget::widget('Widget_Security')->to($security);
Typecho_Widget::widget('Widget_Menu')->to($menu);

/** Context initialization */
$request = $options->request;
$response = $options->response;

/** Detecting whether the first login */
$currentMenu = $menu->getCurrentMenu();
list($prefixVersion, $suffixVersion) = explode('/', $options->version);
$params = parse_url($currentMenu[2]);
$adminFile = basename($params['path']);

if (!$user->logged && !Typecho_Cookie::get('__typecho_first_run') && !empty($currentMenu)) {

    if ('welcome.php' != $adminFile) {
        $response->redirect(Typecho_Common::url('welcome.php', $options->adminUrl));
    } else {
        Typecho_Cookie::set('__typecho_first_run', 1);
    }

} else {

    /** Detecting whether the version upgrade */
    if ($user->pass('administrator', true) && !empty($currentMenu)) {
        $mustUpgrade = (!defined('Typecho_Common::VERSION') || version_compare(str_replace('/', '.', Typecho_Common::VERSION),
        str_replace('/', '.', $options->version), '>'));

        if ($mustUpgrade && 'upgrade.php' != $adminFile) {
            $response->redirect(Typecho_Common::url('upgrade.php', $options->adminUrl));
        } else if (!$mustUpgrade && 'upgrade.php' == $adminFile) {
            $response->redirect($options->adminUrl);
        } else if (!$mustUpgrade && 'welcome.php' == $adminFile && $user->logged) {
            $response->redirect($options->adminUrl);
        }
    }

}
