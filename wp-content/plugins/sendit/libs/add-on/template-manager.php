<?php
/*
ex plugin: Sendit Pro  Newsletter Template Manager
Author URI: http://www.giuseppesurace.com
*/

add_action( 'init', 'register_cpt_sendit_template' );

function register_cpt_sendit_template() {

    $labels = array( 
        'name' => _x( 'Newsletter Template', 'sendit_template' ),
        'singular_name' => _x( 'Template', 'sendit_template' ),
        'add_new' => _x( 'Add New', 'sendit_subscriber' ),
        'add_new_item' => _x( 'Add New Template', 'sendit_template' ),
        'edit_item' => _x( 'Edit Template', 'sendit_template' ),
        'new_item' => _x( 'New Template', 'sendit_template' ),
        'view_item' => _x( 'View Template', 'sendit_template' ),
        'search_items' => _x( 'Search Template', 'sendit_template' ),
        'not_found' => _x( 'No Templates found', 'sendit_template' ),
        'not_found_in_trash' => _x( 'No Templates found in Trash', 'sendit_template' ),
        'parent_item_colon' => _x( 'Parent Tenplate:', 'sendit_template' ),
        'menu_name' => _x( 'Newsletter Templates', 'sendit_template' ),
    );

    $args = array( 
        'labels' => $labels,
        'hierarchical' => false,
        'supports' => array( 'title','thumbnail'),
        //'supports' => array( 'title','custom-fields'),

        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_position' => 20,
        
        'show_in_nav_menus' => true,
        'publicly_queryable' => true,
        'exclude_from_search' => false,
        'has_archive' => true,
        'query_var' => true,
        'can_export' => true,
        'rewrite' => true,
        'capability_type' => 'post',
    	'register_meta_box_cb' => 'sendit_add_custom_box'
    	);
    	
    


    register_post_type( 'sendit_template', $args );
}






function move_to_template()
{

	$migrations=new migrations();
	$liste = $migrations->GetLists();
	foreach($liste as $lista):

	
	$term = term_exists($lista->nomelista, 'mailing_lists');
	
	/* TODO: verificare se il term esiste... */
	
	if ($term !== 0 && $term !== null) {
		echo $lista->nomelista ."Template already migrated!";
	}
	
	
	else {

	

				//inserisco il post del template
				$post = array(
					'post_status' => 'publish', 
					'post_type' => 'sendit_template',
					'post_author' => $user_ID,
					'ping_status' => get_option('default_ping_status'), 
					'post_parent' => 0,
					'post_content'=>$dummy_content,
					'post_name' => 'Template imported from '.sanitize_title($lista->nomelista),
					'menu_order' => 0,
					'to_ping' =>  '',
					'pinged' => '',
					'post_title' => 'Template imported from '.sanitize_title($lista->nomelista),
					'import_id' => 0
					//'tax_input' => array( 'mailing_lists' => $lista->nomelista)
					);
				$new_template_id = wp_insert_post($post, $wp_error );				
					
				update_post_meta($new_template_id, 'newsletter_css', '');
				update_post_meta($new_template_id, 'headerhtml', $lista->header);
				update_post_meta($new_template_id, 'footerhtml', $lista->footer);
				update_post_meta($new_template_id, 'old_list_id', $lista->id_lista);



	} //end if term exists
	   
	endforeach;
	


}
	

function sendit_pro_template_screen()  { 

	if($_POST):
		move_to_template(); ?>
		<div id="message" class="updated fade"><p><strong><?php __('Templates saved!', 'sendit'); ?></strong></p></div>
        
<?php endif;

?>
<div class="wrap">

	<h2>Sendit Pro Template Manager</h2>
	<ul>
		<li>Manage your newsletter template managed as custom post type (including featuring images) used as newsletter's header logo image</li>
		<li>Upload images to your template header</li>
		<li>Newsletter content Images embedding</li>
		<li>Preview your newsletter with template</li>
	</ul>


		<h3>By clicking this button you will migrate your old templates from your mailing list to new custom post type and easily manage from here</h3>	
		<form action="" method="post">
		<input type="hidden" value="1" name="action" />
		<input type="submit" class="button primary" value="Move template from your mailing list" />
		</form>		
		<hr />
	
<?php Sendit_templates(); ?>	

	</div>


<?php } 







?>