<script>
    document.addEventListener('DOMContentLoaded', function() {

        const fruitsContainer = document.getElementById('fruitsContainer');
        const addFruitBtn = document.getElementById('addFruitBtn');
        const templateElement = document.getElementById('fruitBlockTemplate');

        if (!fruitsContainer || !addFruitBtn || !templateElement) return;

        const template = templateElement.innerHTML.trim();

        function getCurrentMaxIndex() {
            return Array.from(
                fruitsContainer.querySelectorAll('.fruit-block')
            ).reduce((max, block) => {
                const index = Number.parseInt(block.dataset.index, 10);

                return Number.isNaN(index) ?
                    max :
                    Math.max(max, index);
            }, -1);
        }

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

        function ensureAtLeastOne() {
            if (!fruitsContainer.querySelector('.fruit-block')) {
                addFruitBlock();
            }
        }

        function reindexFruitBlocks() {
            fruitsContainer.querySelectorAll('.fruit-block').forEach((block, index) => {

                block.dataset.index = index;

                const heading = block.querySelector('strong');

                if (heading) {
                    heading.textContent = 'الصنف رقم ' + (index + 1);
                }

                block.querySelectorAll('select, input').forEach(input => {
                    input.name = input.name.replace(
                        /^fruits\[\d+\]/,
                        `fruits[${index}]`
                    );
                });
            });
        }

        addFruitBtn.addEventListener('click', addFruitBlock);

        fruitsContainer.addEventListener('click', function(event) {

            const removeButton = event.target.closest('.remove-fruit-btn');

            if (!removeButton) return;

            removeButton.closest('.fruit-block')?.remove();

            ensureAtLeastOne();

            reindexFruitBlocks();
        });

        ensureAtLeastOne();

    });
</script>
