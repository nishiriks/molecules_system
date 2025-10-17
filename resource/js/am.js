document.addEventListener('DOMContentLoaded', function() {
    // Toggle Status Handler
    const toggleStatusModal = document.getElementById('toggleStatusModal');
    toggleStatusModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const userId = button.getAttribute('data-user-id');
        const userName = button.getAttribute('data-user-name');
        const currentStatus = button.getAttribute('data-current-status');
        
        const isActive = currentStatus === '1';
        const action = isActive ? 'deactivate' : 'activate';
        const actionText = isActive ? 'Deactivate' : 'Activate';
        
        document.getElementById('toggleStatusUserId').value = userId;
        document.getElementById('toggleStatusUserName').textContent = userName;
        document.getElementById('statusAction').textContent = action;
        
        const statusMessage = isActive 
            ? 'The user will no longer be able to log in, but their data will be preserved.'
            : 'The user will be able to log in again.';
        document.getElementById('statusMessage').textContent = statusMessage;
        
        const toggleButton = document.getElementById('toggleStatusButton');
        toggleButton.textContent = actionText + ' User';
        toggleButton.className = isActive ? 'btn btn-danger' : 'btn btn-success';
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