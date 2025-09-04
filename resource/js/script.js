// for toggle nav-bar 2 
const body = document.querySelector("body"),
      sidebar = body.querySelector(".sidebar"),
      toggle = body.querySelector(".toggle"),
      searchBtn = body.querySelector(".search-box");


toggle.addEventListener("click", () => {
    sidebar.classList.toggle("close");
});

// For pop-up

document.addEventListener('DOMContentLoaded', () => {
    const viewButtons = document.querySelectorAll('.btn-view');
    const equipmentPopup = document.getElementById('equipment-popup');
    const chemicalPopup = document.getElementById('chemical-popup');

    viewButtons.forEach(button => {
        button.addEventListener('click', () => {
            // Read product information from the data attributes
            const productType = button.getAttribute('data-type');
            const productName = button.getAttribute('data-name');
            const productStock = button.getAttribute('data-stock');
            const productImage = button.getAttribute('data-image');

            if (productType === 'equipment') {
                // Populate the equipment pop-up with dynamic data
                equipmentPopup.querySelector('.equipment-title').textContent = productName;
                equipmentPopup.querySelector('.stock-info').textContent = 'Stock: ' + productStock;
                equipmentPopup.querySelector('.popup-image').src = productImage;
                
                // Show the equipment pop-up
                equipmentPopup.classList.add('show');
            } else if (productType === 'chemical') {
                // Populate the chemical pop-up with dynamic data
                chemicalPopup.querySelector('.chemical-title').textContent = productName;
                chemicalPopup.querySelector('.stock-info').textContent = 'Stock: ' + productStock;
                chemicalPopup.querySelector('.popup-image').src = productImage;
                
                // Show the chemical pop-up
                chemicalPopup.classList.add('show');
            }
        });
    });

    const closeBtns = document.querySelectorAll('.product-popup .close-btn');
    closeBtns.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.target.closest('.product-popup').classList.remove('show');
        });
    });

    const popups = document.querySelectorAll('.product-popup');
    popups.forEach(popup => {
        popup.addEventListener('click', (event) => {
            if (event.target === popup) {
                popup.classList.remove('show');
            }
        });
    });
});





