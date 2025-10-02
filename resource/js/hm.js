document.addEventListener('DOMContentLoaded', function() {
    // Delete Handler
    const deleteModal = document.getElementById('deleteModal');
    deleteModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const holidayId = button.getAttribute('data-holiday-id');
        const holidayName = button.getAttribute('data-holiday-name');
        
        document.getElementById('deleteHolidayId').value = holidayId;
        document.getElementById('deleteHolidayName').textContent = holidayName;
    });

    // Edit Handler
    const editModal = document.getElementById('editModal');
    editModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const holidayId = button.getAttribute('data-holiday-id');
        const holidayName = button.getAttribute('data-holiday-name');
        const holidayType = button.getAttribute('data-holiday-type');
        const dateFrom = button.getAttribute('data-date-from');
        const dateTo = button.getAttribute('data-date-to');
        
        document.getElementById('editHolidayId').value = holidayId;
        document.getElementById('edit_holiday_name').value = holidayName;
        document.getElementById('edit_holiday_type').value = holidayType;
        document.getElementById('edit_date_from').value = dateFrom;
        document.getElementById('edit_date_to').value = dateTo;
    });
});