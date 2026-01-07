    <button id="scrollToTopBtn"
        class="btn btn-dark shadow-lg rounded-circle position-fixed d-none d-flex align-items-center justify-content-center"
        style="bottom: 20px; left: 20px; z-index: 1050; width: 54px; height: 54px;" aria-label="Scroll to top">
        <i class="bi bi-arrow-up"></i>
    </button>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const scrollBtn = document.getElementById('scrollToTopBtn');

            window.addEventListener('scroll', () => {
                if (window.scrollY > 300) {
                    scrollBtn.classList.remove('d-none');
                } else {
                    scrollBtn.classList.add('d-none');
                }
            });

            scrollBtn.addEventListener('click', () => {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });
        });
    </script>
