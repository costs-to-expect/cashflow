(function () {
    var container = document.getElementById('default-split-allocations');
    if (!container) {
        return;
    }

    var addButton = document.getElementById('add-default-split-allocation');
    var resources = JSON.parse(container.dataset.resources || '[]');
    var termSingular = container.dataset.termSingular || 'Resource';

    function rows() {
        return Array.from(container.querySelectorAll('.allocation-row'));
    }

    function reindex() {
        rows().forEach(function (row, index) {
            row.querySelectorAll('[name]').forEach(function (field) {
                field.name = field.name.replace(/allocations\[\d+\]/, 'allocations[' + index + ']');
            });
            row.querySelector('.remove-allocation').classList.toggle('hidden', rows().length <= 1);
        });
    }

    addButton.addEventListener('click', function () {
        var index = rows().length;
        var usedIds = rows().map(function (row) {
            return row.querySelector('select').value;
        });
        var nextResource = resources.find(function (resource) {
            return usedIds.indexOf(resource.id) === -1;
        }) || resources[0];

        var options = resources.map(function (resource) {
            var selected = resource.id === nextResource.id ? ' selected' : '';
            return '<option value="' + resource.id + '"' + selected + '>' + resource.name + '</option>';
        }).join('');

        var row = document.createElement('div');
        row.className = 'allocation-row grid grid-cols-[1fr_120px_auto] items-end gap-3';
        row.innerHTML =
            '<div><label class="block text-sm font-medium text-gray-700">' + termSingular + '</label>' +
            '<select name="allocations[' + index + '][resource_id]" required class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm">' + options + '</select></div>' +
            '<div><label class="block text-sm font-medium text-gray-700">Percentage</label>' +
            '<input type="number" name="allocations[' + index + '][percentage]" min="1" max="100" required class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm"></div>' +
            '<button type="button" class="remove-allocation pb-2 text-sm text-red-600 hover:underline">Remove</button>';

        container.appendChild(row);
        reindex();
    });

    container.addEventListener('click', function (event) {
        if (event.target.classList.contains('remove-allocation')) {
            event.target.closest('.allocation-row').remove();
            reindex();
        }
    });
})();
