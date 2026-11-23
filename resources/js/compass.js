const relativeTimeRefreshInMilliseconds = 60000;
const secondsPerRelativeTimeUnit = [
    ['year', 31536000],
    ['month', 2592000],
    ['week', 604800],
    ['day', 86400],
    ['hour', 3600],
    ['minute', 60],
    ['second', 1],
];
const toastLifetimeInMilliseconds = 5000;
const toastLimit = 5;

function closeLater(toast) {
    if (toast.hasAttribute('data-compass-toast-stays-open')) {
        return;
    }

    setTimeout(() => toast.remove(), toastLifetimeInMilliseconds);
}

function toastFromHtml(toastHtml) {
    const template = document.createElement('template');
    template.innerHTML = toastHtml.trim();

    return template.content.firstElementChild;
}

function showToast(toastStack, toastHtml) {
    const toast = toastFromHtml(toastHtml);
    const toastId = toast.getAttribute('data-compass-toast-id');
    const shownToast = toastId === null
        ? null
        : toastStack.querySelector(`[data-compass-toast-id="${toastId}"]`);

    if (shownToast === null) {
        toastStack.prepend(toast);
    } else {
        shownToast.replaceWith(toast);
    }

    while (toastStack.children.length > toastLimit) {
        toastStack.lastElementChild.remove();
    }

    closeLater(toast);
}

function relativeTime(dateTime, relativeTimeFormat) {
    const secondsFromNow = Math.round((dateTime.getTime() - Date.now()) / 1000);

    for (const [unit, secondsPerUnit] of secondsPerRelativeTimeUnit) {
        if (Math.abs(secondsFromNow) >= secondsPerUnit) {
            return relativeTimeFormat.format(Math.trunc(secondsFromNow / secondsPerUnit), unit);
        }
    }

    return relativeTimeFormat.format(0, 'second');
}

function refreshRelativeTimes() {
    const relativeTimeFormat = new Intl.RelativeTimeFormat(document.documentElement.lang, { numeric: 'always' });

    document.querySelectorAll('[data-compass-relative-time]').forEach((time) => {
        time.textContent = relativeTime(new Date(time.getAttribute('datetime')), relativeTimeFormat);
    });
}

let isRefreshing = false;
let isRefreshPending = false;

function keepCollapsibleToggles(liveRegion, refreshedLiveRegion) {
    const refreshedToggles = refreshedLiveRegion.querySelectorAll('[data-compass-collapsible-toggle]');

    liveRegion.querySelectorAll('[data-compass-collapsible-toggle]').forEach((toggle, index) => {
        if (refreshedToggles[index] !== undefined) {
            refreshedToggles[index].checked = toggle.checked;
        }
    });
}

async function refreshLiveRegions() {
    if (isRefreshing) {
        isRefreshPending = true;

        return;
    }

    isRefreshing = true;

    try {
        const response = await fetch(window.location.href, { headers: { Accept: 'text/html' } });
        const refreshedDocument = new DOMParser().parseFromString(await response.text(), 'text/html');

        document.querySelectorAll('[data-compass-live-region]').forEach((liveRegion) => {
            const liveRegionName = liveRegion.getAttribute('data-compass-live-region');
            const refreshedLiveRegion = refreshedDocument.querySelector(`[data-compass-live-region="${liveRegionName}"]`);

            if (refreshedLiveRegion !== null) {
                keepCollapsibleToggles(liveRegion, refreshedLiveRegion);
                liveRegion.replaceWith(refreshedLiveRegion);
            }
        });

        refreshRelativeTimes();
    } finally {
        isRefreshing = false;
    }

    if (isRefreshPending) {
        isRefreshPending = false;

        await refreshLiveRegions();
    }
}

async function sendWithoutRefresh(form) {
    const response = await fetch(form.action, {
        method: form.method,
        body: new FormData(form),
        headers: { Accept: 'application/json' },
    });

    if (!response.ok) {
        form.submit();

        return;
    }

    await refreshLiveRegions();
}

function listen(toastStack) {
    const header = document.querySelector('[data-compass-channel]');

    if (window.Echo === undefined || header === null) {
        return;
    }

    document.addEventListener('submit', (event) => {
        const form = event.target.closest('[data-compass-sends-without-refresh]');

        if (form !== null) {
            event.preventDefault();

            sendWithoutRefresh(form);
        }
    });

    window.Echo
        .private(header.getAttribute('data-compass-channel'))
        .listenToAll((eventName, eventData) => {
            if (eventData.toastHtml) {
                showToast(toastStack, eventData.toastHtml);
            }

            refreshLiveRegions();
        });
}

refreshRelativeTimes();
setInterval(refreshRelativeTimes, relativeTimeRefreshInMilliseconds);

document.addEventListener('click', (event) => {
    const closeButton = event.target.closest('[data-compass-toast-close]');

    if (closeButton !== null) {
        closeButton.closest('[data-compass-toast]').remove();
    }
});

const toastStack = document.querySelector('[data-compass-toast-stack]');

if (toastStack !== null) {
    toastStack.querySelectorAll('[data-compass-toast]').forEach(closeLater);

    listen(toastStack);
}
