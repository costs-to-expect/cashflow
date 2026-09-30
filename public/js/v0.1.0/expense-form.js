(function () {
    // Category -> subcategory cascade (plain <select>s, no framework), shared by the expense and
    // recurring-expense forms, create and edit. The split card has its own script, expense-split.js.
    // Both selects are required with no "None" option, and the fields aren't rendered at all when
    // the resource type has categories turned off - hence the early return.
    var categorySelect = document.getElementById('category_id');
    var subcategorySelect = document.getElementById('subcategory_id');

    if (!categorySelect || !subcategorySelect) {
        return;
    }

    var subcategoriesByCategory = JSON.parse(categorySelect.dataset.subcategories || '{}');
    var initialSubcategoryId = subcategorySelect.dataset.initial || '';

    var rebuildSubcategoryOptions = function () {
        var subcategories = subcategoriesByCategory[categorySelect.value] || [];

        // A category with no subcategories gets one empty-valued placeholder, which the select's
        // "required" attribute refuses to submit.
        var options = subcategories.length > 0
            ? subcategories.map(function (subcategory) {
                return '<option value="' + subcategory.id + '">' + subcategory.name + '</option>';
            })
            : ['<option value="">No subcategories yet</option>'];

        subcategorySelect.innerHTML = options.join('');

        if (initialSubcategoryId) {
            subcategorySelect.value = initialSubcategoryId;
            initialSubcategoryId = '';
        }
    };

    categorySelect.addEventListener('change', rebuildSubcategoryOptions);
    rebuildSubcategoryOptions();
})();
