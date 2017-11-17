<?php
/*
This help who cant copy on fly the template files for custom post type!!
*/

//Template fallback
add_action("template_redirect", 'sendit_theme_redirect');

function sendit_theme_redirect() {
    global $wp;
    $plugindir = dirname( __FILE__ );

    //A Specific Custom Post Type
    if ($wp->query_vars["post_type"] == 'newsletter') {
        $templatefilename = 'single-newsletter.php';
        if (file_exists(TEMPLATEPATH . '/' . $templatefilename)) {
            $return_template = TEMPLATEPATH . '/' . $templatefilename;
        } else {
            $return_template = $plugindir . '/' . $templatefilename;
        }
        do_sendit_redirect($return_template);
    }
    
    elseif($wp->query_vars["post_type"] == 'sendit_template')
    
    {
        $templatefilename = 'single-sendit_template.php';
        if (file_exists(TEMPLATEPATH . '/' . $templatefilename)) {
            $return_template = TEMPLATEPATH . '/' . $templatefilename;
        } else {
            $return_template = $plugindir . '/' . $templatefilename;
        }
        do_sendit_redirect($return_template);
    
    
    }
}

function do_sendit_redirect($url) {
    global $post, $wp_query;
    if (have_posts()) {
        include($url);
        die();
    } else {
        $wp_query->is_404 = true;
    }
}

/*--------------------------------------------------------------
New sendit CONFIRMATION REDIRECT IN PAGE
--------------------------------------------------------------*/
add_action("template_redirect", 'sendit_confirmation_redirect');

function sendit_confirmation_redirect() {
    global $wp;
    $plugindir = dirname( __FILE__ );

    //A Specific Custom Post Type
    if ($_GET['action']=='confirm') {
        $templatefilename = 'sendit-confirmation.php';
        if (file_exists(TEMPLATEPATH . '/' . $templatefilename)) {
            $return_template = TEMPLATEPATH . '/' . $templatefilename;
        } else {
            $return_template = $plugindir . '/' . $templatefilename;
        }
        do_sendit_confirmation_redirect($return_template);
    }

}

function do_sendit_confirmation_redirect($url) {
    global $post, $wp_query;
    if (have_posts()) {
        include($url);
        die();
    } else {
        $wp_query->is_404 = true;
    }
}


/*--------------------------------------------------------------
New sendit UNSUBSCRIBE REDIRECT IN PAGE
--------------------------------------------------------------*/
add_action("template_redirect", 'sendit_unsubscribe_redirect');

function sendit_unsubscribe_redirect() {
    global $wp;
    $plugindir = dirname( __FILE__ );

    //A Specific Custom Post Type
    if ($_GET['action']=='unsubscribe') {
        $templatefilename = 'sendit-unsubscribe.php';
        if (file_exists(TEMPLATEPATH . '/' . $templatefilename)) {
            $return_template = TEMPLATEPATH . '/' . $templatefilename;
        } else {
            $return_template = $plugindir . '/' . $templatefilename;
        }
        do_sendit_unsubscribe_redirect($return_template);
    }

}

function do_sendit_unsubscribe_redirect($url) {
    global $post, $wp_query;
    if (have_posts()) {
        include($url);
        die();
    } else {
        $wp_query->is_404 = true;
    }
}



?>