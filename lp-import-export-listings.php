<?php 
/*
Plugin Name: Listeo Import/Export Listings
Plugin URI:
Description: Listings Import/Export for listeo theme
Version: 1.0
Author: latifpala
Text Domain: listeo-import-export-listings
*/
/* 
user_data
-----------
0 - user_email
1 - user_name
2 - password

post_data
--------------
3 - post_title
4 - post_content
5 - post_name
6 - post_status

listing_categories
--------------------
7 - listing_category
8 - listing_feature
9 - region

address
----------------
10 - friendly_address
11 - address
12 - latitude
13 - longitude

--------------
14 - layout
---------------
15 - gallery
---------------

opening_closing_hours
----------------------------
16 - tuesday_opening_hour
17 - tuesday_closing_hour

18 - wednesday_opening_hour
19 - wednesday_closing_hour

20 - thursday_opening_hour
21 - thursday_closing_hour

22 - friday_opening_hour
23 - friday_closing_hour

24 - saturday_opening_hour
25 - saturday_closing_hour

26 - sunday_opening_hour
27 - sunday_closing_hour

28 - monday_opening_hour
29 - monday_closing_hour

--------------------------
30 - menu_title
------------------------
31 - menu_details
----------------------

social_data
----------------------------
32 - phone
33 - email
34 - facebook
35 - twitter
36 - youtube
37 - instagram
38 - gplus
39 - website
40 - whatsapp
41 - video
42 - skype

------------------------
43 - featured_image
---------------------

personal_data
--------------------------
44 - age
45 - gender
46 - ethnicity
47 - height
48 - hair
49 - eyes
50 - body
51 - boobs
52 - boos_size
53 - intim
54 - skin
55 - body_dekort
56 - language

--------------------------
57 - type
------------------------
*/
require_once plugin_dir_path( __FILE__ ) . 'lib/class-download-remote-image.php';
include( plugin_dir_path( __FILE__ ) . 'admin/lp-import-export-page.php');
include( plugin_dir_path( __FILE__ ) . 'lp-import-ajax.php');

function lp_import_export_install() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'lp_import';
    $table_name2 = $wpdb->prefix . 'lp_import_data';
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE $table_name (
        ID int NOT NULL AUTO_INCREMENT,
        user_data text NULL,
        post_data text NULL,
        listing_categories text NULL,
        address text NULL,
        layout text NULL,
        gallery text NULL,
        opening_closing_hours text NULL,
        menu_title text NULL,
        menu_details text NULL,
        social_data text NULL,
        featured_image text NULL,
        personal_data text NULL,
        type text NULL,
        import_status boolean NULL,
        PRIMARY KEY  (ID)
    ) $charset_collate;";

     $sql2 = "CREATE TABLE $table_name2 (
        import_id int NOT NULL AUTO_INCREMENT,
        import_data text NULL,
        status boolean NULL,
        PRIMARY KEY  (import_id)
    ) $charset_collate;";
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
    dbDelta( $sql2 );
}
register_activation_hook( __FILE__, 'lp_import_export_install' );

add_action( 'admin_enqueue_scripts', 'lp_import_export_admin_enqueue_scripts_func' );
function lp_import_export_admin_enqueue_scripts_func(){
	wp_enqueue_script( 'lp-import-export-script', plugin_dir_url( __FILE__ ) . 'js/import_script.js', array( 'jquery' ), '1.1', false );
	wp_localize_script('lp-import-export-script', 'obj_import', array(
	    'ajax_url' => admin_url('admin-ajax.php')
	));
}

function lp_update_post_meta($post_id, $key, $value){
	if($value!=""){
		update_post_meta($post_id, $key, $value);
	}
}
function save_product($user_id, $description, $title) {

    $product = array (
        'post_author' => $user_id,
        'post_content' => $description,
        'post_status' => 'publish',
        'post_title' => $title,
        'post_parent' => '',
        'post_type' => 'product',
    );

    $product_id = wp_insert_post( $product );
    wp_set_object_terms( $product_id, 'listing_booking', 'product_type' );

    // set product category
    $term = get_term_by( 'name', apply_filters( 'listeo_default_product_category', 'Listeo booking'), 'product_cat', ARRAY_A );

    if ( ! $term ) $term = wp_insert_term(
        apply_filters( 'listeo_default_product_category', 'Listeo booking'),
        'product_cat',
        array(
          'description'=> __( 'Listings category', 'listeo-core' ),
          'slug' => str_replace( ' ', '-', apply_filters( 'listeo_default_product_category', 'Listeo booking') )
        )
      );

    wp_set_object_terms( $product_id, $term['term_id'], 'product_cat');
    return $product_id;
}

function check_if_image_exist( $img ) {
    global $wpdb;
    $sql = intval( $wpdb->get_var( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_value LIKE '%/$img'" ) );
    return $sql !== null ? $sql : false;

    /*$check_if_image_exist = check_if_image_exist(basename($image_url));
    if($check_if_image_exist!=false)
        return $check_if_image_exist;*/
}

function get_latlong($address = ""){
    $key1 = "AIzaSyBGCvMX_dToRgRkog9hvf3WB4FXUzN0428";
    $url = "https://maps.googleapis.com/maps/api/geocode/json?key=".$key1."&address=".urlencode($address)."&sensor=false";
    $result_string = wp_remote_get($url);
    if(!empty($result_string)){
        if($result_string['response']['code']==200){
            if(!empty($result_string['body'])){
                $result = json_decode($result_string['body'], true);
                if(isset($result['results'][0]['geometry']['location'])){
                    return $result['results'][0]['geometry']['location'];
                }
            }
        }
    }
    return array();
}

function convert_character_set($str){
    if (mb_detect_encoding($str, 'UTF-8', true) === false) {
        $str = utf8_encode($str);
    }
    return $str;
}

function get_attachment_image_id( $url, $md5_hash, $post_id ) {
    $attachment_data = array('md5_hash' => $md5_hash, 'post_id' => $post_id);

    $download_remote_image = new Download_Remote_Image( $url, $attachment_data );
    $attachment_id         = $download_remote_image->download();
    if ( ! $attachment_id ) {
        return false; 
    }else{
        return $attachment_id;
    }
}

/*function display_latlong(){
    $key1 = "AIzaSyBGCvMX_dToRgRkog9hvf3WB4FXUzN0428";
    $address = "Zusamstr. 29, 86165 Augsburg";
    $url = "https://maps.googleapis.com/maps/api/geocode/json?key=".$key1."&address=".urlencode($address)."&sensor=false";
    $result_string = wp_remote_get($url);
    echo "<pre>";
    print_r($result_string);
    die;
}
if($_REQUEST['testing']){
    add_action('init', 'display_latlong');
}*/

