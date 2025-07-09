<?php
/**
 * The template for displaying search results
 */

get_header(); ?>

<main class="site-main container">
    <div class="content-area">
        <header class="page-header">
            <h1 class="page-title">
                🔍 Search Results for: "<?php echo get_search_query(); ?>"
            </h1>
            <p class="page-description">
                Found <?php echo $wp_query->found_posts; ?> result<?php echo $wp_query->found_posts != 1 ? 's' : ''; ?> for your search
            </p>
        </header>
        
        <?php if (have_posts()) : ?>
            <div class="search-results">
                <?php while (have_posts()) : the_post(); ?>
                    <article class="search-result-item">
                        <div class="result-content">
                            <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                            
                            <div class="result-meta">
                                <span class="result-type">
                                    <?php 
                                    if (get_post_type() === 'travel_day') {
                                        echo '🗓️ Travel Day';
                                    } else {
                                        echo '📝 Blog Post';
                                    }
                                    ?>
                                </span>
                                
                                <?php if (get_post_type() === 'travel_day') : ?>
                                    <?php 
                                    $travel_date = get_post_meta(get_the_ID(), '_travel_date', true);
                                    if ($travel_date) : ?>
                                        <span class="result-date">📅 <?php echo date('M j, Y', strtotime($travel_date)); ?></span>
                                    <?php endif; ?>
                                    
                                    <?php 
                                    $locations = get_the_terms(get_the_ID(), 'travel_location');
                                    if ($locations) : ?>
                                        <span class="result-location">📍 <?php echo implode(', ', wp_list_pluck($locations, 'name')); ?></span>
                                    <?php endif; ?>
                                    
                                    <?php 
                                    $activities = get_the_terms(get_the_ID(), 'activity_type');
                                    if ($activities) : ?>
                                        <span class="result-activity">🎯 <?php echo implode(', ', wp_list_pluck($activities, 'name')); ?></span>
                                    <?php endif; ?>
                                <?php else : ?>
                                    <span class="result-date">📅 <?php echo get_the_date(); ?></span>
                                    <span class="result-author">✍️ by <?php the_author(); ?></span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="result-excerpt">
                                <?php the_excerpt(); ?>
                            </div>
                            
                            <div class="result-actions">
                                <a href="<?php the_permalink(); ?>" class="read-more">Read More →</a>
                                
                                <?php if (get_post_type() === 'travel_day') : ?>
                                    <?php 
                                    $google_album_url = get_post_meta(get_the_ID(), '_google_album_url', true);
                                    if ($google_album_url) : ?>
                                        <a href="<?php echo esc_url($google_album_url); ?>" target="_blank" class="view-photos">📸 Photos</a>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </article>
                <?php endwhile; ?>
                
                <div class="pagination">
                    <?php the_posts_pagination(array(
                        'mid_size' => 2,
                        'prev_text' => '← Previous',
                        'next_text' => 'Next →'
                    )); ?>
                </div>
            </div>
        <?php else : ?>
            <div class="no-results">
                <div class="no-results-content">
                    <h2>🔍 No Results Found</h2>
                    <p>Sorry, we couldn't find any content matching "<?php echo get_search_query(); ?>" in our Alaska adventure.</p>
                    
                    <div class="search-suggestions">
                        <h3>💡 Search Suggestions:</h3>
                        <ul>
                            <li>Check your spelling</li>
                            <li>Try different keywords</li>
                            <li>Use more general terms</li>
                            <li>Search for specific locations like "Anchorage", "Juneau", "Vancouver"</li>
                            <li>Search for activities like "Glacier", "Cruise", "Wildlife"</li>
                        </ul>
                    </div>
                    
                    <div class="search-actions">
                        <a href="<?php echo home_url('/'); ?>" class="button">🏠 Return Home</a>
                        <a href="<?php echo home_url('/travel-days/'); ?>" class="button">🗓️ Browse Timeline</a>
                        <a href="<?php echo home_url('/photos/'); ?>" class="button">📸 View Photos</a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <aside class="sidebar">
        <?php get_sidebar(); ?>
    </aside>
</main>

<style>
.search-results {
    margin-bottom: 2rem;
}

.search-result-item {
    background: white;
    padding: 2rem;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 1.5rem;
    transition: transform 0.3s ease;
}

.search-result-item:hover {
    transform: translateY(-2px);
}

.search-result-item h2 {
    margin-bottom: 1rem;
}

.search-result-item h2 a {
    color: #2c3e50;
    text-decoration: none;
    font-size: 1.4rem;
}

.search-result-item h2 a:hover {
    color: #3498db;
}

.result-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 1rem;
    font-size: 0.9rem;
}

.result-type,
.result-date,
.result-location,
.result-activity,
.result-author {
    background: #ecf0f1;
    padding: 0.3rem 0.8rem;
    border-radius: 15px;
    color: #666;
}

.result-excerpt {
    color: #666;
    line-height: 1.6;
    margin-bottom: 1rem;
}

.result-actions {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

.read-more,
.view-photos {
    background: #3498db;
    color: white;
    padding: 0.5rem 1rem;
    text-decoration: none;
    border-radius: 4px;
    font-size: 0.9rem;
    transition: background-color 0.3s;
}

.read-more:hover,
.view-photos:hover {
    background: #2980b9;
}

.view-photos {
    background: #27ae60;
}

.view-photos:hover {
    background: #229954;
}

.no-results {
    text-align: center;
    padding: 3rem 0;
}

.no-results-content {
    background: white;
    padding: 3rem;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.no-results h2 {
    color: #e74c3c;
    margin-bottom: 1rem;
}

.search-suggestions {
    background: #f8f9fa;
    padding: 2rem;
    border-radius: 8px;
    margin: 2rem 0;
    text-align: left;
}

.search-suggestions ul {
    list-style: none;
    padding: 0;
}

.search-suggestions li {
    margin-bottom: 0.5rem;
    padding-left: 1.5rem;
    position: relative;
}

.search-suggestions li:before {
    content: "💡";
    position: absolute;
    left: 0;
}

.search-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
    margin-top: 2rem;
}

.button {
    background: #3498db;
    color: white;
    padding: 1rem 2rem;
    text-decoration: none;
    border-radius: 8px;
    font-weight: bold;
    transition: background-color 0.3s;
}

.button:hover {
    background: #2980b9;
}

.pagination {
    text-align: center;
    margin-top: 2rem;
}

.pagination .page-numbers {
    display: inline-block;
    padding: 0.5rem 1rem;
    margin: 0 0.2rem;
    background: #ecf0f1;
    color: #2c3e50;
    text-decoration: none;
    border-radius: 4px;
    transition: background-color 0.3s;
}

.pagination .page-numbers:hover,
.pagination .page-numbers.current {
    background: #3498db;
    color: white;
}

@media (max-width: 768px) {
    .search-result-item {
        padding: 1.5rem;
    }
    
    .result-meta {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .result-actions {
        flex-direction: column;
    }
    
    .search-actions {
        flex-direction: column;
        align-items: center;
    }
    
    .button {
        width: 100%;
        max-width: 300px;
        text-align: center;
    }
}
</style>
<?php get_footer();
