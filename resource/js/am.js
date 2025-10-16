document.addEventListener('DOMContentLoaded', function() {
    // Delete User Handler
    const deleteModal = document.getElementById('deleteModal');
    deleteModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const userId = button.getAttribute('data-user-id');
        const userName = button.getAttribute('data-user-name');
        
        document.getElementById('deleteUserId').value = userId;
        document.getElementById('deleteUserName').textContent = userName;
    });

    // Change Type Handler
    const changeTypeModal = document.getElementById('changeTypeModal');
    changeTypeModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const userId = button.getAttribute('data-user-id');
        const userName = button.getAttribute('data-user-name');
        const currentType = button.getAttribute('data-current-type');
        
        document.getElementById('changeTypeUserId').value = userId;
        document.getElementById('changeTypeUserName').textContent = userName;
        document.getElementById('currentAccountType').textContent = currentType;
        document.getElementById('account_type').value = currentType;
    });
});