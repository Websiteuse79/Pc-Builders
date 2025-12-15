// Handle View button click to show order details modal
document.addEventListener('DOMContentLoaded', function () {
    // Delegate click event for all .btn-view buttons
    document.querySelectorAll('.btn-view').forEach(function (btn) {
        btn.addEventListener('click', function () {
            let order = {};
            try {
                order = JSON.parse(this.getAttribute('data-order'));
            } catch (e) {
                order = {};
            }
            document.getElementById('modal-order-id').textContent = '#' + (order.id || '');
            document.getElementById('modal-customer-name').textContent = order.customer_name || '';
            document.getElementById('modal-order-date').textContent = order.created_at ? (new Date(order.created_at)).toLocaleDateString() : '';
            document.getElementById('modal-order-status').textContent = order.status || '';
            document.getElementById('modal-order-total').textContent = order.total_price ? '₹' + Number(order.total_price).toLocaleString(undefined, {minimumFractionDigits: 2}) : '';

            // Render order items
            const itemsContainer = document.getElementById('modal-order-items');
            itemsContainer.innerHTML = '';
            if (Array.isArray(order.items) && order.items.length > 0) {
                order.items.forEach(function(item, idx) {
                    const div = document.createElement('div');
                    div.className = 'order-item';
                    div.innerHTML =
                        '<strong>' + (item.name || 'Item ' + (idx+1)) + '</strong>' +
                        (item.quantity ? ' x' + item.quantity : '') +
                        (item.price ? ' - ₹' + Number(item.price).toLocaleString(undefined, {minimumFractionDigits: 2}) : '');
                    itemsContainer.appendChild(div);
                });
            } else {
                itemsContainer.textContent = 'No items found.';
            }

            // Show modal using .active class
            document.getElementById('order-modal').classList.add('active');
        });
    });

    // Close modal on close button click
    document.querySelectorAll('.close-modal').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.getElementById('order-modal').classList.remove('active');
        });
    });

    // Optional: Close modal when clicking outside modal content
    document.getElementById('order-modal').addEventListener('click', function (e) {
        if (e.target === this) {
            this.classList.remove('active');
        }
    });
});
document.addEventListener('DOMContentLoaded', function () {
    // Product form toggle
    const addProductBtn = document.getElementById('add-product-btn');
    const productFormContainer = document.getElementById('product-form-container');
    const cancelFormBtn = document.getElementById('cancel-form');
    const formTitle = document.getElementById('form-title');
    const submitBtn = document.getElementById('submit-btn');

    if (addProductBtn) {
        addProductBtn.addEventListener('click', function () {
           // Reset form but keep the category select placeholder selected
            const form = document.getElementById('product-form');
            form.reset();
            document.getElementById('product_id').value = '';
            formTitle.textContent = 'Add New Product';
            submitBtn.name = 'add_product';
            submitBtn.textContent = 'Add Product';

            // set table select to placeholder (empty) so admin must choose category first
            const tableSelect = document.getElementById('table');
            if (tableSelect) {
                tableSelect.value = '';
            }

            // Clear any dynamic fields until a category is chosen
            const dyn = document.getElementById('dynamic-fields');
            if (dyn) dyn.innerHTML = '<div class="notice">Please select a category to load fields.</div>';

            // Show form
            productFormContainer.style.display = 'block';
            window.scrollTo({ top: productFormContainer.offsetTop - 20, behavior: 'smooth' });
        });
    }

    if (cancelFormBtn) {
        cancelFormBtn.addEventListener('click', function () {
            productFormContainer.style.display = 'none';
        });
    }

    // When table selector changes, render its fields
    const tableSelect = document.getElementById('table');
    if (tableSelect) {
        tableSelect.addEventListener('change', function () {
            // if placeholder (empty) selected, clear fields
            if (!this.value) {
                const dyn = document.getElementById('dynamic-fields');
                if (dyn) dyn.innerHTML = '<div class="notice">Please select a category to load fields.</div>';
                return;
            }
            renderDynamicFields(this.value, {});
        });
    }

    // Category filter buttons (filter table rows by data-table)
    document.querySelectorAll('.cat-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.cat-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            const table = this.getAttribute('data-table');
            const rows = document.querySelectorAll('.products-table table tbody tr');
            rows.forEach(r => {
                if (table === 'all') { r.style.display = ''; return; }
                if (r.getAttribute('data-table') === table) r.style.display = '';
                else r.style.display = 'none';
            });
        });
    });

    // Editing is handled server-side by submitting the inline form for each row.

});



var openModalBtn = document.getElementById("openModalBtn");
if (openModalBtn) {
    openModalBtn.addEventListener("click", function() {
        var orderModal = document.getElementById("order-modal");
        if (orderModal) orderModal.classList.add("show");
    });
}

// Dynamic fields rendering: mapping will be injected by PHP as `window.__tableFieldMap`
function renderDynamicFields(table, values) {
        const mapping = window.__tableFieldMap || {};
        const numericSet = new Set(window.__numericFields || []);
        const container = document.getElementById('dynamic-fields');
        if (!container) return;
        container.innerHTML = '';
        const fields = mapping[table] || [];
        fields.forEach(f => {
                const div = document.createElement('div');
                div.className = 'form-group dynamic-field';
                div.setAttribute('data-field', f);
                const label = document.createElement('label');
                label.setAttribute('for', f);
                label.textContent = f.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
                let input;
                if (numericSet.has(f)) {
                        input = document.createElement('input');
                        input.type = 'number';
                        input.step = 'any';
                } else {
                        input = document.createElement('input');
                        input.type = 'text';
                }
                input.id = f;
                input.name = f;
                input.value = values[f] || '';
                div.appendChild(label);
                div.appendChild(input);
                container.appendChild(div);
        });
}
