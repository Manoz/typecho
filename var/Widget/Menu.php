<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
/**
 * Typecho Blog Platform
 *
 * @copyright  Copyright (c) 2008 Typecho team (http://www.typecho.org)
 * @license    GNU General Public License 2.0
 * @version    $Id$
 */

/**
 * Backend menu display
 *
 * @package Widget
 */
class Widget_Menu extends Typecho_Widget
{
    /**
     * Father menu list
     *
     * @access private
     * @var array
     */
    private $_menu = array();

    /**
     * Current parent menu
     *
     * @access private
     * @var integer
     */
    private $_currentParent = 1;

    /**
     * The current sub-menu
     *
     * @access private
     * @var integer
     */
    private $_currentChild = 0;

    /**
     * Current page
     *
     * @access private
     * @var string
     */
    private $_currentUrl;

    /**
     * Global Options
     *
     * @access protected
     * @var Widget_Options
     */
    protected $options;

    /**
     * User Object
     *
     * @access protected
     * @var Widget_User
     */
    protected $user;

    /**
     * The current menu title
     * @var string
     */
    public $title;

    /**
     * The current increase in project links
     * @var string
     */
    public $addLink;

    /**
     * Constructors, init components
     *
     * @access public
     * @param mixed $request request object
     * @param mixed $response response object
     * @param mixed $params Parameter List
     * @return void
     */
    public function __construct($request, $response, $params = NULL)
    {
        parent::__construct($request, $response, $params);

        /** Initialization common components */
        $this->options = $this->widget('Widget_Options');
        $this->user = $this->widget('Widget_User');
    }

    /**
     * Executive functions, init menu
     *
     * @access public
     * @return void
     */
    public function execute()
    {
        $parentNodes = array(NULL, _t('Control Panel'), _t('Write'), _t('Administration'), _t('Setup'));

        $childNodes =  array(
        array(
            array(_t('Login'), _t('Log on to %s', $this->options->title), 'login.php', 'visitor'),
            array(_t('Register'), _t('Sign up to %s', $this->options->title), 'register.php', 'visitor')
        ),
        array(
            array(_t('Summary'), _t('Site summary'), 'index.php', 'subscriber'),
            array(_t('Personal Settings'), _t('Personal Settings'), 'profile.php', 'subscriber'),
            array(_t('Plugins'), _t('Plugin Manager'), 'plugins.php', 'administrator'),
            array(array('Widget_Plugins_Config', 'getMenuTitle'), array('Widget_Plugins_Config', 'getMenuTitle'), 'options-plugin.php?config=', 'administrator', true),
            array(_t('Appearance'), _t('Website Appearance'), 'themes.php', 'administrator'),
            array(array('Widget_Themes_Files', 'getMenuTitle'), array('Widget_Themes_Files', 'getMenuTitle'), 'theme-editor.php', 'administrator', true),
            array(array('Widget_Themes_Config', 'getMenuTitle'), array('Widget_Themes_Config', 'getMenuTitle'), 'options-theme.php', 'administrator', true),
            array(_t('Upgrade'), _t('The upgrade process'), 'upgrade.php', 'administrator', true),
            array(_t('Welcome'), _t('Welcome'), 'welcome.php', 'subscriber', true)
        ),
        array(
            array(_t('Write a post'), _t('Compose new post'), 'write-post.php', 'contributor'),
            array(array('Widget_Contents_Post_Edit', 'getMenuTitle'), array('Widget_Contents_Post_Edit', 'getMenuTitle'), 'write-post.php?cid=', 'contributor', true),
            array(_t('Create a page'), _t('Create a new page'), 'write-page.php', 'editor'),
            array(array('Widget_Contents_Page_Edit', 'getMenuTitle'), array('Widget_Contents_Page_Edit', 'getMenuTitle'), 'write-page.php?cid=', 'editor', true),
        ),
        array(
            array(_t('Posts'), _t('Manage posts'), 'manage-posts.php', 'contributor', false, 'write-post.php'),
            array(array('Widget_Contents_Post_Admin', 'getMenuTitle'), array('Widget_Contents_Post_Admin', 'getMenuTitle'), 'manage-posts.php?uid=', 'contributor', true),
            array(_t('Pages'), _t('Manage pages'), 'manage-pages.php', 'editor', false, 'write-page.php'),
            array(_t('Comments'), _t('Manage comments'), 'manage-comments.php', 'contributor'),
            array(array('Widget_Comments_Admin', 'getMenuTitle'), array('Widget_Comments_Admin', 'getMenuTitle'), 'manage-comments.php?cid=', 'contributor', true),
            array(_t('Categories'), _t('Manage categories'), 'manage-categories.php', 'editor', false, 'category.php'),
            array(_t('New categorie'), _t('New categorie'), 'category.php', 'editor', true),
            array(array('Widget_Metas_Category_Admin', 'getMenuTitle'), array('Widget_Metas_Category_Admin', 'getMenuTitle'), 'manage-categories.php?parent=', 'editor', true, array('Widget_Metas_Category_Admin', 'getAddLink')),
            array(array('Widget_Metas_Category_Edit', 'getMenuTitle'), array('Widget_Metas_Category_Edit', 'getMenuTitle'), 'category.php?mid=', 'editor', true),
            array(array('Widget_Metas_Category_Edit', 'getMenuTitle'), array('Widget_Metas_Category_Edit', 'getMenuTitle'), 'category.php?parent=', 'editor', true),
            array(_t('Tags'), _t('Manage tags'), 'manage-tags.php', 'editor'),
            array(array('Widget_Metas_Tag_Admin', 'getMenuTitle'), array('Widget_Metas_Tag_Admin', 'getMenuTitle'), 'manage-tags.php?mid=', 'editor', true),
            array(_t('Files'), _t('Manage files'), 'manage-medias.php', 'editor'),
            array(array('Widget_Contents_Attachment_Edit', 'getMenuTitle'), array('Widget_Contents_Attachment_Edit', 'getMenuTitle'), 'media.php?cid=', 'contributor', true),
            array(_t('Users'), _t('Manage users'), 'manage-users.php', 'administrator', false, 'user.php'),
            array(_t('New user'), _t('New user'), 'user.php', 'administrator', true),
            array(array('Widget_Users_Edit', 'getMenuTitle'), array('Widget_Users_Edit', 'getMenuTitle'), 'user.php?uid=', 'administrator', true),
        ),
        array(
            array(_t('General'), _t('General settings'), 'options-general.php', 'administrator'),
            array(_t('Discussions'), _t('Discussions settings'), 'options-discussion.php', 'administrator'),
            array(_t('Read'), _t('Read settings'), 'options-reading.php', 'administrator'),
            array(_t('Permalinks'), _t('Permalink settings'), 'options-permalink.php', 'administrator'),
        ));

        /** Get extended menu */
        $panelTable = unserialize($this->options->panelTable);
        $extendingParentMenu = empty($panelTable['parent']) ? array() : $panelTable['parent'];
        $extendingChildMenu = empty($panelTable['child']) ? array() : $panelTable['child'];
        $currentUrl = $this->request->makeUriByRequest();
        $adminUrl = $this->options->adminUrl;
        $menu = array();
        $defaultChildeNode = array(NULL, NULL, NULL, 'administrator', false, NULL);

        $currentUrlParts = parse_url($currentUrl);
        $currentUrlParams = array();
        if (!empty($currentUrlParts['query'])) {
            parse_str($currentUrlParts['query'], $currentUrlParams);
        }

        if ('/' == $currentUrlParts['path'][strlen($currentUrlParts['path']) - 1]) {
            $currentUrlParts['path'] .= 'index.php';
        }

        foreach ($extendingParentMenu as $key => $val) {
            $parentNodes[10 + $key] = $val;
        }

        foreach ($extendingChildMenu as $key => $val) {
            $childNodes[$key] = array_merge(isset($childNodes[$key]) ? $childNodes[$key] : array(), $val);
        }

        foreach ($parentNodes as $key => $parentNode) {
            // this is a simple struct than before
            $children = array();
            $showedChildrenCount = 0;
            $firstUrl = NULL;

            foreach ($childNodes[$key] as $inKey => $childNode) {
                // magic merge
                $childNode += $defaultChildeNode;
                list ($name, $title, $url, $access, $hidden, $addLink) = $childNode;

                // Save the most original hidden information
                $orgHidden = $hidden;

                // parse url
                $url = Typecho_Common::url($url, $adminUrl);

                // compare url
                $urlParts = parse_url($url);
                $urlParams = array();
                if (!empty($urlParts['query'])) {
                    parse_str($urlParts['query'], $urlParams);
                }

                $validate = true;
                if ($urlParts['path'] != $currentUrlParts['path']) {
                    $validate = false;
                } else {
                    foreach ($urlParams as $paramName => $paramValue) {
                        if (!isset($currentUrlParams[$paramName])) {
                            $validate = false;
                            break;
                        }
                    }
                }

                if ($validate
                    && basename($urlParts['path']) == 'extending.php'
                    && !empty($currentUrlParams['panel']) && !empty($urlParams['panel'])
                    && $urlParams['panel'] != $currentUrlParams['panel']){
                    $validate = false;
                }

                if ($hidden && $validate) {
                    $hidden = false;
                }

                if (!$hidden && !$this->user->pass($access, true)) {
                    $hidden = true;
                }

                if (!$hidden) {
                    $showedChildrenCount ++;

                    if (empty($firstUrl)) {
                        $firstUrl = $url;
                    }

                    if (is_array($name)) {
                        list($widget, $method) = $name;
                        $name = Typecho_Widget::widget($widget)->$method();
                    }

                    if (is_array($title)) {
                        list($widget, $method) = $title;
                        $title = Typecho_Widget::widget($widget)->$method();
                    }

                    if (is_array($addLink)) {
                        list($widget, $method) = $addLink;
                        $addLink = Typecho_Widget::widget($widget)->$method();
                    }
                }

                if ($validate) {
                    if ('visitor' != $access) {
                        $this->user->pass($access);
                    }

                    $this->_currentParent = $key;
                    $this->_currentChild = $inKey;
                    $this->title = $title;
                    $this->addLink = $addLink ? Typecho_Common::url($addLink, $adminUrl) : NULL;
                }

                $children[$inKey] = array(
                    $name,
                    $title,
                    $url,
                    $access,
                    $hidden,
                    $addLink,
                    $orgHidden
                );
            }

            $menu[$key] = array($parentNode, $showedChildrenCount > 0, $firstUrl,$children);
        }

        $this->_menu = $menu;
        $this->_currentUrl = $currentUrl;
    }

    /**
     * Get the current menu
     *
     * @access public
     * @return array
     */
    public function getCurrentMenu()
    {
        return $this->_currentParent > 0 ? $this->_menu[$this->_currentParent][3][$this->_currentChild] : NULL;
    }

    /**
     * Output parent menu
     *
     * @access public
     * @return string
     */
    public function output($class = 'focus', $childClass = 'focus')
    {
        foreach ($this->_menu as $key => $node) {
            if (!$node[1] || !$key) {
                continue;
            }

            echo "<ul class=\"root" . ($key == $this->_currentParent ? ' ' . $class : NULL)
                . "\"><li class=\"parent\"><a href=\"{$node[2]}\">{$node[0]}</a></dt>"
                . "</li><ul class=\"child\">";

            $last = 0;
            foreach ($node[3] as $inKey => $inNode) {
                if (!$inNode[4]) {
                    $last = $inKey;
                }
            }

            foreach ($node[3] as $inKey => $inNode) {
                if ($inNode[4]) {
                    continue;
                }

                $classes = array();
                if ($key == $this->_currentParent && $inKey == $this->_currentChild) {
                    $classes[] = $childClass;
                } else if ($inNode[6]) {
                    continue;
                }

                if ($inKey == $last) {
                    $classes[] = 'last';
                }

                echo "<li" . (!empty($classes) ? ' class="' . implode(' ', $classes) . '"' : NULL) .
                    "><a href=\"" . ($key == $this->_currentParent && $inKey == $this->_currentChild ? $this->_currentUrl : $inNode[2]) . "\">{$inNode[0]}</a></li>";
            }

            echo "</ul></ul>";
        }
    }
}

