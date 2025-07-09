// Init
var frm = frm || {};

/*******************************************************************************
Framework - Ajax
*******************************************************************************/
frm.ajax = {};
frm.ajax.jsonrpc = {};
frm.ajax.content = {};
frm.ajax.content.params = {};

/**
 * Hash the HEAD request
 * 
 * @param {*} jqXHR 
 */
frm.ajax.hashHEAD = function (jqXHR) {
    return jqXHR ? frm.crypto.md5(jqXHR.getResponseHeader('content-length') + jqXHR.getResponseHeader('etag') + jqXHR.getResponseHeader('last-modified')) : null;
};

/**
 * Get a Parameter
 * @param {*} pKey 
 */
frm.ajax.content.getParam = function (pKey) {
    return frm.ajax.content.params[pKey];
};

/**
 * Load a Relative URL into a Container
 * @param {*} pSelectorContainer 
 * @param {*} pRelativeURL 
 * @param {*} pParams 
 * @param {*} pAppend 
 */
frm.ajax.content.load = function (pSelectorContainer, pRelativeURL, pParams, pAppend) {
    // Default parameters
    pParams = pParams || {};
    pAppend = pAppend || false;

    // Validate the Relative URL
    if (!frm.uri.isRelative(pRelativeURL))
        return;

    /**
     * Load the URL straight
     * Set async to false to enforce script serialization
     * https://github.com/jquery/jquery/issues/4213
     */
    $.ajax({
        url: pRelativeURL,
        async: false,
        success: function (response) {
            frm.ajax.content.params = pParams;
            if (pAppend)
                $(pSelectorContainer).append(response).promise().done(function () {
                    frm.ajax.content.params = {};
                });
            else
                $(pSelectorContainer).empty().html(response).promise().done(function () {
                    frm.ajax.content.params = {};
                });
        }
    });
};

/**
 * Execute an AJAX callback function
 * @param {*} pFunction 
 * @param {*} pResponse 
 * @param {*} pParams 
 */
frm.ajax.callback = function (pFunction, pResponse, pParams) {
    // Default parameters
    pResponse = pResponse || null;
    pParams = pParams || {};

    // Context is windows in a browser
    var context = window;


    // Check if it is a function itself
    if (typeof pFunction === 'function') {
        if (jQuery.isEmptyObject(pParams))
            return pFunction(pResponse);
        else
            return pFunction(pResponse, pParams);
    }

    // Look for the function within the scope
    var callbackFunction = context[pFunction];
    // Run a function that is not namespaced
    if (typeof callbackFunction === 'function') {
        if (jQuery.isEmptyObject(pParams))
            return callbackFunction(pResponse);
        else
            return callbackFunction(pResponse, pParams);
    }

    // Retrieve the namespaces of the function
    // e.g Namespaces of "MyLib.UI.Read" would be ["MyLib","UI"]
    var namespaces = pFunction.split(".");

    // Retrieve the real name of the function
    // e.g Namespaces of "MyLib.UI.Read" would be Read
    var functionName = namespaces.pop();

    // Iterate through every namespace to access the one that has the function to execute. 
    // For example with the Read fn "MyLib.UI.SomeSub.Read"
    // Loop until context will be equal to SomeSub
    for (var i = 0; i < namespaces.length; i++) {
        context = context[namespaces[i]];
    }

    if (context) {
        // Get the function in the namespaces
        callbackFunction = context[functionName];

        if (jQuery.isEmptyObject(pParams))
            return callbackFunction(pResponse);
        else
            return callbackFunction(pResponse, pParams);
    }

    return false;
};

/**
 * Fetch a resource
 * @param {*} pUrl
 * @param {*} pCallback
 */
frm.ajax.fetch = function (pUrl, pCallback) {
    frm.ajax.config(pUrl, pCallback, {
        method: 'HEAD',
        dataType: null,
        async: true
    })
};

/**
 * Load a configuration file
 * @param {*} pUrl
 * @param {*} pCallback
 * @param {*} pAjaxParams
 */
frm.ajax.config = function (pUrl, pCallback, pAjaxParams) {
    // Default AJAX parameters
    pAjaxParams = pAjaxParams || {};
    pAjaxParams.method = pAjaxParams.method || 'GET';
    pAjaxParams.dataType = pAjaxParams.dataType || 'json';
    pAjaxParams.jsonp = pAjaxParams.jsonp || false; // Fix for "??" JQuery placeholder
    pAjaxParams.timeout = pAjaxParams.timeout || 60000;
    pAjaxParams.async = pAjaxParams.async || false;
    pAjaxParams.cache = pAjaxParams.cache || false;

    ajaxParams = {
        url: pUrl,
        success: function (data, textStatus, jqXHR) {
            if (pCallback)
                pCallback(data, textStatus, jqXHR);
        },
        error: function (jqXHR, textStatus, errorThrown) {
            // Log the issue rather than popping it in a Bootstrap modal because the document may not be ready yet
            console.log("An Internal Server has occurred: the file \"" + pUrl + "\" is missing or invalid.");
        }
    };

    // Merge ajax parameters
    $.extend(ajaxParams, pAjaxParams);
    // Run the Ajax call
    return $.ajax(ajaxParams);
};

/**
 * Format an ajax error response
 * 
 * @param {*} error 
 * @returns 
 */
frm.ajax.formatError = function (error) {
    // Init the error output
    var errorOutput = null;

    // Check error
    if (!error)
        errorOutput = frm.label.getStatic('exception-ajax-error');
    else if (error.data) {
        // Format the structured data, either array or object
        if (($.isArray(error.data) && error.data.length)
            || ($.isPlainObject(error.data) && !$.isEmptyObject(error.data))) {
            errorOutput = $("<ul>", {
                class: "list-group list-group-flush"
            });
            $.each(error.data, function (_index, value) {
                var error = $("<li>", {
                    class: "list-group-item border-bottom border-light",
                    html: value.toString()
                });
                errorOutput.append(error);
            });
        } else
            // Plain error
            errorOutput = error.data;
    } else
        // Get simple message
        errorOutput = error.message;

    return errorOutput;
}

/**
 * Execute an Ajax Request with a JSON-RPC protocol
 * @param {*} pAPI_URL 
 * @param {*} pAPI_Method 
 * @param {*} pAPI_Params 
 * @param {*} callbackFunctionName_onSuccess 
 * @param {*} callbackParams_onSuccess 
 * @param {*} callbackFunctionName_onError 
 * @param {*} callbackParams_onError 
 * @param {*} pAJAX_Params 
 * @param {*} pIsAsyncWrapper 
 */
frm.ajax.jsonrpc.request = function (pAPI_URL, pAPI_Method, pAPI_Params, callbackFunctionName_onSuccess, callbackParams_onSuccess, callbackFunctionName_onError, callbackParams_onError, pAJAX_Params, pIsAsyncWrapper) {
    // Default API parameters
    pAPI_Params = pAPI_Params || {};

    // Default callback functions
    callbackFunctionName_onSuccess = callbackFunctionName_onSuccess || null;
    callbackFunctionName_onError = callbackFunctionName_onError || null;

    // Default callback parameters
    callbackParams_onSuccess = callbackParams_onSuccess || null;
    callbackParams_onError = callbackParams_onError || null;

    // Default AJAX parameters
    pAJAX_Params = pAJAX_Params || {};
    pAJAX_Params.method = pAJAX_Params.method || 'POST';
    pAJAX_Params.dataType = pAJAX_Params.dataType || 'json';
    pAJAX_Params.contentType = pAJAX_Params.contentType || 'application/json'
    pAJAX_Params.jsonp = pAJAX_Params.jsonp || false; // Fix for "??" JQuery placeholder
    pAJAX_Params.timeout = pAJAX_Params.timeout || 180000;

    // Decide to simulate a sync behaviour
    var simulateSync = pAJAX_Params.async === undefined ? false : !pAJAX_Params.async;
    // Override to force aSync ajax even during Sync simulation
    pAJAX_Params.async = true;

    // Default Async Wrapper
    pIsAsyncWrapper = pIsAsyncWrapper || false;

    // Box VTI
    pAPI_Params = frm.crypto.vti.box(pAPI_URL, pAPI_Method, pAPI_Params, callbackFunctionName_onSuccess, callbackParams_onSuccess, callbackFunctionName_onError, callbackParams_onError, pAJAX_Params, pIsAsyncWrapper);
    if (pAPI_Params === false)
        // Init VTI first
        return;

    // Set the Call ID
    var callID = Math.floor(Math.random() * 999999999) + 1;

    // Set the Data to pass into the Ajax call
    var data4Ajax = {
        "jsonrpc": '2.0',
        "method": pAPI_Method,
        "params": pAPI_Params,
        "id": callID
    };

    // Extend AJAX Parameters
    var extendedAJAXParams = {
        url: pAPI_URL,
        data: JSON.stringify(data4Ajax),
        success: async function (response) {
            // Validate the JSON-RPC Call ID
            if (pAJAX_Params.dataType == 'json' && response.id != callID) {
                // Pop the exception in the Bootstrap Modal
                frm.modal.exception(frm.label.getStatic('exception-ajax-jsonrpc'));
                return;
            }

            if (response.error !== undefined) {
                switch (response.error.code) {
                    // System maintenance
                    case -32001:
                        frm.ss.engine.load(frm.config.url.maintenance);
                        break;
                    // IP blocked
                    case -32002:
                        frm.common.ipError(frm.ajax.formatError(response.error));
                        break;
                    // JWT Data exception
                    case -32094:
                        frm.msal.getAccessToken().then(accessToken => {
                            frm.ajax.jsonrpc.request(pAPI_URL, pAPI_Method, pAPI_Params, callbackFunctionName_onSuccess, callbackParams_onSuccess, callbackFunctionName_onError, callbackParams_onError, pAJAX_Params, pIsAsyncWrapper);
                        });
                        break;
                    // JWT Unexpected exception
                    case -32095:
                        frm.msal.logout();
                        break;
                    // Data exception
                    case -32096:
                        frm.common.dataException(frm.ajax.formatError(response.error));
                        break;
                    // Unexpected exception
                    case -32097:
                        frm.common.unexpectedException(frm.ajax.formatError(response.error));
                        break;
                    default:
                        // Error callback
                        if (callbackFunctionName_onError)
                            frm.ajax.callback(callbackFunctionName_onError, response.error, callbackParams_onError);
                        else if (response.error)
                            // Pop the error in the Bootstrap Modal
                            frm.modal.error(frm.ajax.formatError(response.error));
                        else
                            // Pop the exception in the Bootstrap Modal
                            frm.modal.exception(frm.label.getStatic('exception-ajax-error'));
                        break;
                }
            } else if (response.result !== undefined) {
                // Unbox VTI
                response.result = frm.crypto.vti.unbox(response.result);

                // Success callback
                if (callbackFunctionName_onSuccess) {
                    // Override async wrapper to handle it in the callback instead
                    pIsAsyncWrapper = false;

                    // Check if the response.result property exist
                    frm.ajax.callback(callbackFunctionName_onSuccess, response.result, callbackParams_onSuccess);
                }
            } else if (response.error === undefined && response.result === undefined) {
                // Silent response
            } else {
                // Pop the exception in the Bootstrap Modal
                frm.modal.exception(frm.label.getStatic('exception-ajax-infrastructure'));
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            // Error callback
            if (callbackFunctionName_onError) {
                frm.ajax.callback(callbackFunctionName_onError, null, callbackParams_onError);
            }
            else if (errorThrown == "Unauthorized") {
                // Silent response when unauthorized authentication
            }
            else {
                // Pop the exception in the Bootstrap Modal
                frm.modal.exception(frm.label.getStatic('exception-ajax-infrastructure'));
            }
        },
        complete: function (jqXHR, textStatus) {
            // Simulate sync behaviour
            if (simulateSync) {
                frm.spinner.stop();
            }

            // Terminate async wrapper
            if (pIsAsyncWrapper) {
                frm.spinner.stop();
            }
        }
    }

    // Merge pAJAX_Params into extendedAJAXParams
    $.extend(extendedAJAXParams, pAJAX_Params);

    // Simulate sync behaviour
    if (simulateSync) {
        frm.spinner.start();
    }

    try {
        // Make the Ajax call
        return $.ajax(extendedAJAXParams);
    } catch (error) {
        console.log(error);
        // Pop the exception in the Bootstrap Modal
        frm.modal.exception(frm.label.getStatic('exception-ajax-unhandled'));
        return false;
    }
};