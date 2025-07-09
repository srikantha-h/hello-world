<?php
/**
 * Template for displaying single travel day posts
 */

get_header(); ?>

<main class="site-main container">
    <div class="content-area">
        <?php while (have_posts()) : the_post(); ?>
            <article class="travel-day-single">
                <header class="entry-header">
                    <h1 class="entry-title"><?php the_title(); ?></h1>
                    
                    <div class="travel-meta">
                        <?php
                        $travel_date = get_post_meta(get_the_ID(), '_travel_date', true);
                        $weather = get_post_meta(get_the_ID(), '_weather', true);
                        $accommodation = get_post_meta(get_the_ID(), '_accommodation', true);
                        $google_album_url = get_post_meta(get_the_ID(), '_google_album_url', true);
                        ?>
                        
                        <?php if ($travel_date) : ?>
                            <div class="travel-meta-item">
                                <span class="travel-meta-label">📅 Date</span>
                                <span class="travel-meta-value"><?php echo date('F j, Y', strtotime($travel_date)); ?></span>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($weather) : ?>
                            <div class="travel-meta-item">
                                <span class="travel-meta-label">🌤️ Weather</span>
                                <span class="travel-meta-value"><?php echo esc_html($weather); ?></span>
                            </div>
                        <?php endif; ?>
                        
                        <?php 
                        $locations = get_the_terms(get_the_ID(), 'travel_location');
                        if ($locations) : ?>
                            <div class="travel-meta-item">
                                <span class="travel-meta-label">📍 Location</span>
                                <span class="travel-meta-value">
                                    <?php 
                                    $location_names = array();
                                    foreach ($locations as $location) {
                                        $location_names[] = $location->name;
                                    }
                                    echo implode(', ', $location_names);
                                    ?>
                                </span>
                            </div>
                        <?php endif; ?>
                        
                        <?php 
                        $activities = get_the_terms(get_the_ID(), 'activity_type');
                        if ($activities) : ?>
                            <div class="travel-meta-item">
                                <span class="travel-meta-label">🎯 Activity Type</span>
                                <span class="travel-meta-value">
                                    <?php 
                                    $activity_names = array();
                                    foreach ($activities as $activity) {
                                        $activity_names[] = $activity->name;
                                    }
                                    echo implode(', ', $activity_names);
                                    ?>
                                </span>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($accommodation) : ?>
                            <div class="travel-meta-item">
                                <span class="travel-meta-label">🏨 Accommodation</span>
                                <span class="travel-meta-value"><?php echo esc_html($accommodation); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($google_album_url) : ?>
                        <div class="google-photos-section">
                            <a href="<?php echo esc_url($google_album_url); ?>" target="_blank" class="google-album-link">
                                📸 View Photos on Google Photos
                            </a>
                            
                            <div class="google-photos-embed">
                                <h4>🖼️ Photo Preview</h4>
                                <p>Click the link above to view the full album with all photos from this day!</p>
                                <div class="album-info">
                                    <span class="album-platform">📱 Google Photos Album</span>
                                    <span class="album-status">✅ Link Active</span>
                                </div>
                            </div>
                        </div>
                        
                        <style>
                        .google-photos-section {
                            background: linear-gradient(135deg, #4285f4, #34a853);
                            color: white;
                            padding: 2rem;
                            border-radius: 12px;
                            margin: 2rem 0;
                            text-align: center;
                        }
                        
                        .google-album-link {
                            display: inline-block;
                            background: rgba(255,255,255,0.2);
                            color: white;
                            padding: 1rem 2rem;
                            text-decoration: none;
                            border-radius: 8px;
                            font-weight: bold;
                            font-size: 1.1rem;
                            margin-bottom: 1rem;
                            border: 2px solid rgba(255,255,255,0.3);
                            transition: all 0.3s ease;
                        }
                        
                        .google-album-link:hover {
                            background: rgba(255,255,255,0.3);
                            transform: translateY(-2px);
                            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
                        }
                        
                        .google-photos-embed {
                            background: rgba(255,255,255,0.1);
                            padding: 1.5rem;
                            border-radius: 8px;
                            margin-top: 1rem;
                        }
                        
                        .album-info {
                            display: flex;
                            justify-content: center;
                            gap: 2rem;
                            margin-top: 1rem;
                            flex-wrap: wrap;
                        }
                        
                        .album-platform,
                        .album-status {
                            background: rgba(255,255,255,0.2);
                            padding: 0.5rem 1rem;
                            border-radius: 20px;
                            font-size: 0.9rem;
                        }
                        
                        @media (max-width: 768px) {
                            .google-photos-section {
                                padding: 1.5rem;
                            }
                            
                            .album-info {
                                flex-direction: column;
                                gap: 1rem;
                            }
                        }
                        </style>
                    <?php endif; ?>
                    
                    <?php 
                    // Add cruise info if this is a cruise day
                    if ($locations && in_array('Whittier', wp_list_pluck($locations, 'name')) || 
                        in_array('Hubbard Glacier', wp_list_pluck($locations, 'name')) ||
                        in_array('Glacier Bay', wp_list_pluck($locations, 'name')) ||
                        in_array('Skagway', wp_list_pluck($locations, 'name')) ||
                        in_array('Juneau', wp_list_pluck($locations, 'name')) ||
                        in_array('Ketchikan', wp_list_pluck($locations, 'name')) ||
                        in_array('Inside Passage', wp_list_pluck($locations, 'name')) ||
                        in_array('Vancouver', wp_list_pluck($locations, 'name'))) : ?>
                        <div class="cruise-info">
                            <h4>🛳️ Holland America Cruise Information</h4>
                            <p><strong>Ship:</strong> Holland America Noordam</p>
                            <p><strong>Voyage:</strong> 7-Day Glacier Discovery Southbound</p>
                            <div class="stateroom-info">
                                <p><strong>Stateroom:</strong> 5068, Category VA</p>
                                <p><strong>Bed Configuration:</strong> Convertible Twins</p>
                                <p><strong>Dining:</strong> GoldStar Service with complimentary meals</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </header>
                
                <div class="entry-content">
                    <?php the_content(); ?>
                    
                    <?php if (get_edit_post_link()) : ?>
                        <p><a href="<?php echo get_edit_post_link(); ?>" class="edit-link">✏️ Edit this travel day</a></p>
                    <?php endif; ?>
                </div>
                
                <?php echo do_shortcode('[travel_photo_uploader travel_day_id="' . get_the_ID() . '"]'); ?>
                
                <footer class="entry-footer">
                    <div class="post-navigation">
                        <?php
                        $prev_post = get_previous_post();
                        $next_post = get_next_post();
                        ?>
                        
                        <div class="nav-links">
                            <?php if ($prev_post) : ?>
                                <div class="nav-previous">
                                    <a href="<?php echo get_permalink($prev_post->ID); ?>" rel="prev">
                                        ← <?php echo get_the_title($prev_post->ID); ?>
                                    </a>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($next_post) : ?>
                                <div class="nav-next">
                                    <a href="<?php echo get_permalink($next_post->ID); ?>" rel="next">
                                        <?php echo get_the_title($next_post->ID); ?> →
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="back-to-timeline">
                        <a href="<?php echo home_url('/travel-days/'); ?>" class="back-link">
                            ← Back to Full Timeline
                        </a>
                    </div>
                </footer>
            </article>
            
            <?php
            // Show comments if enabled
            if (comments_open() || get_comments_number()) {
                comments_template();
            }
            ?>
        <?php endwhile; ?>
    </div>
    
    <aside class="sidebar">
        <?php get_sidebar(); ?>
    </aside>
</main>

<?php get_footer(); ?>