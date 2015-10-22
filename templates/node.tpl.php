<article<?php print $attributes; ?>>
  <?php print $user_picture; ?>
  
  <?php if (!$page && $title): ?>
  <header>
    <?php print render($title_prefix); ?>
    <h2<?php print $title_attributes; ?>><a href="<?php print $node_url ?>" title="<?php print $title ?>"><?php print $title ?></a></h2>
    <?php print render($title_suffix); ?>
  </header>
  <?php endif; ?>
  
  <?php if ($display_submitted): ?>
  <footer class="submitted"> <span class="submitted">
      <?php
	  	// EdR: Changed date format and order, should be moved to preprocess?
        $custom_date = format_date($node->created, 'custom', 'd F Y');
        print t('!username -- !datetime', array('!username' => $name, '!datetime' => $custom_date));
      ?>
    </span></footer>
  <?php endif; ?>  
  
  <div<?php print $content_attributes; ?>>
    <?php
      // We hide the comments and links now so that we can render them later.
      hide($content['comments']);
      hide($content['links']);
      print render($content);
    ?>
  </div>
  
  <div class="clearfix">
    <?php if (!empty($content['links'])): ?>
      <nav class="links node-links clearfix"><?php print render($content['links']); ?></nav>
    <?php endif; ?>

    <?php print render($content['comments']); ?>
  </div>
</article>