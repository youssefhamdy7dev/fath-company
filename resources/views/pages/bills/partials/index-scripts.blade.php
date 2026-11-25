<script>
    document.addEventListener('DOMContentLoaded', function() {
        const dateFrom = document.getElementById('billDateFrom');
        const dateTo = document.getElementById('billDateTo');

        const clientCheckboxes = document.querySelectorAll('.bill-client-checkbox');
        const fruitCheckboxes = document.querySelectorAll('.bill-fruit-checkbox');

        const clientsCount = document.getElementById('clientsSelectionCount');
        const fruitsCount = document.getElementById('fruitsSelectionCount');

        const resetButton = document.getElementById('resetBillFilters');
        const noBillsFound = document.getElementById('noBillsFound');

        const clientGroups = document.querySelectorAll('.bill-client-group');

        const pageSize = 5;

        function getSelectedValues(checkboxes) {
            const allValues = Array.from(checkboxes).map(checkbox => checkbox.value);
            const selectedValues = Array.from(checkboxes)
                .filter(checkbox => checkbox.checked)
                .map(checkbox => checkbox.value);

            if (
                selectedValues.length === 0 ||
                selectedValues.length === allValues.length
            ) {
                return null;
            }

            return selectedValues;
        }

        function hasActiveFilters() {
            return Boolean(
                dateFrom.value ||
                dateTo.value ||
                getSelectedValues(clientCheckboxes) ||
                getSelectedValues(fruitCheckboxes)
            );
        }

        function updateDropdownLabels() {
            const selectedClients = Array.from(clientCheckboxes)
                .filter(checkbox => checkbox.checked).length;

            const selectedFruits = Array.from(fruitCheckboxes)
                .filter(checkbox => checkbox.checked).length;

            clientsCount.textContent =
                selectedClients === clientCheckboxes.length || selectedClients === 0 ?
                '(الكل)' :
                `(${selectedClients})`;

            fruitsCount.textContent =
                selectedFruits === fruitCheckboxes.length || selectedFruits === 0 ?
                '(الكل)' :
                `(${selectedFruits})`;
        }

        function formatAmount(amount) {
            return new Intl.NumberFormat('ar-EG', {
                maximumFractionDigits: 0
            }).format(amount);
        }

        // Match the PHP round_to_nearest_tenth() helper.
        function getRoundedBillAmount(rawAmount) {
            const amount = Number(rawAmount) || 0;

            return Math.round(amount / 10) * 10;
        }

        function renderPagination(group, visibleRows) {
            const pagination = group.querySelector('.bill-pagination');
            pagination.replaceChildren();

            const pageCount = Math.ceil(visibleRows.length / pageSize);

            if (pageCount <= 1) {
                pagination.classList.add('d-none');
                return;
            }

            pagination.classList.remove('d-none');

            let currentPage = Number(group.dataset.currentPage || 1);

            if (currentPage > pageCount) {
                currentPage = pageCount;
            }

            group.dataset.currentPage = currentPage;

            function createButton(label, page, disabled = false, active = false) {
                const button = document.createElement('button');

                button.type = 'button';
                button.textContent = label;
                button.className = 'btn btn-sm mx-1 ' +
                    (active ? 'btn-primary' : 'btn-outline-primary');

                button.disabled = disabled;

                button.addEventListener('click', function() {
                    group.dataset.currentPage = page;
                    applyFilters();
                });

                return button;
            }

            pagination.appendChild(
                createButton('السابق', currentPage - 1, currentPage === 1)
            );

            for (let page = 1; page <= pageCount; page++) {
                pagination.appendChild(
                    createButton(
                        page,
                        page,
                        false,
                        page === currentPage
                    )
                );
            }

            pagination.appendChild(
                createButton('التالي', currentPage + 1, currentPage === pageCount)
            );
        }

        function applyFilters() {
            const selectedClients = getSelectedValues(clientCheckboxes);
            const selectedFruits = getSelectedValues(fruitCheckboxes);

            const activeFilters = hasActiveFilters();

            let anyVisibleBill = false;

            clientGroups.forEach(group => {
                const clientId = group.dataset.clientId;
                const rows = Array.from(group.querySelectorAll('.bill-row'));

                const matchingRows = rows.filter(row => {
                    const billDate = row.dataset.date;
                    const billFruitIds = row.dataset.fruitIds
                        .split(',')
                        .filter(Boolean);

                    const matchesStartDate = !dateFrom.value || billDate >= dateFrom.value;

                    const matchesEndDate = !dateTo.value || billDate <= dateTo.value;

                    const matchesClient = !selectedClients || selectedClients.includes(
                    clientId);

                    const matchesFruit = !selectedFruits ||
                        selectedFruits.some(fruitId => billFruitIds.includes(fruitId));

                    return (
                        matchesStartDate &&
                        matchesEndDate &&
                        matchesClient &&
                        matchesFruit
                    );
                });

                group.querySelector('.bill-client-total').classList.toggle(
                    'd-none',
                    !activeFilters
                );

                if (activeFilters) {
                    // Sum each bill after applying the same nearest-10 rounding
                    // used by the table display.
                    const total = matchingRows.reduce((sum, row) => {
                        return sum + getRoundedBillAmount(row.dataset.amount);
                    }, 0);

                    group.querySelector('.bill-total-value').textContent =
                        formatAmount(total);

                    group.querySelector('.bill-pagination').replaceChildren();
                    group.querySelector('.bill-pagination').classList.add('d-none');

                    rows.forEach(row => {
                        row.style.display = matchingRows.includes(row) ? '' : 'none';
                    });
                } else {
                    const currentPage = Number(group.dataset.currentPage || 1);
                    const startIndex = (currentPage - 1) * pageSize;
                    const endIndex = startIndex + pageSize;

                    rows.forEach(row => {
                        row.style.display = 'none';
                    });

                    matchingRows.slice(startIndex, endIndex).forEach(row => {
                        row.style.display = '';
                    });

                    renderPagination(group, matchingRows);
                }

                const groupHasBills = matchingRows.length > 0;

                group.classList.toggle('d-none', !groupHasBills);

                if (groupHasBills) {
                    anyVisibleBill = true;
                }
            });

            noBillsFound.classList.toggle('d-none', anyVisibleBill);
        }

        function resetFilters() {
            dateFrom.value = '';
            dateTo.value = '';

            clientCheckboxes.forEach(checkbox => {
                checkbox.checked = true;
            });

            fruitCheckboxes.forEach(checkbox => {
                checkbox.checked = true;
            });

            clientGroups.forEach(group => {
                group.dataset.currentPage = 1;
            });

            updateDropdownLabels();
            applyFilters();
        }

        function handleCheckboxChange() {
            clientGroups.forEach(group => {
                group.dataset.currentPage = 1;
            });

            updateDropdownLabels();
            applyFilters();
        }

        dateFrom.addEventListener('change', handleCheckboxChange);
        dateTo.addEventListener('change', handleCheckboxChange);

        clientCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', handleCheckboxChange);
        });

        fruitCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', handleCheckboxChange);
        });

        document.getElementById('selectAllClients').addEventListener('click', function() {
            clientCheckboxes.forEach(checkbox => {
                checkbox.checked = true;
            });

            handleCheckboxChange();
        });

        document.getElementById('clearClientsSelection').addEventListener('click', function() {
            clientCheckboxes.forEach(checkbox => {
                checkbox.checked = false;
            });

            handleCheckboxChange();
        });

        document.getElementById('selectAllFruits').addEventListener('click', function() {
            fruitCheckboxes.forEach(checkbox => {
                checkbox.checked = true;
            });

            handleCheckboxChange();
        });

        document.getElementById('clearFruitsSelection').addEventListener('click', function() {
            fruitCheckboxes.forEach(checkbox => {
                checkbox.checked = false;
            });

            handleCheckboxChange();
        });

        resetButton.addEventListener('click', resetFilters);

        updateDropdownLabels();
        applyFilters();

        initDeleteHandler('bill');
    });
</script>
