let args = {
	'q': 'Eibergen',
	'email': 'boete@avondklokboete.nl',
	'format': 'jsonv2',
	'extratags': '1',
	'limit': 1
};

jQuery(document).ready(function() {
	jQuery('#field_1_5 #input_1_5').after('<div class="">test</div>');
});

jQuery.get('https://nominatim.openstreetmap.org/search?' + jQuery.param( args ), function( success ){

	var geojson = '';

	// On success, build a GeoJSON object
	if ( success.length > 0 ) {
		var res = success[0];

		geojson = {
			'type': 'Feature',

			'geometry': {
				'type': 'Point',
				'coordinates': [
					parseFloat(res.lon),
					parseFloat(res.lat)
				],
			}
		};

		delete res.boundingbox;
		var props = res.extratags || {};
		delete res.extratags;
		delete res.lat;
		delete res.lon;

		props = jQuery.extend( props, res );

		geojson.properties = props;
	};

	if ( geojson === '' ) {
		//
	} else {
		console.log(geojson);
	}
});