(function () {
    var toggle = document.getElementById('mobile-menu-toggle');
    var menu = document.getElementById('mobile-menu');

    if (!toggle || !menu) {
        return;
    }

    var openIcon = document.getElementById('mobile-menu-toggle-open');
    var closeIcon = document.getElementById('mobile-menu-toggle-close');

    toggle.addEventListener('click', function () {
        var expanded = toggle.getAttribute('aria-expanded') === 'true';

        toggle.setAttribute('aria-expanded', String(!expanded));
        menu.classList.toggle('hidden');

        if (openIcon && closeIcon) {
            openIcon.classList.toggle('hidden');
            closeIcon.classList.toggle('hidden');
        }
    });
})();
