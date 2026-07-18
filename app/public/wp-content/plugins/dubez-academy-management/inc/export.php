<?php
if (!defined('ABSPATH')) {
    exit;
}

function dubez_export_csv($filename, $headers, $rows) {

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="'.$filename.'"');

    $output = fopen('php://output', 'w');

    fputcsv($output, $headers);

    foreach ($rows as $row) {
        fputcsv($output, $row);
    }

    fclose($output);
    exit;
}