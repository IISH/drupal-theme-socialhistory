<article<?php print $attributes; ?>>

		<div class="<?php print render($content['field_color']); ?> <?php print render($content['field_slideshow_image_size']); ?>">

			<div class="slideshow-img">
				<?php print render($content['field_image']); ?>
				<img class="slideshow-overlay" src="<?php print base_path(); ?>sites/all/themes/iisg/images/hook-<?php print render($content['field_color']); ?>.png">
			</div>
			<div<?php print $content_attributes; ?>>
		    <h2><?php print $title ?></h2>
				<?php
				  hide($content['comments']);
				  hide($content['links']);
				  print render($content);
					// print render($content['field_slideshow_link']);
				?>
			</div>
		</div>  
</article>
