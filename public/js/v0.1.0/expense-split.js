/*
 * The add-expense "Who is it for?" section: split toggle, one row per
 * resource (percentage + live amount), an allocation bar and status. Rows
 * are cloned from the server-rendered <template>, so there's one source of
 * markup. (The recurring-expense forms still use expense-form.js's own,
 * older split UI - this only runs where #split-rows exists.)
 */
(function () {
    var root = document.getElementById('split-section');
    var container = document.getElementById('split-rows');

    if (!root || !container) {
        return;
    }

    var resources = JSON.parse(container.dataset.resources || '[]');
    var defaultSplit = JSON.parse(container.dataset.defaultSplit || '[]');

    var template = document.getElementById('allocation-row-template');
    var toggle = document.getElementById('split-toggle');
    var help = document.getElementById('split-help');
    var bar = document.getElementById('split-bar');
    var status = document.getElementById('split-status');
    var addButton = document.getElementById('add-allocation');
    var evenButton = document.getElementById('split-even');
    var defaultButton = document.getElementById('split-default');
    var totalInput = document.getElementById('total');
    var currencySelect = document.getElementById('currency_id');

    function rows() {
        return Array.prototype.slice.call(container.querySelectorAll('.allocation-row'));
    }

    function resourceSelect(row) {
        return row.querySelector('select');
    }

    function percentageInput(row) {
        return row.querySelector('input[type="number"]');
    }

    function resourceById(id) {
        return resources.filter(function (resource) {
            return resource.id === id;
        })[0] || null;
    }

    function isSplit() {
        return toggle !== null && toggle.checked;
    }

    function formatMoney(value) {
        return value.toLocaleString('en-GB', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function currencyCode() {
        var option = currencySelect.options[currencySelect.selectedIndex];

        return option ? option.text : '';
    }

    function reindex() {
        rows().forEach(function (row, index) {
            row.querySelectorAll('[name]').forEach(function (field) {
                field.name = field.name.replace(/allocations\[[^\]]+\]/, 'allocations[' + index + ']');
            });
        });
    }

    function addRow(resourceId, percentage) {
        var row = template.content.firstElementChild.cloneNode(true);

        container.appendChild(row);
        resourceSelect(row).value = resourceId;
        percentageInput(row).value = percentage;
        reindex();

        return row;
    }

    function nextUnusedResourceId() {
        var used = rows().map(function (row) {
            return resourceSelect(row).value;
        });
        var next = resources.filter(function (resource) {
            return used.indexOf(resource.id) === -1;
        })[0];

        return (next || resources[0]).id;
    }

    function setPercentages(list, values) {
        list.forEach(function (row, index) {
            percentageInput(row).value = values[index];
        });
    }

    function splitEvenly() {
        var list = rows();
        var base = Math.floor(100 / list.length);
        var values = list.map(function () {
            return base;
        });

        values[0] += 100 - base * list.length;
        setPercentages(list, values);
    }

    function allocatedPercentage() {
        return rows().reduce(function (sum, row) {
            return sum + (parseFloat(percentageInput(row).value) || 0);
        }, 0);
    }

    function applyDefaultSplit() {
        rows().forEach(function (row) {
            row.remove();
        });

        defaultSplit.forEach(function (allocation) {
            addRow(String(allocation.resource_id), allocation.percentage);
        });
    }

    function update() {
        var split = isSplit();
        var total = parseFloat(totalInput.value) || 0;
        var code = currencyCode();
        var allocated = allocatedPercentage();

        root.querySelectorAll('[data-split-only]').forEach(function (element) {
            element.classList.toggle('hidden', !split);
        });

        if (addButton) {
            // Nothing left to add once every resource already has a row.
            addButton.classList.toggle('hidden', rows().length >= resources.length);
        }

        if (bar) {
            bar.innerHTML = '';
        }

        rows().forEach(function (row) {
            var percentage = split ? (parseFloat(percentageInput(row).value) || 0) : 100;
            var resource = resourceById(resourceSelect(row).value);
            var colour = resource ? resource.dot : 'bg-gray-400';

            row.querySelector('.allocation-dot').className = 'allocation-dot size-2.5 shrink-0 rounded-full ' + colour;
            row.querySelector('[data-share]').textContent = total > 0 ? code + ' ' + formatMoney(total * percentage / 100) : '';

            if (bar) {
                var segment = document.createElement('span');

                segment.className = 'transition-all ' + colour;
                segment.style.width = Math.min(percentage, 100) + '%';
                bar.appendChild(segment);
            }
        });

        if (status) {
            var remaining = 100 - allocated;

            status.className = 'text-sm font-medium ' + (remaining === 0 ? 'text-emerald-700' : (remaining > 0 ? 'text-amber-700' : 'text-red-600'));
            status.textContent = remaining === 0
                ? '100% allocated'
                : (remaining > 0
                    ? allocated + '% allocated, ' + remaining + '% still to go'
                    : allocated + '% allocated, ' + Math.abs(remaining) + '% too much');
        }

        if (help) {
            help.textContent = split ? help.dataset.textSplit : help.dataset.textSingle;
        }
    }

    if (toggle) {
        toggle.addEventListener('change', function () {
            if (toggle.checked) {
                if (rows().length < 2) {
                    if (defaultSplit.length >= 2) {
                        applyDefaultSplit();
                    } else {
                        addRow(nextUnusedResourceId(), '');
                        splitEvenly();
                    }
                }
            } else {
                // Un-splitting keeps the first row, back at 100%.
                rows().slice(1).forEach(function (row) {
                    row.remove();
                });
                percentageInput(rows()[0]).value = 100;
            }

            update();
        });
    }

    if (addButton) {
        addButton.addEventListener('click', function () {
            var remaining = 100 - allocatedPercentage();

            addRow(nextUnusedResourceId(), remaining > 0 ? remaining : '');
            update();
        });
    }

    if (evenButton) {
        evenButton.addEventListener('click', function () {
            splitEvenly();
            update();
        });
    }

    if (defaultButton) {
        defaultButton.addEventListener('click', function () {
            applyDefaultSplit();
            update();
        });
    }

    container.addEventListener('click', function (event) {
        var remove = event.target.closest('[data-remove]');

        if (!remove) {
            return;
        }

        remove.closest('.allocation-row').remove();
        reindex();

        if (rows().length < 2 && toggle) {
            toggle.checked = false;
            percentageInput(rows()[0]).value = 100;
        }

        update();
    });

    container.addEventListener('input', update);
    container.addEventListener('change', update);
    totalInput.addEventListener('input', update);
    currencySelect.addEventListener('change', update);

    update();
})();
