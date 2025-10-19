<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Process form data - convert material_type from string to array
    $material_type = isset($_POST['material_type']) ? $_POST['material_type'] : '';
    if (!empty($material_type) && is_string($material_type)) {
        $material_type = explode(',', $material_type);
    } else {
        $material_type = [];
    }
    
    $instructor_name = $_POST['instructor_name'] ?? '';
    $signature = $_POST['signature'] ?? '';
    $subject = $_POST['subject'] ?? '';
    $date_of_use = $_POST['date_of_use'] ?? '';
    $time = $_POST['time'] ?? '';
    $days = $_POST['days'] ?? '';
    $room = $_POST['room'] ?? '';
    $remarks = $_POST['remarks'] ?? '';
    $issue_date = $_POST['issue_date'] ?? date('m/d/Y');
    $return_date = $_POST['return_date'] ?? '';
    
    // Prepare materials data
    $materials_data = [];
    for ($i = 1; $i <= 8; $i++) {
        $materials_data[] = [
            'quantity_1' => $_POST["quantity_1_$i"] ?? '',
            'material_1' => $_POST["material_1_$i"] ?? '',
            'quantity_2' => $_POST["quantity_2_$i"] ?? '',
            'material_2' => $_POST["material_2_$i"] ?? ''
        ];
    }
    
    // DEBUG: Show what material types we're processing
    error_log("Material types to process: " . implode(', ', $material_type));
    
    // Generate FDF data for PDFtk
    $fdf_data = generateFDFData($material_type, $instructor_name, $signature, $subject, $date_of_use, $time, $days, $room, $remarks, $issue_date, $return_date, $materials_data);
    
    // Save FDF to temporary file
    $fdf_filename = tempnam(sys_get_temp_dir(), 'requisition_') . '.fdf';
    file_put_contents($fdf_filename, $fdf_data);
    
    // Source PDF - fixed path
    $source_pdf = 'resource/pdf/Requisition_form.pdf';
    
    // Output PDF filename
    $output_pdf = 'CEU_Requisition_Form_' . date('Y-m-d_His') . '.pdf';
    
    // Use PDFtk to fill the form
    $pdftk_path = '"C:\Program Files (x86)\PDFtk Server\bin\pdftk"';
    $command = $pdftk_path . " \"$source_pdf\" fill_form \"$fdf_filename\" output \"$output_pdf\" flatten 2>&1";
    
    // Execute command
    $output = [];
    $return_var = 0;
    exec($command, $output, $return_var);
    
    if ($return_var === 0 && file_exists($output_pdf)) {
        // Send PDF to browser
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $output_pdf . '"');
        header('Content-Length: ' . filesize($output_pdf));
        readfile($output_pdf);
        
        // Clean up temporary files
        unlink($fdf_filename);
        unlink($output_pdf);
        exit;
    } else {
        echo "<h2>Error Generating PDF</h2>";
        echo "<p>Return code: $return_var</p>";
        echo "<p>Output: " . htmlspecialchars(implode("\n", $output)) . "</p>";
        echo "<p>Source PDF: $source_pdf</p>";
        echo "<p>Output PDF: $output_pdf</p>";
        echo "<p>FDF File: $fdf_filename</p>";
        
        // Check if source PDF exists
        if (!file_exists($source_pdf)) {
            echo "<p style='color: red;'>Source PDF file not found: $source_pdf</p>";
        }
        
        // Show material types for debugging
        echo "<h3>Material Types Detected:</h3>";
        echo "<pre>" . print_r($material_type, true) . "</pre>";
        
        // Show FDF content for debugging
        echo "<h3>FDF Data:</h3>";
        echo "<pre>" . htmlspecialchars($fdf_data) . "</pre>";
        
        // Show what fields are being set
        echo "<h3>Fields being set in FDF:</h3>";
        preg_match_all('/\/T \(([^)]+)\)/', $fdf_data, $matches);
        echo "<pre>" . print_r($matches[1], true) . "</pre>";
        
        echo '<p><a href="requisition_form.php">Go back to form</a></p>';
    }
} else {
    header('Location: requisition_form.php');
    exit;
}

function generateFDFData($material_type, $instructor_name, $signature, $subject, $date_of_use, $time, $days, $room, $remarks, $issue_date, $return_date, $materials_data) {
    $fields = [];
    
    // Material Type Text Fields - Write 'X' for selected types
    // Try different possible field names for the material type checkboxes
    $textfield_mappings = [
        // Try these variations of field names
        [
            'Apparatus' => 'check_apparatus',
            'Chemicals/Supplies' => 'check_chemicals', 
            'Equipment' => 'check_equipment',
            'Models/Charts' => 'check_models',
            'Specimen' => 'check_specimen'
        ],
        [
            'Apparatus' => 'Apparatus',
            'Chemicals/Supplies' => 'Chemicals/Supplies', 
            'Equipment' => 'Equipment',
            'Models/Charts' => 'Models/Charts',
            'Specimen' => 'Specimen'
        ],
        [
            'Apparatus' => 'apparatus',
            'Chemicals/Supplies' => 'chemicals', 
            'Equipment' => 'equipment',
            'Models/Charts' => 'models',
            'Specimen' => 'specimen'
        ]
    ];

    // Ensure $material_type is an array
    if (!is_array($material_type)) {
        $material_type = [];
    }
    
    // Try each mapping until we find one that works
    $mapping_found = false;
    foreach ($textfield_mappings as $mapping) {
        $fields_tried = [];
        foreach ($mapping as $form_value => $pdf_field) {
            $fields_tried[] = $pdf_field;
            if (in_array($form_value, $material_type)) {
                // Write 'X' in the text field
                $fields[] = "<< /T ($pdf_field) /V (X) >>";
            } else {
                // Leave empty for unselected types
                $fields[] = "<< /T ($pdf_field) /V () >>";
            }
        }
        
        // If we have material types and we're setting fields, assume this mapping might work
        if (!empty($material_type)) {
            error_log("Trying field mapping: " . implode(', ', $fields_tried));
            break; // Use the first mapping for now
        }
    }
    
    // Basic Information
    if (!empty($instructor_name)) {
        $fields[] = "<< /T (prof_name) /V (" . escapeFDFString($instructor_name) . ") >>";
    }
    
    if (!empty($signature)) {
        $fields[] = "<< /T (user_name) /V (" . escapeFDFString($signature) . ") >>";
        $fields[] = "<< /T (user_name2) /V (" . escapeFDFString($signature) . ") >>";
    }
    
    if (!empty($subject)) {
        $fields[] = "<< /T (subject) /V (" . escapeFDFString($subject) . ") >>";
    }
    
    if (!empty($date_of_use)) {
        $fields[] = "<< /T (use_date) /V (" . escapeFDFString($date_of_use) . ") >>";
    }
    
    if (!empty($time)) {
        $fields[] = "<< /T (time) /V (" . escapeFDFString($time) . ") >>";
    }
    
    if (!empty($days)) {
        $fields[] = "<< /T (num_days) /V (" . escapeFDFString($days) . ") >>";
    }
    
    if (!empty($room)) {
        $fields[] = "<< /T (room) /V (" . escapeFDFString($room) . ") >>";
    }
    
    // Dates
    if (!empty($issue_date)) {
        $fields[] = "<< /T (request_date) /V (" . escapeFDFString($issue_date) . ") >>";
        $fields[] = "<< /T (receive_date) /V (" . escapeFDFString($issue_date) . ") >>";
    }
    
    if (!empty($return_date)) {
        $fields[] = "<< /T (return_date) /V (" . escapeFDFString($return_date) . ") >>";
    }
    
    // Remarks (split into two lines if needed)
    if (!empty($remarks)) {
        $remarks_lines = str_split($remarks, 50); // Split long remarks
        $fields[] = "<< /T (remark_line1) /V (" . escapeFDFString($remarks_lines[0] ?? '') . ") >>";
        if (isset($remarks_lines[1])) {
            $fields[] = "<< /T (remark_line2) /V (" . escapeFDFString($remarks_lines[1]) . ") >>";
        }
    }
    
    // Technician names (you might want to set these or leave empty)
    $fields[] = "<< /T (tech_name) /V () >>";
    $fields[] = "<< /T (tech_name2) /V () >>";
    
    // Materials Data - Map to the correct quantity and item fields
    $qty_fields = ['qty1', 'qty2', 'qty3', 'qty4', 'qty5', 'qty6', 'qty7', 'qty8', 
                   'qty9', 'qty10', 'qty11', 'qty12', 'qty13', 'qty14', 'qty15', 'qty16'];
    
    $item_fields = ['item1', 'item2', 'item3', 'item4', 'item5', 'item6', 'item7', 'item8',
                    'item9', 'item10', 'item11', 'item12', 'item13', 'item14', 'item15', 'item16'];
    
    $field_index = 0;
    foreach ($materials_data as $material) {
        // First column materials
        if (!empty($material['quantity_1']) || !empty($material['material_1'])) {
            if ($field_index < 16) {
                if (!empty($material['quantity_1'])) {
                    $fields[] = "<< /T (" . $qty_fields[$field_index] . ") /V (" . escapeFDFString($material['quantity_1']) . ") >>";
                }
                if (!empty($material['material_1'])) {
                    $fields[] = "<< /T (" . $item_fields[$field_index] . ") /V (" . escapeFDFString($material['material_1']) . ") >>";
                }
                $field_index++;
            }
        }
        
        // Second column materials  
        if (!empty($material['quantity_2']) || !empty($material['material_2'])) {
            if ($field_index < 16) {
                if (!empty($material['quantity_2'])) {
                    $fields[] = "<< /T (" . $qty_fields[$field_index] . ") /V (" . escapeFDFString($material['quantity_2']) . ") >>";
                }
                if (!empty($material['material_2'])) {
                    $fields[] = "<< /T (" . $item_fields[$field_index] . ") /V (" . escapeFDFString($material['material_2']) . ") >>";
                }
                $field_index++;
            }
        }
    }
    
    // Build FDF
    $fdf = "%FDF-1.2\n";
    $fdf .= "1 0 obj\n";
    $fdf .= "<<\n";
    $fdf .= "/FDF << /Fields [\n";
    $fdf .= implode("\n", $fields);
    $fdf .= "\n]\n";
    $fdf .= "/F (Requisition_form.pdf)\n";
    $fdf .= ">>\n";
    $fdf .= ">>\n";
    $fdf .= "endobj\n";
    $fdf .= "trailer\n";
    $fdf .= "<<\n";
    $fdf .= "/Root 1 0 R\n";
    $fdf .= ">>\n";
    $fdf .= "%%EOF";
    
    return $fdf;
}

function escapeFDFString($string) {
    $string = str_replace('\\', '\\\\', $string);
    $string = str_replace('(', '\(', $string);
    $string = str_replace(')', '\)', $string);
    $string = str_replace("\r", '\\r', $string);
    $string = str_replace("\n", '\\n', $string);
    return $string;
}
?>