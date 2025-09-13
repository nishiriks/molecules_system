document.addEventListener('DOMContentLoaded', () => {
        const body = document.querySelector("body");
        const sidebar = body.querySelector(".sidebar");
        const toggle = body.querySelector(".toggle");
        const content = body.querySelector(".content");

        // Sidebar toggle logic
        if (toggle && sidebar && content) {
            toggle.addEventListener("click", () => {
                sidebar.classList.toggle("close");
                content.classList.toggle('content-shift');
            });
        }
        
        // Dynamic class for content shift
        const style = document.createElement('style');
        style.innerHTML = `
            .content-shift {
                left: 73px;
                width: calc(100% - 73px);
            }
        `;
        document.head.appendChild(style);

        // General View Pop-up Logic
        const viewButtons = document.querySelectorAll('.btn-view');
        const equipmentPopup = document.getElementById('equipment-popup');
        const chemicalPopup = document.getElementById('chemical-popup');
        const closeBtns = document.querySelectorAll('.product-popup .close-btn');
        const popups = document.querySelectorAll('.product-popup');

        viewButtons.forEach(button => {
            button.addEventListener('click', () => {
                const productType = button.getAttribute('data-type');
                const productName = button.getAttribute('data-name');
                const productStock = button.getAttribute('data-stock');
                const productImage = button.getAttribute('data-image');

                popups.forEach(p => p.classList.remove('show'));

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
            btn.addEventListener('click', () => {
                btn.closest('.product-popup').classList.remove('show');
            });
        });

        popups.forEach(popup => {
            popup.addEventListener('click', (event) => {
                if (event.target === popup) {
                    popup.classList.remove('show');
                }
            });
        });

        // Quantity controls logic for both popups
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
                    // Log the request to the console
                    console.log(`Requesting ${requestedQuantity} of ${productName}.`);
                    alert(`Request for ${requestedQuantity} of ${productName} submitted!`);
                });
            }
        };
        
        setupQuantityControls(equipmentPopup, 'equipment');
        setupQuantityControls(chemicalPopup, 'chemical');
    });