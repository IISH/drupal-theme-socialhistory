<?php

/**
 * @file
 * Container for hook_preprocess_node().
 */

/**
 * Implements hook_preprocess_node().
 * 
 * @see
 *   alpha_preprocess().
 */
function iisg_alpha_preprocess_node(&$vars) {

  if (isset($vars['content']['links']['node']['#links']['node-readmore'])) {
    if (!isset($langcode)) {
      $langcode = $GLOBALS['language_content']->language;
    }
    $title = $vars['content']['links']['node']['#links']['node-readmore']['title'];
    if ($langcode == 'nl') {
      $vars['content']['links']['node']['#links']['node-readmore']['title'] = str_replace('Lees meer<span', '+ meer<span', $title);
    } else {
      $vars['content']['links']['node']['#links']['node-readmore']['title'] = str_replace('Read more<span', '+ more<span', $title);
    }
  }

}
