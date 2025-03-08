<?php
/**
 * Plugin Name: Client Data Sender
 * Description: Sends data to a central server.
 * Version: 1.3
 * Author: Your Name
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class ClientDataSender {

    private $api_url = 'https://rabbitwebdesign.co.uk/wp-json/data-collector/v1/receive'; // Change this to your central site URL

    public function __construct() {
        add_action('admin_init', [$this, 'send_data_to_central']);
    }
    
    public function send_data_to_central() {
    if (get_transient('client_data_sent')) {
        return; // Prevent sending data too often
    }

    $theme = wp_get_theme(); // Get active theme details
    $data = [
        'site_url' => get_site_url(),
        'admin_email' => get_option('admin_email'),
        'active_plugins' => get_option('active_plugins'),
        'theme' => [
            'name' => $theme->get('Name'),
            'version' => $theme->get('Version'),
            'author' => $theme->get('Author'),
            'theme_uri' => $theme->get('ThemeURI'),
        ],
    ];

    $response = wp_remote_post($this->api_url, [
        'method'    => 'POST',
        'body'      => json_encode($data),
        'headers'   => [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . md5(get_site_url()), // Basic security
        ],
        'timeout'   => 10,
    ]);

    if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) == 200) {
        set_transient('client_data_sent', true, 12 * HOUR_IN_SECONDS); // Limit sending every 12 hours
    }
}


}

new ClientDataSender();
