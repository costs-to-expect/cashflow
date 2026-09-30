(function () {
    // Category -> subcategory cascade (plain <select>s, no framework), shared by the expense and
    // recurring-expense forms, create and edit. The split card has its own script, expense-split.js.
    var categorySelect = document.getElementById('category_id');
    var subcategorySelect = document.getElementById('subcategory_id');

    if (!categorySelect || !subcategorySelect) {
        return;
    }

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
})();
