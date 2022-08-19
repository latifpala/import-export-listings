<?php
add_action('admin_menu' , 'lp_ie_page_settings');
function lp_ie_page_settings() {
	add_submenu_page('edit.php?post_type=listing', 'Import/Export', 'Import/Export', 'edit_posts', 'lp_import_export', 'lp_import_export_func');
}

function lp_import_export_func(){
	
?>
	<div class="wrap">
		<h1>Import / Export</h1>
		<form method="post">
			<h3>Export</h3>
			<p class="submit"><input type="submit" name="export_listings" id="export_listings" class="button button-primary" value="Export Listings"></p>
		</form>
		<hr />
		<form id="upload_csv_frm" method="post" enctype="multipart/form-data">
			<h3>Import</h3>
			<input type="hidden" name="action" id="action" value="import_ajax" />
			<input type="hidden" name="total_records" id="total_records" value="0" />
			<input type="hidden" name="processed_records" id="processed_records" value="0" />
			<p><input type="file" name="import_csv_file" id="import_csv_file" accept=".csv" /></p>
			<p class="submit"><input type="submit" name="import_listings" id="import_listings" class="button button-primary" value="import Listings"></p>
		</form>
		<hr />
		<!-- <div id="import_data_result">Messages and errors</div>
		<div id="import_data_total_records">This will store total records</div>
		<div id="import_data_inserted_records">This will display records inserted in import data</div>
		<div id="import_data_processed_records">This will display processed records from import data</div> -->
		<div id="import_data_result"></div>
		<div id="import_data_total_records"></div>
		<div id="import_data_inserted_records"></div>
		<div id="import_data_processed_records"></div>
	</div>
<?php
}

add_action( 'init', 'func_export_all_posts' );
function func_export_all_posts() {
    if(isset($_POST['export_listings'])){
		$titles = array(
			'user_email',
			'user name',
			'password',
			'Listing Title',
			'Listing Content',
			'post_name',
			'post_status',
			'listing_category',
			'listing_feature',
			'region',
			'_friendly_address',
			'_address',
			'_geolocation_lat',
			'_geolocation_long',
			'_layout',
			'_gallery', 
			'_tuesday_opening_hour', '_tuesday_closing_hour', '_wednesday_opening_hour', '_wednesday_closing_hour', '_thursday_opening_hour', '_thursday_closing_hour', '_friday_opening_hour', '_friday_closing_hour', '_saturday_opening_hour', '_saturday_closing_hour', '_sunday_opening_hour', '_sunday_closing_hour',	'_monday_opening_hour',	'_monday_closing_hour',
			'menu_title',
			'_menu',
			'_phone', '_email',	'_facebook', '_twitter', '_youtube', '_instagram', '_gplus', '_website', '_whatsapp', '_video', '_skype',
			'Featured Image',
			'age',
			'gender',
			'ethnicity',
			'height',
			'hair',
			'eyes',
			'body',
			'boobs',
			'boobs-size',
			'intim',
			'skin',
			'body-dekort',
			'language'
		);
		$post_data = array();
		$arg = array(
			'post_type' => 'listing',
			'post_status' => array('preview', 'trash', 'publish', 'expired', 'draft', 'pending_payment'),
			'posts_per_page' => -1,
			'orderby' => 'ID',
			'order' => 'DESC',
		);
		$arr_post = get_posts($arg);
		foreach ($arr_post as $post) {
			setup_postdata($post);
			$post_id = $post->ID;
			$listing_category = get_the_terms( $post_id, 'listing_category' );
			$listing_feature = get_the_terms( $post_id, 'listing_feature' );
			$region = get_the_terms( $post_id, 'region' );

			$listing_category_arr = $event_category_arr = $service_category_arr = $rental_category_arr = $listing_feature_arr = $region_arr = $gallery_array = $menu_array = array();
			$user_email = $user_name = $gallery_urls = $menu_content = "";

			$user_id = $post->post_author;
			$user_details = get_user_by('ID', $user_id);
			if(!empty($user_details)){
				$user_email = $user_details->user_email;
				$user_name = $user_details->user_login;
			}else{
				$user_email = "";
				$user_name = "";
			}


			if(!empty( $listing_category ) && ! is_wp_error( $listing_category )){
				$listing_category_arr = wp_list_pluck( $listing_category, 'name' );
			}

			if(!empty( $listing_feature ) && ! is_wp_error( $listing_feature )){
				$listing_feature_arr = wp_list_pluck( $listing_feature, 'name' );
			}

			if(!empty( $region ) && ! is_wp_error( $region )){
				$region_arr = wp_list_pluck( $region, 'name' );
			}

			$listeo_avg_rating = get_post_meta($post_id, 'listeo-avg-rating', true);
			$slide_template = get_post_meta($post_id, 'slide_template', true);
			$listing_type = get_post_meta($post_id, '_listing_type', true);
			$friendly_address = get_post_meta($post_id, '_friendly_address', true);
			$address = get_post_meta($post_id, '_address', true);
			$geolocation_lat = get_post_meta($post_id, '_geolocation_lat', true);
			$geolocation_long = get_post_meta($post_id, '_geolocation_long', true);
			$layout = get_post_meta($post_id, '_layout', true);
			$gallery = get_post_meta($post_id, '_gallery', true);

			if(!empty($gallery) && is_array($gallery)){
				foreach ($gallery as $key => $value) {
					$gallery_array[] = $value;
				}
			}
			$gallery_urls = implode("#$#", $gallery_array);
			
			$tuesday_opening_hour = get_post_meta($post_id, '_tuesday_opening_hour', true);
			$tuesday_closing_hour = get_post_meta($post_id, '_tuesday_closing_hour', true);
			$wednesday_opening_hour = get_post_meta($post_id, '_wednesday_opening_hour', true);
			$wednesday_closing_hour = get_post_meta($post_id, '_wednesday_closing_hour', true);
			$thursday_opening_hour = get_post_meta($post_id, '_thursday_opening_hour', true);
			$thursday_closing_hour = get_post_meta($post_id, '_thursday_closing_hour', true);
			$friday_opening_hour = get_post_meta($post_id, '_friday_opening_hour', true);
			$friday_closing_hour = get_post_meta($post_id, '_friday_closing_hour', true);
			$saturday_opening_hour = get_post_meta($post_id, '_saturday_opening_hour', true);
			$saturday_closing_hour = get_post_meta($post_id, '_saturday_closing_hour', true);
			$sunday_opening_hour = get_post_meta($post_id, '_sunday_opening_hour', true);
			$sunday_closing_hour = get_post_meta($post_id, '_sunday_closing_hour', true);
			$monday_opening_hour = get_post_meta($post_id, '_monday_opening_hour', true);
			$monday_closing_hour = get_post_meta($post_id, '_monday_closing_hour', true);
			
			$verified = get_post_meta($post_id, '_verified', true);
			$menu = get_post_meta($post_id, '_menu', true);
			$menu_title = "";
			if(!empty($menu) && is_array($menu)){
				foreach ($menu as $key => $value) {
					if(isset($value['menu_title'])){
						$menu_title = $value['menu_title'];	
					}
					if(!empty($value['menu_elements'])){
						foreach ($value['menu_elements'] as $menu_elements) {
							$menu_rec = "";
							$menu_rec .= $menu_elements['name']."==";
							$menu_rec .= $menu_elements['description']."==";
							$menu_rec .= $menu_elements['price']."==";
							
							if(isset($menu_elements['bookable'])){
								if($menu_elements['bookable'])
									$menu_rec .= "on";
								else
									$menu_rec .= "off";
							}else{
								$menu_rec .= "off";
							}
							if(!empty($menu_elements['name']) && !empty($menu_elements['description']) && !empty($menu_elements['price'])){
								$menu_array[$menu_title] = $menu_rec;
							}
						}
					}
				}
			}
			$menu_content = implode('#$#', $menu_array);

			$gallery_style = get_post_meta($post_id, '_gallery_style', true);
			$bookmarks_counter = get_post_meta($post_id, 'bookmarks_counter', true);
			$listing_title = get_post_meta($post_id, 'listing_title', true);
			$keywords = get_post_meta($post_id, 'keywords', true);
			$listing_description = get_post_meta($post_id, 'listing_description', true);
			$phone = get_post_meta($post_id, '_phone', true);
			$email = get_post_meta($post_id, '_email', true);
			$facebook = get_post_meta($post_id, '_facebook', true);
			$twitter = get_post_meta($post_id, '_twitter', true);
			$youtube = get_post_meta($post_id, '_youtube', true);
			$instagram = get_post_meta($post_id, '_instagram', true);
			$gplus = get_post_meta($post_id, '_gplus', true);
			$website = get_post_meta($post_id, '_website', true);
			$whatsapp = get_post_meta($post_id, '_whatsapp', true);
			$video = get_post_meta($post_id, '_video', true);
			$skype = get_post_meta($post_id, '_skype', true);
			
			$slots = get_post_meta($post_id, '_slots', true);
			$expired_after = get_post_meta($post_id, '_expired_after', true);
			$opening_hours = get_post_meta($post_id, '_opening_hours', true);
			$email_contact_widget = get_post_meta($post_id, '_email_contact_widget', true);
			$opening_hours_status = get_post_meta($post_id, '_opening_hours_status', true);
			$menu_status = get_post_meta($post_id, '_menu_status', true);
			$featured = get_post_meta($post_id, '_featured', true);
			$service_avg = get_post_meta($post_id, 'service-avg', true);
			$value_for_money_avg = get_post_meta($post_id, 'value-for-money-avg', true);
			$location_avg = get_post_meta($post_id, 'location-avg', true);
			$cleanliness_avg = get_post_meta($post_id, 'cleanliness-avg', true);
			$price_min = get_post_meta($post_id, '_price_min', true);
			$price_max = get_post_meta($post_id, '_price_max', true);
			$booking_status = get_post_meta($post_id, '_booking_status', true);
			$hide_pricing_if_bookable = get_post_meta($post_id, '_hide_pricing_if_bookable', true);
			$listing_random_id = get_post_meta($post_id, 'listing_random_id', true);
			$listing_random_id_updated = get_post_meta($post_id, 'listing_random_id_updated', true);
			
			$thumbnail_id = get_post_meta($post_id, '_thumbnail_id', true);
			$thumbnail_details = wp_get_attachment_image_src( $thumbnail_id, 'full' );			
			$featured_image_url = 	$thumbnail_details[0];

			$city = get_post_meta($post_id, '_city', true);
			$reservation_price = get_post_meta($post_id, '_reservation_price', true);

			$rooms = get_post_meta($post_id, '_rooms', true);
			$bedrooms = get_post_meta($post_id, '_bedrooms', true);
			$bathrooms = get_post_meta($post_id, '_bathrooms', true);
			$bedtype = get_post_meta($post_id, '_bedtype', true);
			$normal_price = get_post_meta($post_id, '_normal_price', true);
			$weekday_price = get_post_meta($post_id, '_weekday_price', true);
			$availability = get_post_meta($post_id, '_availability', true);
			$event_tickets_sold = get_post_meta($post_id, '_event_tickets_sold', true);
			$count_per_guest = get_post_meta($post_id, '_count_per_guest', true);
			$max_guests = get_post_meta($post_id, '_max_guests', true);
			$instant_booking = get_post_meta($post_id, '_instant_booking', true);
			$event_date = get_post_meta($post_id, '_event_date', true);
			$event_date_end = get_post_meta($post_id, '_event_date_end', true);
			$size = get_post_meta($post_id, '_size', true);
			$slots_status = get_post_meta($post_id, '_slots_status', true);
			$event_tickets = get_post_meta($post_id, '_event_tickets', true);
			
			/* Personal Data */
			$age = get_post_meta($post_id, 'lp-age-content', true);
			$gender = get_post_meta($post_id, 'lp-gender-content', true);
			$ethnicity = get_post_meta($post_id, 'lp-ethnicity-content', true);
			$height = get_post_meta($post_id, 'lp-height-content', true);
			$hair = get_post_meta($post_id, 'lp-hair-content', true);
			$eyes = get_post_meta($post_id, 'lp-eyes-content', true);
			$body = get_post_meta($post_id, 'lp-body-content', true);
			$boobs = get_post_meta($post_id, 'lp-boobs-content', true);
			$boobs_size = get_post_meta($post_id, 'lp-boobs-size-content', true);
			$intim = get_post_meta($post_id, 'lp-intim-content', true);
			$skin = get_post_meta($post_id, 'lp-skin-content', true);
			$body_dekort = get_post_meta($post_id, 'lp-body-dekort-content', true);
			$language = get_post_meta($post_id, 'lp-language-content', true);

			$body_dekort = str_replace(",", "#$#", $body_dekort);
			$language = str_replace(",", "#$#", $language);

			$post_data[] = array(
				$user_email,
				$user_name,
				'',
				$post->post_title,
				$post->post_content,
				$post->post_name,
				$post->post_status,
				implode("#$#",$listing_category_arr),
				implode("#$#",$listing_feature_arr),
				implode("#$#",$region_arr),
				$friendly_address,
				$address,
				$geolocation_lat,
				$geolocation_long,
				$layout,
				$gallery_urls,
				$tuesday_opening_hour,
				$tuesday_closing_hour,
				$wednesday_opening_hour,
				$wednesday_closing_hour,
				$thursday_opening_hour,
				$thursday_closing_hour,
				$friday_opening_hour,
				$friday_closing_hour,
				$saturday_opening_hour,
				$saturday_closing_hour,
				$sunday_opening_hour,
				$sunday_closing_hour,
				$monday_opening_hour,
				$monday_closing_hour,
				$menu_title,
				$menu_content,
				$phone,
				$email,
				$facebook,
				$twitter,
				$youtube,
				$instagram,
				$gplus,
				$website,
				$whatsapp,
				$video,
				$skype,
				$featured_image_url,
				$age,
				$gender,
				$ethnicity,
				$height,
				$hair,
				$eyes,
				$body,
				$boobs,
				$boobs_size,
				$intim,
				$skin,
				$body_dekort,
				$language
			);
		}
		header('Content-type: text/csv');
		header('Content-Disposition: attachment; filename="wp.csv"');
		header('Pragma: no-cache');
		header('Expires: 0');

		$file = fopen('php://output', 'w');

		fputcsv($file, $titles, ';');
		foreach ($post_data as $key => $data) {
			fputcsv($file, $data, ';');
		}
        exit();
	}
}