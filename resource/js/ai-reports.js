// Initialize jsPDF
const { jsPDF } = window.jspdf;

let currentReportContent = '';
let isSidebarCollapsed = false;

function toggleSidebar() {
    isSidebarCollapsed = !isSidebarCollapsed;
    document.body.classList.toggle('sidebar-collapsed', isSidebarCollapsed);
    
    const icon = document.getElementById('sidebar-toggle-icon');
    icon.className = isSidebarCollapsed ? 'fas fa-chevron-right' : 'fas fa-chevron-left';
}

async function sendPrompt(prompt) {
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
        const res = await fetch('', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ prompt })
        });

        const data = await res.json();
        
        if (data.answer) {
            output.innerHTML = data.answer;
            currentReportContent = data.answer;
            saveBtn.style.display = 'block';
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
        const res = await fetch('', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'save_report',
                title: title,
                content: currentReportContent,
                type: type
            })
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
    // Redirect to the same page with view_report parameter
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
        // For PDF, we need to get the report data first
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

function generatePDF(reportData) {
    const doc = new jsPDF();
    let yPosition = 20;
    const pageWidth = doc.internal.pageSize.width;
    const margin = 20;
    const maxWidth = pageWidth - (2 * margin);
    
    // Set initial font
    doc.setFont('helvetica');
    
    // Add title
    doc.setFontSize(16);
    doc.setFont(undefined, 'bold');
    const titleLines = doc.splitTextToSize(reportData.title, maxWidth);
    doc.text(titleLines, margin, yPosition);
    yPosition += titleLines.length * 7 + 10;
    
    // Add metadata
    doc.setFontSize(10);
    doc.setFont(undefined, 'normal');
    doc.text(`Generated: ${reportData.date}`, margin, yPosition);
    yPosition += 5;
    doc.text(`Type: ${reportData.type}`, margin, yPosition);
    yPosition += 10;
    
    // Add separator
    doc.line(margin, yPosition, pageWidth - margin, yPosition);
    yPosition += 15;
    
    // Convert HTML content to plain text for PDF
    const tempDiv = document.createElement('div');
    tempDiv.innerHTML = reportData.content;
    const plainText = tempDiv.textContent || tempDiv.innerText || '';
    
    // Process content with proper line breaks and formatting
    doc.setFontSize(11);
    
    // Split the text into paragraphs
    const paragraphs = plainText.split('\n').filter(p => p.trim().length > 0);
    
    paragraphs.forEach(paragraph => {
        if (paragraph.trim().length === 0) return;
        
        // Check if this looks like a heading (short text, might be bold in original)
        const isHeading = paragraph.length < 100 && (paragraph.includes(':') || /^[A-Z][^a-z]*$/.test(paragraph));
        
        if (isHeading) {
            // Add some space before heading
            yPosition += 5;
            
            // Set heading style
            doc.setFontSize(12);
            doc.setFont(undefined, 'bold');
            
            const headingLines = doc.splitTextToSize(paragraph, maxWidth);
            
            // Check if we need a new page
            if (yPosition + headingLines.length * 6 > doc.internal.pageSize.height - 20) {
                doc.addPage();
                yPosition = 20;
            }
            
            doc.text(headingLines, margin, yPosition);
            yPosition += headingLines.length * 6 + 2;
            
            // Reset to normal text
            doc.setFontSize(11);
            doc.setFont(undefined, 'normal');
        } else {
            // Regular paragraph
            const lines = doc.splitTextToSize(paragraph, maxWidth);
            
            // Check if we need a new page
            if (yPosition + lines.length * 6 > doc.internal.pageSize.height - 20) {
                doc.addPage();
                yPosition = 20;
            }
            
            doc.text(lines, margin, yPosition);
            yPosition += lines.length * 6 + 3;
        }
    });
    
    // Add footer to all pages
    const pageCount = doc.internal.getNumberOfPages();
    for (let i = 1; i <= pageCount; i++) {
        doc.setPage(i);
        doc.setFontSize(8);
        doc.text(`Page ${i} of ${pageCount} - CEU Molecules AI Report`, pageWidth / 2, doc.internal.pageSize.height - 10, { align: 'center' });
    }
    
    // Save the PDF
    doc.save(`${reportData.title.replace(/[^a-z0-9]/gi, '_')}.pdf`);
}

// Handle view report on page load
document.addEventListener('DOMContentLoaded', function() {
    // Add has-sidebar class to body for proper styling
    document.body.classList.add('has-sidebar');
    
    // Check if we should load a report from URL parameter
    const urlParams = new URLSearchParams(window.location.search);
    const viewReportId = urlParams.get('view_report');
    
    if (viewReportId) {
        // Scroll to the main content area
        document.querySelector('.main-content').scrollIntoView({ behavior: 'smooth' });
    }
});