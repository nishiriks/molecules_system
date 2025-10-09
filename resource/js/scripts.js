document.addEventListener('DOMContentLoaded', function () {
    const editPopup = document.getElementById('edit-popup');
    if (editPopup) {
        // This is the standard Bootstrap 5 event listener for a modal
        editPopup.addEventListener('show.bs.modal', function (event) {

            // Get the button that triggered the modal
            const button = event.relatedTarget;

            // Extract the data from the button's data-* attributes
            const itemId = button.getAttribute('data-item-id');
            const itemName = button.getAttribute('data-item-name');
            const itemAmount = button.getAttribute('data-item-amount');

            // Find the elements inside the modal
            const modalTitle = editPopup.querySelector('.modal-title');
            const itemIdInput = editPopup.querySelector('#edit-item-id');
            const itemNameInput = editPopup.querySelector('#edit-item-name');
            const itemAmountInput = editPopup.querySelector('#edit-item-amount');

            // Update the modal's content with the item's data
            modalTitle.textContent = 'Edit ' + itemName;
            itemIdInput.value = itemId;
            itemNameInput.value = itemName;
            itemAmountInput.value = itemAmount;
        });
    }
});