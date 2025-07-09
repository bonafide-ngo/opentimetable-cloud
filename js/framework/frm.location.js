/*******************************************************************************
Framework - Location
*******************************************************************************/

// Check if the framework exists
if (typeof frm === 'undefined') {
    // User landed here straight or attemped a page reload
    // Redirect to the origin appending the pathname and the search for loading the intent correclty
    // https://developer.mozilla.org/en-US/docs/Web/API/Location

    // Enable the following code if the entry point is in app
    /*
    var origin = '';
    var paths = (window.location.pathname.substring(1)).split('/');

    if (paths.length && paths[0] == 'app') {
        // app/etc/page.html
        origin = window.location.origin + '/' + paths[0];
    } else {
        // etc/page.html
        origin = window.location.origin;
    }
    */

    // Reload
    window.location.href = window.location.origin + '/?pathname=' + encodeURIComponent(window.location.pathname) + '&search=' + encodeURIComponent(window.location.search);
} else if (frm.uri.isParam('pathname')) {
    // Set if a redirection is in place
    frm.location = {};
    frm.location.pathname = frm.uri.getParam('pathname');
    frm.location.search = frm.uri.getParam('search');
}

// Check smootState and hashHEAD exist in running environment
if (frm.ss && frm.ss.engine && frm.config.hashHEAD)
    // Fetch and compare hashHEAD
    frm.ajax.fetch('/config/config.json', function (data, textStatus, jqXHR) {
        if (frm.config.hashHEAD != frm.ajax.hashHEAD(jqXHR)) {
            // Clear smoothState cache
            frm.ss.engine.clear();
            // Reload with no history
            window.location.reload();
        }
    });