<?php

/**
 * @file
 * This file is empty by default because the base theme chain (Alpha & Omega) provides
 * all the basic functionality. However, in case you wish to customize the output that Drupal
 * generates through Alpha & Omega this file is a good place to do so.
 * 
 * Alpha comes with a neat solution for keeping this file as clean as possible while the code
 * for your subtheme grows. Please read the README.txt in the /preprocess and /process subfolders
 * for more information on this topic.
 */


/**
 * Return a themed breadcrumb trail. (Taken from Zen)
 *
 * http://api.drupal.org/api/drupal/modules--system--system.api.php/function/hook_menu_breadcrumb_alter/7
 * if ($breadcrumb[0]['href'] == '<front>') { $breadcrumb[0]['title'] = 'iisg'; }
 * en ook breadcrumb op home
 *
 * @param $variables
 *   - title: An optional string to be used as a navigational heading to give
 *     context for breadcrumb links to screen-reader users.
 *   - title_attributes_array: Array of HTML attributes for the title. It is
 *     flattened into a string within the theme function.
 *   - breadcrumb: An array containing the breadcrumb links.
 * @return
 *   A string containing the breadcrumb output.
 */
function iisg_breadcrumb($variables) {
  $breadcrumb = $variables['breadcrumb'];

  // Return the breadcrumb with separators.
  if (!empty($breadcrumb)) {
    $breadcrumb_separator = ' > ';
    $trailing_separator = $title = '';

    $item = menu_get_item();
    if (!empty($item['tab_parent'])) {
      // If we are on a non-default tab, use the tab's title.
      $title = check_plain($item['title']);
    }
    else {
      $title = drupal_get_title();
    }
    if ($title) {
      $trailing_separator = $breadcrumb_separator;
    }

    // Provide a navigational heading to give context for breadcrumb links to
    // screen-reader users.
    if (empty($variables['title'])) {
      $variables['title'] = t('You are here');
    }
    // Unless overridden by a preprocess function, make the heading invisible.
    if (!isset($variables['title_attributes_array']['class'])) {
      $variables['title_attributes_array']['class'][] = 'element-invisible';
    }
    $heading = '<h2' . drupal_attributes($variables['title_attributes_array']) . '>' . $variables['title'] . '</h2>';

//    return '<div class="breadcrumb">' . $heading . implode($breadcrumb_separator, $breadcrumb) . $trailing_separator . $title . '</div>';
    return '<div class="breadcrumb">' . $heading . implode($breadcrumb_separator, $breadcrumb) . '</div>';
  }
  // Otherwise, return an empty string.
  return '';
}

// http://api.drupal.org/api/drupal/modules--field--field.module/function/theme_field/7
function iisg_field__field_color($variables) {
  // Render the items.
  foreach ($variables['items'] as $delta => $item) {
    $output = drupal_render($item);
  }

  return $output;
}

function iisg_field__field_slideshow_link($variables) {
  // Render the items.
  foreach ($variables['items'] as $delta => $item) {
    $output = '<span class="read-more">'. drupal_render($item) .'</span>';
  }

  return $output;
}

// TODO print key instead of value (or value instead of #markup)
function iisg_field__field_slideshow_image_size($variables) {
  // Render the items.
  foreach ($variables['items'] as $delta => $item) {
    $output = 'img'. drupal_render($item); // A class should start with alpha char.
  }

  return $output;
}

// Hide current language from language switcher.
function iisg_language_switch_links_alter(array &$links, $type, $path) {
  global $language;
  $current = $language->language;
  unset($links[$current]);
}

/*
function print_node_view($node) {

  if (node view) && (module_exists('print')) {

    $print_html_link_pos = variable_get('print_html_link_pos', array(PRINT_HTML_LINK_POS_DEFAULT => PRINT_HTML_LINK_POS_DEFAULT));
    $allowed_type = print_link_allowed(array('type' => 'node', 'node' => $node, 'teaser' => FALSE));

    if (!empty($print_html_link_pos['corner'])) {
      $variables['print_links'] = '<span class="print-link">'. print_insert_link(NULL, $node) .'</span>';
    }

  }
}

*/




/*
 * Allow HTML in pager link.
 */
function iisg_pager_link($variables) {
  $text = $variables['text'];
  $page_new = $variables['page_new'];
  $element = $variables['element'];
  $parameters = $variables['parameters'];
  $attributes = $variables['attributes'];

  $page = isset($_GET['page']) ? $_GET['page'] : '';
  if ($new_page = implode(',', pager_load_array($page_new[$element], $element, explode(',', $page)))) {
    $parameters['page'] = $new_page;
  }

  $query = array();
  if (count($parameters)) {
    $query = drupal_get_query_parameters($parameters, array());
  }
  if ($query_pager = pager_get_query_parameters()) {
    $query = array_merge($query, $query_pager);
  }

  // Set each pager link title
  if (!isset($attributes['title'])) {
    static $titles = NULL;
    if (!isset($titles)) {

      $text_first    = theme('image', array( 'path' => path_to_theme() . '/images/first.png', 'alt' => t('First')));
      $text_previous = theme('image', array( 'path' => path_to_theme() . '/images/previous.png', 'alt' => t('Previous')));
      $text_next     = theme('image', array( 'path' => path_to_theme() . '/images/next.png', 'alt' => t('Next')));
      $text_last     = theme('image', array( 'path' => path_to_theme() . '/images/last.png', 'alt' => t('Last')));

      $titles = array(
        $text_first => t('Go to first page'), 
        $text_previous => t('Go to previous page'), 
        $text_next => t('Go to next page'), 
        $text_last => t('Go to last page'),
      );
    }
    if (isset($titles[$text])) {
      $attributes['title'] = $titles[$text];
    }
    elseif (is_numeric($text)) {
      $attributes['title'] = t('Go to page @number', array('@number' => $text));
    }
  }

  return l($text, $_GET['q'], array('html' => TRUE, 'attributes' => $attributes, 'query' => $query));
}

/*
 * Use images for first, last, previous, next.
 */
function iisg_pager($variables) {
  $tags = $variables['tags'];
  $element = $variables['element'];
  $parameters = $variables['parameters'];
  $quantity = $variables['quantity'];
  global $pager_page_array, $pager_total;

  // Calculate various markers within this pager piece:
  // Middle is used to "center" pages around the current page.
  $pager_middle = ceil($quantity / 2);
  // current is the page we are currently paged to
  $pager_current = $pager_page_array[$element] + 1;
  // first is the first page listed by this pager piece (re quantity)
  $pager_first = $pager_current - $pager_middle + 1;
  // last is the last page listed by this pager piece (re quantity)
  $pager_last = $pager_current + $quantity - $pager_middle;
  // max is the maximum page number
  $pager_max = $pager_total[$element];
  // End of marker calculations.

  // Prepare for generation loop.
  $i = $pager_first;
  if ($pager_last > $pager_max) {
    // Adjust "center" if at end of query.
    $i = $i + ($pager_max - $pager_last);
    $pager_last = $pager_max;
  }
  if ($i <= 0) {
    // Adjust "center" if at start of query.
    $pager_last = $pager_last + (1 - $i);
    $i = 1;
  }
  // End of generation loop preparation.

  $text_first    = theme('image', array( 'path' => path_to_theme() . '/images/first.png', 'alt' => t('First')));
  $text_previous = theme('image', array( 'path' => path_to_theme() . '/images/previous.png', 'alt' => t('Previous')));
  $text_next     = theme('image', array( 'path' => path_to_theme() . '/images/next.png', 'alt' => t('Next')));
  $text_last     = theme('image', array( 'path' => path_to_theme() . '/images/last.png', 'alt' => t('Last')));

  $li_first = theme('pager_first', array('text' => (isset($tags[0]) ? $tags[0] : $text_first), 'element' => $element, 'parameters' => $parameters));
  $li_previous = theme('pager_previous', array('text' => (isset($tags[1]) ? $tags[1] : $text_previous), 'element' => $element, 'interval' => 1, 'parameters' => $parameters));
  $li_next = theme('pager_next', array('text' => (isset($tags[3]) ? $tags[3] : $text_next), 'element' => $element, 'interval' => 1, 'parameters' => $parameters));
  $li_last = theme('pager_last', array('text' => (isset($tags[4]) ? $tags[4] : $text_last), 'element' => $element, 'parameters' => $parameters));

  if ($pager_total[$element] > 1) {
    if ($li_first) {
      $items[] = array(
        'class' => array('pager-first'), 
        'data' => $li_first,
      );
    }
    if ($li_previous) {
      $items[] = array(
        'class' => array('pager-previous'), 
        'data' => $li_previous,
      );
    }
    if ($li_next) {
      $items[] = array(
        'class' => array('pager-next'), 
        'data' => $li_next,
      );
    }
    if ($li_last) {
      $items[] = array(
        'class' => array('pager-last'), 
        'data' => $li_last,
      );
    }

    // When there is more than one page, create the pager list.
    if ($i != $pager_max) {
      if ($i > 1) {
        $items[] = array(
          'class' => array('pager-ellipsis'), 
          'data' => '…',
        );
      }
      // Now generate the actual pager piece.
      for (; $i <= $pager_last && $i <= $pager_max; $i++) {
        if ($i < $pager_current) {
          $items[] = array(
            'class' => array('pager-item'), 
            'data' => theme('pager_previous', array('text' => $i, 'element' => $element, 'interval' => ($pager_current - $i), 'parameters' => $parameters)),
          );
        }
        if ($i == $pager_current) {
          $items[] = array(
            'class' => array('pager-current'), 
            'data' => '<span>'. $i .'</span>',
          );
        }
        if ($i > $pager_current) {
          $items[] = array(
            'class' => array('pager-item'), 
            'data' => theme('pager_next', array('text' => $i, 'element' => $element, 'interval' => ($i - $pager_current), 'parameters' => $parameters)),
          );
        }
      }
      if ($i < $pager_max) {
        $items[] = array(
          'class' => array('pager-ellipsis'), 
          'data' => '…',
        );
      }
    }
    // End generation.
    return '<h2 class="element-invisible">' . t('Pages') . '</h2>' . theme('item_list', array(
      'items' => $items, 
      'attributes' => array('class' => array('pager')),
    ));
  }
}


function iisg_pager_first($variables) {
  $text = $variables['text'];
  $element = $variables['element'];
  $parameters = $variables['parameters'];
  global $pager_page_array;
  $output = '';

  // If we are anywhere but the first page
  if ($pager_page_array[$element] > 0) {
    $output = theme('pager_link', array('text' => $text, 'page_new' => pager_load_array(0, $element, $pager_page_array), 'element' => $element, 'parameters' => $parameters));
  } else {
    $output = theme('image', array( 'path' => path_to_theme() . '/images/first-nolink.png', 'alt' => t('First')));
  }

  return $output;
}

function iisg_pager_previous($variables) {
  $text = $variables['text'];
  $element = $variables['element'];
  $interval = $variables['interval'];
  $parameters = $variables['parameters'];
  global $pager_page_array;
  $output = '';

  // If we are anywhere but the first page
  if ($pager_page_array[$element] > 0) {
    $page_new = pager_load_array($pager_page_array[$element] - $interval, $element, $pager_page_array);

    // If the previous page is the first page, mark the link as such.
    if ($page_new[$element] == 0) {
      $output = theme('pager_first', array('text' => $text, 'element' => $element, 'parameters' => $parameters));
    }
    // The previous page is not the first page.
    else {
      $output = theme('pager_link', array('text' => $text, 'page_new' => $page_new, 'element' => $element, 'parameters' => $parameters));
    }
  } else {
    $output = theme('image', array( 'path' => path_to_theme() . '/images/previous-nolink.png', 'alt' => t('Previous')));
  }

  return $output;
}

function iisg_pager_next($variables) {
  $text = $variables['text'];
  $element = $variables['element'];
  $interval = $variables['interval'];
  $parameters = $variables['parameters'];
  global $pager_page_array, $pager_total;
  $output = '';

  // If we are anywhere but the last page
  if ($pager_page_array[$element] < ($pager_total[$element] - 1)) {
    $page_new = pager_load_array($pager_page_array[$element] + $interval, $element, $pager_page_array);
    // If the next page is the last page, mark the link as such.
    if ($page_new[$element] == ($pager_total[$element] - 1)) {
      $output = theme('pager_last', array('text' => $text, 'element' => $element, 'parameters' => $parameters));
    }
    // The next page is not the last page.
    else {
      $output = theme('pager_link', array('text' => $text, 'page_new' => $page_new, 'element' => $element, 'parameters' => $parameters));
    }
  } else {
    $output = theme('image', array( 'path' => path_to_theme() . '/images/next-nolink.png', 'alt' => t('Next')));
  }

  return $output;
}

function iisg_pager_last($variables) {
  $text = $variables['text'];
  $element = $variables['element'];
  $parameters = $variables['parameters'];
  global $pager_page_array, $pager_total;
  $output = '';

  // If we are anywhere but the last page
  if ($pager_page_array[$element] < ($pager_total[$element] - 1)) {
    $output = theme('pager_link', array('text' => $text, 'page_new' => pager_load_array($pager_total[$element] - 1, $element, $pager_page_array), 'element' => $element, 'parameters' => $parameters));
  } else {
    $output = theme('image', array( 'path' => path_to_theme() . '/images/last-nolink.png', 'alt' => t('Last')));
  }

  return $output;
}

/*
 * Make "Profile" translatable.
 */
function iisg_preprocess_user_profile_category(&$variables) {
  $variables['title'] = t($variables['title']);
}


