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
// START TEMPLATE FUNCTIONS FOR IISH AGENDA
/*
 * Adds reservation number (node id) and cancelled value to template
 */
function iisg_preprocess_node(&$variables, $hook) {
	if ($variables["type"] == "event") {

		$variables["reservation_number"] = t("Reserveringsnummer: ") . "#" . $variables["nid"];

		if ($variables['field_event_status'][0]['value'] == 'cancelled') {
			$variables["cancelled"] = t("Geannuleerd");
		}

		if (drupal_valid_path('clone/' . $variables['nid'])) {
			$variables["clone"] = true;
		} else {
			$variables["clone"] = false;
		}

        $variables["create_date"] = format_date($variables["created"], 'event_date');
	}
}

/*
 * Makes changes to event views output
 */
function iisg_views_pre_render(&$view) {
	switch ($view->name) {
		case 'today_events':
			foreach ($view->result as $r => &$result) {
				_cancelcheck($result);
			}
			break;
		case 'upcoming_events':
			// My events display should show any status
			if ($view->current_display !== "page_1") {
				foreach ($view->result as $r => &$result) {
					_cancelcheck($result);
				}
			}
			break;
		case 'calendar':
			foreach ($view->result as $r => &$result) {
				_cancelcheck($result);
				$status = issetor($result->field_field_event_status[0]['raw']['value'], false);
				if (!$status || $status == "new") {
					$result->node_title = "- nog niet bevestigd -";
				}
			}
			break;
		case 'catering':
			foreach ($view->result as $r => &$result) {
				_cancelcheck($result);
				if (isset($result->field_field_note[0]) && isset($result->field_field_event_room[0])) {
					$bg_image = "/" . drupal_get_path('module', 'iishagenda') . "/images/note_bg.png";
					$note = '<div class="messagepop pop" style="background-image: url(' . $bg_image . ');">' . nl2br($result->field_field_note[0]["rendered"]["#markup"]) . '</div>';
					$index = count($result->field_field_event_room) - 1;
					$markup = $result->field_field_event_room[$index]["rendered"]["#markup"] . '<br><a href="#" class="messagepop-link">opmerking!</a>' . $note;
					$result->field_field_event_room[$index]["rendered"] = array('#markup' => $markup, '#access' => TRUE);
				}
			}
			break;
	}
}

/*
 * Add cancelled div for cancelled events
 */
function _cancelcheck(&$result) {
	if (isset($result->field_field_event_status) && isset($result->field_field_event_status[0])) {
		if ($result->field_field_event_status[0]['raw']['value'] == 'cancelled') {
			$result->field_field_event_status[0]['rendered'] = "<span class='cancelled'>geannuleerd</span>";
		} else {
			$result->field_field_event_status[0]['rendered'] = "";
		}
	} else if (isset($result->field_field_canceled)) {
		// TODO: has to be removed when status field becomes the only one
		if ($result->field_field_canceled[0]['raw']['value'] == 1) {
			$result->field_field_canceled[0]['rendered'] = "<span class='cancelled'>geannuleerd</span>";
		} else {
			$result->field_field_canceled[0]['rendered'] = "";
		}
	}
}

/*
 * Adds class to colorize events in calendar based on int/ext field value
 */
function iisg_preprocess_calendar_item(&$vars) {
	$view = $vars['view'];
	if ($view->name == "calendar") {
		$item = $vars["item"];
		$vars["item"]->class = "item " . $item->row->field_field_internal_external[0]['raw']['value'];
	}
}

/*
 * Changes date titles of calendar views
 */
function iisg_date_nav_title($params) {
	$granularity = $params['granularity'];
	$view = $params['view'];
	$date_info = $view->date_info;
	$link = !empty($params['link']) ? $params['link'] : FALSE;
	$format = !empty($params['format']) ? $params['format'] : NULL;
	switch ($granularity) {
		case 'year':
			$title = $date_info->year;
			$date_arg = $date_info->year;
			break;
		case 'month':
			$format = !empty($format) ? $format : (empty($date_info->mini) ? 'F Y' : 'F Y');
			$title = date_format_date($date_info->min_date, 'custom', $format);
			$date_arg = $date_info->year . '-' . date_pad($date_info->month);
			break;
		case 'day':
			$format = !empty($format) ? $format : (empty($date_info->mini) ? 'l j F Y' : 'l j F Y');
			$title = date_format_date($date_info->min_date, 'custom', $format);
			$date_arg = $date_info->year . '-' . date_pad($date_info->month) . '-' . date_pad($date_info->day);
			break;
		case 'week':
			$format = !empty($format) ? $format : (empty($date_info->mini) ? 'W, Y' : 'W, Y');
			$title = t('Week @date', array('@date' => date_format_date($date_info->min_date, 'custom', $format)));
			$date_arg = $date_info->year . '-W' . date_pad($date_info->week);
			break;
	}
	if (!empty($date_info->mini) || $link) {
		$attributes = array('title' => t('View full page month'));
		$url = date_pager_url($view, $granularity, $date_arg, TRUE);
		return l($title, $url, array('attributes' => $attributes));
	} else {
		return $title;
	}
}
// END TEMPLATE FUNCTIONS FOR IISH AGENDA

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
		} else {
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

//      return '<div class="breadcrumb">' . $heading . implode($breadcrumb_separator, $breadcrumb) . $trailing_separator . $title . '</div>';
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
		$output = '<span class="read-more">' . drupal_render($item) . '</span>';
	}

	return $output;
}

// TODO print key instead of value (or value instead of #markup)
function iisg_field__field_slideshow_image_size($variables) {
	// Render the items.
	foreach ($variables['items'] as $delta => $item) {
		$output = 'img' . drupal_render($item); // A class should start with alpha char.
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

			$text_first = theme('image', array('path' => path_to_theme() . '/images/first.png', 'alt' => t('First')));
			$text_previous = theme('image', array('path' => path_to_theme() . '/images/previous.png', 'alt' => t('Previous')));
			$text_next = theme('image', array('path' => path_to_theme() . '/images/next.png', 'alt' => t('Next')));
			$text_last = theme('image', array('path' => path_to_theme() . '/images/last.png', 'alt' => t('Last')));

			$titles = array(
				$text_first => t('Go to first page'),
				$text_previous => t('Go to previous page'),
				$text_next => t('Go to next page'),
				$text_last => t('Go to last page'),
			);
		}
		if (isset($titles[$text])) {
			$attributes['title'] = $titles[$text];
		} elseif (is_numeric($text)) {
			$attributes['title'] = t('Go to page @number', array('@number' => $text));
		}
	}

	return l($text, $_GET['q'], array('html' => TRUE, 'attributes' => $attributes, 'query' => $query));
}

/*
 * Use images for first, last, previous, next.
 */
function iisg_pager($variables) {
	global $pager_page_array, $pager_total;

	$tags = $variables['tags'];
	$element = $variables['element'];
	$parameters = $variables['parameters'];
	$quantity = $variables['quantity'];

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

	$text_first = theme('image', array('path' => path_to_theme() . '/images/first.png', 'alt' => t('First')));
	$text_previous = theme('image', array('path' => path_to_theme() . '/images/previous.png', 'alt' => t('Previous')));
	$text_next = theme('image', array('path' => path_to_theme() . '/images/next.png', 'alt' => t('Next')));
	$text_last = theme('image', array('path' => path_to_theme() . '/images/last.png', 'alt' => t('Last')));

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
						'data' => '<span>' . $i . '</span>',
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
	global $pager_page_array;

	$text = $variables['text'];
	$element = $variables['element'];
	$parameters = $variables['parameters'];

	// If we are anywhere but the first page
	if ($pager_page_array[$element] > 0) {
		$output = theme('pager_link', array('text' => $text, 'page_new' => pager_load_array(0, $element, $pager_page_array), 'element' => $element, 'parameters' => $parameters));
	} else {
		$output = theme('image', array('path' => path_to_theme() . '/images/first-nolink.png', 'alt' => t('Last')));
	}

	return $output;
}

function iisg_pager_previous($variables) {
	global $pager_page_array;

	$text = $variables['text'];
	$element = $variables['element'];
	$interval = $variables['interval'];
	$parameters = $variables['parameters'];

	// If we are anywhere but the first page
	if ($pager_page_array[$element] > 0) {
		$page_new = pager_load_array($pager_page_array[$element] - $interval, $element, $pager_page_array);

		// If the previous page is the first page, mark the link as such.
		if ($page_new[$element] == 0) {
			$output = theme('pager_first', array('text' => $text, 'element' => $element, 'parameters' => $parameters));
		} // The previous page is not the first page.
		else {
			$output = theme('pager_link', array('text' => $text, 'page_new' => $page_new, 'element' => $element, 'parameters' => $parameters));
		}
	} else {
		$output = theme('image', array('path' => path_to_theme() . '/images/previous-nolink.png', 'alt' => t('Last')));
	}

	return $output;
}

function iisg_pager_next($variables) {
	global $pager_page_array, $pager_total;

	$text = $variables['text'];
	$element = $variables['element'];
	$interval = $variables['interval'];
	$parameters = $variables['parameters'];

	// If we are anywhere but the last page
	if ($pager_page_array[$element] < ($pager_total[$element] - 1)) {
		$page_new = pager_load_array($pager_page_array[$element] + $interval, $element, $pager_page_array);
		// If the next page is the last page, mark the link as such.
		if ($page_new[$element] == ($pager_total[$element] - 1)) {
			$output = theme('pager_last', array('text' => $text, 'element' => $element, 'parameters' => $parameters));
		} // The next page is not the last page.
		else {
			$output = theme('pager_link', array('text' => $text, 'page_new' => $page_new, 'element' => $element, 'parameters' => $parameters));
		}
	} else {
		$output = theme('image', array('path' => path_to_theme() . '/images/next-nolink.png', 'alt' => t('Last')));
	}

	return $output;
}

function iisg_pager_last($variables) {
	global $pager_page_array, $pager_total;

	$text = $variables['text'];
	$element = $variables['element'];
	$parameters = $variables['parameters'];

	// If we are anywhere but the last page
	if ($pager_page_array[$element] < ($pager_total[$element] - 1)) {
		$output = theme('pager_link', array('text' => $text, 'page_new' => pager_load_array($pager_total[$element] - 1, $element, $pager_page_array), 'element' => $element, 'parameters' => $parameters));
	} else {
		$output = theme('image', array('path' => path_to_theme() . '/images/last-nolink.png', 'alt' => t('Last')));
	}

	return $output;
}

/*
 * Make "Profile" translatable.
 */
function iisg_preprocess_user_profile_category(&$variables) {
	$variables['title'] = t($variables['title']);
}
