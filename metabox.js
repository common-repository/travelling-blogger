jQuery(document).ready(function($) {
	$('#location-add-toggle').click(function() {
		$('#location-add').toggle();
		return false;
	});

	$('#location-add-submit').click(function() {
		$('#travelling_blogger_meta_box .error').hide();
		var new_location = jQuery.trim($('#newlocation').val());
		if (new_location == '') {
			$('#newlocation').focus(); 
			return;
		}
		var data = {
			action: 'travelling_blogger_new_location',
			new_location: new_location,
			_wp_nonce: $('#travelling_blogger_wp_nonce').val()
		}

		jQuery.post(ajaxurl, data, function (response) {
			error = $(response).find('error').text();
			if (error != '') {
				$('#' + error).show();
			} else {
				select = $('#travelling_blogger_cur_location');
				id = $(response).find('id').text();
				name = $(response).find('name').text();
				$('<option value="' + id + '">' + name +'</option>').appendTo(select);
				select.val(id);
				$('#location-add').hide();
			}
		}, 'xml');
	});
});



