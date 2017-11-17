<?php 
/**/

function sendit_custom_post_type_init() 
{
	/***************************************************
	+++ custom post type: newsletter extract from Sendit Pro
	***************************************************/

  $labels = array(
    'name' => _x('Newsletters', 'post type general name'),
    'singular_name' => _x('Newsletter', 'post type singular name'),
    'add_new' => _x('Add New', 'newsletter'),
    'add_new_item' => __('Add New newsletter'),
    'edit_item' => __('Edit newsletter'),
    'new_item' => __('New newsletter'),
    'view_item' => __('View newsletter'),
    'search_items' => __('Search newsletter'),
    'not_found' =>  __('No newsletters found'),
    'not_found_in_trash' => __('No newsletters found in Trash'), 
    'parent_item_colon' => ''
  );
  $args = array(
    'labels' => $labels,
    'public' => true,
    'publicly_queryable' => true,
    'show_ui' => true, 
    'query_var' => true,
    'rewrite' => false,
    'capability_type' => 'post',
    'hierarchical' => false,
    'menu_position' => null,
    'supports' => array('title','editor','thumbnail'),
	'rewrite' => array(
    'slug' => 'newsletter',
    'with_front' => FALSE

  ),
	'register_meta_box_cb' => 'sendit_add_custom_box'


  ); 
  register_post_type('newsletter',$args);

}


add_action('admin_init', 'disable_revisions');
function disable_revisions(){
    remove_post_type_support('newsletter', 'revisions');
}

add_action('admin_print_scripts', 'disable_autosave');
function disable_autosave(){
    global $post;
    if(get_post_type($post->ID) === 'newsletter'){
        wp_deregister_script('autosave');
    }
}



add_filter('post_updated_messages', 'newsletter_updated_messages');
function newsletter_updated_messages( $messages ) {
	global $_POST;

	if($_POST['send_now']==1):
		$msgok=__('Newsletter Sent Now','sendit');
	elseif($_POST['send_now']==2):
		$msgok=__('Newsletter Scheduled it will be sent automatically','sendit');
	else:
		$msgok=__('Newsletter Saved succesfully','sendit');		
	endif;

  $messages['newsletter'] = array(
    0 => '', // Unused. Messages start at index 1.
    1 => $msgok,
    2 => __('Custom field updated.'),
    3 => __('Custom field deleted.'),
    4 => __('Newsletter updated.'),
    /* translators: %s: date and time of the revision */
    5 => isset($_GET['revision']) ? sprintf( __('Newsletter restored to revision from %s'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
    6 => $msgok,
    //6 => sprintf( __('Newsletter published. <a href="%s">View newsletter</a>'), esc_url( get_permalink($post_ID) ) ),
    7 => __('Newsletter saved.'),
    8 => sprintf( __('Newsletter submitted. <a target="_blank" href="%s">Preview newsletter</a>'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
    9 => sprintf( __('Newsletter scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview newsletter</a>'),
      // translators: Publish box date format, see http://php.net/date
      date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink($post_ID) ) ),
    10 => sprintf( __('Newsletter draft updated. <a target="_blank" href="%s">Preview newsletter</a>'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
  );

  return $messages;
}

add_filter( 'gettext', 'sendit_change_publish_button', 10, 2 );

function sendit_change_publish_button( $translation, $text ) {
if ( 'newsletter' == get_post_type())
if ( $text == 'Publish' || $text == 'Update')
    return 'Send Newsletter';

return $translation;
}




//display contextual help for Newsletters
add_action( 'contextual_help', 'add_help_text', 10, 3 );

function add_help_text($contextual_help, $screen_id, $screen) { 
$contextual_help =  ''; //var_dump($screen); // use this to help determine $screen->id
  if ('newsletter' == $screen->id ) {
    $contextual_help =
      '<p>' . __('Very important notices for a better use:','sendit') . '</p>' .
      '<ul>' .
      '<li>' . __('Insert your favorite content to send using the editor exactly in the same way you edit post, remember this content will be sent so be careful.','sendit') . '</li>' .
      '<li>' . __('Specify the mailing list from the radio men&ugrave; at the bottom of edit','sendit') . '</li>' .
      '</ul>' .
      '<p>' . __('If you want to schedule immediatly the newsletter check YES:','sendit') . '</p>' .
      '<ul>' .
      '<li>' . __('Under the Publish module, click on the Edit link next to Publish.','sendit') . '</li>' .
      '<li>' . __('Newsletter will be scheduled to be sent with your favorite settings.','sendit') . '</li>' .
      '</ul>' .
      '<p><strong>' . __('For more information:') . '</strong></p>' .
      '<p>' . __('<a href="http://codex.wordpress.org/Posts_Edit_SubPanel" target="_blank">Edit Posts Documentation</a>','sendit') . '</p>' .
      '<p>' . __('<a href="http://wordpress.org/support/" target="_blank">Support Forums</a>','sendit') . '</p>' ;
  } elseif ( 'edit-newsletter' == $screen->id ) {
    $contextual_help = 
      '<p>' . __('This is the help screen displaying the table of Newsletter system.','sendit') . '</p>' ;
  }
  return $contextual_help;
}









function send_newsletter($post_ID)
{
	$sendit = new Actions();
	$article = get_post($post_ID);
	$send_now = get_post_meta($post_ID, 'send_now',true);
	$sendit_list = get_post_meta($post_ID, 'sendit_list',true);	
	$table_liste =  SENDIT_LIST_TABLE;
	$list_detail = $sendit->GetListDetail($sendit_list);
	$subscribers = $sendit->GetSubscribers($sendit_list); //only confirmed
	$css='';
	/*+++++++++++++++++++ TEMPLATE pro EMAIL +++++++++++++++++++++++++++++++++++++++++*/
	
	//to do: templatizer, if exixts get the template from template_id
	
	$header=$list_detail->header;
	$footer=$list_detail->footer;
	$css='';
	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	if(is_plugin_active('sendit-pro-template-manager/sendit-pro-template-manager.php')):
		//custom post type template	
		$template_id=get_post_meta($post_ID,'template_id', true);
		$template= get_post($template_id);
		$title = $newsletter->post_title;
		//echo 'template id '.$template_id;
		


		
		$header=get_post_meta($template_id,'headerhtml', true);
		$header=str_replace('[style]','<style>'.$css.'</style>',$header);
			if ( has_post_thumbnail($template_id) ) {
				$header_image=get_the_post_thumbnail($template_id);
				}
			else {
				$header_image='<img alt="" src="http://placehold.it/300x50/" />';
			}
			
			$header=str_replace('[logo]',$header_image,$header);
			$header=str_replace('[homeurl]',get_bloginfo('siteurl'),$header);


		$footer=get_post_meta($template_id,'footerhtml', true);	
	
		$content = apply_filters('the_content',$newsletter->post_content);
	endif;
	
	
	
	
	
	$email_from=$list_detail->email_lista;
	
	/*+++++++++++++++++++ HEADERS EMAIL +++++++++++++++++++++++++++++++++++++++++*/
	$email=$email_from;
	$headers= "MIME-Version: 1.0\n" .
	"From: ".$email." <".$email.">\n" .
	"Content-Type: text/html; charset=\"" .
	get_option('blog_charset') . "\"\n";
	/*+++++++++++++++++++ CONTENT EMAIL +++++++++++++++++++++++++++++++++++++++++*/
	$title = $article->post_title;
	
	//$content = apply_filters('the_content',$article->post_content);
	$content = apply_filters('the_content',$article->post_content);

	//$newsletter_content=$header.$content.$footer;
	//new 2.1.2 content is already with footer and header
	$newsletter_content=$content;
	
	
	
	//CSS get template id comment tag parse and extract css.... v 2.2.2
		
	$get_template_id=getStylesheet($newsletter_content);
		
	$css_id=$get_template_id[1][0];

	$css=get_post_meta($css_id,'newsletter_css', true);
	
	
	
	$readonline = get_permalink($post_ID);

	if($send_now==1):
		foreach($subscribers as $subscriber):
			if(get_option('sendit_unsubscribe_link')=='yes'):
			
				//aggiungo messaggio con il link di cancelazione che cicla il magic_string..
				$delete_link="
				<center>
	 			-------------------------------------------------------------------------------
				<p>".__('To unsubscribe, please click on the link below', 'sendit')."<br />
				<a href=\"".get_bloginfo('siteurl').'/'."?action=unsubscribe&c=".$subscriber->magic_string."\">".__('Unsubscribe now', 'sendit')."</a></p>
				</center>";
			else:
				$delete_link='';
			endif;
			//send the newsletter!		
			
			//verify if inliner is installed
			if(is_plugin_active('sendit-css-inliner/sendit-pro-css-inliner.php')):
				$newsletter_content=inline_newsletter($css,$newsletter_content);
				$response = preg_replace('/(&Acirc;|&nbsp;)+/i', ' ', $response);
			endif;
			
			if(is_plugin_active('sendit-pro-analytics-campaign/sendit-pro-analytics-campaign.php')):		
				$newsletter_content=AppendCampaignToString($newsletter_content);
			endif;		
		
			
			wp_mail($subscriber->email, $title ,$newsletter_content.$delete_link, $headers, $attachments);		
		endforeach;
		//set to 5 status : sent with classic plugin
		update_post_meta($post_ID, 'send_now', '5');	
	endif;
}


/* Premium Plugin screenshots */

function sendit_woocommerce_screen()
{ ?>
	<div class="wrap">

	<h2><?php echo __('To import your Woocommerce customer into Sendit you need to buy Sendit pro Woocommerce importer','sendit');?></h2>
		<p><?php echo __('With Sendit pro Woocommerce importer (available now for only 5 euros) you will be able to import your Woocommerce customers and orders and build mailing lists. Sendit can also send products post_type with thumbnails!','sendit'); ?></p>
			<div class="sendit_box_woocommerce sendit_box_menu"><h2><?php __('Woocommerce user?', 'sendit'); ?></h2>
		  	<a href="http://senditplugin.com/plugin-shop/wordpress-plugin/sendit-pro-csv-list-exporter" class="button-primary"><?php echo __('Import your customer into Sendit', 'sendit'); ?></a>
		  </div>	
	</div>
<?php }


function export_subscribers_screen()
{ ?>
	<div class="wrap">

	<h2><?php echo __('To export Sendit mailing list you need to buy Sendit pro exporter','sendit');?></h2>
		<p><?php echo __('With Sendit pro export tool (available now for only 5 euros) you will be able to export and reimport as CSV files all your Sendit subscribers'); ?></p>
		<a class="button primary" href="http://senditplugin.com/plugin-shop/wordpress-plugin/sendit-pro-csv-list-exporter/"><?php echo __('Buy this plugin Now for 5 euros', 'Sendit'); ?></a>
	
	</div>
<?php }


function template_manager_screen()
{ ?>
	<div class="wrap">

	<h2>Give your newsletter an incredible and unique design with Sendit Pro Template manager!</h2>
	<ul>
		<li>Manage your newsletter template managed as custom post type (including featuring images)</li>
		<li>Upload images to your template header</li>
		<li>Preview your newsletter</li>
	</ul>
	<hr />
			<p><?php echo __('To use this feature you need to buy the new Sendit pro Template manager. Easily managament of templates within 5 included free templates, advanced customization and custom post type integration. Try it for only 10 &euro;','sendit');?></p>
		<a class="button primary" href="http://senditplugin.com/plugin-shop/wordpress-plugin/sendit-pro-csv-list-exporter/"><?php echo __('Buy Now for 5 &euro;', 'Sendit'); ?></a>
		
		<hr />
	
<?php if(function_exists(Sendit_templates)) Sendit_templates(); ?>		
		

	

	
	</div>
<?php }

function sendit_morefields_screen()
{ ?>
	<div class="wrap">

	<h2><?php echo __('To add and manage more fields to your subscription form you need to buy Sendit More Fields');?></h2>
		<p><?php echo __('With Sendit More Fields tool (available now for only 5 euros) you will be able to create manage and add additional fields and store as serialized data to your subscriptions. Also you can use to personalize your newsletter with something like dear {Name}'); ?></p>
		<h4><?php echo __('This video show you how much easy is to add fields to your subscription form with Sendit More Fields','sendit'); ?></h4>
		<iframe src="http://player.vimeo.com/video/34833902?title=0&amp;byline=0&amp;portrait=0" width="601" height="338" frameborder="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>
		<h4>Take a look to Sendit Plugins shop</h4>
		<a class="button-primary sendit-actions" href="http://senditplugin.com/plugin-shop/wordpress-plugin/sendit-more-fields/">
		<?php echo __('Buy Now for 5 &euro;', 'Sendit'); ?></a>
	
	</div>
<?php }

/*
 * Function creates post duplicate as a draft and redirects then to the edit post screen
 */
function sendit_duplicate_post_as_draft(){
	global $wpdb;
	if (! ( isset( $_GET['post']) || isset( $_POST['post'])  || ( isset($_REQUEST['action']) && 'rd_duplicate_post_as_draft' == $_REQUEST['action'] ) ) ) {
		wp_die('No post to duplicate has been supplied!');
	}
 
	/*
	 * get the original post id
	 */
	$post_id = (isset($_GET['post']) ? $_GET['post'] : $_POST['post']);
	/*
	 * and all the original post data then
	 */
	$post = get_post( $post_id );
 
	/*
	 * if you don't want current user to be the new post author,
	 * then change next couple of lines to this: $new_post_author = $post->post_author;
	 */
	$current_user = wp_get_current_user();
	$new_post_author = $current_user->ID;
 
	/*
	 * if post data exists, create the post duplicate
	 */
	if (isset( $post ) && $post != null) {
 
		/*
		 * new post data array
		 */
		$args = array(
			'comment_status' => $post->comment_status,
			'ping_status'    => $post->ping_status,
			'post_author'    => $new_post_author,
			'post_content'   => $post->post_content,
			'post_excerpt'   => $post->post_excerpt,
			'post_name'      => $post->post_name,
			'post_parent'    => $post->post_parent,
			'post_password'  => $post->post_password,
			'post_status'    => 'draft',
			'post_title'     => $post->post_title,
			'post_type'      => $post->post_type,
			'to_ping'        => $post->to_ping,
			'menu_order'     => $post->menu_order
		);
 
		/*
		 * insert the post by wp_insert_post() function
		 */
		$new_post_id = wp_insert_post( $args );
 
		/*
		 * get all current post terms ad set them to the new post draft
		 */
		$taxonomies = get_object_taxonomies($post->post_type); // returns array of taxonomy names for post type, ex array("category", "post_tag");
		foreach ($taxonomies as $taxonomy) {
			$post_terms = wp_get_object_terms($post_id, $taxonomy, array('fields' => 'slugs'));
			wp_set_object_terms($new_post_id, $post_terms, $taxonomy, false);
		}
 
		/*
		 * duplicate all post meta apart from sendit custom post_meta (sendit_list, startnum, subscribers)
		 */
		$post_meta_infos = $wpdb->get_results("SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id=$post_id and meta_key!='send_now' and meta_key!='subscribers' and meta_key!='startnum' and meta_key!='sendit_list'");
		if (count($post_meta_infos)!=0) {
			$sql_query = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) ";
			foreach ($post_meta_infos as $meta_info) {
				$meta_key = $meta_info->meta_key;
				$meta_value = addslashes($meta_info->meta_value);
				$sql_query_sel[]= "SELECT $new_post_id, '$meta_key', '$meta_value'";
			}
			$sql_query.= implode(" UNION ALL ", $sql_query_sel);
			$wpdb->query($sql_query);
		}
 
 
		/*
		 * finally, redirect to the edit post screen for the new draft
		 */
		wp_redirect( admin_url( 'post.php?action=edit&post=' . $new_post_id ) );
		exit;
	} else {
		wp_die('Post creation failed, could not find original post: ' . $post_id);
	}
}
add_action( 'admin_action_sendit_duplicate_post_as_draft', 'sendit_duplicate_post_as_draft' );
 
/*
 * Add the duplicate link to action list for post_row_actions except post values for sendit useful for testing
 */
function sendit_duplicate_post_link( $actions, $post ) {
	if (current_user_can('edit_posts')) {
		if($post->post_type=='newsletter') {
		$actions['duplicate'] = '<a href="admin.php?action=sendit_duplicate_post_as_draft&amp;post=' . $post->ID . '" title="Duplicate this item" rel="permalink">Duplicate</a>';			
		}

	}
	return $actions;	
}
 
add_filter( 'post_row_actions', 'sendit_duplicate_post_link', 10, 2 );



add_filter("manage_edit-newsletter_columns", "senditfree_newsletter_columns");

function senditfree_newsletter_columns($columns)
{

	global $post;
	$columns = array(
		"cb" => "<input type=\"checkbox\" name=\"post[]\" value=\"".$post->ID."\" />",
		"title" => "Newsletter Title",
		"queued" => "queued",
		"subscribers" => "subscribers",
		"startnum" => "sent",
		"opened" => "opened",
		"next_send" => "Next Job",
		"list" => "Receiver list"				
	);
	return $columns;
}


// Add to admin_init function
add_action('manage_posts_custom_column', 'senditfree_manage_newsletter_columns', 10, 2);

function senditfree_manage_newsletter_columns($column_name, $id) {
	global $wpdb;
	$buymsg='<small>'.__('To schedule newsletter delivery you need Sendit Pro scheduler', 'sendit').'</small><br />';
	$buymsg.= '<a href="http://senditplugin.com/plugin-shop/sendit-pro/">Buy now</a>';
	switch ($column_name) {
	case 'id':
		echo $id;
	    break;

	case 'queued':
		if(!function_exists('Sendit_tracker_installation'))
		{
		/*
		Buy the extension
		*/
		echo $buymsg;
		} else {
				if(get_post_meta($id, 'send_now', TRUE)=='2'):
				   if(time()>wp_next_scheduled('sendit_event')) {
				   		//wp_clear_scheduled_hook( 'sendit_event' );
				   		//wp_schedule_event(time()+get_option('sendit_interval'), 'sendit_send_newsletter', 'sendit_event');
				   		//echo 'cron aggiornato';
				   }
					echo '<div class="jobrunning senditmessage"><p>'.__('Warning! newsletter is currently running the job','sendit').'</p></div>';
				elseif(get_post_meta($id, 'send_now', TRUE)=='4'):
					echo '<div class="jobdone senditmessage"><p>'.__('Newsletter Sent','sendit').'</p></div>';
			else:
		
			endif;
		}
	break;
		
	case 'list':
		echo 'List id: '. get_post_meta($id,'sendit_list',TRUE);
		if(!function_exists('Sendit_tracker_installation'))
		{
			/*
			Buy the extension
			*/
			//echo $buymsg;
		} 
		else
		{ 
			get_queued_newsletter();
		}
	
	break;
	
	case 'subscribers':
		echo get_post_meta($id,'subscribers',TRUE);
	break;

	case 'startnum':
		if(!function_exists('Sendit_tracker_installation'))
		{
		/*
		Buy the extension
		*/
			echo $buymsg;
		} 
		else
		{
			echo get_post_meta($id,'startnum',TRUE);
		}
				
	break;

	case 'opened':
		if(!function_exists('Sendit_tracker_installation'))
		{
			/*
			Buy the extension
			*/
			echo $buymsg;
		}
		else
		{	
			//status 5 inviate con invio normale
			if(get_post_meta($id,'send_now',TRUE)==5):
				echo '<small>'.__('Sent traditionally without tracker','sendit').'</small>';		
			elseif(get_post_meta($id,'send_now',TRUE)==4):
				$viewed = $wpdb->get_var("SELECT count(reader_ID) FROM ".TRACKING_TABLE." WHERE newsletter_ID = {$id}");
				$unique_visitors = $wpdb->get_results("SELECT DISTINCT(reader_ID) FROM ".TRACKING_TABLE." WHERE newsletter_ID = {$id}");		
			echo '<small>'.__('Opened:','sendit').' '.$viewed. ' '.__('times','sendit').'<br />by: '.count($unique_visitors).' readers</small>';
			
				
			

			endif;
		}
		
	break;	
	
		
	case 'next_send':
		if(!function_exists('Sendit_tracker_installation'))
		{
			/*
			Buy the extension
			*/
			echo $buymsg;
		}
		else
		{
			if(get_post_meta($id, 'send_now', TRUE)==2):
				echo strftime("%d/%m/%Y - %H:%M ",wp_next_scheduled('sendit_event'));
			endif;
		}
		
	break;



	
	default:
	break;
	} // end switch
}

	
	// This code is copied, from wp-includes/pluggable.php as at version 2.2.2
	function sendit_init_smtp($phpmailer) {


		
		// Set the mailer type as per config above, this overrides the already called isMail method
		if(get_option('sendit_smtp_host')!='') {
			$phpmailer->Mailer = 'smtp';			
			// If we're sending via SMTP, set the host
			$phpmailer->Host = get_option('sendit_smtp_host');
			// If we're using smtp auth, set the username & password SO WE USE AUTH
			if (get_option('sendit_smtp_username')!='') {
				//print_r($phpmailer);
				$phpmailer->SMTPAuth = TRUE;
				$phpmailer->SMTPSecure = get_option('sendit_smtp_ssl');
				$phpmailer->SMTPDebug  = get_option('sendit_smtp_debug'); 
				$phpmailer->Port = get_option('sendit_smtp_port'); 
				$phpmailer->Username = get_option('sendit_smtp_username');
				$phpmailer->Password = get_option('sendit_smtp_password');
			}
		}
		
		// You can add your own options here, see the phpmailer documentation for more info:
		// http://phpmailer.sourceforge.net/docs/
		
		// Stop adding options here.
		
	} // End of phpmailer_init_smtp() function definition
	



add_action('phpmailer_init','sendit_init_smtp');


function sendit_templates() {
// new 2.1.1 list me the templates available, to be dynamic

?>

		<div style="width:100%; display:block;clear:both;">
		<div style="float:left; margin:10px;"><h2>Zurb based (responsive)</h2>
			  	<a href="#">
			  	<img src="<?php echo WP_PLUGIN_URL ?>/sendit/images/sendit-template-basic.jpg" />
			  	</a>
		</div>

		<div style="float:left; margin:10px;"><h2>Helvetico Black style</h2>
			  	<a href="#">
			  	<img src="<?php echo WP_PLUGIN_URL ?>/sendit/images/sendit-template-helv.jpg" />
			  	</a>
		</div>

		<div style="float:left; margin:10px;"><h2>Sendit Light</h2>
			  	<a href="#">
			  	<img src="<?php echo WP_PLUGIN_URL ?>/sendit/images/sendit-template-light.jpg" />
			  	</a>
		</div>
		</div>	
<?php }



function getStylesheet($content) {
	
	preg_match_all("~\[template_id=(\d+)\]~i", $content, $matches);
	return $matches;
}

?>