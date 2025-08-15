/*******************************************************************************
Service Worker (PWA)
*******************************************************************************/

self.addEventListener("install", () => {
    // Activate immediately after install
    self.skipWaiting();
});

self.addEventListener("activate", (event) => {
    // Take control of all pages under scope right away
    event.waitUntil(self.clients.claim());
});

self.addEventListener("fetch", () => {
    // Do nothing – pass all requests to the network
});
