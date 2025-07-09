<?php
/**
 * The sidebar for Alaska Adventure Theme
 */

if (!is_active_sidebar('travel-sidebar')) {
    return;
}
?>

<aside class="sidebar">
    <div class="sidebar-content">
        <?php dynamic_sidebar('travel-sidebar'); ?>
        
        <!-- Default sidebar content if no widgets are active -->
        <div class="widget">
            <h3 class="widget-title">🗓️ Adventure Countdown</h3>
            <div class="travel-countdown">
                <p>Our epic Alaska adventure begins in:</p>
                <div class="countdown-date">May 29, 2025</div>
                <p>21 days of incredible experiences!</p>
            </div>
        </div>
        
        <div class="widget">
            <h3 class="widget-title">🌟 Adventure Phases</h3>
            <ul class="adventure-phases-list">
                <li><span class="badge-alaska">🏔️ Alaska Tours</span> May 29-31</li>
                <li><span class="badge-cruise">🛳️ Cruise</span> June 1-8</li>
                <li><span class="badge-vancouver">🍁 Vancouver</span> June 8-10</li>
                <li><span class="badge-roadtrip">🗻 Road Trip</span> June 11-17</li>
            </ul>
        </div>
        
        <div class="widget">
            <h3 class="widget-title">📸 Quick Links</h3>
            <ul class="quick-links">
                <li><a href="<?php echo home_url('/travel-days/'); ?>">🗓️ Complete Timeline</a></li>
                <li><a href="<?php echo home_url('/photos/'); ?>">📸 Photo Gallery</a></li>
                <?php if (current_user_can('edit_posts')) : ?>
                    <li><a href="<?php echo admin_url('post-new.php?post_type=travel_day'); ?>">✍️ Add Travel Day</a></li>
                <?php endif; ?>
            </ul>
        </div>
        
        <div class="widget">
            <h3 class="widget-title">🛳️ Cruise Info</h3>
            <div class="cruise-widget">
                <p><strong>Ship:</strong> Holland America Noordam</p>
                <p><strong>Stateroom:</strong> 5068, Category VA</p>
                <p><strong>Voyage:</strong> 7-Day Glacier Discovery</p>
            </div>
        </div>
    </div>
</aside>

<style>
.sidebar {
    background: white;
    padding: 2rem;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    height: fit-content;
}

.widget {
    margin-bottom: 2rem;
    padding-bottom: 1.5rem;
    border-bottom: 1px solid #ecf0f1;
}

.widget:last-child {
    border-bottom: none;
    margin-bottom: 0;
}

.widget-title {
    color: #2c3e50;
    border-bottom: 2px solid #3498db;
    padding-bottom: 0.5rem;
    margin-bottom: 1rem;
    font-size: 1.2rem;
}

.travel-countdown {
    text-align: center;
    background: linear-gradient(135deg, #667db6 0%, #0082c8 100%);
    color: white;
    padding: 1.5rem;
    border-radius: 8px;
}

.countdown-date {
    font-size: 1.5rem;
    font-weight: bold;
    margin: 0.5rem 0;
}

.adventure-phases-list {
    list-style: none;
    padding: 0;
}

.adventure-phases-list li {
    margin-bottom: 0.8rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.badge-alaska,
.badge-cruise,
.badge-vancouver,
.badge-roadtrip {
    display: inline-block;
    padding: 0.3rem 0.6rem;
    border-radius: 15px;
    font-size: 0.8rem;
    font-weight: bold;
    min-width: 80px;
    text-align: center;
}

.badge-alaska {
    background: #e74c3c;
    color: white;
}

.badge-cruise {
    background: #3498db;
    color: white;
}

.badge-vancouver {
    background: #f39c12;
    color: white;
}

.badge-roadtrip {
    background: #27ae60;
    color: white;
}

.quick-links {
    list-style: none;
    padding: 0;
}

.quick-links li {
    margin-bottom: 0.5rem;
}

.quick-links a {
    color: #3498db;
    text-decoration: none;
    padding: 0.5rem 0;
    display: block;
    border-radius: 4px;
    transition: background-color 0.3s;
}

.quick-links a:hover {
    background-color: #ecf0f1;
    padding-left: 0.5rem;
}

.cruise-widget {
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 6px;
    border-left: 4px solid #3498db;
}

.cruise-widget p {
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
}

@media (max-width: 768px) {
    .sidebar {
        margin-top: 2rem;
    }
}
</style> 