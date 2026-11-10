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

function listen(toastStack) {
    const header = document.querySelector('[data-compass-channel]');

    if (window.Echo === undefined || header === null) {
        return;
    }

    window.Echo
        .private(header.getAttribute('data-compass-channel'))
        .listenToAll((eventName, eventData) => {
            if (eventData.toastHtml) {
                showToast(toastStack, eventData.toastHtml);
            }
        });
}

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
