(function($) {
	$.entwine('ss', function($){
		$('.geo-ext').entwine({
			onmatch: function() {
				let latitude = $('[name="Latitude"]').val();
				let longitude = $('[name="Longitude"]').val();
				console.log('Load leaflet map with marker position', latitude, longitude);
				let map = L.map('geo-map').setView([latitude,longitude], 13);
				L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {}).addTo(map);
				L.marker([latitude,longitude]).addTo(map);
            }
		});
	});
})(jQuery);