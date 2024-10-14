<?php /* Plugin Name: Visitor list admin page 
Description: Shows the table with the visitor entries from the database using the shortcode: [display_visitors] 
Version: 1.0 Author: Luis Wilkens */

// Shortcode to display the visitors table 
add_shortcode('display_visitors', 'display_visitors_function');

function display_visitors_function() { global $wpdb;

$results = $wpdb->get_results("SELECT * FROM _visitors", OBJECT);

if (empty($results)) {
    return '<p>Es befinden sich keine Besucher im Gebäude.</p>';
}

//Function for creating the table with the visitor entries

$output = '<table class="wp-list-table widefat fixed striped">';
$output .= '<thead><tr><th>ID</th>
						<th>Firmenname</th>
						<th>Vorname</th>
						<th>Nachname</th>
						<th>Ansprechpartner</th>
						<th>Ankunftszeit</th>
						<th>Geh-Zeit</th>
						<th>Abmeldecode</th>
						</tr></thead>';
$output .= '<tbody>';
foreach ($results as $row) {
    $output .= '<tr>';
    $output .= '<td>' . esc_html($row->id) . '</td>';
    $output .= '<td>' . esc_html($row->firmenname) . '</td>';
    $output .= '<td>' . esc_html($row->vorname) . '</td>';
    $output .= '<td>' . esc_html($row->nachname) . '</td>';
    $output .= '<td>' . esc_html($row->ansprechpartner) . '</td>';
    $output .= '<td>' . esc_html($row->ankunftszeit) . '</td>';
    $output .= '<td>' . esc_html($row->gehzeit) . '</td>';
    $output .= '<td>' . esc_html($row->abmeldung_code) . '</td>';
    $output .= '</tr>';
}
$output .= '</tbody>';
$output .= '</table>';

return $output;
} ?>