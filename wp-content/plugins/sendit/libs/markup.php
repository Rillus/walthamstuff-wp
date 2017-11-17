<?php

/*--------------------------------------------------------------
++++++++++++++++++++++++ New sendit +++++++++++++++++++++++++++
--------------------------------------------------------------*/

/**
 * Proper way to enqueue scripts and styles
 */

// Register Style
function custom_styles() {

	wp_enqueue_style( 'sendit-messages', plugins_url( 'sendit/sendit.css'));
	wp_enqueue_script( 'spin', plugins_url( 'sendit/assets/js/spin.js'), array(), '3.0.0', true);
	wp_enqueue_script( 'jquery-spin', plugins_url( 'sendit/assets/js/jquery.spin.js'), array(), '3.0.0', true);
	wp_enqueue_script( 'sendit-frontend', plugins_url( 'sendit/assets/js/frontend.js'), array(), '3.0.0', true);

}
add_action( 'wp_enqueue_scripts', 'custom_styles' );


function sendit_ajaxurl() {
	?>
	<script type="text/javascript">
	var sendit_ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
	</script>
	<?php
}
add_action('wp_head','sendit_ajaxurl');


add_action('wp_ajax_sendit_subscription', 'sendit_subscription' );
add_action('wp_ajax_nopriv_sendit_subscription', 'sendit_subscription');

function sendit_subscription() {
	$sendit=new Actions();
	$sendit->NewSubscriber();
	wp_die(); // this is required to terminate immediately and return a proper response
}


function sendit_shortcode($atts) {
   $markup=sendit_markup($atts[id]);
   return $markup;
}

add_shortcode('newsletter', 'sendit_shortcode');


function sendit_markup($id)
{
     /*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     	The standard HTML form for all usage (widget shortcode etc)
     +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/

	$sendit_markup=get_option('sendit_markup');
 	$sendit_markup=str_replace("{list_id}",$id, $sendit_markup);

 	if(function_exists('sendit_morefields')):
 		$sendit_markup=str_replace("{sendit_morefields}",sendit_morefields(), $sendit_markup);
	else:
 		$sendit_markup=str_replace("{sendit_morefields}",'', $sendit_markup);
	endif;

 	$sendit_markup=str_replace("{subscribe_text}", get_option('sendit_subscribe_button_text'), $sendit_markup);
 	$sendit_markup.='<div class="spin" data-spin id="sendit-wait"></div>';
 	$sendit_markup.='<div id="sendit_response"></div>';
   	//love link
   	$sendit_markup.='<small>Sendit <a href="http://www.giuseppesurace.com" title="Wordpress newsletter plugin">Wordpress newsletter</a></small>';


 	if(is_user_logged_in()):
		$sendit_markup.='<br /><small><a href="wp-admin/admin.php?page=sendit_general_settings">'.__('Customize Widget','sendit').'</a></small>';
 	endif;
 	return $sendit_markup;

}


function sendit_register_head() {
    echo '<style type="text/css">'.get_option('sendit_css').'</style>';
}


function DisplayForm()
{
    if ( !function_exists('register_sidebar_widget') ){return; }
    register_sidebar_widget('Sendit Widget','JqueryForm');
    register_widget_control('Sendit Widget','Sendit_widget_options', 200, 200);
}

function JqueryForm($args) {
    global $dcl_global;
    extract($args);
    $lista= get_option('id_lista');
    //before_widget,before_title,after_title,after_widget

    $form_aggiunta=$before_widget."
             ".$before_title.get_option('titolo').$after_title;
  			$form_aggiunta.=sendit_markup($lista);
           // if (!$dcl_global) $form_aggiunta.="<p><small>Sendit <a href=\"http://www.giuseppesurace.com\">Wordpress  newsletter</a></small></p>";
            $form_aggiunta.=$after_widget;

    echo $form_aggiunta;
}

function Sendit_widget_options() {
        if ($_POST['id_lista']) {
            $id_lista=$_POST['id_lista'];
            $titolo=$_POST['titolo'];
            update_option('id_lista',$id_lista);
            update_option('titolo',$_POST['titolo']);
        }
        $id_lista = get_option('id_lista');
        $titolo = get_option('titolo');
        //titolo
        echo '<p><label for="titolo">'.__('Newsletter title: ', 'sendit').' <input id="titolo" name="titolo"  type="text" value="'.$titolo.'" /></label></p>';
        //id della mailing list
        echo '<p><label for="id_lista">'.__('Mailing list ID: ', 'sendit').' <input id="id_lista" name="id_lista" type="text" value="'.$id_lista.'" /></label></p>';


    }

?>
