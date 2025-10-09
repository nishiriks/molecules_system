document.addEventListener('DOMContentLoaded', () => {
    const editPopup = document.getElementById('edit-popup');
    const closeBtn = editPopup.querySelector('.close-btn');
    const editForm = document.getElementById('edit-form');
    const itemNameInput = document.getElementById('edit-item-name');
    const itemAmountInput = document.getElementById('edit-item-amount');

    const cartEditButtons = document.querySelectorAll('.cart-card-item .edit-btn');

    let activeCartItemCard = null;

    const showPopup = () => {
        editPopup.classList.add('show');
    };

    const hidePopup = () => {
        editPopup.classList.remove('show');
    };

    cartEditButtons.forEach(button => {
        button.addEventListener('click', () => {
            const parentCard = button.closest('.cart-card-item');
            activeCartItemCard = parentCard;

            const itemName = parentCard.querySelector('.item-name').textContent;
            const itemAmount = parentCard.querySelector('.item-amount').textContent;

            itemNameInput.value = itemName;
            itemAmountInput.value = itemAmount.replace('Amount: ', '').trim();

            showPopup();
        });
    });

    if (closeBtn) {
        closeBtn.addEventListener('click', hidePopup);
    }

    if (editForm) {
        editForm.addEventListener('submit', (event) => {
            event.preventDefault();

            if (activeCartItemCard) {
                const itemNameElement = activeCartItemCard.querySelector('.item-name');
                const itemAmountElement = activeCartItemCard.querySelector('.item-amount');

                itemNameElement.textContent = itemNameInput.value;
                itemAmountElement.textContent = `Amount: ${itemAmountInput.value}`;
            }
            hidePopup();
        });
    }

    window.addEventListener('click', (event) => {
        if (event.target === editPopup) {
            hidePopup();
        }
    });
});