(function($) {
    $(document).ready(function() {
        const eventFilterControls = document.getElementById('event-filter-controls');

        if (eventFilterControls) {
            const eventFilterDialog = document.getElementById('event-filter-dialog');
            document.getElementById('open-event-filters').addEventListener('click', function() {
                eventFilterDialog.showModal();
            });
            eventFilterDialog.getElementsByClassName('modal-close')[0].addEventListener('click', function() {
                eventFilterDialog.close();
            });

            document.getElementById('event-filter-form').addEventListener('submit', function(e) {
                for (const control of this.elements) {
                    if (control.value === '') {
                        control.disabled = true;
                    }
                }
            });
        }
    });
})(jQuery);