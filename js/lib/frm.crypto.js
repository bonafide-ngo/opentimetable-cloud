// Init
var frm = frm || {};

/*******************************************************************************
Framework - Crypto
*******************************************************************************/
frm.crypto = {};

/**
 * Generate an H digest
 * @param {*} text 
 * @returns 
 */
frm.crypto.h = function (text, logPerformance) {
    // Run on demand form console to evaluate performance
    logPerformance = logPerformance || false;

    var h = text;
    var md = forge.md.sha512.create();

    var start = null;
    if (logPerformance)
        start = moment().valueOf();

    for (let index = 0; index < frm.config.threshold.hr; index++) {
        md.update(h);
        h = md.digest().toHex();
    }

    if (logPerformance)
        console.log('H time (s): ' + ((moment().valueOf() - start) / 1000).toFixed(3));

    return h;
}

/**
 * Generate a MD5 digest
 * @param {*} text 
 * @returns 
 */
frm.crypto.md5 = function (text) {
    var md = forge.md.md5.create();
    md.update(text);
    return md.digest().toHex();
}

/**
 * Generate a SHA1 digest
 * @param {*} text 
 * @returns 
 */
frm.crypto.sha1 = function (text) {
    var md = forge.md.sha1.create();
    md.update(text);
    return md.digest().toHex();
}

/**
 * Generate a SHA512 digest
 * @param {*} text 
 * @returns 
 */
frm.crypto.sha512 = function (text) {
    var md = forge.md.sha512.create();
    md.update(text);
    return md.digest().toHex();
}

/**
 * Shortcut for setting cookies
 * @param {*} cookieOptions 
 * @param {*} value 
 */
frm.crypto.setCookie = function (cookieOptions, value) {
    Cookies.set(cookieOptions.name, value, $.extend(true, {}, frm.config.cookie.option, cookieOptions));
};

/**
 * Shortcut for removing cookies
 * @param {*} cookieOptions 
 */
frm.crypto.removeCookie = function (cookieOptions) {
    Cookies.remove(cookieOptions.name, frm.config.cookie.option);
};

/*******************************************************************************
Framework - Crypto - VTI (Virtual Tunnel Interface)
*******************************************************************************/
frm.crypto.vti = {};


/**
 * Clear VTI
 */
frm.crypto.vti.clear = function () {
    if (frm.config.vti.enable) {
        // Clear vtiPK and vtiSK
        sessionStorage.removeItem(frm.config.vti.storage.PK_base64);
        sessionStorage.removeItem(frm.config.vti.storage.SK_base64);
    }
}

/**
 * Box VTI
 */
frm.crypto.vti.box = function (pAPI_URL, pAPI_Method, pAPI_Params, callbackFunctionName_onSuccess, callbackParams_onSuccess, callbackFunctionName_onError, callbackParams_onError, pAJAX_Params, pIsAsyncWrapper) {
    // Check VTI is enable
    if (!frm.config.vti.enable)
        return pAPI_Params;
    // Check VTI has being initialised
    if (pAPI_Method == 'VTI.Init') {
        return pAPI_Params;
    }

    // Get VTI credentials
    var vtiPK_base64 = sessionStorage.getItem(frm.config.vti.storage.PK_base64);
    var vtiSK_base64 = sessionStorage.getItem(frm.config.vti.storage.SK_base64);

    // Check for VTI credentials
    if (vtiPK_base64 && vtiSK_base64) {
        // Box VTI
        if (pAPI_Params[frm.config.vti] === undefined) {
            // Create NaCl
            var nonce = nacl.randomBytes(24);
            // Let's box
            var box = nacl.box(nacl.util.decodeUTF8(JSON.stringify(pAPI_Params)), nonce, nacl.util.decodeBase64(vtiPK_base64), nacl.util.decodeBase64(vtiSK_base64));

            // Override API Params
            pAPI_Params = {};
            pAPI_Params[frm.config.vti] = {
                box_base64: nacl.util.encodeBase64(box),
                nonce_base64: nacl.util.encodeBase64(nonce)
            }
        }

        // Return to the API params boxed in VTI
        return pAPI_Params;
    }

    // If there's another ajax running then it must be a previous and unfinished VTI.Init
    // Let's wait for it to finish, otherwise VTI keys will conflit
    if ($.active) {
        window.setTimeout(function () {
            frm.ajax.jsonrpc.request(pAPI_URL, pAPI_Method, pAPI_Params, callbackFunctionName_onSuccess, callbackParams_onSuccess, callbackFunctionName_onError, callbackParams_onError, pAJAX_Params, pIsAsyncWrapper);
        }, 10);

        // Stop Ajax looping/propagation
        return false;
    }

    // Create NaCl
    var keyPair = nacl.box.keyPair();

    // Init VTI
    frm.ajax.jsonrpc.request(
        frm.config.url.api,
        'VTI.Init',
        { publicKey_base64: nacl.util.encodeBase64(keyPair.publicKey) },
        onSuccess,
        null,
        null,
        null,
        pAJAX_Params,
        pIsAsyncWrapper);

    function onSuccess(result) {
        if (result) {
            // Store VTI credentials
            sessionStorage.setItem(frm.config.vti.storage.PK_base64, result);
            sessionStorage.setItem(frm.config.vti.storage.SK_base64, nacl.util.encodeBase64(keyPair.secretKey));

            // Recall original Ajax with params boxed in VTI
            frm.ajax.jsonrpc.request(
                pAPI_URL,
                pAPI_Method,
                pAPI_Params,
                callbackFunctionName_onSuccess,
                callbackParams_onSuccess,
                callbackFunctionName_onError,
                callbackParams_onError,
                pAJAX_Params,
                pIsAsyncWrapper);
        } else
            frm.modal.exception(frm.label.getStatic('exception-ajax-unexpected'));
    }

    // Stop Ajax looping/propagation
    return false;
}

/**
 * Unbox VTI
 */
frm.crypto.vti.unbox = function (result) {
    // Check VTI is enable
    if (!frm.config.vti.enable)
        return result;

    if (result[frm.config.vti] !== undefined) {
        // Get VTI credentials
        var vtiPK_base64 = sessionStorage.getItem(frm.config.vti.storage.PK_base64);
        var vtiSK_base64 = sessionStorage.getItem(frm.config.vti.storage.SK_base64);

        // Check for VTI credentials
        if (vtiPK_base64 && vtiSK_base64) {
            // Unbox VTI
            var box = nacl.box.open(nacl.util.decodeBase64(result[frm.config.vti].box_base64), nacl.util.decodeBase64(result[frm.config.vti].nonce_base64), nacl.util.decodeBase64(vtiPK_base64), nacl.util.decodeBase64(vtiSK_base64));
            if (!box)
                frm.common.unexpectedException();
            else
                return JSON.parse(nacl.util.encodeUTF8(box));
        } else
            frm.common.unexpectedException();
    } else
        // Nothing to unbox
        return result;
}