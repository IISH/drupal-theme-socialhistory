<?php
/**
 * @file views-view-fields.tpl.php
 * Default simple view template to all the fields as a row.
 *
 * - $view: The view in use.
 * - $fields: an array of $field objects. Each one contains:
 *   - $field->content: The output of the field.
 *   - $field->raw: The raw data for the field, if it exists. This is NOT output safe.
 *   - $field->class: The safe class id to use.
 *   - $field->handler: The Views field handler object controlling this field. Do not use
 *     var_export to dump this object, as it can't handle the recursion.
 *   - $field->inline: Whether or not the field should be inline.
 *   - $field->inline_html: either div or span based on the above flag.
 *   - $field->wrapper_prefix: A complete wrapper containing the inline_html to use.
 *   - $field->wrapper_suffix: The closing tag for the wrapper.
 *   - $field->separator: an optional separator that may appear before a field.
 *   - $field->label: The wrap label text to use.
 *   - $field->label_html: The full HTML of the label to use including
 *     configured element type.
 * - $row: The raw result object from the query, with all data it fetched.
 *
 * @ingroup views_templates
 */
?>
<?php 
  $color = $fields['field_color']->content;
  $img = 'img'. $fields['field_slideshow_image_size']->content;
  $link = FALSE;
  // A node reference field has no way to only display the link in a view,
  // so this is a work-around:
  if (preg_match('#(<a href=".+">)#', $fields['field_slideshow_link']->content, $matches)) {
    $link = $matches[0];
  }
?>

<div class="row-slideshow">
  <div class="<?php print $color .' '. $img; ?>">
    <div class="slideshow-img">
      <?php if ($link) : ?>
        <?php print $link; ?>
        <?php print $fields['field_image']->content; ?>
        <img class="slideshow-overlay" src="<?php print base_path() . $directory; ?>/images/hook-<?php print $color; ?>.png">
        <?php print '</a>'; ?>
      <?php else : ?>
        <?php print $fields['field_image']->content; ?>
        <img class="slideshow-overlay" src="<?php print base_path() . $directory; ?>/images/hook-<?php print $color; ?>.png">
      <?php endif; ?>
    </div>
    <div class="content">
      <h2><?php print $fields['title']->content; ?></h2>
      <div class="field-name-body"><?php print $fields['body']->content; ?></div>

    <?php if ($link) : ?>
      <span class="read-more">
        <?php print $link .'<span class="plus">+</span> '. t('more') .'</a>'; ?>
      </span>
    <?php endif; ?>

    </div>
  </div>
</div>

