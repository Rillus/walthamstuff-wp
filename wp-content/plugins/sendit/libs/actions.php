<?php
require('constants.php');

class Actions{

	function NewSubscriber() {
	    global $wpdb;
	    $table_email = SENDIT_EMAIL_TABLE;

	     //messaggio di successo
	     $successo='<p class="sendit-success">'.__('Subscription completed now Check your email and confirm', 'sendit').'</p>';
	     if(get_option('sendit_response_mode')=='alert'):
			$successo=strip_tags($successo);
		endif;
	     //messaggio di errore
	     $errore='<p class="sendit-error">'.__('not valid email address', 'sendit').'</p>';

			 if(get_option('sendit_response_mode')=='alert'):
	     	$errore=strip_tags($errore);
		endif;

	    if(isset($_POST['email_add'])):
	    	//proviamo
	    	$subscriber_info=json_encode($_POST);
	    	//print_r($subscriber_info);

	    	//$subscriber_array=json_decode($subscriber_info);

	    	if (!ereg("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$", $_POST['email_add'])) :
	               echo $errore;
	      	else :

				$lista=esc_attr($_POST['lista']); //security hack suggested this summer
				$lista=(int)$lista;

	            if($this->SubscriberExists($_POST['email_add'],$lista)) :
	                $errore_presente = '<p class="sendit-error">'.__('email address already present', 'sendit').'</p>';
	            		if(get_option('sendit_response_mode')=='alert'):
	            			$errore_presente=strip_tags($errore_presente);
	                	endif;
	                	echo $errore_presente;
	            else :

	                	//genero stringa univoca x conferme sicure
	                	$code = md5(uniqid(rand(), true));
						//2.3.9
						$created_at = date("Y-m-d H:i:s");

						/*+++++++++++++++++++ DB INSERT ***+++++++++++++++++++++++++++++++++++++++++*/
	                 	$wpdb->query("INSERT INTO $table_email (email, id_lista, subscriber_info, magic_string, accepted, created_at) VALUES ('$_POST[email_add]', $lista,'$subscriber_info','$code','n','$created_at')");

	                 	/*qui mando email*/

	                	$table_liste = SENDIT_LIST_TABLE;

	                    $templaterow=$wpdb->get_row("SELECT * from $table_liste where id_lista = $lista ");
	                    //costruisco il messaggio come oggetto composto da $gheader $messagio $ footer

	                    //utile anzi fondamentale
	                    $plugindir   = "sendit/";
	                    $sendit_root = get_option('siteurl') . '/wp-content/plugins/'.$plugindir;
	                    $siteurl = get_option('siteurl');

						/*+++++++++++++++++++ HEADERS EMAIL +++++++++++++++++++++++++++++++++++++++++*/
	                    $headers= "MIME-Version: 1.0\n" .
			        	"From: ".$templaterow->email_lista." <".$templaterow->email_lista.">\n" .
			        	"Content-Type: text/html; charset=\"" .
						get_option('blog_charset') . "\"\n";

						/*+++++++++++++++++++ BODY EMAIL ++++++++++++++++++++++++++++++++++++++++++++*/
	                    $header= $templaterow->header;
	                    $welcome = __('Welcome to newsletter by: ', 'sendit').get_bloginfo('blog_name');
	                    $messaggio= "<h3>".$welcome."</h3>";
	                    $messaggio.=__('To confirm your subscription please follow this link', 'sendit').':<br />
	                    <a href="'.get_option('siteurl').'/?action=confirm&c='.$code.'">'.__('Confirm here', 'sendit').'</a>';
	                    $footer= $templaterow->footer;
	                    $content_send = $header.$messaggio.$footer;
						/*+++++++++++++++++++ FINE BODY EMAIL ++++++++++++++++++++++++++++++++++++++++*/


						/*+++++++++++++++++++ invio email ++++++++++++++++++++++++++++++++++++++++++++*/
						if(wp_mail($_POST['email_add'], $welcome ,$content_send, $headers, $attachments)):
	                         //admin notification (notifica nuova iscrizione all email admin)
	                         wp_mail($templaterow->email_lista, __('New subscriber for your newsletter:', 'sendit').' '.$_POST['email_add'].' '.get_bloginfo('blog_name'), __('New subscriber for your newsletter: '.$_POST['email_add'], 'sendit').get_bloginfo('blog_name'));
	                         echo $successo;
	                     else :
	                         echo $errore;
	                     endif;

	                endif;

	            endif;

	    endif;


	}


	function ConfirmSubscriber() {


    global $_GET;
	global $wpdb;

    $table_email = SENDIT_EMAIL_TABLE;

    if($_GET['action']=="confirm"):

    		if(!$this->SubscriberExists('','',$_GET['c'])) :
    			echo '<center>
						<h3>'.get_bloginfo('name').' Newsletter</h3>
    					<p class="sendit-error">'.__('Indirizzo email non presente o qualcosa non sta funzionando!','sendit').'</p>
    				  </center>';
    		else :

	 			$wpdb->query("UPDATE $table_email set accepted='y' where magic_string = '$_GET[c]'");
				$table_liste = SENDIT_LIST_TABLE;
				$templaterow=$wpdb->get_row("SELECT * from $table_liste where id_lista = '$_GET[lista]' ");
				$plugindir   = "sendit/";
				$sendit_root = get_option('siteurl') . '/wp-content/plugins/'.$plugindir;

			/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
			 * QUI potete ridisegnare il vs TEMA da libs/frontend
			 +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
				echo '<center>
						<h3>'.get_bloginfo('name').' Newsletter</h3>
						<p class="sendit-success">'.__("Thank you for subscribe our newsletter!", "sendit").'<br />'.__("you will be updated", "sendit").'</p>
					</center>';

			endif;

    endif;


	}

	function Unsubscribe() {
		global $_GET;
		global $wpdb;

    $table_email = $wpdb->prefix . "nl_email";

	    if($_GET['action']=="unsubscribe"):

	    	$user_count = $wpdb->get_var("SELECT COUNT(*) FROM $table_email where magic_string ='$_GET[c]';");

	    		if($user_count<1) :
	    			echo "<center><p class=\"sendit-error\">".__('email address not present or something is going wrong?!', 'sendit')."</p></center>";
	    		else :

		 			$wpdb->query("DELETE from $table_email where magic_string = '$_GET[c]'");
					$table_liste = $wpdb->prefix . "nl_liste";

						 $templaterow=$wpdb->get_row("SELECT * from $table_liste where id_lista = '$_GET[lista]' ");


						//utile anzi fondamentale
						$plugindir   = "sendit/";
						$sendit_root = get_option('siteurl') . '/wp-content/plugins/'.$plugindir;


			/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
			 * QUI potete ridisegnare il vs TEMA da libs/frontend
			 +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
				echo '<center>
						<h3>'.get_bloginfo('name').' Newsletter</h3>
						<p class="sendit-success">'.__("Your email address was deleted succesfully from our mailing list!", "sendit").'</p>
					</center>';

				endif;

	    endif;

	}

	function GetSubscribers($id_lista)
	{
		global $wpdb;
    	$table_email = SENDIT_EMAIL_TABLE;
		$subscribers=$wpdb->get_results("select * from $table_email where id_lista = '".$id_lista."' and accepted='y'");
		return $subscribers;
	}

	function GetAllSubscribers()
	{
		global $wpdb;
    	$table_email = SENDIT_EMAIL_TABLE;
		$subscribers=$wpdb->get_results("select * from $table_email");
		return $subscribers;
	}

	function GetSubscriberbyId($id)
	{
		global $wpdb;
    	$table_email = SENDIT_EMAIL_TABLE;
		$subscriber=$wpdb->get_row("select * from $table_email where id_email = $id");
		return $subscriber;
	}

	function SubscriberExists($email='',$lista='',$code='')
	{
		global $wpdb;
    	$table_email = SENDIT_EMAIL_TABLE;

    	if($code!=''):
    		//used for confirmation by code
			$user_count=$wpdb->get_var("SELECT COUNT(*) FROM $table_email where magic_string ='$_GET[c]';");
		else:
			//used for subscription check
			$user_count=$wpdb->get_var("SELECT COUNT(*) FROM $table_email where email = '$email' and id_lista = $lista;");
		endif;

		if($user_count>0):
			return true;
		endif;
	}


	function ChangeStatus($id,$status)
	{
		global $wpdb;
		$table_email = $wpdb->prefix . "nl_email";
   		$update=$wpdb->query("update $table_email set accepted = '$status' where id_email = $id");
		return true;
	}


	function is_expired($code)
	{
		global $wpdb;
		$table_request = $wpdb->prefix . "request";
		$now=$wpdb->get_var("select SYSDATE()");
		$request=$this->GetrequestbyCode($code);
		$diff=$wpdb->get_var("SELECT TIMEDIFF('$now','$request->quoted_date')");
		if($diff>'12:00:00'):
			return true;
		endif;
	}


	function GetListDetail($id_lista)
	{
		global $wpdb;
    	$table_liste = SENDIT_LIST_TABLE;
		$lista=$wpdb->get_row("select * from $table_liste where id_lista = '".$id_lista."'");
		return $lista;
	}


}
?>
