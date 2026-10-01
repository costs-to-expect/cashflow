/*
 * Reporting period switcher. Inside a [data-period-switcher] container,
 * clicking a [data-period-tab="<key>"] button shows every
 * [data-period="<key>"] element in the container and hides the rest.
 *
 * The choice is remembered (per browser) so the dashboard and resource pages
 * stay on the same period; if that period isn't on the page, the
 * server-rendered default is left alone.
 */
(function () {
    var STORAGE_KEY = 'cashflow.period';

    function remembered() {
        try {
            return window.localStorage.getItem(STORAGE_KEY);
        } catch (e) {
            return null;
        }
    }

    function remember(key) {
        try {
            window.localStorage.setItem(STORAGE_KEY, key);
        } catch (e) {
            // Storage blocked or full - the switcher still works, it just isn't remembered.
        }
    }

    document.querySelectorAll('[data-period-switcher]').forEach(function (container) {
        var tabs = container.querySelectorAll('[data-period-tab]');
        var panels = container.querySelectorAll('[data-period]');

        if (tabs.length === 0) {
            return;
        }

        function select(key) {
            tabs.forEach(function (tab) {
                tab.setAttribute('aria-pressed', String(tab.dataset.periodTab === key));
            });

            panels.forEach(function (panel) {
                panel.classList.toggle('hidden', panel.dataset.period !== key);
            });
        }

        tabs.forEach(function (tab) {
            tab.addEventListener('click', function () {
                select(tab.dataset.periodTab);
                remember(tab.dataset.periodTab);
            });
        });

        var stored = remembered();

        if (stored !== null && Array.prototype.some.call(tabs, function (tab) { return tab.dataset.periodTab === stored; })) {
            select(stored);
        }
    });
})();
