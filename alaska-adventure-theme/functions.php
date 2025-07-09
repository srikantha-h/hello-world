<?php
/**
 * Theme Name: Alaska Adventure Blog
 * Description: A custom theme for Srikantha & Cecilia's Alaska travel blog
 * Version: 1.0
 * Author: Custom Theme
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Theme setup
function alaska_travel_theme_setup() {
    add_theme_support('post-thumbnails');
    add_theme_support('title-tag');
    add_theme_support('custom-logo');
    
    // Register navigation menus
    register_nav_menus(array(
        'primary' => 'Primary Menu',
        'footer' => 'Footer Menu'
    ));
}
add_action('after_setup_theme', 'alaska_travel_theme_setup');

// Enqueue styles and scripts
function alaska_travel_enqueue_scripts() {
    wp_enqueue_style('alaska-travel-style', get_stylesheet_uri());
    wp_enqueue_script('alaska-travel-script', get_template_directory_uri() . '/assets/js/custom.js', array('jquery'), '1.0', true);
    
    // Localize script for AJAX
    wp_localize_script('alaska-travel-script', 'ajax_object', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('alaska_travel_nonce')
    ));
}
add_action('wp_enqueue_scripts', 'alaska_travel_enqueue_scripts');

// Custom post type for travel days
function create_travel_day_post_type() {
    $labels = array(
        'name' => 'Travel Days',
        'singular_name' => 'Travel Day',
        'add_new' => 'Add New Day',
        'add_new_item' => 'Add New Travel Day',
        'edit_item' => 'Edit Travel Day',
        'new_item' => 'New Travel Day',
        'view_item' => 'View Travel Day',
        'search_items' => 'Search Travel Days',
        'not_found' => 'No travel days found',
        'not_found_in_trash' => 'No travel days found in Trash'
    );

    $args = array(
        'labels' => $labels,
        'public' => true,
        'has_archive' => true,
        'rewrite' => array('slug' => 'travel-days'),
        'supports' => array('title', 'editor', 'thumbnail', 'custom-fields'),
        'menu_icon' => 'dashicons-calendar-alt'
    );

    register_post_type('travel_day', $args);
}
add_action('init', 'create_travel_day_post_type');

// Custom taxonomies
function create_travel_taxonomies() {
    // Location taxonomy
    $location_args = array(
        'label' => 'Locations',
        'rewrite' => array('slug' => 'location'),
        'hierarchical' => true,
        'public' => true,
        'show_ui' => true,
        'show_admin_column' => true,
    );
    register_taxonomy('travel_location', 'travel_day', $location_args);
    
    // Activity type taxonomy
    $activity_args = array(
        'label' => 'Activity Types',
        'rewrite' => array('slug' => 'activity'),
        'hierarchical' => true,
        'public' => true,
        'show_ui' => true,
        'show_admin_column' => true,
    );
    register_taxonomy('activity_type', 'travel_day', $activity_args);
}
add_action('init', 'create_travel_taxonomies');

// Add custom meta boxes
function add_travel_day_meta_boxes() {
    add_meta_box(
        'travel_day_details',
        'Travel Day Details',
        'travel_day_details_callback',
        'travel_day',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'add_travel_day_meta_boxes');

function travel_day_details_callback($post) {
    wp_nonce_field('travel_day_details_nonce', 'travel_day_details_nonce');
    
    $travel_date = get_post_meta($post->ID, '_travel_date', true);
    $google_album_url = get_post_meta($post->ID, '_google_album_url', true);
    $weather = get_post_meta($post->ID, '_weather', true);
    $accommodation = get_post_meta($post->ID, '_accommodation', true);
    
    echo '<table class="form-table">';
    echo '<tr><th><label for="travel_date">Travel Date:</label></th>';
    echo '<td><input type="date" id="travel_date" name="travel_date" value="' . esc_attr($travel_date) . '" /></td></tr>';
    
    echo '<tr><th><label for="google_album_url">Google Photos Album URL:</label></th>';
    echo '<td><input type="url" id="google_album_url" name="google_album_url" value="' . esc_attr($google_album_url) . '" style="width: 100%;" /></td></tr>';
    
    echo '<tr><th><label for="weather">Weather:</label></th>';
    echo '<td><input type="text" id="weather" name="weather" value="' . esc_attr($weather) . '" /></td></tr>';
    
    echo '<tr><th><label for="accommodation">Accommodation:</label></th>';
    echo '<td><input type="text" id="accommodation" name="accommodation" value="' . esc_attr($accommodation) . '" style="width: 100%;" /></td></tr>';
    echo '</table>';
}

// Save meta box data
function save_travel_day_meta($post_id) {
    if (!isset($_POST['travel_day_details_nonce']) || !wp_verify_nonce($_POST['travel_day_details_nonce'], 'travel_day_details_nonce')) {
        return;
    }
    
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    
    $fields = array('travel_date', 'google_album_url', 'weather', 'accommodation');
    
    foreach ($fields as $field) {
        if (isset($_POST[$field])) {
            update_post_meta($post_id, '_' . $field, sanitize_text_field($_POST[$field]));
        }
    }
}
add_action('save_post', 'save_travel_day_meta');

// Register sidebar
function alaska_travel_widgets_init() {
    register_sidebar(array(
        'name' => 'Travel Sidebar',
        'id' => 'travel-sidebar',
        'description' => 'Sidebar for travel pages',
        'before_widget' => '<div class="widget %2$s">',
        'after_widget' => '</div>',
        'before_title' => '<h3 class="widget-title">',
        'after_title' => '</h3>',
    ));
}
add_action('widgets_init', 'alaska_travel_widgets_init');

// Simple shortcode for travel timeline
function travel_timeline_shortcode($atts) {
    $atts = shortcode_atts(array(
        'limit' => -1,
        'location' => '',
        'activity' => ''
    ), $atts);
    
    $args = array(
        'post_type' => 'travel_day',
        'posts_per_page' => intval($atts['limit']),
        'meta_key' => '_travel_date',
        'orderby' => 'meta_value',
        'order' => 'ASC'
    );
    
    if (!empty($atts['location'])) {
        $args['tax_query'][] = array(
            'taxonomy' => 'travel_location',
            'field' => 'slug',
            'terms' => $atts['location']
        );
    }
    
    if (!empty($atts['activity'])) {
        $args['tax_query'][] = array(
            'taxonomy' => 'activity_type',
            'field' => 'slug',
            'terms' => $atts['activity']
        );
    }
    
    $travel_days = new WP_Query($args);
    
    ob_start();
    
    if ($travel_days->have_posts()) {
        echo '<div class="travel-timeline">';
        while ($travel_days->have_posts()) {
            $travel_days->the_post();
            $travel_date = get_post_meta(get_the_ID(), '_travel_date', true);
            $weather = get_post_meta(get_the_ID(), '_weather', true);
            
            echo '<div class="timeline-item">';
            echo '<div class="timeline-date">' . ($travel_date ? date('M j, Y', strtotime($travel_date)) : '') . '</div>';
            echo '<div class="timeline-content">';
            echo '<h3><a href="' . get_permalink() . '">' . get_the_title() . '</a></h3>';
            if ($weather) echo '<p class="weather">Weather: ' . esc_html($weather) . '</p>';
            echo '<div class="excerpt">' . get_the_excerpt() . '</div>';
            echo '<a href="' . get_permalink() . '" class="read-more">Read More</a>';
            echo '</div>';
            echo '</div>';
        }
        echo '</div>';
    }
    
    wp_reset_postdata();
    return ob_get_clean();
}
add_shortcode('travel_timeline', 'travel_timeline_shortcode');

// Simple photo uploader shortcode
function travel_photo_uploader_shortcode($atts) {
    $atts = shortcode_atts(array(
        'travel_day_id' => get_the_ID()
    ), $atts);
    
    if (!is_user_logged_in()) {
        return '<p>Please log in to upload photos.</p>';
    }
    
    ob_start();
    ?>
    <div class="photo-uploader">
        <h3>Upload Photos</h3>
        <form id="photo-upload-form">
            <div class="form-group">
                <label for="google_album_url">Google Photos Album URL:</label>
                <input type="url" id="google_album_url" name="album_url" placeholder="https://photos.google.com/share/..." required>
                <p class="description">Share your Google Photos album and paste the link here.</p>
            </div>
            <button type="submit" class="button button-primary">Upload Photos</button>
        </form>
        <div id="upload-results"></div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('travel_photo_uploader', 'travel_photo_uploader_shortcode');

// Simple travel map shortcode
function travel_map_shortcode($atts) {
    $atts = shortcode_atts(array(
        'height' => '400px'
    ), $atts);
    
    ob_start();
    ?>
    <div id="travel-map" style="height: <?php echo esc_attr($atts['height']); ?>; width: 100%; background: linear-gradient(135deg, #667db6 0%, #0082c8 100%); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white; text-align: center;">
        <div>
            <h3>🗺️ Alaska Adventure Route Map</h3>
            <p>Alaska → Canada → American West</p>
            <p>May 29 - June 18, 2025</p>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('travel_map', 'travel_map_shortcode');

// Create initial travel days (simplified)
function populate_initial_travel_days() {
    if (get_option('alaska_travel_days_created') == 'yes') {
        return; // Already created
    }
    
    $travel_days = array(
        array('title' => 'Matanuska Glacier Tour', 'date' => '2025-05-29', 'location' => 'Anchorage', 'activity' => 'Glacier Trekking'),
        array('title' => 'Denali Star Train & Glacier Landing', 'date' => '2025-05-30', 'location' => 'Talkeetna', 'activity' => 'Train & Flight'),
        array('title' => 'Turnagain Arm & Wildlife Center', 'date' => '2025-05-31', 'location' => 'Anchorage', 'activity' => 'Wildlife Tour'),
        array('title' => 'Embark Holland America Noordam', 'date' => '2025-06-01', 'location' => 'Whittier', 'activity' => 'Cruise'),
        array('title' => 'Hubbard Glacier Cruising', 'date' => '2025-06-02', 'location' => 'Hubbard Glacier', 'activity' => 'Cruise'),
        array('title' => 'Glacier Bay National Park', 'date' => '2025-06-03', 'location' => 'Glacier Bay', 'activity' => 'Cruise'),
        array('title' => 'Skagway - Gold Rush Town', 'date' => '2025-06-04', 'location' => 'Skagway', 'activity' => 'Shore Excursion'),
        array('title' => 'Juneau - Alaska Capital', 'date' => '2025-06-05', 'location' => 'Juneau', 'activity' => 'Shore Excursion'),
        array('title' => 'Ketchikan - Salmon Capital', 'date' => '2025-06-06', 'location' => 'Ketchikan', 'activity' => 'Shore Excursion'),
        array('title' => 'Inside Passage Cruising', 'date' => '2025-06-07', 'location' => 'Inside Passage', 'activity' => 'Cruise'),
        array('title' => 'Whistler Mountain & Gondola', 'date' => '2025-06-08', 'location' => 'Vancouver', 'activity' => 'Sightseeing'),
        array('title' => 'Victoria & Butchart Gardens', 'date' => '2025-06-09', 'location' => 'Vancouver', 'activity' => 'Sightseeing'),
        array('title' => 'Stanley Park Vancouver', 'date' => '2025-06-10', 'location' => 'Vancouver', 'activity' => 'Sightseeing'),
        array('title' => 'Travel to Minneapolis', 'date' => '2025-06-11', 'location' => 'Minneapolis', 'activity' => 'Travel'),
        array('title' => 'Corn Palace & Mt Rushmore', 'date' => '2025-06-12', 'location' => 'South Dakota', 'activity' => 'Sightseeing'),
        array('title' => 'Sturgis & Devil\'s Tower', 'date' => '2025-06-13', 'location' => 'South Dakota', 'activity' => 'Sightseeing'),
        array('title' => 'Yellowstone National Park', 'date' => '2025-06-14', 'location' => 'Wyoming', 'activity' => 'Nature'),
        array('title' => 'Theodore Roosevelt National Park', 'date' => '2025-06-15', 'location' => 'Montana', 'activity' => 'Nature'),
        array('title' => 'Crab Dinner Minneapolis', 'date' => '2025-06-16', 'location' => 'Minneapolis', 'activity' => 'Dining'),
        array('title' => 'Mall of America & Recreation', 'date' => '2025-06-17', 'location' => 'Minneapolis', 'activity' => 'Recreation'),
        array('title' => 'Return to Tokyo', 'date' => '2025-06-18', 'location' => 'Tokyo', 'activity' => 'Travel')
    );
    
    foreach ($travel_days as $day) {
        $post_id = wp_insert_post(array(
            'post_title' => $day['title'],
            'post_type' => 'travel_day',
            'post_status' => 'publish',
            'post_content' => 'Add your travel blog content here...'
        ));
        
        if ($post_id && !is_wp_error($post_id)) {
            update_post_meta($post_id, '_travel_date', $day['date']);
            wp_set_post_terms($post_id, $day['location'], 'travel_location');
            wp_set_post_terms($post_id, $day['activity'], 'activity_type');
        }
    }
    
    update_option('alaska_travel_days_created', 'yes');
}

// Trigger creation on theme activation and admin visits
add_action('after_switch_theme', 'populate_initial_travel_days');
add_action('admin_init', 'populate_initial_travel_days');

// Fallback menu function
function alaska_travel_fallback_menu() {
    echo '<ul id="primary-menu">';
    echo '<li><a href="' . home_url('/') . '">Home</a></li>';
    echo '<li><a href="' . home_url('/travel-days/') . '">Travel Days</a></li>';
    echo '<li><a href="' . home_url('/photos/') . '">Photos</a></li>';
    echo '</ul>';
}


// Add setup admin page
function alaska_manual_setup_menu() {
    add_management_page(
        'Alaska Setup',
        'Alaska Setup',
        'manage_options',
        'alaska-setup',
        'alaska_manual_setup_page'
    );
}
add_action('admin_menu', 'alaska_manual_setup_menu');

function alaska_manual_setup_page() {
    ?>
    <div class="wrap">
        <h1>🏔️ Alaska Adventure Blog Setup</h1>
        
        <?php if (isset($_POST['setup_travel_days'])) : ?>
            <?php echo alaska_create_travel_days(); ?>
        <?php endif; ?>
        
        <?php if (isset($_POST['setup_pages'])) : ?>
            <?php echo alaska_create_pages(); ?>
        <?php endif; ?>
        
        <?php if (isset($_POST['setup_all'])) : ?>
            <?php 
            echo alaska_create_travel_days();
            echo alaska_create_pages();
            ?>
        <?php endif; ?>
        
        <div class="setup-section">
            <h2>Current Status</h2>
            <ul>
                <li><strong>Travel Days Created:</strong> <?php echo get_option('alaska_travel_days_created', 'No'); ?></li>
                <li><strong>Pages Created:</strong> <?php echo get_option('alaska_pages_created', 'No'); ?></li>
                <li><strong>Travel Days Count:</strong> <?php echo wp_count_posts('travel_day')->publish; ?></li>
                <li><strong>Post Type Registered:</strong> <?php echo post_type_exists('travel_day') ? 'Yes' : 'No'; ?></li>
            </ul>
        </div>
        
        <div class="setup-actions">
            <h2>Setup Actions</h2>
            <form method="post" style="margin-bottom: 1rem;">
                <input type="submit" name="setup_travel_days" value="Create Travel Days" class="button button-primary">
                <p class="description">Creates all 21 travel days for your Alaska adventure</p>
            </form>
            
            <form method="post" style="margin-bottom: 1rem;">
                <input type="submit" name="setup_pages" value="Create Pages" class="button button-primary">
                <p class="description">Creates Photos page and Travel Days page</p>
            </form>
            
            <form method="post" style="margin-bottom: 1rem;">
                <input type="submit" name="setup_all" value="Setup Everything" class="button button-primary button-hero">
                <p class="description">Creates everything - travel days and pages</p>
            </form>
        </div>
        
        <div class="manual-instructions">
            <h2>Manual Instructions</h2>
            <ol>
                <li><strong>Click "Setup Everything"</strong> to create all content</li>
                <li><strong>Go to Pages</strong> and verify Photos and Travel Days pages exist</li>
                <li><strong>Go to Travel Days</strong> in admin menu to see your 21 travel days</li>
                <li><strong>Set permalinks:</strong> Settings → Permalinks → Save Changes</li>
                <li><strong>Create navigation menu:</strong> Appearance → Menus</li>
            </ol>
        </div>
    </div>
    
    <style>
    .setup-section, .setup-actions, .manual-instructions {
        background: white;
        padding: 1.5rem;
        margin: 1rem 0;
        border-radius: 8px;
        border-left: 4px solid #3498db;
    }
    .button-hero {
        font-size: 1.2rem !important;
        padding: 1rem 2rem !important;
    }
    </style>
    <?php
}

function alaska_create_travel_days() {
    $output = '<div class="notice notice-info"><h3>Creating Travel Days...</h3>';
    
    // First, make sure post type exists
    if (!post_type_exists('travel_day')) {
        $output .= '<p>❌ Travel Day post type not found. Check functions.php.</p></div>';
        return $output;
    }
    
    $travel_days = array(
        array('title' => '🏔️ Matanuska Glacier Tour', 'date' => '2025-05-29', 'location' => 'Anchorage', 'activity' => 'Glacier Trekking', 'content' => 'Journey two hours north of Anchorage to explore the incredible Matanuska Glacier. This 27-mile frozen river of ancient ice offers moderate glacier hiking with expert guides.'),
        array('title' => '🚂 Denali Star Train & Glacier Landing', 'date' => '2025-05-30', 'location' => 'Talkeetna', 'activity' => 'Train & Flight', 'content' => 'Board the Alaska Railroad\'s GoldStar service to Talkeetna, then take a spectacular bush plane flight for glacier landing on Denali.'),
        array('title' => '🐻 Turnagain Arm & Wildlife Center', 'date' => '2025-05-31', 'location' => 'Anchorage', 'activity' => 'Wildlife Tour', 'content' => 'Explore Turnagain Arm National Scenic Byway and visit Alaska Wildlife Conservation Center to see moose, bears, and other Alaskan wildlife.'),
        array('title' => '🛳️ Embark Holland America Noordam', 'date' => '2025-06-01', 'location' => 'Whittier', 'activity' => 'Cruise Embarkation', 'content' => 'Board the Holland America Noordam for our 7-Day Glacier Discovery Southbound cruise. Stateroom 5068, Category VA.'),
        array('title' => '🧊 Hubbard Glacier Cruising', 'date' => '2025-06-02', 'location' => 'Hubbard Glacier', 'activity' => 'Glacier Viewing', 'content' => 'Full day cruising spectacular Hubbard Glacier, North America\'s largest tidewater glacier. Watch for calving ice and wildlife.'),
        array('title' => '🏞️ Glacier Bay National Park', 'date' => '2025-06-03', 'location' => 'Glacier Bay', 'activity' => 'National Park Cruising', 'content' => 'UNESCO World Heritage Site with pristine wilderness, tidewater glaciers, and abundant wildlife. Park rangers board to provide narration.'),
        array('title' => '⛏️ Skagway - Historic Gold Rush Town', 'date' => '2025-06-04', 'location' => 'Skagway', 'activity' => 'Shore Excursion', 'content' => 'Explore this authentic Gold Rush boomtown with its wooden boardwalks and false-front buildings.'),
        array('title' => '🏛️ Juneau - Alaska\'s Capital', 'date' => '2025-06-05', 'location' => 'Juneau', 'activity' => 'Shore Excursion', 'content' => 'Visit Alaska\'s capital city, accessible only by air or sea. Optional excursions to Mendenhall Glacier available.'),
        array('title' => '🐟 Ketchikan - Salmon Capital', 'date' => '2025-06-06', 'location' => 'Ketchikan', 'activity' => 'Shore Excursion', 'content' => 'Explore the "Salmon Capital of the World" with its Creek Street historic district built on stilts over water.'),
        array('title' => '🌊 Inside Passage Scenic Cruising', 'date' => '2025-06-07', 'location' => 'Inside Passage', 'activity' => 'Scenic Cruising', 'content' => 'Full day of scenic cruising through the famous Inside Passage, a protected waterway with pristine wilderness.'),
        array('title' => '🎿 Whistler Mountain & Gondola', 'date' => '2025-06-08', 'location' => 'Vancouver', 'activity' => 'Sightseeing', 'content' => 'Day trip to Whistler, site of 2010 Winter Olympics. Scenic gondola rides and alpine village exploration.'),
        array('title' => '🌺 Victoria & Butchart Gardens', 'date' => '2025-06-09', 'location' => 'Vancouver', 'activity' => 'Sightseeing', 'content' => 'Ferry to Victoria, BC\'s capital. Visit the world-famous Butchart Gardens with over 55 acres of stunning floral displays.'),
        array('title' => '🌲 Stanley Park Vancouver', 'date' => '2025-06-10', 'location' => 'Vancouver', 'activity' => 'Sightseeing', 'content' => 'Explore Stanley Park, one of North America\'s largest urban parks. Seawall cycling and spectacular city views.'),
        array('title' => '✈️ Travel to Minneapolis', 'date' => '2025-06-11', 'location' => 'Minneapolis', 'activity' => 'Travel', 'content' => 'Travel day from Vancouver to Minneapolis, Minnesota to begin our American road trip adventure.'),
        array('title' => '🗻 Corn Palace & Mt Rushmore', 'date' => '2025-06-12', 'location' => 'South Dakota', 'activity' => 'Sightseeing', 'content' => 'Visit the famous Corn Palace in Mitchell, SD, then continue to Mount Rushmore National Memorial.'),
        array('title' => '🤠 Sturgis & Devil\'s Tower', 'date' => '2025-06-13', 'location' => 'South Dakota', 'activity' => 'Sightseeing', 'content' => 'Explore historic Sturgis, Belle Fourche, and the magnificent Devil\'s Tower National Monument.'),
        array('title' => '🌋 Yellowstone National Park', 'date' => '2025-06-14', 'location' => 'Wyoming', 'activity' => 'Nature', 'content' => 'Full day exploring America\'s first national park. Old Faithful, Grand Prismatic Spring, and wildlife viewing.'),
        array('title' => '🦬 Theodore Roosevelt National Park', 'date' => '2025-06-15', 'location' => 'Montana', 'activity' => 'Nature', 'content' => 'Drive from Billings to Minneapolis, visiting Theodore Roosevelt National Park. Badlands scenery and prairie wildlife.'),
        array('title' => '🦀 Crab Dinner Minneapolis', 'date' => '2025-06-16', 'location' => 'Minneapolis', 'activity' => 'Dining', 'content' => 'Special crab dinner in Minneapolis, celebrating our incredible journey through Alaska and the American West.'),
        array('title' => '🛍️ Mall of America & Recreation', 'date' => '2025-06-17', 'location' => 'Minneapolis', 'activity' => 'Recreation', 'content' => 'Visit the famous Mall of America, enjoy archery activities, and recreational fishing.'),
        array('title' => '🛫 Return to Tokyo', 'date' => '2025-06-18', 'location' => 'Tokyo', 'activity' => 'Travel', 'content' => 'Departure from Minneapolis back to Tokyo, concluding our epic 21-day Alaska adventure.')
    );
    
    $created_count = 0;
    foreach ($travel_days as $day) {
        $existing = get_posts(array(
            'post_type' => 'travel_day',
            'title' => $day['title'],
            'post_status' => 'any',
            'numberposts' => 1
        ));
        
        if (empty($existing)) {
            $post_id = wp_insert_post(array(
                'post_title' => $day['title'],
                'post_type' => 'travel_day',
                'post_status' => 'publish',
                'post_content' => $day['content']
            ));
            
            if ($post_id && !is_wp_error($post_id)) {
                update_post_meta($post_id, '_travel_date', $day['date']);
                wp_set_post_terms($post_id, array($day['location']), 'travel_location');
                wp_set_post_terms($post_id, array($day['activity']), 'activity_type');
                $created_count++;
                $output .= '<p>✅ Created: ' . $day['title'] . '</p>';
            } else {
                $output .= '<p>❌ Failed to create: ' . $day['title'] . '</p>';
            }
        } else {
            $output .= '<p>⚠️ Already exists: ' . $day['title'] . '</p>';
        }
    }
    
    update_option('alaska_travel_days_created', 'yes');
    $output .= '<p><strong>✅ Created ' . $created_count . ' travel days!</strong></p></div>';
    
    return $output;
}

function alaska_create_pages() {
    $output = '<div class="notice notice-info"><h3>Creating Pages...</h3>';
    
    // Create Photos page
    $photos_page = get_page_by_title('Photos');
    if (!$photos_page) {
        $photos_page_id = wp_insert_post(array(
            'post_title' => 'Photos',
            'post_content' => 'Browse our Alaska adventure photos by location, activity, or date.',
            'post_status' => 'publish',
            'post_type' => 'page',
            'page_template' => 'page-photos.php'
        ));
        
        if ($photos_page_id) {
            update_post_meta($photos_page_id, '_wp_page_template', 'page-photos.php');
            $output .= '<p>✅ Created Photos page</p>';
        } else {
            $output .= '<p>❌ Failed to create Photos page</p>';
        }
    } else {
        $output .= '<p>⚠️ Photos page already exists</p>';
    }
    
    // Create Travel Days page
    $timeline_page = get_page_by_title('Travel Days');
    if (!$timeline_page) {
        $timeline_page_id = wp_insert_post(array(
            'post_title' => 'Travel Days',
            'post_content' => 'Our complete 21-day Alaska adventure timeline.',
            'post_status' => 'publish',
            'post_type' => 'page',
            'page_template' => 'page-travel-days.php'
        ));
        
        if ($timeline_page_id) {
            update_post_meta($timeline_page_id, '_wp_page_template', 'page-travel-days.php');
            $output .= '<p>✅ Created Travel Days page</p>';
        } else {
            $output .= '<p>❌ Failed to create Travel Days page</p>';
        }
    } else {
        $output .= '<p>⚠️ Travel Days page already exists</p>';
    }
    
    update_option('alaska_pages_created', 'yes');
    $output .= '<p><strong>✅ Pages setup complete!</strong></p></div>';
    
    return $output;
}

// Force flush rewrite rules
function alaska_flush_rules() {
    if (get_option('alaska_rules_flushed') != 'yes') {
        flush_rewrite_rules();
        update_option('alaska_rules_flushed', 'yes');
    }
}
add_action('init', 'alaska_flush_rules');

/**
 * Simple Photo Upload System for Alaska Adventure Blog
 */

// Add photo upload meta box to travel days
function alaska_add_photo_upload_meta_box() {
    add_meta_box(
        'alaska_photos',
        '📸 Alaska Adventure Photos',
        'alaska_photo_upload_callback',
        'travel_day',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'alaska_add_photo_upload_meta_box');

function alaska_photo_upload_callback($post) {
    wp_nonce_field('alaska_photo_upload_nonce', 'alaska_photo_upload_nonce');
    
    $google_album_url = get_post_meta($post->ID, '_google_album_url', true);
    $uploaded_photos = get_post_meta($post->ID, '_uploaded_photos', true);
    
    ?>
    <div class="alaska-photo-upload">
        <h3>🌐 Google Photos Album</h3>
        <table class="form-table">
            <tr>
                <th><label for="google_album_url">Google Photos Album URL:</label></th>
                <td>
                    <input type="url" id="google_album_url" name="google_album_url" 
                           value="<?php echo esc_attr($google_album_url); ?>" 
                           style="width: 100%;" 
                           placeholder="https://photos.google.com/share/...">
                    <p class="description">
                        📱 <strong>How to get Google Photos album URL:</strong><br>
                        1. Open Google Photos app/website<br>
                        2. Create an album with your travel day photos<br>
                        3. Click "Share" → "Create link"<br>
                        4. Copy the link and paste it here
                    </p>
                </td>
            </tr>
        </table>
        
        <h3>📱 Upload Photos Directly</h3>
        <div class="photo-upload-area">
            <button type="button" class="button button-primary" id="upload-photos-btn">
                📸 Upload Photos
            </button>
            <p class="description">Click to select and upload photos from your device</p>
            
            <div id="photo-gallery-preview">
                <?php if ($uploaded_photos) : ?>
                    <h4>Current Photos:</h4>
                    <div class="photo-grid">
                        <?php foreach ($uploaded_photos as $photo_id) : ?>
                            <?php $image = wp_get_attachment_image_src($photo_id, 'thumbnail'); ?>
                            <?php if ($image) : ?>
                                <div class="photo-item" data-photo-id="<?php echo $photo_id; ?>">
                                    <img src="<?php echo $image[0]; ?>" alt="Travel photo">
                                    <button type="button" class="remove-photo" data-photo-id="<?php echo $photo_id; ?>">×</button>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <input type="hidden" id="uploaded_photos" name="uploaded_photos" 
                   value="<?php echo esc_attr(implode(',', (array)$uploaded_photos)); ?>">
        </div>
    </div>
    
    <style>
    .alaska-photo-upload {
        background: #f9f9f9;
        padding: 20px;
        border-radius: 8px;
        margin: 10px 0;
    }
    
    .photo-upload-area {
        border: 2px dashed #ccc;
        padding: 20px;
        text-align: center;
        border-radius: 8px;
        background: white;
    }
    
    .photo-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
        gap: 10px;
        margin-top: 15px;
    }
    
    .photo-item {
        position: relative;
        border-radius: 8px;
        overflow: hidden;
    }
    
    .photo-item img {
        width: 100%;
        height: 100px;
        object-fit: cover;
    }
    
    .remove-photo {
        position: absolute;
        top: 5px;
        right: 5px;
        background: red;
        color: white;
        border: none;
        border-radius: 50%;
        width: 20px;
        height: 20px;
        cursor: pointer;
        font-size: 12px;
    }
    </style>
    
    <script>
    jQuery(document).ready(function($) {
        var mediaUploader;
        
        $('#upload-photos-btn').on('click', function(e) {
            e.preventDefault();
            
            if (mediaUploader) {
                mediaUploader.open();
                return;
            }
            
            mediaUploader = wp.media({
                title: 'Choose Photos for Alaska Adventure',
                button: {
                    text: 'Add to Travel Day'
                },
                multiple: true
            });
            
            mediaUploader.on('select', function() {
                var attachments = mediaUploader.state().get('selection').toJSON();
                var existingPhotos = $('#uploaded_photos').val().split(',').filter(Boolean);
                
                attachments.forEach(function(attachment) {
                    if (existingPhotos.indexOf(attachment.id.toString()) === -1) {
                        existingPhotos.push(attachment.id);
                        
                        var photoHtml = '<div class="photo-item" data-photo-id="' + attachment.id + '">' +
                                       '<img src="' + attachment.sizes.thumbnail.url + '" alt="Travel photo">' +
                                       '<button type="button" class="remove-photo" data-photo-id="' + attachment.id + '">×</button>' +
                                       '</div>';
                        
                        $('#photo-gallery-preview .photo-grid').append(photoHtml);
                    }
                });
                
                if ($('#photo-gallery-preview .photo-grid').length === 0) {
                    $('#photo-gallery-preview').append('<h4>Current Photos:</h4><div class="photo-grid"></div>');
                }
                
                $('#uploaded_photos').val(existingPhotos.join(','));
            });
            
            mediaUploader.open();
        });
        
        $(document).on('click', '.remove-photo', function() {
            var photoId = $(this).data('photo-id');
            var existingPhotos = $('#uploaded_photos').val().split(',').filter(Boolean);
            var index = existingPhotos.indexOf(photoId.toString());
            
            if (index > -1) {
                existingPhotos.splice(index, 1);
                $('#uploaded_photos').val(existingPhotos.join(','));
                $(this).closest('.photo-item').remove();
            }
        });
    });
    </script>
    <?php
}

// Save photo upload data
function alaska_save_photo_upload($post_id) {
    if (!isset($_POST['alaska_photo_upload_nonce']) || 
        !wp_verify_nonce($_POST['alaska_photo_upload_nonce'], 'alaska_photo_upload_nonce')) {
        return;
    }
    
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    
    // Save Google Photos album URL
    if (isset($_POST['google_album_url'])) {
        update_post_meta($post_id, '_google_album_url', sanitize_url($_POST['google_album_url']));
    }
    
    // Save uploaded photos
    if (isset($_POST['uploaded_photos'])) {
        $photo_ids = array_filter(array_map('intval', explode(',', $_POST['uploaded_photos'])));
        update_post_meta($post_id, '_uploaded_photos', $photo_ids);
    }
}
add_action('save_post', 'alaska_save_photo_upload');

// Display photos in the frontend
function alaska_display_travel_photos($post_id = null) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }
    
    $google_album_url = get_post_meta($post_id, '_google_album_url', true);
    $uploaded_photos = get_post_meta($post_id, '_uploaded_photos', true);
    
    ob_start();
    ?>
    
    <div class="alaska-travel-photos">
        <?php if ($google_album_url) : ?>
            <div class="google-album-section">
                <h3>📸 Google Photos Album</h3>
                <a href="<?php echo esc_url($google_album_url); ?>" target="_blank" class="google-album-btn">
                    🌐 View Full Album on Google Photos
                </a>
            </div>
        <?php endif; ?>
        
        <?php if ($uploaded_photos && is_array($uploaded_photos)) : ?>
            <div class="uploaded-photos-section">
                <h3>📷 Travel Day Photos</h3>
                <div class="travel-photo-gallery">
                    <?php foreach ($uploaded_photos as $photo_id) : ?>
                        <?php 
                        $image_full = wp_get_attachment_image_src($photo_id, 'large');
                        $image_thumb = wp_get_attachment_image_src($photo_id, 'medium');
                        $caption = wp_get_attachment_caption($photo_id);
                        ?>
                        <?php if ($image_thumb) : ?>
                            <div class="travel-photo-item">
                                <a href="<?php echo $image_full[0]; ?>" class="photo-lightbox">
                                    <img src="<?php echo $image_thumb[0]; ?>" alt="<?php echo esc_attr($caption); ?>">
                                </a>
                                <?php if ($caption) : ?>
                                    <p class="photo-caption"><?php echo esc_html($caption); ?></p>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if (!$google_album_url && (!$uploaded_photos || empty($uploaded_photos))) : ?>
            <div class="no-photos">
                <p>📸 Photos will appear here once uploaded!</p>
                <?php if (current_user_can('edit_posts')) : ?>
                    <p><a href="<?php echo get_edit_post_link($post_id); ?>">Add photos to this travel day</a></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <style>
    .alaska-travel-photos {
        margin: 2rem 0;
        padding: 1.5rem;
        background: #f8f9fa;
        border-radius: 8px;
    }
    
    .google-album-btn {
        display: inline-block;
        background: #4285f4;
        color: white;
        padding: 1rem 2rem;
        text-decoration: none;
        border-radius: 8px;
        font-weight: bold;
        margin: 1rem 0;
        transition: background-color 0.3s;
    }
    
    .google-album-btn:hover {
        background: #3367d6;
    }
    
    .travel-photo-gallery {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1rem;
        margin-top: 1rem;
    }
    
    .travel-photo-item {
        background: white;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        transition: transform 0.3s;
    }
    
    .travel-photo-item:hover {
        transform: translateY(-3px);
    }
    
    .travel-photo-item img {
        width: 100%;
        height: 200px;
        object-fit: cover;
        cursor: pointer;
    }
    
    .photo-caption {
        padding: 1rem;
        margin: 0;
        font-size: 0.9rem;
        color: #666;
    }
    
    .no-photos {
        text-align: center;
        padding: 2rem;
        border: 2px dashed #ccc;
        border-radius: 8px;
        background: white;
    }
    
    @media (max-width: 768px) {
        .travel-photo-gallery {
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        }
    }
    </style>
    
    <?php
    return ob_get_clean();
}

// Add photos to single travel day template
function alaska_add_photos_to_content($content) {
    if (is_singular('travel_day')) {
        $content .= alaska_display_travel_photos();
    }
    return $content;
}
add_filter('the_content', 'alaska_add_photos_to_content');

// Enqueue media uploader scripts
function alaska_enqueue_media_uploader() {
    global $post_type;
    if ($post_type == 'travel_day') {
        wp_enqueue_media();
        wp_enqueue_script('jquery');
    }
}
add_action('admin_enqueue_scripts', 'alaska_enqueue_media_uploader');

/**
 * Debug Google Photos URLs - Add this to functions.php temporarily
 */

// Add debug info to travel day edit screen
function alaska_debug_google_photos($post) {
    if ($post->post_type !== 'travel_day') return;
    
    $google_album_url = get_post_meta($post->ID, '_google_album_url', true);
    $all_meta = get_post_meta($post->ID);
    
    ?>
    <div class="debug-info" style="background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin: 10px 0;">
        <h4>🔍 Debug Information</h4>
        <p><strong>Post ID:</strong> <?php echo $post->ID; ?></p>
        <p><strong>Google Album URL:</strong> 
            <?php if ($google_album_url) : ?>
                <code style="background: #e1f5fe; padding: 3px 6px; border-radius: 3px;"><?php echo esc_html($google_album_url); ?></code>
                <br><a href="<?php echo esc_url($google_album_url); ?>" target="_blank" style="color: #1976d2;">🔗 Test Link</a>
            <?php else : ?>
                <span style="color: #d32f2f;">❌ No URL saved</span>
            <?php endif; ?>
        </p>
        
        <details>
            <summary>All Meta Data for This Post</summary>
            <pre style="background: #f5f5f5; padding: 10px; border-radius: 3px; font-size: 12px; overflow-x: auto;">
<?php print_r($all_meta); ?>
            </pre>
        </details>
        
        <p><strong>Quick Test:</strong></p>
        <div style="background: #f8f9fa; padding: 10px; border-radius: 5px;">
            <input type="text" placeholder="Test Google Photos URL here..." style="width: 100%; margin-bottom: 10px;">
            <button type="button" onclick="testGooglePhotosUrl(this)" class="button">Test URL Format</button>
        </div>
    </div>
    
    <script>
    function testGooglePhotosUrl(button) {
        const input = button.previousElementSibling;
        const url = input.value;
        
        if (!url) {
            alert('Please enter a URL to test');
            return;
        }
        
        if (url.includes('photos.google.com') || url.includes('photos.app.goo.gl')) {
            alert('✅ Valid Google Photos URL format!\n\nURL: ' + url);
            // Try to open it
            window.open(url, '_blank');
        } else {
            alert('❌ This doesn\'t look like a Google Photos URL.\n\nGoogle Photos URLs should contain:\n- photos.google.com\n- photos.app.goo.gl\n\nYour URL: ' + url);
        }
    }
    </script>
    <?php
}
add_action('edit_form_after_title', 'alaska_debug_google_photos');

// Add admin notice to check Google Photos URLs
function alaska_check_google_photos_admin_notice() {
    $screen = get_current_screen();
    if ($screen->id !== 'travel_day') return;
    
    // Count travel days with Google Photos URLs
    $travel_days_with_photos = get_posts(array(
        'post_type' => 'travel_day',
        'meta_query' => array(
            array(
                'key' => '_google_album_url',
                'value' => '',
                'compare' => '!='
            )
        ),
        'posts_per_page' => -1
    ));
    
    $total_travel_days = wp_count_posts('travel_day')->publish;
    
    ?>
    <div class="notice notice-info">
        <h3>📸 Google Photos Status</h3>
        <p>
            <strong><?php echo count($travel_days_with_photos); ?></strong> out of 
            <strong><?php echo $total_travel_days; ?></strong> travel days have Google Photos albums.
        </p>
        
        <?php if (count($travel_days_with_photos) === 0) : ?>
            <div style="background: #fff3cd; padding: 10px; border-radius: 5px; margin-top: 10px;">
                <p><strong>🔧 How to add Google Photos:</strong></p>
                <ol>
                    <li>Create an album in Google Photos</li>
                    <li>Click "Share" → "Create link"</li>
                    <li>Copy the link (should start with https://photos.google.com/ or https://photos.app.goo.gl/)</li>
                    <li>Paste in the "Google Photos Album URL" field below</li>
                    <li>Save the post</li>
                </ol>
            </div>
        <?php endif; ?>
        
        <?php if (count($travel_days_with_photos) > 0) : ?>
            <p><strong>Travel days with photos:</strong></p>
            <ul>
            <?php foreach ($travel_days_with_photos as $post) : ?>
                <li>
                    <a href="<?php echo get_edit_post_link($post->ID); ?>"><?php echo $post->post_title; ?></a>
                    - <a href="<?php echo get_permalink($post->ID); ?>" target="_blank">View on site</a>
                </li>
            <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
    <?php
}
add_action('admin_notices', 'alaska_check_google_photos_admin_notice');

// Force save Google Photos URL with better handling
function alaska_force_save_google_photos($post_id) {
    // Skip if this is an autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    
    // Skip if user can't edit this post
    if (!current_user_can('edit_post', $post_id)) return;
    
    // Only for travel_day post type
    if (get_post_type($post_id) !== 'travel_day') return;
    
    // Check if Google Photos URL was submitted
    if (isset($_POST['google_album_url'])) {
        $url = sanitize_url($_POST['google_album_url']);
        
        // Log the save attempt
        error_log("Alaska Debug: Saving Google Photos URL for post $post_id: $url");
        
        if (!empty($url)) {
            $result = update_post_meta($post_id, '_google_album_url', $url);
            error_log("Alaska Debug: Update result: " . ($result ? 'SUCCESS' : 'FAILED'));
        } else {
            delete_post_meta($post_id, '_google_album_url');
            error_log("Alaska Debug: Deleted empty Google Photos URL for post $post_id");
        }
    }
}
add_action('save_post', 'alaska_force_save_google_photos', 20);

// Add simple shortcode to display Google Photos
function alaska_google_photos_shortcode($atts) {
    $atts = shortcode_atts(array(
        'post_id' => get_the_ID()
    ), $atts);
    
    $google_album_url = get_post_meta($atts['post_id'], '_google_album_url', true);
    
    if (!$google_album_url) {
        return '<p style="background: #fff3cd; padding: 10px; border-radius: 5px;">📸 No Google Photos album added yet for this travel day.</p>';
    }
    
    return '<div style="text-align: center; background: #e3f2fd; padding: 20px; border-radius: 10px; margin: 20px 0;">
                <h3>📸 View Our Photos</h3>
                <a href="' . esc_url($google_album_url) . '" target="_blank" 
                   style="background: #1976d2; color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; font-weight: bold; display: inline-block;">
                    🌐 Open Google Photos Album
                </a>
                <p style="margin-top: 10px; color: #666;">Click to view all photos from this travel day!</p>
            </div>';
}
add_shortcode('google_photos', 'alaska_google_photos_shortcode');

// AJAX handler for photo filtering
function alaska_travel_filter_photos() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'alaska_travel_nonce')) {
        wp_die('Security check failed');
    }
    
    $location = sanitize_text_field($_POST['location']);
    $activity = sanitize_text_field($_POST['activity']);
    $date = sanitize_text_field($_POST['date']);
    $phase = sanitize_text_field($_POST['phase']);
    
    $args = array(
        'post_type' => 'travel_day',
        'posts_per_page' => -1,
        'meta_key' => '_travel_date',
        'orderby' => 'meta_value',
        'order' => 'ASC'
    );
    
    // Add filters
    if (!empty($location) || !empty($activity) || !empty($date) || !empty($phase)) {
        $args['tax_query'] = array('relation' => 'AND');
        
        if (!empty($location)) {
            $args['tax_query'][] = array(
                'taxonomy' => 'travel_location',
                'field' => 'slug',
                'terms' => $location
            );
        }
        
        if (!empty($activity)) {
            $args['tax_query'][] = array(
                'taxonomy' => 'activity_type',
                'field' => 'slug',
                'terms' => $activity
            );
        }
        
        if (!empty($date)) {
            $args['meta_query'] = array(
                array(
                    'key' => '_travel_date',
                    'value' => $date,
                    'compare' => '='
                )
            );
        }
    }
    
    $travel_days_query = new WP_Query($args);
    
    ob_start();
    
    if ($travel_days_query->have_posts()) {
        echo '<div class="photo-albums-grid">';
        
        while ($travel_days_query->have_posts()) {
            $travel_days_query->the_post();
            $google_album_url = get_post_meta(get_the_ID(), '_google_album_url', true);
            $travel_date = get_post_meta(get_the_ID(), '_travel_date', true);
            $weather = get_post_meta(get_the_ID(), '_weather', true);
            $locations = get_the_terms(get_the_ID(), 'travel_location');
            $activities = get_the_terms(get_the_ID(), 'activity_type');
            
            // Determine phase for styling
            $phase_class = 'general';
            $phase_name = 'Adventure';
            if ($locations) {
                $location_names = wp_list_pluck($locations, 'name');
                if (in_array('Anchorage', $location_names) || in_array('Talkeetna', $location_names)) {
                    $phase_class = 'alaska';
                    $phase_name = '🏔️ Alaska Tours';
                } elseif (in_array('Whittier', $location_names) || in_array('Hubbard Glacier', $location_names) || 
                         in_array('Glacier Bay', $location_names) || in_array('Skagway', $location_names) ||
                         in_array('Juneau', $location_names) || in_array('Ketchikan', $location_names) ||
                         in_array('Inside Passage', $location_names)) {
                    $phase_class = 'cruise';
                    $phase_name = '🛳️ Alaska Cruise';
                } elseif (in_array('Vancouver', $location_names)) {
                    $phase_class = 'vancouver';
                    $phase_name = '🍁 Vancouver';
                } elseif (in_array('South Dakota', $location_names) || in_array('Wyoming', $location_names) || 
                         in_array('Montana', $location_names) || in_array('Minneapolis', $location_names)) {
                    $phase_class = 'roadtrip';
                    $phase_name = '🗻 US Road Trip';
                }
            }
            
            // Apply phase filter if specified
            if (!empty($phase) && $phase !== $phase_class && $phase !== 'all') {
                continue;
            }
            
            echo '<div class="photo-album-card ' . $phase_class . '">';
            echo '<div class="album-header">';
            echo '<div class="album-date">' . ($travel_date ? date('M j, Y', strtotime($travel_date)) : '') . '</div>';
            echo '<div class="album-phase">' . $phase_name . '</div>';
            echo '</div>';
            
            echo '<div class="album-content">';
            echo '<h4>' . get_the_title() . '</h4>';
            
            if ($locations || $activities) {
                echo '<div class="album-meta">';
                if ($locations) {
                    echo '<span class="album-location">📍 ' . implode(', ', wp_list_pluck($locations, 'name')) . '</span>';
                }
                if ($activities) {
                    echo '<span class="album-activity">🎯 ' . implode(', ', wp_list_pluck($activities, 'name')) . '</span>';
                }
                if ($weather) {
                    echo '<span class="album-weather">🌤️ ' . esc_html($weather) . '</span>';
                }
                echo '</div>';
            }
            
            echo '<div class="album-excerpt">' . get_the_excerpt() . '</div>';
            
            echo '<div class="album-actions">';
            if ($google_album_url) {
                echo '<a href="' . esc_url($google_album_url) . '" target="_blank" class="view-album-btn">📸 View Photos</a>';
            } else {
                echo '<span class="no-photos">📷 Photos coming soon</span>';
            }
            echo '<a href="' . get_permalink() . '" class="read-day-btn">📖 Read Day</a>';
            echo '</div>';
            
            echo '</div>';
            echo '</div>';
        }
        
        echo '</div>';
        wp_reset_postdata();
    } else {
        echo '<p>No travel days found matching your filters. Try adjusting your search criteria.</p>';
    }
    
    $output = ob_get_clean();
    wp_send_json_success($output);
}
add_action('wp_ajax_alaska_travel_filter_photos', 'alaska_travel_filter_photos');
add_action('wp_ajax_nopriv_alaska_travel_filter_photos', 'alaska_travel_filter_photos');

?>