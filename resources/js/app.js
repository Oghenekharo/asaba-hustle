import "./bootstrap";
import $ from "jquery";
import jQuery from "jquery";
import { createIcons, icons } from "lucide";
import AOS from "aos";
import "aos/dist/aos.css";

import {
    handleAjaxForm,
    setActionButtonLoading,
    resetActionButtonLoading,
    togglePasswordView,
    showAlert,
    handleModalsForms,
    openLogModal,
    toggleChannel,
    toggleFilter,
    initUserDropdown,
    initJobCreationModal,
    initMessagesPage,
    initLocationFields,
    initDashboardLocationPrompt,
    initJobDetailPage,
    initNavbarNotifications,
    initNotificationsPage,
    registerPush,
} from "./main";

window.$ = window.jQuery = jQuery;
window.lucide = { createIcons, icons };

createIcons({ icons });
AOS.init();

const updateThemeToggleIcons = () => {
    const dark = document.documentElement.classList.contains('dark');
    document.querySelectorAll('[data-theme-icon-dark]').forEach((icon) => icon.classList.toggle('hidden', dark));
    document.querySelectorAll('[data-theme-icon-light]').forEach((icon) => icon.classList.toggle('hidden', !dark));
    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
        button.setAttribute('aria-label', dark ? 'Switch to light mode' : 'Switch to dark mode');
        button.setAttribute('title', dark ? 'Switch to light mode' : 'Switch to dark mode');
    });
};

document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
    button.addEventListener('click', () => {
        const dark = document.documentElement.classList.toggle('dark');
        localStorage.setItem('asaba-theme', dark ? 'dark' : 'light');
        updateThemeToggleIcons();
    });
});
updateThemeToggleIcons();

window.handleAjaxForm = handleAjaxForm;
window.togglePasswordView = togglePasswordView;
window.showAlert = showAlert;
window.handleModalsForms = handleModalsForms;
window.toggleChannel = toggleChannel;
window.toggleFilter = toggleFilter;
window.openLogModal = openLogModal;
window.registerPush = registerPush;
window.initNavbarNotifications = initNavbarNotifications;
window.initNotificationsPage = initNotificationsPage;

$(document).on("click", ".alert-close", function () {
    $(this).closest("#js-error-container").addClass("hidden");
});

$(document).on("submit", 'form[action$="/logout"]', function () {
    if (window.Echo) {
        try {
            window.Echo.disconnect();
        } catch (_) {
            // Ignore disconnect issues during logout.
        }
    }
});

// Ajax set up
$.ajaxSetup({
    headers: {
        "X-CSRF-TOKEN":
            document.querySelector('meta[name="csrf-token"]')?.content ?? "",
        Accept: "application/json",
    },
});

// Opening Modals
window.openModal = function (id, data = null) {
    const $modal = $(`#${id}`);
    const $backdrop = $modal.find(".js-modal-backdrop");
    const $content = $modal.find(".js-modal-content");

    // If data is passed, you can dynamically fill the modal
    if (data) {
        if (data.title) $modal.find(".js-modal-title").text(data.title);
        if (data.body) $modal.find(".js-modal-body").html(data.body);
    }

    $modal.removeClass("hidden");

    // Animate In
    setTimeout(() => {
        $backdrop.removeClass("opacity-0").addClass("opacity-100");
        $content
            .removeClass("scale-95 opacity-0")
            .addClass("scale-100 opacity-100");
    }, 10);
};

// Close Modals
window.closeModal = function (id) {
    const $modal = $(`#${id}`);
    const $backdrop = $modal.find(".js-modal-backdrop");
    const $content = $modal.find(".js-modal-content");

    // Animate Out
    $backdrop.removeClass("opacity-100").addClass("opacity-0");
    $content
        .removeClass("scale-100 opacity-100")
        .addClass("scale-95 opacity-0");

    setTimeout(() => {
        $modal.addClass("hidden");
    }, 300);
};

$(document).on("click", ".js-modal", function (event) {
    const $target = $(event.target);

    if (
        !$target.hasClass("js-modal-backdrop") &&
        !$target.closest(".js-modal-backdrop").length
    ) {
        return;
    }

    window.closeModal($(this).attr("id"));
});

// Password toggle
if ($(".js-password-toggle")) {
    togglePasswordView();
}

// Login users
if ($("#login-form").length) {
    handleAjaxForm("#login-form", "#login-submit");
}
// Register Users
if ($("#register-form").length) {
    handleAjaxForm("#register-form", "#register-submit");
}

if ($("#forgot-password-form").length) {
    handleAjaxForm("#forgot-password-form", "#forgot-password-submit");
}

function updateRegistrationVerificationMethod() {
    const method = document.querySelector(
        'input[name="verification_method"]:checked',
    )?.value;
    const emailInput = document.getElementById("email");

    if (!emailInput || !method) return;

    emailInput.required = method === "email";
    $("#verification-method-help").text(
        method === "email"
            ? "We’ll send a verification link to your email address."
            : "We’ll send a verification code to your phone.",
    );
}

$(function () {
    $('input[name="verification_method"]').on(
        "change",
        updateRegistrationVerificationMethod,
    );
    updateRegistrationVerificationMethod();
    $('input[name="channel"]').on("change", function () {
        toggleChannel(this.value);
    });

    (function () {
        if ($('input[name="channel"]').length > 0) {
            const checked = document.querySelector(
                'input[name="channel"]:checked',
            );

            if (checked) {
                toggleChannel(checked.value);
            }
        }
    })();

    $("#budget-slider").on("input", function () {
        $("#budget-val").text("MAX: ₦" + parseInt($(this).val()) / 1000 + "K");
    });
});

if ($("#reset-password-form").length) {
    handleAjaxForm("#reset-password-form", "#reset-password-submit");
}
if ($("#verify-phone-form").length) {
    handleAjaxForm("#verify-phone-form", "#verify-phone-submit");
}

if ($("#resend-btn").length) {
    $("#resend-btn").on("click", function () {
        const $btn = $(this);
        const url = $btn.data("url");

        setActionButtonLoading($btn, "Please wait...");

        $.ajax({
            url: url,
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },

            beforeSend: function () {
                showAlert(
                    "Resending verification code sent.",
                    "info",
                    "#verify-phone-form",
                );
            },
            success: function (response) {
                if (response.success === true) {
                    showAlert(
                        response.message || "Verification code sent.",
                        "success",
                        "#verify-phone-form",
                    );
                } else {
                    showAlert(
                        response.message || "An error occured.",
                        "error",
                        "#verify-phone-form",
                    );
                }

                resetActionButtonLoading($btn);
            },

            error: function (xhr) {
                let message = "Something went wrong.";

                if (xhr.responseJSON?.message) {
                    message = xhr.responseJSON.message;
                }
                showAlert(message, "error", "#verify-phone-form");

                resetActionButtonLoading($btn);
            },
        });
    });
}

if ($("#profile-update-form").length) {
    handleAjaxForm(
        "#profile-update-form",
        "#profile-update-submit",
        function (response) {
            if (response.success === true) {
                setTimeout(() => {
                    closeModal("editProfileModal");
                    location.reload();
                }, 3000);
            }
        },
    );
}
if ($("#profile-image-form").length) {
    handleAjaxForm(
        "#profile-image-form",
        "#profile-image-submit",
        function (response) {
            if (response.success === true) {
                setTimeout(() => {
                    location.reload();
                    closeModal("uploadPhotoModal");
                }, 3000);
            }
        },
    );
}

if ($("#job-create-form").length) {
    handleAjaxForm(
        "#job-create-form",
        "#job-create-submit",
        function (response) {
            if (response.success === true) {
                setTimeout(() => {
                    closeModal("createJobModal");
                    if (response?.data?.slug) {
                        window.location.href = `/dashboard/jobs/${response.data.slug}`;
                        return;
                    }

                    window.location.reload();
                }, 1200);
            }
        },
    );
}

if ($("#password-change-form").length) {
    handleAjaxForm("#password-change-form", "#password-change-submit");
}

if ($("#loggedOutBox").length) {
    const msg = $("#loggedOutBox").data("message");
    window.showAlert(msg, "warning", "#login-form");
    $("#loggedOutBox").remove();
}

window.initUserDropdown = initUserDropdown;

// Auto-initialize on load
$(document).ready(function () {
    window.initUserDropdown();
    initJobCreationModal();
    initMessagesPage();
    initLocationFields();
    initDashboardLocationPrompt();
    initJobDetailPage();
    initNavbarNotifications();
    initNotificationsPage();
});

// NEGOTIATION
if ($("#negotiation-form").length) {
    handleAjaxForm(
        "#negotiation-form",
        "#negotiation-submit",
        function (response) {
            if (response.status === true) {
                location.reload();
            }
        },
    );
}
$(document).ready(function () {
    let deferredPrompt = null;
    const installBanner = $("#installBanner");
    const installBtn = $("#installBtn");

    if (!installBanner.length || !installBtn.length) {
        return;
    }

    const isStandalone = window.matchMedia(
        "(display-mode: standalone)",
    ).matches;

    if (isStandalone) {
        installBanner.hide();
    }

    // -----------------------------
    // CHROME / EDGE
    // -----------------------------
    window.addEventListener("beforeinstallprompt", (e) => {
        e.preventDefault();
        deferredPrompt = e;

        installBanner.removeClass("hidden").show();
        installBtn.text("Install");
    });

    // -----------------------------
    // FIREFOX / SAFARI (fallback)
    // -----------------------------
    if (!("BeforeInstallPromptEvent" in window) && !isStandalone) {
        installBanner.removeClass("hidden").show();

        installBtn.text("How to Install");

        installBtn
            .off("click")
            .on("click", () => window.openModal("installGuideModal"));
    }

    // -----------------------------
    // INSTALL BUTTON (Chrome only)
    // -----------------------------
    installBtn.on("click", async () => {
        if (!deferredPrompt) return;

        deferredPrompt.prompt();

        const { outcome } = await deferredPrompt.userChoice;

        console.log(
            outcome === "accepted"
                ? "User installed the app"
                : "User dismissed install",
        );

        deferredPrompt = null;
        installBanner.addClass("hidden");
    });

    // -----------------------------
    // AFTER INSTALL
    // -----------------------------
    window.addEventListener("appinstalled", () => {
        installBanner.addClass("hidden");
    });

    // -----------------------------
    // AUTO HIDE
    // -----------------------------
    setTimeout(() => {
        installBanner.addClass("hidden");
    }, 20000);
});

const params = new URLSearchParams(window.location.search);
const handler = params.get("handler");

if (handler) {
    const url = handler.replace("web+hustle://", "");

    if (url.startsWith("job/")) {
        const publicKey = url.split("/")[1];
        window.location.href = `/dashboard/jobs/${publicKey}`;
    }

    if (url.startsWith("chat/")) {
        const id = url.split("/")[1];
        window.location.href = `/dashboard/conversations/${id}`;
    }
}
document
    .querySelectorAll("#enableNotifications, #dashboard-enable-push")
    .forEach((button) => {
        button.addEventListener("click", async () => {
            const status = document.getElementById("dashboard-permission-status");
            const showStatus = (message, isError = false) => {
                if (!status || button.id !== "dashboard-enable-push") return;
                status.textContent = message;
                status.classList.remove("hidden", "text-red-500", "text-emerald-600", "text-slate-500");
                status.classList.add(isError ? "text-red-500" : "text-emerald-600");
            };

            // 1. Basic Support Check
            if (!("Notification" in window)) {
                showStatus("Your browser does not support push notifications.", true);
                return;
            }

            if (Notification.permission === "denied") {
                showStatus("Notifications are blocked in browser settings. Allow them there, then try again.", true);
                return;
            }

            // 2. iOS Safari Check (Must be a PWA)
            const isStandalone =
                window.matchMedia("(display-mode: standalone)").matches ||
                window.navigator.standalone;
            if (!isStandalone && /iPhone|iPad|iPod/.test(navigator.userAgent)) {
                window.openModal("installGuideModal");
                return;
            }

            try {
                const subscription = await registerPush();

                if (!subscription) {
                    showStatus("Notifications were not enabled. Allow them in the browser prompt to continue.", true);
                    return;
                }

                button.textContent = "Push notifications enabled";
                showStatus("Push notifications are enabled on this device.");
            } catch (err) {
                console.error("Registration failed:", err);
                showStatus(err?.message || "Unable to enable push notifications.", true);
            }
        });
    });
