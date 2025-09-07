document.addEventListener('DOMContentLoaded', () => {

    // --- Sidebar Toggle Logic (remains unchanged) ---
    const body = document.querySelector("body");
    const sidebar = body.querySelector(".sidebar");
    const toggle = body.querySelector(".toggle");

    if (toggle && sidebar) {
        toggle.addEventListener("click", () => {
            sidebar.classList.toggle("close");
        });
    }

    // --- General View and Edit Pop-up Logic (remains unchanged) ---
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
    
    let activeTitleElement = null;
    let activeStockElement = null;
    let activeProductType = null;

    viewButtons.forEach(button => {
        button.addEventListener('click', () => {
            const productType = button.getAttribute('data-type');
            const productName = button.getAttribute('data-name');
            const productStock = button.getAttribute('data-stock');
            const productImage = button.getAttribute('data-image');
            
            popups.forEach(p => p.classList.remove('show'));
            
            activeProductType = productType;

            if (productType === 'equipment') {
                equipmentPopup.querySelector('.equipment-title').textContent = productName;
                equipmentPopup.querySelector('.stock-info').textContent = 'Stock: ' + productStock;
                equipmentPopup.querySelector('.popup-image').src = productImage;
                activeTitleElement = equipmentPopup.querySelector('.equipment-title');
                activeStockElement = equipmentPopup.querySelector('.stock-info');
                equipmentPopup.classList.add('show');
            } else if (productType === 'chemical') {
                chemicalPopup.querySelector('.chemical-title').textContent = productName;
                chemicalPopup.querySelector('.stock-info').textContent = 'Stock: ' + productStock;
                chemicalPopup.querySelector('.popup-image').src = productImage;
                activeTitleElement = chemicalPopup.querySelector('.chemical-title');
                activeStockElement = chemicalPopup.querySelector('.stock-info');
                chemicalPopup.classList.add('show');
            }
        });
    });

    closeBtns.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.target.closest('.product-popup').classList.remove('show');
            activeTitleElement = null;
            activeStockElement = null;
            activeProductType = null;
        });
    });

    popups.forEach(popup => {
        popup.addEventListener('click', (event) => {
            if (event.target === popup) {
                popup.classList.remove('show');
                activeTitleElement = null;
                activeStockElement = null;
                activeProductType = null;
            }
        });
    });

    editButtons.forEach(editButton => {
        editButton.addEventListener('click', () => {
            if (activeTitleElement && activeStockElement) {
                const currentTitle = activeTitleElement.textContent;
                const fullStock = activeStockElement.textContent.replace('Stock: ', '');
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
// for equipment and chemical pop-up
    editEquipmentForm.addEventListener('submit', (event) => {
        event.preventDefault();
        const newTitle = editEquipmentTitleInput.value;
        const newStock = editEquipmentStockInput.value;
        if (activeTitleElement) activeTitleElement.textContent = newTitle;
        if (activeStockElement) activeStockElement.textContent = `Stock: ${newStock}`;
        editEquipmentPopup.style.display = 'none';
        console.log(`Saved Equipment: Title: ${newTitle}, Stock: ${newStock}`);
    });

    editChemicalForm.addEventListener('submit', (event) => {
        event.preventDefault();
        const newTitle = editChemicalTitleInput.value;
        const newStock = editChemicalStockInput.value;
        const newUnit = editChemicalStockUnitSelect.value;
        if (activeTitleElement) activeTitleElement.textContent = newTitle;
        if (activeStockElement) {
            if (newUnit === "" || newUnit === null) {
                activeStockElement.textContent = `Stock: ${newStock}`;
            } else {
                activeStockElement.textContent = `Stock: ${newStock} ${newUnit}`;
            }
        }
        editChemicalPopup.style.display = 'none';
        console.log(`Saved Chemical: Title: ${newTitle}, Stock: ${newStock}, Unit: ${newUnit}`);
    });
});
