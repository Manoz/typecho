<?php
/**
 * Typecho Blog Platform
 *
 * @copyright  Copyright (c) 2008 Typecho team (http://www.typecho.org)
 * @license    GNU General Public License 2.0
 * @version    $Id: index.php 1153 2009-07-02 10:53:22Z magike.net $
 *
 * English translation
 * @author  Kevin Legrand
 * @link    https://github.com/Manoz/
 * @version 1.0
 */

/** Load config */
if (!@include_once 'config.inc.php') {
    file_exists('./install.php') ? header('Location: install.php') : print('Missing Config File');
    exit;
}

/** Init components */
Typecho_Widget::widget('Widget_Init');

/** Register plugin init */
Typecho_Plugin::factory('index.php')->begin();

/** Begin routing */
Typecho_Router::dispatch();

/** Register end plugin */
Typecho_Plugin::factory('index.php')->end();
