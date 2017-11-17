<?php
/*
Plugin Name: Sendit
Plugin URI: http://www.giuseppesurace.com/sendit-wp-newsletter-mailing-list/
Description: Wordpress newsletter plugin. Sendit is a friendly and easy newsletter and mailing lists plugin for WordPress, born to make newsletter delivery management a great experience.
Version: 2.4.4
Author: Giuseppe Surace
Author URI: http://senditplugin.com
*/

include_once plugin_dir_path( __FILE__ ).'libs/install-core.php';
include_once plugin_dir_path( __FILE__ ).'libs/actions.php';
include_once plugin_dir_path( __FILE__ ).'libs/markup.php';
include_once plugin_dir_path( __FILE__ ).'libs/admin/subscribers-list.php';
include_once plugin_dir_path( __FILE__ ).'libs/admin/admin-core.php';
include_once plugin_dir_path( __FILE__ ).'libs/extensions-handler.php';
include_once plugin_dir_path( __FILE__ ).'libs/import.php';
include_once plugin_dir_path( __FILE__ ).'libs/add-on/template-manager.php';
include_once plugin_dir_path( __FILE__ ).'libs/add-on/css-inliner.php';

//new folder filesystem pre setup
include_once plugin_dir_path( __FILE__ ).'libs/admin/meta-boxes.php';
include_once plugin_dir_path( __FILE__ ).'libs/frontend/frontend.php';
include_once plugin_dir_path( __FILE__ ).'libs/admin/migrations.php';

load_plugin_textdomain('sendit', false, basename(dirname(__FILE__)) . '/languages'); //thanks to Davide http://www.jqueryitalia.org

register_activation_hook( __FILE__, 'sendit_install' );
register_activation_hook( __FILE__, 'sendit_sampledata');

/* Display a notice that can be dismissed */

add_action('admin_notices', 'sendit_admin_notice');

function sendit_admin_notice() {
   global $sendit_db_version;

   $sendit_db_version = SENDIT_DB_VERSION;
   $installed_version = get_option('sendit_db_version');
    global $current_user ;
        $user_id = $current_user->ID;
        /* Check that the user hasn't already clicked to ignore the message */

    if ($sendit_db_version!=$installed_version) {
        echo '<div class="updated"><h2>Warning!</h2>';
        printf(__('You need to run Update of Sendit plugin table structure NOW!! | <a href="admin.php?page=update-sendit&upgrade_from_box=1">Click here to run process &raquo;</a>'), '');
        echo "</p></div>";
    }

    else

    {
    	if ( ! get_user_meta($user_id, 'sendit_ignore') ) {
        	echo '<div class="updated"><p>';
        	printf(__('Your Sendit database table structure is currently updated to latest version '.SENDIT_DB_VERSION.' | <a href="%1$s">Hide this Notice</a>'), admin_url( 'admin.php?page=sendit/libs/admin/admin-core.php&sendit_ignore=0'));
        	echo "</p></div>";
    	}
    }






}

add_action('admin_init', 'sendit_ignore');

function sendit_ignore() {
    global $current_user;
        $user_id = $current_user->ID;
        /* If user clicks to ignore the notice, add that to their user meta */
        if ( isset($_GET['sendit_ignore']) && '0' == $_GET['sendit_ignore'] ) {
             add_user_meta($user_id, 'sendit_ignore', 'true', true);
    }
}


add_action('admin_notices', 'sendit_cron_notice');

function sendit_cron_notice() {
   global $sendit_db_version;

   $sendit_db_version = SENDIT_DB_VERSION;
   $installed_version = get_option('sendit_db_version');
    global $current_user ;
        $user_id = $current_user->ID;
        /* Check that the user hasn't already clicked to ignore the message */

    include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	if(!is_plugin_active('sendit-scheduler/sendit-cron.php') &&  !get_user_meta($user_id, 'sendit_cron_ignore')){
        echo '<div class="updated"><h3>'.__('It\'s time to upgrade your Sendit plugin to PRO version and schedule your newsletter', 'sendit').'</h3>
				<span>'.__('Scheduling newsletters it is important to automate this process and send block of emails to avoid spam and hosting rate limits.','sendit').' <a target="_blank" title="Get Sendit" href="http://senditplugin.com/plugin-shop/sendit-pro/?utm_source=banner_pro&utm_medium=plugin&utm_campaign=sendit_231" class="button-primary">Buy Now Sendit Scheduler</a> | <a class="button-secondary" href="admin.php?page=sendit/libs/admin/admin-core.php&sendit_cron_ignore=0">Hide Notice</a></span>';

        echo "</p></div>";
    }

    else

    {
    	if ( ! get_user_meta($user_id, 'sendit_cron_ignore') ) {
        	echo '<div class="updated"><h3>Congratulations!</h3><p>';
        	printf(__('You have Sendit Pro Scheduler, start here and setup your <a class="button-primary" href="admin.php?page=cron-settings">cron jobs</a> | <a href="%1$s">Hide this Notice</a>'), admin_url( 'admin.php?page=sendit/libs/admin/admin-core.php&sendit_cron_ignore=0'));
        	echo "</p></div>";
    	}
    }






}

add_action('admin_init', 'sendit_cron_ignore');

function sendit_cron_ignore() {
    global $current_user;
        $user_id = $current_user->ID;
        /* If user clicks to ignore the notice, add that to their user meta */
        if ( isset($_GET['sendit_cron_ignore']) && '0' == $_GET['sendit_cron_ignore'] ) {
             add_user_meta($user_id, 'sendit_cron_ignore', 'true', true);
    }
}


add_action('wp_head', 'sendit_register_head');
add_action('plugins_loaded','DisplayForm');
add_action('admin_menu', 'gestisci_menu');

add_action('init', 'sendit_custom_post_type_init');
add_action('save_post', 'sendit_save_postdata');

add_action('save_post', 'send_newsletter');

?>
