const showFeedback = (selector, type, message, details = []) => {
    const node = $(selector);

    if (!node.length) {
        return;
    }

    const detailMarkup = details.length
        ? `<ul class="mt-2 list-disc space-y-1 pl-5">${details.map((detail) => `<li>${detail}</li>`).join("")}</ul>`
        : "";

    const styles =
        type === "success"
            ? "border-emerald-200 bg-emerald-50 text-emerald-800"
            : "border-red-200 bg-red-50 text-red-800";

    node.removeClass(
        "hidden border-emerald-200 bg-emerald-50 text-emerald-800 border-red-200 bg-red-50 text-red-800",
    )
        .addClass(styles)
        .html(`<p>${message}</p>${detailMarkup}`);
};

const collectErrors = (errors = {}) =>
    Object.values(errors).flat().filter(Boolean);

const prettyJson = (value) => JSON.stringify(value, null, 2);

const renderJobs = (jobs = []) => {
    if (!jobs.length) {
        return '<div class="rounded-3xl border border-dashed border-black/10 bg-[var(--surface-soft)] p-6 text-sm text-black/60">No jobs matched the current filter.</div>';
    }

    return jobs
        .map(
            (job) => `
        <article class="rounded-3xl border border-black/6 bg-[var(--surface-soft)] p-5">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[var(--brand)]">${job.skill?.name ?? "General skill"}</p>
                    <h3 class="mt-2 text-xl font-bold text-[var(--ink)]">#${job.id} ${job.title}</h3>
                </div>
                <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-black/60">${String(job.status).replaceAll("_", " ")}</span>
            </div>
            <p class="mt-3 text-sm leading-7 text-black/65">${job.description}</p>
            <div class="mt-4 flex flex-wrap gap-3 text-sm text-black/60">
                <span class="rounded-full bg-white px-3 py-1">Budget: ₦${job.budget}</span>
                <span class="rounded-full bg-white px-3 py-1">${job.location}</span>
                <span class="rounded-full bg-white px-3 py-1">${job.payment_method}</span>
            </div>
        </article>
    `,
        )
        .join("");
};

const renderCompactJobs = (jobs = []) => {
    if (!jobs.length) {
        return '<div class="rounded-3xl border border-dashed border-black/10 bg-[var(--surface-soft)] p-4 text-sm text-black/60">No jobs available.</div>';
    }

    return jobs
        .map(
            (job) => `
        <div class="rounded-3xl border border-black/6 bg-[var(--surface-soft)] p-4 text-sm">
            <div class="font-semibold text-[var(--ink)]">#${job.id} ${job.title}</div>
            <div class="mt-1 text-black/60">${job.status}</div>
        </div>
    `,
        )
        .join("");
};

const renderConversations = (conversations = []) => {
    if (!conversations.length) {
        return '<div class="rounded-3xl border border-dashed border-black/10 bg-[var(--surface-soft)] p-4 text-sm text-black/60">No conversations yet.</div>';
    }

    return conversations
        .map(
            (conversation) => `
        <div class="rounded-3xl border border-black/6 bg-[var(--surface-soft)] p-4 text-sm">
            <div class="font-semibold text-[var(--ink)]">Conversation #${conversation.id}</div>
            <div class="mt-1 text-black/60">Job: ${conversation.job?.title ?? "Unknown job"}</div>
            <div class="mt-1 text-black/60">Latest: ${conversation.latest_message?.message ?? "No messages yet"}</div>
        </div>
    `,
        )
        .join("");
};

const renderMessages = (messages = []) => {
    if (!messages.length) {
        return '<div class="rounded-3xl border border-dashed border-black/10 bg-[var(--surface-soft)] p-4 text-sm text-black/60">No messages found.</div>';
    }

    return messages
        .map(
            (message) => `
        <div class="rounded-3xl border border-black/6 bg-[var(--surface-soft)] p-4 text-sm">
            <div class="font-semibold text-[var(--ink)]">${message.sender?.name ?? "Unknown sender"}</div>
            <div class="mt-1 text-black/70">${message.message}</div>
            <div class="mt-1 text-black/50">Read: ${message.is_read ? "yes" : "no"}</div>
        </div>
    `,
        )
        .join("");
};

const renderNotifications = (notifications = []) => {
    if (!notifications.length) {
        return '<div class="rounded-3xl border border-dashed border-black/10 bg-[var(--surface-soft)] p-4 text-sm text-black/60">No notifications found.</div>';
    }

    return notifications
        .map(
            (notification) => `
        <div class="rounded-3xl border border-black/6 bg-[var(--surface-soft)] p-4 text-sm">
            <div class="font-semibold text-[var(--ink)]">#${notification.id} ${notification.title}</div>
            <div class="mt-1 text-black/70">${notification.message}</div>
            <div class="mt-1 text-black/50">${notification.type} · ${notification.is_read ? "read" : "unread"}</div>
        </div>
    `,
        )
        .join("");
};

const request = ({
    url,
    method = "GET",
    data = null,
    feedback = null,
    onSuccess = null,
    onError = null,
    processData = true,
    contentType = "application/x-www-form-urlencoded; charset=UTF-8",
}) => {
    $.ajax({
        url,
        method,
        data,
        processData,
        contentType,
    })
        .done((response) => {
            const metaDetails = [];

            if (response.meta?.debug_token) {
                metaDetails.push(`Debug token: ${response.meta.debug_token}`);
            }

            if (response.meta?.debug_link) {
                metaDetails.push(`Debug link: ${response.meta.debug_link}`);
            }

            if (feedback) {
                showFeedback(
                    feedback,
                    "success",
                    response.message,
                    metaDetails,
                );
            }

            onSuccess?.(response);
        })
        .fail((xhr) => {
            const response = xhr.responseJSON ?? {};

            if (feedback) {
                showFeedback(
                    feedback,
                    "error",
                    response.message ?? "Request failed.",
                    collectErrors(response.errors),
                );
            }

            onError?.(response);
        });
};

const submitAuthForm = () => {
    const config = window.asabaAuthConfig;

    if (!config) {
        return;
    }

    const form = $(config.formId);

    form.on("submit", function (event) {
        event.preventDefault();

        request({
            url: config.action,
            method: "POST",
            data: form.serialize(),
            feedback: config.feedbackId,
            onSuccess: (response) => {
                const redirect =
                    response.data?.redirect ?? config.redirectFallback;
                if (redirect) {
                    window.setTimeout(() => {
                        window.location.assign(redirect);
                    }, 900);
                }
            },
        });
    });
};

const bootAppScreen = () => {
    const config = window.asabaAppConfig;

    if (!config) {
        return;
    }

    const loadMe = () => {
        request({
            url: config.meUrl,
            feedback: "#me-feedback",
            onSuccess: (response) => {
                $("#me-panel").text(prettyJson(response.data));
            },
        });
    };

    const loadJobs = () => {
        request({
            url: config.jobsUrl,
            data: $("#job-filter-form").serialize(),
            feedback: "#jobs-feedback",
            onSuccess: (response) => {
                $("#jobs-list").html(renderJobs(response.data ?? []));
            },
        });
    };

    const loadMyJobs = () => {
        request({
            url: config.myJobsUrl,
            feedback: "#my-jobs-feedback",
            onSuccess: (response) => {
                $("#my-jobs-list").html(renderCompactJobs(response.data ?? []));
            },
        });
    };

    const loadConversations = () => {
        request({
            url: config.conversationsUrl,
            feedback: "#conversations-feedback",
            onSuccess: (response) => {
                $("#conversations-list").html(
                    renderConversations(response.data ?? []),
                );
            },
        });
    };

    const loadNotifications = () => {
        request({
            url: config.notificationsUrl,
            feedback: "#notifications-feedback",
            onSuccess: (response) => {
                $("#notifications-list").html(
                    renderNotifications(response.data ?? []),
                );
            },
        });
    };

    $("#refresh-me").on("click", loadMe);
    $("#job-filter-form").on("submit", function (event) {
        event.preventDefault();
        loadJobs();
    });
    $("#load-conversations").on("click", loadConversations);
    $("#load-notifications").on("click", loadNotifications);

    $("#profile-form").on("submit", function (event) {
        event.preventDefault();
        request({
            url: config.profileUpdateUrl,
            method: "POST",
            data: `${$(this).serialize()}&_method=PUT`,
            feedback: "#profile-feedback",
            onSuccess: loadMe,
        });
    });

    $("#availability-form").on("submit", function (event) {
        event.preventDefault();
        request({
            url: config.availabilityUrl,
            method: "POST",
            data: $(this).serialize(),
            feedback: "#availability-feedback",
            onSuccess: loadMe,
        });
    });

    $("#password-form").on("submit", function (event) {
        event.preventDefault();
        request({
            url: config.changePasswordUrl,
            method: "POST",
            data: $(this).serialize(),
            feedback: "#password-feedback",
        });
    });

    $("#upload-id-form").on("submit", function (event) {
        event.preventDefault();
        const formData = new FormData(this);

        request({
            url: config.uploadIdUrl,
            method: "POST",
            data: formData,
            processData: false,
            contentType: false,
            feedback: "#upload-feedback",
        });
    });

    $("#verification-send-form").on("submit", function (event) {
        event.preventDefault();
        request({
            url: config.sendVerificationUrl,
            method: "POST",
            data: $(this).serialize(),
            feedback: "#verification-send-feedback",
        });
    });

    $("#verification-check-form").on("submit", function (event) {
        event.preventDefault();
        request({
            url: config.verifyContactUrl,
            method: "POST",
            data: $(this).serialize(),
            feedback: "#verification-check-feedback",
            onSuccess: loadMe,
        });
    });

    $("#job-create-form").on("submit", function (event) {
        event.preventDefault();
        request({
            url: config.createJobUrl,
            method: "POST",
            data: $(this).serialize(),
            feedback: "#job-create-feedback",
            onSuccess: () => {
                this.reset();
                loadJobs();
                loadMyJobs();
            },
        });
    });

    $("[data-job-action]").on("click", function () {
        const action = $(this).data("job-action");
        const jobId = $('#job-action-form [name="job_id"]').val();
        const workerId = $('#job-action-form [name="worker_id"]').val();
        const message = $('#job-action-form [name="message"]').val();

        if (!jobId) {
            showFeedback(
                "#job-action-feedback",
                "error",
                "Job ID is required.",
            );
            return;
        }

        const routes = {
            apply: {
                method: "POST",
                url: `${config.jobShowBase}/${jobId}/apply`,
                data: { message },
            },
            hire: {
                method: "POST",
                url: `${config.jobShowBase}/${jobId}/hire`,
                data: { worker_id: workerId },
            },
            accept: {
                method: "POST",
                url: `${config.jobShowBase}/${jobId}/accept`,
                data: {},
            },
            start: {
                method: "POST",
                url: `${config.jobShowBase}/${jobId}/start`,
                data: {},
            },
            complete: {
                method: "POST",
                url: `${config.jobShowBase}/${jobId}/complete`,
                data: {},
            },
        };

        request({
            ...routes[action],
            feedback: "#job-action-feedback",
            onSuccess: () => {
                loadJobs();
                loadMyJobs();
            },
        });
    });

    $("#load-suggested-workers").on("click", function () {
        const jobId = $('#job-action-form [name="job_id"]').val();
        if (!jobId) {
            showFeedback(
                "#job-action-feedback",
                "error",
                "Job ID is required.",
            );
            return;
        }

        request({
            url: `${config.jobShowBase}/${jobId}/suggested-workers`,
            feedback: "#job-action-feedback",
            onSuccess: (response) => {
                showFeedback(
                    "#job-action-feedback",
                    "success",
                    response.message,
                    [prettyJson(response.data ?? [])],
                );
            },
        });
    });

    $("#job-rate-form").on("submit", function (event) {
        event.preventDefault();
        const jobId = $('#job-rate-form [name="job_id"]').val();

        request({
            url: `${config.jobShowBase}/${jobId}/rate`,
            method: "POST",
            data: $(this).serialize().replace(`job_id=${jobId}`, ""),
            feedback: "#job-rate-feedback",
            onSuccess: () => {
                loadJobs();
                loadMyJobs();
            },
        });
    });

    $("#conversation-messages-form").on("submit", function (event) {
        event.preventDefault();
        const conversationId = $(
            '#conversation-messages-form [name="conversation_id"]',
        ).val();

        request({
            url: `${config.conversationsUrl}/${conversationId}/messages`,
            feedback: "#messages-feedback",
            onSuccess: (response) => {
                $("#messages-list").html(renderMessages(response.data ?? []));
            },
        });
    });

    $("#mark-read-button").on("click", function () {
        const conversationId = $(
            '#conversation-messages-form [name="conversation_id"]',
        ).val();

        request({
            url: `${config.conversationsUrl}/${conversationId}/read`,
            method: "POST",
            feedback: "#messages-feedback",
        });
    });

    $("#send-message-form").on("submit", function (event) {
        event.preventDefault();
        request({
            url: `${config.conversationsUrl.replace("/conversations", "/messages")}`,
            method: "POST",
            data: $(this).serialize(),
            feedback: "#send-message-feedback",
            onSuccess: () => {
                this.reset();
                loadConversations();
            },
        });
    });

    $("#notification-read-form").on("submit", function (event) {
        event.preventDefault();
        request({
            url: config.notificationReadUrl,
            method: "POST",
            data: $(this).serialize(),
            feedback: "#notifications-feedback",
            onSuccess: loadNotifications,
        });
    });

    $("#mark-all-notifications").on("click", function () {
        request({
            url: config.notificationReadAllUrl,
            method: "POST",
            feedback: "#notifications-feedback",
            onSuccess: loadNotifications,
        });
    });

    $("#paystack-init-form").on("submit", function (event) {
        event.preventDefault();
        request({
            url: config.paystackInitUrl,
            method: "POST",
            data: $(this).serialize(),
            feedback: "#paystack-init-feedback",
            onSuccess: (response) => {
                const details = [];
                if (response.data?.reference)
                    details.push(`Reference: ${response.data.reference}`);
                if (response.data?.authorization_url)
                    details.push(
                        `Authorization URL: ${response.data.authorization_url}`,
                    );
                showFeedback(
                    "#paystack-init-feedback",
                    "success",
                    response.message,
                    details,
                );
            },
        });
    });

    $("#paystack-verify-form").on("submit", function (event) {
        event.preventDefault();
        request({
            url: config.paystackVerifyUrl,
            method: "POST",
            data: $(this).serialize(),
            feedback: "#paystack-verify-feedback",
            onSuccess: (response) => {
                showFeedback(
                    "#paystack-verify-feedback",
                    "success",
                    response.message,
                    [prettyJson(response.data)],
                );
            },
        });
    });

    $("#flutterwave-init-form").on("submit", function (event) {
        event.preventDefault();
        request({
            url: config.flutterwaveInitUrl,
            method: "POST",
            data: $(this).serialize(),
            feedback: "#flutterwave-init-feedback",
            onSuccess: (response) => {
                const details = [];
                if (response.data?.reference)
                    details.push(`Reference: ${response.data.reference}`);
                if (response.data?.payment_link)
                    details.push(`Payment Link: ${response.data.payment_link}`);
                showFeedback(
                    "#flutterwave-init-feedback",
                    "success",
                    response.message,
                    details,
                );
            },
        });
    });

    $("#flutterwave-verify-form").on("submit", function (event) {
        event.preventDefault();
        request({
            url: config.flutterwaveVerifyUrl,
            method: "POST",
            data: $(this).serialize(),
            feedback: "#flutterwave-verify-feedback",
            onSuccess: (response) => {
                showFeedback(
                    "#flutterwave-verify-feedback",
                    "success",
                    response.message,
                    [prettyJson(response.data)],
                );
            },
        });
    });

    loadMe();
    loadJobs();
    loadMyJobs();
    loadNotifications();
};

const bootLogout = () => {
    if (!window.asabaLogoutUrl || !$("#logout-button").length) {
        return;
    }

    $("#logout-button").on("click", function () {
        $.post(window.asabaLogoutUrl).done((response) => {
            window.location.assign(
                response.data?.redirect ?? window.asabaLoginUrl ?? "/login",
            );
        });
    });
};

createIcons({ icons });
AOS.init();

$(function () {
    submitAuthForm();
    bootAppScreen();
    bootLogout();
});
