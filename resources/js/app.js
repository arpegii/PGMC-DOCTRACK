import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

const LIVE_SEARCH_DELAY_MS = 280;

const buildUrlWithFormParams = (form) => {
    const url = new URL(form.action, window.location.origin);
    const params = new URLSearchParams();

    for (const [key, value] of new FormData(form).entries()) {
        const normalized = String(value).trim();
        if (normalized !== '') {
            params.set(key, normalized);
        }
    }

    url.search = params.toString();
    return url;
};

const replaceTableShell = (htmlText) => {
    const parser = new DOMParser();
    const nextDoc = parser.parseFromString(htmlText, 'text/html');
    const nextShell = nextDoc.querySelector('.table-shell');
    const currentShell = document.querySelector('.table-shell');

    if (!nextShell || !currentShell) {
        return false;
    }

    currentShell.replaceWith(nextShell);
    return true;
};

const initLiveSearchForm = (form) => {
    const searchInput = form.querySelector('input[name="search"]');
    if (!searchInput) {
        return;
    }

    let debounceTimer = null;
    let activeRequest = null;

    const runSearch = () => {
        const targetUrl = buildUrlWithFormParams(form);

        if (activeRequest) {
            activeRequest.abort();
        }

        activeRequest = new AbortController();

        fetch(targetUrl.toString(), {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
            signal: activeRequest.signal,
        })
            .then((response) => response.text())
            .then((html) => {
                const replaced = replaceTableShell(html);
                if (!replaced) {
                    return;
                }

                const qs = targetUrl.search ? targetUrl.search : '';
                window.history.replaceState({}, '', `${targetUrl.pathname}${qs}`);
            })
            .catch((error) => {
                if (error.name !== 'AbortError') {
                    // Fallback path is full page submission.
                    form.submit();
                }
            });
    };

    form.addEventListener('submit', (event) => {
        event.preventDefault();
        runSearch();
    });

    searchInput.addEventListener('input', () => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(runSearch, LIVE_SEARCH_DELAY_MS);
    });
};

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('form[data-live-search-form]').forEach(initLiveSearchForm);
});
