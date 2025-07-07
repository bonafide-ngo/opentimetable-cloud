/*******************************************************************************
Framework - Firebase
*******************************************************************************/

/**
 * Firebase Version 9 (module) not compatible with service worker.
 * Must use Firebase Version 8 (namespace) instead
 * https://firebase.google.com/docs/web/setup#available-libraries
 */

// Import namespaced Firebase
importScripts('https://www.gstatic.com/firebasejs/8.10.1/firebase-app.js');
importScripts('https://www.gstatic.com/firebasejs/8.10.1/firebase-messaging.js');

// Configure Firebase
const firebaseConfig = {};

// Initialize Firebase
firebase.initializeApp(firebaseConfig);
const firebaseMessaging = firebase.messaging();

/**
 * Handle (only) data message, without notification
 */
firebaseMessaging.onBackgroundMessage((payload) => {
    // Trigger call notification for webrtc when on background
    // N.B. See frm.config.firebase.webRtc
    if (payload.data.type == 'webRtc') {
        const notificationOptions = {
            body: payload.data.body,
            data: payload.data
        };

        // Show notification
        self.registration.showNotification(payload.data.title, notificationOptions);
    }

    // Find all listening clients
    self.clients.matchAll().then(function (clients) {
        clients.forEach(function (client) {
            // Send the data to a client.
            client.postMessage(payload);
        });
    });

});

// Notification click event listener
self.addEventListener('notificationclick', e => {
    // Clone data without methods inherited form e.notification
    var payload = { data: e.notification.data };
    // Close the notification popout
    e.notification.close();
    // Get all the Window clients
    e.waitUntil(clients.matchAll({ includeUncontrolled: true, type: 'window' }).then(windowClients => {
        // Test every tab/window against the hostname
        if (!windowClients.some(function (windowClient) {
            const windowClientURL = new URL(windowClient.url);
            const notificationURL = new URL(e.notification.data.url);

            if (windowClientURL.hostname == notificationURL.hostname) {
                // Send the data to a client.
                windowClient.postMessage(payload);
                // Focus on the tab/window
                windowClient.focus();
                return true;
            } else
                return false;
        }))
            // Otherwise, open a new tab/window and focus
            clients.openWindow(e.notification.data.url).then(windowClient => windowClient ? windowClient.focus() : null);
    }));
});