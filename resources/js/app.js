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

const initFilterUnitPicker = (wrapper) => {
    const trigger = wrapper.querySelector('[data-filter-unit-trigger]');
    const menu = wrapper.querySelector('[data-filter-unit-menu]');
    const label = wrapper.querySelector('[data-filter-unit-label]');
    const input = wrapper.querySelector('[data-filter-unit-input]');
    const options = wrapper.querySelectorAll('[data-filter-unit-option]');
    const form = wrapper.closest('form');

    if (!trigger || !menu || !label || !input || !form) {
        return;
    }

    const flyouts = {};
    document.querySelectorAll('[data-filter-flyout]').forEach((flyout) => {
        const key = flyout.dataset.filterFlyout;
        if (!key) {
            return;
        }
        flyouts[key] = flyout;
        if (flyout.parentNode !== document.body) {
            document.body.appendChild(flyout);
        }
    });

    const setLabel = (value, text) => {
        label.textContent = text;
        label.style.color = value ? '#111827' : '#6b7280';
    };

    const submitForm = () => {
        if (typeof form.requestSubmit === 'function') {
            form.requestSubmit();
        } else {
            form.dispatchEvent(new Event('submit', { bubbles: true, cancelable: true }));
        }
    };

    const closeMenu = () => {
        menu.classList.add('is-hidden');
        trigger.setAttribute('aria-expanded', 'false');
        Object.values(flyouts).forEach((flyout) => {
            flyout.style.display = 'none';
        });
    };

    const openMenu = () => {
        menu.classList.remove('is-hidden');
        trigger.setAttribute('aria-expanded', 'true');
    };

    const toggleMenu = () => {
        if (menu.classList.contains('is-hidden')) {
            openMenu();
            return;
        }
        closeMenu();
    };

    const showFlyout = (key, anchor) => {
        const flyout = flyouts[key];
        if (!flyout || !anchor) {
            return;
        }
        const rect = anchor.getBoundingClientRect();
        flyout.style.top = `${rect.top}px`;
        flyout.style.left = `${rect.right + 6}px`;
        flyout.style.display = 'block';
    };

    const hideFlyout = (key) => {
        const flyout = flyouts[key];
        if (flyout) {
            flyout.style.display = 'none';
        }
    };

    const hideAllFlyouts = () => {
        Object.keys(flyouts).forEach(hideFlyout);
    };

    let flyoutTimers = {};

    trigger.addEventListener('click', (event) => {
        event.preventDefault();
        event.stopPropagation();
        toggleMenu();
    });

    options.forEach((option) => {
        const flyoutKey = option.dataset.hasFlyout;

        option.addEventListener('mouseenter', () => {
            option.style.background = '#f3f4f6';
            if (flyoutKey) {
                const otherKey = flyoutKey === 'pau' ? 'bgcu' : 'pau';
                hideFlyout(otherKey);
                clearTimeout(flyoutTimers[flyoutKey]);
                showFlyout(flyoutKey, option);
            } else {
                hideAllFlyouts();
            }
        });

        option.addEventListener('mouseleave', () => {
            option.style.background = '';
            if (flyoutKey) {
                flyoutTimers[flyoutKey] = setTimeout(() => hideFlyout(flyoutKey), 120);
            }
        });

        option.addEventListener('click', (event) => {
            event.preventDefault();
            const nextValue = option.dataset.unitId ?? '';
            const nextLabel = option.dataset.unitName ?? option.textContent.trim();

            input.value = nextValue;
            setLabel(nextValue, nextLabel);
            closeMenu();
            submitForm();
        });
    });

    Object.values(flyouts).forEach((flyout) => {
        flyout.querySelectorAll('[data-filter-flyout-item]').forEach((item) => {
            item.addEventListener('mouseenter', () => {
                item.style.background = '#eff6ff';
            });
            item.addEventListener('mouseleave', () => {
                item.style.background = '';
            });
            item.addEventListener('click', () => {
                const nextValue = item.dataset.unitId ?? '';
                const nextLabel = item.dataset.unitName ?? item.textContent.trim();

                input.value = nextValue;
                setLabel(nextValue, nextLabel);
                closeMenu();
                submitForm();
            });
        });
    });

    Object.entries(flyouts).forEach(([key, flyout]) => {
        flyout.addEventListener('mouseenter', () => clearTimeout(flyoutTimers[key]));
        flyout.addEventListener('mouseleave', () => hideFlyout(key));
    });

    document.addEventListener('click', (event) => {
        if (!wrapper.contains(event.target) && !Object.values(flyouts).some((flyout) => flyout.contains(event.target))) {
            closeMenu();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeMenu();
        }
    });
};

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('form[data-live-search-form]').forEach(initLiveSearchForm);
    document.querySelectorAll('[data-filter-unit-picker]').forEach(initFilterUnitPicker);
});
