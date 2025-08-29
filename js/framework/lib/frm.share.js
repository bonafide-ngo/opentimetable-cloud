// Init
var frm = frm || {};

/*******************************************************************************
Framework - Share
*******************************************************************************/

frm.share = {};
frm.share.android = {};
frm.share.ios = {};
frm.share.text = null;
frm.share.params = null;
frm.share.paramsBase64 = null;
frm.share.file = {};
frm.share.file.uploads = [];
frm.share.file.upload = {
    input: {
        name: null,
        size: null,
        type: null
    },
    base64: null,
    canvas: null
};

/**
 * Share media device dependant
 * 
 * @param {*} that 
 * @param {*} subtitle 
 * @param {*} url 
 */
frm.share.media = function (that, subtitle, url) {
    that = that || null;
    subtitle = subtitle || null;
    url = url || frm.config.url.root;

    // Build data to share
    var data2share = {
        title: frm.label.getStatic('i-title') + (subtitle ? ': ' + subtitle : ''),
        url: url
    }

    // Switch among device type
    switch (frm.firebase.getType()) {
        case C_FIREBASE_TYPE_ANDROID:
            // Call the native android share
            if (Android.hookShare)
                Android.hookShare(data2share.title, data2share.url);
            break;

        case C_FIREBASE_TYPE_IOS:
        // TODO check if a specific ios share can be implemented
        case C_FIREBASE_TYPE_BROWSER:
        default:
            // Check if mobile browser supports share
            // https://developer.mozilla.org/en-US/docs/Web/API/Navigator/share
            if (isMobile.any && navigator.canShare && navigator.canShare(data2share))
                // Call the native browser share
                navigator.share(data2share);
            else {
                // Copy to clipboard
                navigator.clipboard.writeText(url);
                // Popover notice
                window.setTimeout(function () {
                    $(that).popover('hide');
                }, 2000);
            }
    }
}

/**
 * Encode params base64
 * @param {*} params 
 * @returns 
 */
frm.share.encodeParamsBase64 = function (params) {
    return frm.share.paramsBase64 = btoa(JSON.stringify(params));
}

/**
 * Set params
 * @returns 
 */
frm.share.setParams = function () {
    return frm.share.params = frm.uri.isParam(C_PARAM_SHARE) ? JSON.parse(atob(frm.uri.getParam(C_PARAM_SHARE))) : null;
}

/**
 * Clear params
 */
frm.share.clearParams = function () {
    frm.share.params = null;
    frm.share.paramsBase = null;
}

/** 
 * Handle trigger 
 */
frm.share.handleTrigger = function (shares, options, trigger) {
    // Trigger if sharing anything or something against valid options
    if (!shares || (options.length && options.length))
        trigger();
}

/**
 * Check sharing is in progress
 */
frm.share.isInProgress = function () {
    return frm.share.text || frm.share.file.uploads.length ? true : false;
}

/**
 * Reset share
 */
frm.share.reset = function (text) {
    frm.share.text = null;
    frm.share.file.uploads = [];
}

/**
 * Handle share for text
 */
frm.share.textHandler = function (text) {
    // Force reset
    frm.share.reset();

    // Store for later
    frm.share.text = text;
}
// Interface registered by Android
frm.share.android.textHandler = frm.share.textHandler;
// Interface registered by iOS
frm.share.ios.textHandler = function (text) {
    frm.share.textHandler(text);

    // Switch to board because iOS retain the previous state
    frm.ss.engine.load(frm.config.url.board);
}

/**
 * Handle share for single file
 */
frm.share.fileHandler = function (jsonData) {
    // Force reset
    frm.share.reset();

    var intent = JSON.parse(jsonData);

    // Check for the hard limit of the file size
    if (parseInt(intent[C_INTENT_FILE_SIZE]) > frm.config.threshold.file) {
        frm.modal.error(frm.label.parseDynamic("error-share-size", [frm.formatSize(frm.config.threshold.file, 'B')]));
        return;
    }

    frm.share.file.uploads[0] = $.extend(true, {}, frm.share.file.upload);
    frm.share.file.uploads[0].base64 = intent[C_INTENT_FILE_BASE64];
    frm.share.file.uploads[0].input.name = intent[C_INTENT_FILE_NAME];
    frm.share.file.uploads[0].input.size = parseInt(intent[C_INTENT_FILE_SIZE]);
    frm.share.file.uploads[0].input.type = intent[C_INTENT_FILE_TYPE];
}
// Interface registered by Android
frm.share.android.fileHandler = frm.share.fileHandler;
// Interface registered by iOS
frm.share.ios.fileHandler = function (jsonData) {
    frm.share.fileHandler(jsonData);

    // Switch to board because iOS retain the previous state
    frm.ss.engine.load(frm.config.url.board);
}

/**
 * Handle share for multiple files
 */
frm.share.filesHandler = function (jsonData) {
    // Force reset
    frm.share.reset();

    var intents = JSON.parse(jsonData);
    $.each(intents, function (index, intent) {
        // Check for the hard limit of the file size
        if (parseInt(intent[C_INTENT_FILE_SIZE]) > frm.config.threshold.file) {
            // Show Error once only
            if (!$("#modal-error").hasClass('show'))
                frm.modal.error(frm.label.parseDynamic("error-share-size", [frm.formatSize(frm.config.threshold.file, 'B')]));
        } else {
            frm.share.file.uploads[index] = $.extend(true, {}, frm.share.file.upload);
            frm.share.file.uploads[index].base64 = intent[C_INTENT_FILE_BASE64];
            frm.share.file.uploads[index].input.name = intent[C_INTENT_FILE_NAME];
            frm.share.file.uploads[index].input.size = parseInt(intent[C_INTENT_FILE_SIZE]);
            frm.share.file.uploads[index].input.type = intent[C_INTENT_FILE_TYPE];
        }
    });

    // Force reset if nothing to share
    if (!frm.share.file.uploads.length)
        frm.share.reset();
}
// Interface registered by Android
frm.share.android.filesHandler = frm.share.filesHandler;
// Interface registered by iOS
frm.share.ios.filesHandler = function (jsonData) {
    frm.share.filesHandler(jsonData);

    // Switch to board because iOS retain the previous state
    frm.ss.engine.load(frm.config.url.board);
}