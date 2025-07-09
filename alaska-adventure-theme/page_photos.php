<?php
/*
Template Name: Photos Page
*/

get_header(); ?>

<main class="site-main container">
    <div class="content-area">
        <header class="page-header">
            <h1 class="page-title">📸 Alaska Adventure Photo Gallery</h1>
            <p class="page-description">Browse our photos by location, activity, or date from our epic 21-day journey</p>
        </header>
        
        <div class="photo-gallery-intro">
            <div class="gallery-stats">
                <?php
                $travel_days = get_posts(array(
                    'post_type' => 'travel_day',
                    'posts_per_page' => -1,
                    'meta_query' => array(
                        array(
                            'key' => '_google_album_url',
                            'value' => '',
                            'compare' => '!='
                        )
                    )
                ));
                ?>
                <div class="stat-box">
                    <span class="stat-number"><?php echo count($travel_days); ?></span>
                    <span class="stat-label">Photo Albums</span>
                </div>
                <div class="stat-box">
                    <span class="stat-number">21</span>
                    <span class="stat-label">Adventure Days</span>
                </div>
                <div class="stat-box">
                    <span class="stat-number">4</span>
                    <span class="stat-label">Adventure Phases</span>
                </div>
            </div>
        </div>
        
        <div class="photo-filters">
            <h3>🔍 Filter Photo Albums</h3>
            <form id="photo-filter-form">
                <div class="filter-row" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                    <div class="filter-group">
                        <label for="filter-location">📍 Filter by Location:</label>
                        <select id="filter-location" class="photo-filter-select">
                            <option value="">All Locations</option>
                            <?php
                            $locations = get_terms(array(
                                'taxonomy' => 'travel_location',
                                'hide_empty' => false
                            ));
                            foreach ($locations as $location) {
                                echo '<option value="' . $location->slug . '">' . $location->name . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="filter-activity">🎯 Filter by Activity:</label>
                        <select id="filter-activity" class="photo-filter-select">
                            <option value="">All Activities</option>
                            <?php
                            $activities = get_terms(array(
                                'taxonomy' => 'activity_type',
                                'hide_empty' => false
                            ));
                            foreach ($activities as $activity) {
                                echo '<option value="' . $activity->slug . '">' . $activity->name . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="filter-date">📅 Filter by Date:</label>
                        <input type="date" id="filter-date" class="photo-filter-input" min="2025-05-29" max="2025-06-18">
                    </div>
                    
                    <div class="filter-group">
                        <label for="filter-phase">🌟 Adventure Phase:</label>
                        <select id="filter-phase" class="photo-filter-select">
                            <option value="">All Phases</option>
                            <option value="alaska">🏔️ Alaska Tours (May 29-31)</option>
                            <option value="cruise">🛳️ Alaska Cruise (June 1-8)</option>
                            <option value="vancouver">🍁 Vancouver (June 8-10)</option>
                            <option value="roadtrip">🗻 US Road Trip (June 11-17)</option>
                        </select>
                    </div>
                </div>
                
                <div class="filter-actions">
                    <button type="submit" class="filter-button">🔍 Filter Photos</button>
                    <button type="button" id="clear-filters" class="clear-button">🔄 Clear All</button>
                </div>
            </form>
        </div>
        
        <div id="photo-results">
            <!-- Initial load of all photos -->
            <?php
            $travel_days_query = new WP_Query(array(
                'post_type' => 'travel_day',
                'posts_per_page' => -1,
                'meta_key' => '_travel_date',
                'orderby' => 'meta_value',
                'order' => 'ASC'
            ));
            
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
                echo '<p>🎬 No travel days found yet. Your photo gallery is being prepared!</p>';
            }
            ?>
        </div>
        
        <div class="upload-section">
            <h2>📤 Upload Your Photos</h2>
            <p>Traveling with us? Upload your photos to any day of our adventure!</p>
            <?php if (is_user_logged_in()) : ?>
                <?php echo do_shortcode('[travel_photo_uploader]'); ?>
            <?php else : ?>
                <p><a href="<?php echo wp_login_url(get_permalink()); ?>">Login</a> to upload photos to our adventure!</p>
            <?php endif; ?>
        </div>
    </div>
    
    <aside class="sidebar">
        <?php get_sidebar(); ?>
    </aside>
</main>

<style>
.photo-gallery-intro {
    background: linear-gradient(135deg, #667db6 0%, #0082c8 100%);
    color: white;
    padding: 2rem;
    border-radius: 12px;
    margin-bottom: 2rem;
    text-align: center;
}

.gallery-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 1rem;
    max-width: 600px;
    margin: 0 auto;
}

.stat-box {
    background: rgba(255,255,255,0.1);
    padding: 1.5rem;
    border-radius: 8px;
    backdrop-filter: blur(10px);
}

.stat-number {
    display: block;
    font-size: 2.5rem;
    font-weight: bold;
    line-height: 1;
    margin-bottom: 0.5rem;
}

.stat-label {
    font-size: 0.9rem;
    opacity: 0.9;
}

.filter-row {
    margin-bottom: 1rem;
}

.filter-actions {
    text-align: center;
    margin-top: 1rem;
}

.clear-button {
    background: #95a5a6;
    color: white;
    padding: 0.8rem 1.5rem;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 1rem;
    transition: background-color 0.3s;
    margin-left: 1rem;
}

.clear-button:hover {
    background: #7f8c8d;
}

.photo-albums-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 1.5rem;
    margin: 2rem 0;
}

.photo-album-card {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
    border-left: 4px solid #3498db;
}

.photo-album-card:hover {
    transform: translateY(-3px);
}

.photo-album-card.alaska {
    border-left-color: #e74c3c;
}

.photo-album-card.cruise {
    border-left-color: #3498db;
}

.photo-album-card.vancouver {
    border-left-color: #27ae60;
}

.photo-album-card.roadtrip {
    border-left-color: #f39c12;
}

.album-header {
    background: #f8f9fa;
    padding: 1rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.album-date {
    font-weight: bold;
    color: #2c3e50;
}

.album-phase {
    font-size: 0.8rem;
    padding: 0.3rem 0.8rem;
    border-radius: 15px;
    background: #ecf0f1;
    color: #2c3e50;
}

.photo-album-card.alaska .album-phase {
    background: #e74c3c;
    color: white;
}

.photo-album-card.cruise .album-phase {
    background: #3498db;
    color: white;
}

.photo-album-card.vancouver .album-phase {
    background: #27ae60;
    color: white;
}

.photo-album-card.roadtrip .album-phase {
    background: #f39c12;
    color: white;
}

.album-content {
    padding: 1.5rem;
}

.album-content h4 {
    margin-bottom: 1rem;
    color: #2c3e50;
    font-size: 1.1rem;
}

.album-meta {
    margin-bottom: 1rem;
}

.album-meta span {
    display: block;
    font-size: 0.9rem;
    color: #666;
    margin-bottom: 0.3rem;
}

.album-excerpt {
    color: #666;
    font-size: 0.9rem;
    line-height: 1.5;
    margin-bottom: 1rem;
}

.album-actions {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.view-album-btn {
    background: #e74c3c;
    color: white;
    padding: 0.5rem 1rem;
    text-decoration: none;
    border-radius: 4px;
    font-size: 0.9rem;
    transition: background-color 0.3s;
    flex: 1;
    text-align: center;
}

.view-album-btn:hover {
    background: #c0392b;
}

.read-day-btn {
    background: #3498db;
    color: white;
    padding: 0.5rem 1rem;
    text-decoration: none;
    border-radius: 4px;
    font-size: 0.9rem;
    transition: background-color 0.3s;
    flex: 1;
    text-align: center;
}

.read-day-btn:hover {
    background: #2980b9;
}

.no-photos {
    background: #95a5a6;
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 4px;
    font-size: 0.9rem;
    flex: 1;
    text-align: center;
    display: block;
}

.upload-section {
    background: #f8f9fa;
    padding: 2rem;
    border-radius: 8px;
    margin-top: 3rem;
    text-align: center;
    border: 2px dashed #bdc3c7;
}

.upload-section h2 {
    color: #2c3e50;
    margin-bottom: 1rem;
}

@media (max-width: 768px) {
    .photo-albums-grid {
        grid-template-columns: 1fr;
    }
    
    .filter-row {
        grid-template-columns: 1fr;
    }
    
    .gallery-stats {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .album-actions {
        flex-direction: column;
    }
    
    .filter-actions {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        align-items: center;
    }
    
    .clear-button {
        margin-left: 0;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    // Clear filters functionality
    $('#clear-filters').on('click', function() {
        $('#filter-location').val('');
        $('#filter-activity').val('');
        $('#filter-date').val('');
        $('#filter-phase').val('');
        $('#photo-filter-form').submit();
    });
    
    // Phase filter handling
    $('#filter-phase').on('change', function() {
        var phase = $(this).val();
        if (phase) {
            // Auto-filter based on phase selection
            switch(phase) {
                case 'alaska':
                    $('#filter-date').val('2025-05-29'); // Start with first Alaska day
                    break;
                case 'cruise':
                    $('#filter-date').val('2025-06-01'); // Start with cruise embarkation
                    break;
                case 'vancouver':
                    $('#filter-date').val('2025-06-08'); // Vancouver start
                    break;
                case 'roadtrip':
                    $('#filter-date').val('2025-06-11'); // Road trip start
                    break;
            }
        }
    });
});
</script>

<?php get_footer(); ?>