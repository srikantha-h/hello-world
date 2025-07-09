<?php
/*
Template Name: Travel Days
*/

get_header(); ?>

<main class="site-main container">
    <div class="content-area">
        <header class="page-header">
            <h1 class="page-title">🗓️ Our Complete Travel Timeline</h1>
            <p class="page-description">Every day of our epic 21-day Alaska adventure</p>
        </header>
        
        <div class="timeline-summary">
            <?php
            $travel_days = get_posts(array(
                'post_type' => 'travel_day',
                'numberposts' => -1
            ));
            $locations = get_terms(array('taxonomy' => 'travel_location', 'hide_empty' => false));
            $activities = get_terms(array('taxonomy' => 'activity_type', 'hide_empty' => false));
            ?>
            <div class="stats-grid">
                <div class="stat-item">
                    <span class="stat-number"><?php echo count($travel_days); ?></span>
                    <span class="stat-label">Travel Days</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number"><?php echo count($locations); ?></span>
                    <span class="stat-label">Locations</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number"><?php echo count($activities); ?></span>
                    <span class="stat-label">Activities</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">21</span>
                    <span class="stat-label">Epic Days</span>
                </div>
            </div>
        </div>
        
        <div class="travel-timeline">
            <?php
            $timeline_query = new WP_Query(array(
                'post_type' => 'travel_day',
                'posts_per_page' => -1,
                'meta_key' => '_travel_date',
                'orderby' => 'meta_value',
                'order' => 'ASC'
            ));
            
            if ($timeline_query->have_posts()) :
                while ($timeline_query->have_posts()) : $timeline_query->the_post();
                    $travel_date = get_post_meta(get_the_ID(), '_travel_date', true);
                    $weather = get_post_meta(get_the_ID(), '_weather', true);
                    $google_album_url = get_post_meta(get_the_ID(), '_google_album_url', true);
                    $locations = get_the_terms(get_the_ID(), 'travel_location');
                    $activities = get_the_terms(get_the_ID(), 'activity_type');
                    ?>
                    <div class="timeline-item">
                        <div class="timeline-date">
                            <?php echo $travel_date ? date('M j, Y', strtotime($travel_date)) : ''; ?>
                        </div>
                        <div class="timeline-content">
                            <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                            
                            <?php if ($locations || $activities) : ?>
                                <div class="timeline-meta">
                                    <?php if ($locations) : ?>
                                        <span class="location">📍 <?php echo implode(', ', wp_list_pluck($locations, 'name')); ?></span>
                                    <?php endif; ?>
                                    
                                    <?php if ($activities) : ?>
                                        <span class="activity">🎯 <?php echo implode(', ', wp_list_pluck($activities, 'name')); ?></span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($weather) : ?>
                                <p class="weather">🌤️ <?php echo esc_html($weather); ?></p>
                            <?php endif; ?>
                            
                            <div class="excerpt"><?php the_excerpt(); ?></div>
                            
                            <div class="timeline-actions">
                                <a href="<?php the_permalink(); ?>" class="read-more">Read More →</a>
                                <?php if ($google_album_url) : ?>
                                    <a href="<?php echo esc_url($google_album_url); ?>" target="_blank" class="view-photos">📸 Photos</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile;
                wp_reset_postdata();
            else : ?>
                <p>🎬 No travel days found yet. Your epic adventure is being prepared!</p>
            <?php endif; ?>
        </div>
    </div>
    
    <aside class="sidebar">
        <?php get_sidebar(); ?>
    </aside>
</main>
<?php get_footer();
