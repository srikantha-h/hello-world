<?php
/**
 * Plugin Name: Alaska Travel Blog Functions
 * Description: Enhanced functionality for the Alaska Travel Blog
 * Version: 1.0
 * Author: Custom Plugin
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class AlaskaTravelPlugin {
    
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('wp_ajax_upload_google_photos', array($this, 'handle_google_photos_upload'));
        add_action('wp_ajax_bulk_import_photos', array($this, 'bulk_import_photos'));
        
        // Remove conflicting shortcodes - these are handled by the theme
        // add_shortcode('travel_photo_uploader', array($this, 'photo_uploader_shortcode'));
        // add_shortcode('travel_map', array($this, 'travel_map_shortcode'));
        
        // User registration hooks
        add_action('wp_ajax_register_traveler', array($this, 'register_traveler'));
        add_action('wp_ajax_nopriv_register_traveler', array($this, 'register_traveler'));
    }
    
    public function init() {
        // Create custom database table for photo metadata
        $this->create_photos_table();
        
        // Add custom user fields
        add_action('show_user_profile', array($this, 'add_user_travel_fields'));
        add_action('edit_user_profile', array($this, 'add_user_travel_fields'));
        add_action('personal_options_update', array($this, 'save_user_travel_fields'));
        add_action('edit_user_profile_update', array($this, 'save_user_travel_fields'));
        
        // Create custom user role if it doesn't exist
        $this->create_traveler_role();
    }
    
    public function enqueue_scripts() {
        // Only enqueue if files exist to prevent errors
        $plugin_js = plugin_dir_url(__FILE__) . 'assets/plugin.js';
        $plugin_css = plugin_dir_url(__FILE__) . 'assets/plugin.css';
        
        // Check if files exist before enqueuing
        if (file_exists(plugin_dir_path(__FILE__) . 'assets/plugin.js')) {
            wp_enqueue_script('alaska-travel-plugin', $plugin_js, array('jquery'), '1.0', true);
        }
        
        if (file_exists(plugin_dir_path(__FILE__) . 'assets/plugin.css')) {
            wp_enqueue_style('alaska-travel-plugin', $plugin_css, array(), '1.0');
        }
        
        wp_localize_script('jquery', 'alaska_travel_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('alaska_travel_nonce'),
            'is_logged_in' => is_user_logged_in()
        ));
    }
    
    public function create_photos_table() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'alaska_travel_photos';
        
        // Check if table already exists
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            $charset_collate = $wpdb->get_charset_collate();
            
            $sql = "CREATE TABLE $table_name (
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                travel_day_id bigint(20) NOT NULL,
                photo_url varchar(500) NOT NULL,
                google_photo_id varchar(255),
                caption text,
                upload_user_id bigint(20) NOT NULL,
                upload_date datetime DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY travel_day_id (travel_day_id),
                KEY upload_user_id (upload_user_id)
            ) $charset_collate;";
            
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
        }
    }
    
    public function add_admin_menu() {
        add_menu_page(
            'Alaska Travel Admin',
            'Alaska Travel',
            'manage_options',
            'alaska-travel-admin',
            array($this, 'admin_page'),
            'dashicons-camera',
            30
        );
        
        add_submenu_page(
            'alaska-travel-admin',
            'Photo Management',
            'Photo Management',
            'manage_options',
            'alaska-travel-photos',
            array($this, 'photos_admin_page')
        );
        
        add_submenu_page(
            'alaska-travel-admin',
            'Traveler Management',
            'Travelers',
            'manage_options',
            'alaska-travel-travelers',
            array($this, 'travelers_admin_page')
        );
    }
    
    public function admin_page() {
        ?>
        <div class="wrap">
            <h1>Alaska Travel Blog Admin</h1>
            
            <div class="admin-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-top: 2rem;">
                <div class="admin-card" style="background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                    <h2>Quick Stats</h2>
                    <?php
                    $travel_days = wp_count_posts('travel_day')->publish;
                    $travelers = count_users();
                    $photos_count = $this->get_photos_count();
                    ?>
                    <ul>
                        <li><strong><?php echo $travel_days; ?></strong> Travel Days Created</li>
                        <li><strong><?php echo $travelers['total_users']; ?></strong> Registered Users</li>
                        <li><strong><?php echo $photos_count; ?></strong> Photos Uploaded</li>
                    </ul>
                </div>
                
                <div class="admin-card" style="background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                    <h2>Quick Actions</h2>
                    <p><a href="<?php echo admin_url('post-new.php?post_type=travel_day'); ?>" class="button button-primary">Add New Travel Day</a></p>
                    <p><a href="<?php echo admin_url('admin.php?page=alaska-travel-photos'); ?>" class="button">Manage Photos</a></p>
                    <p><a href="<?php echo admin_url('admin.php?page=alaska-travel-travelers'); ?>" class="button">Manage Travelers</a></p>
                </div>
            </div>
            
            <div class="bulk-import-section" style="background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-top: 2rem;">
                <h2>Bulk Import Google Photos</h2>
                <form id="bulk-import-form">
                    <table class="form-table">
                        <tr>
                            <th><label for="google_album_urls">Google Album URLs (one per line):</label></th>
                            <td>
                                <textarea id="google_album_urls" name="google_album_urls" rows="5" cols="50" placeholder="https://photos.google.com/share/..."></textarea>
                                <p class="description">Enter Google Photos album URLs, one per line. Each will be processed and linked to travel days.</p>
                            </td>
                        </tr>
                    </table>
                    <p><button type="submit" class="button button-primary">Import Albums</button></p>
                </form>
                <div id="import-results"></div>
            </div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            $('#bulk-import-form').on('submit', function(e) {
                e.preventDefault();
                
                var urls = $('#google_album_urls').val();
                if (!urls.trim()) {
                    alert('Please enter at least one Google Photos album URL.');
                    return;
                }
                
                $('#import-results').html('<p>Processing albums...</p>');
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'bulk_import_photos',
                        album_urls: urls,
                        nonce: '<?php echo wp_create_nonce('alaska_travel_nonce'); ?>'
                    },
                    success: function(response) {
                        $('#import-results').html(response);
                        $('#google_album_urls').val('');
                    },
                    error: function() {
                        $('#import-results').html('<p style="color: red;">Error processing albums. Please try again.</p>');
                    }
                });
            });
        });
        </script>
        <?php
    }
    
    public function photos_admin_page() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'alaska_travel_photos';
        $photos = $wpdb->get_results("SELECT * FROM $table_name ORDER BY upload_date DESC LIMIT 50");
        
        ?>
        <div class="wrap">
            <h1>Photo Management</h1>
            
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Photo</th>
                        <th>Travel Day</th>
                        <th>Caption</th>
                        <th>Uploaded by</th>
                        <th>Upload Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($photos as $photo): ?>
                        <tr>
                            <td>
                                <img src="<?php echo esc_url($photo->photo_url); ?>" style="width: 50px; height: 50px; object-fit: cover;">
                            </td>
                            <td>
                                <?php 
                                $travel_day = get_post($photo->travel_day_id);
                                echo $travel_day ? $travel_day->post_title : 'Unknown';
                                ?>
                            </td>
                            <td><?php echo esc_html($photo->caption); ?></td>
                            <td>
                                <?php 
                                $user = get_user_by('id', $photo->upload_user_id);
                                echo $user ? $user->display_name : 'Unknown';
                                ?>
                            </td>
                            <td><?php echo $photo->upload_date; ?></td>
                            <td>
                                <a href="#" class="button button-small delete-photo" data-photo-id="<?php echo $photo->id; ?>">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
    
    public function travelers_admin_page() {
        $travelers = get_users(array('role' => 'traveler'));
        ?>
        <div class="wrap">
            <h1>Traveler Management</h1>
            
            <div class="traveler-registration" style="background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 2rem;">
                <h2>Register New Traveler</h2>
                <form id="register-traveler-form">
                    <table class="form-table">
                        <tr>
                            <th><label for="traveler_username">Username:</label></th>
                            <td><input type="text" id="traveler_username" name="username" required></td>
                        </tr>
                        <tr>
                            <th><label for="traveler_email">Email:</label></th>
                            <td><input type="email" id="traveler_email" name="email" required></td>
                        </tr>
                        <tr>
                            <th><label for="traveler_first_name">First Name:</label></th>
                            <td><input type="text" id="traveler_first_name" name="first_name"></td>
                        </tr>
                        <tr>
                            <th><label for="traveler_last_name">Last Name:</label></th>
                            <td><input type="text" id="traveler_last_name" name="last_name"></td>
                        </tr>
                        <tr>
                            <th><label for="traveler_phone">Phone:</label></th>
                            <td><input type="tel" id="traveler_phone" name="phone"></td>
                        </tr>
                    </table>
                    <p><button type="submit" class="button button-primary">Register Traveler</button></p>
                </form>
                <div id="registration-results"></div>
            </div>
            
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Registration Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($travelers as $traveler): ?>
                        <tr>
                            <td><?php echo esc_html($traveler->user_login); ?></td>
                            <td><?php echo esc_html($traveler->first_name . ' ' . $traveler->last_name); ?></td>
                            <td><?php echo esc_html($traveler->user_email); ?></td>
                            <td><?php echo esc_html(get_user_meta($traveler->ID, 'phone', true)); ?></td>
                            <td><?php echo $traveler->user_registered; ?></td>
                            <td>
                                <a href="<?php echo get_edit_user_link($traveler->ID); ?>" class="button button-small">Edit</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            $('#register-traveler-form').on('submit', function(e) {
                e.preventDefault();
                
                var formData = {
                    action: 'register_traveler',
                    username: $('#traveler_username').val(),
                    email: $('#traveler_email').val(),
                    first_name: $('#traveler_first_name').val(),
                    last_name: $('#traveler_last_name').val(),
                    phone: $('#traveler_phone').val(),
                    nonce: '<?php echo wp_create_nonce('alaska_travel_nonce'); ?>'
                };
                
                $('#registration-results').html('<p>Registering traveler...</p>');
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        $('#registration-results').html(response);
                        if (response.includes('successfully')) {
                            $('#register-traveler-form')[0].reset();
                            location.reload();
                        }
                    },
                    error: function() {
                        $('#registration-results').html('<p style="color: red;">Error registering traveler. Please try again.</p>');
                    }
                });
            });
        });
        </script>
        <?php
    }
    
    public function get_photos_count() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'alaska_travel_photos';
        return $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
    }
    
    public function handle_google_photos_upload() {
        check_ajax_referer('alaska_travel_nonce', 'nonce');
        
        $album_url = sanitize_url($_POST['album_url']);
        $travel_day_id = intval($_POST['travel_day_id']);
        $user_id = get_current_user_id();

        if (!current_user_can('edit_post', $travel_day_id)) {
            wp_die('Insufficient permissions.');
        }
        
        if (!$album_url || !$travel_day_id) {
            wp_die('Invalid data provided.');
        }
        
        // Store the album URL in the travel day meta
        update_post_meta($travel_day_id, '_google_album_url', $album_url);
        
        // Log the photo upload
        global $wpdb;
        $table_name = $wpdb->prefix . 'alaska_travel_photos';
        
        $wpdb->insert(
            $table_name,
            array(
                'travel_day_id' => $travel_day_id,
                'photo_url' => $album_url,
                'caption' => 'Google Photos Album',
                'upload_user_id' => $user_id,
                'upload_date' => current_time('mysql')
            ),
            array('%d', '%s', '%s', '%d', '%s')
        );
        
        echo 'Photos uploaded successfully!';
        wp_die();
    }
    
    public function bulk_import_photos() {
        check_ajax_referer('alaska_travel_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions.');
        }
        
        $album_urls = sanitize_textarea_field($_POST['album_urls']);
        $urls = explode("\n", $album_urls);
        
        $results = array();
        $processed = 0;
        
        foreach ($urls as $url) {
            $url = trim($url);
            if (empty($url)) continue;
            
            // Try to match with existing travel days based on patterns or manual assignment
            // For now, we'll create a general import record
            global $wpdb;
            $table_name = $wpdb->prefix . 'alaska_travel_photos';
            
            $wpdb->insert(
                $table_name,
                array(
                    'travel_day_id' => 0, // Will be assigned later
                    'photo_url' => $url,
                    'caption' => 'Bulk Import - Needs Assignment',
                    'upload_user_id' => get_current_user_id(),
                    'upload_date' => current_time('mysql')
                ),
                array('%d', '%s', '%s', '%d', '%s')
            );
            
            $processed++;
        }
        
        echo "<p style='color: green;'>Successfully imported {$processed} photo albums. Please assign them to specific travel days.</p>";
        wp_die();
    }
    
    public function register_traveler() {
        check_ajax_referer('alaska_travel_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions.');
        }
        
        $username = sanitize_user($_POST['username']);
        $email = sanitize_email($_POST['email']);
        $first_name = sanitize_text_field($_POST['first_name']);
        $last_name = sanitize_text_field($_POST['last_name']);
        $phone = sanitize_text_field($_POST['phone']);
        
        if (!$username || !$email) {
            echo '<p style="color: red;">Username and email are required.</p>';
            wp_die();
        }
        
        $user_id = wp_create_user($username, wp_generate_password(), $email);
        
        if (is_wp_error($user_id)) {
            echo '<p style="color: red;">Error creating user: ' . $user_id->get_error_message() . '</p>';
            wp_die();
        }
        
        // Set user role to traveler
        $user = new WP_User($user_id);
        $user->set_role('traveler');
        
        // Update user meta
        update_user_meta($user_id, 'first_name', $first_name);
        update_user_meta($user_id, 'last_name', $last_name);
        update_user_meta($user_id, 'phone', $phone);
        
        echo '<p style="color: green;">Traveler registered successfully! Login details have been emailed to them.</p>';
        
        // Send welcome email
        wp_new_user_notification($user_id, null, 'both');
        
        wp_die();
    }
    
    public function create_traveler_role() {
        // Create traveler role if it doesn't exist
        if (!get_role('traveler')) {
            add_role('traveler', 'Traveler', array(
                'read' => true,
                'edit_posts' => false,
                'delete_posts' => false,
                'upload_files' => true,
                'edit_travel_day' => true,
                'edit_travel_days' => true,
                'edit_private_travel_days' => true,
                'edit_published_travel_days' => true,
                'publish_travel_days' => false,
                'read_private_travel_days' => true,
                'delete_travel_day' => false,
                'delete_travel_days' => false,
                'delete_private_travel_days' => false,
                'delete_published_travel_days' => false,
                'edit_others_travel_days' => false,
                'publish_travel_days' => false,
                'read_private_travel_days' => true,
                'delete_others_travel_days' => false
            ));
        }
    }
    
    public function add_user_travel_fields($user) {
        ?>
        <h3>Travel Information</h3>
        <table class="form-table">
            <tr>
                <th><label for="phone">Phone Number</label></th>
                <td>
                    <input type="tel" name="phone" id="phone" value="<?php echo esc_attr(get_user_meta($user->ID, 'phone', true)); ?>" class="regular-text" />
                    <p class="description">Your contact phone number for the trip.</p>
                </td>
            </tr>
            <tr>
                <th><label for="emergency_contact">Emergency Contact</label></th>
                <td>
                    <input type="text" name="emergency_contact" id="emergency_contact" value="<?php echo esc_attr(get_user_meta($user->ID, 'emergency_contact', true)); ?>" class="regular-text" />
                    <p class="description">Emergency contact person and phone number.</p>
                </td>
            </tr>
            <tr>
                <th><label for="dietary_restrictions">Dietary Restrictions</label></th>
                <td>
                    <textarea name="dietary_restrictions" id="dietary_restrictions" rows="3" cols="30"><?php echo esc_textarea(get_user_meta($user->ID, 'dietary_restrictions', true)); ?></textarea>
                    <p class="description">Any dietary restrictions or allergies.</p>
                </td>
            </tr>
        </table>
        <?php
    }
    
    public function save_user_travel_fields($user_id) {
        if (!current_user_can('edit_user', $user_id)) {
            return false;
        }
        
        update_user_meta($user_id, 'phone', sanitize_text_field($_POST['phone']));
        update_user_meta($user_id, 'emergency_contact', sanitize_text_field($_POST['emergency_contact']));
        update_user_meta($user_id, 'dietary_restrictions', sanitize_textarea_field($_POST['dietary_restrictions']));
    }
}

// Initialize the plugin
new AlaskaTravelPlugin();

// Custom widget for travel countdown
class Alaska_Travel_Countdown_Widget extends WP_Widget {
    
    public function __construct() {
        parent::__construct(
            'alaska_travel_countdown',
            'Travel Countdown',
            array('description' => 'Shows countdown to travel start date')
        );
    }
    
    public function widget($args, $instance) {
        echo $args['before_widget'];
        
        if (!empty($instance['title'])) {
            echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
        }
        
        $start_date = !empty($instance['start_date']) ? $instance['start_date'] : '2025-06-08';
        $start_timestamp = strtotime($start_date);
        $current_timestamp = time();
        
        if ($current_timestamp < $start_timestamp) {
            $diff = $start_timestamp - $current_timestamp;
            $days = floor($diff / (60 * 60 * 24));
            echo '<div class="travel-countdown">';
            echo '<p><strong>' . $days . ' days</strong> until our Alaska adventure begins!</p>';
            echo '<p class="countdown-date">Starting: ' . date('F j, Y', $start_timestamp) . '</p>';
            echo '</div>';
        } else {
            echo '<div class="travel-countdown">';
            echo '<p><strong>Our Alaska adventure is underway!</strong></p>';
            echo '<p>Started: ' . date('F j, Y', $start_timestamp) . '</p>';
            echo '</div>';
        }
        
        echo $args['after_widget'];
    }
    
    public function form($instance) {
        $title = !empty($instance['title']) ? $instance['title'] : 'Travel Countdown';
        $start_date = !empty($instance['start_date']) ? $instance['start_date'] : '2025-06-08';
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>">Title:</label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('start_date'); ?>">Start Date:</label>
            <input class="widefat" id="<?php echo $this->get_field_id('start_date'); ?>" name="<?php echo $this->get_field_name('start_date'); ?>" type="date" value="<?php echo esc_attr($start_date); ?>">
        </p>
        <?php
    }
    
    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';
        $instance['start_date'] = (!empty($new_instance['start_date'])) ? strip_tags($new_instance['start_date']) : '';
        return $instance;
    }
}

// Register the widget
function register_alaska_travel_widgets() {
    register_widget('Alaska_Travel_Countdown_Widget');
}
add_action('widgets_init', 'register_alaska_travel_widgets');

// Add custom CSS for admin
function alaska_travel_admin_styles() {
    echo '<style>
        .travel-day-admin .form-table th { width: 200px; }
        .travel-countdown { text-align: center; background: #e8f4fd; padding: 1rem; border-radius: 8px; }
        .countdown-date { font-size: 0.9em; color: #666; }
        .admin-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; }
        .admin-card { background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    </style>';
}
add_action('admin_head', 'alaska_travel_admin_styles');
add_action('wp_head', 'alaska_travel_admin_styles');

// Custom login redirect for travelers
function alaska_travel_login_redirect($redirect_to, $request, $user) {
    if (isset($user->roles) && is_array($user->roles)) {
        if (in_array('traveler', $user->roles)) {
            return home_url('/travel-days/');
        }
    }
    return $redirect_to;
}
add_filter('login_redirect', 'alaska_travel_login_redirect', 10, 3);

// Add custom post states
function alaska_travel_post_states($post_states, $post) {
    if ($post->post_type == 'travel_day') {
        $travel_date = get_post_meta($post->ID, '_travel_date', true);
        if ($travel_date) {
            $post_states[] = date('M j', strtotime($travel_date));
        }
    }
    return $post_states;
}
add_filter('display_post_states', 'alaska_travel_post_states', 10, 2);
?>