document.addEventListener('DOMContentLoaded', function () {
    const editPopup = document.getElementById('edit-popup');
    if (editPopup) {
        editPopup.addEventListener('show.bs.modal', function (event) {
            
            const button = event.relatedTarget; 
            const itemId = button.getAttribute('data-item-id');
            const itemName = button.getAttribute('data-item-name');
            const itemAmount = button.getAttribute('data-item-amount');
            const maxStock = button.getAttribute('data-max-stock');

            const modalTitle = editPopup.querySelector('.modal-title');
            const itemIdInput = editPopup.querySelector('#edit-item-id');
            const itemNameInput = editPopup.querySelector('#edit-item-name');
            const itemAmountInput = editPopup.querySelector('#edit-item-amount');
            const maxStockDisplay = editPopup.querySelector('#max-stock-display');
            const amountFeedback = editPopup.querySelector('#amount-feedback');

            // Update the modal's content
            modalTitle.textContent = 'Edit ' + itemName;
            itemIdInput.value = itemId;
            itemNameInput.value = itemName;
            itemAmountInput.value = itemAmount;
            maxStockDisplay.textContent = maxStock;

            // Set max attribute to enforce client-side validation
            itemAmountInput.setAttribute('max', maxStock);

            // Add real-time validation
            itemAmountInput.addEventListener('input', function() {
                const enteredAmount = parseInt(this.value);
                const availableStock = parseInt(maxStock);
                
                if (enteredAmount > availableStock) {
                    this.classList.add('is-invalid');
                    amountFeedback.textContent = `Cannot exceed available stock of ${availableStock} units`;
                } else {
                    this.classList.remove('is-invalid');
                    amountFeedback.textContent = '';
                }
            });

            // Validate on form submission
            const editForm = editPopup.querySelector('#edit-form');
            editForm.addEventListener('submit', function(e) {
                const enteredAmount = parseInt(itemAmountInput.value);
                const availableStock = parseInt(maxStock);
                
                if (enteredAmount > availableStock) {
                    e.preventDefault();
                    itemAmountInput.classList.add('is-invalid');
                    amountFeedback.textContent = `Cannot exceed available stock of ${availableStock} units`;
                    itemAmountInput.focus();
                }
            });
        });
    }
});