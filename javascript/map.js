jQuery.noConflict();

(function ($) {
    var map = null;
    var markers;
    var mapInit = false;
    var $map;
    var spinner = $('<i class="uk-icon-spinner uk-icon-spin uk-icon-large"></i>');
    var clustering = false;
    var zoom = false;

    function initMap() {
        map = L.map('geo-map', {
            scrollWheelZoom: false,
            // Disable one finger scroll on mobile (double finger scroll/zoom allowed).
            dragging: !L.Browser.mobile,
            tap: !L.Browser.mobile
        });

        // Create a fullscreen button and add it to the map, @see https://github.com/brunob/leaflet.fullscreen
        // L.control.fullscreen({
        //     position: 'topleft',
        //     title: 'Plein écran',
        //     titleCancel: 'Sortir du plein écran',
        //     content: '<i class="uk-icon-expand"></i>',
        //     // forceSeparateButton: true, // force seperate button to detach from zoom buttons, default false
        //     // forcePseudoFullscreen: true, // force use of pseudo full screen even if full screen API is available, default false
        //     // fullscreenElement: false // Dom element to render in full screen, false by default, fallback to map._container
        // }).addTo(map);

        L.tileLayer(_leafletTileProvider, {
            attribution: _leafletAttribution,
            maxZoom: 18
        }).addTo(map);

        mapInit = true;
        return map;
    }

    function buildMap(data) {
        spinner.hide();

        if (!data.length) {
            $map.empty();
            $map.append('<i class="uk-icon-warning uk-icon-large"></i>');
            return;
        }

        if (!data.length && !mapInit) {
            $map.empty();
            return;
        }

        if (map === null) {
            initMap();
        } else {
            map.removeLayer(markers);
        }
        
        //add markers
        var points = [];
        markers = new L.MarkerClusterGroup();
        for (var i = 0; i < data.length; i++) {
            var item = data[i];
            var point = [item.lat, item.lon];
            var marker = L.marker(point);

            // Map markers clustering (group markers)
            if (clustering) {
                markers.addLayer(marker);
            } else {
                marker.addTo(map);
            }
            marker.bindPopup(item.popup);
            points.push(point);
        }

        // Map markers clustering (group markers)
        if (clustering) {
            map.addLayer(markers);
        }

        // fit to bounds
        var bounds = new L.LatLngBounds(points);
        map.fitBounds(bounds);

        // Allow map fixed zoom
        if (zoom) {
            zoomDelta = map.getZoom() - zoom;
            if (zoomDelta != 0) map.zoomOut(zoomDelta);
        }
        
        $(document).trigger('map-built', [map]);
    }

    $(document).ready(function () {
        $map = $('#geo-map'); // @TODO define map-$ID as id to may use multiple maps
        $map.append(spinner);
        var firstItem = $('[data-map=\'controls\'] a').eq(0);
        firstItem.parents('li').addClass('uk-active');
        $.getJSON($map.data('src'), {type: firstItem.data('type')}, buildMap);

        // Map markers clustering (group markers)
        clustering = $map.data('clustering');
        // Allow map fixed zoom
        zoom = $map.data('zoom');

        $('.js-map-control .uk-tab a').click(function (e) {
            e.preventDefault();
            spinner.show();
            $.getJSON($map.data('src'), {
                type: $(this).data('type')
            }, buildMap);
        });
    });
}(jQuery));