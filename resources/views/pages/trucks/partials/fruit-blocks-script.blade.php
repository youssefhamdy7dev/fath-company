<script>
    document.addEventListener('DOMContentLoaded', function() {
        const fruitsContainer = document.getElementById('fruitsContainer');
        const addFruitBtn = document.getElementById('addFruitBtn');
        const templateElement = document.getElementById('fruitBlockTemplate');

        // Stop if the required elements are unavailable
        if (!fruitsContainer || !addFruitBtn || !templateElement) {
            return;
        }

        const template = templateElement.innerHTML.trim();

        // Get highest index from existing blocks
        function getCurrentMaxIndex() {
            return Array.from(fruitsContainer.querySelectorAll('.fruit-block'))
                .reduce((max, block) => {
                    const index = Number.parseInt(block.dataset.index, 10);

                    return Number.isNaN(index) ? max : Math.max(max, index);
                }, -1);
        }

        // Add new fruit section
        function addFruitBlock() {
            const nextIndex = getCurrentMaxIndex() + 1;

            const html = template
                .replace(/__INDEX__/g, nextIndex)
                .replace(/__LABEL__/g, nextIndex + 1);

            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = html.trim();

            const block = tempDiv.firstElementChild;

            if (block) {
                fruitsContainer.appendChild(block);
            }
        }

        // Ensure at least one block exists
        function ensureAtLeastOne() {
            if (fruitsContainer.querySelector('.fruit-block')) {
                return;
            }

            addFruitBlock();
        }

        // Re-index and re-label blocks
        function reindexFruitBlocks() {
            fruitsContainer.querySelectorAll('.fruit-block').forEach((block, index) => {
                block.dataset.index = index;

                const heading = block.querySelector('strong');

                if (heading) {
                    heading.textContent = 'الصنف رقم ' + (index + 1);
                }

                // Update all input/select names
                block.querySelectorAll('select, input').forEach(input => {
                    input.name = input.name.replace(/fruits\[\d+\]/, `fruits[${index}]`);
                });
            });
        }

        // Add button
        addFruitBtn.addEventListener('click', addFruitBlock);

        // Delete button (event delegation)
        fruitsContainer.addEventListener('click', function(event) {
            const removeButton = event.target.closest('.remove-fruit-btn');

            if (!removeButton || !fruitsContainer.contains(removeButton)) {
                return;
            }

            const block = removeButton.closest('.fruit-block');

            if (!block) {
                return;
            }

            block.remove();

            ensureAtLeastOne();
            reindexFruitBlocks();
        });

        ensureAtLeastOne();
    });
</script>
