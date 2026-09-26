(function () {
    function toFixedNumber(value, points) {
        if (value !== '') {
            return parseFloat(value).toFixed(points);
        }
        return '';
    }

    document.querySelectorAll('input[data-format="number"]').forEach(function (input) {
        input.addEventListener('blur', function () {
            input.value = toFixedNumber(input.value, parseInt(input.dataset.points, 10));
        });
    });

    var submit = document.querySelector('button[type="submit"]');
    if (submit) {
        submit.addEventListener('click', function () {
            document.querySelectorAll('input[data-format="number"]').forEach(function (input) {
                input.value = toFixedNumber(input.value, parseInt(input.dataset.points, 10));
            });
        });
    }
})();
