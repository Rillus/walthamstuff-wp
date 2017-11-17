<?php
/*
Plugin Name: Custom List Table Example
Plugin URI: http://www.mattvanandel.com/
Description: A highly documented plugin that demonstrates how to create custom List Tables using official WordPress APIs.
Version: 1.4.1
Author: Matt van Andel
Author URI: http://www.mattvanandel.com
License: GPL2
*/
/*  Copyright 20120  Matthew Van Andel  (email : matt@mattvanandel.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 201 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/



/* == NOTICE ===================================================================
 * Please do not alter this file. Instead: make a copy of the entire plugin,
 * rename it, and work inside the copy. If you modify this plugin directly and
 * an update is released, your changes will be lost!
 * ========================================================================== */



/*************************** LOAD THE BASE CLASS *******************************
 *******************************************************************************
 * The WP_List_Table class isn't automatically available to plugins, so we need
 * to check if it's available and load it if necessary. In this tutorial, we are
 * going to use the WP_List_Table class directly from WordPress core.
 *
 * IMPORTANT:
 * Please note that the WP_List_Table class technically isn't an official API,
 * and it could change at some point in the distant future. Should that happen,
 * I will update this plugin with the most current techniques for your reference
 * immediately.
 *
 * If you are really worried about future compatibility, you can make a copy of
 * the WP_List_Table class (file path is shown just below) to use and distribute
 * with your plugins. If you do that, just remember to change the name of the
 * class to avoid conflicts with core.
 *
 * Since I will be keeping this tutorial up-to-date for the foreseeable future,
 * I am going to work with the copy of the class provided in WordPress core.
 */
if(!class_exists('WP_List_Table')){
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}




/************************** CREATE A PACKAGE CLASS *****************************
 *******************************************************************************
 * Create a new list table package that extends the core WP_List_Table class.
 * WP_List_Table contains most of the framework for generating the table, but we
 * need to define and override some methods so that our data can be displayed
 * exactly the way we need it to be.
 *
 * To display this example on a page, you will first need to instantiate the class,
 * then call $yourInstance->prepare_items() to handle any data manipulation, then
 * finally call $yourInstance->display() to render the table to the page.
 *
 * Our theme for this list table is going to be movies.
 */
class Sendit_List_Table extends WP_List_Table {

    /** ************************************************************************
     * REQUIRED. Set up a constructor that references the parent constructor. We
     * use the parent reference to set some default configs.
     ***************************************************************************/
    function __construct(){
        global $status, $page;

        //Set parent defaults
        parent::__construct( array(
            'singular'  => 'subscriber',     //singular name of the listed records
            'plural'    => 'subscribers',    //plural name of the listed records
            'ajax'      => false        //does this table support ajax?
        ) );

    }


    /** ************************************************************************
     * Recommended. This method is called when the parent class can't find a method
     * specifically build for a given column. Generally, it's recommended to include
     * one method for each column you want to render, keeping your package class
     * neat and organized. For example, if the class needs to process a column
     * named 'title', it would first see if a method named $this->column_title()
     * exists - if it does, that method will be used. If it doesn't, this one will
     * be used. Generally, you should try to use custom column methods as much as
     * possible.
     *
     * Since we have defined a column_title() method later on, this method doesn't
     * need to concern itself with any column with a name of 'title'. Instead, it
     * needs to handle everything else.
     *
     * For more detailed insight into how columns are handled, take a look at
     * WP_List_Table::single_row_columns()
     *
     * @param array $item A singular item (one full row's worth of data)
     * @param array $column_name The name/slug of the column to be processed
     * @return string Text or HTML to be placed inside the column <td>
     **************************************************************************/
    function column_default($item, $column_name){
        switch($column_name){
            case 'email':
            case 'accepted':
                return $item[$column_name];
            default:
                return print_r($item,true); //Show the whole array for troubleshooting purposes
        }
    }


    /** ************************************************************************
     * Recommended. This is a custom column method and is responsible for what
     * is rendered in any column with a name/slug of 'title'. Every time the class
     * needs to render a column, it first looks for a method named
     * column_{$column_title} - if it exists, that method is run. If it doesn't
     * exist, column_default() is called instead.
     *
     * This example also illustrates how to implement rollover actions. Actions
     * should be an associative array formatted as 'slug'=>'link html' - and you
     * will need to generate the URLs yourself. You could even ensure the links
     *
     *
     * @see WP_List_Table::::single_row_columns()
     * @param array $item A singular item (one full row's worth of data)
     * @return string Text to be placed inside the column <td> (movie title only)
     **************************************************************************/
    function column_email($item){

        //Build row actions
        $actions = array(
            'edit'      => sprintf('<a href="'.get_bloginfo('siteurl').'/wp-admin/admin-ajax.php?action=edit_contact&width=350&height=250&page=%s&id=%s" class="thickbox">Edit contact</a>',$_REQUEST['page'],$item['ID']),
            'delete'    => sprintf('<a href="'.get_bloginfo('siteurl').'/wp-admin/admin-ajax.php?action=delete_contact&width=350&height=250&page=%s&id=%s" class="thickbox delete_contact">Delete contact</a>',$_REQUEST['page'],$item['ID']),
        );

        //Return the title contents
        return sprintf('<span id="sendit-contact-%2$s">%1$s</span> <span style="color:silver">(id:%2$s)</span>%3$s',
            /*$1%s*/ $item['email'],
            /*$2%s*/ $item['ID'],
            /*$3%s*/ $this->row_actions($actions)
        );
    }

    function column_accepted($item){

        //Build row actions
        $actions = array(
            'edit'      => sprintf('<a href="'.get_bloginfo('siteurl').'/wp-admin/admin-ajax.php?action=edit_contact&width=350&height=250&page=%s&id=%s" class="thickbox">Edit contact</a>',$_REQUEST['page'],$item['ID']),
            'delete'    => sprintf('<a href="'.get_bloginfo('siteurl').'/wp-admin/admin-ajax.php?action=delete_contact&width=350&height=250&page=%s&id=%s" class="thickbox">Delete contact</a>',$_REQUEST['page'],$item['ID']),
        );

        //Return the title contents
        return sprintf('<span id="sendit-contact-status-%2$s">%1$s</span>',
            /*$1%s*/ $item['accepted'],
            /*$2%s*/ $item['ID']
            /*$3%s*/ //$this->row_actions($actions)
        );
    }


    /** ************************************************************************
     * REQUIRED if displaying checkboxes or using bulk actions! The 'cb' column
     * is given special treatment when columns are processed. It ALWAYS needs to
     * have it's own method.
     *
     * @see WP_List_Table::::single_row_columns()
     * @param array $item A singular item (one full row's worth of data)
     * @return string Text to be placed inside the column <td> (movie title only)
     **************************************************************************/
    function column_cb($item){
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            /*$1%s*/ $this->_args['singular'],  //Let's simply repurpose the table's singular label ("movie")
            /*$2%s*/ $item['ID']                //The value of the checkbox should be the record's id
        );
    }


    /** ************************************************************************
     * REQUIRED! This method dictates the table's columns and titles. This should
     * return an array where the key is the column slug (and class) and the value
     * is the column's title text. If you need a checkbox for bulk actions, refer
     * to the $columns array below.
     *
     * The 'cb' column is treated differently than the rest. If including a checkbox
     * column in your table you must create a column_cb() method. If you don't need
     * bulk actions or checkboxes, simply leave the 'cb' entry out of your array.
     *
     * @see WP_List_Table::::single_row_columns()
     * @return array An associative array containing column information: 'slugs'=>'Visible Titles'
     **************************************************************************/
    function get_columns(){
        $columns = array(
            'cb'        => '<input type="checkbox" />', //Render a checkbox instead of text
            'email'     => 'Email',
            'accepted'    => 'Confirmed'
        );
        return $columns;
    }



    function get_sendit_lists() {
        global $wpdb;
        $table_name   = $wpdb->prefix . "nl_liste";

        $orderby = ( ! empty( $_GET['orderby'] ) ) ? $_GET['orderby'] : 'id_lista';
        $order = ( ! empty($_GET['order'] ) ) ? $_GET['order'] : 'asc';
        $sql = "SELECT id_lista, email_lista, nomelista FROM $table_name ORDER BY $orderby $order";

        $lists = $wpdb->get_results($sql,ARRAY_A);
        return $lists;
    }




    function get_registered_subscribers() {
        global $wpdb;
        $table_name   = $wpdb->prefix . "nl_email";

        $orderby = ( ! empty( $_GET['orderby'] ) ) ? $_GET['orderby'] : 'email';
        $order = ( ! empty($_GET['order'] ) ) ? $_GET['order'] : 'asc';

        $list = ( ! empty( $_GET['lista'] ) ) ? ' where id_lista = '.$_GET['lista'] : '';

        $search = ( ! empty($_GET['s'] ) ) ? $_GET['s'] : '';

        if(!empty($search)){
            $sql = "SELECT id_email as ID, email, accepted FROM $table_name where email like '%$search%' ORDER BY $orderby $order";
        } else {
            $sql = "SELECT id_email as ID, email, accepted FROM $table_name $list ORDER BY $orderby $order";
        }


        $emails = $wpdb->get_results($sql,ARRAY_A);

        return $emails;
    }



    /** ************************************************************************
     * Optional. If you want one or more columns to be sortable (ASC/DESC toggle),
     * you will need to register it here. This should return an array where the
     * key is the column that needs to be sortable, and the value is db column to
     * sort by. Often, the key and value will be the same, but this is not always
     * the case (as the value is a column name from the database, not the list table).
     *
     * This method merely defines which columns should be sortable and makes them
     * clickable - it does not handle the actual sorting. You still need to detect
     * the ORDERBY and ORDER querystring variables within prepare_items() and sort
     * your data accordingly (usually by modifying your query).
     *
     * @return array An associative array containing all the columns that should be sortable: 'slugs'=>array('data_values',bool)
     **************************************************************************/
    function get_sortable_columns() {
        $sortable_columns = array(
            'email'     => array('email',false),     //true means it's already sorted
            'accepted'    => array('accepted',false)
        );
        return $sortable_columns;
    }


    /** ************************************************************************
     * Optional. If you need to include bulk actions in your list table, this is
     * the place to define them. Bulk actions are an associative array in the format
     * 'slug'=>'Visible Title'
     *
     * If this method returns an empty value, no bulk action will be rendered. If
     * you specify any bulk actions, the bulk actions box will be rendered with
     * the table automatically on display().
     *
     * Also note that list tables are not automatically wrapped in <form> elements,
     * so you will need to create those manually in order for bulk actions to function.
     *
     * @return array An associative array containing all the bulk actions: 'slugs'=>'Visible Titles'
     **************************************************************************/
    function get_bulk_actions() {
        $actions = array(
            'delete'    => 'Delete',
            'sublist'    => 'Create a sublist from selected address'
        );
        return $actions;
    }


    /** ************************************************************************
     * Optional. You can handle your bulk actions anywhere or anyhow you prefer.
     * For this example package, we will handle it in the class to keep things
     * clean and organized.
     *
     * @see $this->prepare_items()
     **************************************************************************/
    function process_bulk_action() {
        global $wpdb;
        $list_table   = $wpdb->prefix . "nl_liste";
        $email_table = $wpdb->prefix . "nl_email";
        //Detect when a bulk action is being triggered...
        if( 'delete'===$this->current_action() ) {
            //wp_die('Items deleted (or they would be if we had items to delete)!');
        		$id_emails = implode(",", $_GET['subscriber']);
                //echo $id_emails;
                   $delete=$wpdb->query("delete from $email_table where id_email in ($id_emails)");
                   echo '<div id="message" class="updated fade"><p><strong>'.__("Email deleted succesfully!", "sendit").'</strong></p></div>';
        }

        if('sublist'===$this->current_action()) {

          	//echo $_GET['lista'];
              //$code = md5(uniqid(rand(), true));
              $id_emails = implode(",", $_GET['subscriber']);
              //echo $id_emails;

              $emails=$wpdb->get_results("select * from $email_table where id_email in ($id_emails)");
              //print_r($emails);

          if(count($emails)>0):
            $parent_list = $emails[0]->id_lista;
      		  $newlist = $wpdb->insert(SENDIT_LIST_TABLE, array('list_parent' => $parent_list, 'nomelista' => 'Sublist '.$parent_list.' segmented', 'email_lista' => get_bloginfo('admin_email'), 'header' =>$header_default, 'footer'=>$footer_default) );
      		  $newlist_id=$wpdb->insert_id;

      		foreach($emails as $email):
      	      $code = md5(uniqid(rand(), true));
      	 			$insert=$wpdb->query("INSERT INTO $email_table (email,id_lista, magic_string, accepted) VALUES ('$email->email', $newlist_id, '$code', 'y')");
      		endforeach;
                //print_r($emails);

                 //$update=$wpdb->query("update $table_email set email = '$_POST[email]', magic_string='$_POST[code]', accepted = '$_POST[status]' where id_email = '$_POST[id_email]'");

                 echo '<div id="message" class="updated fade"><p><strong>'.__('bo', 'sendit').'</p></div>';
          endif;
        }


    }


    /** ************************************************************************
     * REQUIRED! This is where you prepare your data for display. This method will
     * usually be used to query the database, sort and filter the data, and generally
     * get it ready to be displayed. At a minimum, we should set $this->items and
     * $this->set_pagination_args(), although the following properties and methods
     * are frequently interacted with here...
     *
     * @global WPDB $wpdb
     * @uses $this->_column_headers
     * @uses $this->items
     * @uses $this->get_columns()
     * @uses $this->get_sortable_columns()
     * @uses $this->get_pagenum()
     * @uses $this->set_pagination_args()
     **************************************************************************/
    function prepare_items() {
        global $wpdb; //This is used only if making any database queries

        /**
         * First, lets decide how many records per page to show
         */
        $per_page = 20;


        /**
         * REQUIRED. Now we need to define our column headers. This includes a complete
         * array of columns to be displayed (slugs & titles), a list of columns
         * to keep hidden, and a list of columns that are sortable. Each of these
         * can be defined in another method (as we've done here) before being
         * used to build the value for our _column_headers property.
         */
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();


        /**
         * REQUIRED. Finally, we build an array to be used by the class for column
         * headers. The $this->_column_headers property takes an array which contains
         * 3 other arrays. One for all columns, one for hidden columns, and one
         * for sortable columns.
         */
        $this->_column_headers = array($columns, $hidden, $sortable);


        /**
         * Optional. You can handle your bulk actions however you see fit. In this
         * case, we'll handle them within our package just to keep things clean.
         */
        $this->process_bulk_action();


        /**
         * Instead of querying a database, we're going to fetch the example data
         * property we created for use in this plugin. This makes this example
         * package slightly different than one you might build on your own. In
         * this example, we'll be using array manipulation to sort and paginate
         * our data. In a real-world implementation, you will probably want to
         * use sort and pagination data to build a custom query instead, as you'll
         * be able to use your precisely-queried data immediately.
         */
        $data = $this->get_registered_subscribers();


        /**
         * This checks for sorting input and sorts the data in our array accordingly.
         *
         * In a real-world situation involving a database, you would probably want
         * to handle sorting by passing the 'orderby' and 'order' values directly
         * to a custom query. The returned data will be pre-sorted, and this array
         * sorting technique would be unnecessary.
         */
        function usort_reorder($a,$b){
            $orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'email'; //If no sort, default to title
            $order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'asc'; //If no order, default to asc
            $result = strcmp($a[$orderby], $b[$orderby]); //Determine sort order
            return ($order==='asc') ? $result : -$result; //Send final sort direction to usort
        }
        usort($data, 'usort_reorder');


        /***********************************************************************
         * ---------------------------------------------------------------------
         * vvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvv
         *
         * In a real-world situation, this is where you would place your query.
         *
         * For information on making queries in WordPress, see this Codex entry:
         * http://codex.wordpress.org/Class_Reference/wpdb
         *
         * ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
         * ---------------------------------------------------------------------
         **********************************************************************/


        /**
         * REQUIRED for pagination. Let's figure out what page the user is currently
         * looking at. We'll need this later, so you should always include it in
         * your own package classes.
         */
        $current_page = $this->get_pagenum();

        /**
         * REQUIRED for pagination. Let's check how many items are in our data array.
         * In real-world use, this would be the total number of items in your database,
         * without filtering. We'll need this later, so you should always include it
         * in your own package classes.
         */
        $total_items = count($data);


        /**
         * The WP_List_Table class does not handle pagination for us, so we need
         * to ensure that the data is trimmed to only the current page. We can use
         * array_slice() to
         */
        $data = array_slice($data,(($current_page-1)*$per_page),$per_page);



        /**
         * REQUIRED. Now we can add our *sorted* data to the items property, where
         * it can be used by the rest of the class.
         */
        $this->items = $data;


        /**
         * REQUIRED. We also have to register our pagination options & calculations.
         */
        $this->set_pagination_args( array(
            'total_items' => $total_items,                  //WE have to calculate the total number of items
            'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
            'total_pages' => ceil($total_items/$per_page)   //WE have to calculate the total number of pages
        ) );
    }


}

/* end class */

function add_options() {
  global $myListTable;
  $option = 'per_page';
  $args = array(
         'label' => 'Sendit options',
         'default' => 10,
         'option' => 'subscribers_per_page'
         );
  add_screen_option( $option, $args );
  $myListTable = new Sendit_List_Table();
}


function get_contact($id)
{
    global $wpdb;
    $table_name   = $wpdb->prefix . "nl_email";
	$sql = "SELECT id_email, email, accepted, magic_string FROM $table_name where id_email = $id";
	$contact = $wpdb->get_row($sql);
	return $contact;
}


add_action( 'wp_ajax_add_contacts', 'add_contacts' );

function add_contacts() {

   $migrations=new migrations();
 	 $liste = $migrations->GetLists();
	 //print_r($contact);
	 $html= '<h3><span class="dashicons-before dashicons-admin-users"></span>Add Contacts #</h3>';
   $html.='<div id="contacts_modal_response"></div>';
	 $html.= '<form method="post" class="senditcontactaddform">';
   $html.='<label for="list_id" class="sendit-form-label">Mailing List</label><br />';

   $html.='<select name="list_id" id="list_id">';
   foreach($liste as $lista){
     $html.='<option value="'.$lista->id_lista.'">'.$lista->id_lista.' - '.$lista->nomelista.'</option>';
   }
   $html.='</select><br />';
	 $html.= '<label for="email" class="sendit-form-label">email (1 per line)</label><br />
	 		      <textarea id="emails" name="emails" cols="50" rows="10" required></textarea><hr />';
	 $html.= '<button type="button" id="insert_contacts" class="insert_contacts button action">Save</button>';
	 $html.= '</form>';
	 echo $html;
	 die();
}

add_action( 'wp_ajax_edit_contact', 'edit_contact' );

function edit_contact() {
	 $contact = get_contact($_GET['id']);

	 $confirmed = ($contact->accepted=='y' ? 'selected' : '' );
	 $unconfirmed = ($contact->accepted=='n' ? 'selected' : '' );
	 //print_r($contact);
	 $html= '<h3><span class="dashicons-before dashicons-admin-users"></span>Edit Contact #'.$contact->id_email.'</h3>';
	 $html.= '<form method="post" class="senditcontactform">';
	 $html.= '<input type="hidden" value="'.$contact->id_email.'" name="id_email" class="id_email" id="id_email" /><br />';
	 $html.= '<label for="email" class="sendit-form-label">email</label><br />
	 		  <input type="email" value="'.$contact->email.'" name="email" class="email" id="email" /><br />';
	 $html.= '<label for="accepted" class="sendit-form-label">Confirmed</label><br />
	 			<select name="accepted" class="accepted">
	 				<option value="y" '.$confirmed.'>Yes</option>
	 				<option value="n" '.$unconfirmed.'>No</option>
	 			</select>
	 			<br />
	 				<br />';
	 $html.= '<button type="button" id="save_contact" class="save_contact button action">Save</button>';
	 $html.= '</form>';
	 $html.='<div id="sendit_modal_response"></div>';
	 echo $html;
	 die();
}


add_action( 'wp_ajax_update_contact', 'update_contact' );


function update_contact()
{
	$id_contact = (int) $_POST['id_contact'];
	//print_r($_POST);
    global $wpdb;
    $email = trim($_POST['email']);
    $accepted = $_POST['accepted'];
    $table_name   = $wpdb->prefix . "nl_email";

	$wpdb->update(
	$table_name,
	array(
		'email' => $email,	// string
		'accepted' => $accepted	// integer (number)
	),
	array( 'id_email' => $id_contact )
	);
	echo 'Contact updated';
}

add_action( 'wp_ajax_delete_contact', 'delete_contact' );

function delete_contact()
{
  $contact = get_contact($_GET['id']);


  $html= '<h3><span class="dashicons-before dashicons-admin-users"></span>Delete Contact #'.$contact->id_email.'</h3>';
  $html.= '<form method="post" class="senditdeleteform">';
  $html.= '<input type="hidden" value="'.$contact->id_email.'" name="id_email" class="id_email" id="id_email" /><br />';
  $html.= '<label for="email" class="sendit-form-label">'.$contact->email.'</label><br /><br />';
  $html.= '<button type="button" id="destroy_contact" class="destroy_contact button action">Delete contact</button>';
  $html.= '</form>';
  $html.='<div id="sendit_modal_response"></div>';
  echo $html;
  die();
}

add_action( 'wp_ajax_destroy_contact', 'destroy_contact' );

function destroy_contact()
{
	$id_contact = (int) $_POST['id_contact'];
    global $wpdb;
    $table_name   = $wpdb->prefix . "nl_email";
	  $wpdb->delete($table_name, array( 'id_email' => $id_contact ));
	  echo 'Contact deleted succesfully!';
    exit;
}

add_action( 'wp_ajax_insert_contacts', 'insert_contacts' );

function insert_contacts()
{
  global $wpdb;
  $table_email   = $wpdb->prefix . "nl_email";
  if($_POST['emails']!="") {
    $list_id = (int) $_POST['list_id'];
    $email_add= explode("\n", $_POST['emails']);
    foreach ($email_add as $key => $value) {
        if (!ereg("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$", trim($value))) {
         	echo '<div id="message" class="error"><p><strong>indirizzo email '.$value.' non valido!</strong></p></div>'; exit;
        } else {
  		$user_count = $wpdb->get_var("SELECT COUNT(*) FROM $table_email where email ='$value' and id_lista = $list_id order by email;");
             if($user_count>0) {
                 echo "<div class=\"error\"><p><strong>".sprintf(__('email %s already present', 'sendit'), $value)."</strong></p></div>"; exit;
             } else {
                 $code = md5(uniqid(rand(), true));
                 $wpdb->query("INSERT INTO $table_email (email,id_lista, magic_string, accepted) VALUES ('$value', $list_id, '$code', 'y')");
                  echo '<div class="updated fade"><p><strong>'.sprintf(__('email %s added succesfully!', 'sendit'), $value).'</strong></p></div>';
             }
  	  }
    }
  } else { echo '<div id="message" class="error"><p><strong>'._('Please enter at least one or more email address. One per row', 'sendit').'</strong></p></div>'; exit;}
}






add_action( 'admin_head', 'sendit_admin_ajax_url');

function sendit_admin_ajax_url() { ?>

<script type="text/javascript">
var sendit_ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
</script>
<?php }

/** *************************** RENDER TEST PAGE ********************************
 *******************************************************************************
 * This function renders the admin page and the example list table. Although it's
 * possible to call prepare_items() and display() from the constructor, there
 * are often times where you may need to include logic here between those steps,
 * so we've instead called those methods explicitly. It keeps things flexible, and
 * it's the way the list tables are used in the WordPress core.
 */
function sendit_render_list_page(){
    //Create an instance of our package class...


    $SenditListTable = new Sendit_List_Table();
    //Fetch, prepare, sort, and filter our data...
    $liste = $SenditListTable->get_sendit_lists();
    $SenditListTable->prepare_items();


    ?>
    <div class="wrap">

        <div id="icon-users" class="icon32"><br/></div>
        <h1>Sendit <a href="<?php echo get_bloginfo('siteurl'); ?>/wp-admin/admin-ajax.php?action=add_contacts&width=500&height=400&page=lista-iscritti" class="page-title-action thickbox">Add Subscribers</a></h1>



        <?php add_thickbox(); ?>
        <div id="my-content-id" style="display:none;">
			<p>
          id <?php echo $_GET['id'];?>
		  	</p>
	 	</div>


        <div>
           <h3>Contact management</h3>
          <p><i>sono le ore <?php echo date("Y-m-d H:i:s"); ?></i></p>

        <ul class="subsubsub">
        <?php
            $allcss = !isset($_GET['lista']) ? ' current' : '';
            foreach ($liste as $lista) {
                $css = ($_GET['lista'] == $lista['id_lista']  ? ' current' : '');
                $subscribers = count_subscribers($lista['id_lista']);
            ?>
            <li><a class="<?php echo $css; ?>"href="<?php echo admin_url('admin.php?page=lista-iscritti&lista='.$lista['id_lista']); ?>"><?php echo $lista['nomelista']; ?> (<?php echo $subscribers; ?>)</a>
            </li>
        <?php $tot_subscribers+=$subscribers; } ?>
        <li><a class="<?php echo $allcss; ?>" href="<?php echo admin_url('admin.php?page=lista-iscritti'); ?>">All contact (<?php echo $tot_subscribers; ?>)</a></li>
        </ul>


        </div>

        <br />



        <form  method="get" id="searchcontact">
            <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
            <?php $SenditListTable->search_box('search', 'search_id'); ?>
        </form>

        <br />
        <!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
        <form id="subscribers-filter" method="get">
            <!-- For plugins, we also need to ensure that the form posts back to our current page -->
            <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
            <!-- Now we can render the completed list table -->
            <?php $SenditListTable->display() ?>
        </form>

    </div>
    <?php
}
