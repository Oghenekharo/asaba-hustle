<x-modal id="installGuideModal" title="Install App">
    <div class="space-y-5">
        <div class="flex items-center gap-3">
            <img src="/images/icons/asaba-hustle.png" alt="Asaba Hustle" class="h-10 w-10 rounded-xl" />
            <div>
                <h3 class="font-semibold text-gray-900">Install Asaba Hustle</h3>
                <p class="text-xs text-gray-500">Get faster access and enable notifications from your home screen.</p>
            </div>
        </div>

        <div class="space-y-2">
            <h4 class="text-sm font-semibold text-gray-800">Android (Chrome)</h4>
            <ol class="list-inside list-decimal space-y-1 text-sm text-gray-600">
                <li>Tap the browser menu in the top right.</li>
                <li>Select <strong>Install App</strong>.</li>
                <li>Tap <strong>Install</strong>.</li>
            </ol>
        </div>

        <div class="space-y-2">
            <h4 class="text-sm font-semibold text-gray-800">Firefox</h4>
            <ol class="list-inside list-decimal space-y-1 text-sm text-gray-600">
                <li>Tap the browser menu.</li>
                <li>Select <strong>Add to Home screen</strong>.</li>
                <li>Confirm installation.</li>
            </ol>
        </div>

        <div class="space-y-2">
            <h4 class="text-sm font-semibold text-gray-800">iPhone (Safari)</h4>
            <ol class="list-inside list-decimal space-y-1 text-sm text-gray-600">
                <li>Tap the Share button in Safari.</li>
                <li>Select <strong>Add to Home Screen</strong>.</li>
                <li>Tap <strong>Add</strong>, then reopen the app from your home screen.</li>
            </ol>
        </div>

        <div class="pt-2">
            <button onclick="closeModal('installGuideModal')"
                class="w-full rounded-xl bg-orange-500 py-2.5 font-semibold text-white transition active:scale-95">
                Got it
            </button>
        </div>
    </div>
</x-modal>
