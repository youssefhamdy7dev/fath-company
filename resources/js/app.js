import 'bootstrap';
import * as bootstrap from 'bootstrap';
window.bootstrap = bootstrap;

import './bootstrap';
import flatpickr from "flatpickr";
import "flatpickr/dist/flatpickr.min.css";
import { Arabic } from "flatpickr/dist/l10n/ar.js";
import Choices from 'choices.js';

// Initialize Flatpickr and Choices
document.addEventListener('DOMContentLoaded', function () {

    // Get business date calculated by Laravel (10:00 AM cutoff)
    const businessDate = document.querySelector(
        'meta[name="business-date"]'
    )?.content;

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

    // Date pickers
    let minimumDate = new Date(
        new Date().getFullYear(),
        new Date().getMonth(),
        1
    );

    const datepickerElement = document.querySelector(".purchasedate");

    if (datepickerElement) {
        const minDateStr = datepickerElement.dataset.minDate;

        if (minDateStr) {
            minimumDate = new Date(minDateStr);
        }
    }

    // Customer purchase date
    flatpickr(".purchasedate", {
        dateFormat: "Y-m-d",
        locale: Arabic,
        allowInput: true,
        minDate: minimumDate,
        maxDate: businessDate,
    });

    // General date picker
    flatpickr(".datepicker", {
        dateFormat: "Y-m-d",
        locale: Arabic,
        allowInput: true,
        minDate: new Date(
            new Date().getFullYear(),
            new Date().getMonth(),
            1
        ),
        maxDate: businessDate,
    });

    // Global date picker
    flatpickr(".globdatepicker", {
        dateFormat: "Y-m-d",
        locale: Arabic,
        allowInput: true,
        maxDate: businessDate,
    });
});


/**
 * Initialize AJAX search + location filter for customers index page.
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

//     searchInput.addEventListener('input', fetchCustomers);
//     locationSelect.addEventListener('change', fetchCustomers);

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
 */
window.initDeleteHandler = function (resourceName) {
    const deleteModal = document.getElementById(
        `delete${capitalize(resourceName)}Modal`
    );

    const deleteForm = document.getElementById(
        `delete${capitalize(resourceName)}Form`
    );

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

    if (!deleteModal || !deleteForm) return;

    const modalBody = deleteModal.querySelector('.modal-body');
    const confirmBtn = deleteModal.querySelector('[id$="Confirm"]');

    document.querySelectorAll('.delete-btn').forEach((btn) => {
        btn.addEventListener('click', function () {
            const itemId = this.dataset.id;
            const itemName = this.dataset.name;
            const itemType = resourceTranslation[resourceName] || '';

            // Update modal body
            modalBody.textContent =
                `هل أنت متأكد من حذف ${itemType}: ${itemName}؟`;

            // Update form action
            deleteForm.action = `/${resourceName}s/${itemId}`;
        });
    });

    if (confirmBtn) {
        confirmBtn.addEventListener('click', () => deleteForm.submit());
    }
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
            this.classList.add('disabled');
        });
    });
});
