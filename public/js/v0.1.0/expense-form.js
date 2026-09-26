(function () {
    // Category -> subcategory cascade (plain <select>s, no framework). Runs
    // on both the create form (with allocations) and the edit form (without).
    var categorySelect = document.getElementById('category_id');
    var subcategorySelect = document.getElementById('subcategory_id');

    if (categorySelect && subcategorySelect) {
        var subcategoriesByCategory = JSON.parse(categorySelect.dataset.subcategories || '{}');
        var initialSubcategoryId = subcategorySelect.dataset.initial || '';

        var rebuildSubcategoryOptions = function () {
            var subcategories = subcategoriesByCategory[categorySelect.value] || [];
            var options = ['<option value="">None</option>'].concat(subcategories.map(function (subcategory) {
                return '<option value="' + subcategory.id + '">' + subcategory.name + '</option>';
            }));

            subcategorySelect.innerHTML = options.join('');

            if (initialSubcategoryId) {
                subcategorySelect.value = initialSubcategoryId;
                initialSubcategoryId = '';
            }
        };

        categorySelect.addEventListener('change', rebuildSubcategoryOptions);
        rebuildSubcategoryOptions();
    }

    var container = document.getElementById('allocations');
    if (!container) {
        return;
    }

    var splitToggle = document.getElementById('split-toggle');
    var addButton = document.getElementById('add-allocation');
    var resources = JSON.parse(container.dataset.resources || '[]');
    var defaultSplit = JSON.parse(container.dataset.defaultSplit || '[]');
    var termSingular = container.dataset.termSingular || 'Resource';

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

    function buildRow(index, resourceId, percentage) {
        var options = resources.map(function (resource) {
            var selected = resource.id === resourceId ? ' selected' : '';
            return '<option value="' + resource.id + '"' + selected + '>' + resource.name + '</option>';
        }).join('');

        var row = document.createElement('div');
        row.className = 'allocation-row grid grid-cols-[1fr_120px_auto] items-end gap-3';
        row.innerHTML =
            '<div><label class="block text-sm font-medium text-gray-700">' + termSingular + '</label>' +
            '<select name="allocations[' + index + '][resource_id]" required class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm">' + options + '</select></div>' +
            '<div><label class="block text-sm font-medium text-gray-700">Percentage</label>' +
            '<input type="number" name="allocations[' + index + '][percentage]" value="' + percentage + '" min="1" max="100" required class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm"></div>' +
            '<button type="button" class="remove-allocation pb-2 text-sm text-red-600 hover:underline">Remove</button>';

        return row;
    }

    // If a default split is configured and the form is still showing a
    // single (un-split) row, checking the box pre-fills it from the default
    // rather than leaving the user to add every row by hand.
    function applyDefaultSplitIfFresh() {
        if (rows().length > 1 || defaultSplit.length < 2) {
            return;
        }

        var resourceIds = resources.map(function (resource) {
            return resource.id;
        });
        var applicable = defaultSplit.filter(function (allocation) {
            return resourceIds.indexOf(allocation.resource_id) !== -1;
        });

        if (applicable.length < 2) {
            return;
        }

        container.innerHTML = '';
        applicable.forEach(function (allocation, index) {
            container.appendChild(buildRow(index, allocation.resource_id, allocation.percentage));
        });
    }

    splitToggle.addEventListener('change', function () {
        if (splitToggle.checked) {
            applyDefaultSplitIfFresh();
        }
        updateToggleUi();
    });

    addButton.addEventListener('click', function () {
        var index = rows().length;
        var usedIds = rows().map(function (row) {
            return row.querySelector('select').value;
        });
        var nextResource = resources.find(function (resource) {
            return usedIds.indexOf(resource.id) === -1;
        }) || resources[0];

        container.appendChild(buildRow(index, nextResource.id, ''));
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
