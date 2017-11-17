<?php
function sendit_admin_setup(){
	wp_localize_script( 'single-ajax-request', 'SingleAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
}
add_action( 'wp_enqueue_scripts', 'sendit_admin_setup' );

function admin_styles() {

	wp_enqueue_style( 'sendit-messages', plugins_url( 'sendit/sendit.css'));
	wp_enqueue_script( 'spin', plugins_url( 'sendit/assets/js/spin.js'), array(), '3.0.0', true);
	wp_enqueue_script( 'jquery-spin', plugins_url( 'sendit/assets/js/jquery.spin.js'), array(), '3.0.0', true);
	wp_enqueue_script( 'sendit-frontend', plugins_url( 'sendit/assets/js/frontend.js'), array(), '3.0.0', true);

}
add_action( 'wp_enqueue_scripts', 'admin_styles' );


add_action ( 'wp_ajax_nopriv_sendit-load-single', 'sendit_single_ajax_content' );
add_action ( 'wp_ajax_sendit-load-single', 'sendit_single_ajax_content' );

function sendit_single_ajax_content () {
		   $response='';
		   $post_id=$_POST['post_id'];
		   $post = get_post($post_id);
		   if($_POST['content_type'] == 'post'):
            $response.='<div class="title">';
		   	$response.='<h2><a href="'.get_permalink($post_id).'">'.apply_filters('the_title',$post->post_title).'</a></h2>';
		   	$response.='</div>';
			$response.='<div class="body-text">';
		   	$thumb = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), 'thumbnail');
		   	$url = $thumb['0'];
		  	$thumb_url = wp_get_attachment_url('thumbnail', true);
		  	$response.='<a href="'.get_permalink($post_id).'"><img src="'.$url.'" class="img-responsive alignleft" alt="'.apply_filters('the_title',$post->post_title).'" /></a>';
		  	$response.= $post->post_content;
		  	$response.='</div>';
		   else:
		   	$css= get_post_meta($post->ID, 'newsletter_css', TRUE);
			$header= '<!-- [template_id='.$post->ID.'] -->';
			$header.=get_post_meta($post->ID, 'headerhtml', TRUE);
			//parse header shortcode...
			$header=str_replace('[style]','<style>'.$css.'</style>',$header);

			//logo
			if ( has_post_thumbnail($post->ID) ) {
				$header_image=get_the_post_thumbnail($post->ID);
				}
			else {
				$header_image='<img alt="" src="http://placehold.it/300x50/" />';
			}


			$header = str_replace('[logo]',$header_image,$header);
			$header = str_replace('[homeurl]',get_bloginfo('siteurl'),$header);
			$footer = get_post_meta($post->ID, 'footerhtml', TRUE);
			//build template scaffold


			$response .= $header;
			$response .= '<h2>'.__('Good Luck!','sendit').'</h2>';
			$response .= '<p>'.__(' Start from here to edit your content').'</p>';
			$response .= $footer;


		   endif;


		    if(is_plugin_active('sendit-css-inliner/sendit-pro-css-inliner.php')):
				$response=inline_newsletter($css,$response);
			endif;
				$response = preg_replace('/(&Acirc;|&nbsp;)+/i', ' ', $response);




      echo $response;


      die(1);
}


function count_subscribers($id_lista) {
     global $wpdb;
	 $user_count = $wpdb->get_var("SELECT COUNT(*) FROM ".SENDIT_EMAIL_TABLE." where id_lista = $id_lista");
	 return $user_count;
}


function ManageLists() {
    global $_POST;
    global $wpdb;

    //nome tabella LISTE
    $table_liste = $wpdb->prefix . "nl_liste";

    if($_POST['newsletteremail']!="" AND $_POST['com']!="EDIT"):
        $liste_count = $wpdb->get_var("SELECT COUNT(*) FROM $table_liste where email_lista ='$_POST[newsletteremail]';");
           $wpdb->query("INSERT INTO $table_liste (email_lista, nomelista) VALUES ('$_POST[newsletteremail]', '$_POST[newslettername]')");
           echo '<div id="message" class="updated fade"><p><strong>'.__('Mailing list created succesfully!', 'sendit').'</strong></p></div>';
    endif;

    if($_POST['com']=="EDIT") :
    	$header = $_POST['header'];
    	$footer = $_POST['footer'];
        $aggiorno= $wpdb->query("UPDATE $table_liste set email_lista = '$_POST[newsletteremail]', nomelista = '$_POST[newslettername]', header='$header', footer='$footer' where id_lista = '$_POST[id_lista]'");
        $msg =  '<div id="message" class="updated fade"><p><strong>'.__('Mailing list updated', 'sendit').'</strong></p></div>';
    elseif($_POST['com']=="ADD") :
        $newemail = __('email@here', 'sendit');
        $newname = __('New mailing list', 'sendit');
        $ins= $wpdb->query("insert into $table_liste (email_lista, nomelista) values('$newemail', '$newname')");
        $msg = '<div id="message" class="updated fade"><p><strong>'.__('Mailing created successfully!', 'sendit').'</strong></p></div>';
    elseif($_POST['com']=="DEL") :
        $ins= $wpdb->query("delete from $table_liste where id_lista = $_POST[id_lista]");
        $msg = '<div id="message" class="updated fade"><p><strong>'.__('Mailing deleted successfully!', 'sendit').'</strong></p></div>';
    endif;

    if(($_GET['update']==1)&&(!isset($_POST['com']))) :
      $listacorrente= $wpdb->get_row("select * from $table_liste where id_lista = '$_GET[id_lista]'");
      $com="EDIT";
    endif;

    if($_GET['delete']==1) :
       //div che avvisa prima della cancellazione con form
       $msg = "<div class=\"error\"class=\"wrap\"><p>".sprintf(__('Are You sure to delete %d list? it will delete all mailing list and subscribers data ', 'sendit'), $_GET['id_lista'])." ".$listacorrente->nomelista."</p>
                <form action=\"admin.php?page=lists-management\" method=\"post\" name=\"delml\">
                    <input type=\"hidden\" name=\"id_lista\" value = \"".$_GET['id_lista']."\">
                    <input type=\"submit\" name=\"com\" value = \"DEL\">
                </form>
                </div>";
    endif;

            echo "<div class=\"wrap\"class=\"wrap\"><h2>".__('Lists Management', 'sendit')." ".$listacorrente->nomelista."</h2>";




            //esco il messaggio
                    echo $msg;




                    $table_liste = $wpdb->prefix . "nl_liste";
                    $liste= $wpdb->get_results("select * from $table_liste");


                    echo "
                    <form action='$_SERVER[REQUEST_URI]' method='post' name='addml'>
                    <input type='submit' class='button-primary sendit_actionbuttons' name='go' value='".__('Create new list', 'sendit')."'>
                    <input type='hidden' name='com' value='ADD' />
                    </form>
                         <table class=\"wp-list-table widefat fixed posts\">
                            <thead>
                            <tr>
                            <th>".__('Available lists', 'sendit')."</th>
                            <th>".__('from','sendit')."</th>
                            <th style=\"width:100px;\">".__('Subscribers','sendit')."</th>
                            <th>".__('actions','sendit')."</th>
                            </tr>
                            </thead>
                            <tbody>
                            ";
                    foreach ($liste as $lista) {

                        echo "<tr>
                            <td>". $lista->id_lista." - "  .$lista->nomelista."</td>
                            <td>". $lista->email_lista. " </td>
                            <td><b>".count_subscribers($lista->id_lista)."</b></td>
                            <td><a class=\"button-secondary\" href=\"admin.php?page=lists-management&update=1&id_lista=".$lista->id_lista."\"><i class=\"dashicons-before dashicons-admin-tools\"></i> ".__('Edit', 'sendit')."</a> <a class=\"button-secondary\" href=\"admin.php?page=lista-iscritti&lista=".$lista->id_lista."\"><i class=\"dashicons-before dashicons-admin-users\"></i> ".__('Manage subscribers', 'sendit')."</a> <a class=\"button-secondary\" href=\"admin.php?page=lists-management&delete=1&id_lista=".$lista->id_lista."\">".__('Delete', 'sendit')."</a></td></p></tr>";

                        }

                        echo "</tbody></table>";

            if($_GET['id_lista'] and !$_GET['delete']) :

            echo "<form action='$_SERVER[REQUEST_URI]' method='post' >
            <p>".__('Newsletter options', 'sendit')."</p>
            <table>

                    <tr>
                        <th scope=\"row\" width=\"200\"><label for=\"newsletteremail\">".__('from email', 'sendit')."</label><th>
                        <td><input type=\"text\" name=\"newsletteremail\" value=\"".$listacorrente->email_lista."\" ></td>
                    </tr>
                    <tr>
                    <th scope=\"row\" ><label for=\"newslettername\">".__('Newsletter name', 'sendit')."</label><th>
                    <td><input type=\"text\" name=\"newslettername\"  value=\"".$listacorrente->nomelista."\"><input type=\"hidden\" name=\"com\" value=\"".$com."\">
                    <input type=\"hidden\" name=\"id_lista\" value=\"".$_GET[id_lista]."\">
                    </td></tr>
                    <tr>
                    <th scope=\"row\"><label for=\"template_id\">Select Template</label></th><td></td>

                    </tr>
                    <tr><th scope=\"row\" ><label for=\"header\">".__('Header', 'sendit')."</label><th>
                    <td><textarea name=\"header\" rows=\"5\" cols=\"50\">".$listacorrente->header."</textarea></td></tr>
                        <tr><th scope=\"row\" ><label for=\"footer\">".__('Footer', 'sendit')."</label><th>
                    <td><textarea name=\"footer\" rows=\"5\" cols=\"50\">".$listacorrente->footer."</textarea></td></tr>
                    <tr><th scope=\"row\" ><th>
                    <td><p class=\"submit\"><input type=\"submit\" class=\"button-primary\" name=\"salva\" value=\"".__('Save', 'sendit')."\"></p></td>
                    </tr>
                 </table>
                    </form>";
                endif;
                echo "</div>";

}

function SmtpSettings()
{

    /*
    $mail->Host = get_option('sendit_smtp_host'); // Host
    $mail->Hostname = get_option('sendit_smtp_hostname');// SMTP server hostname
    $mail->Port  = get_option('sendit_smtp_port');// set the SMTP port
    */

    $markup= "<div class=\"wrap\"class=\"wrap\"><h2>".__('Sendit SMTP settings', 'sendit');

    if($_POST):
        update_option('sendit_smtp_host',$_POST['sendit_smtp_host']);
        update_option('sendit_smtp_hostname',$_POST['sendit_smtp_hostname']);
        update_option('sendit_smtp_port',$_POST['sendit_smtp_port']);

        update_option('sendit_smtp_authentication',$_POST['sendit_smtp_authentication']);
        update_option('sendit_smtp_username',$_POST['sendit_smtp_username']);
        update_option('sendit_smtp_password',$_POST['sendit_smtp_password']);
        update_option('sendit_smtp_ssl',$_POST['sendit_smtp_ssl']);

        //new from 1.5.0!!!
        update_option('sendit_sleep_time',$_POST['sendit_sleep_time']);
        update_option('sendit_sleep_each',$_POST['sendit_sleep_each']);
		//new from 2.2.8
		update_option('sendit_smtp_debug',$_POST['sendit_smtp_debug']);


        $selected_debug=get_option('sendit_smtp_debug');

        $markup.='<div id="message" class="updated fade"><p><strong>'.__('Settings saved!', 'sendit').'</strong></p></div>';
    endif;
    $markup.='<h3>'.__('Smtp settings are required only if you want to send mail using an SMTP server','sendit').'</h3>
    <p>'.__('By default Sendit will send newsletter using the mail() function, if you want to send mail using SMTP server you just have to type your settings in this section.<br /> I strongly recommend to use my own SMTP service in partnership with Smtp.com. Easy to configure as you can see on the link below').'</p>
<form method="post" action="'.$_SERVER[REQUEST_URI].'">
<table class="form-table">
    <tr>
        <th><label for="sendit_smtp_debug">'.__('Display Debug informations', 'sendit').'?</label></th>
        <td>
           	<select name="sendit_smtp_debug" id="sendit_smtp_ssl">
        		<option value="'.get_option('sendit_smtp_debug').'" selected="selected" />'.get_option('sendit_smtp_debug').'</option>
        		<option value="0">0</option>
        		<option value="1">1</option>
        		<option value="2">2</option>
			</select>
        </td>
    </tr>

    <tr>
        <th><label for="sendit_smtp_host">'.__('SMTP host', 'sendit').'<br />('.__('Need One', 'sendit').'? <a href="http://www.smtp.com/senditwordpress/">'.__('Try Sendit with SMTP.com', 'sendit').'</a>)</label></th>
        <td><input name="sendit_smtp_host" id="sendit_smtp_host" type="text" value="'.get_option('sendit_smtp_host').'" class="regular-text code" /></td>
    </tr>

    <tr>
        <th><label for="sendit_smtp_port">'.__('SMTP port', 'sendit').'</label></th>
        <td><input name="sendit_smtp_port" id="sendit_smtp_hostname" type="text" value="'.get_option('sendit_smtp_port').'" class="regular-text code" /></td>
    </tr>
    <tr>
        <th colspan="2">
        <h3>'.__('Settings below are required only if SMTP server require authentication','sendit').'</h3>
        </th>
    </tr>
    <tr>
        <th><label for="sendit_smtp_username">'.__('SMTP username', 'sendit').'</label></th>
        <td><input name="sendit_smtp_username" id="sendit_smtp_username" type="text" value="'.get_option('sendit_smtp_username').'" class="regular-text code" /></td>
    </tr>
    <tr>
        <th><label for="sendit_smtp_password">'.__('SMTP password', 'sendit').'</label></th>
        <td><input name="sendit_smtp_password" id="sendit_smtp_password" type="password" value="'.get_option('sendit_smtp_password').'" class="regular-text code" /></td>
    </tr>
    <tr>
        <th><label for="sendit_smtp_ssl">SMTP SSL</label></th>
        <td>
        	<select name="sendit_smtp_ssl" id="sendit_smtp_ssl">
        		<option value="'.get_option('sendit_smtp_ssl').'" selected="selected" />'.get_option('sendit_smtp_ssl').'</option>
        		<option value="">no</option>
        		<option value="ssl">SSL</option>
        		<option value="tls">TLS</option>
		</select>
        </td>
    </tr>


</table>
<div class="suggest">
<p>
<i>'.
__('Are you on panic for large mailing lists, bad delivery (spam etc)?','sendit').'<br />';

$markup.='<strong>Relax!</strong>'.__('Let SMTP.com with Sendit handle your email delivery used with Sendit. Get 25% off any plan by clicking my link.','sendit');

$markup.='<br /><a href="http://www.smtp.com/senditwordpress/"><strong>Sendit SMTP.com</strong> service</a><br />';

$markup.='<br /><br />Also SendGrid helps you reach more users instead of spam folders. Click this link to get your 25% discount on your first month\'s membership. Believe me you will be addicted!<br />';

$markup.='<a href="http://sendgrid.tellapal.com/a/clk/3Rv3Ng">http://sendgrid.tellapal.com/a/clk/3Rv3Ng</a>';

$markup.='<br />Best<br />Giuseppe</i>
</p></div>

<p class="submit">
    <input type="submit" name="submit" class="button-primary" value="'.__('Save settings', 'sendit').'" />
</p>
  </form>';

    $markup.='</div>';

    echo $markup;

}


function SenditMainSettings($c='')
{


    $markup= '<div class="wrap"class="wrap">';

    $markup.='<h2>'.__('Sendit General settings', 'sendit').'</h2>';

    $c=md5(uniqid(rand(), true));
    if($_POST):
        update_option('sendit_subscribe_button_text',stripslashes($_POST['sendit_subscribe_button_text']));
        update_option('sendit_response_mode',stripslashes($_POST['sendit_response_mode']));
        update_option('sendit_markup',stripslashes($_POST['sendit_markup']));
        update_option('sendit_css',stripslashes($_POST['sendit_css']));
        update_option('sendit_unsubscribe_link',stripslashes($_POST['sendit_unsubscribe_link']));
        update_option('sendit_gravatar',stripslashes($_POST['sendit_gravatar']));


        $markup.='<div id="message" class="updated fade"><p><strong>'.__('Settings saved!', 'sendit').'</strong></p></div>';
        //$markup.='<div id="sendit_preview">'.sendit_markup(1).'</div>';
    endif;

    $markup.='<h3>'.__('Welcome to the new Sendit Newsletter plugin Panel').'</h3>';
    $markup.='<img style="float:left;margin:0 5px 0 0;"src="http://senditplugin.com/wp-content/uploads/sendit_big1.jpg" width="200" /><i>Welcome to the new Sendit plugin. Probably you were expeting the old form to send newsletter from here. As well i rebuilt and did a big refactoring of all plugin so its new. The new Sendit support custom post types so newsletters will be saved. The new plugin also containes a lot of functions requested directly by users so should be excited to test. You can finally built newsletter selecting content from posts (more than 1 post) just choosing from the <strong>custom meta box</strong></i><br />


<form method="post" action="'.$_SERVER[REQUEST_URI].'&c='.$c.'">
<table class="form-table">
    <tr>
        <th><label for="sendit_subscribe_button_text">Subscribtion button text</label></th>
        <td><input type="text" name="sendit_subscribe_button_text" id="sendit_subscribe_button_text" value="'.get_option('sendit_subscribe_button_text').'" /></td>
    </tr>
    <tr>
        <th><label for="sendit_unsubscribe_link">Show unsubscribe link on footer?</label></th>
        <td>
        	<select name="sendit_unsubscribe_link">
        		<option value="'.get_option('sendit_unsubscribe_link').'" selected="selected">'.get_option('sendit_unsubscribe_link').'</option>
        		<option value="no">no</option>
        		<option value="yes">yes</option>
        	</select> <small>(If not be sure you have an option to unsubscribe)</small>
        </td>
    </tr>
        <tr>
        <th><label for="sendit_gravatar">Show gravatar on subscriber list</label></th>
        <td>
        	<select name="sendit_gravatar">
        		<option value="'.get_option('sendit_gravatar').'" selected="selected">'.get_option('sendit_gravatar').'</option>
        		<option value="no">no</option>
        		<option value="yes">yes</option>
        	</select>
        </td>
    </tr>
    <tr>
        <th><label for="sendit_response_mode">'.__('Response mode', 'sendit').'</label></th>
        <td>
        	<select name="sendit_response_mode">
        		<option value="'.get_option('sendit_response_mode').'" selected="selected">'.get_option('sendit_response_mode').'</option>
        		<option value="alert">Alert</option>
        		<option value="ajax">Ajax</option>
        	</select>
		</td>
    </tr>
    <tr>
        <th><label for="sendit_markup">'.__('Subscription form Html markup', 'sendit').'</label></th>
        <td><textarea class="sendit_code source" rows="15" cols="70" name="sendit_markup" id="sendit_markup">'.get_option('sendit_markup').'</textarea></td>
    </tr>
    <tr>
        <th><label for="sendit_css">Subscription widget CSS markup</label></th>
        <td><textarea class="sendit_blackcss" rows="15" cols="70" name="sendit_css" id="sendit_css">'.get_option('sendit_css').'</textarea></td>
    </tr>

</table>


<p class="submit">
    <input type="submit" name="submit" class="button-primary sendit_actionbuttons" value="'.__('Save settings', 'sendit').'" />
</p>
  </form>';

    $markup.='</div>';

    echo $markup;

}


function MainSettings($c='')
{


    $markup= '<div class="wrap"class="wrap"><h2>'.__('Sendit', 'sendit').'</h2>';

    //new 2.2.0
    $markup.='
<div class="sendit-banner">
	<h3>Welcome to the new Sendit '.SENDIT_VERSION.' control panel</h3>
	<span>Enhance your newsletter plugin by adding pro plugins! Learn more.<br /><a target="_blank" title="Get Sendit" href="http://senditplugin.com/">take a tour</a> and don\'t forget to subscribe our newsletter to be updated</span>


</div>';



    //$markup.='<label>Preview Area</label><div class="preview"></div>';
    $c=md5(uniqid(rand(), true));
    if($_POST):
        update_option('sendit_subscribe_button_text',stripslashes($_POST['sendit_subscribe_button_text']));
        update_option('sendit_markup',stripslashes($_POST['sendit_markup']));
        update_option('sendit_css',stripslashes($_POST['sendit_css']));
    endif;
	$markup.='<div class="sendit_box_list sendit_box_menu"><h2>'.__('Mailing lists', 'sendit').'</h2>
			  	<a href="'.admin_url( 'admin.php?page=lists-management').'" class="button-primary">'.__('Create and manage lists', 'sendit').'</a>
			  </div>
			  <div class="sendit_box_design sendit_box_menu"><h2>'.__('Main Settings', 'sendit').'</h2>
			  	<a href="'.admin_url( 'admin.php?page=sendit_general_settings').'" class="button-primary">'.__('Main Settings', 'sendit').'</a>
			  </div>
			  <div class="sendit_box_sendnewsletter sendit_box_menu"><h2>'.__('Send Newsletter', 'sendit').'</h2>
			  	<a href="'.admin_url( 'post-new.php?post_type=newsletter').'" class="button-primary">'.__('Create and send newsletter', 'sendit').'</a>
			  </div>';

	$markup.='<!-- start payment extensions --><div class="sendit_box_fields sendit_box_menu"><h2>'.__('Add more fields', 'sendit').'</h2>
			  	<a href="'.admin_url( 'admin.php?page=sendit_morefields_settings').'" class="button-primary">'.__('Add more fields', 'sendit').'</a>
			  </div>';

	$markup.='<div class="sendit_box_export sendit_box_menu"><h2>'.__('Export mailing lists', 'sendit').'</h2>
			  	<a href="'.admin_url('admin.php?page=export-subscribers').'" class="button-primary">'.__('Save your list as CSV', 'sendit').'</a>
			  </div>';
	//new from 2.1.0 to hide cron settings if you dont have the scheduler active
	if (is_plugin_active('sendit-scheduler/sendit-cron.php')) {

	$markup.='<div class="sendit_box_cron sendit_box_menu"><h2>'.__('Cron Settings', 'sendit').'</h2>
			  	<a href="'.admin_url('admin.php?page=cron-settings').'" class="button-primary">'.__('Cron settings', 'sendit').'</a>
			  </div>';
	} else {

		$markup.='<div class="sendit_box_cron sendit_box_menu"><h2>'.__('Cron Settings', 'sendit').'</h2>
			  	<a href="'.admin_url('admin.php?page=cron-settings').'" class="button-primary">'.__('Buy Sendit Scheduler', 'sendit').'</a>
			  </div>';

	}

	$markup.='<div class="sendit_box_template sendit_box_menu"><h2>'.__('Newsletter Templates', 'sendit').'</h2>
		  	<a href="'.admin_url('admin.php?page=sendit_pro_template').'" class="button-primary">'.__('Template manager', 'sendit').'</a>
		  </div>';


	$markup.='<div class="sendit_box_shop sendit_box_menu"><h2>'.__('Extend your plugin', 'sendit').'</h2>
		  	<a href="http://senditplugin.com" class="button-primary">'.__('Go to the shop', 'sendit').'</a>
		  </div>';

if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
	$markup.='<div class="sendit_box_woocommerce sendit_box_menu"><h2>'.__('Woocommerce user?', 'sendit').'</h2>
		  	<a href="http://senditplugin.com" class="button-primary">'.__('Import your customer into Sendit', 'sendit').'</a>
		  </div>';
}



    $markup.='</div>';

    echo $markup;

}


function gestisci_menu() {
/*++++++++++++++++Menu Handler+++++++++++++++++++++++++++++++*/
	global $wpdb;
    global $myListTable;
    add_menu_page(__('Send', 'sendit'), __('Sendit', 'sendit'), 8, __FILE__, 'MainSettings');

    add_submenu_page(__FILE__, __('Manage subscribers', 'sendit'), __('Manage subscribers', 'sendit'), 8, 'lista-iscritti', 'sendit_render_list_page');

    //add_submenu_page(__FILE__, __('Manage subscribers', 'sendit'), __('Manage subscribers', 'sendit'), 8, 'lista-iscritti', 'Iscritti');
    add_submenu_page(__FILE__, __('List Options', 'sendit'), __('Lists management', 'sendit'), 8, 'lists-management', 'ManageLists');
    add_submenu_page(__FILE__, __('Main settings', 'sendit'), __('Main settings', 'sendit'), 8, 'sendit_general_settings', 'senditpanel_admin');

	/*2.0 export addon*/
	if (function_exists('sendit_morefields'))
	{
		add_submenu_page(__FILE__, __('Fields settings', 'sendit'), __('Fields settings', 'sendit'), 8, 'sendit_morefields_settings', 'SenditMoreFieldSettings');
	}
	else
	{
		add_submenu_page(__FILE__, __('Fields list', 'sendit'), __('Fields settings', 'sendit'), 8, 'sendit_morefields_settings', 'sendit_morefields_screen');
	}

    add_submenu_page(__FILE__, __('SMTP settings', 'sendit'), __('SMTP settings', 'sendit'), 8, 'sendit_smtp_settings', 'SmtpSettings');
    add_submenu_page(__FILE__, __('Test email', 'sendit'), __('Test email', 'sendit'), 8, 'sendit_test_email', 'sendit_test_email');
    add_submenu_page(__FILE__, __('email import', 'sendit'), __('Import emails from comments', 'sendit'), 8, 'mass-import', 'ImportWpComments');
    add_submenu_page(__FILE__, __('email import', 'sendit'), __('Import emails from WP Users', 'sendit'), 8, 'import', 'ImportWpUsers');

	//woocommerce import 2.3.7
	if (is_plugin_active('woocommerce/woocommerce.php')) {
		if (is_plugin_active('sendit-woocommerce-import/sendit-woocommerce-import.php')) {
			add_submenu_page(__FILE__, __('Woocommerce import', 'sendit'), __('Import email from Woocommerce', 'sendit'), 8, 'import-woocommerce-customers', 'ImportWoocommerceCustomers');
		}
		else
		{
			add_submenu_page(__FILE__, __('Woocommerce import', 'sendit'), __('Import email from Woocommerce', 'sendit'), 8, 'import-woocommerce-screen', 'sendit_woocommerce_screen');
		}
	}

	if ($wpdb->get_var("show tables like 'bb_press'") != '') :
		add_submenu_page(__FILE__, __('email import', 'sendit'), __('Import emails from BBpress', 'sendit'), 8, 'import-bb-users', 'ImportBbPress');
	endif;
	//fixed in 2.1.0 (permission denied)
	if (is_plugin_active('sendit-scheduler/sendit-cron.php')) {
	    //plugin is activated
	    add_submenu_page(__FILE__, __('Cron Settings', 'sendit'), __('cron settings', 'sendit'), 8, 'cron-settings', 'cron_settings');
	}
	else
	{
	    add_submenu_page(__FILE__, __('Cron Settings', 'sendit'), __('cron settings', 'sendit'), 8, 'cron-settings', 'buy_plugin_page');
	}

	/*1.5.7 export addon*/
	if (function_exists('sendit_csv_export'))
	{
		add_submenu_page(__FILE__, __('Export list', 'sendit'), __('Export list', 'sendit'), 8, 'export-subscribers', 'export_subscribers');
	}
	else
	{
		add_submenu_page(__FILE__, __('Export list', 'sendit'), __('Export list', 'sendit'), 8, 'export-subscribers', 'export_subscribers_screen');
	}

	/*2.1.1 template addon*/
	if (is_plugin_active('sendit-pro-template-manager/sendit-pro-template-manager.php'))
	{
		add_submenu_page(__FILE__, __('Email Templates', 'sendit'), __('Newsletter template', 'sendit'), 8, 'sendit_pro_template', 'sendit_pro_template_screen');
	}
	else
	{
		add_submenu_page(__FILE__, __('Email Templates', 'sendit'), __('Newsletter template', 'sendit'), 8, 'sendit_pro_template', 'template_manager_screen');
	}


	/*version check*/
   $sendit_db_version = SENDIT_DB_VERSION;
   $installed_version = get_option('sendit_db_version');
   if($sendit_db_version!=$installed_version)
   {
   	 add_submenu_page(__FILE__, __('Upgrade Sendit', 'sendit'), __('Sendit upgrade', 'sendit'), 8, 'update-sendit', 'sendit_install');
   }

}



function sendit_test_email() {

  if (!current_user_can('manage_options'))  {
    wp_die( __('You do not have sufficient permissions to access this page.') );
  }

  $markup= '<div class="wrap">';
  $markup.= '<div id="icon-options-general" class="icon32"><br /></div>';
  $markup.= '<h2>'.__('Email Testing').'</h2>';
  $markup.='<div class="" id="sendit-banner">
	<span class="main">Send test email</span>
	<span><form action="" method="get">
            <label for="test_email">Email to:</label>
            <input type="text" name="test_email">
            <input type="hidden" name="test_send" value="1">
            <input type="hidden" name="page" value="sendit_test_email">

            <input type="submit" name="submit" class="button-primary" value="'.__('Send Test email', 'sendit').'" />


        </form></span>


</div>';
  $markup.= '<p>'.__('Send to yourself an email to check if your configuration is ok. Just type your email address, send and check').'</p>';

    $headers= "MIME-Version: 1.0\n" .
        "From: ".get_option('admin_email')." <".get_option('admin_email').">\n" .
            "Content-Type: text/html; charset=\"" .
    get_option('blog_charset') . "\"\n";
   // $phpmailer->SMTPDebug = 2;

    if($_GET['test_send']==1):
        $inviata=wp_mail($_GET['test_email'], 'Sendit '.SENDIT_VERSION.' test email: '.get_bloginfo('name'),'testing smtp', $headers);
        $markup.='<div id="message" class="updated fade"><p><strong>'.__('Email Test Sent!', 'sendit').'</strong></p></div>';
    endif;

    $markup.='<h3>Enviroment Settings</h3>';
	$markup.='<ul>';
	$markup.='<li>'.__('SMTP host').': <strong>'.get_option('sendit_smtp_host').'</strong></li>';
	$markup.='<li>'.__('SMTP port').': <strong>'.get_option('sendit_smtp_port').'</strong></li>';
	$markup.='<li>'.__('SMTP authentication').': <strong>'.get_option('sendit_smtp_authentication').'</strong></li>';
	$markup.='<li>'.__('SMTP username').': <strong>'.get_option('sendit_smtp_username').'</strong></li>';
	$markup.='<li>'.__('SMTP password').': <strong>'.get_option('sendit_smtp_password').'</strong></li>';
	$markup.='<li>'.__('SMTP debug').': <strong>'.get_option('sendit_smtp_debug').'</strong></li>';
	$markup.='</ul>';
    $markup.='</div>';

    $markup.='<h3>Cron Settings</h3>';
	$markup.='<ul>';
	$markup.='<li>'.__('Run Tasks every').': <strong>'.get_option('sendit_interval').' seconds</strong></li>';
	$markup.='<li>'.__('Send Blocks of').': <strong>'.get_option('sendit_email_blocks').' recipients</strong></li>';
	$markup.='</ul>';
    $markup.='</div>';


    echo $markup;

}


function subscriber_columns()
{
     /*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     	retrieve columns based on fields
     +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
	 	$columns='';
        $sendit_morefields=get_option('sendit_dynamic_settings');
        $fields = json_decode($sendit_morefields);
		if(!empty($fields)):
	        foreach ($fields as $k => $v):
	         $columns.='<th>'.$v->name.'</th>';
	        endforeach;
		endif;
		return $columns;
}

function subscriber_columns_values($json)
{
     /*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     	custom fields loop and form input auto generation
     +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
	 	$valori=json_decode($json);

	 	$columns='';
        $sendit_morefields=get_option('sendit_dynamic_settings');

        $fields = json_decode($sendit_morefields);
if(!empty($fields)):
        foreach ($fields as $k => $v):
			$columns.= '<td class="">';
			$info_string= $valori->options;
			$explodes=explode("&", $info_string);
				foreach($explodes as $explode):
					$chiave=explode("=", $explode);

						if($chiave[0]==$v->name):
				 			$columns.=$chiave[1];
				 		endif;

				endforeach;
			$columns.= '</td>';
        endforeach;
endif;
		return $columns;

}


function subscriber_options($json)
{
     /*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     	custom fields loop and form input auto generation
     +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/

	$sendit_morefields=get_option('sendit_dynamic_settings');
	$markup='';
	$valori=json_decode($json);
	$info_string= $valori->options;

	$explodes=explode("&", $info_string);

	//print_r($explodes);

	foreach($explodes as $explode):
		$chiave=explode("=", $explode);
		if($chiave[1]!=''):
			if($chiave[0]!='email_add' and $chiave[0]!='lista' ):
				$markup.= $chiave[0];
	 			$markup.=': <strong>'. $chiave[1].'</strong> ';
	 			//$markup.= '<input type="text" name="subscriber_option['.$chiave[0].']" class="'.$v->class.' '.$v->rules.'" value="'.$chiave[1].'">';
	 		endif;
		endif;
	endforeach;

 	//$arr=json_decode($sendit_morefields);
 	//$c = array_combine((array) $explodes, (array) $arr);
 	//print_r($c);
 		return $markup;
}

$themename = "Sendit";
$shortname = "sndt";


function list_sendit_plugins() {
	/*
	The final Hack to check my plugins!
	array (plugin name, path, buy_url, desc,img)
	*/

$siteurl = get_option('siteurl');
$file_dir = $siteurl . '/wp-content/plugins/sendit/';
$pro_plugins = array(

        		array('Sendit Pro Scheduler',
        			  'sendit-scheduler/sendit-cron.php',
        			  'http://senditplugin.com/plugin-shop/sendit-pro-auto-css-inliner/?panel_from_domain='.$siteurl,
        			  'Essential add plugin for mailing list with more than 500/1000 email recipients to avoid spam and help
        			  newsletter delivery scheduling the Job and Tracking newsletter',
        			  $file_dir.'images/scheduler-90x90.jpg',
        			  '20'
        			  ),


    			array('Sendit Pro Template Manager',
        			  'sendit-pro-template-manager/sendit-pro-template-manager.php',
        			  'http://senditplugin.com/?panel_from_domain='.$siteurl,
        			  'Added for free from version 3.0.0 so have fun with ',
        			  $file_dir.'images/template-90x90.png',
        			  'free'
        			  ),


        		array('Sendit Pro Css Inliner',
        			  'sendit-css-inliner/sendit-pro-css-inliner.php',
        			  'http://senditplugin.com/plugin-shop/sendit-pro-auto-css-inliner/?panel_from_domain='.$siteurl,
        			  'Let your reader see the same email! No more timeless Inline css coding, let Sendit Pro Css inliner do for you, added for free from version 3.0.0',
        			  $file_dir.'images/css_inliner-90x90.png',
        			  'free'
        			  ),

        		array('Sendit Pro More Fields',
        			  'sendit-morefields/sendit-morefields.php',
        			  'http://senditplugin.com/plugin-shop/sendit-pro-more-fields/?panel_from_domain='.$siteurl,
        			  'Add Informations field to your widget form simply drag and drop fields (name,city etc). Currently refactoring',
        			  $file_dir.'images/morefields-90x90.jpg',
        			  '5'
        			  ),


        		array('Sendit Pro Export to CsV',
        			  'sendit-export/sendit-export.php',
        			  'http://senditplugin.com/plugin-shop/sendit-pro-csv-list-exporter/?panel_from_domain='.$siteurl,
        			  'Need to export your mailing list for personal purpose or to change plugin? :( Feel free to do it with this tool!',
        			  $file_dir.'images/senditcsv-90x90.jpg',
        			  '5'
        			  ),

        		array('Sendit Pro Woocommerce importer',
        			  'sendit-woocommerce-import/sendit-woocommerce-import.php',
        			  'http://senditplugin.com/plugin-shop/sendit-pro-woocommerce-importer/?panel_from_domain='.$siteurl,
        			  'Track your newsletter campaign by tracking visitors from newsletter with Google Analytics Integration',
        			  $file_dir.'images/scheduler-90x90.jpg',
        			  '5'
        			  ),

        		array('Sendit Pro Analytics Campaign tracker',
        			  'sendit-pro-analytics-campaign/sendit-pro-analytics-campaign.php',
        			  'http://senditplugin.com/plugin-shop/sendit-pro-auto-css-inliner/?panel_from_domain='.$siteurl,
        			  'Track your newsletter campaign by tracking visitors from newsletter with Google Analytics Integration',
        			  $file_dir.'images/scheduler-90x90.jpg',
        			  '5'
        			  ),

        		array('Sendit Premium All in One',
        			  'sendit-premium/sendit-premium-activator.php',
        			  'http://senditplugin.com/plugin-shop/sendit-premium/?panel_from_domain='.$siteurl,
        			  'This is the full Sendit Premium Package at special price of &euro; 35 (save up to 15 &euro;)',
        			  $file_dir.'images/allinone-90x90.png',
        			  '35'
        			  )



        		);



	return $pro_plugins;


}




function options_array()
{

/*
The final Hack to check my plugins!
*/



	$options = array (

array( "name" => $themename." Options",
	"type" => "title"),

//main settings wrapper
array( "name" => "Main Settings","type" => "section"),
array( "type" => "open"),

array( "name" => "Sendit subscribe button text",
	"desc" => "Select the text to display in your subscription button",
	//"id" => $shortname."_color_scheme",
	"id" => 'sendit_subscribe_button_text',
	"type" => "text"),

array( "name" => "Sendit Response Mode",
	"desc" => "Enter the response mode if you want alert or a jquery append style response",
	"id" => 'sendit_response_mode',
	"type" => "select",
	"options" => array("Ajax", "alert"),

	"std" => "Ajax"),



array( "name" => "Show gravatar on subscribers list",
	"desc" => "If enabled avatar will be showed for each subscriber",
	"id" => 'sendit_gravatar',
	"type" => "select",
	"options" => array("yes", "no"),

	"std" => "yes"),




array( "name" => "Subscription widget markup",
	"desc" => "Want to add any custom CSS code? Put in here, and the rest is taken care of. This overrides any other stylesheets. eg: a.button{color:green}",
	"id" => 'sendit_markup',
	"type" => "textarea",
	"std" => ""),





array( "name" => "Custom CSS",
	"desc" => "Want to add any custom CSS code or customize this one? Put in here, and the rest is taken care of. This overrides any other subscription form widget stylesheets. eg: a.button{color:green}.",
	"id" => 'sendit_css',
	"class" => 'black_field',
	"type" => "textarea",
	"std" => ""),

array( "type" => "close"),


//smtp panel

/*
        //new from 1.5.0!!!
        update_option('sendit_sleep_time',$_POST['sendit_sleep_time']);
        update_option('sendit_sleep_each',$_POST['sendit_sleep_each']);
*/
array( "name" => __('SMTP settings', 'sendit'),
	"type" => "section"),
array( "type" => "open"),

array( "name" => __('SMTP Host', 'sendit'),
	"desc" => "Enter your smtp host",
	"id" => 'sendit_smtp_host',
	"type" => "text",
	"std" => ""),

array( "name" => __('SMTP port', 'sendit'),
	"desc" => "Enter your smtp port (es 465)",
	"id" => 'sendit_smtp_port',
	"type" => "text",
	"std" => ""),



array( "name" => __('SMTP display debug informations', 'sendit'),
	"desc" => "Display debug informations",
	"id" => 'sendit_smtp_debug',
	"type" => "select",
	"options"=>array('0','1','2'),
	"std" => get_option('sendit_smtp_debug')),

array( "name" => "Smtp Username",
	"id" => 'sendit_smtp_username',
	"type" => "text",
	"desc" => "Enter your smtp username Required only if your SMTP provider requires authentication",
	"std" => ""),


array( "name" => "Smtp Password",
	"desc" => "",
	"id" => 'sendit_smtp_password',
	"type" => "password",
	"desc" => "Enter your smtp password, required only if your SMTP provider requires authentication",
	"std" => ""),


array( "name" => "Smtp SLL/TLS",
	"id" => 'sendit_smtp_ssl',
	"type" => "select",
	"desc" => 'If SMTP requires a secure connection is required please select one. Are you on panic for large mailing lists, bad delivery (spam etc)?<br><strong>Relax!</strong>Let SendGrid handle your email delivery used with Sendit. Get 25% off any plan by clicking my link.<br><a href="http://sendgrid.tellapal.com/a/clk/3Rv3Ng">http://sendgrid.tellapal.com/a/clk/3Rv3Ng</a><br>SendGrid helps you reach more users instead of spam folders. Click this link to get your 25% discount on your first month membership. Believe me you will be addicted!<br><a href="http://sendgrid.tellapal.com/a/clk/3Rv3Ng">http://sendgrid.tellapal.com/a/clk/3Rv3Ng</a>',
	"options"=>array('','ssl','tls'),
	"std" => ""),

array( "type" => "close"),


array( "name" => "Design Elements",
	"type" => "section"),
array( "type" => "open"),

array( "name" => "Footer copyright text",
	"desc" => "Enter text used in the right side of the footer. html code allowed",
	"id" => 'sendit_footer_text',
	"type" => "text",
	"std" => ""),

array( "name" => "Privacy text",
	"desc" => "You can paste hereyour privacy text that will be displayed in footer.",
	"id" => 'sendit_privacy_text',
	"type" => "textarea",
	"std" => ""),


array( "type" => "close"),

//scheduler panel

array( "name" => "Scheduler Settings",
	"type" => "section"),
array( "type" => "open"),

array( "name" => "",
	"desc" => "Enter text used in the right side of the footer. html code allowed",
	"id" => 'scheduler_panel',
	"type" => "scheduler_panel",
	"std" => ""),



array( "type" => "close"),

//premium panel


array( "name" => "Sendit Plugins Premium",
	"type" => "section"),
array( "type" => "open"),

array( "name" => "Sendit Panel Text",
	"desc" => "Enter text used in the right side of the footer. It can be HTML",
	"id" => $shortname."_footer_text",
	"type" => "plugin_check_list",
	"std" => ""),


array( "type" => "close")



);

return $options;
}


function senditpanel_add_admin() {
$options=options_array();

global $themename, $shortname;

if ( $_GET['page'] == 'sendit_general_settings' ) {

	if ( 'save' == $_REQUEST['action'] ) {
 		//print_r($_POST);
		foreach ($options as $value) {
		//print_r($value);
		update_option( $value['id'], $_REQUEST[ $value['id'] ] ); }

foreach ($options as $value) {
	if( isset( $_REQUEST[ $value['id'] ] ) ) { update_option( $value['id'], stripslashes( $_REQUEST[ $value['id'] ])); } else { delete_option( $value['id'] ); } }

	header("Location: admin.php?page=sendit_general_settings&saved=true");
die;

}
else if( 'reset' == $_REQUEST['action'] ) {

	foreach ($options as $value) {
		delete_option( $value['id'] ); }

	header("Location: admin.php?page=sendit_general_settings&reset=true");
die;

}
}

}

function senditadmin_add_init() {
	wp_enqueue_style("sendit-admin", plugins_url( 'sendit/sendit-admin.css'), false, "3.0", "all");
	wp_enqueue_style("sendit-functions", plugins_url( 'sendit/functions.css'), false, "3.0", "all");

	//wp_enqueue_script("sendit_app", $file_dir."sendit_app.js", false, "3.0");
	wp_enqueue_script("spin",  plugins_url( 'sendit/assets/js/spin.js'), false, "3.0");
	wp_enqueue_script("jqspin", plugins_url( 'sendit/assets/js/jquery.spin.js'), false, "3.0");
	wp_enqueue_script("admin-js", plugins_url( 'sendit/assets/js/admin.js'), false, "3.0");
}


function senditpanel_admin() {

global $themename, $shortname, $options;
$i=0;

$siteurl = get_option('siteurl');
$file_dir = $siteurl . '/wp-content/plugins/sendit/';


if ( $_REQUEST['saved'] ) echo '<div id="message" class="updated fade"><p><strong>'.$themename.' settings saved.</strong></p></div>';
if ( $_REQUEST['reset'] ) echo '<div id="message" class="updated fade"><p><strong>'.$themename.' settings reset.</strong></p></div>';

?>
<div class="wrap rm_wrap">

<h2>Sendit Control Panel</h2>


<div class="" id="sendit-banner">
	<span class="main">Welcome to the new <?php echo $themename; ?> <?php echo SENDIT_VERSION; ?> panel</span>
	<span>Enhance your newsletter plugin by adding pro plugins! Learn more. <a target="_blank" title="Get Sendit" href="http://senditplugin.com/">take a tour</a></span>


</div>



<div class="rm_opts">
<form method="post">
<?php
$options=options_array();

foreach ($options as $value) {
switch ( $value['type'] ) {

case "open":
?>

<?php break;

case "close":
?>

</div>
</div>
<br />


<?php break;

case "title":
?>
<p>To easily use the <?php echo $themename;?> theme, you can use the menu below.</p>


<?php break;

case 'text':
?>

<div class="rm_input rm_text">
	<label for="<?php echo $value['id']; ?>"><?php echo $value['name']; ?></label>
 	<input name="<?php echo $value['id']; ?>" id="<?php echo $value['id']; ?>" type="<?php echo $value['type']; ?>" value="<?php if ( get_option( $value['id'] ) != "") { echo get_option( $value['id']); } else { echo $value['std']; } ?>" />
 <small><?php echo $value['desc']; ?></small>
 <div class="clearfix"></div>

 </div>
<?php
break;

case 'password':
?>

<div class="rm_input rm_text">
	<label for="<?php echo $value['id']; ?>"><?php echo $value['name']; ?></label>
 	<input name="<?php echo $value['id']; ?>" id="<?php echo $value['id']; ?>" type="<?php echo $value['type']; ?>" value="<?php if ( get_option( $value['id'] ) != "") { echo stripslashes(get_option( $value['id'])  ); } else { echo $value['std']; } ?>" />
 <small><?php echo $value['desc']; ?></small><div class="clearfix"></div>

 </div>
<?php
break;


case 'plugin_check_list':





?>

<div class="rm_input rm_text">
<h2>Sendit Control Panel</h2>


<p><i>This is your checkpoint where you can activate and buy additional pro plugins to your Sendit Free Installation</i></p>
	<table>
		<thead>
			<tr>
				<th>Plugin</th>
				<th>Name</th>
				<th>Description</th>
				<th>Price</th>
				<th>Status</th>
			</tr>
		</thead>
		<tbody>
	<?php

	$pro_plugins=list_sendit_plugins();
	foreach($pro_plugins as $plugin)
	 {

	 if (is_plugin_active($plugin[1])) {
	     echo '<tr><td><img class="pluginthumb" width="60" src="'.$plugin[4].'" alt="'.$plugin[0].'"/></td>
	     			<td><a href="'.$plugin[2].'">'.$plugin[0].'</a></td>
	     			<td class="plugin_on"><strong>&euro; '.$plugin[5].'</strong></td>
	     			<td class="plugin_on"><small>'.$plugin[3].'</small></td>
	     			<td class="plugin_on"> <strong>ACTIVE</strong> </td></tr>';
	   }
	  else {
	  	 echo '<tr><td><img class="pluginthumb" width="60" src="'.$plugin[4].'" alt="'.$plugin[0].'"/></td>
	  	 			<td><a href="'.$plugin[2].'">'.$plugin[0].'</a></td>
	     			<td class="plugin_on"><strong>&euro; '.$plugin[5].'</strong></td>
	  	 			<td><small>'.$plugin[3].'</small></td>
	  	 			<td class="plugin_off"><a class="button-primary" href="'.$plugin[2].'">buy now ' .$plugin[0].'</a></td></tr>';
	    }
	 }

	?>
	</tbody>
	</table>

 <div class="clearfix"></div>

 </div>
<?php
break;


case 'scheduler_panel':
?>

<div class="rm_input rm_textarea">
	<label for="<?php echo $value['id']; ?>"><?php echo $value['name']; ?></label>
	<?php
	if (is_plugin_active('sendit-scheduler/sendit-cron.php')) {
		echo cron_settings_panel();
	} else {
		echo buy_plugin($plugin);
	}
	?>

 </div>

<?php
break;


case 'textarea':
?>

<div class="rm_input rm_textarea">
	<label for="<?php echo $value['id']; ?>"><?php echo $value['name']; ?></label>
 	<textarea class="<?php echo $value['class']; ?>" name="<?php echo $value['id']; ?>"><?php if ( get_settings( $value['id'] ) != "") { echo stripslashes(get_settings( $value['id']) ); } else { echo $value['std']; } ?></textarea>
 <small><?php echo $value['desc']; ?></small><div class="clearfix"></div>

 </div>

<?php
break;

case 'select':
?>

<div class="rm_input rm_select">
	<label for="<?php echo $value['id']; ?>"><?php echo $value['name']; ?></label>

<select name="<?php echo $value['id']; ?>" id="<?php echo $value['id']; ?>">
<?php foreach ($value['options'] as $option) { ?>
		<option <?php if (get_settings( $value['id'] ) == $option) { echo 'selected="selected"'; } ?>><?php echo $option; ?></option><?php } ?>
</select>

	<small><?php echo $value['desc']; ?></small><div class="clearfix"></div>
</div>
<?php
break;

case "checkbox":
?>

<div class="rm_input rm_checkbox">
	<label for="<?php echo $value['id']; ?>"><?php echo $value['name']; ?></label>

<?php if(get_option($value['id'])){ $checked = "checked=\"checked\""; }else{ $checked = "";} ?>
<input type="checkbox" name="<?php echo $value['id']; ?>" id="<?php echo $value['id']; ?>" value="true" <?php echo $checked; ?> />


	<small><?php echo $value['desc']; ?></small><div class="clearfix"></div>
 </div>
<?php break;
case "section":

$i++;

?>

<div class="rm_section">
<div class="rm_title"><h3><img src="<?php echo $file_dir; ?>images/trans.png" class="inactive" alt="""><?php echo $value['name']; ?></h3><span class="submit"><input type="hidden" name="action" value="save" /><input class="button-primary" name="save<?php echo $i; ?>" type="submit" value="Save changes" />
</span><div class="clearfix"></div></div>
<div class="rm_options">


<?php break;

}
}
?>


</form>
<form method="post">
<p class="submit">
<input name="reset" class="button-primary" type="submit" value="Reset" />
<input type="hidden" name="action" value="reset" />
</p>
</form>

</div>


<?php
}
?>
<?php
add_action('admin_init', 'senditadmin_add_init');
add_action('admin_menu', 'senditpanel_add_admin');

function buy_plugin()
{ ?>
<div id="premium-panel">
	<span class="main">You don't have Sendit Pro Scheduler installed</span>
<span>Scheduler split delivery process for you using cron jobs <a class="button-primary" href="http://senditplugin.com/plugin-shop/sendit-pro/">Buy Now</a></span>
</div>
<?php }


function buy_plugin_page()
{ ?>
<div class="wrap">
<h2>Sendit Pro Scheduler...</h2>
	<div id="premium-panel">
		<span class="main">Ops! You don't have Sendit Pro Scheduler installed or maybe you forgot to activate!</span>
	<span>Scheduler split delivery process for you using cron jobs <a class="button-primary" href="http://senditplugin.com/plugin-shop/sendit-pro/">Buy Now</a></span>
	</div>
</div>
<?php }
?>
