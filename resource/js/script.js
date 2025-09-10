document.addEventListener('DOMContentLoaded', () => {

    // --- LOGIC FOR SIDEBAR (Runs on any page with a sidebar) ---
    const sidebar = document.querySelector(".sidebar");
    if (sidebar) {
        const toggle = document.querySelector(".toggle");
        if (toggle) {
            toggle.addEventListener("click", () => {
                sidebar.classList.toggle("close");
            });
        }
    }

    // --- LOGIC FOR "VIEW PRODUCT" POPUPS (For user-search.php) ---
    const viewButtons = document.querySelectorAll('.btn-view');
    const equipmentPopup = document.getElementById('equipment-popup');
    const chemicalPopup = document.getElementById('chemical-popup');
    
    // This code only runs if there are "View" buttons on the page
    if (viewButtons.length > 0) {
        const allPopups = document.querySelectorAll('.product-popup');

        viewButtons.forEach(button => {
            button.addEventListener('click', () => {
                const productType = button.getAttribute('data-type');
                const productId = button.getAttribute('data-product-id');
                const productImage = button.getAttribute('data-image');
                const cardBody = button.closest('.card-body');
                const productName = cardBody.querySelector('.card-text').textContent;
                const productStock = cardBody.querySelector('.stock-text').textContent;

                let popupToShow = null;
                if (productType === 'Equipment' && equipmentPopup) {
                    popupToShow = equipmentPopup;
                    popupToShow.querySelector('.equipment-title').textContent = productName;
                    popupToShow.querySelector('.stock-info').textContent = productStock;
                    popupToShow.querySelector('.popup-image').src = productImage;
                    popupToShow.querySelector('#equipment-popup-product-id').value = productId;
                } else if ((productType === 'Chemical' || productType === 'Supplies' || productType === 'Specimen' || productType === 'Models') && chemicalPopup) {
                    popupToShow = chemicalPopup;
                    popupToShow.querySelector('.chemical-title').textContent = productName;
                    popupToShow.querySelector('.stock-info').textContent = productStock;
                    popupToShow.querySelector('.popup-image').src = productImage;
                    popupToShow.querySelector('#chemical-popup-product-id').value = productId;
}

                if (popupToShow) {
                    popupToShow.classList.add('show');
                }
            });
        });

        // Add close functionality to the view popups
        allPopups.forEach(popup => {
            const closeBtn = popup.querySelector('.close-btn');
            if (closeBtn) {
                closeBtn.addEventListener('click', () => popup.classList.remove('show'));
            }
            popup.addEventListener('click', (event) => {
                if (event.target === popup) {
                    popup.classList.remove('show');
                }
            });
        });
    }

    // --- LOGIC FOR "EDIT ITEM" POPUP (For cart.php) ---
    const editPopupModal = document.getElementById('edit-popup');
    
    // This code only runs if the edit popup exists on the page
    if (editPopupModal) {
        editPopupModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const itemId = button.getAttribute('data-item-id');
            const itemName = button.getAttribute('data-item-name');
            const itemAmount = button.getAttribute('data-item-amount');

            const modalTitle = editPopupModal.querySelector('.modal-title');
            const itemIdInput = editPopupModal.querySelector('#edit-item-id');
            const itemNameInput = editPopupModal.querySelector('#edit-item-name');
            const itemAmountInput = editPopupModal.querySelector('#edit-item-amount');

            modalTitle.textContent = 'Edit ' + itemName;
            itemIdInput.value = itemId;
            itemNameInput.value = itemName;
            itemAmountInput.value = itemAmount;
        });
    }
});