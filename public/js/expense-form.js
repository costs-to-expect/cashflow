(function () {
    var container = document.getElementById('allocations');
    if (!container) {
        return;
    }

    var splitToggle = document.getElementById('split-toggle');
    var addButton = document.getElementById('add-allocation');
    var children = JSON.parse(container.dataset.children || '[]');

    function rows() {
        return Array.from(container.querySelectorAll('.allocation-row'));
    }

    function reindex() {
        rows().forEach(function (row, index) {
            row.querySelectorAll('[name]').forEach(function (field) {
                field.name = field.name.replace(/allocations\[\d+\]/, 'allocations[' + index + ']');
            });
            var removeButton = row.querySelector('.remove-allocation');
            removeButton.classList.toggle('hidden', rows().length <= 1);
        });
    }

    function updateToggleUi() {
        var isSplit = splitToggle.checked;
        addButton.classList.toggle('hidden', !isSplit);
        rows().forEach(function (row) {
            row.querySelector('.remove-allocation').classList.toggle('hidden', !isSplit || rows().length <= 1);
        });
    }

    splitToggle.addEventListener('change', updateToggleUi);

    addButton.addEventListener('click', function () {
        var index = rows().length;
        var usedIds = rows().map(function (row) {
            return row.querySelector('select').value;
        });
        var nextChild = children.find(function (child) {
            return usedIds.indexOf(child.id) === -1;
        }) || children[0];

        var options = children.map(function (child) {
            var selected = child.id === nextChild.id ? ' selected' : '';
            return '<option value="' + child.id + '"' + selected + '>' + child.name + '</option>';
        }).join('');

        var row = document.createElement('div');
        row.className = 'allocation-row grid grid-cols-[1fr_120px_auto] items-end gap-3';
        row.innerHTML =
            '<div><label class="block text-sm font-medium text-gray-700">Child</label>' +
            '<select name="allocations[' + index + '][resource_id]" required class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm">' + options + '</select></div>' +
            '<div><label class="block text-sm font-medium text-gray-700">Percentage</label>' +
            '<input type="number" name="allocations[' + index + '][percentage]" min="1" max="100" required class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm"></div>' +
            '<button type="button" class="remove-allocation pb-2 text-sm text-red-600 hover:underline">Remove</button>';

        container.appendChild(row);
        updateToggleUi();
    });

    container.addEventListener('click', function (event) {
        if (event.target.classList.contains('remove-allocation')) {
            event.target.closest('.allocation-row').remove();
            reindex();
            updateToggleUi();
        }
    });
})();
