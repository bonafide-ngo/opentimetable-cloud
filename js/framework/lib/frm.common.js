// Init
var frm = frm || {};

/*******************************************************************************
Framework - Common
*******************************************************************************/
frm.common = {};
frm.common.toast = {};
frm.common.select2 = {};
frm.common.sync = {};
frm.common.sync.id = null;
frm.common.sync.isDraft = false;
frm.common.sync.isActive = false;
frm.common.sync.isPreview = false;
frm.common.matomo = {};

/**
 * Customise the header when scrolling
 * @param {*} selector 
 */
frm.common.headerOnScroll = function (selector) {
    $(window).scroll(function () {
        if ($(this).scrollTop())
            $(selector).addClass('border-bottom');
        else
            $(selector).removeClass('border-bottom');
    });
};

/**
 * Raise a data exception
 * @param {*} error 
 */
frm.common.dataException = function (error) {
    error = error || frm.label.getStatic('exception-ajax-data');

    frm.modal.error(error);
    $('#modal-error').once('hidden.bs.modal', function () {
        // Load current page seamlessly
        frm.ss.engine.load(window.location.href);
    });
};

/**
 * Raise an unexpected exception
 * @param {*} error 
 */
frm.common.unexpectedException = function (error) {
    error = error || frm.label.getStatic('exception-ajax-unexpected');

    frm.modal.exception(error);
    $('#modal-exception').once('hidden.bs.modal', function () {
        // Reload home page with no history
        window.location.replace(frm.config.url.home);
    });
};

/**
 * Raise an ip error
 * @param {*} error 
 */
frm.common.ipError = function (error) {
    error = error || frm.label.getStatic('error-ip-blocked');

    frm.modal.error(error);
    $('#modal-error').once('hidden.bs.modal', function () {
        // Redirect to website with no history
        window.location.replace(frm.config.url.www);
    });
};

/**
 * Framework routing to run in each page
 * 
 * @param {*} title 
 * @param {*} description 
 * @param {*} breadcrumb 
 * @returns 
 */
frm.common.routine = function (title, description, breadcrumb) {
    // Set attributes
    title = title || frm.label.getStatic('i-title');
    description = description || frm.label.getStatic('i-description');
    breadcrumb = breadcrumb || false;

    // Set meta title
    frm.label.setMetaTitle(title);

    // Set meta description
    frm.label.setMetaDescription(description);

    // Set meta language
    frm.label.setMetaLang(frm.label.lang);

    // Set alternate links
    frm.label.setAlternateLinks();

    // Set Open Graph
    frm.label.setOG(title, description);

    // Set navbar
    $('#navbar-menu').find('.nav-link').removeClass('active')
    $('#navbar-menu').find('.nav-link:contains("' + title + '")').addClass('active');

    // Set breadcrumb
    if (breadcrumb)
        $('#breadcrumb').find('[name="page"]').text(title).show();
    else
        $('#breadcrumb').find('[name="page"]').text(title).hide();

    // Reset geolocation
    frm.initGeolocation();

    // Parse labels
    frm.label.getStatic();

    // Clear any spinner
    frm.spinner.clear();

    // Empty toasts 
    $('#toast-trail').empty();

    // Initialise popovers 
    $('[data-bs-toggle="popover"]').popover();

    // Check ie
    if (frm.isIE())
        return false;

    // Check cookies
    if (!frm.isCookie())
        return false;

    // Matomo SPA traking
    frm.common.matomo.track();

    return true;
}

/**
 * Parse URLs within text
 * @param {*} text 
 * @returns 
 */
frm.common.parseURLinText = function (text) {
    var parsedText = URI.withinString(text, function (url) {
        return $('<a>', {
            href: url,
            text: url,
            class: 'text-warning',
            target: '_blank'
        })[0].outerHTML;
    });

    // Prepend link icon to the parsed text that may contain multiple links
    return text != parsedText ? $('<i>', { class: 'fas fa-external-link-alt fa-pull-left text-quintenary pe-1 pt-1', })[0].outerHTML + parsedText : text;
}

/**
 * Test string for solo url
 * @param {*} message 
 * @returns 
 */
frm.common.isSoloUrl = function (message) {
    return (new RegExp(C_REGEX_URL, "igm")).test(message);
}

/**
 * Test string for solo url image
 * @param {*} message 
 * @returns 
 */
frm.common.isSoloUrlImg = function (message) {
    // N.B. Must test for URL first
    if (frm.uri.isAbsolute(message))
        return (new RegExp(C_REGEX_URL_IMG, "igm")).test(message);
    else
        return false;
}

/**
 * Get base64 from url
 * @param {*} url 
 * @returns 
 */
frm.common.getBase64FromUrl = async function (url) {
    const data = await fetch(url);
    const blob = await data.blob();
    return new Promise((resolve) => {
        const reader = new FileReader();
        reader.readAsDataURL(blob);
        reader.onloadend = () => {
            resolve(reader.result);
        }
    });
}

/**
 * Format timestamp
 * 
 * @param {*} timestamp 
 * @returns 
 */
frm.common.formatTimestamp = function (timestamp) {
    timestamp = timestamp || moment().unix();

    return moment.unix(timestamp).calendar({
        sameDay: frm.label.getStatic('moment-calendar-sameday'),
        lastDay: frm.label.getStatic('moment-calendar-lastday'),
        lastWeek: frm.label.getStatic('moment-calendar-lastweek'),
        sameElse: frm.label.getStatic('moment-calendar-sameelse'),
    });
}

/**
 * Handle the smartbanner
 * 
 * @returns 
 */
frm.common.smartbanner = async function () {
    // Check is a browser
    if (frm.firebase.getType() != C_FIREBASE_TYPE_BROWSER)
        return;

    // Get isSafari for later
    var isSafari = frm.isSafari();

    // Render smartbanner for android
    if (isMobile.android.device) {
        // Set Google Play labels
        $("#smartbanner").find('[name="store"]').text(frm.label.getStatic('smartbanner-store-android'));
        $("#smartbanner").find('a[name="open"]').attr('href', frm.config.url.googlePlay);
        // Set extension
        $("#smartbanner-android").find('[name="apk-filepath"]').attr('href', frm.config.apk.filepath);
        $("#smartbanner-android").find('[name="apk-version"]').text(frm.config.apk.version);
        $("#smartbanner-android").find('img').attr('src', frm.label.getStatic('badge-google-play'));
        $("#smartbanner-android").fadeIn();

    }
    // Render smartbanner for ios or safari
    else if (isMobile.apple.device || isSafari) {
        // Set App Store labels
        $("#smartbanner").find('[name="store"]').text(frm.label.getStatic('smartbanner-store-apple'));
        $("#smartbanner").find('a[name="open"]').attr('href', frm.config.url.appStore.sprintf([frm.label.lang]));
        // Show extension
        $("#smartbanner-ios").find('img').attr('src', frm.label.getStatic('badge-apple-store'));
        $("#smartbanner-ios").fadeIn();
    } else
        return;

    // Set defaults
    $("#smartbanner").find('[name="title"]').text(frm.label.getStatic('i-title'));
    $("#smartbanner").find('[name="description"]').text(frm.label.getStatic('i-description'));
    $("#smartbanner").find('a[name="open"]').text(frm.label.getStatic('open').toUpperCase());
    $("#smartbanner").find('[name="close"]').once('click', function () {
        // Close banner
        $("#smartbanner").slideUp();
    });

    // Pop modal for inputs/buttons
    $("html").find('input, button[name="login"], button[name="signup"], button[name="join"]').prop('readonly', true).once('click', function (e) {
        e.preventDefault();
        e.stopPropagation();

        frm.modal.information(frm.label.getStatic('smartbanner-info'));
    });

    // Wait a tick before sliding down the banner
    await frm.sleep(1000);
    $("#smartbanner").slideDown();
}

/**
 * Handle the Safari notice
 * @returns 
 */
frm.common.safariNotice = function () {
    // Try redirecting to iOS app via URLscheme 
    window.location.href = frm.config.url.appleUrlScheme.sprintf([encodeURIComponent(window.location.href)]);
    // Prompt modal in case the iOS app is not installed
    $("#modal-safari").find('.modal-body').html(frm.label.parseDynamic('safari-notice-info', [frm.config.url.appStore.sprintf([frm.label.lang])]));
    $("#modal-safari").modal("show");
}

/*******************************************************************************
Framework - Common - Toast
*******************************************************************************/

/**
 * Toast a generic notification
 * 
 * @param {*} title 
 * @param {*} body 
 * @param {*} faClass 
 * @param {*} isBeep 
 * @param {*} onTimeoutCallback 
 * @param {*} guid 
 */
frm.common.toast.generic = function (title, body, faClass, isBeep, onTimeoutCallback, guid, flyTo) {
    faClass = faClass || 'fa-bell text-warning';
    isBeep = isBeep || false;
    onTimeoutCallback = onTimeoutCallback || null;
    guid = guid || null;
    flyTo = flyTo || [];

    var toastName = frm.uniqueId();

    // Throw a toast
    var template = $('#toast').find('[name="template-notification"]').clone();
    // Do not set the cht_did as the html tag id to avoid overlapping with the uchat/gchat context
    template.attr('name', toastName);

    // Set params
    template.find('[name="title"]').text(title);
    template.find('[name="icon"]').addClass(faClass);
    if (body)
        template.find('[name="body"]').text(body);
    else
        template.find('[name="body"]').remove();

    // Append
    $('#toast-trail').append(template);

    // Bind remove toast
    $('#toast-trail').find('[name="' + toastName + '"]').fadeIn().once('hidden.bs.toast', function () {
        $(this).remove();
    });

    // Bind close toast
    $('#toast-trail').find('[name="' + toastName + '"]').find('[data-bs-dismiss="toast"]').once('click', function () {
        $('#toast-trail').find('[name="' + toastName + '"]').fadeOut();
    });
    // Bind click
    $('#toast-trail').find('[name="' + toastName + '"]').once('click', function () {
        // TODO - pending notification implementation
        frm.ss.engine.load(frm.config.url.home);

        $('#toast-trail').find('[name="' + toastName + '"]').fadeOut();
    });

    // Dispose on timeout
    window.setTimeout(function () {
        $('#toast-trail').find('[name="' + toastName + '"]').fadeOut();
        if (onTimeoutCallback)
            onTimeoutCallback();
    }, frm.config.validity.toast);

    // Play beep 
    if (isBeep)
        $('#audio-beep')[0].play().catch((e) => { });

    return toastName;
}

/*******************************************************************************
Framework - Common - Sync
*******************************************************************************/

/**
 * Get the active sync id
 * @returns 
 */
frm.common.sync.getActive = function () {
    return frm.ajax.jsonrpc.request(
        frm.config.url.api,
        'App.Admin.Read_Sync_Active',
        null,
        null,
        null,
        null,
        null,
        { async: false });
}

/**
 * Get the draft sync id
 * @returns 
 */
frm.common.sync.getDraft = function () {
    // Authenticate
    if (!frm.msal.isAuthenticated() || ![C_MSAL_GROUP_REVIEWER].includes(frm.msal.role))
        return null;

    return frm.ajax.jsonrpc.request(
        frm.config.url.api,
        'App.Admin.Read_Sync_Draft',
        null,
        null,
        null,
        null,
        null,
        { async: false });
}

/**
 * Get the preview sync id
 * @returns 
 */
frm.common.sync.getPreview = function () {
    // Authenticate
    if (!frm.msal.isAuthenticated() || ![C_MSAL_GROUP_ADMIN, C_MSAL_GROUP_STAFF].includes(frm.msal.role))
        return null;

    return Cookies.get(frm.config.cookie.property.session.sync.name);
}

/**
 * Init the sync properties
 * 
 * @param {*} callbackFunction 
 * @param {*} forceCallback 
 */
frm.common.sync.init = function (callbackFunction, forceCallback) {
    callbackFunction = callbackFunction || null;
    forceCallback = forceCallback || false;

    // Get preview syncid
    var previewSyncId = frm.common.sync.getPreview();

    $.when(
        frm.common.sync.getDraft(),
        frm.common.sync.getActive()
    ).done(function (responseDraft, responseActive) {
        // Override draft and view syncid
        // N.B. A shared syncid implies always an active one
        if (frm.share.params) {
            responseDraft = null;
            previewSyncId = null;
        }

        // Check draft first
        if (responseDraft && responseDraft[0].result) {
            // Store for later
            frm.common.sync.isDraft = true;
            frm.common.sync.isActive = responseDraft[0].result == responseActive[0].result;
            frm.common.sync.isPreview = false;
            frm.common.sync.id = responseDraft[0].result;
        }
        // Check view and match against active 
        else if (previewSyncId) {
            // Store for later
            frm.common.sync.isDraft = false;
            frm.common.sync.isActive = false;
            frm.common.sync.isPreview = true;
            frm.common.sync.id = previewSyncId;
        }
        // Check active
        else if (responseActive && responseActive[0].result) {
            // Store for later
            frm.common.sync.isDraft = false;
            frm.common.sync.isActive = true;
            frm.common.sync.isPreview = false;
            frm.common.sync.id = responseActive[0].result;
        } else {
            // Store for later
            frm.common.sync.isDraft = false;
            frm.common.sync.isActive = false;
            frm.common.sync.isPreview = false;
            frm.common.sync.id = null;
        }

        // Handle the sync rendering
        frm.common.sync.render();

        // No timetable exists
        if (!frm.common.sync.id) {
            // Do no bother in home and admin entity
            if (!app || (!app.home && !app.admin)) {
                frm.modal.error(frm.label.getStatic('error-no-sync'));
                // Bring to home
                $('#modal-error').once('hidden.bs.modal', function (e) {
                    frm.ss.engine.load(frm.config.url.home);
                });
            }
        }

        // Call optional callback function
        if (callbackFunction && (frm.common.sync.id || forceCallback))
            callbackFunction();
    });
}

/**
 * Render the sync properties
 */
frm.common.sync.render = function () {
    if (frm.common.sync.isDraft)
        // Render draft breadcrumb
        $('#breadcrumb').find('[name="sync"]').html(
            $('<span>', {
                class: 'badge bg-danger',
                text: frm.label.getStatic('draft') + ' #' + frm.common.sync.id
            })).fadeIn();
    else if (frm.common.sync.isPreview)
        // Render view breadcrumb
        $('#breadcrumb').find('[name="sync"]').html(
            $('<span>', {
                class: 'badge bg-success',
                text: frm.label.getStatic('preview') + ' #' + frm.common.sync.id
            })).fadeIn();
    else if (frm.common.sync.isActive && [C_MSAL_GROUP_ADMIN, C_MSAL_GROUP_STAFF].includes(frm.msal.role))
        // Render active breadcrumb for staff and admin only, otherwise give no hint
        $('#breadcrumb').find('[name="sync"]').html(
            $('<span>', {
                class: 'badge bg-danger',
                text: frm.label.getStatic('active') + ' #' + frm.common.sync.id
            })).fadeIn();
    else
        // Blank view breadcrumb
        $('#breadcrumb').find('[name="sync"]').html('').hide();
}

/*******************************************************************************
Framework - Common - Select2
*******************************************************************************/

/**
 * Select2 for courses
 * @param {*} results 
 * @returns 
 */
frm.common.select2.department = function (results) {
    if (results && results.length)
        return $.map(results, function (result) {
            result.id = result.dpt_code;
            result.text = frm.strcasecmp(result.dpt_code, result.dpt_name) ? result.dpt_name : result.dpt_code + ' - ' + result.dpt_name;
            return result;
        });
    else
        return [];
}

/**
 * Select2 for courses
 * @param {*} results 
 * @returns 
 */
frm.common.select2.course = function (results) {
    if (results && results.length)
        return $.map(results, function (result) {
            result.id = result.crs_code;
            result.text = frm.strcasecmp(result.crs_code, result.crs_name) ? result.crs_name : result.crs_code + ' - ' + result.crs_name;
            return result;
        });
    else
        return [];
}

/**
 * Select2 for venues
 * @param {*} results 
 * @returns 
 */
frm.common.select2.venue = function (results) {
    if (results && results.length)
        return $.map(results, function (result) {
            result.id = result.vnx_code;
            result.text = frm.strcasecmp(result.vnx_code, result.vnx_name) ? result.vnx_name : result.vnx_code + ' - ' + result.vnx_name;
            return result;
        });
    else
        return [];
}

/**
 * Select2 for weeks
 * @param {*} results 
 * @returns 
 */
frm.common.select2.week = function (results) {
    if (results && results.length)
        return $.map(results, function (result) {
            result.id = frm.config.prefix.week + result.prd_week;
            result.text = frm.label.getStatic('week') + ' ' + result.prd_week_label + ' - ' + moment.unix(result.prd_week_start_timestamp).format("DD MMMM YYYY");
            return result;
        });
    else
        return [];
}
/**
 * Select2 for semester
 * @param {*} results 
 * @returns 
 */
frm.common.select2.semester = function (results) {
    if (results && results.length)
        return $.map(results, function (result) {
            result.id = frm.config.prefix.semester + result.prd_semester;
            result.text = frm.label.getStatic('semester') + ' ' + result.prd_semester;
            return result;
        });
    else
        return [];
}

/**
 * Select2 for modules
 * @param {*} results 
 * @returns 
 */
frm.common.select2.module = function (results) {
    if (results && results.length)
        return $.map(results, function (result) {
            result.id = result.tmt_module;
            result.text = result.tmt_module;
            return result;
        });
    else
        return [];
}

/*******************************************************************************
Framework - Common - Matomo
*******************************************************************************/

/**
 * Initiliase Matomo
 * @returns 
 */
frm.common.matomo.init = function () {
    if (!frm.config.matomo.enable)
        return;

    // Fetch instance
    var _paq = window._paq = window._paq || [];

    if (frm.config.matomo.disableCookies)
        // Cookieless tracking
        _paq.push(['disableCookies']);

    // Link tracking
    _paq.push(['enableLinkTracking']);

    (function () {
        const u = frm.config.matomo.baseUrl;
        _paq.push(['setTrackerUrl', u + 'matomo.php']);
        _paq.push(['setSiteId', frm.config.matomo.siteId]);
        var d = document, g = d.createElement('script'), s = d.getElementsByTagName('script')[0];
        g.async = true; g.src = u + 'matomo.js'; s.parentNode.insertBefore(g, s);
    })();

    // Set referrer
    window.referrerSPA = window.location.href;
}

/**
 * Track a Single Page App (SPA) visit
 * @param {*} isOTT 
 * @returns 
 */
frm.common.matomo.track = function (isOTT) {
    isOTT = isOTT || false;

    if (!frm.config.matomo.enable)
        return;

    // Fetch instance
    var _paq = window._paq = window._paq || [];

    _paq.push(['setReferrerUrl', window.referrerSPA]);
    _paq.push(['setCustomUrl', window.location.href]);
    _paq.push(['setDocumentTitle', document.title + (isOTT ? ' [' + frm.config.matomo.timetableTitle + ']' : '')]);
    // Tracker methods like "setCustomDimension" should be called before "trackPageView"
    _paq.push(['trackPageView']);

    if (!isOTT)
        // Update referrer
        window.referrerSPA = window.location.href;
}