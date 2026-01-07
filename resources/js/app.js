import 'bootstrap';
import * as bootstrap from 'bootstrap';
window.bootstrap = bootstrap;
import './bootstrap';
import flatpickr from "flatpickr";
import "flatpickr/dist/flatpickr.min.css";
import { Arabic } from "flatpickr/dist/l10n/ar.js";
import Choices from 'choices.js';

// Initialize flatpickr globally for all inputs with class `.datepicker`
document.addEventListener('DOMContentLoaded', function () {
    // Customer Select
    const customerSelect = document.getElementById("customer_id");

    if (customerSelect) {
        new Choices(customerSelect, {
            searchPlaceholderValue: "ابحث...",
            noResultsText: "لم يتم العثور على الزبون...",
            itemSelectText: "",
            searchEnabled: true,
            shouldSort: false,
            placeholder: true,
        });
    }

    // Date
    let minimumDate = new Date(new Date().getFullYear(), new Date().getMonth(), 1);
    const datepickerElement = document.querySelector(".purchasedate");
    if (datepickerElement) {
        const minDateStr = datepickerElement.dataset.minDate;
        minimumDate = new Date(minDateStr);
    }

    flatpickr(".purchasedate", {
        dateFormat: "Y-m-d",
        locale: Arabic,
        allowInput: true,
        minDate: minimumDate,
        maxDate: new Date(),
    });

    flatpickr(".datepicker", {
        dateFormat: "Y-m-d",
        locale: Arabic,
        allowInput: true,
        minDate: new Date(new Date().getFullYear(), new Date().getMonth(), 1),
        maxDate: new Date(),
    });

    flatpickr(".globdatepicker", {
        dateFormat: "Y-m-d",
        locale: Arabic,
        allowInput: true,
        maxDate: new Date(),
    });
});

/**
 * Initialize AJAX search + location filter for customers index page.
 * Expects:
 * - #searchName input for name filter
 * - #filterLocation select for location filter
 * - #customersTableBody as the table body container
 * - delete modals per customer (handled below)
 */
// window.initCustomerIndex = function () {
//     const searchInput = document.getElementById('searchName');
//     const locationSelect = document.getElementById('filterLocation');
//     const tableBody = document.getElementById('customersTableBody');

//     if (!searchInput || !locationSelect || !tableBody) return;

//     const fetchCustomers = () => {
//         const name = searchInput.value;
//         const location = locationSelect.value;

//         fetch(`${window.customerIndexRoute}?name=${encodeURIComponent(name)}&location=${encodeURIComponent(location)}`, {
//             headers: { 'X-Requested-With': 'XMLHttpRequest' }
//         })
//             .then(res => res.text())
//             .then(html => {
//                 tableBody.innerHTML = html;
//             })
//             .catch(err => console.error('Error fetching customers:', err));
//     };

//     // Trigger search on typing or location change
//     searchInput.addEventListener('input', fetchCustomers);
//     locationSelect.addEventListener('change', fetchCustomers);

//     // Handle modal delete confirmations (one per customer)
//     document.querySelectorAll('[id^="deleteCustomerModal"][id$="Confirm"]').forEach(btn => {
//         btn.addEventListener('click', function () {
//             const modalId = this.id.replace('Confirm', '');
//             const customerId = modalId.replace('deleteCustomerModal', '');
//             const form = document.getElementById('deleteCustomerForm' + customerId);
//             if (form) form.submit();
//         });
//     });
// };


/**
 * Generic delete modal handler
 * Works for any resource index page (clients, fruits, etc.)
 * Expects:
 * - delete buttons with class `.delete-btn`
 * - each button having data-id and data-name
 * - a single modal with id `#deleteModal`
 * - a hidden form with id `#deleteForm`
 */
window.initDeleteHandler = function (resourceName) {
    const deleteModal = document.getElementById(`delete${capitalize(resourceName)}Modal`);
    const deleteForm = document.getElementById(`delete${capitalize(resourceName)}Form`);
    const resourceTranslation = {
        fruit: 'الصنف',
        client: 'العميل',
        driver: 'السائق',
        customer: 'الزبون',
        employee: 'الموظف',
        truck: 'العربة',
        purchase: 'مشتروات العميل',
        payment: 'تحصيل العميل',
        bill: 'الفاتورة'
    };
    if (!deleteModal || !deleteForm) return; // Exit if not found

    const modalBody = deleteModal.querySelector('.modal-body');
    const confirmBtn = deleteModal.querySelector('[id$="Confirm"]');

    document.querySelectorAll('.delete-btn').forEach((btn) => {
        btn.addEventListener('click', function () {
            const itemId = this.dataset.id;
            const itemName = this.dataset.name;
            const itemType = resourceTranslation[resourceName] || '';

            // Update modal body
            modalBody.textContent = `هل أنت متأكد من حذف ${itemType}: ${itemName}؟`;

            // Update form action
            deleteForm.action = `/${resourceName}s/${itemId}`;
        });
    });

    confirmBtn.addEventListener('click', () => deleteForm.submit());
};

/**
 * Capitalize first letter (used for modal/form IDs)
 */
function capitalize(str) {
    return str.charAt(0).toUpperCase() + str.slice(1);
}

// Disable all `.btn-reload` buttons when clicked until page reloads
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.btn-reload').forEach((button) => {
        button.addEventListener('click', function () {
            this.classList.add('disabled'); // Optional: add Bootstrap disabled style
        });
    });
});