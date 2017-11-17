<?php
/*******************************
Installation core
*******************************/
require('constants.php');
global $sendit_db_version;
global $wpdb;



function sendit_install() {
   global $_GET;
   global $wpdb;
   global $sendit_db_version;
   $sendit_db_version = SENDIT_DB_VERSION;
   $installed_version = get_option('sendit_db_version');
	/*
	++++++++++++++++++++++++++++
	Table: wp_nl_email
	++++++++++++++++++++++++++++
	*/
   $table_email = $wpdb->prefix . "nl_email";
   $table_liste = $wpdb->prefix . "nl_liste";

   if($sendit_db_version!=$installed_version) {

   $sql_email = "CREATE TABLE " . SENDIT_EMAIL_TABLE . " (
	  		  	id_email int(11) NOT NULL AUTO_INCREMENT,
              	id_lista  int(11) default '1',
              	contactname varchar(250) default NULL,
              	email varchar(250) default NULL,
              	subscriber_info text default NULL,
              	created_at DATETIME NULL,
              	updated_at DATETIME NULL,
              	magic_string varchar(250) default NULL,
              	accepted varchar(1) default 'n',
              	post_id mediumint(9) NULL,
              	ipaddress VARCHAR(255)   NULL,

               PRIMARY KEY  (`id_email`),
                           KEY `id_lista` (`id_lista`)
    );";
     update_option("sendit_db_version", $sendit_db_version);


	/*
	++++++++++++++++++++++++++++
	Table: wp_nl_liste
	++++++++++++++++++++++++++++
	*/
    $sql_liste = "CREATE TABLE ".SENDIT_LIST_TABLE." (
                  `id_lista` int(11) NOT NULL auto_increment,
                  `nomelista` varchar(250) default NULL,
                  `email_lista` varchar(250) default NULL,
                  `header` mediumtext NULL,
                  `footer` mediumtext NULL,
                  `list_parent` int(11) default '0',
                   PRIMARY KEY  (`id_lista`)
                 );";

   //splitter
   $sql_parent="ALTER TABLE ". SENDIT_LIST_TABLE ." add column list_parent int(11) default '0'";

   $sql_alter="ALTER TABLE ". SENDIT_EMAIL_TABLE ." add column subscriber_info text default NULL";





   require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

   dbDelta($sql_email);
   dbDelta($sql_liste);
   dbDelta($sql_parent);
   dbDelta($sql_alter);

   $init_html='<!-- Start Sendit Subscription form -->
     <div class="sendit">
		<form class="form-sendit" id="senditform">
			<!-- the shortcode to generate subscription fields -->
	        {sendit_morefields}
			<input type="text" class="form-sendit" name="email_add" id="email_add" placeholder="email">
			<input type="hidden" name="lista" id="lista" value="{list_id}">
			<button type="submit" class="btn-sendit" name="submit" id="sendit_subscribe_button" value="{subscribe_text}">Subscribe</button>
		</form>
	</div>';


	$init_css='
	.sendit{
     width:99%;
		}
	input.form-sendit{
		width:100%;
		padding:5px;
		}
	';

	if(get_option('sendit_markup')=='') update_option('sendit_markup', $init_html);
	if(get_option('sendit_css')=='') update_option('sendit_css', $init_css);
	if(get_option('sendit_subscribe_button_text')=='') update_option('sendit_subscribe_button_text', 'subscribe');
	if(get_option('sendit_response_mode')=='') update_option('sendit_response_mode', 'ajax');
	if(get_option('sendit_unsubscribe_link')=='') update_option('sendit_unsubscribe_link', 'yes');
	if(get_option('sendit_gravatar')=='') update_option('sendit_gravatar', 'yes');

	if($_GET['upgrade_from_box']==1):
        	echo '<div class="updated"><h2>';
        	printf(__('Your Sendit Database table Structure is succesfully updated to version: '.SENDIT_DB_VERSION.' | <a href="%1$s">Hide this Notice and get started! &raquo;</a>'), admin_url( 'admin.php?page=sendit/libs/admin.php&sendit_ignore=0'));
        	echo "</h2></div>";
  	endif;

  }

}







function sendit_sampledata() {
   	/*
	++++++++++++++++++++++++++++
	inserimento lista 1 di test con dati di prova
	++++++++++++++++++++++++++++
	*/
    global $wpdb;
    $header_default='<h1>'.get_option('blogname').'</h1>';
    $header_default.='<h2>newsletter</h2>';
    $footer_default='<p><a href="http://senditplugin.com">'.__('Newsletter sent by Sendit Wordpress plugin').'</a></p>';

    $rows_affected = $wpdb->insert(SENDIT_LIST_TABLE, array('nomelista' => 'Testing list','email_lista' => get_bloginfo('admin_email'), 'header' =>$header_default, 'footer'=>$footer_default) );
}
?>
