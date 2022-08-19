<?php
add_action( "wp_ajax_import_ajax", "lp_import_ajax_func" );
add_action( "wp_ajax_nopriv_import_ajax", "lp_import_ajax_func" );
function lp_import_ajax_func(){
	global $wpdb;
    $uploadedfile = $_FILES['import_csv_file'];
    $upload_overrides = array( 'test_form' => false );
    $movefile = wp_handle_upload( $uploadedfile, $upload_overrides );
	$import_table = $wpdb->prefix.'lp_import';
	$import_data_table = $wpdb->prefix.'lp_import_data';
	
	// Delete all old records
	$delete_old_records_qry = "TRUNCATE TABLE ".$import_data_table;
	$wpdb->query($delete_old_records_qry);

	$delete_old_records_qry2 = "TRUNCATE TABLE ".$import_table;
	$wpdb->query($delete_old_records_qry2);

	if($movefile && ! isset( $movefile['error'])){

		$file_data = fopen($movefile['file'], 'r');
		$data = array();
		$row_count = 1;
		$total_count = 0;
		
		while($row = fgetcsv($file_data, 0, ';')){
			if($row_count == 1){ $row_count++; continue; } 

			if(trim($row[1])!="" && trim($row[5])!=""){
				$row_data = array();
				$row_data[] = convert_character_set($row[0]); //useremail
				$row_data[] = convert_character_set($row[1]); //username
				$row_data[] = convert_character_set($row[2]); //forename
				$row_data[] = convert_character_set($row[3]); //surname
				$row_data[] = convert_character_set($row[4]); //password
				$row_data[] = convert_character_set($row[5]); //listingtitle
				$row_data[] = convert_character_set($row[6]); // listing content
				$row_data[] = convert_character_set($row[7]); // post_name
				$row_data[] = convert_character_set($row[8]); // post_status
				$row_data[] = convert_character_set($row[9]); // listing_category
				$row_data[] = convert_character_set($row[10]); // listing_feature
				$row_data[] = convert_character_set($row[11]); // region
				$row_data[] = convert_character_set($row[12]); // _friendly_address
				$row_data[] = convert_character_set($row[13]); // _address
				$row_data[] = convert_character_set($row[14]); // _geolocation_lat
				$row_data[] = convert_character_set($row[15]); // _geolocation_long
				$row_data[] = convert_character_set($row[16]); // _layout
				$row_data[] = convert_character_set($row[17]); // GALLERY
				$row_data[] = convert_character_set($row[18]); // _tuesday_opening_hour
				$row_data[] = convert_character_set($row[19]); 
				$row_data[] = convert_character_set($row[20]);
				$row_data[] = convert_character_set($row[21]);
				$row_data[] = convert_character_set($row[22]);
				$row_data[] = convert_character_set($row[23]);
				$row_data[] = convert_character_set($row[24]);
				$row_data[] = convert_character_set($row[25]);
				$row_data[] = convert_character_set($row[26]);
				$row_data[] = convert_character_set($row[27]);
				$row_data[] = convert_character_set($row[28]);
				$row_data[] = convert_character_set($row[29]);
				$row_data[] = convert_character_set($row[30]);
				$row_data[] = convert_character_set($row[31]); // _monday_closing_hour
				$row_data[] = convert_character_set($row[32]); // menu_title
				$row_data[] = convert_character_set($row[33]); // menu
				$row_data[] = convert_character_set($row[34]); // phone
				$row_data[] = convert_character_set($row[35]); // _email
				$row_data[] = convert_character_set($row[36]);
				$row_data[] = convert_character_set($row[37]);
				$row_data[] = convert_character_set($row[38]);
				$row_data[] = convert_character_set($row[39]);
				$row_data[] = convert_character_set($row[40]);
				$row_data[] = convert_character_set($row[41]);
				$row_data[] = convert_character_set($row[42]);
				$row_data[] = convert_character_set($row[43]);
				$row_data[] = convert_character_set($row[44]); // _skype
				$row_data[] = convert_character_set($row[45]); // featured_image
				$row_data[] = convert_character_set($row[46]); // age
				$row_data[] = convert_character_set($row[47]); // gender
				$row_data[] = convert_character_set($row[48]); // ethnicity
				$row_data[] = convert_character_set($row[49]); // height
				$row_data[] = convert_character_set($row[50]); // hair
				$row_data[] = convert_character_set($row[51]); // eyes
				$row_data[] = convert_character_set($row[52]); // body
				$row_data[] = convert_character_set($row[53]); // boobs
				$row_data[] = convert_character_set($row[54]); // boobs-size
				$row_data[] = convert_character_set($row[55]); // intimrasur
				$row_data[] = convert_character_set($row[56]); // skin
				$row_data[] = convert_character_set($row[57]); // Piercing Tatto
				$row_data[] = convert_character_set($row[58]); // language
				$row_data[] = convert_character_set($row[59]); // add/update
				
				$insert_data_array = array(
					'import_data' => serialize($row_data),
					'status' => 0,
				);
				$ret = $wpdb->insert($import_data_table,$insert_data_array);

				$total_count++;
			}
		}
		$status = "success";
	}else{
		$status = "failed";
	}

	if($status=="success"){
		$msg = "<p style='color:green;'><span style='font-weight:bold;'>".$total_count."</span> Records Found</p>";
	}else{
		$msg = "<p style='color:red; font-weight:bold;'>Invalid Import File.</p>";
	}
	echo json_encode(array('status' => $status, 'msg' => $msg, 'total_records' => $total_count));
	die;
}
add_action( "wp_ajax_process_records", "lp_process_records_func" );
add_action( "wp_ajax_nopriv_process_records", "lp_process_records_func" );
function lp_process_records_func(){
	global $wpdb;
	$import_data_table = $wpdb->prefix.'lp_import_data';
	$import_table = $wpdb->prefix.'lp_import';

	$query = "SELECT * FROM ".$import_data_table." WHERE status = 0 order by import_id ASC LIMIT 50";
	$res = $wpdb->get_results($query);
	foreach ($res as $data) {
		$import_id = $data->import_id;
		$row = unserialize($data->import_data);
		
		$user_data = $post_data = $listing_category_data = $address_data = $opening_closing_hour = $social_data = $personal_data = array();
		$layout = $gallery = $menu_title = $menu_details = $featured_image = $type = "";

		$user_data_serialize = $post_data_serialize = $listing_category_data_serialize = $address_data_serialize = $gallery_serialize = $opening_closing_hour_seialize = $menu_details_serialize = $social_data_serialize = $personal_data_serialize = "";

		/* User data */
		$user_data['user_email'] = $row[0];
		$user_data['user_name'] = $row[1];
		$user_data['first_name'] = $row[2];
		$user_data['last_name'] = $row[3];
		$user_data['password'] = $row[4];
		$user_data_serialize = maybe_serialize($user_data);

		/* Post data */
		$post_data['post_title'] = $row[5];
		$post_data['post_content'] = $row[6];
		$post_data['post_name'] = $row[7];
		$post_data['post_status'] = $row[8];
		$post_data_serialize = maybe_serialize($post_data);


		/* categories */
		$listing_category_data['listing_category'] = $row[9];
		$listing_category_data['listing_feature'] = $row[10];
		$listing_category_data['region'] = $row[11];
		$listing_category_data_serialize = maybe_serialize($listing_category_data);

		/* Post meta data */
		$address_data['friendly_address'] = $row[12];
		$address_data['address'] = $row[13];
		$address = $row[13];

		$geolocation_lat = $row[14];
		$geolocation_long = $row[15];
		
		if($geolocation_lat=="" && $geolocation_long=="" && $address!=""){
			$latlong = get_latlong($address);
			if(!empty($latlong)){
				if(isset($latlong['lat']))
					$geolocation_lat = $latlong['lat'];
				
				if(isset($latlong['lng']))
					$geolocation_long = $latlong['lng'];
			}
		}
		$address_data['latitude'] = $geolocation_lat;
		$address_data['longitude'] = $geolocation_long;
		$address_data_serialize = maybe_serialize($address_data);

		$layout = $row[16];
		
		//$gallery = explode("#$#",convert_character_set($row[15])); //serialize
		$gallery = $row[17];

		$opening_closing_hour['tuesday_opening_hour'] = $row[18];
		$opening_closing_hour['tuesday_closing_hour'] = $row[19];
		$opening_closing_hour['wednesday_opening_hour'] = $row[20];
		$opening_closing_hour['wednesday_closing_hour'] = $row[21];
		$opening_closing_hour['thursday_opening_hour'] = $row[22];
		$opening_closing_hour['thursday_closing_hour'] = $row[23];
		$opening_closing_hour['friday_opening_hour'] = $row[24];
		$opening_closing_hour['friday_closing_hour'] = $row[25];
		$opening_closing_hour['saturday_opening_hour'] = $row[26];
		$opening_closing_hour['saturday_closing_hour'] = $row[27];
		$opening_closing_hour['sunday_opening_hour'] = $row[28];
		$opening_closing_hour['sunday_closing_hour'] = $row[29];
		$opening_closing_hour['monday_opening_hour'] = $row[30];
		$opening_closing_hour['monday_closing_hour'] = $row[31];
		$opening_closing_hour_seialize = maybe_serialize($opening_closing_hour);
		
		$menu_title = $row[32];

		//$menu_array = explode('#$#', $row[31]);
		$menu_details = $row[33];
		
		$social_data['phone'] = $row[34];
		$social_data['email'] = $row[35];
		$social_data['facebook'] = $row[36];
		$social_data['twitter'] = $row[37];
		$social_data['youtube'] = $row[38];
		$social_data['instagram'] = $row[39];
		$social_data['gplus'] = $row[40];
		$social_data['website'] = $row[41];
		$social_data['whatsapp'] = $row[42];
		$social_data['video'] = $row[43];
		$social_data['skype'] = $row[44];
		$social_data_serialize = maybe_serialize($social_data);

		$featured_image_src = $row[45]; // serialize

		$personal_data['age'] = $row[46];
		$personal_data['gender'] = $row[47];
		$personal_data['ethnicity'] = $row[48];
		$personal_data['height'] = $row[49];
		$personal_data['hair'] = $row[50];
		$personal_data['eyes'] = $row[51];
		$personal_data['body'] = $row[52];
		$personal_data['boobs'] = $row[53];
		$personal_data['boobs_size'] = $row[54];
		$personal_data['intim'] = $row[55];
		$personal_data['skin'] = $row[56];
		$personal_data['body_dekort'] = $row[57];
		$personal_data['language'] = $row[58];
		$personal_data_serialize = maybe_serialize($personal_data);

		/*$body_dekort = explode("#$#", convert_character_set($row[55]));
		$language = explode("#$#", convert_character_set($row[56]));*/

		$type = strtolower($row[59]);
		if($type==""){
			$type = "add/update";
		}
		$insert_data_array = array(
			'user_data' => $user_data_serialize, 
			'post_data' => $post_data_serialize,
			'listing_categories' => $listing_category_data_serialize,
			'address' => $address_data_serialize,
			'layout' => $layout,
			'gallery' => $gallery,
			'opening_closing_hours' => $opening_closing_hour_seialize,
			'menu_title' => $menu_title,
			'menu_details' => $menu_details,
			'social_data' => $social_data_serialize,
			'featured_image' => $featured_image_src,
			'personal_data' => $personal_data_serialize,
			'type' => $type,
			'import_status' => 0,
		);
		$wpdb->insert($import_table,$insert_data_array);
		$wpdb->update($import_data_table, array( 'status' => 1 ), array( 'import_id' => $import_id ));

	}
	$records_processed_qry = "SELECT import_id FROM ".$import_data_table." WHERE status = 1 order by import_id ASC";
	$records_processed = $wpdb->get_results($records_processed_qry);

	$records_pending_qry = "SELECT import_id FROM ".$import_data_table." WHERE status = 0 order by import_id ASC";
	$records_pending = $wpdb->get_results($records_pending_qry);	

	if(!empty($records_pending)){
		echo json_encode(array('more_records' => "yes", 'pending_records' => count($records_pending), 'processed_records' => count($records_processed)));
	}else{
		echo json_encode(array('more_records' => "no", 'pending_records' => count($records_pending), 'processed_records' => count($records_processed)));
	}
	die;
}

add_action( "wp_ajax_import_records", "lp_import_records_func" );
add_action( "wp_ajax_nopriv_import_records", "lp_import_records_func" );
function lp_import_records_func(){
	global $wpdb;
	$import_table = $wpdb->prefix.'lp_import';
	$query = "SELECT * FROM ".$import_table." WHERE import_status = 0 order by ID ASC LIMIT 2";
	$res = $wpdb->get_results($query);
	$new_posts = $skipped_posts = $gallery_error_arr = $insert_failed_arr = $update_failed_arr = array();
	$insert_failed_str = $update_failed_str = "";

	foreach ($res as $data) {	
		$gallery_arr = array();

		$id = $data->ID;
		$user_data = maybe_unserialize($data->user_data);
		$post_data = maybe_unserialize($data->post_data);
		$listing_categories = maybe_unserialize($data->listing_categories);
		$address_data = maybe_unserialize($data->address);
		$layout = $data->layout;
		$gallery_data = $data->gallery;
		$opening_closing_hours = maybe_unserialize($data->opening_closing_hours);
		$menu_title = $data->menu_title;
		$menu_details = $data->menu_details;
		$social_data = maybe_unserialize($data->social_data);
		$featured_image = $data->featured_image;
		$personal_data = maybe_unserialize($data->personal_data);
		$type = $data->type;


		/* User data */
		$user_email = $user_data['user_email']; 
		$user_name = $user_data['user_name']; 
		$first_name = $user_data['first_name']; 
		$last_name = $user_data['last_name']; 
		$password = $user_data['password'];

		/* Post data */
		$post_title = $post_data['post_title'];
		$post_content = $post_data['post_content'];
		$post_name = $post_data['post_name'];
		$post_status = $post_data['post_status'];

		/* Listing categories */
		$listing_category = $listing_categories['listing_category'];
		$listing_feature = $listing_categories['listing_feature'];
		$region = $listing_categories['region'];

		/* Address Data */
		$friendly_address = $address_data['friendly_address'];
		$address = $address_data['address'];
		$latitude = $address_data['latitude'];
		$longitude = $address_data['longitude'];


		/* opening closing hours data */
		$tuesday_opening_hour = $opening_closing_hours['tuesday_opening_hour'];
		$tuesday_closing_hour = $opening_closing_hours['tuesday_closing_hour'];
		$wednesday_opening_hour = $opening_closing_hours['wednesday_opening_hour'];
		$wednesday_closing_hour = $opening_closing_hours['wednesday_closing_hour'];
		$thursday_opening_hour = $opening_closing_hours['thursday_opening_hour'];
		$thursday_closing_hour = $opening_closing_hours['thursday_closing_hour'];
		$friday_opening_hour = $opening_closing_hours['friday_opening_hour'];
		$friday_closing_hour = $opening_closing_hours['friday_closing_hour'];
		$saturday_opening_hour = $opening_closing_hours['saturday_opening_hour'];
		$saturday_closing_hour = $opening_closing_hours['saturday_closing_hour'];
		$sunday_opening_hour = $opening_closing_hours['sunday_opening_hour'];
		$sunday_closing_hour = $opening_closing_hours['sunday_closing_hour'];
		$monday_opening_hour = $opening_closing_hours['monday_opening_hour'];
		$monday_closing_hour = $opening_closing_hours['monday_closing_hour'];
		

		$phone = $social_data['phone'];
		$email = $social_data['email'];
		$facebook = $social_data['facebook'];
		$twitter = $social_data['twitter'];
		$youtube = $social_data['youtube'];
		$instagram = $social_data['instagram'];
		$gplus = $social_data['gplus'];
		$website = $social_data['website'];
		$whatsapp = $social_data['whatsapp'];
		$video = $social_data['video'];
		$skype = $social_data['skype'];

		$age = $personal_data['age'];
		$gender = $personal_data['gender'];
		$ethnicity = $personal_data['ethnicity'];
		$height = $personal_data['height'];
		$hair = $personal_data['hair'];
		$eyes = $personal_data['eyes'];
		$body = $personal_data['body'];
		$boobs = $personal_data['boobs'];
		$boobs_size = $personal_data['boobs_size'];
		$intim = $personal_data['intim'];
		$skin = $personal_data['skin'];
		$body_dekort = explode("#$#",$personal_data['body_dekort']);
		$language = explode("#$#",$personal_data['language']);

		$gallery = explode("#$#", $gallery_data);
		$menu_array = explode('#$#', $menu_details);

		$user_name = str_replace("+", "", $user_name);
		
		$user = get_user_by('login', $user_name);
		$user_id = 0;
		$md5_hash = "";
		
		if(!empty($user)){
			$user_id = $user->ID;
			$old_firstname = $user->first_name;
			$old_lastname = $user->last_name;

			if($first_name=="")
				$first_name = $old_firstname;

			if($last_name=="")
				$last_name = $old_lastname;

			$user_nicename = $first_name." ".$last_name;

			$userdata_args = array(
				'ID'		  => $user_id,
				'first_name'  => $first_name,
				'last_name'   => $last_name,
				'user_nicename' => $user_nicename,
				'display_name' => $user_nicename
			);
			$ret = wp_update_user($userdata_args);
		}else{
			$userdata_args = array(
				'user_login'  => $user_name,
				'user_pass'   => $password,
				'first_name'  => $first_name,
				'last_name'   => $last_name,
				'role'		  => 'owner'
			);
			if($user_email!="")
				$userdata_args['user_email'] = $user_email;

			$user_id = wp_insert_user($userdata_args);
		}

		// check if it is already created pending
		$posts = $wpdb->get_results( $wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE post_title='%s' AND post_author='%d' AND post_type='listing'", $post_title, $user_id) );
			
		if(!empty($posts) && ($type=="update" || $type=="add/update")){
			$post_id = $posts[0]->ID;
			$skipped_post = array();
			$skipped_post['title'] = $post_title;
			$skipped_post['slug'] = $post_name;
			$skipped_posts[] = $skipped_post;

			$post_main = array(
				'ID'           => $post_id,
				'post_title'   => $post_title,
				'post_content' => $post_content,
				'post_status'  => $post_status,
				'post_name'	   => $post_name,
			);
			$update_post = wp_update_post($post_main, true);
			if(is_wp_error($update_post)){
				$update_failed_arr[] = $post_title;
				continue;
			}
		}else if($type=="add" || ($type=="add/update" && empty($posts))){
			$post_main = array(
				'post_title'   => $post_title,
				'post_content' => $post_content,
				'post_status'  => $post_status,
				'post_name'	   => $post_name,
				'post_type'    => 'listing',
				'post_author'  => $user_id
			);
			$post_id = wp_insert_post($post_main);

			if(is_wp_error($post_id)){
				$insert_failed_arr[] = $post_title;
				continue;
			}
			$new_post = array();
			$new_post['title'] = $post_title;
			$new_post['slug'] = $post_name;
			$new_posts[] = $new_post;
		}
		if(metadata_exists( 'post', $post_id, 'md5_hash' )){
			$md5_hash = get_post_meta($post_id, 'md5_hash', true);
		}else{
			if(function_exists('get_listing_hash')){
				$md5_hash = get_listing_hash();
			}else{
				$md5_hash = md5($post_id);
			}	
		}
		

		if(!empty($gallery)){
			/* If we have to upload new images, then reset counter */
			update_post_meta($post_id, 'lp_image_upload_counter', 0);

			foreach ($gallery as $key => $value) {
				$gallery_id = get_attachment_image_id($value, $md5_hash, $post_id);
				if($gallery_id==false){
					$gallery_error_arr[] = $value;
				}else{
					$gallery_image_url = wp_get_attachment_image_src( $gallery_id, 'full' );
					$gallery_arr[$gallery_id] = $gallery_image_url[0];
				}
			}
		}
		$featured_image_id = get_attachment_image_id($featured_image, $md5_hash, $post_id);

		if($listing_category!=""){
			$listing_categories = explode("#$#", $listing_category);
			foreach ($listing_categories as $category) {
				$term_details = get_term_by('name', $category, 'listing_category', ARRAY_A);
				if(!empty($term_details)){
					wp_set_post_terms($post_id, $term_details['term_id'], 'listing_category', true);
				}else{
					$term_details = wp_insert_term($category, 'listing_category');
					wp_set_post_terms($post_id, $term_details['term_id'], 'listing_category', true);
				}
			}
		}

		if($listing_feature!=""){
			$listing_features = explode("#$#", $listing_feature);
			foreach ($listing_features as $category) {
				$term_details = get_term_by('name', $category, 'listing_feature', ARRAY_A);
				if(!empty($term_details)){
					wp_set_post_terms($post_id, $term_details['term_id'], 'listing_feature', true);
				}else{
					$term_details = wp_insert_term($category, 'listing_feature');
					wp_set_post_terms($post_id, $term_details['term_id'], 'listing_feature', true);
				}
			}
		}

		if($region!=""){
			$region_categories = explode("#$#", $region);
			foreach ($region_categories as $category) {
				$term_details = get_term_by('name', $category, 'region', ARRAY_A);
				if(!empty($term_details)){
					wp_set_post_terms($post_id, $term_details['term_id'], 'region', true);
				}else{
					$term_details = wp_insert_term($category, 'region');
					wp_set_post_terms($post_id, $term_details['term_id'], 'region', true);
				}
			}
		}

		$menu = array();
		if(!empty($menu_array)){
			if($menu_title!="")
				$menu[0]['menu_title'] = $menu_title;

			foreach ($menu_array as $menus) {
				$menu_values = explode("==", $menus);
				
				$menu_arr = array();
				$menu_arr['name'] = $menu_values[0];
				$menu_arr['description'] = $menu_values[1];
				$menu_arr['price'] = $menu_values[2];
				$menu_arr['bookable'] = $menu_values[3];
				$menu[0]['menu_elements'][] = $menu_arr;
			}
		}
		$hide_pricing_if_bookable['hide'] = 'hide';
		if(get_post_meta($post_id, 'product_id', true)==""){
			$product_id = save_product($user_id, $post_content, $post_title);
			update_post_meta($post_id, 'product_id', $product_id);
		}

		update_post_meta($post_id, 'slide_template', 'default');
		update_post_meta($post_id, '_listing_type', 'service');
		update_post_meta($post_id, '_layout', 'top');
		update_post_meta($post_id, '_gallery_style', 'top');
		update_post_meta($post_id, '_booking_status', 'on');
		update_post_meta($post_id, '_hide_pricing_if_bookable', maybe_serialize($hide_pricing_if_bookable));
		update_post_meta($post_id, '_opening_hours_status', 'on');

		if(!empty($menu))
			update_post_meta($post_id, '_menu_status', 'on');

		if(get_post_meta($post_id, 'md5_hash', true)==""){
			update_post_meta($post_id, 'md5_hash', $md5_hash);
		}

		update_post_meta($post_id, '_friendly_address', $friendly_address);
		update_post_meta($post_id, '_address', $address);
		//echo "<br />".$address."<br />";
		update_post_meta($post_id, '_geolocation_lat', $latitude);
		update_post_meta($post_id, '_geolocation_long', $longitude);

		if(!empty($gallery_arr)){
			update_post_meta($post_id, '_gallery', $gallery_arr);
		}
			
		update_post_meta($post_id, '_tuesday_opening_hour', $tuesday_opening_hour);
		update_post_meta($post_id, '_tuesday_closing_hour', $tuesday_closing_hour);
		update_post_meta($post_id, '_wednesday_opening_hour', $wednesday_opening_hour);
		update_post_meta($post_id, '_wednesday_closing_hour', $wednesday_closing_hour);
		update_post_meta($post_id, '_thursday_opening_hour', $thursday_opening_hour);
		update_post_meta($post_id, '_thursday_closing_hour', $thursday_closing_hour);
		update_post_meta($post_id, '_friday_opening_hour', $friday_opening_hour);
		update_post_meta($post_id, '_friday_closing_hour', $friday_closing_hour);
		update_post_meta($post_id, '_saturday_opening_hour', $saturday_opening_hour);
		update_post_meta($post_id, '_saturday_closing_hour', $saturday_closing_hour);
		update_post_meta($post_id, '_sunday_opening_hour', $sunday_opening_hour);
		update_post_meta($post_id, '_sunday_closing_hour', $sunday_closing_hour);
		update_post_meta($post_id, '_monday_opening_hour', $monday_opening_hour);
		update_post_meta($post_id, '_monday_closing_hour', $monday_closing_hour);
		/*update_post_meta($post_id, '_verified', "off");*/

		update_post_meta($post_id, '_menu', $menu); 

		update_post_meta($post_id, '_phone', $phone);
		update_post_meta($post_id, '_email', $email);
		update_post_meta($post_id, '_facebook', $facebook);
		update_post_meta($post_id, '_twitter', $twitter);
		update_post_meta($post_id, '_youtube', $youtube);
		update_post_meta($post_id, '_instagram', $instagram);
		update_post_meta($post_id, '_gplus', $gplus);
		update_post_meta($post_id, '_website', $website);
		update_post_meta($post_id, '_whatsapp', $whatsapp);
		update_post_meta($post_id, '_video', $video);
		update_post_meta($post_id, '_skype', $skype);

		if($post_name!=""){
			update_post_meta($post_id, 'listing_random_id', $post_name);
			update_post_meta($post_id, 'listing_random_id_updated', $listing_random_id_updated);
		}

		update_post_meta($post_id, '_thumbnail_id', $featured_image_id);
		set_post_thumbnail( $post_id, $featured_image_id );

		update_post_meta($post_id, 'lp-age-content', $age);
		update_post_meta($post_id, 'lp-gender-content', $gender);
		update_post_meta($post_id, 'lp-ethnicity-content', $ethnicity);
		update_post_meta($post_id, 'lp-height-content', $height);
		update_post_meta($post_id, 'lp-hair-content', $hair);
		update_post_meta($post_id, 'lp-eyes-content', $eyes);
		update_post_meta($post_id, 'lp-body-content', $body);
		update_post_meta($post_id, 'lp-boobs-content', $boobs);
		update_post_meta($post_id, 'lp-boobs-size-content', $boobs_size);
		update_post_meta($post_id, 'lp-intim-content', $intim);
		update_post_meta($post_id, 'lp-skin-content', $skin);
		update_post_meta($post_id, 'lp-body-dekort-content', $body_dekort);
		update_post_meta($post_id, 'lp-language-content', $language);

		$wpdb->update($import_table, array( 'import_status' => 1 ), array( 'ID' => $id ));
	}
	$query = "SELECT ID FROM ".$import_table." WHERE import_status = 0 order by ID ASC";
	$more_res = $wpdb->get_results($query);
	
	if(!empty($insert_failed_arr))	{
		$insert_failed_str = "<p><b>Insert listing errors</b></p><p>";
		foreach ($insert_failed_arr as $key => $value) {
			$insert_failed_str .= "<br />".$value;
		}
		$insert_failed_str .= "</p>";
	}

	if(!empty($update_failed_arr))	{
		$update_failed_str = "<p><b>Update listing errors</b></p><p>";
		foreach ($update_failed_arr as $key => $value) {
			$update_failed_str .= $value."<br />";
		}
		$update_failed_str .= "</p>";
	}

	if(!empty($more_res)){
		echo json_encode(array('more_records' => "yes", 'pending_records' => count($more_res)));
	}else{
		echo json_encode(array('more_records' => "no", 'pending_records' => count($more_res), 'insert_str' => $insert_failed_str, 'update_str' => $update_failed_str));
	}
	die;
}