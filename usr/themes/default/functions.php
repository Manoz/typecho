<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

function themeConfig($form) {
    $logoUrl = new Typecho_Widget_Helper_Form_Element_Text('logoUrl', NULL, NULL, _t('Logo URL'), _t('Enter here the URL of an image for your logo.'));
    $form->addInput($logoUrl);

    $sidebarBlock = new Typecho_Widget_Helper_Form_Element_Checkbox('sidebarBlock',
    array('ShowRecentPosts' => _t('Show recent posts?'),
    'ShowRecentComments' => _t('Show recent comments?'),
    'ShowCategory' => _t('Show categories?'),
    'ShowArchive' => _t('Show archives?'),
    'ShowOther' => _t('Show other?')),
    array('ShowRecentPosts', 'ShowRecentComments', 'ShowCategory', 'ShowArchive', 'ShowOther'), _t('Sidebar widgets'));

    $form->addInput($sidebarBlock->multiMode());
}


/*
function themeFields($layout) {
    $logoUrl = new Typecho_Widget_Helper_Form_Element_Text('logoUrl', NULL, NULL, _t('Logo URL'), _t('Enter here the URL of an image for your logo.'));
    $layout->addItem($logoUrl);
}
*/

