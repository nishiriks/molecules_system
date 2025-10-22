// Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Bootstrap tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Add click handlers for calendar events
    const calendarEvents = document.querySelectorAll('.calendar-event');
    calendarEvents.forEach(event => {
        event.addEventListener('click', function(e) {
            // The onclick attribute already handles navigation
            // This is for additional functionality if needed
            console.log('Calendar event clicked:', this.textContent);
        });
    });
    
    // Keyboard navigation
    document.addEventListener('keydown', function(e) {
        const currentMonth = new URLSearchParams(window.location.search).get('month') || new Date().getMonth() + 1;
        const currentYear = new URLSearchParams(window.location.search).get('year') || new Date().getFullYear();
        
        let newMonth = parseInt(currentMonth);
        let newYear = parseInt(currentYear);
        
        switch(e.key) {
            case 'ArrowLeft':
                // Previous month
                newMonth = newMonth - 1;
                if (newMonth === 0) {
                    newMonth = 12;
                    newYear = newYear - 1;
                }
                window.location.href = `a-calendar.php?month=${newMonth}&year=${newYear}`;
                break;
            case 'ArrowRight':
                // Next month
                newMonth = newMonth + 1;
                if (newMonth === 13) {
                    newMonth = 1;
                    newYear = newYear + 1;
                }
                window.location.href = `a-calendar.php?month=${newMonth}&year=${newYear}`;
                break;
            case 'Home':
                // Current month
                const now = new Date();
                window.location.href = `a-calendar.php?month=${now.getMonth() + 1}&year=${now.getFullYear()}`;
                break;
        }
    });
    
    // Add today indicator
    const today = new Date();
    const todayFormatted = today.toISOString().split('T')[0];
    const todayCells = document.querySelectorAll('.calendar-day.today');
    
    todayCells.forEach(cell => {
        const dayNumber = cell.querySelector('.day-number');
        if (dayNumber) {
            dayNumber.innerHTML = `<strong>${dayNumber.textContent} • Today</strong>`;
        }
    });
});