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
function iisg_alpha_preprocess_region(&$vars) {
  if (in_array($vars['elements']['#region'], array('menu_first', 'menu_second', 'content'))) {
    $theme = alpha_get_theme();

    switch ($vars['elements']['#region']) {
      case 'content':
        if (drupal_is_front_page()) {
          $vars['title'] = "";
          // unset($vars['elements']['system_main']['default_message']);
          // unset($vars['page']['content']['system_main']['default_message']);
        } else {
          // Set the language of the title:
          $vars['dir'] = ($GLOBALS[LANGUAGE_TYPE_CONTENT]->direction == 1) ? "rtl" : "ltr";
          $vars['lang'] = $GLOBALS[LANGUAGE_TYPE_CONTENT]->language;
        }
        break;
    }
  }
}
