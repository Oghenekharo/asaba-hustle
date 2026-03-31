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
