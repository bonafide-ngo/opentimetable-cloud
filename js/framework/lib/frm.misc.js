// Init
var frm = frm || {};

/**
 * JQuery expression for case-insensitive contains
 * https://stackoverflow.com/questions/8746882/jquery-contains-selector-uppercase-and-lower-case-issue
 */
jQuery.expr[':'].icontains = function (a, i, m) {
    return jQuery(a).text().toUpperCase()
        .indexOf(m[3].toUpperCase()) >= 0;
};

/**
 * JQuery extension for re-usable events
 */
frm.once = [];
(function ($) {
    $.fn.once = function () {
        arguments[2] = arguments[2] || false;

        // Check for retaintion flag
        if (arguments[2]) {
            frm.once[arguments[0]] = arguments[1];
        }

        // Append retaintion if any and different from itself
        if (frm.once[arguments[0]] && frm.once[arguments[0]].toString() != arguments[1].toString())
            return this.off(arguments[0]).on(arguments[0], arguments[1]).on(arguments[0], frm.once[arguments[0]]);
        else
            return this.off(arguments[0]).on(arguments[0], arguments[1]);
    };
})(jQuery);

/**
 * Ucwords emulator
 */
String.prototype.ucwords = String.prototype.ucwords || function () {
    var string = this.toLowerCase();
    return string.replace(/(^([a-zA-Z\p{M}]))|([ -][a-zA-Z\p{M}])/g, function (s) { return s.toUpperCase(); });
};

/**
 * Sprintf emulator
 */
String.prototype.sprintf = String.prototype.sprintf || function (params) {
    params = params || [];

    var sprintfRegex = /\{(\d+)\}/g;
    var sprintf = function (match, number) {
        return number in params ? params[number] : match;
    };

    if (Array.isArray(params) && params.length)
        return this.replace(sprintfRegex, sprintf);
    else
        return this;
};

/**
 * Truncate text
 */
String.prototype.truncate = String.prototype.truncate || function (truncateLength, truncateHint) {
    truncateLength = (truncateLength || 12) * (isMobile.any ? 1 : 2);
    truncateHint = truncateHint || '...';

    var string = this;
    if (this.length > truncateLength)
        string = string.substring(0, truncateLength) + truncateHint;

    return string;
};

/**
 * String To Array Buffer
 * https://stackoverflow.com/questions/34993292/how-to-save-xlsx-data-to-file-as-a-blob
 */
String.prototype.s2ab = String.prototype.s2ab || function () {
    var buffer = new Uint8Array(new ArrayBuffer(this.length));
    for (var i = 0; i != this.length; ++i) {
        buffer[i] = this.charCodeAt(i) & 0xFF;
    }
    return buffer;
};

/**
 * Converter to a boolean
 * @param {*} input 
 * @returns 
 */
frm.boolean = function (input) {
    input = input || '';

    // Normalise by converting to string
    string = input.toString();

    if (string === '[object Object]')
        // Check for object first
        return !jQuery.isEmptyObject(input);
    else
        // All other cases
        return !string || string == 'false' || string == '0' || !string.length ? false : true;
};

/**
 * Strcasecmp emulator for case insensitive comparison
 * @param {*} string1 
 * @param {*} string2 
 * @returns 
 */
frm.strcasecmp = function (string1, string2) {
    // Use toUpperCase rather than toLowerCase
    // https://docs.microsoft.com/en-ie/dotnet/fundamentals/code-analysis/quality-rules/ca1308?view=vs-2019&redirectedfrom=MSDN&viewFallbackFrom=vs-2015
    return string1.toUpperCase() == string2.toUpperCase();
}

/**
 * Safely strip HTML tags
 * @param {*} string 
 * @returns 
 */
frm.stripHTML = function (string) {
    return $('<p>' + string + '</p>').text();
}

/**
 * Check and block deprecated IE browser
 */
frm.isIE = function () {
    if (window.navigator.userAgent.match(/MSIE|Trident/) == null) {
        return false;
    } else {
        $("#modal-ie").modal("show");
        $('#modal-ie').find('button').once('click', function () {
            window.location.href = frm.config.url.ieInfo;
        });

        return true;
    }
};

/**
 * Check if Safari browser
 * https://stackoverflow.com/questions/9847580/how-to-detect-safari-chrome-ie-firefox-and-opera-browser
 */
frm.isSafari = function () {
    if (
        // Check webkit
        typeof webkit == 'undefined'
        // Check feature
        && !(typeof safari !== 'undefined' && window['safari'].pushNotification)
        // Check vendor
        && !(navigator.vendor && navigator.vendor.indexOf('Apple') > -1)
        // Check standard userAgent 
        && !(/^((?!chrome|android|crios|fxios).)*safari/i.test(navigator.userAgent)))
        return false;
    else
        return true;
};

/**
 * Check and warn about disabled cookies
 */
frm.isCookie = function () {
    if (!navigator.cookieEnabled) {
        $('#modal-cookie').find('[name="cookie-information"]').html(frm.label.parseDynamic('cookie-information', frm.config.url.privacy.sprintf([frm.label.lang])));
        $('#modal-cookie').modal('show');
        $('#modal-cookie').find('button').once('click', function () {
            frm.ss.engine.load(window.location.href);
        });
        return false;
    } else
        return true;
};

/** 
 * Simulate an async sleep. 
 * The parent outer function must be async
 * 
 */
frm.sleep = function (ms) {
    ms = ms || 600;
    return new Promise(resolve => window.setTimeout(resolve, ms));
}

/** 
 * Generate a unique id based on the UUIDv4 format
 */
frm.uniqueId = function () {
    function s4() {
        return Math.floor((1 + Math.random()) * 0x10000)
            .toString(16)
            .substring(1);
    }

    // define a UUIDv4
    var uuidV4 = s4() + s4() + '-' + s4() + '-' + s4() + '-' + s4() + '-' + s4() + s4() + s4() + s4();
    // Append time in milliseconds for universal uniqueness
    return frm.crypto.sha1(uuidV4 + '-' + moment().valueOf());
}

/**
 * Override toDataURL
 * 
 * @param {*} element
 * @returns 
 */
frm.toDataURL = function (element) {
    // Force Jpeg because of limited support of WebP
    return element.toDataURL('image/jpeg');
}

/**
 * Format a binary size according to the locale
 * 
 * @param {*} number 
 * @param {*} unit 
 * @param {*} noDecimals 
 * @returns 
 */
frm.formatSize = function (number, unit, noDecimals) {
    noDecimals = noDecimals || false;

    if (number / 1024 < 1)
        // b/B
        return number.toFixed(0).toLocaleString(frm.label.lang) + unit;
    else if (number / 1024 / 1024 < 1)
        // Kb/KB
        return (number / 1024).toFixed(0).toLocaleString(frm.label.lang) + ' K' + unit;
    else if (number / 1024 / 1024 / 1024 < 1)
        // Mb/MB with 1 decimal place
        return (number / 1024 / 1024).toFixed(noDecimals ? 0 : 1).toLocaleString(frm.label.lang) + ' M' + unit;
    else if (number / 1024 / 1024 / 1024 / 1024 < 1)
        // Gb/GB with 1 decimal place
        return (number / 1024 / 1024 / 1024).toFixed(noDecimals ? 0 : 1).toLocaleString(frm.label.lang) + ' G' + unit;
    else if (number / 1024 / 1024 / 1024 / 1024 / 1024 < 1)
        // Tb/TB with 1 decimal place
        return (number / 1024 / 1024 / 1024 / 1024).toFixed(noDecimals ? 0 : 1).toLocaleString(frm.label.lang) + ' T' + unit;
}

/**
 * Download a dynamic resource
 * @param {*} filename 
 * @param {*} fileBase64 
 */
frm.download = function (filename, fileBase64) {

    // Switch among device type
    switch (frm.firebase.getType()) {
        case C_FIREBASE_TYPE_ANDROID:
            // Pass the resource directly to Android rather than downloading it again
            if (Android.hookDownload)
                Android.hookDownload(filename, fileBase64, frm.label.getStatic('saved'));
            break;

        case C_FIREBASE_TYPE_IOS:
            // Pass the resource directly to iOS rather than downloading it again
            webkit.messageHandlers.hookDownload.postMessage({
                filename: filename,
                fileBase64: fileBase64,
                message: frm.label.getStatic('saved')
            });
            break;

        case C_FIREBASE_TYPE_BROWSER:
        default:
            // Create a temporary tag
            var a = document.createElement("a");
            // split by the ;base64
            var fileStruct = fileBase64.split(';base64,');
            // Convert data ASCCI/Binary
            fileBase64 = atob(fileStruct[1]);
            // Convert data Binary/ArrayBuffer
            fileBase64 = fileBase64.s2ab();
            // remove the data
            mimeType = fileStruct[0].substring(5);

            // Append download attribute
            a.download = filename;
            // Create a temporarily blob url
            a.href = URL.createObjectURL(new Blob([fileBase64], { type: mimeType }));

            // Dispatch event
            if (document.createEvent) {
                // https://developer.mozilla.org/en-US/docs/Web/API/Document/createEvent
                var event = document.createEvent('MouseEvents');
                event.initEvent('click', true, true);
                a.dispatchEvent(event);
            }
            else {
                a.click();
            }
            break;
    }
};

/**
 * Initialise geolocatin window properties
 */
frm.initGeolocation = function () {
    // Clear any geolocation
    if (window.geolocation) {
        // Stop timeout
        window.clearTimeout(window.geolocation.timerId);
        // Stop watching
        if (navigator.geolocation)
            navigator.geolocation.clearWatch(window.geolocation.watchId);
        // Stop interval
        window.clearInterval(window.geolocation.interval);
    }

    // Re/Set window properties
    window.geolocation = {};
    window.geolocation.atlas = null;
    window.geolocation.watchId = null;
    window.geolocation.timerId = null;
    window.geolocation.interval = null;

    // Reset map template
    $('#modal-map-atlas').remove();
    var template = $('#modal-map').find('[name="template"]').clone();
    template.removeAttr('name').attr('id', 'modal-map-atlas').show();
    $('#modal-map').find('.modal-body').append(template);
}

/**
 * HTML to BBCode
 * @param {*} html 
 * @returns 
 */
frm.htmlToBBCode = function (html) {
    return html
        .replace(/<strong>(.*?)<\/strong>/gi, "[b]$1[/b]")           // Bold (strong)
        .replace(/<b>(.*?)<\/b>/gi, "[b]$1[/b]")                     // Bold (b)
        .replace(/<i>(.*?)<\/i>/gi, "[i]$1[/i]")                     // Italic
        .replace(/<em>(.*?)<\/em>/gi, "[i]$1[/i]")                   // Italic (em)
        .replace(/<u>(.*?)<\/u>/gi, "[u]$1[/u]")                     // Underline
        .replace(/<p>(.*?)<\/p>/gi, "[p]$1[/p]")                     // Paragraph
        .replace(/<blockquote>(.*?)<\/blockquote>/gi, "[quote]$1[/quote]") // Blockquote
        .replace(/<a\s+([^>]*?)href="(.*?)"([^>]*?)>(.*?)<\/a>/gi, function (match, beforeHref, href, afterHref, text) {
            // Extract title, target, and rel attributes from beforeHref
            let titleMatch = beforeHref.match(/title="(.*?)"/) || afterHref.match(/title="(.*?)"/);
            let targetMatch = beforeHref.match(/target="(.*?)"/) || afterHref.match(/target="(.*?)"/);
            let relMatch = beforeHref.match(/rel="(.*?)"/) || afterHref.match(/rel="(.*?)"/);

            // Build BBCode URL with optional attributes
            let bbcode = `[url=${href}`;
            if (titleMatch) bbcode += ` title="${titleMatch[1]}"`;
            if (targetMatch) bbcode += ` target="${targetMatch[1]}"`;
            if (relMatch) bbcode += ` rel="${relMatch[1]}"`;
            bbcode += `]${text}[/url]`;

            return bbcode;
        })
        .replace(/<span\s+style="([^"]*?)">(.*?)<\/span>/gi, function (match, style, text) {
            // Check if color or font-size exist in the style attribute
            let colorMatch = style.match(/color:\s*([^;]+);?/);
            let sizeMatch = style.match(/font-size:\s*([^;]+);?/);

            let bbcode = "";

            // If color is found, add the color BBCode
            if (colorMatch) {
                bbcode += `[color=${colorMatch[1]}]${text}[/color]`;
            } else {
                bbcode += text; // if no color, just use the text
            }

            // If font-size is found, add the size BBCode
            if (sizeMatch) {
                bbcode = `[size=${sizeMatch[1]}]${bbcode}[/size]`;
            }

            return bbcode;
        })
}

/**
 * BBCode to HTML
 * @param {*} bbcode 
 * @returns 
 */
frm.bbcodeToHTML = function (bbcode) {
    if (!bbcode)
        return bbcode;

    return bbcode
        .replace(/\[b\](.*?)\[\/b\]/gi, "<strong>$1</strong>")       // Bold
        .replace(/\[i\](.*?)\[\/i\]/gi, "<em>$1</em>")               // Italic
        .replace(/\[u\](.*?)\[\/u\]/gi, "<u>$1</u>")                 // Underline
        .replace(/\[p\](.*?)\[\/p\]/gi, "<p>$1</p>")                 // Paragraph
        .replace(/\[quote\](.*?)\[\/quote\]/gi, "<blockquote>$1</blockquote>") // Blockquote
        .replace(/\[url=(.*?)(?:\s+title="(.*?)")?(?:\s+target="(.*?)")?(?:\s+rel="(.*?)")?\](.*?)\[\/url\]/gi,
            function (match, href, title, target, rel, text) {
                let attributes = ` href="${href}"`;
                if (title) attributes += ` title="${title}"`;
                if (target) attributes += ` target="${target}"`;
                if (rel) attributes += ` rel="${rel}"`;
                return `<a${attributes}>${text}</a>`;
            })
        .replace(/\[color=(.*?)\](.*?)\[\/color\]/gi, function (match, color, text) {
            return `<span style="color:${color};">${text}</span>`;
        }) // Color
        .replace(/\[size=(.*?)\](.*?)\[\/size\]/gi, function (match, size, text) {
            return `<span style="font-size:${size};">${text}</span>`;
        }) // Font size
}

/**
 * Get the current breakpoint
 * @returns 
 */
frm.breakpoint = function (debug) {
    debug = debug || false;

    if (debug)
        $('#breakpoint-debug').show();

    return parseInt($('#breakpoint-detector > div:visible').data('breakpoint'));
}