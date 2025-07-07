// Init
var frm = frm || {};

/*******************************************************************************
Framework - Configuration
*******************************************************************************/

// Set
frm.config = {};

/**
 * Preset JQuery Ajax calls to be Async by default
 * @param {*} options
 * @param {*} originalOptions
 * @param {*} jqXHR
 */
$.ajaxPrefilter(function (options, originalOptions, jqXHR) {
    options.async = originalOptions.async === undefined ? true : originalOptions.async;
});

// Load the configuration
frm.ajax.config("/config/config.json", function (pConfig, textStatus, jqXHR) {
    // Set hashHEAD
    pConfig.hashHEAD = frm.ajax.hashHEAD(jqXHR);
    // Normalise expires for JS-Cookie compability
    $.each(pConfig.cookie.property, function (index, items) {
        $.each(items, function (key, item) {
            if (!item.httponly)
                pConfig.cookie.property[index][key].expires = Math.round(item.expires / 24 / 3600);
            if (!item.expires)
                delete pConfig.cookie.property[index][key].expires;
        });
    });
    // Store for later
    frm.config = pConfig;
});

