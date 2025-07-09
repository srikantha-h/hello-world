<?php
/**
 * The template for displaying 404 pages (not found)
 */

get_header(); ?>

<main class="site-main container">
    <div class="content-area">
        <header class="page-header">
            <h1 class="page-title">❌ Page Not Found</h1>
            <p class="page-description">Sorry, we couldn't find the page you're looking for on our Alaska adventure.</p>
        </header>
        
        <div class="error-404">
            <div class="error-content">
                <div class="error-illustration">
                    <h2>🏔️ Oops! Lost in the Wilderness</h2>
                    <p>It looks like you've wandered off the beaten path. Don't worry - even the most experienced adventurers get lost sometimes!</p>
                </div>
                
                <div class="error-suggestions">
                    <h3>🧭 Let's Get You Back on Track</h3>
                    <ul>
                        <li><a href="<?php echo home_url('/'); ?>">🏠 Return to Home</a></li>
                        <li><a href="<?php echo home_url('/travel-days/'); ?>">🗓️ View Our Travel Timeline</a></li>
                        <li><a href="<?php echo home_url('/photos/'); ?>">📸 Browse Photo Gallery</a></li>
                        <li><a href="<?php echo admin_url('post-new.php?post_type=travel_day'); ?>">✍️ Add a New Travel Day</a></li>
                    </ul>
                </div>
                
                <div class="search-section">
                    <h3>🔍 Search Our Adventure</h3>
                    <?php get_search_form(); ?>
                </div>
                
                <div class="recent-posts">
                    <h3>📝 Recent Travel Days</h3>
                    <?php
                    $recent_posts = new WP_Query(array(
                        'post_type' => 'travel_day',
                        'posts_per_page' => 5,
                        'meta_key' => '_travel_date',
                        'orderby' => 'meta_value',
                        'order' => 'DESC'
                    ));
                    
                    if ($recent_posts->have_posts()) : ?>
                        <ul class="recent-posts-list">
                            <?php while ($recent_posts->have_posts()) : $recent_posts->the_post(); ?>
                                <li>
                                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                    <span class="post-date"><?php echo get_post_meta(get_the_ID(), '_travel_date', true) ? date('M j, Y', strtotime(get_post_meta(get_the_ID(), '_travel_date', true))) : ''; ?></span>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                        <?php wp_reset_postdata();
                    else : ?>
                        <p>No travel days found yet. Start your adventure!</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <aside class="sidebar">
        <?php get_sidebar(); ?>
    </aside>
</main>

<style>
.error-404 {
    text-align: center;
    padding: 3rem 0;
}

.error-illustration {
    background: linear-gradient(135deg, #667db6 0%, #0082c8 100%);
    color: white;
    padding: 3rem;
    border-radius: 12px;
    margin-bottom: 2rem;
}

.error-illustration h2 {
    font-size: 2.5rem;
    margin-bottom: 1rem;
}

.error-suggestions {
    background: #f8f9fa;
    padding: 2rem;
    border-radius: 8px;
    margin-bottom: 2rem;
    text-align: left;
}

.error-suggestions ul {
    list-style: none;
    padding: 0;
}

.error-suggestions li {
    margin-bottom: 1rem;
}

.error-suggestions a {
    color: #3498db;
    text-decoration: none;
    font-weight: bold;
    padding: 0.5rem 1rem;
    border-radius: 4px;
    transition: background-color 0.3s;
    display: inline-block;
}

.error-suggestions a:hover {
    background-color: #ecf0f1;
}

.search-section {
    background: white;
    padding: 2rem;
    border-radius: 8px;
    margin-bottom: 2rem;
    border: 2px solid #ecf0f1;
}

.search-section form {
    display: flex;
    gap: 1rem;
    max-width: 400px;
    margin: 0 auto;
}

.search-section input[type="search"] {
    flex: 1;
    padding: 0.8rem;
    border: 2px solid #ecf0f1;
    border-radius: 4px;
    font-size: 1rem;
}

.search-section input[type="submit"] {
    background: #3498db;
    color: white;
    border: none;
    padding: 0.8rem 1.5rem;
    border-radius: 4px;
    cursor: pointer;
    font-weight: bold;
}

.search-section input[type="submit"]:hover {
    background: #2980b9;
}

.recent-posts {
    background: white;
    padding: 2rem;
    border-radius: 8px;
    border: 2px solid #ecf0f1;
}

.recent-posts-list {
    list-style: none;
    padding: 0;
    text-align: left;
}

.recent-posts-list li {
    margin-bottom: 1rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #ecf0f1;
}

.recent-posts-list li:last-child {
    border-bottom: none;
    margin-bottom: 0;
}

.recent-posts-list a {
    color: #2c3e50;
    text-decoration: none;
    font-weight: bold;
    display: block;
    margin-bottom: 0.2rem;
}

.recent-posts-list a:hover {
    color: #3498db;
}

.post-date {
    color: #666;
    font-size: 0.9rem;
}

@media (max-width: 768px) {
    .error-illustration h2 {
        font-size: 2rem;
    }
    
    .search-section form {
        flex-direction: column;
    }
}
</style>

<?php get_footer(); ?> 