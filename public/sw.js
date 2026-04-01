self.addEventListener("push", function (event) {
    let data = {
        title: "Notification",
        body: "New update",
        url: "/",
    };

    if (event.data) {
        try {
            const parsed = event.data.json();

            data = {
                title: parsed.title || data.title,
                body: parsed.body || data.body,
                url: parsed.url || parsed.data?.url || data.url,
            };
        } catch (e) {
            try {
                const text = event.data.text();
                data.body = text;
            } catch (err) {
                console.error("Push data error:", err);
            }
        }
    }

    event.waitUntil(
        self.registration.showNotification(data.title, {
            body: data.body,
            icon: "/favicon.ico",
            data: data.url,
        }),
    );
});

self.addEventListener("notificationclick", function (event) {
    event.notification.close();

    const targetUrl =
        typeof event.notification.data === "string" &&
        event.notification.data.trim() !== ""
            ? event.notification.data
            : "/";

    event.waitUntil(
        clients.matchAll({ type: "window", includeUncontrolled: true }).then(
            function (windowClients) {
                for (const client of windowClients) {
                    if ("focus" in client) {
                        client.navigate(targetUrl);
                        return client.focus();
                    }
                }

                if (clients.openWindow) {
                    return clients.openWindow(targetUrl);
                }
            },
        ),
    );
});

const CACHE_NAME = "hustle-v3";
const STATIC_ASSETS = [
    "/",
    "/offline.html",
    "/images/icons/asaba-hustle.png",
];

self.addEventListener("install", (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(STATIC_ASSETS);
        }),
    );

    self.skipWaiting();
});

self.addEventListener("activate", (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) =>
            Promise.all(
                cacheNames
                    .filter((cacheName) => cacheName !== CACHE_NAME)
                    .map((cacheName) => caches.delete(cacheName)),
            ),
        ),
    );

    self.clients.claim();
});

self.addEventListener("fetch", (event) => {
    const requestUrl = new URL(event.request.url);

    event.respondWith(
        caches.match(event.request).then((cached) => {
            if (cached) return cached;

            if (requestUrl.pathname === "/offline.html") {
                return caches.match("/offline.html");
            }

            return fetch(event.request).catch(() => {
                if (event.request.destination === "document") {
                    const offlineUrl = new URL(
                        "/offline.html",
                        self.location.origin,
                    );
                    offlineUrl.searchParams.set("retry", event.request.url);

                    return Response.redirect(offlineUrl.toString(), 302);
                }
            });
        }),
    );
});
