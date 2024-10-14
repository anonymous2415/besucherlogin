<form method="post" action="">
    <!-- Input field for the check-out code, marked as required -->
    <label for="abmeldung_code">Abmeldecode:</label>
    <input type="text" id="abmeldung_code" name="abmeldung_code" required class="felder">
    <!-- Submit button to check the code -->
    <input type="submit" name="submit_check_code" value="Code überprüfen" class="form-submit-button">
</form>

<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_check_code'])) {
    // Sanitize the input check-out code
    $abmeldung_code = sanitize_text_field($_POST['abmeldung_code']);

    // Database query to check the validity of the check-out code
    global $wpdb;
    $table_name = $wpdb->prefix . 'visitors';
    $visitor = $wpdb->get_row($wpdb->prepare("SELECT id, firmenname, vorname, nachname FROM $table_name WHERE abmeldung_code = %s AND gehzeit IS NULL", $abmeldung_code));

    if ($visitor) {
        // Display visitor information and request confirmation for check-out
        echo '<p>Abmeldung für ' . esc_html($visitor->firmenname) . ' - ' . esc_html($visitor->vorname) . ' ' . esc_html($visitor->nachname) . '?</p>';
        echo '<form method="post" action="">
                <input type="hidden" name="abmeldung_code" value="' . esc_attr($abmeldung_code) . '">
                <input type="submit" name="submit_confirm_yes" value="Ja" class="form-submit-button">
                <input type="submit" name="submit_confirm_no" value="Nein" class="form-submit-button">
              </form>';
    } else {
        // Invalid check-out code or visitor already checked out
        echo '<p>Ungültiger Abmeldecode oder Besucher bereits abgemeldet.</p>';
    }
} elseif ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_confirm_yes'])) {
    // Sanitize the check-out code again for confirmation
    $abmeldung_code = sanitize_text_field($_POST['abmeldung_code']);

    // Database query to check the validity of the check-out code and perform check-out
    global $wpdb;
    $table_name = $wpdb->prefix . 'visitors';
    $visitor = $wpdb->get_row($wpdb->prepare("SELECT id, firmenname, vorname, nachname FROM $table_name WHERE abmeldung_code = %s AND gehzeit IS NULL", $abmeldung_code));

    if ($visitor) {
        // Perform the check-out process
        $wpdb->update(
            $table_name,
            array('gehzeit' => current_time('mysql')),
            array('id' => $visitor->id)
        );

        echo '<p>Abmeldung erfolgreich für ' . esc_html($visitor->firmenname) . ' - ' . esc_html($visitor->vorname) . ' ' . esc_html($visitor->nachname) . '. </p>';

        // JavaScript for redirecting after 5 seconds
        echo '<script>
                setTimeout(function() {
                    window.location.href = "link-zur-wordpress-seite";
                }, 5000); // 5000 Millisekunden = 5 Sekunden
              </script>';
    } else {
        // Invalid check-out code or visitor already checked out
        echo '<p>Ungültiger Abmeldecode oder Besucher bereits abgemeldet.</p>';
    }
} elseif ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_confirm_no'])) {
    // Refresh the page if the user selects "No"
    echo '<script>
            window.location.href = window.location.href;
          </script>';
} elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // If the form was submitted without a valid check-out code
    echo '<p>Ungültiger Abmeldecode. Bitte geben Sie einen gültigen Code ein.</p>';
}
?>