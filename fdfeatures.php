<?php 
/**
 * Plugin Name: FactorDaily Features and APIs
 * Plugin URI: https://factordaily.com/
 * Description: All factordaily specific features and APIs to those features. 
 * Version: 2.0
 * Author: Gautham Sarang
 * Author URI: http://gauthamsarang.in
 * License: GPL2 (More about the licence later)
 */ 

/* added 
V 1.2
1. Login
2. registration
3. update read count
4. retrieve read count
5. add comments

V 1.21
1. order and orderby for posts

V 1.22
1. Added contact table
2. Added contact form post data end point

V 1.23
1. added medium for search images (Needs to work on this)
2. total count and pagination for categories
3. retrive only gravatar url

V 1.24
1. added mail and auth mail
2. tags for category

V 2.0
1. Created endpoints folder and moved all the functions to different files 
*/

global $fd_features_version;
$fd_features_version = '1.24';

function fd_features_install() {

	global $wpdb;
	$table_name = $wpdb->prefix . 'fd_contact_form_data';
	$charset_collate = $wpdb->get_charset_collate();

#--------Q1: contact table.--------#
	
	$sql = "CREATE TABLE $table_name (
	  fdc_id mediumint(9) NOT NULL AUTO_INCREMENT,
	  fdc_time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
	  fdc_ip varchar(100) DEFAULT '' NOT NULL,
	  fdc_agent varchar(255) DEFAULT '' NOT NULL,
	  fdc_name tinytext NOT NULL,
	  fdc_email varchar(100) DEFAULT '' NOT NULL,
	  fdc_message longtext NOT NULL,
	  PRIMARY KEY  (fdc_id)
	) $charset_collate;";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( array($sql) );
	add_option( 'fd_features_version', $fd_features_version );
	add_image_size( 'search_thumb', 70, 88, true, array('center','center') );
}
register_activation_hook( __FILE__, 'fd_features_install' );


/*-------------------------------------------------------------------------*/
/*	                          Add Custom Post types                        */
/*-------------------------------------------------------------------------*/
class fd_custom_posts {

	function __construct() {
		add_action('init',array($this,'create_post_type_event'));
		add_action('init',array($this,'create_post_type_testimonial'));
	}

	function create_post_type_event() {
		$labels = array(
		    'name' => 'Event',
		    'singular_name' => 'Event',
		    'add_new' => 'Add New ',
		    'all_items' => 'All Events',
		    'add_new_item' => 'Add New Event',
		    'edit_item' => 'Edit Event',
		    'new_item' => 'New Event',
		    'view_item' => 'View Event',
		    'search_items' => 'Search Event',
		    'not_found' =>  'No Events found',
		    'not_found_in_trash' => 'No Events found in trash',
		    'menu_name' => 'Events'
		);
		$args = array(
			'labels' => $labels,
			'description' => "Enter the event description here",
			'public' => true,
			'exclude_from_search' => false,
			'publicly_queryable' => true,
			'show_ui' => true,
			'show_in_nav_menus' => true,
			'show_in_menu' => true,
			'show_in_admin_bar' => true,
			'menu_position' => 4,
			'menu_icon' => 'dashicons-calendar',
			'capability_type' => 'post',
			'hierarchical' => false,
			'supports' => array('title','editor','author','custom-fields', 'shortlinks','post_tag','thumbnail' ),
			'has_archive' => true,
			'rewrite' => array('slug' => 'event', 'with_front' => ''),
			'query_var' => true,
			'can_export' => true,
			'taxonomies' => array('post_tag','category')
		);
		register_post_type('fd_events',$args);
	}	

	function create_post_type_testimonial() {
		$labels = array(
		    'name' => 'Testimonials',
		    'singular_name' => 'Testimonial',
		    'add_new' => 'Add New ',
		    'all_items' => 'All Testimonials',
		    'add_new_item' => 'Add New Testimonial',
		    'edit_item' => 'Edit Testimonial',
		    'new_item' => 'New Testimonial',
		    'view_item' => 'View Testimonial',
		    'search_items' => 'Search Testimonials',
		    'not_found' =>  'No Testimonials found',
		    'not_found_in_trash' => 'No Testimonials found in trash',
		    'menu_name' => 'Testimonial'
		);
		$args = array(
			'labels' => $labels,
			'description' => "Enter the testimonial description here",
			'public' => true,
			'exclude_from_search' => false,
			'publicly_queryable' => true,
			'show_ui' => true,
			'show_in_nav_menus' => true,
			'show_in_menu' => true,
			'show_in_admin_bar' => true,
			'menu_position' => 5,
			'menu_icon' => 'dashicons-testimonial',
			'capability_type' => 'post',
			'hierarchical' => false,
			'supports' => array('title','editor','author','custom-fields', 'shortlinks','thumbnail' ),
			'has_archive' => true,
			'rewrite' => array('slug' => 'testimonial', 'with_front' => ''),
			'query_var' => true,
			'can_export' => true,
			'taxonomies' => array('post_tag','category')
		);
		register_post_type('fd_testimonial',$args);
	}	

}

$fd_cpts = new fd_custom_posts();


/*---------------------------------------------------------------*/
/*	                       Add Meta boxes                        */
/*---------------------------------------------------------------*/

add_action( 'add_meta_boxes', 'fd_metaboxes' );

function fd_metaboxes() {
	
	add_meta_box( 'event_location', __( 'Venue', 'fd_features_plugin' ), 'event_location_metabox', 'fd_events', 'side', 'high');
	add_meta_box( 'event_coordinates', __( 'Venue co-ordinates', 'fd_features_plugin' ), 'event_coordinates_metabox', 'fd_events', 'side', 'high');
	add_meta_box( 'event_date_from', __( 'Date', 'fd_features_plugin' ), 'event_date_metabox', 'fd_events', 'side', 'high');
	add_meta_box( 'event_date_to', __( 'End date', 'fd_features_plugin' ), 'event_end_date_metabox', 'fd_events', 'side', 'high');
	add_meta_box( 'event_agenda', __( 'Agenda', 'fd_features_plugin' ), 'event_agenda_metabox', 'fd_events', 'normal', 'high');
	add_meta_box( 'testimonial_who', __( 'Designation', 'fd_features_plugin' ), 'testimonial_who_metabox', 'fd_testimonial', 'side', 'high');
}

/*------------ Save post action ---------------*/
add_action( 'save_post', 'event_details_save', 10, 1 );
add_action( 'save_post', 'testimonial_details_save', 10, 1 );

/*---------- Save Event details ---------------*/
function event_details_save($post_id) {
	if( isset( $_POST['_fd_event_venue'] ) ) {
		$fd_event_venue = $_POST['_fd_event_venue'];
		update_post_meta( $post_id, '_fd_event_venue', $fd_event_venue );
	}
	
	if( isset( $_POST['_fd_event_cord'] ) ) {
		echo $_POST['_fd_event_cord'];
		$fd_event_coordinates = $_POST['_fd_event_cord'];
		update_post_meta( $post_id, '_fd_event_cord', $fd_event_coordinates );
	}
	
	if( isset( $_POST['_fd_event_date'] ) ) {
		echo $_POST['_fd_event_date'];
		$fd_event_date = $_POST['_fd_event_date'];
		update_post_meta( $post_id, '_fd_event_date', $fd_event_date );
	}
	
	if( isset( $_POST['_fd_event_end_date'] ) ) {
		echo $_POST['_fd_event_end_date'];
		$fd_event_end_date = $_POST['_fd_event_end_date'];
		update_post_meta( $post_id, '_fd_event_end_date', $fd_event_end_date );
	}
	
	if( isset( $_POST['_fd_event_agenda'] ) ) {
		echo $_POST['_fd_event_agenda'];
		$fd_event_agenda = $_POST['_fd_event_agenda'];
		update_post_meta( $post_id, '_fd_event_agenda', $fd_event_agenda );
	}
}
/*----------- Save Testimonial details---------------*/

function testimonial_details_save($post_id) {
	if( isset( $_POST['_fd_testimonial_who'] ) ) {
		$fd_testimonial_who = $_POST['_fd_testimonial_who'];
		update_post_meta( $post_id, '_fd_testimonial_who', $fd_testimonial_who );
	}
}
/*---------------------------------------------------------------*/
/*	             Add Event Meta box content                      */
/*---------------------------------------------------------------*/

function event_location_metabox($post){
	echo "<h2>Enter Venue details</h2>";
	$stored_fd_event_venue = get_post_meta($post->ID, '_fd_event_venue');
	if($stored_fd_event_venue[0] == ''){  $stored_fd_event_venue ='';  }
	$content = '<input id="fd_event_venue" name="_fd_event_venue" type="text" value="'.$stored_fd_event_venue[0].'" >';
	echo $content ;
}

function event_coordinates_metabox($post){
	echo "<h2>Enter Coordinates</h2>";
	$stored_fd_event_cord = get_post_meta($post->ID, '_fd_event_cord');
	if($stored_fd_event_cord[0] == ''){  $stored_fd_event_cord ='';  }
	$content = '<input id="fd_event_cord" name="_fd_event_cord" type="text" value="'.$stored_fd_event_cord[0].'" >';
	echo $content ;
}


function event_date_metabox($post){
	echo "<h2>Enter Event Date</h2>";
	$stored_fd_event_date = get_post_meta($post->ID, '_fd_event_date');
	if($stored_fd_event_date[0] == ''){  $stored_fd_event_date ='';  }
	$content = '<input id="fd_event_date" name="_fd_event_date" type="text" value="'.$stored_fd_event_date[0].'" >';
	echo $content ;
}

function event_end_date_metabox($post){
	$stored_fd_event_end_date = get_post_meta($post->ID, '_fd_event_end_date');
	if($stored_fd_event_end_date[0] == ''){  $stored_fd_event_end_date ='';  }
	$content = '<input id="fd_event_end_date" name="_fd_event_end_date" type="text" value="'.$stored_fd_event_end_date[0].'" >';
	echo $content ;
	
}

function event_agenda_metabox($post){
	$stored_fd_event_agenda = get_post_meta($post->ID, '_fd_event_agenda');
          wp_editor ( 
           $stored_fd_event_agenda[0] , 
           '_fd_event_agenda', 
           array ( "media_buttons" => false ) 
          );
}

/*-------------------------------------------------------*/
/*			Add Testimonial metabox content				 */
/*-------------------------------------------------------*/

function testimonial_who_metabox($post){
	echo "<h2>Designation and company</h2>";
	$stored_fd_testimonial_who = get_post_meta($post->ID, '_fd_testimonial_who');
	if($stored_fd_testimonial_who[0] == ''){  $stored_fd_testimonial_who ='';  }
	$content = '<input id="fd_testimonial_who" name="_fd_testimonial_who" type="text" value="'.$stored_fd_testimonial_who[0].'" >';
	echo $content ;
}

/*=======================================================*/
/*						API functions                    */
/*-------------------------------------------------------*/

/*------ Posts by user ID -------*/
/*1*/ include_once('endpoints/authorposts.php');
include_once('endpoints/getposts.php');
include_once('endpoints/leadstories.php');
include_once('endpoints/pages.php');
include_once('endpoints/relatedposts.php');
include_once('endpoints/logout.php');
include_once('endpoints/login.php');
include_once('endpoints/commreadcount.php');
include_once('endpoints/register.php');
include_once('endpoints/profile.php');
include_once('endpoints/userupdate.php');
include_once('endpoints/mail.php');
include_once('endpoints/postformdata.php');
include_once('endpoints/topstories.php');
include_once('endpoints/validate.php');
include_once('endpoints/archive.php');
include_once('endpoints/fdcomments.php');
include_once('endpoints/events.php');
include_once('endpoints/related-events.php');
include_once('endpoints/past-events.php');
include_once('endpoints/teamdetails.php');
include_once('endpoints/resetpassword.php');
/*+++++++++++++++ Top stories ++++++++++++++++++*/

function fd_update_user($request) {
	session_start();
	$incomingtoken = $request['fdtoken'];
	$user_id = $request['user_id'];
	//how are we passing the fields here?
	if($incomingtoken == $_SESSION['fdtoken']){
		
	}
	
}

function fd_check_token($token) {
//	$token = $token['fdtoken'];
	session_start();
	if($_SESSION['fdtoken'] == ''){
		return(false);
	}
	if($_SESSION['fdtoken'] == $token){
		return(true);
	}else{
		return(false);
	}
}






function fdrest_token_validate($request){
		$url = get_home_url().'/wp-json/jwt-auth/v1/token/validate/';
//		$cred = 'username='.$username.'&email'.$useremail.'&password='.$userpass;
		
	$header = array();
$header[] = 'Content-length: 0';
$header[] = 'Content-type: application/json';
$header[] = 'Authorization: OAuth'.$requst['token'];
	$authorization = "Authorization: Bearer ". $request['token'];

		$ch = curl_init( $url );
		curl_setopt( $ch, CURLOPT_POST, 1);
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $cred);
		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , $authorization ));
 
		curl_setopt( $ch, CURLOPT_HEADER, $header);
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);

		$response = curl_exec( $ch );
	

if ($response === false)
{
    // throw new Exception('Curl error: ' . curl_error($crl));
    print_r('Curl error: ' . curl_error($crl));
}

curl_close($ch);
print_r($response);
		$decoded = json_decode($response);
		return $response ;
}
// register rest api
add_action( 'rest_api_init', function () {

register_rest_route( 'fdrest/v1', '/fdposts/', array(
    'methods' => 'GET',
    'callback' => 'fdrest_get_posts',
  ) );
	
register_rest_route( 'fdrest/v1', '/fdleadstories/', array(
    'methods' => 'GET',
    'callback' => 'fdrest_leadstories',
  ) );
	
register_rest_route( 'fdrest/v1', '/fdpages/', array(
    'methods' => 'GET',
    'callback' => 'fdrest_get_pages',
  ) );

register_rest_route( 'fdrest/v1', '/fdteamdetails/', array(
    'methods' => 'GET',
    'callback' => 'fdrest_team_details',
  ) );
	
register_rest_route( 'fdrest/v1', '/fdcomments/', array(
    'methods' => 'GET',
    'callback' => 'fdrest_get_comments',
  ) );
	
register_rest_route( 'fdrest/v1', '/fdrelatedposts/', array(
    'methods' => 'GET',
    'callback' => 'fdrest_related_posts',
  ) );

register_rest_route( 'fdrest/v1', '/register/', array(
		'methods'             => 'POST',
		'callback'            => 'fdrest_register'
	) );
	
register_rest_route( 'fdrest/v1', '/login/', array(
		'methods'             => 'POST',
		'callback'            => 'fdrest_login'
	) );

register_rest_route( 'fdrest/v1', '/updateuser/', array(
		'methods'             => 'POST',
		'callback'            => 'fdrest_update_user'
	) );
	
register_rest_route( 'fdrest/v1', '/logout/', array(
		'methods'             => 'POST',
		'callback'            => 'fdrest_logout'
	) );

register_rest_route( 'fdrest/v1', '/fdrcc/', array(
		'methods'             => array('GET','POST'),
		'callback'            => 'fdrest_comment_read_count'
	) );
	
register_rest_route( 'fdrest/v1', '/postformdata/', array(
		'methods'             => array('GET','POST'),
		'callback'            => 'fdrest_form_data'
	) );
	
register_rest_route( 'fdrest/v1', '/fdchecktoken/', array(
		'methods'             => 'GET',
		'callback'            => 'fd_check_token'
	) );
	
register_rest_route( 'fdrest/v1', '/tokenvalidate/', array(
		'methods'             => array('GET','POST'),
		'callback'            => 'fdrest_token_validate'
	) );
register_rest_route( 'fdrest/v1', '/fdauthmail/', array(
		'methods'             => array('GET','POST'),
		'callback'            => 'fdrest_auth_mail'
	) );
register_rest_route( 'fdrest/v1', '/fdmail/', array(
		'methods'             => array('GET','POST'),
		'callback'            => 'fdrest_mail'
	) );
register_rest_route( 'fdrest/v1', '/fdprofile/', array(
		'methods'             => array('GET','POST'),
		'callback'            => 'fdrest_profile'
	) );
register_rest_route( 'fdrest/v1', '/fdap/', array(
		'methods'             => array('GET','POST'),
		'callback'            => 'fdrest_ap'
	) );
register_rest_route( 'fdrest/v1', '/fdtopstories/', array(
		'methods'             => array('GET'),
		'callback'            => 'fdrest_top_stories'
	) );
register_rest_route( 'fdrest/v1', '/authpaged/', array(
		'methods'             => array('GET'),
		'callback'            => 'fdrest_auth_paged'
	) );
register_rest_route( 'fdrest/v1', '/fdvalidatetoken/', array(
		'methods'             => array('POST'),
		'callback'            => 'fdrest_validate'
	) );
register_rest_route( 'fdrest/v1', '/fdarchive/', array(
		'methods'             => array('GET','POST'),
		'callback'            => 'fdrest_get_archive'
	) );
register_rest_route( 'fdrest/v1', '/fdgetevent/', array(
		'methods'             => array('GET'),
		'callback'            => 'fdrest_get_event'
	) );
register_rest_route( 'fdrest/v1', '/fdrelatedevents/', array(
		'methods'             => array('GET'),
		'callback'            => 'fdrest_related_events'
	) );
register_rest_route( 'fdrest/v1', '/fdpastevents/', array(
		'methods'             => array('GET'),
		'callback'            => 'fdrest_past_events'
	) );
register_rest_route( 'fdrest/v1', '/resetpassword/', array(
		'methods'             => array('POST'),
		'callback'            => 'fdrest_reset_password'
	) );
} );

