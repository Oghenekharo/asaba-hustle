function setButtonLoading($btn, state = true) {
    const $spinner = $btn.find(".spinner");
    const $icon = $btn.find(".button-icon");

    if (state) {
        if ($btn.data("loading")) return;

        $btn.data("loading", true);
        $btn.prop("disabled", true);

        $icon.addClass("hidden");
        $spinner.removeClass("hidden");
    } else {
        $btn.data("loading", false);
        $btn.prop("disabled", false);

        $spinner.addClass("hidden");
        $icon.removeClass("hidden");
    }
}

/**
 * Generic AJAX Form Handler for Fintech UI
 * @param {string} formId - The ID of the form (e.g., '#login-form')
 * @param {string} btnId - The ID of the submit button (e.g., '#submit-btn')
 * @param {function} onSuccess - Optional custom success logic
 * @param {object} options
 * @param {function} options.beforeSubmit - Optional gate; return false to pause submit
 */
export function handleAjaxForm(formId, btnId, onSuccess = null, options = {}) {
    const $form = $(formId);
    const $btn = $(btnId);
    const $btnText = $btn.find(".button-text"); // Finds the text span inside the button
    const $errorContainer = $form.find("#js-error-container").first();
    const originalBtnText = $btnText.text();

    const runAjax = function () {
        // 1. Reset UI State
        $(".text-red-500").addClass("hidden").text(""); // Hide all field errors
        $errorContainer.addClass("hidden").html("");
        setButtonLoading($btn, true);
        $btnText.text("Processing...");

        // 2. Prepare Data (Handle Files vs. Text)
        const isMultipart = $form.attr("enctype") === "multipart/form-data";
        const formData = isMultipart
            ? new FormData($form[0])
            : $form.serialize();

        $.ajax({
            url: $form.attr("action"),
            type: "POST",
            data: formData,
            processData: !isMultipart,
            contentType: isMultipart
                ? false
                : "application/x-www-form-urlencoded; charset=UTF-8",
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (response) {
                showAlert(
                    response.message || "Success!",
                    response.success === true ? "success" : "error",
                    formId,
                );
                if (onSuccess) {
                    onSuccess(response);
                } else {
                    // Default redirect logic
                    $btnText.text("Redirecting...");
                    if (response.data.redirect) {
                        setTimeout(
                            () =>
                                (window.location.href = response.data.redirect),
                            1500,
                        );
                    } else {
                        setButtonLoading($btn, false);
                        $btnText.text(originalBtnText);
                    }
                }
            },
            error: function (xhr) {
                // 3. Reset Button on Error
                setButtonLoading($btn, false);
                $btnText.text(originalBtnText);

                if (xhr.status === 422) {
                    showAlert(
                        xhr.responseJSON.error ??
                            xhr.responseJSON.message ??
                            "Please fix the errors below.",
                        "error",
                        formId,
                    );
                    const errors = xhr.responseJSON.errors;
                    $.each(errors, function (key, message) {
                        $(`#error_${key}`)
                            .removeClass("hidden")
                            .text(message[0]);
                    });
                } else {
                    const errorMsg =
                        xhr.responseJSON.message ||
                        "An unexpected error occurred.";
                    showAlert(errorMsg, "error", formId);
                    if (window.lucide?.createIcons) {
                        window.lucide.createIcons({
                            icons: window.lucide.icons,
                        });
                    }
                }
            },
        });
    };

    // runAjax();

    $form.on("submit", function (e) {
        e.preventDefault();

        if (typeof options.beforeSubmit === "function") {
            const canContinue = options.beforeSubmit($form, $btn);
            if (canContinue === false) return;
        }

        setTimeout(runAjax, 10);
    });

    $form.on("ajaxSubmit", function (e) {
        e.preventDefault();
        runAjax();
    });
}

export const togglePasswordView = function () {
    $(".js-password-toggle").on("click", function () {
        const btn = $(this);
        const inputId = btn.data("target");
        const input = $("#" + inputId);
        const eyeIcon = btn.find(".js-eye-icon");
        const eyeOffIcon = btn.find(".js-eye-off-icon");
        console.log(input.attr("type"));

        // Toggle input type
        if (input.attr("type") === "password") {
            input.attr("type", "text");
            eyeIcon.addClass("hidden");
            eyeOffIcon.removeClass("hidden");
        } else {
            input.attr("type", "password");
            eyeIcon.removeClass("hidden");
            eyeOffIcon.addClass("hidden");
        }
    });
};

window.closeAlert = function (el) {
    const $alert = $(el).closest("#js-error-container");

    $alert.fadeOut(150, function () {
        $alert.addClass("hidden").hide("fast");
    });
};

/**
 * Updates the Alert Component UI
 * @param {string} message - The text to show
 * @param {string} type - 'success', 'error', 'warning', or 'info'
 */
export const showAlert = function (message, type = "error", parent = null) {
    let $container = $("#js-error-container").first();

    if (parent) {
        const $parent = $(parent).first();

        if ($parent.length) {
            $container = $parent.is("#js-error-container")
                ? $parent
                : $parent.find("#js-error-container").first();
        }
    }

    if (!$container.length) {
        return;
    }

    // 1. Theme and Icon Maps
    const themes = {
        success: "border-emerald-100 bg-emerald-50/50 text-emerald-600",
        error: "border-red-100 bg-red-50/50 text-red-600",
        warning: "border-amber-100 bg-amber-50/50 text-amber-600",
        info: "border-blue-100 bg-blue-50/50 text-blue-600",
        default: "border-gray-100 bg-gray-50/50 text-gray-600",
    };

    const icons = {
        success: "check-circle",
        error: "alert-circle",
        warning: "alert-triangle",
        info: "info",
        default: "check",
    };

    // 2. Clear the container and remove old theme classes
    $container.empty().removeClass(Object.values(themes).join(" "));

    // 3. Build the inner HTML
    const innerHtml = `
    <div class='flex items-center justify-between'>
        <div class="flex items-center gap-2">
            <i data-lucide="${icons[type] ?? icons["default"]}" class="h-4 w-4"></i>
            <span id="error-message" class="font-medium text-xs">${message}</span>
        </div>
        <button type="button" onclick="closeAlert(this)"
            class="flex items-center cursor-pointer justify-center opacity-70 hover:opacity-100">
            <i data-lucide="x" class="h-4 w-4"></i>
        </button>
    </div>
    `;

    // 4. Inject and Show
    $container
        .addClass(themes[type] ?? themes["default"])
        .append(innerHtml)
        .removeClass("hidden");

    // Re-hydrate lucide icons added via dynamic HTML injection.
    if (window.lucide?.createIcons) {
        try {
            window.lucide.createIcons({ icons: window.lucide.icons });
        } catch (_) {
            // Ignore: dynamic icon rendering should never break form flows.
        }
    }
};

function renderAvatarMarkup(name = "User", photoUrl = "") {
    const safeName = escapeHtml(name || "User");
    const safePhotoUrl = escapeHtml(photoUrl || "");
    const initial = escapeHtml(
        (
            String(name || "User")
                .trim()
                .charAt(0) || "U"
        ).toUpperCase(),
    );

    if (safePhotoUrl) {
        return `<img src="${safePhotoUrl}" alt="${safeName}" class="h-full w-full object-cover" />`;
    }

    return `<span>${initial}</span>`;
}

function renderAvatarShell(name = "User", photoUrl = "", options = {}) {
    const size = options.size ?? "h-10 w-10";
    const rounded = options.rounded ?? "rounded-2xl";
    const text = options.text ?? "text-sm";
    const extraClass = options.className ?? "";

    return `
        <div class="${size} ${rounded} ${text} ${extraClass} overflow-hidden bg-slate-900 text-white flex items-center justify-center font-black uppercase shrink-0">
            ${renderAvatarMarkup(name, photoUrl)}
        </div>
    `;
}

export const handleModalsForms = function (form, btn, modal, loc = null) {
    if ($("#" + form).length) {
        handleAjaxForm("#" + form, "#" + btn, function (response) {
            setTimeout(() => {
                closeModal(modal);
                if (loc === null) {
                    location.reload();
                } else {
                    window.location.href = loc;
                }
            }, 3000);
        });
    }
};

export const toggleChannel = function (val = "phone") {
    const phone = document.getElementById("phone-field");
    const email = document.getElementById("email-field");
    if (val === "phone") {
        phone.classList.remove("hidden");
        email.classList.add("hidden");
    } else {
        phone.classList.add("hidden");
        email.classList.remove("hidden");
    }
};

export const toggleFilter = function (id) {
    const el = document.getElementById(id);
    const arrow = document.getElementById(id.split("-")[0] + "-arrow");
    el.classList.toggle("hidden");
    arrow.classList.toggle("rotate-180");
};

export const initUserDropdown = function () {
    const $trigger = $("#userDropdownTrigger");
    const $menu = $("#userDropdownMenu");
    const $arrow = $("#dropdownArrow");
    const $overlay = $("#userDrawerOverlay");
    const $close = $("#userDrawerClose");

    if (!$trigger.length || !$menu.length) return;

    const isMobileDrawer = () => window.innerWidth < 768;

    function openMenu() {
        if (isMobileDrawer()) {
            $menu.removeClass("hidden translate-x-[105%]");
            $overlay.removeClass("hidden");
            return;
        }

        $menu.removeClass("hidden");
        $arrow.addClass("rotate-180");
    }

    function closeMenu() {
        if (isMobileDrawer()) {
            $menu.addClass("translate-x-[105%]");
            $overlay.addClass("hidden");

            setTimeout(() => {
                if (isMobileDrawer()) {
                    $menu.addClass("hidden");
                }
            }, 300);

            return;
        }

        $menu.addClass("hidden");
        $arrow.removeClass("rotate-180");
    }

    $trigger.on("click", function (e) {
        e.stopPropagation();

        if (isMobileDrawer()) {
            if ($menu.hasClass("hidden")) {
                openMenu();
            } else {
                closeMenu();
            }

            return;
        }

        $menu.toggleClass("hidden");
        $arrow.toggleClass("rotate-180");
    });

    $close.on("click", closeMenu);
    $overlay.on("click", closeMenu);

    $(window).on("click", function (e) {
        if (
            !$trigger.is(e.target) &&
            $trigger.has(e.target).length === 0 &&
            !$menu.is(e.target) &&
            $menu.has(e.target).length === 0
        ) {
            closeMenu();
        }
    });

    $(window).on("resize", function () {
        if (!isMobileDrawer()) {
            $overlay.addClass("hidden");
            $menu.removeClass("translate-x-[105%]");
        }
    });
};

export const initJobCreationModal = function () {
    if (!$(".js-open-job-modal").length) return;

    $(".js-open-job-modal").on("click", function () {
        const skillId = $(this).data("skill-id");
        const skillName = $(this).data("skill-name");
        const $form = $("#job-create-form");
        const $skillSelect = $("#job_skill_id");
        const $modalTitle = $("#createJobModal .js-modal-title");
        const $errorBox = $("#job-create-form #js-error-container");

        if ($form.length) {
            $form[0].reset();
            $form.find(".text-red-500").addClass("hidden").text("");
        }

        $errorBox.addClass("hidden").empty();

        if ($skillSelect.length) {
            $skillSelect.val(skillId || "");
        }

        if ($modalTitle.length) {
            $modalTitle.text(
                skillName
                    ? `Post a New ${skillName} Hustle`
                    : "Post a New Hustle",
            );
        }

        window.openModal("createJobModal");
        $("#job_title").trigger("focus");
    });
};

let cachedBrowserLocation = null;
let browserLocationRequest = null;

function updateLocationStatus(target, message, tone = "default") {
    if (!target) return;

    const $target = $(target);

    if (!$target.length) return;

    $target
        .removeClass("text-slate-400 text-red-500 text-emerald-600")
        .addClass(
            tone === "error"
                ? "text-red-500"
                : tone === "success"
                  ? "text-emerald-600"
                  : "text-slate-400",
        )
        .text(message);
}

function getBrowserLocation() {
    if (cachedBrowserLocation) {
        return Promise.resolve(cachedBrowserLocation);
    }

    if (browserLocationRequest) {
        return browserLocationRequest;
    }

    browserLocationRequest = new Promise((resolve, reject) => {
        if (!navigator.geolocation) {
            reject(new Error("Geolocation is not supported on this browser."));
            return;
        }

        navigator.geolocation.getCurrentPosition(
            function (position) {
                cachedBrowserLocation = {
                    latitude: position.coords.latitude.toFixed(6),
                    longitude: position.coords.longitude.toFixed(6),
                };

                resolve(cachedBrowserLocation);
            },
            function () {
                reject(new Error("Location access was denied or unavailable."));
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 300000,
            },
        );
    }).finally(function () {
        browserLocationRequest = null;
    });

    return browserLocationRequest;
}

function hydrateLocationFields(options = {}) {
    const $lat = $(options.latTarget);
    const $long = $(options.longTarget);

    if (!$lat.length || !$long.length) return;

    const hasExistingValues =
        String($lat.val() ?? "").trim() !== "" &&
        String($long.val() ?? "").trim() !== "";

    if (hasExistingValues && !options.force) {
        updateLocationStatus(
            options.statusTarget,
            "Saved coordinates loaded",
            "success",
        );
        return;
    }

    updateLocationStatus(
        options.statusTarget,
        "Requesting browser coordinates...",
    );

    getBrowserLocation()
        .then(function (coords) {
            $lat.val(coords.latitude);
            $long.val(coords.longitude);
            updateLocationStatus(
                options.statusTarget,
                "Browser location synced",
                "success",
            );
        })
        .catch(function (error) {
            updateLocationStatus(options.statusTarget, error.message, "error");
        });
}

export const initLocationFields = function () {
    const bindings = [
        {
            latTarget: "#profile_latitude",
            longTarget: "#profile_longitude",
            statusTarget: "#profile-location-status",
        },
        {
            latTarget: "#job_latitude",
            longTarget: "#job_longitude",
            statusTarget: "#job-location-status",
        },
    ];

    bindings.forEach(function (binding) {
        if ($(binding.latTarget).length && $(binding.longTarget).length) {
            hydrateLocationFields(binding);
        }
    });

    $(".js-location-refresh").on("click", function () {
        hydrateLocationFields({
            latTarget: $(this).data("lat-target"),
            longTarget: $(this).data("long-target"),
            statusTarget: $(this).data("status-target"),
            force: true,
        });
    });
};

function escapeHtml(value) {
    return String(value ?? "")
        .replaceAll("&", "&amp;")
        .replaceAll("<", "&lt;")
        .replaceAll(">", "&gt;")
        .replaceAll('"', "&quot;")
        .replaceAll("'", "&#39;");
}

function scrollMessagesToBottom(animated = false) {
    const $container = $("#messages-container");

    if (!$container.length) return;

    const target = $container[0].scrollHeight;

    if (animated) {
        $container.stop().animate({ scrollTop: target }, 300);
        return;
    }

    $container.scrollTop(target);
}

const formatTime = (dateString) => {
    if (!dateString || dateString === "Just now") return "Just now";
    const date = new Date(dateString);
    return date.toLocaleTimeString([], {
        hour: "2-digit",
        minute: "2-digit",
        hour12: true,
    });
};

function isSingleEmojiMessage(value = "") {
    const trimmed = String(value ?? "").trim();

    if (!trimmed) return false;
    if (/[\p{L}\p{N}]/u.test(trimmed)) return false;
    if (!/\p{Extended_Pictographic}/u.test(trimmed)) return false;

    if (typeof Intl !== "undefined" && typeof Intl.Segmenter === "function") {
        const graphemes = [
            ...new Intl.Segmenter(undefined, {
                granularity: "grapheme",
            }).segment(trimmed),
        ];

        return graphemes.length === 1;
    }

    return trimmed.length <= 4;
}

function buildMessageMarkup(message, currentUserId, options = {}) {
    const senderId = Number(message?.sender?.id ?? message?.sender_id ?? 0);
    const isOwnMessage = senderId === Number(currentUserId);
    const rawContent = message?.message ?? "";
    const content = escapeHtml(rawContent);
    const senderNameRaw = message?.sender?.name ?? "Unknown user";
    const senderName = escapeHtml(senderNameRaw);
    const senderPhotoUrl =
        message?.sender?.profile_photo_url ?? message?.sender?.avatar_url ?? "";
    const timeLabel = formatTime(message?.created_at ?? "Just now");
    const pendingClass = options.pending ? "js-pending-message" : "";
    const bubbleTone = isOwnMessage ? "msg-sent" : "msg-received";
    const singleEmoji = isSingleEmojiMessage(rawContent);
    const displayName = isOwnMessage
        ? options.currentUserName ?? senderNameRaw ?? "You"
        : senderNameRaw;
    const displayPhotoUrl = isOwnMessage
        ? options.currentUserAvatarUrl ?? message?.sender?.profile_photo_url ?? ""
        : senderPhotoUrl;

    return `
        <div class="flex ${isOwnMessage ? "justify-end" : "justify-start"} animate-in fade-in slide-in-from-bottom-2 duration-300 ${pendingClass}">
            <div class="flex w-full max-w-[calc(100%-0.5rem)] items-end gap-3 md:max-w-[88%] lg:max-w-[82%] ${isOwnMessage ? "flex-row-reverse" : ""}">
                ${renderAvatarShell(displayName, displayPhotoUrl, {
                    size: "h-10 w-10",
                    rounded: "rounded-2xl",
                    text: "text-xs",
                    className: isOwnMessage
                        ? "shadow-lg shadow-orange-500/15"
                        : "border border-slate-100 shadow-sm",
                })}
                <div class="flex min-w-0 flex-col space-y-1.5 ${isOwnMessage ? "items-end" : "items-start"}">
                    <div class="msg-bubble ${bubbleTone} relative w-fit max-w-full rounded-[1.5rem] break-words [overflow-wrap:anywhere] transition-all
                        ${
                            isOwnMessage
                                ? "bg-[var(--brand)] text-white shadow-xl shadow-orange-500/20 border-b-r-none rounded-br-none"
                                : "bg-white text-[var(--ink)] border border-slate-100 shadow-sm rounded-bl-none"
                        }
                        ${
                            singleEmoji
                                ? "px-4 py-3 text-4xl leading-none"
                                : "px-5 py-3.5 text-sm font-medium leading-relaxed"
                        }">
                        ${content}
                    </div>

                    <div class="flex items-center gap-2 px-1">
                        <span class="text-[9px] font-black uppercase tracking-widest opacity-30">
                            ${isOwnMessage ? "You" : senderName}
                        </span>
                        <span class="h-1 w-1 rounded-full bg-slate-300 opacity-40"></span>
                        <span class="text-[9px] font-black uppercase tracking-widest opacity-30 italic">
                            ${timeLabel}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    `;
}

function renderMessages(messages) {
    const $container = $("#messages-container");
    const currentUserId = $container.data("current-user-id");
    const currentUserName = $container.data("current-user-name");
    const currentUserAvatarUrl = $container.data("current-user-avatar-url");

    if (!messages.length) {
        $container.html(`
            <div class="flex h-full items-center justify-center opacity-30 font-black uppercase text-xs tracking-widest">
                No messages yet
            </div>
        `);
        return;
    }

    $container.html(
        messages
            .map((message) =>
                buildMessageMarkup(message, currentUserId, {
                    currentUserName,
                    currentUserAvatarUrl,
                }),
            )
            .join(""),
    );

    scrollMessagesToBottom();
}

function setActiveConversation($trigger) {
    if ($trigger.data("is-active") === true) {
        return;
    }

    $(".js-conversation-trigger")
        .removeClass(
            "bg-[var(--surface-soft)] border-[var(--brand)]/10 shadow-lg",
        )
        .addClass("border-transparent")
        .data("is-active", false);

    $trigger
        .removeClass("border-transparent")
        .addClass("bg-[var(--surface-soft)] border-[var(--brand)]/10 shadow-lg")
        .data("is-active", true);

    $("#active-conversation-id").val($trigger.data("conversation-id"));
    $("#active-job-id").val($trigger.data("job-id"));
    $("#active-user-name").text($trigger.data("other-user-name"));
    $("#active-user-avatar").html(
        renderAvatarMarkup(
            $trigger.data("other-user-name"),
            $trigger.data("other-user-avatar-url"),
        ),
    );
    $("#active-job-title")
        .text($trigger.data("job-title"))
        .attr("href", $trigger.data("job-url") || "#");
}

function updateConversationPreview(conversationUuid, payload) {
    const $trigger = $(
        `.js-conversation-trigger[data-conversation-id="${conversationUuid}"]`,
    ).first();

    if (!$trigger.length) return;

    const previewTime =
        typeof payload?.created_at === "string" &&
        payload.created_at.includes("T")
            ? "Just now"
            : (payload?.created_at ?? "Just now");

    $trigger.find(".js-conversation-preview").text(payload?.message ?? "");
    $trigger.find(".js-conversation-time").text(previewTime);
    $trigger.prependTo($trigger.parent());
}

function updateConversationUnreadBadge(conversationUuid, nextCount) {
    const $trigger = $(
        `.js-conversation-trigger[data-conversation-id="${conversationUuid}"]`,
    ).first();

    if (!$trigger.length) return;

    const $badge = $trigger.find(".js-unread-message-badge");
    const safeCount = Math.max(0, Number(nextCount) || 0);

    if (safeCount <= 0) {
        $badge.addClass("hidden").text("0");
        return;
    }

    $badge.removeClass("hidden").text(safeCount > 9 ? "9+" : String(safeCount));
}

function incrementConversationUnreadBadge(conversationUuid) {
    const $trigger = $(
        `.js-conversation-trigger[data-conversation-id="${conversationUuid}"]`,
    ).first();

    if (!$trigger.length) return;

    const $badge = $trigger.find(".js-unread-message-badge");
    const currentText = ($badge.text() || "0").trim();
    const currentCount = currentText === "9+" ? 9 : Number(currentText) || 0;

    updateConversationUnreadBadge(conversationUuid, currentCount + 1);
}

function markConversationRead($trigger) {
    const readUrl = $trigger.data("read-url");

    if (!readUrl) return;

    $.post(readUrl)
        .done(function () {
            updateConversationUnreadBadge($trigger.data("conversation-id"), 0);
        })
        .fail(function () {
            // Read-state sync should not interrupt the chat flow.
        });
}

function appendRealtimeMessage(payload) {
    const $container = $("#messages-container");
    const currentConversationUuid = $("#active-conversation-id").val();

    if (!$container.length) return;
    if (!payload?.conversation_uuid) return;

    updateConversationPreview(payload.conversation_uuid, payload);

    if (payload.conversation_uuid !== currentConversationUuid) {
        incrementConversationUnreadBadge(payload.conversation_uuid);
        return;
    }

    const currentUserId = Number($container.data("current-user-id"));
    const currentUserName = $container.data("current-user-name");
    const currentUserAvatarUrl = $container.data("current-user-avatar-url");

    if (
        $container.find(".font-black.uppercase.tracking-widest").length === 1 &&
        $container.text().trim() === "No messages yet"
    ) {
        $container.empty();
    }

    updateConversationUnreadBadge(payload.conversation_uuid, 0);

    if (Number(payload.sender_id) === currentUserId) {
        const existing = $container.find(".js-pending-message").last();

        if (existing.length) {
            existing.replaceWith(
                buildMessageMarkup(payload, currentUserId, {
                    currentUserName,
                    currentUserAvatarUrl,
                }),
            );
            scrollMessagesToBottom(true);
            return;
        }
    }

    $container.append(
        buildMessageMarkup(payload, currentUserId, {
            currentUserName,
            currentUserAvatarUrl,
        }),
    );
    scrollMessagesToBottom(true);
}

function notificationTypeIcon(type) {
    switch (type) {
        case "message":
            return "message-circle";
        case "payment":
            return "badge-dollar-sign";
        case "job":
            return "briefcase-business";
        default:
            return "bell";
    }
}

function formatNotificationTime(value) {
    if (!value) return "Just now";
    if (typeof value === "string" && !value.includes("T")) return value;

    return new Date(value).toLocaleString([], {
        month: "short",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
}

function setNavNotificationBadge(count) {
    const $badge = $("#navNotificationBadge");
    const safeCount = Math.max(0, Number(count) || 0);

    if (!$badge.length) return;

    if (safeCount <= 0) {
        $badge.addClass("hidden").text("0");
        return;
    }

    $badge.removeClass("hidden").text(safeCount > 9 ? "9+" : String(safeCount));
}

function getCurrentNavNotificationCount() {
    const $badge = $("#navNotificationBadge");

    if (!$badge.length || $badge.hasClass("hidden")) return 0;

    const text = ($badge.text() || "0").trim();
    return text === "9+" ? 9 : Number(text) || 0;
}

function buildNotificationItem(notification) {
    return `
        <button type="button"
            class="js-nav-notification-item w-full rounded-2xl border border-slate-100 px-4 py-4 text-left transition hover:bg-slate-50 ${notification.is_read ? "bg-white" : "bg-orange-50/50"}"
            data-notification-id="${notification.id}"
            data-action-url="${escapeHtml(notification.action_url ?? "")}">
            <div class="flex items-start gap-3">
                <div class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-2xl bg-slate-900 text-white">
                    <i data-lucide="${notificationTypeIcon(notification.type)}" class="h-4 w-4"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <div class="flex items-start justify-between gap-3">
                        <p class="text-xs font-black text-slate-900">${escapeHtml(notification.title ?? "Notification")}</p>
                        ${notification.is_read ? "" : '<span class="js-notification-unread-dot mt-1 h-2 w-2 shrink-0 rounded-full bg-[var(--brand)]"></span>'}
                    </div>
                    <p class="mt-1 text-[11px] font-medium leading-relaxed text-slate-500">${escapeHtml(notification.message ?? "")}</p>
                    <p class="mt-2 text-[9px] font-black uppercase tracking-widest text-slate-300">${escapeHtml(formatNotificationTime(notification.created_at))}</p>
                    ${notification.action_url ? `<p class="mt-2 text-[9px] font-black uppercase tracking-widest text-[var(--brand)]">${escapeHtml(notification.action_label ?? "Open")}</p>` : ""}
                </div>
            </div>
        </button>
    `;
}

function renderNavbarNotifications(notifications = []) {
    const $list = $("#navNotificationsList");

    if (!$list.length) return;

    if (!notifications.length) {
        $list.html(`
            <div class="rounded-2xl border border-dashed border-slate-200 px-4 py-6 text-center">
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-300">No notifications yet</p>
            </div>
        `);
        return;
    }

    $list.html(notifications.map(buildNotificationItem).join(""));

    if (window.lucide?.createIcons) {
        window.lucide.createIcons({ icons: window.lucide.icons });
    }
}

export const initNavbarNotifications = function () {
    const config = window.asabaAppConfig || {};
    const $trigger = $("#notificationDropdownTrigger");
    const $menu = $("#notificationDropdownMenu");
    const $list = $("#navNotificationsList");
    const $overlay = $("#notificationDrawerOverlay");
    const $close = $("#notificationDrawerClose");

    if (
        !$trigger.length ||
        !$menu.length ||
        !config.notificationsUrl ||
        !config.notificationReadUrl ||
        !config.notificationReadAllUrl
    ) {
        return;
    }

    let hasLoaded = false;
    const isMobileDrawer = () => window.innerWidth < 768;

    function openMenu() {
        if (isMobileDrawer()) {
            $menu.removeClass("hidden translate-x-[105%]");
            $overlay.removeClass("hidden");
            return;
        }

        $menu.removeClass("hidden");
    }

    function closeMenu() {
        if (isMobileDrawer()) {
            $menu.addClass("translate-x-[105%]");
            $overlay.addClass("hidden");

            setTimeout(() => {
                if (isMobileDrawer()) {
                    $menu.addClass("hidden");
                }
            }, 300);

            return;
        }

        $menu.addClass("hidden");
    }

    function fetchNotifications() {
        $list.html(`
            <div class="rounded-2xl border border-dashed border-slate-200 px-4 py-6 text-center">
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-300 animate-pulse">Loading notifications</p>
            </div>
        `);

        $.get(config.notificationsUrl).done(function (response) {
            const notifications = response?.data ?? [];
            const unreadCount = notifications.filter(
                (notification) => !notification.is_read,
            ).length;

            renderNavbarNotifications(notifications);
            setNavNotificationBadge(unreadCount);
            hasLoaded = true;
        });
    }

    $trigger.on("click", function (e) {
        e.stopPropagation();
        if (isMobileDrawer()) {
            if ($menu.hasClass("hidden")) {
                openMenu();
            } else {
                closeMenu();
            }
        } else {
            $menu.toggleClass("hidden");
        }

        if (!$menu.hasClass("hidden") && !hasLoaded) {
            fetchNotifications();
        }
    });

    $close.on("click", closeMenu);
    $overlay.on("click", closeMenu);

    $(window).on("click", function (e) {
        if (
            !$trigger.is(e.target) &&
            $trigger.has(e.target).length === 0 &&
            !$menu.is(e.target) &&
            $menu.has(e.target).length === 0
        ) {
            closeMenu();
        }
    });

    $(window).on("resize", function () {
        if (!isMobileDrawer()) {
            $overlay.addClass("hidden");
            $menu.removeClass("translate-x-[105%]");
        }
    });

    $(document).on("click", ".js-nav-notification-item", function () {
        const $item = $(this);
        const notificationId = $item.data("notification-id");
        const actionUrl = $item.data("action-url");

        $.post(config.notificationReadUrl, {
            notification_id: notificationId,
        }).always(function () {
            $item.removeClass("bg-orange-50/50").addClass("bg-white");
            $item.find(".js-notification-unread-dot").remove();

            setNavNotificationBadge(getCurrentNavNotificationCount() - 1);

            if (actionUrl) {
                window.location.href = actionUrl;
            }
        });
    });

    $("#markAllNotificationsReadButton").on("click", function () {
        $.post(config.notificationReadAllUrl).always(function () {
            setNavNotificationBadge(0);
            $("#navNotificationsList .js-nav-notification-item").each(
                function () {
                    const $item = $(this);
                    $item.removeClass("bg-orange-50/50").addClass("bg-white");
                    $item.find(".js-notification-unread-dot").remove();
                },
            );
        });
    });

    if (window.Echo && config.currentUserId) {
        window.Echo.private(`user.${config.currentUserId}`).listen(
            ".notification.created",
            function (payload) {
                const newNotification = {
                    ...payload,
                    is_read: false,
                };

                const existingItems = $(
                    "#navNotificationsList .js-nav-notification-item",
                )
                    .map(function () {
                        return $(this).data("notification-id");
                    })
                    .get();

                if (existingItems.includes(newNotification.id)) {
                    return;
                }

                if (
                    $("#navNotificationsList .js-nav-notification-item")
                        .length === 0
                ) {
                    renderNavbarNotifications([newNotification]);
                } else {
                    $("#navNotificationsList").prepend(
                        buildNotificationItem(newNotification),
                    );

                    if (window.lucide?.createIcons) {
                        window.lucide.createIcons({
                            icons: window.lucide.icons,
                        });
                    }
                }

                setNavNotificationBadge(getCurrentNavNotificationCount() + 1);
            },
        );
    }
};

export const initNotificationsPage = function () {
    const $page = $("#notifications-page");

    if (!$page.length) return;

    const readUrl = $page.data("read-url");
    const readAllUrl = $page.data("read-all-url");
    const $count = $("#notifications-page-unread-count");
    const $feedback = $("#notifications-page-feedback");

    function currentUnreadCount() {
        return Number.parseInt($count.text(), 10) || 0;
    }

    function setUnreadCount(value) {
        $count.text(Math.max(0, value));
    }

    $(document).on("click", ".js-notification-page-read", function () {
        const $button = $(this);
        const notificationId = $button.data("notification-id");
        const $item = $button.closest(".js-notification-page-item");

        $.post(readUrl, {
            notification_id: notificationId,
        })
            .done(function (response) {
                showAlert(
                    response?.message || "Notification marked as read.",
                    "success",
                    "#notifications-page-feedback",
                );

                $item
                    .removeClass("border-orange-100 bg-orange-50/50")
                    .addClass("border-slate-100 bg-white");
                $item.find(".js-notification-page-read").remove();
                $item
                    .find("span")
                    .first()
                    .removeClass("bg-orange-100 text-orange-600")
                    .addClass("bg-slate-100 text-slate-500")
                    .text("Read");

                setUnreadCount(currentUnreadCount() - 1);
            })
            .fail(function (xhr) {
                showAlert(
                    xhr.responseJSON?.message ||
                        "Unable to update notification.",
                    "error",
                    "#notifications-page-feedback",
                );
            });
    });

    $("#notifications-page-mark-all").on("click", function () {
        $.post(readAllUrl)
            .done(function (response) {
                showAlert(
                    response?.message || "All notifications marked as read.",
                    "success",
                    "#notifications-page-feedback",
                );

                setUnreadCount(0);

                $(".js-notification-page-item").each(function () {
                    const $item = $(this);
                    $item
                        .removeClass("border-orange-100 bg-orange-50/50")
                        .addClass("border-slate-100 bg-white");
                    $item.find(".js-notification-page-read").remove();
                    $item
                        .find("span")
                        .first()
                        .removeClass("bg-orange-100 text-orange-600")
                        .addClass("bg-slate-100 text-slate-500")
                        .text("Read");
                });
            })
            .fail(function (xhr) {
                showAlert(
                    xhr.responseJSON?.message ||
                        "Unable to mark notifications as read.",
                    "error",
                    "#notifications-page-feedback",
                );
            });
    });
};

const subscribedConversationChannels = new Set();

function subscribeToConversation(conversationUuid) {
    if (!window.Echo || !conversationUuid) return;
    if (subscribedConversationChannels.has(conversationUuid)) return;

    subscribedConversationChannels.add(conversationUuid);

    window.Echo.private(`conversation.${conversationUuid}`).listen(
        ".chat.message.sent",
        function (payload) {
            appendRealtimeMessage(payload);
        },
    );
}

function loadChat(conversationUuid) {
    const $trigger = $(
        `.js-conversation-trigger[data-conversation-id="${conversationUuid}"]`,
    ).first();
    const messagesUrl = $trigger.data("messages-url");

    if (!$trigger.length || !messagesUrl) return;
    if ($trigger.data("is-active") === true) return;

    $("#chat-blank-state").addClass("hidden");
    setActiveConversation($trigger);
    subscribeToConversation(conversationUuid);

    $("#messages-container").html(`
        <div class="flex h-full items-center justify-center opacity-30 font-black uppercase text-xs tracking-widest animate-pulse">
            Fetching Messages...
        </div>
    `);

    $.get(messagesUrl)
        .done(function (response) {
            renderMessages(response?.data ?? []);
            markConversationRead($trigger);
        })
        .fail(function (xhr) {
            const message =
                xhr.responseJSON?.message || "Unable to load messages.";

            $("#messages-container").html(`
                <div class="flex h-full items-center justify-center text-center text-xs font-black uppercase tracking-widest text-red-500/80">
                    ${escapeHtml(message)}
                </div>
            `);
        });
}

export const initMessagesPage = function () {
    if (!$(".js-conversation-trigger").length) return;

    const $mobilePanel = $("#mobile-conversations-panel");
    const $mobileOverlay = $("#mobile-conversations-overlay");

    function openMobileConversationPanel() {
        if (!$mobilePanel.length || window.innerWidth >= 1024) return;

        $mobilePanel.removeClass("-translate-x-[105%]");
        $mobileOverlay.removeClass("hidden");
    }

    function closeMobileConversationPanel() {
        if (!$mobilePanel.length || window.innerWidth >= 1024) return;

        $mobilePanel.addClass("-translate-x-[105%]");
        $mobileOverlay.addClass("hidden");
    }

    window.loadChat = loadChat;

    $(".js-conversation-trigger").each(function () {
        subscribeToConversation($(this).data("conversation-id"));
    });

    $("#mobile-conversations-toggle, #chat-mobile-conversations-toggle").on(
        "click",
        function () {
            openMobileConversationPanel();
        },
    );

    $("#mobile-conversations-close, #mobile-conversations-overlay").on(
        "click",
        function () {
            closeMobileConversationPanel();
        },
    );

    $(".js-conversation-trigger").on("click", function () {
        loadChat($(this).data("conversation-id"));
        closeMobileConversationPanel();
    });

    $("#send-message-form").on("submit", function (e) {
        e.preventDefault();

        const $form = $(this);
        const $input = $("#message-input");
        const $button = $("#send-message-submit");
        const $container = $("#messages-container");
        const currentUserId = $container.data("current-user-id");
        const currentUserName = $container.data("current-user-name");
        const currentUserAvatarUrl = $container.data("current-user-avatar-url");
        const sendUrl = $form.data("send-url");
        const jobId = $("#active-job-id").val();
        const conversationUuid = $("#active-conversation-id").val();
        const content = ($input.val() ?? "").trim();

        if (!conversationUuid) {
            showAlert(
                "Select a conversation before sending a message.",
                "warning",
                "#send-message-form",
            );
            return;
        }

        if (!content) return;

        const optimisticMessage = {
            message: content,
            sender: {
                id: currentUserId,
                name: currentUserName || "You",
                profile_photo_url: currentUserAvatarUrl || "",
            },
            sender_id: currentUserId,
            conversation_uuid: conversationUuid,
            created_at: "Just now",
        };

        updateConversationPreview(conversationUuid, optimisticMessage);

        if (
            $container.find(".font-black.uppercase.tracking-widest").length ===
                1 &&
            $container.text().trim() === "No messages yet"
        ) {
            $container.empty();
        }

        const $optimisticNode = $(
            buildMessageMarkup(optimisticMessage, currentUserId, {
                pending: true,
                currentUserName,
                currentUserAvatarUrl,
            }),
        );

        $container.append($optimisticNode);
        $input.val("");
        scrollMessagesToBottom(true);
        $button.prop("disabled", true);

        $.ajax({
            url: sendUrl,
            method: "POST",
            data: {
                job_id: jobId,
                conversation_uuid: conversationUuid,
                message: content,
            },
        })
            .fail(function (xhr) {
                $optimisticNode.remove();
                $input.val(content).focus();

                const message =
                    xhr.responseJSON?.message || "Unable to send message.";
                showAlert(message, "error", "#send-message-form");
            })
            .always(function () {
                $button.prop("disabled", false);
            });
    });

    const params = new URLSearchParams(window.location.search);
    const conversationUuid = params.get("conversation");

    if (conversationUuid) {
        loadChat(conversationUuid);
        closeMobileConversationPanel();
    }
};

export const initJobDetailPage = function () {
    const $jobPage = $("#job-detail-page");

    window.copyTransferDetail = async function (value, label = "Detail") {
        if (!value) {
            showAlert(
                `No ${label.toLowerCase()} available to copy.`,
                "warning",
                "#assignedWorkerTransferFeedback",
            );
            return;
        }

        try {
            await navigator.clipboard.writeText(String(value));
            showAlert(
                `${label} copied successfully.`,
                "success",
                "#assignedWorkerTransferFeedback",
            );
        } catch (_) {
            showAlert(
                `Unable to copy ${label.toLowerCase()} on this device.`,
                "error",
                "#assignedWorkerTransferFeedback",
            );
        }
    };

    window.openNegotiationDecisionModal = function (config = {}) {
        const action = String(config.action || "").toLowerCase();
        const isReject = action === "reject";
        const heading = isReject ? "Reject This Offer?" : "Accept This Offer?";
        const body = isReject
            ? `Rejecting this offer will send back your preferred amount${config.workerName ? ` to ${config.workerName}` : ""}.`
            : "Accepting this negotiation will assign the worker to this job and move the job into the assigned stage.";

        $("#negotiationDecisionForm").attr("action", config.url || "");
        $("#negotiationDecisionHeading").text(heading);
        $("#negotiationDecisionText").text(body);
        $("#negotiationDecisionSubmitText").text(
            isReject ? "Reject Offer" : "Accept Offer",
        );
        $("#negotiationDecisionSubmit")
            .toggleClass("bg-rose-600 hover:bg-rose-700 shadow-rose-500/20", isReject)
            .toggleClass("bg-emerald-600 hover:bg-emerald-700 shadow-emerald-500/20", !isReject);
        $("#negotiationDecisionIcon")
            .toggleClass("bg-rose-50 text-rose-600", isReject)
            .toggleClass("bg-emerald-50 text-emerald-600", !isReject)
            .html(
                isReject
                    ? '<i data-lucide="octagon-x" class="h-8 w-8"></i>'
                    : '<i data-lucide="handshake" class="h-8 w-8"></i>',
            );
        $("#negotiationDecisionSubmitIcon").attr(
            "data-lucide",
            isReject ? "x-circle" : "check-circle",
        );
        $("#negotiationDecisionFields").toggleClass("hidden", !isReject);
        $("#negotiation_reject_amount")
            .val(isReject ? config.amount || "" : "")
            .prop("disabled", !isReject);
        $("#negotiation_reject_message")
            .val("")
            .prop("disabled", !isReject);

        openModal("negotiationDecisionModal");

        if (window.lucide?.createIcons) {
            window.lucide.createIcons({ icons: window.lucide.icons });
        }
    };

    if ($("#job-apply-form").length) {
        handleAjaxForm(
            "#job-apply-form",
            "#job-apply-submit",
            function (response) {
                if (response.success === true) {
                    setTimeout(() => {
                        window.location.reload();
                    }, 1200);
                }
            },
        );
    }

    if ($("#job-chat-starter-form").length) {
        handleAjaxForm(
            "#job-chat-starter-form",
            "#job-chat-starter-submit",
            function (response) {
                const conversationUuid = response?.data?.conversation_uuid;

                setTimeout(() => {
                    if (conversationUuid) {
                        window.location.href = `/app/conversations?conversation=${conversationUuid}`;
                        return;
                    }

                    window.location.href = "/app/conversations";
                }, 900);
            },
        );
    }

    if ($("#job-rate-form").length) {
        handleAjaxForm("#job-rate-form", "#job-rate-submit", function () {
            setTimeout(() => {
                window.location.reload();
            }, 1200);
        });
    }

    $("#negotiationDecisionForm").on("submit", function (e) {
        e.preventDefault();

        const $form = $(this);
        const $button = $("#negotiationDecisionSubmit");
        const $text = $("#negotiationDecisionSubmitText");
        const originalText = $text.text();

        if (!$form.attr("action")) {
            return;
        }

        $button.prop("disabled", true);
        $text.text("Processing...");

        $.ajax({
            url: $form.attr("action"),
            method: "POST",
            data: $form.serialize(),
        })
            .done(function (response) {
                showAlert(
                    response.message || "Negotiation updated successfully.",
                    "success",
                    "#negotiationDecisionForm",
                );

                setTimeout(function () {
                    window.location.reload();
                }, 1000);
            })
            .fail(function (xhr) {
                showAlert(
                    xhr.responseJSON?.message ||
                        "Unable to update the negotiation.",
                    "error",
                    "#negotiationDecisionForm",
                );
                $button.prop("disabled", false);
                $text.text(originalText);
            });
    });

    $(".job-status-form").on("submit", function (e) {
        e.preventDefault();

        const $form = $(this);
        const $button = $form.find("button[type='submit']");
        const $buttonText = $button.find(".button-text");
        const originalText = $buttonText.text();
        const loadingText = $form.data("loading-text") || "Updating...";
        const feedbackTarget =
            $form.data("success-target") || "#job-lifecycle-feedback";

        $button.prop("disabled", true);
        $buttonText.text(loadingText);

        $.ajax({
            url: $form.attr("action"),
            method: "POST",
            data: $form.serialize(),
        })
            .done(function (response) {
                showAlert(
                    response.message || "Job status updated.",
                    "success",
                    feedbackTarget,
                );

                setTimeout(function () {
                    window.location.reload();
                }, 1200);
            })
            .fail(function (xhr) {
                showAlert(
                    xhr.responseJSON?.message || "Unable to update job status.",
                    "error",
                    feedbackTarget,
                );

                $button.prop("disabled", false);
                $buttonText.text(originalText);
            });
    });

    const jobId = $jobPage.data("job-id");
    const initialStatus = String($jobPage.data("job-status") || "");

    if (window.Echo && jobId) {
        window.Echo.private(`job.${jobId}`).listen(
            ".job.status.updated",
            function (payload) {
                if (!payload) return;

                const nextStatus = String(payload.status || "");
                const hasRating = Boolean(payload.has_rating);

                if (nextStatus === initialStatus && !hasRating) {
                    return;
                }

                window.location.reload();
            },
        );
        window.Echo.private(`job.${jobId}`).listen(
            ".negotiation.updated",
            function (e) {
                showAlert("Negotiation updated", "info");

                // Reload or update UI dynamically
                window.location.reload();
            },
        );
    }
};
