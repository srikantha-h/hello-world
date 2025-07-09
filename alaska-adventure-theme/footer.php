<?php
/**
 * The footer for Alaska Adventure Theme
 */
?>

<footer class="site-footer">
    <div class="container">
        <div class="footer-content">
            <p>&copy; <?php echo date('Y'); ?> Srikantha & Cecilia's Alaska Adventure. All rights reserved.</p>
            <p>🏔️ Epic 21-Day Journey: Alaska → Canada → American West 🗻</p>
            <p>May 29 - June 18, 2025 • From Glacier Trekking to Cruise Adventures</p>
            
            <?php if (has_nav_menu('footer')) : ?>
                <nav class="footer-navigation">
                    <?php wp_nav_menu(array(
                        'theme_location' => 'footer',
                        'menu_class' => 'footer-menu'
                    )); ?>
                </nav>
            <?php endif; ?>
            
            <div class="adventure-summary">
                <p><strong>Our Epic Adventure:</strong></p>
                <p>🚂 Alaska Railroad • 🛳️ Holland America Noordam • 🏔️ Matanuska Glacier • 🗻 Mount Rushmore • 🌋 Yellowstone</p>
            </div>
        </div>
    </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>