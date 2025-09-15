document.addEventListener('DOMContentLoaded', () => {
    const body = document.querySelector("body");
    const sidebar = body.querySelector(".sidebar");
    const toggle = body.querySelector(".toggle");

    if (toggle && sidebar) {
        toggle.addEventListener("click", () => {
            sidebar.classList.toggle("close");
        });
    }

    // --- General View and Edit Pop-up Logic ---
    const viewButtons = document.querySelectorAll('.btn-view');
    const equipmentPopup = document.getElementById('equipment-popup');
    const chemicalPopup = document.getElementById('chemical-popup');
    const closeBtns = document.querySelectorAll('.product-popup .close-btn');
    const popups = document.querySelectorAll('.product-popup');

    const editButtons = document.querySelectorAll('.edit-button');
    const editEquipmentPopup = document.getElementById('edit-equipment-popup');
    const editChemicalPopup = document.getElementById('edit-chemical-popup');
    
    const editEquipmentForm = document.getElementById('edit-equipment-form');
    const editEquipmentTitleInput = document.getElementById('edit-equipment-title');
    const editEquipmentStockInput = document.getElementById('edit-equipment-stock');

    const editChemicalForm = document.getElementById('edit-chemical-form');
    const editChemicalTitleInput = document.getElementById('edit-chemical-title');
    const editChemicalStockInput = document.getElementById('edit-chemical-stock');
    const editChemicalStockUnitSelect = document.getElementById('edit-chemical-stock-unit');
    
    // CHANGE: These will now hold references to the main card's elements
    let activeCardTitleElement = null;
    let activeCardStockElement = null;
    let activeProductType = null;

    viewButtons.forEach(button => {
        button.addEventListener('click', () => {
            const card = button.closest('.card'); // Get the parent card of the clicked button
            
            // Get data directly from the elements within the card
            const productType = button.getAttribute('data-type');
            const productName = card.querySelector('.card-body .card-text:first-of-type').textContent;
            const productStock = card.querySelector('.stock-text').textContent.replace('Stock: ', '');
            const productImage = card.querySelector('.card-img-top').src;
            
            // CHANGE: Store references to the actual card's elements
            activeCardTitleElement = card.querySelector('.card-body .card-text:first-of-type');
            activeCardStockElement = card.querySelector('.stock-text');
            activeProductType = productType;

            // Hide all other popups
            popups.forEach(p => p.classList.remove('show'));

            // Populate and show the correct pop-up
            if (productType === 'equipment') {
                equipmentPopup.querySelector('.equipment-title').textContent = productName;
                equipmentPopup.querySelector('.stock-info').textContent = 'Stock: ' + productStock;
                equipmentPopup.querySelector('.popup-image').src = productImage;
                equipmentPopup.classList.add('show');
            } else if (productType === 'chemical') {
                chemicalPopup.querySelector('.chemical-title').textContent = productName;
                chemicalPopup.querySelector('.stock-info').textContent = 'Stock: ' + productStock;
                chemicalPopup.querySelector('.popup-image').src = productImage;
                chemicalPopup.classList.add('show');
            }
        });
    });

    closeBtns.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.target.closest('.product-popup').classList.remove('show');
            // Clear the stored references when the popup closes
            activeCardTitleElement = null;
            activeCardStockElement = null;
            activeProductType = null;
        });
    });

    popups.forEach(popup => {
        popup.addEventListener('click', (event) => {
            if (event.target === popup) {
                popup.classList.remove('show');
                // Clear the stored references when the popup closes
                activeCardTitleElement = null;
                activeCardStockElement = null;
                activeProductType = null;
            }
        });
    });

    editButtons.forEach(editButton => {
        editButton.addEventListener('click', () => {
            if (activeCardTitleElement && activeCardStockElement) {
                const currentTitle = activeCardTitleElement.textContent;
                const fullStock = activeCardStockElement.textContent.replace('Stock: ', '');
                
                const allEditPopups = document.querySelectorAll('.edit-popup');
                allEditPopups.forEach(p => p.style.display = 'none');
                
                if (activeProductType === 'equipment') {
                    editEquipmentTitleInput.value = currentTitle;
                    editEquipmentStockInput.value = fullStock;
                    editEquipmentPopup.style.display = 'block';
                } else if (activeProductType === 'chemical') {
                    const stockParts = fullStock.split(' ');
                    editChemicalTitleInput.value = currentTitle;
                    editChemicalStockInput.value = stockParts[0];
                    editChemicalStockUnitSelect.value = stockParts[1] || '';
                    editChemicalPopup.style.display = 'block';
                }
            }
        });
    });

    const closeEditBtns = document.querySelectorAll('.edit-popup .close-btn');
    closeEditBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            btn.closest('.edit-popup').style.display = 'none';
        });
    });

    window.addEventListener('click', (event) => {
        const allEditPopups = document.querySelectorAll('.edit-popup');
        allEditPopups.forEach(popup => {
            if (event.target === popup) {
                popup.style.display = 'none';
            }
        });
    });

    // --- Quantity controls logic for both popups ---
    const setupQuantityControls = (popup, type) => {
        const decrementBtn = popup.querySelector(`#${type}-decrement-btn`);
        const incrementBtn = popup.querySelector(`#${type}-increment-btn`);
        const quantityInput = popup.querySelector(`#${type}-quantity-input`);
        const requestBtn = popup.querySelector('.request-button');

        if (decrementBtn) {
            decrementBtn.addEventListener('click', () => {
                let value = parseInt(quantityInput.value);
                if (value > 1) {
                    quantityInput.value = value - 1;
                }
            });
        }
        
        if (incrementBtn) {
            incrementBtn.addEventListener('click', () => {
                let value = parseInt(quantityInput.value);
                quantityInput.value = value + 1;
            });
        }

        if (quantityInput) {
            quantityInput.addEventListener('change', () => {
                if (parseInt(quantityInput.value) < 1 || isNaN(parseInt(quantityInput.value))) {
                    quantityInput.value = 1;
                }
            });
        }

        if (requestBtn) {
            requestBtn.addEventListener('click', () => {
                const requestedQuantity = quantityInput.value;
                const productName = popup.querySelector('.popup-title').textContent;
                console.log(`Requesting ${requestedQuantity} of ${productName}.`);
                alert(`Request for ${requestedQuantity} of ${productName} submitted!`);
            });
        }
    };
    
    setupQuantityControls(equipmentPopup, 'equipment');
    setupQuantityControls(chemicalPopup, 'chemical');
});
