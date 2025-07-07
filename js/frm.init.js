// Init
var frm = frm || {};

/*******************************************************************************
Framework - Initialise - Loads
*******************************************************************************/
// Load resources
$(document).ready(function () {
    // Toasts
    frm.ajax.content.load("#toast", "./toast.html");
    // Timetable templates
    frm.ajax.content.load("#timetable-compact", "./timetable.compact.html");
    frm.ajax.content.load("#timetable-full", "./timetable.full.html");
    // Modal templates
    frm.ajax.content.load("#modal-app", "./modal.app.html");
    frm.ajax.content.load("#modal-frm", "./modal.frm.html");
});

/*******************************************************************************
Framework - Initialise - Label
*******************************************************************************/
frm.label.loadLang();

// Set Document Title
frm.label.setMetaTitle(frm.label.getStatic('i-title'));

// Set Meta Description
frm.label.setMetaDescription(frm.label.getStatic('i-description'));

// Set Meta Lang
frm.label.setMetaLang(frm.label.lang);

// Parse labels
$(document).ready(function () {
    frm.label.getStatic();
});

/*******************************************************************************
Framework - Initialise - SmoothState
*******************************************************************************/
frm.ss = {};
frm.ss.engine = null;
frm.ss.transition = null;
frm.ss.params = null;
frm.ss.trigger = null;

// Pull shortcut trigger
frm.ss.pullTrigger = function () {
    var trigger = frm.ss.trigger;
    // Reset trigger
    frm.ss.trigger = null;
    //Return trigger
    return trigger;
}

$(document).ready(function () {
    // Initialise SS
    frm.ss.engine = $('#ss-container').show().smoothState({
        blacklist: '.ss-none',
        progressOverlay: false,
        prefetch: true,
        cacheLength: 0,
        onBefore: function ($currentTarget, $container) {
            // Blank location from previous redirects
            frm.location = null;
        },
        onStart: {
            duration: 0,
            render: function ($container, push) {
                // Set transition base don flow direction
                frm.ss.transition = push ? 'moveright' : 'moveleft';
                // Blank location from previous redirects
                frm.location = null;
            }
        },
        onReady: {
            duration: 200,
            render: async function ($container, $newContent) {
                // Run transition
                $('#ss-container').attr('data-transition', frm.ss.transition);
                // Set content
                $container.html($newContent);
            }
        }
    }).data('smoothState');
});

/*******************************************************************************
Framework - Initialise - sessionStorage across tabs
https://blog.guya.net/2015/06/12/sharing-sessionstorage-between-tabs-for-secure-multi-tab-authentication/
*******************************************************************************/

if (!sessionStorage.length) {
    // Sync sessionStorage across tabs
    localStorage.setItem('__syncStorage', Date.now());
};

// Listner for storage event
window.addEventListener('storage', function (event) {
    switch (event.key) {
        case '__syncStorage':
            // Move sessionStorage across another tab by calling events
            localStorage.setItem('__sessionStorage', JSON.stringify(sessionStorage));
            localStorage.removeItem('__sessionStorage');
            break;
        case '__sessionStorage':
            localStorage.removeItem('__syncStorage');
            if (!sessionStorage.length) {
                // Fill empty sessionStorage
                var session = JSON.parse(event.newValue);
                for (key in session) {
                    sessionStorage.setItem(key, session[key]);
                }
            }
            break;
    }
});
/*******************************************************************************
Framework - Initialise - Resize
*******************************************************************************/

/*******************************************************************************
Framework - Initialise - Geolocation
*******************************************************************************/

frm.initGeolocation();

/*******************************************************************************
Framework - Initialise - Steams
*******************************************************************************/

/*******************************************************************************
Framework - Initialise - Modal, Back button, Print
*******************************************************************************/

$(document).ready(function () {
    $('.modal').once('shown.bs.modal', function (e) {
        // Push modal state
        frm.ss.engine.modalPush();
    }, true);
});

$(document).ready(function () {
    frm.modal.fixPrint();
});

window.onbeforeprint = function () {
    if ($('#timetable-full-modal').hasClass('show'))
        $('head').append($('<style>', {
            name: 'print',
            text: `@media print {
            @page {
                size: landscape !important;
            }
        }`,
        }));
};
window.onafterprint = function () {
    $('head').find('style[name="print"]').remove();
};

/*******************************************************************************
Framework - Initialise - Firebase
*******************************************************************************/

/**
 * Firebase Version 9 (module) not compatible with service worker.
 * Must use Firebase Version 8 (namespace) instead
 * https://firebase.google.com/docs/web/setup#available-libraries
 */

/* TODO, pending notification implementation
// Initialise Firebase
frm.firebase.init(frm.config.firebase.fcm.oAuth);
// Initialise Location
frm.firebase.locate.init();
*/

/*******************************************************************************
Framework - Initialise - Select2
*******************************************************************************/

frm.initSelect2 = function (selector, data, defaultId, allowClear) {
    allowClear = allowClear || false;
    defaultId = defaultId !== null ? defaultId : null;

    // Destroy if already initialised
    if ($(selector).hasClass("select2-hidden-accessible")) {
        $(selector).select2('destroy');
        $(selector).empty().val(null);
    }

    return $(selector).select2({
        data: data,
        dropdownAutoWidth: true,
        allowClear: allowClear,
        language: frm.label.lang,
        theme: "bootstrap-5",
        placeholder: frm.label.getStatic('type-select'),
        templateResult: function (select) {
            switch (select.id) {
                case 'divider':
                    return $('<hr>', { class: 'm-0' });
                    break;
                default:
                    return select.text
                    break;
            }
        }
    }).val(defaultId).trigger('change');
}

/*******************************************************************************
Framework - Initialise - MSAL
*******************************************************************************/

/**
 * Override msal logout method
 * 
 * @param {*} intentional 
 */
frm.msal.override.logout = function (intentional) {
    intentional = intentional || false;

    // Clear VTI cryptos
    frm.crypto.vti.clear();

    // User has intentionally logged out
    if (intentional) {
        frm.modal.success(frm.label.getStatic('success-logout'));
        $('#modal-success').once('hidden.bs.modal', function () {
            // Redirect to home
            window.location.href = frm.config.url.home;
        });
    }
};

/**
 * Override msal init method
 * @param {*} isLogin 
 */
frm.msal.override.init = async function (isLogin) {
    isLogin = isLogin || false;

    // Check user is MSAL authenticated
    if (frm.msal.isAuthenticated()) {
        // Set fullname in breadcrumb
        $('#breadcrumb').find('[name="fullname"]').text(frm.msal.decodedIdToken.given_name + ' ' + frm.msal.decodedIdToken.family_name).fadeIn();

        // Init role by user groups
        switch (await frm.msal.initRole()) {
            case C_MSAL_GROUP_STUDENT:
                // Hide admin button
                $('button[name="admin"').hide();
                // Hide draft button
                $('button[name="draft"').hide();
                // Show logout button
                $('button[name="logout"').show();
                // Open student tab
                if (isLogin)
                    frm.ss.engine.load(frm.config.url.student);
                break;
            case C_MSAL_GROUP_REVIEWER:
                // Hide admin button
                $('button[name="admin"').hide();
                // Hide draft button
                $('button[name="draft"').hide();
                // Show logout button
                $('button[name="logout"').show();
                break;
            case C_MSAL_GROUP_STAFF:
                // Show admin button
                $('button[name="admin"').show();
                // Hide draft button
                $('button[name="draft"').hide();
                // Show logout button
                $('button[name="logout"').show();
                // Open admin tab
                if (isLogin)
                    frm.ss.engine.load(frm.config.url.admin);
                break;
            case C_MSAL_GROUP_ADMIN:
                // Show admin button
                $('button[name="admin"').show();
                // Hide draft button
                $('button[name="draft"').hide();
                // Show logout button
                $('button[name="logout"').show();
                // Open admin tab
                if (isLogin)
                    frm.ss.engine.load(frm.config.url.admin);
                break;
            default:
                // N.B. Authenticated user with no role

                // Hide admin button
                $('button[name="admin"').hide();
                // Hide draft button
                $('button[name="draft"').hide();
                // Show logout button
                $('button[name="logout"').show();
                break;
        }
    } else {
        // Reset fullname in breadcrumb
        $('#breadcrumb').find('[name="fullname"]').text('').fadeOut();

        // Reset buttons
        $('button[name="admin"').show();
        $('button[name="draft"').show();
        $('button[name="logout"').hide();
    }
}

// Set MSAL plublic client app
frm.msal.setPublicClientApplication();
