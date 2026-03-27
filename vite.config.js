import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import tailwindcss from "@tailwindcss/vite";

export default defineConfig({
    plugins: [
        laravel({
            input: ["resources/css/app.css", "resources/js/app.js"],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        host: "0.0.0.0",
        port: 5173,
        strictPort: true,
        cors: {
            origin: [
                "http://127.0.0.1:8000",
                "http://localhost:8000",
                "http://0.0.0.0:8000",
                "http://localhost",
            ],
        },
        hmr: {
            host: "127.0.0.1",
            protocol: "ws",
            port: 5173,
            clientPort: 5173,
        },
        watch: {
            ignored: ["**/storage/framework/views/**"],
        },
    },
});
