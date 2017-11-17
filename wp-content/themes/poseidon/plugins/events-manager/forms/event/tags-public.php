<?php
/*
 * This file is called by templates/forms/location-editor.php to display fields for uploading images on your event form on your website. This does not affect the admin featured image section.
* You can override this file by copying it to /wp-content/themes/yourtheme/plugins/events-manager/forms/event/ and editing it there.
*/
global $EM_Event;
/* @var $EM_Event EM_Event */ 
$tags = EM_Tags::get(array('orderby'=>'name','hide_empty'=>0));
echo '<pre>';
print_r($tags);
echo '</pre>';
// Get post ID
$theId = get_the_ID();
?>
<?php if( $theId == 255 ): ?>
	<?php if( count($tags) > 0 ): ?>
		<div class="event-tags">
			<!-- START Tags -->
			<label for="event_tags[]"><?php _e ( 'Tags:', 'events-manager'); ?></label>
			<select name="event_tags[]" multiple size="10">
			<?php
			$selected = '13';
			// $walker = new EM_Walker_CategoryMultiselect();
			$args_em = array('selected' => $selected);
			echo walk_category_dropdown_tree($tags, 0, $args_em);
			?></select>
			<!-- END Tags -->
		</div>
	<?php endif; ?>
<?php endif; ?>