// Calendar functionality with day click modal
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Bootstrap tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Initialize the modal
    const dayDetailsModal = new bootstrap.Modal(document.getElementById('dayDetailsModal'));
    let currentSelectedDate = null;
    
    // Add click handlers for calendar days
    const calendarDays = document.querySelectorAll('.calendar-day:not(.other-month)');
    calendarDays.forEach(day => {
        day.addEventListener('click', function(e) {
            // Don't trigger if clicking on an event or counter
            if (e.target.closest('.calendar-event') || e.target.closest('.event-counter')) {
                return;
            }
            
            const dayNumber = this.querySelector('.day-number').textContent.trim();
            const currentMonth = new URLSearchParams(window.location.search).get('month') || new Date().getMonth() + 1;
            const currentYear = new URLSearchParams(window.location.search).get('year') || new Date().getFullYear();
            
            // Create the date in YYYY-MM-DD format
            const monthStr = currentMonth.toString().padStart(2, '0');
            const dayStr = dayNumber.padStart(2, '0');
            currentSelectedDate = `${currentYear}-${monthStr}-${dayStr}`;
            
            // Show loading state
            showLoadingState();
            
            // Load orders for this date
            loadOrdersForDate(currentSelectedDate);
        });
    });
    
    // Add click handlers for event counters
    const eventCounters = document.querySelectorAll('.event-counter');
    eventCounters.forEach(counter => {
        counter.addEventListener('click', function(e) {
            e.stopPropagation();
            const day = this.closest('.calendar-day');
            const dayNumber = day.querySelector('.day-number').textContent.trim();
            const currentMonth = new URLSearchParams(window.location.search).get('month') || new Date().getMonth() + 1;
            const currentYear = new URLSearchParams(window.location.search).get('year') || new Date().getFullYear();
            
            // Create the date in YYYY-MM-DD format
            const monthStr = currentMonth.toString().padStart(2, '0');
            const dayStr = dayNumber.padStart(2, '0');
            currentSelectedDate = `${currentYear}-${monthStr}-${dayStr}`;
            
            // Show loading state
            showLoadingState();
            
            // Load orders for this date
            loadOrdersForDate(currentSelectedDate);
        });
    });
    
    // Function to show loading state
    function showLoadingState() {
        const ordersList = document.getElementById('dayOrdersList');
        ordersList.innerHTML = `
            <div class="loading-orders">
                <div class="spinner-border" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Loading orders...</p>
            </div>
        `;
        dayDetailsModal.show();
    }
    
    // Function to load orders for a specific date
    function loadOrdersForDate(date) {
        // Update modal title
        const modalDateElement = document.getElementById('modalDate');
        const formattedDate = new Date(date + 'T00:00:00').toLocaleDateString('en-US', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
        modalDateElement.textContent = formattedDate;
        
        // Display orders for this date
        displayOrdersForDate(date);
    }
    
    // Function to display orders for a specific date
    function displayOrdersForDate(date) {
        const ordersList = document.getElementById('dayOrdersList');
        
        // Find all events for this date using multiple methods
        const dateEvents = [];
        
        // Method 1: Find by data-date attribute (most reliable)
        const eventsByDataAttr = document.querySelectorAll('.calendar-event[data-date="' + date + '"]');
        eventsByDataAttr.forEach(event => {
            const requestId = event.getAttribute('data-request-id');
            const status = getEventStatus(event);
            const tooltipText = event.getAttribute('data-bs-original-title') || event.title || '';
            
            if (requestId) {
                const eventInfo = parseEventInfo(event, tooltipText, requestId, status);
                if (eventInfo) {
                    dateEvents.push(eventInfo);
                }
            }
        });
        
        // Method 2: If no events found by data attribute, try to find events in the clicked day
        if (dateEvents.length === 0) {
            const dayNumber = date.split('-')[2];
            const calendarDays = document.querySelectorAll('.calendar-day:not(.other-month)');
            
            calendarDays.forEach(day => {
                const dayNumElement = day.querySelector('.day-number');
                if (dayNumElement && dayNumElement.textContent.trim() === dayNumber) {
                    const eventsInDay = day.querySelectorAll('.calendar-event');
                    eventsInDay.forEach(event => {
                        const onclickAttr = event.getAttribute('onclick');
                        const requestIdMatch = onclickAttr ? onclickAttr.match(/id=(\d+)/) : null;
                        const requestId = requestIdMatch ? requestIdMatch[1] : null;
                        const status = getEventStatus(event);
                        const tooltipText = event.getAttribute('data-bs-original-title') || event.title || '';
                        
                        if (requestId) {
                            const eventInfo = parseEventInfo(event, tooltipText, requestId, status);
                            if (eventInfo) {
                                dateEvents.push(eventInfo);
                            }
                        }
                    });
                }
            });
        }
        
        if (dateEvents.length === 0) {
            ordersList.innerHTML = `
                <div class="no-orders">
                    <i class="fas fa-calendar-times"></i>
                    <h5>No Orders</h5>
                    <p>There are no orders scheduled for this date.</p>
                </div>
            `;
        } else {
            let ordersHTML = '';
            
            dateEvents.forEach(order => {
                ordersHTML += `
                    <div class="day-order-item ${order.status}">
                        <div class="order-header">
                            <div class="order-user">${order.userName}</div>
                            <span class="order-status" style="background-color: var(--${order.status}-color)">${order.status}</span>
                        </div>
                        <div class="order-details">
                            <strong>Product:</strong> ${order.productType}
                        </div>
                        ${order.dateRange ? `<div class="order-date-range">Date Range: ${order.dateRange}</div>` : ''}
                        <div class="order-actions">
                            <a href="a-order-details.php?id=${order.id}" class="view-order-btn">
                                <i class="fas fa-eye"></i> View Details
                            </a>
                        </div>
                    </div>
                `;
            });
            
            ordersList.innerHTML = ordersHTML;
        }
    }
    
    // Helper function to get event status
    function getEventStatus(event) {
        const statusClasses = ['pending', 'submitted', 'pickup', 'received', 'returned', 'broken', 'lost', 'canceled', 'disapproved'];
        return Array.from(event.classList).find(cls => statusClasses.includes(cls)) || 'pending';
    }
    
    // Helper function to parse event information
    function parseEventInfo(event, tooltipText, requestId, status) {
        let productType = 'General';
        let userName = 'Unknown User';
        let dateRange = '';
        let displayText = event.textContent.trim();
        
        // Try to extract from tooltip
        if (tooltipText) {
            // Pattern: "Product Request - First Last (MM/DD/YYYY - MM/DD/YYYY)"
            const tooltipMatch = tooltipText.match(/(.+?) Request - (.+?) \((.+?)\)/);
            if (tooltipMatch) {
                productType = tooltipMatch[1] || 'General';
                userName = tooltipMatch[2] || 'Unknown User';
                dateRange = tooltipMatch[3] || '';
            } else {
                // Fallback: try simpler parsing
                const parts = tooltipText.split(' - ');
                if (parts.length >= 2) {
                    productType = parts[0] || 'General';
                    userName = parts[1] || 'Unknown User';
                    
                    // Extract date range from parentheses if present
                    const dateMatch = tooltipText.match(/\(([^)]+)\)/);
                    if (dateMatch) {
                        dateRange = dateMatch[1];
                    }
                } else {
                    // Last resort: use display text
                    userName = displayText;
                }
            }
        } else {
            // No tooltip, use display text as user name
            userName = displayText;
        }
        
        return {
            id: requestId,
            productType: productType,
            userName: userName,
            dateRange: dateRange,
            status: status,
            displayText: displayText
        };
    }
    
    // Add click handlers for calendar events
    const calendarEvents = document.querySelectorAll('.calendar-event');
    calendarEvents.forEach(event => {
        // Add hover effects for better UX
        event.addEventListener('mouseenter', function() {
            this.style.zIndex = '1000';
        });
        
        event.addEventListener('mouseleave', function() {
            this.style.zIndex = '';
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
            case 'Escape':
                if (dayDetailsModal._isShown) {
                    dayDetailsModal.hide();
                } else {
                    // Go to today
                    const today = new Date();
                    window.location.href = `a-calendar.php?month=${today.getMonth() + 1}&year=${today.getFullYear()}`;
                }
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
    
    // Add event counters to days with many events
    addEventCounters();
    
    // Function to add event counters
    function addEventCounters() {
        const calendarDays = document.querySelectorAll('.calendar-day:not(.other-month)');
        calendarDays.forEach(day => {
            const events = day.querySelectorAll('.calendar-event');
            if (events.length > 0) {
                // Remove existing counter if any
                const existingCounter = day.querySelector('.event-counter');
                if (existingCounter) {
                    existingCounter.remove();
                }
                
                const counter = document.createElement('div');
                counter.className = 'event-counter';
                counter.textContent = events.length;
                counter.title = `Click to view all ${events.length} orders`;
                day.appendChild(counter);
                
                // Show only first 3 events, hide others
                for (let i = 3; i < events.length; i++) {
                    events[i].style.display = 'none';
                }
            }
        });
    }
    
    // Debug function to check event data
    function debugEvents() {
        console.log('=== DEBUG: Checking calendar events ===');
        const allEvents = document.querySelectorAll('.calendar-event');
        allEvents.forEach((event, index) => {
            console.log(`Event ${index + 1}:`, {
                text: event.textContent,
                dataDate: event.getAttribute('data-date'),
                dataRequestId: event.getAttribute('data-request-id'),
                onclick: event.getAttribute('onclick'),
                tooltip: event.getAttribute('data-bs-original-title') || event.title,
                classes: event.className
            });
        });
        console.log('=== DEBUG END ===');
    }
    
    // debugEvents();
    
    console.log('Calendar loaded successfully. Click on any day to view all orders.');
});