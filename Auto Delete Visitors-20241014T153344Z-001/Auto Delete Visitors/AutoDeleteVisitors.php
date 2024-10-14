<?php
/*
Plugin Name: Autodelete Visitor Table
Description: Automatic deletion of visitor entries from the database table every 30 days
Version: 1.0 | Author: Luis Wilkens
*/

//Function for emptying the table
function empty_visitors_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'visitors';
    $sql = "TRUNCATE TABLE $table_name";
    $wpdb->query($sql);
}

// Schedule cron job
function schedule_empty_visitors_table_cron() {
    if (!wp_next_scheduled('empty_visitors_table_cron_hook')) {
        $current_time = current_time('timestamp');
        // Calculate the last day of the current month
        $next_run = strtotime('last day of this month', $current_time);
        
        // If today is the last day of the month, schedule the next last day of the next month
        if (date('j', $current_time) == date('t', $current_time)) {
            $next_run = strtotime('last day of next month', $current_time);
        }

        wp_schedule_event($next_run, 'monthly', 'empty_visitors_table_cron_hook');
    }
}
add_action('wp', 'schedule_empty_visitors_table_cron');

// Register cron job hook
add_action('empty_visitors_table_cron_hook', 'empty_visitors_table');

// Remove cron job
function unschedule_empty_visitors_table_cron() {
    $timestamp = wp_next_scheduled('empty_visitors_table_cron_hook');
    wp_unschedule_event($timestamp, 'empty_visitors_table_cron_hook');
}
register_deactivation_hook(__FILE__, 'unschedule_empty_visitors_table_cron');

// Activate plugin
register_activation_hook(__FILE__, 'schedule_empty_visitors_table_cron');