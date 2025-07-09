<?php
/**
 * Template for displaying travel days archive
 */

get_header(); ?>

<main class="site-main container">
    <div class="content-area">
        <header class="page-header">
            <h1 class="page-title">🗓️ Our Complete Travel Timeline</h1>
            <p class="page-description">Every day of our epic 21-day Alaska adventure - from glacier trekking to cruise ports to national parks</p>
        </header>
        
        <div class="timeline-summary">
            <div class="timeline-stats">
                <?php
                $total_days = wp_count_posts('travel_day')->publish;
                $locations = get_terms(array('taxonomy' => 'travel_location', 'hide_empty' => true));
                $activities = get_terms(array('taxonomy' => 'activity_type', 'hide_empty' => true));
                ?>
                <div class="stat-item">
                    <span class="stat-number"><?php echo $total_days; ?></span>
                    <span class="stat-label">Epic Days</span>
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
                    <span class="stat-number">2</span>
                    <span class="stat-label">Countries</span>
                </div>
            </div>
        </div>
        
        <div class="timeline-filters">
            <h3>🔍 Filter by Adventure Phase:</h3>
            <div class="filter-buttons">
                <button class="filter-btn active" data-filter="all">All Days</button>
                <button class="filter-btn" data-filter="alaska">🏔️ Alaska Tours</button>
                <button class="filter-btn" data-filter="cruise">🛳️ Cruise</button>
                <button class="filter-btn" data-filter="vancouver">🍁 Vancouver</button>
                <button class="filter-btn" data-filter="roadtrip">🗻 Road Trip</button>
            </div>
        </div>
        
        <?php echo do_shortcode('[travel_timeline]'); ?>
        
        <div class="adventure-map-section">
            <h2>🗺️ Our Journey Route</h2>
            <?php echo do_shortcode('[travel_map height="600px"]'); ?>
        </div>
        
        <div class="travel-days-grid">
            <h2>📋 All Travel Days</h2>
            <?php if (have_posts()) : ?>
                <div class="days-grid">
                    <?php while (have_posts()) : the_post(); ?>
                        <?php
                        $travel_date = get_post_meta(get_the_ID(), '_travel_date', true);
                        $locations = get_the_terms(get_the_ID(), 'travel_location');
                        $activities = get_the_terms(get_the_ID(), 'activity_type');
                        $weather = get_post_meta(get_the_ID(), '_weather', true);
                        $google_album_url = get_post_meta(get_the_ID(), '_google_album_url', true);
                        
                        // Determine phase for styling
                        $phase_class = 'general';
                        if ($locations) {
                            $location_names = wp_list_pluck($locations, 'name');
                            if (in_array('Anchorage', $location_names) || in_array('Talkeetna', $location_names)) {
                                $phase_class = 'alaska-phase';
                            } elseif (in_array('Whittier', $location_names) || in_array('Hubbard Glacier', $location_names) || 
                                     in_array('Glacier Bay', $location_names) || in_array('Skagway', $location_names) ||
                                     in_array('Juneau', $location_names) || in_array('Ketchikan', $location_names) ||
                                     in_array('Inside Passage', $location_names)) {
                                $phase_class = 'cruise-phase';
                            } elseif (in_array('Vancouver', $location_names)) {
                                $phase_class = 'vancouver-phase';
                            } elseif (in_array('South Dakota', $location_names) || in_array('Wyoming', $location_names) || 
                                     in_array('Montana', $location_names) || in_array('Minneapolis', $location_names)) {
                                $phase_class = 'roadtrip-phase';
                            }
                        }
                        ?>
                        
                        <article class="day-card <?php echo $phase_class; ?>">
                            <div class="day-header">
                                <?php if ($travel_date) : ?>
                                    <div class="day-date">
                                        <?php echo date('M j', strtotime($travel_date)); ?>
                                        <span class="day-year"><?php echo date('Y', strtotime($travel_date)); ?></span>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="day-phase-badge">
                                    <?php if ($phase_class === 'alaska-phase') : ?>
                                        <span class="badge-alaska">🏔️ Alaska</span>
                                    <?php elseif ($phase_class === 'cruise-phase') : ?>
                                        <span class="badge-cruise">🛳️ Cruise</span>
                                    <?php elseif ($phase_class === 'vancouver-phase') : ?>
                                        <span class="badge-vancouver">🍁 Vancouver</span>
                                    <?php elseif ($phase_class === 'roadtrip-phase') : ?>
                                        <span class="badge-roadtrip">🗻 Road Trip</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="day-content">
                                <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                                
                                <div class="day-meta">
                                    <?php if ($locations) : ?>
                                        <span class="day-location">📍 <?php echo implode(', ', wp_list_pluck($locations, 'name')); ?></span>
                                    <?php endif; ?>
                                    
                                    <?php if ($activities) : ?>
                                        <span class="day-activity">🎯 <?php echo implode(', ', wp_list_pluck($activities, 'name')); ?></span>
                                    <?php endif; ?>
                                    
                                    <?php if ($weather) : ?>
                                        <span class="day-weather">🌤️ <?php echo esc_html($weather); ?></span>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="day-excerpt">
                                    <?php the_excerpt(); ?>
                                </div>
                                
                                <div class="day-actions">
                                    <a href="<?php the_permalink(); ?>" class="read-more">Read More →</a>
                                    <?php if ($google_album_url) : ?>
                                        <a href="<?php echo esc_url($google_album_url); ?>" target="_blank" class="view-photos">📸 Photos</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </article>
                    <?php endwhile; ?>
                </div>
                
                <div class="pagination">
                    <?php the_posts_pagination(); ?>
                </div>
            <?php else : ?>
                <p>🎬 No travel days found. Your epic adventure is being prepared!</p>
            <?php endif; ?>
        </div>
    </div>
    
    <aside class="sidebar">
        <?php get_sidebar(); ?>
    </aside>
</main>

<style>
.timeline-summary {
    background: linear-gradient(135deg, #667db6 0%, #0082c8 100%);
    color: white;
    padding: 2rem;
    border-radius: 12px;
    margin-bottom: 2rem;
}

.timeline-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: 1rem;
    text-align: center;
}

.stat-item {
    background: rgba(255,255,255,0.1);
    padding: 1rem;
    border-radius: 8px;
    backdrop-filter: blur(10px);
}

.stat-number {
    display: block;
    font-size: 2.5rem;
    font-weight: bold;
    line-height: 1;
}

.stat-label {
    font-size: 0.9rem;
    opacity: 0.9;
}

.timeline-filters {
    margin-bottom: 2rem;
    text-align: center;
}

.filter-buttons {
    display: flex;
    gap: 0.5rem;
    justify-content: center;
    flex-wrap: wrap;
    margin-top: 1rem;
}

.filter-btn {
    padding: 0.5rem 1rem;
    border: 2px solid #3498db;
    background: white;
    color: #3498db;
    border-radius: 25px;
    cursor: pointer;
    transition: all 0.3s;
    font-weight: 500;
}

.filter-btn:hover,
.filter-btn.active {
    background: #3498db;
    color: white;
}

.days-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 1.5rem;
    margin: 2rem 0;
}

.day-card {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
    border-left: 4px solid #3498db;
}

.day-card:hover {
    transform: translateY(-3px);
}

.day-card.alaska-phase {
    border-left-color: #e74c3c;
}

.day-card.cruise-phase {
    border-left-color: #3498db;
}

.day-card.vancouver-phase {
    border-left-color: #27ae60;
}

.day-card.roadtrip-phase {
    border-left-color: #f39c12;
}

.day-header {
    background: #f8f9fa;
    padding: 1rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.day-date {
    font-size: 1.2rem;
    font-weight: bold;
    color: #2c3e50;
}

.day-year {
    font-size: 0.8rem;
    opacity: 0.7;
    display: block;
}

.day-content {
    padding: 1.5rem;
}

.day-content h3 {
    margin-bottom: 1rem;
}

.day-content h3 a {
    color: #2c3e50;
    text-decoration: none;
}

.day-content h3 a:hover {
    color: #3498db;
}

.day-meta {
    margin-bottom: 1rem;
}

.day-meta span {
    display: block;
    font-size: 0.9rem;
    color: #666;
    margin-bottom: 0.3rem;
}

.day-actions {
    display: flex;
    gap: 1rem;
    margin-top: 1rem;
}

.view-photos {
    background: #e74c3c;
    color: white;
    padding: 0.5rem 1rem;
    text-decoration: none;
    border-radius: 4px;
    font-size: 0.9rem;
    transition: background-color 0.3s;
}

.view-photos:hover {
    background: #c0392b;
}

.adventure-map-section {
    margin: 3rem 0;
    text-align: center;
}

.adventure-map-section h2 {
    margin-bottom: 2rem;
    font-size: 2rem;
    color: #2c3e50;
}

@media (max-width: 768px) {
    .days-grid {
        grid-template-columns: 1fr;
    }
    
    .filter-buttons {
        flex-direction: column;
        align-items: center;
    }
    
    .day-actions {
        flex-direction: column;
    }
}
</style>

<?php get_footer(); ?>