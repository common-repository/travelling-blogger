jQuery(document).ready(function($) {
	// Location table page
	$('#location-filter .delete a').click(function() {
		return confirm(commonL10n.warnDelete);
	});
	
	$('form#addlocation').submit(function () {
		form = $('form#addlocation');

		//Get new location name
		newName = jQuery.trim(form.find('#location-name').val());
		if (newName == '') {
			form.find('#location-name').focus();
			return false;
		}
		
		//Hide error if any
		$('.error').hide();
		
		//Set up request
		var data = {
			action: 'travelling_blogger_new_location',
			new_location: newName,
			_wp_nonce: form.find('#_wp_nonce').val()
		}
		
		console.dir(data);

		jQuery.post(ajaxurl, data, function (response) {
			error = $(response).find('error').text();
			if (error != '') {
				$('#' + error).show();
			} else {
				id = $(response).find('id').text();
				name = $(response).find('name').text();
				coords = $(response).find('coords').text();
				nonce = $(response).find('nonce').text();
				addRow2Table(id, name, coords, 0, nonce);
				form.find('#location-name').val('');
			}
		}, 'xml');
		
		return false;
	});
	
	function addRow2Table(id, name, coords, posts, nonce) {
		body = $('form#location-filter tbody');
		alternate = (body.find('tr').size() % 2) == 0;
		tr = $('<tr />');
		if (alternate) {tr.addClass('alternate');}

		//checkbox		
		th = $('<th scope="row" class="check-column"/>');
		th.html('<input type="checkbox" name="location[]" value="' + id + '">');
		th.appendTo(tr);
		
		// Location Name
		td = $('<td  />').addClass('name column-name');
		$('<strong><a href="?page=travelling-blogger&amp;action=edit&amp;location=' + id + '">' + name + '</a></strong>').appendTo(td);
		div = $('<div />').addClass('row-actions');
		$('<span class="edit"><a href="?page=travelling-blogger&action=edit&location=' + id + '">' + 
			travelling_blogger.edit_label + '</a> | </span>').appendTo(div);
		$('<span class="delete"><a href="?page=travelling-blogger&action=delete&location=' +
			id + '&_wp_nonce=' + nonce + '&paged=1">' + 
			travelling_blogger.delete_label + '</a></span>').appendTo(div);
		div.appendTo(td);
		td.appendTo(tr)
		
		//Coords
		$('<td  />').addClass('coords column-coords').html(coords).appendTo(tr);
		
		//Posts
		$('<td  />').addClass('post_count column-post_count').html(posts).appendTo(tr);
		
		//Add row at beginning of table
		tr.prependTo(body);
	}
	
	// Edit Location page
	$('#mapLocationEdit').each(function (i,e) {
		var lat = $('#lat');
		var lng = $('#lng');
		var latlng = new google.maps.LatLng(lat.val(), lng.val());
		var locationName = $('#name');
		var myOptions = {
			zoom: 8,
			center: latlng,
			mapTypeId: google.maps.MapTypeId.TERRAIN
		};
	    var map = new google.maps.Map(e, myOptions);
	    var marker = new google.maps.Marker({
	    	map: map,
	    	draggable: true,
	    	position: latlng
	    });
	    
		$('#geocode').click(function () {
			if (jQuery.trim(locationName.val()) == '') {
				locationName.focus();
				return false;
			}

			$('.error').hide();
			
			geocoder = new google.maps.Geocoder();
			geocoder.geocode( { 'address': locationName.val(), bounds: map.getBounds()}, function(results, status) {
				if (status == google.maps.GeocoderStatus.OK) {
					map.setCenter(results[0].geometry.location);
					marker.setPosition(results[0].geometry.location);
				} else {
					$('.error').show();
				}
			});
			return false;
		});
		
		$('#editlocation').submit(function() {
			if (jQuery.trim(locationName.val()) == '') {
				locationName.focus();
				return false;
			}

			coords = marker.getPosition()
			lat.val(coords.lat());
			lng.val(coords.lng());			
		});
	    
	});
});



