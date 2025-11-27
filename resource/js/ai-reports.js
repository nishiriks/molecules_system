// Initialize jsPDF
const { jsPDF } = window.jspdf;

let currentReportContent = '';
let currentReportDateFrom = '';
let currentReportDateTo = '';
let isSidebarCollapsed = false;

function toggleSidebar() {
    isSidebarCollapsed = !isSidebarCollapsed;
    document.body.classList.toggle('sidebar-collapsed', isSidebarCollapsed);
    
    const icon = document.getElementById('sidebar-toggle-icon');
    icon.className = isSidebarCollapsed ? 'fas fa-chevron-right' : 'fas fa-chevron-left';
}

async function sendPrompt(prompt, dateFrom = '', dateTo = '') {
    const output = document.getElementById('output');
    const saveBtn = document.getElementById('save-report-btn');
    
    output.innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary mb-3" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p>Generating AI analysis...</p>
        </div>
    `;
    
    saveBtn.style.display = 'none';

    try {
        const requestData = { prompt };
        
        // Add date range for custom analysis
        if (prompt === 'CUSTOM_DATE_RANGE_ANALYSIS') {
            requestData.date_from = dateFrom;
            requestData.date_to = dateTo;
        }

        const res = await fetch('', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(requestData)
        });

        const data = await res.json();
        
        if (data.answer) {
            output.innerHTML = data.answer;
            currentReportContent = data.answer;
            saveBtn.style.display = 'block';
            
            // Store date range for saving
            if (prompt === 'CUSTOM_DATE_RANGE_ANALYSIS') {
                currentReportDateFrom = dateFrom;
                currentReportDateTo = dateTo;
            } else {
                currentReportDateFrom = '';
                currentReportDateTo = '';
            }
        } else {
            output.innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Error getting response from AI.
                </div>
            `;
            currentReportContent = '';
            saveBtn.style.display = 'none';
        }
    } catch (error) {
        output.innerHTML = `
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle me-2"></i>
                Network error: ${error.message}
            </div>
        `;
        currentReportContent = '';
        saveBtn.style.display = 'none';
    }
}

function showCustomPromptModal() {
    const modal = new bootstrap.Modal(document.getElementById('customPromptModal'));
    modal.show();
}

function submitCustomPrompt() {
    const dateFrom = document.getElementById('dateFrom').value;
    const dateTo = document.getElementById('dateTo').value;
    
    if (!dateFrom || !dateTo) {
        alert('Please select both start and end dates');
        return;
    }
    
    if (new Date(dateFrom) > new Date(dateTo)) {
        alert('Start date cannot be after end date');
        return;
    }
    
    const modal = bootstrap.Modal.getInstance(document.getElementById('customPromptModal'));
    modal.hide();
    
    sendPrompt('CUSTOM_DATE_RANGE_ANALYSIS', dateFrom, dateTo);
}

function showSaveReportModal() {
    if (!currentReportContent) {
        alert('No report content to save!');
        return;
    }
    
    document.getElementById('reportPreview').innerHTML = currentReportContent;
    const modal = new bootstrap.Modal(document.getElementById('saveReportModal'));
    modal.show();
}

async function saveCurrentReport() {
    const title = document.getElementById('reportTitle').value.trim();
    const type = document.getElementById('reportType').value;
    
    if (!title) {
        alert('Please enter a report title');
        return;
    }
    
    try {
        const saveData = {
            action: 'save_report',
            title: title,
            content: currentReportContent,
            type: type
        };
        
        // Add date range if this was a custom analysis
        if (currentReportDateFrom && currentReportDateTo) {
            saveData.date_start = currentReportDateFrom;
            saveData.date_end = currentReportDateTo;
        }
        
        const res = await fetch('', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(saveData)
        });
        
        const data = await res.json();
        
        if (data.success) {
            // Close modal and show success message
            const modal = bootstrap.Modal.getInstance(document.getElementById('saveReportModal'));
            modal.hide();
            
            // Reload the page to show the new saved report
            location.reload();
        } else {
            alert('Error saving report: ' + data.error);
        }
    } catch (error) {
        alert('Error saving report: ' + error.message);
    }
}

function loadSavedReport(reportId) {
    window.location.href = `?view_report=${reportId}`;
}

function deleteReport(reportId) {
    if (confirm('Are you sure you want to delete this report?')) {
        fetch('', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'delete_report',
                report_id: reportId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error deleting report: ' + data.error);
            }
        })
        .catch(error => {
            alert('Error deleting report: ' + error.message);
        });
    }
}

function clearAllReports() {
    if (confirm('Are you sure you want to delete all saved reports? This action cannot be undone.')) {
        fetch('', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'clear_all_reports'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error clearing reports: ' + data.error);
            }
        })
        .catch(error => {
            alert('Error clearing reports: ' + error.message);
        });
    }
}

function exportReport(reportId, format) {
    if (format === 'pdf') {
        fetch('', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'get_report_data',
                report_id: reportId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                generatePDF(data);
            } else {
                alert('Error exporting report: ' + data.error);
            }
        })
        .catch(error => {
            alert('Error exporting report: ' + error.message);
        });
    }
}

// Improved PDF generation that preserves HTML formatting using html2canvas
function generatePDF(reportData) {
    const doc = new jsPDF();
    let yPosition = 20;
    const pageWidth = doc.internal.pageSize.width;
    const pageHeight = doc.internal.pageSize.height;
    const margin = 20;
    const maxWidth = pageWidth - (2 * margin);
    
    // Add title and metadata
    doc.setFontSize(16);
    doc.setFont(undefined, 'bold');
    const titleLines = doc.splitTextToSize(reportData.title, maxWidth);
    doc.text(titleLines, margin, yPosition);
    yPosition += titleLines.length * 7 + 10;
    
    doc.setFontSize(10);
    doc.setFont(undefined, 'normal');
    doc.text(`Generated: ${reportData.date}`, margin, yPosition);
    yPosition += 5;
    doc.text(`Type: ${reportData.type}`, margin, yPosition);
    yPosition += 15;
    
    // Use html2canvas to capture the actual HTML rendering
    const tempDiv = document.createElement('div');
    tempDiv.innerHTML = reportData.content;
    tempDiv.style.width = '800px';
    tempDiv.style.padding = '20px';
    tempDiv.style.fontFamily = 'Arial, sans-serif';
    tempDiv.style.fontSize = '14px';
    tempDiv.style.lineHeight = '1.4';
    document.body.appendChild(tempDiv);
    
    // Use html2canvas to capture the exact rendering
    html2canvas(tempDiv, {
        scale: 2, // Higher scale for better quality
        useCORS: true,
        allowTaint: true,
        logging: false
    }).then(canvas => {
        document.body.removeChild(tempDiv);
        
        const imgData = canvas.toDataURL('image/png');
        const imgWidth = pageWidth - (2 * margin);
        const imgHeight = (canvas.height * imgWidth) / canvas.width;
        
        // Check if content fits on one page
        if (yPosition + imgHeight > pageHeight - 20) {
            // Content is too tall, split across multiple pages
            let heightLeft = imgHeight;
            let position = 0;
            const pageContentHeight = pageHeight - yPosition - 20;
            
            doc.addImage(imgData, 'PNG', margin, yPosition, imgWidth, imgHeight, '', 'FAST');
            heightLeft -= pageContentHeight;
            
            while (heightLeft > 0) {
                yPosition = -heightLeft;
                doc.addPage();
                doc.addImage(imgData, 'PNG', margin, yPosition, imgWidth, imgHeight, '', 'FAST');
                heightLeft -= pageHeight;
            }
        } else {
            // Content fits on one page
            doc.addImage(imgData, 'PNG', margin, yPosition, imgWidth, imgHeight);
        }
        
        // Add footer to all pages
        const pageCount = doc.internal.getNumberOfPages();
        for (let i = 1; i <= pageCount; i++) {
            doc.setPage(i);
            doc.setFontSize(8);
            doc.setTextColor(100, 100, 100);
            doc.text(`Page ${i} of ${pageCount} - CEU Molecules AI Report`, pageWidth / 2, pageHeight - 10, { align: 'center' });
        }
        
        doc.save(`${reportData.title.replace(/[^a-z0-9]/gi, '_')}.pdf`);
    }).catch(error => {
        document.body.removeChild(tempDiv);
        console.error('Error generating PDF with html2canvas:', error);
        // Fallback to basic PDF generation
        createBasicPDFFromHTML(reportData, doc, margin, yPosition, pageWidth, pageHeight);
    });
}

// Fallback PDF generation for when html2canvas fails
function createBasicPDFFromHTML(reportData, doc, margin, startY, pageWidth, pageHeight) {
    let yPosition = startY;
    const maxWidth = pageWidth - (2 * margin);
    
    const tempDiv = document.createElement('div');
    tempDiv.innerHTML = reportData.content;
    
    // Process different HTML elements
    const processElement = (element) => {
        if (yPosition > pageHeight - 30) {
            doc.addPage();
            yPosition = 20;
        }
        
        const tagName = element.tagName.toLowerCase();
        
        switch(tagName) {
            case 'h1':
                doc.setFontSize(16);
                doc.setFont(undefined, 'bold');
                yPosition += 5;
                break;
            case 'h2':
                doc.setFontSize(14);
                doc.setFont(undefined, 'bold');
                yPosition += 5;
                break;
            case 'h3':
                doc.setFontSize(12);
                doc.setFont(undefined, 'bold');
                yPosition += 5;
                break;
            case 'table':
                yPosition = createSimpleTablePDF(element, doc, margin, yPosition, pageWidth, pageHeight);
                return yPosition;
            case 'ul':
            case 'ol':
                const listItems = element.querySelectorAll('li');
                listItems.forEach(item => {
                    if (yPosition > pageHeight - 20) {
                        doc.addPage();
                        yPosition = 20;
                    }
                    doc.setFontSize(10);
                    doc.setFont(undefined, 'normal');
                    const text = '• ' + item.textContent.trim();
                    const lines = doc.splitTextToSize(text, maxWidth - 10);
                    doc.text(lines, margin + 5, yPosition);
                    yPosition += lines.length * 6;
                });
                yPosition += 5;
                return yPosition;
            case 'p':
                doc.setFontSize(10);
                doc.setFont(undefined, 'normal');
                yPosition += 3;
                break;
            default:
                doc.setFontSize(10);
                doc.setFont(undefined, 'normal');
        }
        
        if (tagName !== 'table' && tagName !== 'ul' && tagName !== 'ol') {
            const text = element.textContent.trim();
            if (text) {
                const lines = doc.splitTextToSize(text, maxWidth);
                if (lines.length > 0) {
                    doc.text(lines, margin, yPosition);
                    yPosition += lines.length * 6;
                }
            }
        }
        
        return yPosition;
    };
    
    // Process all child nodes
    const childNodes = Array.from(tempDiv.childNodes);
    childNodes.forEach(node => {
        if (node.nodeType === Node.ELEMENT_NODE) {
            yPosition = processElement(node);
        } else if (node.nodeType === Node.TEXT_NODE) {
            const text = node.textContent.trim();
            if (text) {
                if (yPosition > pageHeight - 20) {
                    doc.addPage();
                    yPosition = 20;
                }
                doc.setFontSize(10);
                doc.setFont(undefined, 'normal');
                const lines = doc.splitTextToSize(text, maxWidth);
                if (lines.length > 0) {
                    doc.text(lines, margin, yPosition);
                    yPosition += lines.length * 6;
                }
            }
        }
    });
    
    // Add footer
    const pageCount = doc.internal.getNumberOfPages();
    for (let i = 1; i <= pageCount; i++) {
        doc.setPage(i);
        doc.setFontSize(8);
        doc.text(`Page ${i} of ${pageCount} - CEU Molecules AI Report`, pageWidth / 2, pageHeight - 10, { align: 'center' });
    }
    
    doc.save(`${reportData.title.replace(/[^a-z0-9]/gi, '_')}.pdf`);
}

function createSimpleTablePDF(table, doc, margin, startY, pageWidth, pageHeight) {
    let yPosition = startY;
    const rows = table.querySelectorAll('tr');
    const availableWidth = pageWidth - (2 * margin);
    
    if (rows.length === 0) return yPosition;
    
    // Simple column width calculation
    const colCount = rows[0].querySelectorAll('th, td').length;
    const colWidth = availableWidth / colCount;
    
    // Process each row
    for (let i = 0; i < rows.length; i++) {
        const cells = rows[i].querySelectorAll('th, td');
        const isHeader = cells[0] && cells[0].tagName.toLowerCase() === 'th';
        
        // Check if we need a new page
        if (yPosition > pageHeight - 30) {
            doc.addPage();
            yPosition = 20;
        }
        
        let xPosition = margin;
        let maxCellHeight = 0;
        
        // First pass: calculate maximum height for this row
        for (let j = 0; j < cells.length; j++) {
            const cellText = cells[j].textContent.trim();
            const lines = doc.splitTextToSize(cellText, colWidth - 4);
            const cellHeight = lines.length * 5 + 4;
            maxCellHeight = Math.max(maxCellHeight, cellHeight);
        }
        
        // Set style for the row
        if (isHeader) {
            doc.setFont(undefined, 'bold');
            doc.setFillColor(22, 21, 66);
            doc.setTextColor(255, 255, 255);
        } else {
            doc.setFont(undefined, 'normal');
            doc.setFillColor(255, 255, 255);
            doc.setTextColor(0, 0, 0);
        }
        
        // Draw cells
        for (let j = 0; j < cells.length; j++) {
            const cellText = cells[j].textContent.trim();
            const lines = doc.splitTextToSize(cellText, colWidth - 4);
            
            // Draw cell background and border
            doc.rect(xPosition, yPosition, colWidth, maxCellHeight, 'F');
            doc.setDrawColor(100, 100, 100);
            doc.rect(xPosition, yPosition, colWidth, maxCellHeight);
            
            // Add text
            doc.setFontSize(9);
            for (let k = 0; k < lines.length; k++) {
                doc.text(lines[k], xPosition + 2, yPosition + 4 + (k * 5));
            }
            
            xPosition += colWidth;
        }
        
        yPosition += maxCellHeight;
    }
    
    return yPosition + 10;
}

// Handle view report on page load
document.addEventListener('DOMContentLoaded', function() {
    document.body.classList.add('has-sidebar');
    
    const urlParams = new URLSearchParams(window.location.search);
    const viewReportId = urlParams.get('view_report');
    
    if (viewReportId) {
        document.querySelector('.main-content').scrollIntoView({ behavior: 'smooth' });
    }
    
    // Set default dates for custom prompt (last 30 days)
    const today = new Date();
    const oneMonthAgo = new Date();
    oneMonthAgo.setMonth(today.getMonth() - 1);
    
    document.getElementById('dateFrom').valueAsDate = oneMonthAgo;
    document.getElementById('dateTo').valueAsDate = today;
});