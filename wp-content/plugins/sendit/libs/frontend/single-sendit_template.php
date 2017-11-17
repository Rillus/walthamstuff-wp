<?php
/* The Newsletter template
It will recognize automatically presence of Sendit Pro Template Manager
and switch header footer css to template post type, if not get info (header / footer / css ) from the list_details
*/

	$template_id= get_post_meta($post->ID, 'template_id', TRUE); 
	$css=get_post_meta($post->ID, 'newsletter_css', TRUE); 
	?>
<?php if (have_posts()) : 
	while (have_posts()) : the_post(); global $post; 
		$template_id= get_post_meta($post->ID, 'template_id', TRUE);    		
     		$header=get_post_meta($post->ID, 'headerhtml', TRUE);
     		$box_styles='.info, .success, .warning, .error, .validation {
    border: 1px solid;
    margin: 10px 0px;
    padding:15px 10px 15px 50px;
    background-repeat: no-repeat;
    background-position: 10px center;
}
.info {
    color: #00529B;
    background-color: #BDE5F8;
}
.success {
    color: #4F8A10;
    background-color: #DFF2BF;
}
.warning {
    color: #9F6000;
    background-color: #FEEFB3;
}
.error {
    color: #D8000C;
    background-color: #FFBABA;
}';
     		
			$header=str_replace('[style]','<style>'.$css.$box_styles.'</style>',$header);
			
			if ( has_post_thumbnail() ) {
				$header_image=get_the_post_thumbnail();
				}
			else {
				$header_image='<img alt="" src="http://placehold.it/600x250/" />';
			}
			
			$header=str_replace('[logo]',$header_image,$header);
			$header=str_replace('[homeurl]',get_bloginfo('siteurl'),$header);

			
			
			$footer=get_post_meta($post->ID, 'footerhtml', TRUE);

	  		$dummy_content='<h2>Heading text for your newsletter</h2>
	  		<h3><a href="#">Article 1</a></h3>
     		
     		
     		<p><img alt="" src="http://placehold.it/200x200/" title="test" class="alignleft size-thumbnail wp-image-15">Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.</p><div class="sendit_readmore"><a class="sendit_more_button">Read More</a></div><hr />
     		
     		
     			  		<h3><a href="#">Article 2</a></h3>

     		     		<p><img alt="" src="http://placehold.it/200x200/" title="test" class="alignleft size-thumbnail wp-image-15">Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.</p><div class="sendit_readmore"><a class="sendit_more_button">Read More</a></div><hr />';
     		
     		
     		
 		if(function_exists('inline_newsletter')):    ?>		
     		<div class="info">This preview is generated and parsed by Sendit Pro Inliner tool with all styles converted into inline.</div>
     	<?php else: ?>
     		<div class="warning"><h4>Buy Sendit Pro auto inliner tool</h4>
     		<p>Sendit Pro auto inliner tool will parse the HTML and convert all styles into inline styles.</p>
     		</div>     	
     	<?php endif; ?>	
		<?php 
		$template_content=$header.$dummy_content.$footer;
		
		//verify if inliner is installed
		if(function_exists('inline_newsletter')):
			$template_content=inline_newsletter($css,$template_content);
		endif;
		
		echo $template_content;
		
		
     endwhile; 
    endif;
    wp_footer();
?>