<?php
/**
 * The header for Alaska Adventure Theme
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
    
    <!-- Inline critical CSS -->
    <style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        line-height: 1.6;
        color: #333;
        background-color: #f8f9fa;
        margin: 0;
        padding: 0;
    }
    
    .site-header {
        background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
        color: white;
        padding: 1rem 0;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    
    .site-header .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
    }
    
    .site-title {
        font-size: 1.8rem;
        font-weight: bold;
        text-decoration: none;
        color: white;
        margin: 0;
    }
    
    .site-title:hover {
        color: #ecf0f1;
    }
    
    .site-description {
        font-size: 0.9rem;
        opacity: 0.8;
        margin: 0.2rem 0 0 0;
    }
    
    .main-navigation ul {
        list-style: none;
        display: flex;
        gap: 1.5rem;
        margin: 0;
        padding: 0;
        flex-wrap: wrap;
    }
    
    .main-navigation a {
        color: white;
        text-decoration: none;
        padding: 0.5rem 1rem;
        border-radius: 4px;
        transition: background-color 0.3s;
        display: block;
    }
    
    .main-navigation a:hover {
        background-color: rgba(255,255,255,0.2);
    }
    
    .menu-toggle {
        display: none;
        background: none;
        border: 2px solid white;
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 4px;
        cursor: pointer;
    }
    
    .sidebar {
        background: white;
        padding: 2rem;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        height: fit-content;
    }
    
    .widget {
        margin-bottom: 2rem;
    }
    
    .widget-title {
        color: #2c3e50;
        border-bottom: 2px solid #3498db;
        padding-bottom: 0.5rem;
        margin-bottom: 1rem;
    }
    
    @media (max-width: 768px) {
        .site-header .container {
            flex-direction: column;
            gap: 1rem;
        }
        
        .main-navigation ul {
            flex-direction: column;
            gap: 0.5rem;
            width: 100%;
        }
        
        .menu-toggle {
            display: block;
        }
        
        .main-navigation ul {
            display: none;
        }
        
        .main-navigation ul.show {
            display: flex;
        }
    }
    </style>
</head>
<body <?php body_class(); ?>>

<header class="site-header">
    <div class="container">
        <div class="site-branding">
            <?php if (has_custom_logo()) : ?>
                <?php the_custom_logo(); ?>
            <?php else : ?>
                <h1 class="site-title">
                    <a href="<?php echo esc_url(home_url('/')); ?>">
                        🏔️ <?php bloginfo('name'); ?>
                    </a>
                </h1>
                <?php $description = get_bloginfo('description'); ?>
                <?php if ($description) : ?>
                    <p class="site-description"><?php echo esc_html($description); ?></p>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        
        <nav class="main-navigation">
            <button class="menu-toggle" onclick="toggleMenu()" aria-controls="primary-menu" aria-expanded="false">
                <span class="menu-toggle-text">☰ Menu</span>
            </button>
            
            <?php if (has_nav_menu('primary')) : ?>
                <?php wp_nav_menu(array(
                    'theme_location' => 'primary',
                    'menu_id' => 'primary-menu'
                )); ?>
            <?php else : ?>
                <ul id="primary-menu">
                    <li><a href="<?php echo home_url('/'); ?>">🏠 Home</a></li>
                    <li><a href="<?php echo home_url('/travel-days/'); ?>">🗓️ Timeline</a></li>
                    <li><a href="<?php echo home_url('/photos/'); ?>">📸 Photos</a></li>
                    <?php if (current_user_can('edit_posts')) : ?>
                        <li><a href="<?php echo admin_url('post-new.php?post_type=travel_day'); ?>">✍️ Add Day</a></li>
                    <?php endif; ?>
                    <?php if (is_user_logged_in()) : ?>
                        <li><a href="<?php echo wp_logout_url(home_url('/')); ?>">🚪 Logout</a></li>
                    <?php else : ?>
                        <li><a href="<?php echo wp_login_url(); ?>">🔑 Login</a></li>
                    <?php endif; ?>
                </ul>
            <?php endif; ?>
        </nav>
    </div>
</header>

<script>
function toggleMenu() {
    var menu = document.getElementById('primary-menu');
    var button = document.querySelector('.menu-toggle');
    
    if (menu.style.display === 'flex' || menu.classList.contains('show')) {
        menu.style.display = 'none';
        menu.classList.remove('show');
        button.setAttribute('aria-expanded', 'false');
    } else {
        menu.style.display = 'flex';
        menu.classList.add('show');
        button.setAttribute('aria-expanded', 'true');
    }
}
</script>