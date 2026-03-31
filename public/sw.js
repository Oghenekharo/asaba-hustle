self.addEventListener("push", function (event) {
    let data = {
        title: "Notification",
        body: "New update",
        url: "/",
    };

    if (event.data) {
        try {
            // Try JSON first (Laravel WebPush preferred format)
            const parsed = event.data.json();
            console.log(parsed);

            data = {
                title: parsed.title || data.title,
                body: parsed.body || data.body,
                url: parsed.url || parsed.data?.url || data.url,
            };
        } catch (e) {
            // Fallback to plain text
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

    console.log("Push received:", event);
});

const CACHE_NAME = "hustle-v1";
const STATIC_ASSETS = [
    "/",
    "/offline.html",
    "/images/icons/asaba-hustle.png", // ✅ add this
];

self.addEventListener("install", (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(STATIC_ASSETS);
        }),
    );
});

self.addEventListener("fetch", (event) => {
    event.respondWith(
        caches.match(event.request).then((cached) => {
            // 1. Return cached asset if available
            if (cached) return cached;

            // 2. Otherwise try network
            return fetch(event.request).catch(() => {
                // 3. Only fallback for HTML pages
                if (event.request.destination === "document") {
                    return caches.match("/offline.html");
                }
            });
        }),
    );
});
