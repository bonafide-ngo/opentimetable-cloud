// Init
var frm = frm || {};

/*******************************************************************************
Framework - URI
*******************************************************************************/
frm.uri = {};

/**
 * Parse a URL and check if absolute
 * @param {*} url 
 */
frm.uri.isAbsolute = function (url) {
    var uri = new URI(url);
    return uri.is("absolute");
};

/**
 * Parse a URL and check if relative
 * @param {*} url 
 */
frm.uri.isRelative = function (url) {
    var uri = new URI(url);
    return uri.is("relative");
};

/**
 * Parse a URL and return the GET parameters as a object
 * @param {*} url 
 */
frm.uri.parse = function (url) {
    var URI = new URI(url);
    return URI.parseQuery(uri.search());
};

/**
 * Check if a GET parameter is set in the URL
 * @param {*} param 
 * @param {*} url 
 */
frm.uri.isParam = function (param, url) {
    // Default parameters
    url = url || window.location.href;

    // Parse the URL by using URI.js
    var uri = new URI(url);
    // Get the Query Parameters
    var paramsURL = URI.parseQuery(uri.search());
    if (param in paramsURL)
        return true;
    else
        return false;
};

/**
 * Return the GET parameter set in the URL
 * @param {*} param 
 * @param {*} url 
 */
frm.uri.getParam = function (param, url) {
    // Default parameters
    url = url || window.location.href;

    // Parse the URL by using URI.js
    var uri = new URI(url);
    // Get the Query Parameters
    var paramsURL = URI.parseQuery(uri.search());
    if (param in paramsURL)
        return paramsURL[param];
    else
        return '';
};

/**
 * Ad dor replace a URL parameter
 * @param {*} param 
 * @param {*} value 
 * @param {*} url 
 * @returns 
 */
frm.uri.addParam = function (param, value, url) {
    // Default parameters
    url = url || window.location.href;

    // This will add param=value or replace an existing one
    return new URI(url).setSearch(param, value);
};

/**
 * Return the presetn URL without hash target if any
 * @param {*} param 
 * @param {*} url 
 */
frm.uri.getHashlessURL = function (url) {
    // Default parameters
    url = url || window.location.href;

    urls = url.split('#');
    return urls[0];
};
