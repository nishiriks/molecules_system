document.addEventListener('DOMContentLoaded', () => {

    // --- LOGIC FOR SIDEBAR ---
    const sidebar = document.querySelector(".sidebar");
    if (sidebar) {
        const toggle = document.querySelector(".toggle");
        if (toggle) {
            toggle.addEventListener("click", () => sidebar.classList.toggle("close"));
        }
    }

    // --- CACHE ALL POPUP ELEMENTS ONCE ---
    const equipmentPopup = document.getElementById('equipment-popup');
    const chemicalPopup = document.getElementById('chemical-popup');
    const editEquipmentPopup = document.getElementById('edit-equipment-popup');
    const editChemicalPopup = document.getElementById('edit-chemical-popup');

    // --- A SINGLE, UNIFIED LISTENER FOR ALL POPUP ACTIONS ---
    document.body.addEventListener('click', (event) => {
        
        const viewButton = event.target.closest('.btn-view');
        const editButton = event.target.closest('.edit-button');
        const closeTrigger = event.target.closest('.close-btn, .edit-close-btn');
        const popupOverlay = event.target.closest('.product-popup, .edit-popup');
        const quantityBtn = event.target.closest('.quantity-btn');

        // --- ACTION 1: OPEN "VIEW" POPUP ---
        if (viewButton) {
            const productId = viewButton.dataset.productId;
            const productType = viewButton.dataset.type;
            const productStockValue = parseInt(viewButton.dataset.stock, 10); // Get raw stock number
            const cardBody = viewButton.closest('.card-body');
            const productName = cardBody.querySelector('.card-text').textContent;
            const productStock = cardBody.querySelector('.stock-text').textContent;
            let popupToShow = null;

            if (productType.toLowerCase() === 'equipment' || productType.toLowerCase() === 'equipments') {
                popupToShow = equipmentPopup;
                if(popupToShow) popupToShow.querySelector('.equipment-title').textContent = productName;
            } else {
                popupToShow = chemicalPopup;
                if(popupToShow) popupToShow.querySelector('.chemical-title').textContent = productName;
            }
            
            if (popupToShow) {
                // Store the max stock on the popup itself for easy access
                popupToShow.dataset.maxStock = productStockValue;

                popupToShow.querySelector('.popup-product-type').textContent = productType;
                popupToShow.dataset.editingProductId = productId;
                popupToShow.querySelector('.stock-info').textContent = productStock;
                popupToShow.querySelector('.popup-image').src = viewButton.dataset.image;
                popupToShow.querySelector('.popup-image').alt = productName;

                const productIdInput = popupToShow.querySelector('input[name="product_id"]');
                if (productIdInput) {
                    productIdInput.value = productId;
                }

                // Set max value on the quantity input and reset its value
                const quantityInput = popupToShow.querySelector('.quantity-input');
                if (quantityInput) {
                    quantityInput.value = 1; // Reset to 1
                    quantityInput.max = productStockValue; // Set the max attribute
                }
                
                popupToShow.classList.add('show');
            }
            return;
        }

        // --- ACTION 2: OPEN "EDIT" POPUP (Admin only) ---
        if (editButton) {
            // This part of the logic remains unchanged
            const viewPopup = editButton.closest('.product-popup');
            if (!viewPopup) return;
            // ... (rest of the edit logic)
        }

        // --- ACTION 3: CLOSE ANY POPUP ---
        if (closeTrigger || (popupOverlay && event.target === popupOverlay)) {
            if (popupOverlay) {
                popupOverlay.classList.remove('show');
            }
        }

        // --- ACTION 4: HANDLE QUANTITY CHANGES ---
        if (quantityBtn) {
            const popup = quantityBtn.closest('.product-popup');
            const maxStock = popup && popup.dataset.maxStock ? parseInt(popup.dataset.maxStock, 10) : Infinity;
            const input = quantityBtn.parentElement.querySelector('.quantity-input');
            if (!input) return;

            let currentValue = parseInt(input.value, 10);
            
            if (quantityBtn.id.includes('increment')) {
                if (currentValue < maxStock) {
                    currentValue++;
                }
            } else if (quantityBtn.id.includes('decrement')) {
                currentValue = Math.max(1, currentValue - 1); 
            }
            
            input.value = currentValue;
        }
    });
});