var over;

jQuery(document).ready(function($) {
	maps = $('.travelling_blogger_map');
	maps.each(function(i, e) {
		cat = $(e).find('.travelling_blogger_map_cat').text();
		marker = $(e).find('.travelling_blogger_map_marker').text();
		mapType = $(e).find('.travelling_blogger_map_type').text();
		mapType = eval('google.maps.MapTypeId.'+mapType);
		
		$(e).find('*').remove();
		
		latlng = new google.maps.LatLng(0,0);
		myOptions = {
			zoom: 1,
			center: latlng,
			mapTypeId: mapType,
			disableDefaultUI: false,
		};
		
		map = new google.maps.Map(e,myOptions);

		infowindow = new google.maps.InfoWindow();
		
		//baseUrl = travelling_blogger.kmlurl;
		baseUrl = 'http://otago.maximerainville.com/wananga/wp-admin/admin-ajax.php'; //Testing URL
		url = baseUrl+'?action=travelling_blogger_kml'
		if (cat != '') {url += '&cat=' + cat;}
		if (marker != '') {url += '&marker=' + marker;}
		url += '&time=' + (new Date).getTime();

		kmlLayer = new google.maps.KmlLayer(url, {preserveViewport: false, map: map, suppressInfoWindows: true});

		google.maps.event.addListener(kmlLayer, 'click', function(kmlEvent) {
			var content = $(kmlEvent.featureData.infoWindowHtml);
			content.find('a').attr('target', '_self');
			infowindow.setContent(content.html());
			infowindow.setPosition(kmlEvent.latLng);
			infowindow.open(map);
		});
	});

	miniMaps = $('.travelling_blogger_minimap');
	miniMaps.each(function (i, e) {
		mapType = $(e).find('.travelling_blogger_map_type').text();
		mapType = eval('google.maps.MapTypeId.'+mapType);
		lat = $(e).find('.travelling_blogger_lat').text();
		lng = $(e).find('.travelling_blogger_lng').text();
		latlng = new google.maps.LatLng(lat,lng);
		locationName = $(e).find('.travelling_blogger_map_location_name').text();

		$(e).find('*').remove();

		myOptions = {
			zoom: 7,
			center: latlng,
			mapTypeId: mapType,
			disableDefaultUI: true,
			draggable: false,
			maxZoom: 7,
			minZoom: 7
		};

		map = new google.maps.Map(e,myOptions);

		new google.maps.Marker({
			position: latlng, 
			map: map
		});

	});



});



