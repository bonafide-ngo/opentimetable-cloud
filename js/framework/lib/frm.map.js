// Init
var frm = frm || {};

/*******************************************************************************
Framework - Map
*******************************************************************************/

// Set
frm.map = {};
frm.map.marker = null;
frm.map.position = null;

/**
 * Listen to map
 * @returns 
 */
frm.map.listen = function (latitude, longitude, code, name) {
    // Init
    name = name || null;

    // Set header
    $('#modal-map').find('[name="header"]').find('[name="name"]').text(code + (name ? ' - ' + name : ''));
    // Set popup
    $('#modal-map-popup').find('[name="code"]').text(code);
    $('#modal-map-popup').find('[name="name"]').text(name);

    // Open/close modal event
    $('#modal-map').once('shown.bs.modal', function () {
        frm.map.position = { coords: { latitude: latitude, longitude: longitude } };
        if (!window.geolocation.atlas)
            frm.map.draw('modal-map-atlas', frm.map.position, true);
        else
            // Update map
            frm.map.marker.setLatLng([frm.map.position.coords.latitude, frm.map.position.coords.longitude]).update();

        // Directions
        frm.map.bindDirections();
    }).once('hide.bs.modal', function () {
        // Reset geolocation
        frm.initGeolocation();
    }).once('hidden.bs.modal', function () {
    }).modal('show');
}

/**
 * Bind directions
 */
frm.map.bindDirections = function () {
    // Trigger directions
    $('#modal-map').find('[name="directions"]').once('click', function () {
        //Set url
        if (isMobile.any)
            var url = frm.config.url.directionsGPS;
        else
            var url = frm.config.url.directionsStatic;

        // Open in another page
        window.open(url.sprintf([frm.map.position.coords.latitude, frm.map.position.coords.longitude]), '_blank');
    });
}

/**
 * Draw a map
 * 
 * @param {*} id 
 * @param {*} position 
 * @param {*} fitBounds 
 */
frm.map.draw = function (id, position, fitBounds) {
    fitBounds = fitBounds || false;

    // Create atlas
    window.geolocation.atlas = L.map(id, { preferCanvas: true, zoomControl: false }).setView([position.coords.latitude, position.coords.longitude], 18);
    // Override attribution 
    window.geolocation.atlas.attributionControl.setPrefix('<a href="https://leafletjs.com/" target="_blank">Leaflet</a>');

    // Add layer
    switch (frm.config.map.layer) {
        case C_MAP_ESRI:
            L.esri.tiledMapLayer({
                url: frm.config.map.layers[frm.config.map.layer].tile
            }).addTo(window.geolocation.atlas);
            break;
        default:
            L.tileLayer(frm.config.map.layers[frm.config.map.layer].tile, {
                attribution: `<a href="${frm.config.map.layers[frm.config.map.layer].attribution.href}" target="_blank">${frm.config.map.layers[frm.config.map.layer].attribution.text}</a>`,
                subdomains: frm.config.map.layers[frm.config.map.layer].subdomains
            }).addTo(window.geolocation.atlas);
            break;
    }

    // Add zoom controls
    new L.Control.Zoom({ position: 'bottomleft' }).addTo(window.geolocation.atlas);

    // Clone template
    template = $('#modal-map-popup').find('div').clone();

    // Add pin location
    frm.map.marker = L.marker([position.coords.latitude, position.coords.longitude]).bindPopup(template[0].outerHTML).addTo(window.geolocation.atlas).openPopup();
}