// Init
var frm = frm || {};

/*******************************************************************************
Framework - Firebase
*******************************************************************************/

frm.firebase = {};
frm.firebase.app = {};
frm.firebase.messaging = {};
frm.firebase.token = null;
frm.firebase.type = null;
frm.firebase.android = {};
frm.firebase.ios = {};
frm.firebase.browser = {};
frm.firebase.browser.isNotification = false;

// Constants
const C_FIREBASE_TYPE_ANDROID = 'android';
const C_FIREBASE_TYPE_IOS = 'ios';
const C_FIREBASE_TYPE_BROWSER = 'browser';

/**
 * Initialise firebase
 */
frm.firebase.init = function (config) {
    // Configure Firebase
    frm.firebase.config = config;
    // Set type
    frm.firebase.setType();
    // Register handler
    frm.firebase.registerHandler();
    // Sync cookies
    frm.firebase.syncCookies();
}

/**
 * Set firebase type
 */
frm.firebase.setType = function () {
    if (typeof Android != 'undefined')
        frm.firebase.type = C_FIREBASE_TYPE_ANDROID;
    else if (typeof webkit != 'undefined')
        frm.firebase.type = C_FIREBASE_TYPE_IOS;
    else
        frm.firebase.type = C_FIREBASE_TYPE_BROWSER;
}

/**
 * Get firebase type
 */
frm.firebase.getType = function () {
    return frm.firebase.type;
}

/**
 * Get the environment version label
 * @returns 
 */
frm.firebase.versionLabel = function () {
    var userAgentParts = window.navigator.userAgent.split('/');

    // Set version based on environment type
    switch (frm.firebase.getType()) {
        case C_FIREBASE_TYPE_ANDROID:
            // Android version appened to the userAgent 
            return frm.label.getStatic('android') + ' ' + userAgentParts.pop();
            break;
        case C_FIREBASE_TYPE_IOS:
            // iOS version appened to the userAgent 
            return frm.label.getStatic('ios') + ' ' + userAgentParts.pop();
            break;
        case C_FIREBASE_TYPE_BROWSER:
            return frm.label.getStatic('web') + ' ' + frm.config.version;
            break;
    }
}

/**
 * Register firebase handler
 */
frm.firebase.registerHandler = function () {
    // Switch among types
    switch (frm.firebase.getType()) {
        case C_FIREBASE_TYPE_ANDROID:
            // Handler called by the Android interface (frm.firebase.android.handler)
            break;

        case C_FIREBASE_TYPE_IOS:
            // Handler called by the iOS interface (frm.firebase.ios.handler)
            break;

        case C_FIREBASE_TYPE_BROWSER:
        default:
            frm.firebase.browser.handler();
            break;
    }
}

/**
 * Get the token if any
 * @returns 
 */
frm.firebase.getToken = function () {
    return frm.firebase.token;
}

/**
 * Sync Token with firebase
 */
frm.firebase.syncToken = function () {
    // Switch among types
    switch (frm.firebase.getType()) {
        case C_FIREBASE_TYPE_ANDROID:
            // Call the Android interface to retrieve the firebase token
            if (Android.hookFirebaseToken)
                Android.hookFirebaseToken();
            break;

        case C_FIREBASE_TYPE_IOS:
            // Call the iOS interface to retrieve the firebase token
            // N.B. postMessage requires mandatory arguments, so let's pass a blank string not to upset webkit
            webkit.messageHandlers.hookFirebaseToken.postMessage("");
            break;

        case C_FIREBASE_TYPE_BROWSER:
        default:
            // Check if notifications are enabled / supported
            if (!frm.firebase.browser.isNotification)
                return;

            // Call the browser interface to retrieve the firebase token
            frm.firebase.messaging.getToken({ vapidKey: frm.config.firebase.fcm.webpushKeyPair }).then((token) => {
                frm.firebase.browser.hookFirebaseToken_Callback(token);
            }).catch((error) => {
                // Log error
                console.log(error);

                $("#modal-notification").modal("show");
                $('#modal-notification').find('button').once('click', function () {
                    frm.ss.engine.load(window.location.href);
                });
            });
            break;
    }
}

/**
 * Firebase sync cookies
 * Closure function for persistent memory
 * The outer function is executed immediately, returning the inner function, and creating a private scope
 */
frm.firebase.syncCookies = function () {
    // N.B. Currently required by iOS only
    if (frm.firebase.getType() != C_FIREBASE_TYPE_IOS)
        return;

    var sync = function () {
        // Store for later
        var pastCookie = document.cookie;

        return function () {
            // Check is any cookie has changed
            if (pastCookie != document.cookie) {
                // Store for later
                pastCookie = document.cookie;

                // Call the iOS interface to sync cookies
                // N.B. postMessage requires mandatory arguments, so let's pass a blank string not to upset webkit
                webkit.messageHandlers.hookSyncCookies.postMessage("");
            }
        }
    }();

    if (!window.syncCookies)
        // Monitor the change in cookies to ping iOS for archiving cookies
        window.syncCookies = window.setInterval(sync, 100);
};

/**
 * Firebase android and ios handler
 */
frm.firebase.hookFirebaseToken_Callback = function (token) {
    if (!token) {
        // Log to trace error
        console.log('Device notifications are not enabled.', 'userAgent: ' + window.navigator.userAgent);

        // Show error
        $("#modal-notification").modal("show");
        $('#modal-notification').find('button').once('click', function () {
            frm.ss.engine.load(window.location.href);
        });
        return;
    }

    // Compare and register
    if (token != frm.firebase.token) {
        // Register token locally
        frm.firebase.token = token;
        // Register token against the user
        frm.ajax.jsonrpc.request(
            frm.config.url.api,
            'App.Profile.Update_FirebaseToken',
            { firebaseToken: token });
    }

}
// Interface registered by Android
frm.firebase.android.hookFirebaseToken_Callback = frm.firebase.hookFirebaseToken_Callback;
// Interface registered by iOS
frm.firebase.ios.hookFirebaseToken_Callback = frm.firebase.hookFirebaseToken_Callback;
// Browser
frm.firebase.browser.hookFirebaseToken_Callback = frm.firebase.hookFirebaseToken_Callback;

/**
 * Firebase android and ios handler for incoming messages
 * N.B. Registered by the Android interface
 * @param {*} jsonData 
 */
frm.firebase.android.handler = function (jsonData) {
    // Dispatch a notification
    frm.firebase.dispatch(JSON.parse(jsonData));
}
frm.firebase.ios.handler = frm.firebase.android.handler;

/**
 * Firebase browser handler
 */
frm.firebase.browser.handler = function () {
    // Check that browser notifications are supported
    if (firebase.messaging.isSupported()) {
        // Store for later
        frm.firebase.browser.isNotification = true;

        // Initialize Firebase
        frm.firebase.app = firebase.initializeApp(frm.firebase.config);
        frm.firebase.messaging = firebase.messaging(frm.firebase.app);
    } else {
        // Log to trace error
        console.log('Browser notifications are not supported.', 'userAgent: ' + window.navigator.userAgent);
        return;
    }

    // Handle incoming message in foreground
    frm.firebase.messaging.onMessage((payload) => {
        // Do nothing, to avoid duplicate call by service worker
    });

    // Handle incoming message from service worker
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.addEventListener('message', e => {
            var payload = e.data;
            // Dispatch a notification
            frm.firebase.dispatch(payload.data);
        });
    }
}

/**
 * Dispatch a notification
 * @param {*} data 
 * @returns 
 */
frm.firebase.dispatch = function (data) {
    if (data && data.type)
        // Wait for resources to be loaded before dispatching the signal
        $(document).ready(function () {
            switch (data.type) {
                // Generic notification
                case frm.config.firebase.generic:
                    // Toast generic, no click
                    frm.common.toast.generic(data.title, data.body);
                    break;
            }
        });
}