/*
 * The "Copy to" pop-up of a resource's expense list (resources/views/components/expense/copy-to-dialog.blade.php).
 * There is one dialog on the page. Each expense's "Copy to" button carries what it needs - the expense's
 * name, where "Copy" posts to and the copy form "Adjust" opens - and this fills the dialog in from them.
 * "Adjust" links to the copy form with the chosen resource in ?to=, which the form preselects.
 */
(function () {
    var dialog = document.getElementById('copy-to-dialog');

    if (!dialog || typeof dialog.showModal !== 'function') {
        return;
    }

    var form = document.getElementById('copy-to-form');
    var name = document.getElementById('copy-to-name');
    var target = document.getElementById('target_resource_id');
    var adjust = document.getElementById('copy-to-adjust');
    var submit = document.getElementById('copy-to-submit');
    var adjustUrl = '';

    function updateAdjustLink() {
        adjust.href = adjustUrl + '?to=' + encodeURIComponent(target.value);
    }

    document.addEventListener('click', function (event) {
        var opener = event.target.closest('[data-copy-to]');

        if (opener) {
            form.action = opener.dataset.copyUrl;
            adjustUrl = opener.dataset.adjustUrl;
            name.textContent = opener.dataset.name;
            updateAdjustLink();
            dialog.showModal();

            return;
        }

        // The backdrop is part of the dialog, the form fills the rest of it.
        if (event.target.closest('[data-copy-to-close]') || event.target === dialog) {
            dialog.close();
        }
    });

    target.addEventListener('change', updateAdjustLink);

    // One copy per click: a second would add a duplicate.
    form.addEventListener('submit', function () {
        submit.disabled = true;
    });

    // Back to the page from the browser's cache brings the disabled button with it.
    window.addEventListener('pageshow', function () {
        submit.disabled = false;
    });
})();
