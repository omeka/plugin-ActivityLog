document.addEventListener('DOMContentLoaded', function() {

    // Handle the event filters modal window.
    const eventFilterDialog = document.getElementById('event-filter-dialog');
    document.getElementById('open-event-filters').addEventListener('click', function() {
        eventFilterDialog.showModal();
    });
    eventFilterDialog.getElementsByClassName('modal-close')[0].addEventListener('click', function() {
        eventFilterDialog.close();
    });

    // Do not submit filters with empty values because they would always return no results.
    document.getElementById('event-filter-form').addEventListener('submit', function(e) {
        for (const control of this.elements) {
            if (control.value === '') {
                control.disabled = true;
            }
        }
    });

});
