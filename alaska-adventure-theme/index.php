<?php
/**
 * The main template file for Alaska Adventure Theme
 */

get_header(); ?>

<main class="site-main container">
    <div class="content-area">
        <header class="page-header">
            <h1 class="page-title">🏔️ Srikantha & Cecilia's Alaska Adventure</h1>
            <p class="page-description">Follow our incredible 21-day journey through Alaska, Canada, and the American West!</p>
        </header>
        
        <div class="travel-overview">
            <div class="traveler-info">
                <h2>🌟 Our Epic Alaska Adventure</h2>
                <p><strong>Travelers:</strong> Dr. Srikantha Herath, Mrs. Cecilia Warain Herath and Friends</p>
                <p><strong>Duration:</strong> May 29 - June 18, 2025 (21 incredible days)</p>
                <div class="adventure-phases">
                    <div class="phase-item">
                        <span class="phase-icon">🏔️</span>
                        <strong>Alaska Land Tours</strong><br>
                        <small>May 29-31: Glacier trekking, train rides, wildlife</small>
                    </div>
                    <div class="phase-item">
                        <span class="phase-icon">🛳️</span>
                        <strong>Alaska Cruise</strong><br>
                        <small>June 1-8: Holland America Noordam - 7-Day Glacier Discovery</small>
                    </div>
                    <div class="phase-item">
                        <span class="phase-icon">🍁</span>
                        <strong>Vancouver Exploration</strong><br>
                        <small>June 8-10: Whistler, Victoria, Stanley Park</small>
                    </div>
                    <div class="phase-item">
                        <span class="phase-icon">🗻</span>
                        <strong>American West Road Trip</strong><br>
                        <small>June 11-17: National parks, monuments, recreation</small>
                    </div>
                </div>
                <p><strong>Countries:</strong> United States 🇺🇸, Canada 🇨🇦</p>
                <p><strong>Cruise Ship:</strong> Holland America Noordam (Stateroom 5068, Category VA)</p>
            </div>
        </div>
        
        <div class="quick-navigation">
            <h2>🧭 Quick Navigation</h2>
            <div class="nav-buttons">
                <a href="<?php echo home_url('/travel-days/'); ?>" class="nav-btn timeline-btn">
                    📅 View Timeline
                </a>
                <a href="<?php echo home_url('/photos/'); ?>" class="nav-btn photos-btn">
                    📸 Photo Gallery
                </a>
                <?php if (current_user_can('edit_posts')) : ?>
                <a href="<?php echo admin_url('post-new.php?post_type=travel_day'); ?>" class="nav-btn admin-btn">
                    ✍️ Add New Day
                </a>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="travel-timeline-preview">
            <h2>🗓️ Recent Travel Days</h2>
            <?php
            // Get travel days
            $travel_days = new WP_Query(array(
                'post_type' => 'travel_day',
                'posts_per_page' => 5,
                'meta_key' => '_travel_date',
                'orderby' => 'meta_value',
                'order' => 'ASC'
            ));
            
            if ($travel_days->have_posts()) :
                echo '<div class="timeline-preview">';
                while ($travel_days->have_posts()) : $travel_days->the_post();
                    $travel_date = get_post_meta(get_the_ID(), '_travel_date', true);
                    $weather = get_post_meta(get_the_ID(), '_weather', true);
                    ?>
                    <div class="timeline-item-preview">
                        <div class="timeline-date-small">
                            <?php echo $travel_date ? date('M j', strtotime($travel_date)) : ''; ?>
                        </div>
                        <div class="timeline-content-small">
                            <h4><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
                            <?php if ($weather) : ?>
                                <p class="weather-small">🌤️ <?php echo esc_html($weather); ?></p>
                            <?php endif; ?>
                            <div class="excerpt-small"><?php echo wp_trim_words(get_the_excerpt(), 15); ?></div>
                        </div>
                    </div>
                <?php endwhile;
                echo '</div>';
                wp_reset_postdata();
            else : ?>
                <div class="no-travel-days">
                    <p>🎬 Your Alaska adventure is being prepared!</p>
                    <p>Travel days will appear here once the theme is fully set up.</p>
                </div>
            <?php endif; ?>
            
            <div class="view-all-link">
                <a href="<?php echo home_url('/travel-days/'); ?>" class="view-all-btn">
                    View Complete Timeline →
                </a>
            </div>
        </div>
        
        <div class="blog-posts">
            <h2>📝 Latest Blog Posts</h2>
            <?php
            // Get regular blog posts
            $blog_posts = new WP_Query(array(
                'post_type' => 'post',
                'posts_per_page' => 3
            ));
            
            if ($blog_posts->have_posts()) : ?>
                <div class="posts-grid">
                    <?php while ($blog_posts->have_posts()) : $blog_posts->the_post(); ?>
                        <article class="post-item">
                            <?php if (has_post_thumbnail()) : ?>
                                <div class="post-thumbnail">
                                    <a href="<?php the_permalink(); ?>">
                                        <?php the_post_thumbnail('medium'); ?>
                                    </a>
                                </div>
                            <?php endif; ?>
                            <div class="post-content">
                                <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                                <div class="post-meta">
                                    <span class="post-date">📅 <?php echo get_the_date(); ?></span>
                                    <span class="post-author">✍️ by <?php the_author(); ?></span>
                                </div>
                                <div class="post-excerpt">
                                    <?php the_excerpt(); ?>
                                </div>
                                <a href="<?php the_permalink(); ?>" class="read-more">Read More →</a>
                            </div>
                        </article>
                    <?php endwhile; ?>
                </div>
                <?php wp_reset_postdata();
            else : ?>
                <div class="no-posts">
                    <p>🎬 No blog posts yet. Start sharing your travel experiences!</p>
                    <p>Your Alaska adventure blog is ready - begin documenting your incredible journey!</p>
                    <?php if (current_user_can('edit_posts')) : ?>
                        <a href="<?php echo admin_url('post-new.php'); ?>" class="button">Create Your First Post</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <aside class="sidebar">
        <?php get_sidebar(); ?>
    </aside>
</main>

<style>
/* Inline styles to ensure styling works */
.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

.site-main {
    display: grid;
    grid-template-columns: 1fr 300px;
    gap: 2rem;
    padding: 2rem 0;
}

.content-area {
    background: white;
    padding: 2rem;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.page-header {
    text-align: center;
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #ecf0f1;
}

.page-title {
    font-size: 2.5rem;
    color: #2c3e50;
    margin-bottom: 0.5rem;
}

.page-description {
    font-size: 1.1rem;
    color: #666;
}

.travel-overview {
    background: linear-gradient(135deg, #667db6 0%, #0082c8 100%);
    color: white;
    padding: 2rem;
    border-radius: 12px;
    margin-bottom: 2rem;
}

.adventure-phases {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin: 1rem 0;
}

.phase-item {
    background: rgba(255,255,255,0.1);
    padding: 1rem;
    border-radius: 8px;
    text-align: center;
}

.phase-icon {
    font-size: 2rem;
    display: block;
    margin-bottom: 0.5rem;
}

.quick-navigation {
    margin-bottom: 2rem;
}

.nav-buttons {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-top: 1rem;
}

.nav-btn {
    display: block;
    padding: 1rem;
    text-decoration: none;
    text-align: center;
    border-radius: 8px;
    font-weight: bold;
    transition: transform 0.3s;
}

.nav-btn:hover {
    transform: translateY(-2px);
}

.timeline-btn {
    background: #3498db;
    color: white;
}

.photos-btn {
    background: #e74c3c;
    color: white;
}

.admin-btn {
    background: #27ae60;
    color: white;
}

.timeline-preview {
    display: grid;
    gap: 1rem;
}

.timeline-item-preview {
    display: flex;
    gap: 1rem;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 8px;
    border-left: 4px solid #3498db;
}

.timeline-date-small {
    background: #e74c3c;
    color: white;
    padding: 0.5rem;
    border-radius: 8px;
    text-align: center;
    font-weight: bold;
    min-width: 60px;
    height: fit-content;
}

.timeline-content-small h4 {
    margin-bottom: 0.5rem;
}

.timeline-content-small h4 a {
    color: #2c3e50;
    text-decoration: none;
}

.weather-small {
    font-size: 0.9rem;
    color: #666;
    margin-bottom: 0.5rem;
}

.excerpt-small {
    color: #666;
    font-size: 0.9rem;
}

.view-all-btn {
    background: #3498db;
    color: white;
    padding: 1rem 2rem;
    text-decoration: none;
    border-radius: 8px;
    display: inline-block;
    margin-top: 1rem;
    font-weight: bold;
}

.posts-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
}

.post-item {
    background: #f8f9fa;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.post-content {
    padding: 1.5rem;
}

.post-meta {
    color: #666;
    font-size: 0.9rem;
    margin-bottom: 1rem;
}

.read-more {
    background: #3498db;
    color: white;
    padding: 0.5rem 1rem;
    text-decoration: none;
    border-radius: 4px;
    font-size: 0.9rem;
}

.no-travel-days,
.no-posts {
    text-align: center;
    padding: 2rem;
    background: #f8f9fa;
    border-radius: 8px;
    border: 2px dashed #bdc3c7;
}

@media (max-width: 768px) {
    .site-main {
        grid-template-columns: 1fr;
    }
    
    .timeline-item-preview {
        flex-direction: column;
    }
    
    .nav-buttons {
        grid-template-columns: 1fr;
    }
}
</style>

<?php get_footer(); ?>