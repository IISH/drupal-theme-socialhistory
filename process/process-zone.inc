<?php

/**
 * @file
 * Container for hook_process_zone().
 */

/**
 * Implements hook_process_zone().
 * 
 * @see
 *   alpha_process().
 */
function iisg_alpha_process_zone(&$vars) {
  if ($vars['elements']['#zone'] == 'preface') {
    $theme = alpha_get_theme();
    $vars['messages'] = $theme->page['messages'];
    $vars['breadcrumb'] = $theme->page['breadcrumb'];
  }
}
