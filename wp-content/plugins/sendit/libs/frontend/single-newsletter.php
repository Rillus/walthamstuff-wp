<?php
/* The Newsletter template
It will recognize automatically presence of Sendit Pro Template Manager
and switch header footer css to template post type, if not get info (header / footer / css ) from the list_details
*/

$sendit = new Actions();


if (have_posts()) : 
	while (have_posts()) : the_post(); 
	$template_id= get_post_meta($post->ID, 'template_id', TRUE); 

		global $post; 
		//default free plugin value
		$sendit_list = get_post_meta($post->ID, 'sendit_list',true);	
		$list_detail = $sendit->GetListDetail($sendit_list);

		$header=$list_detail->header;
		$footer=$list_detail->footer;
		$css='';


	if(function_exists('sendit_pro_template_screen')):
	

		$template_id= get_post_meta($post->ID, 'template_id', TRUE);    		
		$css=get_post_meta($template_id, 'newsletter_css', TRUE); 
		
		
	

		$header=get_post_meta($template_id, 'headerhtml', TRUE);
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
			
			if ( has_post_thumbnail($template_id) ) {
				$header_image=get_the_post_thumbnail($template_id);
				}
			else {
				$header_image='<img alt="" src="http://placehold.it/300x50/" />';
			}
			
			$header=str_replace('[logo]',$header_image,$header);
			$header=str_replace('[homeurl]',get_bloginfo('siteurl'),$header);
		
		$footer=get_post_meta($template_id, 'footerhtml', TRUE);

	endif; //plugin is active

		//$newsletter_content=$header.get_the_content().$footer;

		$newsletter_content=get_the_content();
		
		//CSS get template id comment tag parse and extract css....
		
		$get_template_id=getStylesheet($newsletter_content);
		
		$css_id=$get_template_id[1][0];

		$css=get_post_meta($css_id,'newsletter_css', true);
		


		//verify if inliner is installed
		if(function_exists('inline_newsletter')):
			$newsletter_content=inline_newsletter($css,$newsletter_content);
		endif;
		//verify if analytics is installed

		if(function_exists('AppendCampaignToString')):		
			echo AppendCampaignToString($newsletter_content);
		endif;		
		
		
	endwhile;

else : ?>
     <!-- Stuff to do if there are no posts-->
	<h2>No Newsletter</h2>
<?php endif; 
wp_footer();

?>