<?php

/**
 * @file
 * Container for hook_process_region().
 */

/**
 * Implements hook_process_region().
 * 
 * @see
 *   alpha_process().
 */
function iisg_alpha_process_region(&$vars) {
  if (in_array($vars['elements']['#region'], array('branding', 'menu_first', 'menu_second', 'content'))) {
    $theme = alpha_get_theme();

    switch ($vars['elements']['#region']) {

      case 'branding':
        if ($GLOBALS[LANGUAGE_TYPE_INTERFACE]->language == 'nl') {
          $vars['site_name'] = $theme->page['site_name'];
          $vars['logo'] = preg_replace('#/[^/]+.png#', '/logo-nl.png', $theme->page['logo']);
          $vars['logo_img'] = $vars['logo'] ? '<img src="' . $vars['logo'] . '" alt="' . $vars['site_name'] . '" id="logo" />' : '';
          $vars['linked_logo_img'] = $vars['logo'] ? l($vars['logo_img'], '<front>', array('attributes' => array('rel' => 'home', 'title' => t($vars['site_name'])), 'html' => TRUE)) : '';
        }
        break;      

      case 'menu_first':
      case 'menu_second':
        $vars['main_menu'] = $theme->page['main_menu'];
        $vars['secondary_menu'] = $theme->page['secondary_menu'];
        break;

      case 'content':
        if (drupal_is_front_page()) {
          $vars['title'] = "";
          // unset($vars['elements']['system_main']['default_message']);
          // unset($vars['page']['content']['system_main']['default_message']);
        }
        break;
    }
  }
}
