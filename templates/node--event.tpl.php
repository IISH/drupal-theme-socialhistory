<?php  if(isset($variables['cancelled'])): ?>
    <div class="cancelled"><?php  print render($variables['cancelled']); ?></div>
<?php endif; ?>

<div style="clear: both;"></div>
<?php print $user_picture; ?>
<?php print $variables["reservation_number"]; ?>

<article<?php print $attributes; ?>>

    <?php print render($title_prefix); ?>
    <?php if (!$page && $title): ?>
        <header>
            <h2<?php print $title_attributes; ?>>
                <a href="<?php print $node_url ?>" title="<?php print $title ?>"><?php print $title ?></a>
            </h2>
        </header>

    <?php endif; ?>
    <?php print render($title_suffix); ?>
    <?php if ($display_submitted): ?>
        <footer class="submitted">Aangemaakt door <?php print $name; ?> op <?php print $create_date; ?></footer>
    <?php endif; ?>

    <?php if($clone): ?>
        <a class='clone-link' href="/clone/<?php print $node->nid;?>">Clone evenement</a>
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