<?php
/*
Plugin Name: Visitor login
Description: This WordPress plugin enables the management of visitor data. It creates two database tables: 
1.'visitors' to store visitor information (company name, first name, last name, contact person, arrival time, logout code and consent).
2. 'contacts' to retrieve the names of contact persons (first name, last name).

The plugin provides a form for entering visitor data with an autocomplete function for the contact person field. 
After submitting the form, a unique logout code is generated and a print preview with the visitor information is displayed. 
visitor information is displayed. It contains shortcodes for integrating the registration and deregistration form into WordPress pages or posts.
Version: 1.0
Author: Luis Wilkens
*/

function visitor_form_enqueue_styles() {
    // Enqueue styles and scripts for the visitor form
    wp_enqueue_style('visitor-form-styles', plugin_dir_url(__FILE__) . 'styles.css');
    wp_enqueue_script('jquery');
    wp_enqueue_script('jquery-ui-autocomplete');
    wp_enqueue_style('jquery-ui-css', 'https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css');
}
add_action('wp_enqueue_scripts', 'visitor_form_enqueue_styles');

function create_visitors_table() {
    // Create the 'visitors' table in the database to store visitor information
    global $wpdb;
    $table_name = $wpdb->prefix . 'visitors';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        firmenname varchar(100) NOT NULL,
        vorname varchar(50) NOT NULL,
        nachname varchar(50) NOT NULL,
        ansprechpartner varchar(100) NOT NULL,
        ankunftszeit datetime DEFAULT CURRENT_TIMESTAMP,
        gehzeit datetime NULL,
        einwilligung boolean NOT NULL DEFAULT FALSE,
        abmeldung_code int(4) NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
register_activation_hook(__FILE__, 'create_visitors_table');

function create_contacts_table() {
    // Create the 'contacts' table in the database to store contact information
    global $wpdb;
    $table_name = $wpdb->prefix . 'contacts';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        vorname varchar(50) NOT NULL,
        nachname varchar(50) NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
register_activation_hook(__FILE__, 'create_contacts_table');

function ajax_autocomplete_contacts() {
    // Handle AJAX request for autocomplete suggestions for contacts
    global $wpdb;
    $search = sanitize_text_field($_GET['term']);
    $table_name = $wpdb->prefix . 'contacts';

    $results = $wpdb->get_results($wpdb->prepare("SELECT vorname, nachname FROM $table_name WHERE vorname LIKE %s OR nachname LIKE %s LIMIT 10", '%' . $wpdb->esc_like($search) . '%', '%' . $wpdb->esc_like($search) . '%'));

    $suggestions = array();
    foreach ($results as $result) {
        $suggestions[] = $result->vorname . ' ' . $result->nachname;
    }

    echo json_encode($suggestions);
    wp_die();
}
add_action('wp_ajax_nopriv_autocomplete_contacts', 'ajax_autocomplete_contacts');
add_action('wp_ajax_autocomplete_contacts', 'ajax_autocomplete_contacts');

function add_autocomplete_script() {
    // Add JavaScript for the autocomplete functionality on the contact field
    ?>
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            $('#ansprechpartner').autocomplete({
                source: function(request, response) {
                    $.ajax({
                        url: '<?php echo admin_url('admin-ajax.php'); ?>',
                        dataType: 'json',
                        data: {
                            action: 'autocomplete_contacts',
                            term: request.term
                        },
                        success: function(data) {
                            response(data);
                        }
                    });
                },
                minLength: 2
            });
        });
    </script>
    <?php
}
add_action('wp_footer', 'add_autocomplete_script');

function generate_unique_code($length = 4) {
    // Generate a unique 4-digit code for visitor check-out
    $characters = '0123456789';
    $characters_length = strlen($characters);
    $random_string = '';
    for ($i = 0; $i < $length; $i++) {
        $random_string .= $characters[rand(0, $characters_length - 1)];
    }
    return $random_string;
}

function process_visitor_form() {
    // Process the visitor form submission and store data in the database
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_visitor'])) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'visitors';

        $firmenname = sanitize_text_field($_POST['firmenname']);
        $vorname = sanitize_text_field($_POST['vorname']);
        $nachname = sanitize_text_field($_POST['nachname']);
        $ansprechpartner = sanitize_text_field($_POST['ansprechpartner']);
        $abmeldung_code = generate_unique_code();

        $wpdb->insert(
            $table_name,
            array(
                'firmenname' => $firmenname,
                'vorname' => $vorname,
                'nachname' => $nachname,
                'ansprechpartner' => $ansprechpartner,
                'abmeldung_code' => $abmeldung_code
            )
        );
        // JavaScript to open a window with the visitor's code
        echo '<script type="text/javascript">
            document.addEventListener("DOMContentLoaded", function() {
                var code = "' . $abmeldung_code . '";
                var firmenname = "' . $firmenname . '";
                var vorname = "' . $vorname . '";
                var nachname = "' . $nachname . '";
                var ansprechpartner = "' . $ansprechpartner . '";
                var screenWidth = window.screen.width;
                var screenHeight = window.screen.height;
                var width = 600;
                var height = 400;
                var left = (screenWidth / 2) - (width / 2);
                var top = (screenHeight / 2) - (height / 2);
                var printWindow = window.open("", "", "width=" + width + ",height=" + height + ",left=" + left + ",top=" + top);
                printWindow.document.write("<html><head><title>Druckansicht</title><style>");
                printWindow.document.write("@page { size: A6; margin: 0; }");
                printWindow.document.write(".title { text-align: center; margin-top: 5px; font-size: 22px; color: black; letter-spacing: 2px}");
                printWindow.document.write(".title2 { text-align: center; margin-top: 5px; font-size: 20px; color: black; letter-spacing: 2px}");
                printWindow.document.write(".info { text-align: left; font-size: 17px; margin-top: 10px; margin-left: 10px; color: black;}");
                printWindow.document.write(".code { position: absolute; bottom: 10px; right: 10px; font-size: 20px; text-align: right; color: black;}");
                printWindow.document.write(".link { position: absolute; bottom: 10px; left: 10px; font-size: 18px; text-align: left; color: black;}");
				printWindow.document.write(".image {;}");
                printWindow.document.write("</style>");
                printWindow.document.write("<link href=\"https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;700&display=swap\" rel=\"stylesheet\">");
                printWindow.document.write("</head><body>");
                printWindow.document.write("<div class=\'title\'><u>Besucherausweis</u></div>");
                printWindow.document.write("<div class=\'title2\'><i>Firmenname</i></div>");
                printWindow.document.write("<div class=\'info\'>");
                printWindow.document.write("<p>Firma: " + firmenname + "<br>Name: " + vorname + " " + nachname + "<br>Ansprechpartner: " + ansprechpartner + "</p>");
                printWindow.document.write("</div>");
                printWindow.document.write("<div class=\'link\'>Abmeldung durch<br>Ansprechpartner</div>");
                printWindow.document.write("<div class=\'code\'>Code: " + code + "</div>");
                printWindow.document.write("</body></html>");
                printWindow.document.close();
                printWindow.focus();
                printWindow.print();
                
            });
        </script>';
    }
}
add_action('init', 'process_visitor_form');

function visitor_form_shortcode() {
    // Create a shortcode to display the visitor registration form
    ob_start();
    include plugin_dir_path(__FILE__) . 'Anmeldeformular.php';
    return ob_get_clean();
}
add_shortcode('visitor_form', 'visitor_form_shortcode');

function checkout_form_shortcode() {
    // Create a shortcode to display the visitor check-out form
    ob_start();
    include plugin_dir_path(__FILE__) . 'Abmeldeformular.php';
    return ob_get_clean();
}
add_shortcode('checkout_form', 'checkout_form_shortcode');