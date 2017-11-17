<?php 


add_action('init', 'sendit_empty_check');
//remove all custom fields empty

add_action('mailing_lists_add_form_fields', 'sendit_list_metadata_add', 10, 1);

function sendit_empty_check() {

	$post_id=$_GET['post'];
	if($post_id):

	    //obtain custom field meta for this post
	     $custom_fields = get_post_custom($post_id);
	
	    foreach($custom_fields as $key=>$values):
	    //$values is an array of values associated with $key - even if there is only one value. 
	    //Filter to remove empty values.
	    //Be warned this will remove anything that casts as false, e.g. 0 or false 
	    //- if you don't want this, specify a callback.
	    //See php documentation on array_filter
	    $nonemptyvalues = array_filter($values);
	
	    //If the $nonemptyvalues doesn't match $values then we removed an empty value(s).
	    if($nonemptyvalues!=$values):
	         //delete key
	         delete_post_meta($post_id,$key);
	
	         //re-add key and insert only non-empty values
	         foreach($nonemptyvalues as $nonemptyvalue){
	             add_post_meta($post_id,$key,$nonemptyvalue);
	         }
	    endif;  
	endforeach;
	endif;


} 




/**
 * Add additional fields to the taxonomy add view
 * e.g. /wp-admin/edit-tags.php?taxonomy=category
 */

function extract_posts()
{
	/*
	Todo post type management
	*/
	$args=array('posts_per_page'  => 15,
    'offset'          => 0,
    'orderby'         => 'post_type',
    'order'           => 'DESC',
    'post_type'       => array('post','product'),
    'post_status'     => 'publish');
	$posts=get_posts($args);
	return $posts;
}

function extract_templates()
{
	$args = array(
   'numberposts' => 8,
   'orderby' => 'id',
   'post_type' => 'sendit_template',
   'post_status' => 'publish'
);
	$posts=get_posts($args);
	return $posts;
}



function sendit_add_custom_box() 
{
  if( function_exists( 'add_meta_box' ))
  {

	
	add_meta_box( 'help_html', __( 'Guideline', 'sendit' ),'sendit_help_box', 'newsletter', 'side','high' );
	


	//template choice from newsletter
	add_meta_box( 'template_choice', __( 'Select template for newsletter', 'sendit' ),'sendit_push_template', 'newsletter', 'side','high' );


	add_meta_box( 'action_html', __( 'Action', 'sendit' ),'sendit_custom_box', 'newsletter', 'side','high' );

	add_meta_box( 'template_html', __( 'Edit newsletter template', 'sendit' ),'sendit_html_box', 'sendit_template', 'advanced','default' );
	//content choice send element to editor
	add_meta_box( 'content_choice', __( 'Append content from existing posts', 'sendit' ),'sendit_content_box', 'newsletter', 'advanced','high' );
	//template choice from newsletter
	
	//add_meta_box( 'template_choice', __( 'Select template for newsletter', 'sendit' ),'sendit_template_select', 'newsletter', 'side','high' );


    
   } 
}


function sendit_action_box($post)
{
 ?>
		<select name="newsletter_status" id="newsletter_status">
			<option value="<?php echo get_post_meta($post->ID,'newsletter_status', true);?>" selected="selected"><?php echo get_post_meta($post->ID,'newsletter_status', true);?></option>
			<option value="<?php _e('save and send Later', 'sendit'); ?>"><?php _e('Save', 'sendit'); ?></option>
			<option value="<?php _e('send now', 'sendit'); ?>"><?php _e('Send now', 'sendit'); ?></option>
			<option value="<?php _e('schedule', 'sendit'); ?>"><?php _e('Schedule Job', 'sendit'); ?></option>
		</select>

<?php 
}


function sendit_template_select($post)
{
 if (is_plugin_active('sendit-pro-template-manager/sendit-pro-template-manager.php')) {
	wp_reset_query();
	$templates=extract_templates();
 ?>
<select name="template_id" id="template_id">
			<option value="<?php echo get_post_meta($post->ID,'template_id', true);?>" selected="selected"><?php echo get_the_title(get_post_meta($post->ID,'template_id', true));?></option>
			<?php
			 foreach($templates as $template): ?>
				<option value="<?php echo $template->ID; ?>"><?php echo apply_filters('the_title',$template->post_title); ?></option>
				<?php

			  endforeach;
				wp_reset_query();
			?>
		</select>
		

<?php 
} else 
	{
		echo 'Try Sendit Pro Template engine';
	}

}


function sendit_html_box($post)
{
	
	
	
	
	$css=get_post_meta($post->ID, 'newsletter_css', TRUE);
	$header=get_post_meta($post->ID, 'headerhtml', TRUE);
	$footer=get_post_meta($post->ID, 'footerhtml', TRUE); 
	?>
	<h3><?php _e('Custom Css','sendit'); ?></h3>
	<textarea name="newsletter_css" cols="80" rows="20"><?php echo $css;  ?></textarea>
	<h3><?php _e('Html Header', 'sendit') ?></h3>
	<textarea name="headerhtml" cols="80" rows="20"><?php echo $header;  ?></textarea>
	
	
	
	<?php 
	//wp_editor($header, 'headerhtml', $settings = array() );
	?>
	<h3><?php _e('Html Footer', 'sendit') ?></h3>
	<textarea name="footerhtml" cols="80" rows="20"><?php echo $footer;  ?></textarea>
	<?php 
	//wp_editor($footer, 'footerhtml', $settings = array() );

}







function sendit_push_template($post) {

	/*
	new: template push 
	*/
		
	wp_reset_query();	
	$templates=extract_templates();

	foreach($templates as $template): ?>
	<div class="post_box">
	<table>
		<tr>
			<th style="width:200px; text-align:left;"><?php echo $template->post_title; ?></th><td><a data-content-type="template" data-post-id="<?php echo $template->ID; ?>" class="button-primary send_to_editor">Apply Template &raquo;</a><span class="spinner"></span></td>
		</tr>
	</table>
	</div>
	<?php endforeach; ?>

<h2><?php _e('Last email theme from Sendit:'); ?></h2>
<?php // Get RSS Feed(s)
include_once(ABSPATH . WPINC . '/feed.php');

// Get a SimplePie feed object from the specified feed source.
$rss = fetch_feed('http://senditplugin.com/email_template/feed/');
if (!is_wp_error( $rss ) ) : // Checks that the object is created correctly 
    // Figure out how many total items there are, but limit it to 5. 
    $maxitems = $rss->get_item_quantity(1); 

    // Build an array of all the items, starting with element 0 (first element).
    $rss_items = $rss->get_items(0, $maxitems);
endif;
?>

<ul>
    <?php if ($maxitems == 0) echo '<li>No items.</li>';
    else
    // Loop through each feed item and display each item as a hyperlink.
    foreach ( $rss_items as $item ) :  ?>
    <li>
        <a href='<?php echo esc_url( $item->get_permalink() ); ?>'
        title='<?php echo 'Posted '.$item->get_date('j F Y | g:i a'); ?>'>
        <?php echo esc_html( $item->get_title() ); ?></a>
    </li>
    <?php endforeach; ?>
</ul>
	
<?php }


function sendit_help_box()
{
	?>
<h5><?php _e('Step by Step guide','sendit'); ?></h5>
	<ol>
		<li><?php _e('<a class="scroll" href="#template_choice"><b>Select template</b></a> for your newsletter (must have Sendit template manager, if not skip to 2)','sendit'); ?></li>
		<li><?php _e('<a class="scroll" href="#title"><b>Edit newsletter subject</b></a>'); ?></li>
		<li><?php _e('<a class="scroll" href="#wp-content-editor-container"><b>Edit</b></a> your email content or <a class="scroll" href="#content_choice"><b>adds articles</b></a> directly from your blog posts archive</a>. It automatically adds articles with featured images in the HTML editor','sendit'); ?></li>
		<li><?php _e('<a class="scroll" href="#action_html"><b>Select</a></b> mailing list if you want to send out the email','sendit'); ?></li>
		<li><?php _e('<a class="scroll" href="#submitdiv"><b>Decide</b></a> what to do! Save a draft, send instantly with free mode or schedule if you have <a href="http://senditplugin.com/?panel_from_domain='.get_bloginfo('siteurl').'">Sendit Pro Scheduler</a> activated','sendit'); ?></li>
		<li><?php _e('If sendit pro Scheduler is activated from newsletter panel you will have stats in realtime to see who opens your email','sendit'); ?></li>


	</ol>
<div id="sendit-banner">
	<span class="main">Limited offer</span>
	<span><a href="http://senditplugin.com/?panel_from_domain=<?php get_bloginfo('siteurl'); ?>"><?php _e('Get Sendit Premium Suite (6 plugins) for 35 &euro; and save 15 &euro; now','sendit'); ?></a></span>
</div>
	<?php
}


function sendit_content_box($post) {

	/*
	TO DO: Readmore customizing 
	*/
	
	global $post;
	$posts=extract_posts();
	foreach($posts as $post): ?>
	<div class="post_box">
	<table>
		<tr>
			<th style="width:200px; text-align:left;"><?php echo $post->post_title; ?></th><td><a class="button-secondary send_to_editor" data-content-type="post" data-post-id="<?php echo $post->ID; ?>">Send to Editor &raquo;</a></td>
		</tr>
	</table>

    </div>

<?php endforeach; 
	
}

add_action('save_post', 'sendit_save_postdata');

function sendit_save_postdata( $post_id )
{
 	//print_r($_POST);
	//if ( !wp_verify_nonce( $_POST['sendit_noncename'], 'sendit_noncename'.$post_id ))
		//return $post_id;
 
 	 if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) 
	    return $post_id;
 
  	if ( !current_user_can( 'edit_page', $post_id ) )
	    return $post_id;
 
	$post = get_post($post_id);
	if ($post->post_type == 'newsletter') {
		//old
		update_post_meta($post_id, 'send_now', $_POST['send_now']);	
		update_post_meta($post_id, 'sendit_list', $_POST['sendit_list']);
		//old
		
		
		//new 3.0
		update_post_meta($post_id, 'newsletter_status', $_POST['newsletter_status']);
		
		if($_POST['newsletter_status']=='send now'):
			send_newsletter($post_ID);
		endif;


		if($_POST['send_now']=='2'):
			wp_schedule_single_event( time() + 60, 'sendit_newsletter_scheduled' );
		endif;
		
		//echo $_POST['newsletter_status'];
		update_post_meta($post_id, 'template_id',$_POST['template_id']);

		//save scheduler data if exixts
		if(function_exists('Sendit_tracker_installation'))
		{
			update_post_meta($post_id, 'subscribers', get_list_subcribers($_POST['sendit_list']));
			update_post_meta($post_id, 'sendit_scheduled',$_POST['sendit_scheduled']);

		}
		//save which template

		return(esc_attr($_POST));
	}
}


add_action('save_post', 'sendit_template_postdata');

function sendit_template_postdata( $post_id )
{
 	//print_r($_POST);
	//if ( !wp_verify_nonce( $_POST['sendit_noncename'], 'sendit_noncename'.$post_id )) return $post_id;
 
 	 if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return $post_id;
 
  	if ( !current_user_can( 'edit_page', $post_id )) return $post_id;
 	$post = get_post($post_id);
	//obtain custom field meta for this post
     $custom_fields = get_post_custom($post_id);

    if(!$custom_fields) return;

    foreach($custom_fields as $key=>$custom_field):
        //$custom_field is an array of values associated with $key - even if there is only one value. 
        //Filter to remove empty values.
        //Be warned this will remove anything that casts as false, e.g. 0 or false 
        //- if you don't want this, specify a callback.
        //See php documentation on array_filter
        $values = array_filter($custom_field);

        //After removing 'empty' fields, is array empty?
        if(empty($values)):
            delete_post_meta($post_id,$key); //Remove post's custom field
        endif;
    endforeach; 
	
	
	if ($post->post_type == 'sendit_template') {
			
		update_post_meta($post_id, 'newsletter_css', $_POST['newsletter_css']);	
		update_post_meta($post_id, 'headerhtml', $_POST['headerhtml']);	
		update_post_meta($post_id, 'footerhtml', $_POST['footerhtml']);
		
	
		return(esc_attr($_POST));
	}
}



function sendit_custom_box($post) {
	$sendit = new Actions();
	global $wpdb;
	$choosed_list = get_post_meta($post->ID, 'sendit_list', TRUE);
	//echo $choosed_list;
	$table_email =  SENDIT_EMAIL_TABLE;   
	$table_liste =  SENDIT_LIST_TABLE;   
    $liste = $wpdb->get_results("SELECT id_lista, nomelista FROM $table_liste ");
	echo '<label for="send_now">'.__('Action', 'sendit').': </label>';
	
	if(get_post_meta($post->ID, 'send_now', TRUE)=='2'):
		echo '<div class="jobrunning senditmessage"><h5>'.__('Warning newsletter is currently running the job','sendit').'</h5></div>';
	elseif(get_post_meta($post->ID, 'send_now', TRUE)=='4'):
		echo '<div class="jobdone senditmessage"><h5>'.__('Newsletter already Sent','sendit').'</h5></div>';
	else:
		
	endif;	
	
	echo '<select name="send_now" id="send_now">';
	
	if(function_exists('Sendit_tracker_installation')):
		if(get_post_meta($post->ID, 'send_now', TRUE)==2){ $selected=' selected="selected" ';} else { $selected='';}
		echo '<option value="2" '.$selected.'>'.__( 'Schedule with Sendit Pro', 'sendit' ).'</option>';
	endif;
	
		if(get_post_meta($post->ID, 'send_now', TRUE)==1){ $selected=' selected="selected" ';} else { $selected='';}
		echo '<option value="1" '.$selected.'>'.__( 'Send now', 'sendit' ).'</option>';	

		if(get_post_meta($post->ID, 'send_now', TRUE)==0){ $selected=' selected="selected" ';} else { $selected='';}
		echo '<option value="0" '.$selected.'>'.__( 'Save and send later', 'sendit' ).'</option>';
		
	if(function_exists('Sendit_tracker_installation')):	
		if(get_post_meta($post->ID, 'send_now', TRUE)==4){ $selected=' selected="selected" ';} else { $selected='';}
		echo '<option value="4" '.$selected.'>'.__( 'Sent with Sendit pro', 'sendit' ).'</option>';	
	endif;
			
		if(get_post_meta($post->ID, 'send_now', TRUE)==5){ $selected=' selected="selected" ';} else { $selected='';}
		echo '<option value="4" '.$selected.'>'.__( 'Sent with Sendit free', 'sendit' ).'</option>';
				
	echo '</select><br />';
	echo '<h4>'.__('Select List', 'sendit').'</h4>';
	foreach($liste as $lista): 
		$subscribers=count($sendit->GetSubscribers($lista->id_lista));?>
    	<input type="radio" name="sendit_list" value="<?php echo $lista->id_lista; ?>" <?php if ($choosed_list == $lista->id_lista) echo "checked=1";?>> <?php echo $lista->nomelista; ?>  subscribers: <?php echo $subscribers; ?><br/>
	<?php endforeach; ?>


	<input type="hidden" name="sendit_noncename" id="sendit_noncename" value="<?php echo wp_create_nonce( 'sendit_noncename'.$post->ID );?>" />
	
	<?php
}



/*replace with this one in version 3*/

function sendit_custom_box_3($post) {
	$sendit = new Actions();
	global $wpdb;
	$choosed_list = get_post_meta($post->ID, 'sendit_list', TRUE);
	//echo $choosed_list;
	$table_email =  SENDIT_EMAIL_TABLE;   
	$table_liste =  SENDIT_LIST_TABLE;   
    $liste = $wpdb->get_results("SELECT id_lista, nomelista FROM $table_liste ");
	echo '<label for="send_now">'.__('Action', 'sendit').': </label>';
	
	if(get_post_meta($post->ID, 'send_now', TRUE)=='2'):
		echo '<div class="jobrunning senditmessage"><h5>'.__('Warning newsletter is currently running the job','sendit').'</h5></div>';
	elseif(get_post_meta($post->ID, 'send_now', TRUE)=='4'):
		echo '<div class="jobdone senditmessage"><h5>'.__('Newsletter already Sent','sendit').'</h5></div>';
	else:
		
	endif;	
	
	echo '<select name="send_now" id="send_now">';
	
	if(function_exists('Sendit_tracker_installation')):
		if(get_post_meta($post->ID, 'send_now', TRUE)==2){ $selected=' selected="selected" ';} else { $selected='';}
		echo '<option value="2" '.$selected.'>'.__( 'Schedule with Sendit Pro', 'sendit' ).'</option>';
	endif;
		if(get_post_meta($post->ID, 'send_now', TRUE)==1){ $selected=' selected="selected" ';} else { $selected='';}
		echo '<option value="1" '.$selected.'>'.__( 'Send now', 'sendit' ).'</option>';	

		if(get_post_meta($post->ID, 'send_now', TRUE)==0){ $selected=' selected="selected" ';} else { $selected='';}
		echo '<option value="0" '.$selected.'>'.__( 'Save and send later', 'sendit' ).'</option>';
		
		if(get_post_meta($post->ID, 'send_now', TRUE)==4){ $selected=' selected="selected" ';} else { $selected='';}
		echo '<option value="4" '.$selected.'>'.__( 'Sent with Sendit pro', 'sendit' ).'</option>';	
		
		if(get_post_meta($post->ID, 'send_now', TRUE)==5){ $selected=' selected="selected" ';} else { $selected='';}
		echo '<option value="4" '.$selected.'>'.__( 'Sent with Sendit free', 'sendit' ).'</option>';
				
	echo '</select><br />';
	echo '<h4>'.__('Select List', 'sendit').'</h4>';
	foreach($liste as $lista): 
		$subscribers=count($sendit->GetSubscribers($lista->id_lista));?>
    	<input type="radio" name="sendit_list" value="<?php echo $lista->id_lista; ?>" <?php if ($choosed_list == $lista->id_lista) echo "checked=1";?>> <?php echo $lista->nomelista; ?>  subscribers: <?php echo $subscribers; ?><br/>
	<?php endforeach; ?>


	<input type="hidden" name="sendit_noncename" id="sendit_noncename" value="<?php echo wp_create_nonce( 'sendit_noncename'.$post->ID );?>" />
	
	<?php
}




?>