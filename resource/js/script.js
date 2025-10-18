document.addEventListener('DOMContentLoaded', () => {

    // --- LOGIC FOR SIDEBAR ---
    const sidebar = document.querySelector(".sidebar");
    if (sidebar) {
        const toggle = document.querySelector(".toggle");
        if (toggle) {
            toggle.addEventListener("click", () => sidebar.classList.toggle("close"));
        }
    }

    // --- LOGIC FOR LIVE PRODUCT SEARCH ---
    const searchInput = document.querySelector('.search-input');
    const productCols = document.querySelectorAll('.product-col');

    if (searchInput && productCols.length > 0) {
        const filterProducts = () => {
            const searchTerm = searchInput.value.trim().toLowerCase();
            productCols.forEach(col => {
                const productName = col.querySelector('.card-text').textContent.toLowerCase();
                if (productName.includes(searchTerm)) {
                    col.style.display = ''; 
                } else {
                    col.style.display = 'none'; 
                }
            });
        };

        searchInput.addEventListener('input', filterProducts);
        searchInput.addEventListener('keydown', (event) => {
            if (event.key === 'Enter') {
                event.preventDefault(); 
                const searchTerm = searchInput.value.trim();
                
                if (searchTerm) {
                    window.location.href = `admin-search.php?search=${encodeURIComponent(searchTerm)}`;
                }
            }
        });
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
        const deleteButton = event.target.closest('.delete-button');
        const closeTrigger = event.target.closest('.close-btn, .edit-close-btn');
        const popupOverlay = event.target.closest('.product-popup, .edit-popup');
        const quantityBtn = event.target.closest('.quantity-btn');

        // --- ACTION 1: OPEN "VIEW" POPUP ---
        if (viewButton) {
            const productId = viewButton.dataset.productId;
            const productType = viewButton.dataset.type;
            const productStockValue = parseInt(viewButton.dataset.stock, 10);
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

                const quantityInput = popupToShow.querySelector('.quantity-input');
                if (quantityInput) {
                    quantityInput.value = 1;
                    quantityInput.max = productStockValue;
                    quantityInput.setAttribute('min', '1');
                }
                
                popupToShow.classList.add('show');
            }
            return;
        }

        // --- ACTION 2: OPEN "EDIT" POPUP (Admin only) ---
        if (editButton) {
            const viewPopup = editButton.closest('.product-popup');
            if (!viewPopup) return;
            
            const productId = viewPopup.dataset.editingProductId;
            let editPopupToShow = null;

            if (viewPopup.id === 'equipment-popup' && editEquipmentPopup) {
                editPopupToShow = editEquipmentPopup;
                const title = viewPopup.querySelector('.equipment-title').textContent;
                const stockText = viewPopup.querySelector('.stock-info').textContent;
                const stockValue = stockText.replace(/Stock:\s*/, '').trim().split(' ')[0];
                
                editPopupToShow.querySelector('.edit-popup-title').textContent = `Edit: ${title}`;
                editPopupToShow.querySelector('#edit-equipment-title').value = title;
                editPopupToShow.querySelector('#edit-equipment-stock').value = stockValue;

            } else if (viewPopup.id === 'chemical-popup' && editChemicalPopup) {
                editPopupToShow = editChemicalPopup;
                const title = viewPopup.querySelector('.chemical-title').textContent;
                const stockText = viewPopup.querySelector('.stock-info').textContent;
                const stockString = stockText.replace(/Stock:\s*/, '').trim();
                const match = stockString.match(/^([\d.]+)\s*(\w+)?$/);
                const stockValue = match ? match[1] : '';
                const stockUnit = match ? match[2] : '';

                editPopupToShow.querySelector('.edit-popup-title').textContent = `Edit: ${title}`;
                editPopupToShow.querySelector('#edit-chemical-title').value = title;
                editPopupToShow.querySelector('#edit-chemical-stock').value = stockValue;
                const unitSelect = editPopupToShow.querySelector('#edit-chemical-stock-unit');
                if (unitSelect) unitSelect.value = stockUnit || "";
            }

            if (editPopupToShow) {
                const hiddenIdInput = editPopupToShow.querySelector('input[name="product_id"]');
                if (hiddenIdInput) {
                    hiddenIdInput.value = productId;
                }
                
                viewPopup.classList.remove('show');
                editPopupToShow.classList.add('show');
            }
            return;
        }

        // --- ACTION 3: HANDLE DELETE BUTTON (Admin only) ---
        if (deleteButton) {
            const popup = deleteButton.closest('.product-popup');
            const productId = popup.dataset.editingProductId;
            const productNameElement = popup.querySelector('.equipment-title') || popup.querySelector('.chemical-title');
            const productName = productNameElement ? productNameElement.textContent : 'this item';

            // Show a confirmation dialog before deleting
            if (confirm(`Are you sure you want to delete "${productName}"? This action cannot be undone.`)) {
                // If confirmed, create a temporary form to submit the product ID
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'deleteProduct.php';
                
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'product_id';
                hiddenInput.value = productId;

                form.appendChild(hiddenInput);
                document.body.appendChild(form);
                form.submit();
            }
        }

        // --- ACTION 4: CLOSE ANY POPUP ---
        if (closeTrigger || (popupOverlay && event.target === popupOverlay)) {
            if (popupOverlay) {
                popupOverlay.classList.remove('show');
            }
        }

        // --- ACTION 5: HANDLE QUANTITY CHANGES ---
        if (quantityBtn) {
            const popup = quantityBtn.closest('.product-popup');
            const maxStock = popup && popup.dataset.maxStock ? parseInt(popup.dataset.maxStock, 10) : Infinity;
            const input = quantityBtn.parentElement.querySelector('.quantity-input');
            if (!input) return;

            let currentValue = parseInt(input.value, 10);
            
            if (quantityBtn.id.includes('increment')) {
                if (currentValue < maxStock) {
                    currentValue++;
                } else {
                    alert(`Cannot exceed maximum available stock of ${maxStock} units`);
                }
            } else if (quantityBtn.id.includes('decrement')) {
                currentValue = Math.max(1, currentValue - 1);
            }
            
            input.value = currentValue;
        }
    });

    // --- CART FUNCTIONALITY ---
    initializeCartFunctionality();
});

// Cart-specific functionality
function initializeCartFunctionality() {
    // Edit modal show event
    const editModal = document.getElementById('edit-popup');
    if (editModal) {
        editModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const itemId = button.getAttribute('data-item-id');
            const itemName = button.getAttribute('data-item-name');
            const currentAmount = button.getAttribute('data-item-amount');
            const maxStock = button.getAttribute('data-max-stock');
            
            // Set the form values
            document.getElementById('edit-item-id').value = itemId;
            document.getElementById('edit-item-name').value = itemName;
            document.getElementById('edit-item-amount').value = currentAmount;
            document.getElementById('max-stock-display').textContent = maxStock;
            
            // Set validation constraints
            const amountInput = document.getElementById('edit-item-amount');
            amountInput.setAttribute('min', '1');
            amountInput.setAttribute('max', maxStock);
            
            // Add input event listener for real-time validation
            amountInput.addEventListener('input', function() {
                validateCartAmount(this);
            });
        });
    }
    
    // Form validation for edit form
    const editForm = document.getElementById('edit-form');
    if (editForm) {
        editForm.addEventListener('submit', function(event) {
            const amountInput = document.getElementById('edit-item-amount');
            const amount = parseInt(amountInput.value);
            const maxStock = parseInt(amountInput.getAttribute('max'));
            
            if (amount < 1) {
                event.preventDefault();
                alert('Amount cannot be less than 1');
                amountInput.focus();
                return false;
            }
            
            if (amount > maxStock) {
                event.preventDefault();
                alert(`Cannot exceed maximum available stock of ${maxStock} units`);
                amountInput.focus();
                return false;
            }
            
            if (isNaN(amount)) {
                event.preventDefault();
                alert('Please enter a valid number');
                amountInput.focus();
                return false;
            }
        });
    }
}

function validateCartAmount(input) {
    const value = parseInt(input.value);
    const maxStock = parseInt(input.getAttribute('max'));
    const feedback = document.getElementById('amount-feedback');
    
    if (isNaN(value)) {
        input.classList.add('is-invalid');
        feedback.textContent = 'Please enter a valid number';
    } else if (value < 1) {
        input.classList.add('is-invalid');
        feedback.textContent = 'Amount must be at least 1';
    } else if (value > maxStock) {
        input.classList.add('is-invalid');
        feedback.textContent = `Cannot exceed maximum available stock of ${maxStock} units`;
    } else {
        input.classList.remove('is-invalid');
        input.classList.add('is-valid');
        feedback.textContent = '';
    }
}