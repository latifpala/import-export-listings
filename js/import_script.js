jQuery(document).ready(function(){
	jQuery('#upload_csv_frm').on('submit', function(event){
		event.preventDefault();
		jQuery("#import_listings").val("Please wait...");
		jQuery("#import_listings").attr("disabled", "disabled");
		jQuery.ajax({
			url: obj_import.ajax_url,
			method:"POST",
			data:new FormData(this),
			dataType:'json',
			contentType:false,
			cache:false,
			processData:false,
			success:function(jsonData){
				if(jsonData.status=="success"){
					jQuery('#import_data_total_records').html(jsonData.msg); 
					jQuery('#total_records').val(jsonData.total_records);
					process_imported_records();
				}else{
					jQuery('#import_data_result').html(jsonData.msg);
				}
			},
			error:function(res, error_type){
				jQuery('#import_csv_file').val('');
				jQuery('#import_data_result').html("<p style='color:red; font-weight:bold;'>Something went wrong. Please try again.</p>");	
				jQuery("#import_listings").val("Import Listings");
				jQuery("#import_listings").removeAttr("disabled");
			},
			timeout: 0
		});
	});
});

function process_imported_records(){
	jQuery.ajax({
		url: obj_import.ajax_url,
		method:"POST",
		data:{'action': 'process_records'},
		dataType:'json',
		success:function(res){
			jQuery('#import_data_inserted_records').html("<p style='color:green;'><span style='font-weight:bold;'>"+res.processed_records+"</span> Records Processed</p>");
			if(res.more_records=="no"){
				jQuery('#import_data_inserted_records').append("<p style='color:green; font-weight:bold;'>Starting Import now</p>");
				import_listings();
			}else{
				process_imported_records();
			}
		},
		error:function(res, error_type){
			process_imported_records();
		},
		timeout: 0
	});
}

function import_listings(){
	jQuery.ajax({
		url: obj_import.ajax_url,
		method:"POST",
		data:{'action': 'import_records'},
		dataType:'json',
		success:function(res){
			var total_records = parseInt(jQuery('#total_records').val());
			var pending_records = parseInt(res.pending_records);
			var processed_records = total_records-pending_records;
			jQuery('#processed_records').val(processed_records);

			//var processed_records_display = parseInt(jQuery('#processed_records').val());
			var msg_display = processed_records+" of total "+total_records+" records processed<br />";
			
			jQuery('#import_data_processed_records').html(msg_display);

			if(res.more_records=="no"){
				jQuery('#import_csv_file').val('');
				jQuery("#import_listings").val("Import Listings");
				jQuery("#import_listings").removeAttr("disabled");

				jQuery('#import_data_result').append(jsonData.insert_str);
				jQuery('#import_data_result').append(jsonData.update_str);
			}else{
				import_listings();
			}
		},
		error:function(res, error_type){
			import_listings();
		},
		timeout: 0
	});
}