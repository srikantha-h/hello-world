// Alaska Adventure Theme Custom JavaScript

jQuery(document).ready(function($) {
    
    // Photo filtering functionality
    $('#photo-filter-form').on('submit', function(e) {
        e.preventDefault();
        
        var location = $('#filter-location').val();
        var activity = $('#filter-activity').val();
        var date = $('#filter-date').val();
        var phase = $('#filter-phase').val();
        
        // Show loading state
        $('#photo-results').html('<div class="loading">Loading photos<span class="loading-dots"></span></div>');
        
        $.ajax({
            url: ajax_object.ajax_url,
            type: 'POST',
            data: {
                action: 'alaska_travel_filter_photos',
                location: location,
                activity: activity,
                date: date,
                phase: phase,
                nonce: ajax_object.nonce
            },
            success: function(response) {
                $('#photo-results').html(response);
                // Re-initialize any interactive elements
                initializePhotoGallery();
            },
            error: function() {
                $('#photo-results').html('<p>Error loading photos. Please try again.</p>');
            }
        });
    });
    
    // Auto-submit form when filters change
    $('.photo-filter-select, .photo-filter-input').on('change', function() {
        $('#photo-filter-form').submit();
    });
    
    // Timeline filtering
    $('.filter-btn').on('click', function() {
        var filter = $(this).data('filter');
        
        // Update active state
        $('.filter-btn').removeClass('active');
        $(this).addClass('active');
        
        // Filter timeline items
        if (filter === 'all') {
            $('.timeline-item').fadeIn();
        } else {
            $('.timeline-item').hide();
            $('.timeline-item[data-phase="' + filter + '"]').fadeIn();
        }
    });
    
    // Smooth scrolling for timeline navigation
    $('a[href^="#"]').on('click', function(e) {
        e.preventDefault();
        var target = $(this.getAttribute('href'));
        if (target.length) {
            $('html, body').stop().animate({
                scrollTop: target.offset().top - 100
            }, 1000);
        }
    });
    
    // Image lazy loading for better performance
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.remove('lazy');
                    imageObserver.unobserve(img);
                }
            });
        });
        
        document.querySelectorAll('img[data-src]').forEach(img => {
            imageObserver.observe(img);
        });
    }
    
    // Mobile menu toggle
    $('.menu-toggle').on('click', function() {
        $('.main-navigation ul').slideToggle();
        $(this).attr('aria-expanded', function(i, attr) {
            return attr === 'true' ? 'false' : 'true';
        });
    });
    
    // Photo gallery lightbox effect
    function initializePhotoGallery() {
        $('.photo-item img, .album-thumbnail').off('click').on('click', function() {
            var src = $(this).attr('src') || $(this).data('src');
            var alt = $(this).attr('alt') || 'Alaska Adventure Photo';
            
            var lightbox = $('<div class="lightbox-overlay">' +
                '<div class="lightbox-content">' +
                '<img src="' + src + '" alt="' + alt + '">' +
                '<button class="lightbox-close" aria-label="Close lightbox">&times;</button>' +
                '</div>' +
                '</div>');
            
            $('body').append(lightbox);
            lightbox.fadeIn(300);
            
            // Close lightbox
            $('.lightbox-close, .lightbox-overlay').on('click', function(e) {
                if (e.target === this) {
                    lightbox.fadeOut(300, function() {
                        lightbox.remove();
                    });
                }
            });
            
            // Close on escape key
            $(document).on('keydown.lightbox', function(e) {
                if (e.keyCode === 27) {
                    lightbox.fadeOut(300, function() {
                        lightbox.remove();
                    });
                    $(document).off('keydown.lightbox');
                }
            });
        });
    }
    
    // Initialize photo gallery on page load
    initializePhotoGallery();
    
    // Travel day cards hover effects
    $('.day-card, .photo-album-card, .timeline-item').hover(
        function() {
            $(this).addClass('hover-zoom');
        },
        function() {
            $(this).removeClass('hover-zoom');
        }
    );
    
    // Countdown timer for trip start
    function updateCountdown() {
        var startDate = new Date('2025-05-29T00:00:00').getTime();
        var now = new Date().getTime();
        var distance = startDate - now;
        
        if (distance > 0) {
            var days = Math.floor(distance / (1000 * 60 * 60 * 24));
            var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            
            $('.countdown-timer').html(
                '<div class="countdown-item"><span>' + days + '</span> Days</div>' +
                '<div class="countdown-item"><span>' + hours + '</span> Hours</div>' +
                '<div class="countdown-item"><span>' + minutes + '</span> Minutes</div>'
            );
        } else {
            $('.countdown-timer').html('<div class="countdown-started">🎉 Our Alaska Adventure is underway!</div>');
        }
    }
    
    // Update countdown every minute
    if ($('.countdown-timer').length) {
        updateCountdown();
        setInterval(updateCountdown, 60000);
    }
    
    // Google Photos album integration
    $('.google-album-link').on('click', function(e) {
        // Track photo album clicks for analytics
        if (typeof gtag !== 'undefined') {
            gtag('event', 'photo_album_click', {
                'album_title': $(this).closest('.timeline-item, .day-card').find('h3, h4').first().text()
            });
        }
    });
    
    // Weather icons based on text
    function addWeatherIcons() {
        $('.weather, .day-weather, .album-weather').each(function() {
            var weatherText = $(this).text().toLowerCase();
            var icon = '';
            
            if (weatherText.includes('sunny') || weatherText.includes('clear')) {
                icon = '☀️ ';
            } else if (weatherText.includes('cloudy') || weatherText.includes('overcast')) {
                icon = '☁️ ';
            } else if (weatherText.includes('rain') || weatherText.includes('shower')) {
                icon = '🌧️ ';
            } else if (weatherText.includes('snow') || weatherText.includes('blizzard')) {
                icon = '❄️ ';
            } else if (weatherText.includes('wind')) {
                icon = '💨 ';
            } else if (weatherText.includes('fog')) {
                icon = '🌫️ ';
            } else {
                icon = '🌤️ ';
            }
            
            if (!$(this).text().match(/^[🌤️☀️☁️🌧️❄️💨🌫️]/)) {
                $(this).prepend(icon);
            }
        });
    }
    
    // Add weather icons on page load
    addWeatherIcons();
    
    // Animate timeline items on scroll
    function animateOnScroll() {
        $('.timeline-item').each(function() {
            var elementTop = $(this).offset().top;
            var viewportBottom = $(window).scrollTop() + $(window).height();
            
            if (elementTop < viewportBottom - 100) {
                $(this).addClass('animate-in');
            }
        });
    }
    
    // Animate on scroll
    $(window).on('scroll', throttle(animateOnScroll, 100));
    animateOnScroll(); // Initial check
    
    // Throttle function for scroll events
    function throttle(func, wait) {
        var timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
    
    // Phase-based styling for travel days
    function addPhaseClasses() {
        $('.timeline-item, .day-card, .photo-album-card').each(function() {
            var date = $(this).find('[data-date]').data('date') || 
                      $(this).data('date') ||
                      $(this).find('.timeline-date, .day-date, .album-date').text();
            
            if (date) {
                var dateObj = new Date(date);
                var month = dateObj.getMonth() + 1;
                var day = dateObj.getDate();
                
                // Determine phase based on date
                if ((month === 5 && day >= 29) || (month === 6 && day <= 1)) {
                    $(this).addClass('phase-alaska');
                } else if (month === 6 && day >= 1 && day <= 8) {
                    $(this).addClass('phase-cruise');
                } else if (month === 6 && day >= 8 && day <= 10) {
                    $(this).addClass('phase-vancouver');
                } else if (month === 6 && day >= 11) {
                    $(this).addClass('phase-roadtrip');
                }
            }
        });
    }
    
    // Add phase classes on page load
    addPhaseClasses();
    
    // Copy to clipboard functionality for sharing
    $('.share-day-btn').on('click', function(e) {
        e.preventDefault();
        var url = $(this).data('url') || window.location.href;
        
        if (navigator.clipboard) {
            navigator.clipboard.writeText(url).then(function() {
                showNotification('Link copied to clipboard!', 'success');
            });
        } else {
            // Fallback for older browsers
            var textArea = document.createElement('textarea');
            textArea.value = url;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
            showNotification('Link copied to clipboard!', 'success');
        }
    });
    
    // Notification system
    function showNotification(message, type) {
        var notification = $('<div class="notification ' + type + '">' + message + '</div>');
        $('body').append(notification);
        
        setTimeout(function() {
            notification.addClass('show');
        }, 100);
        
        setTimeout(function() {
            notification.removeClass('show');
            setTimeout(function() {
                notification.remove();
            }, 300);
        }, 3000);
    }
    
    // Alaska-specific interactions
    $('.glacier-item').on('click', function() {
        showNotification('🧊 Glacier facts: This glacier is thousands of years old!', 'info');
    });
    
    $('.wildlife-item').on('click', function() {
        showNotification('🐻 Wildlife spotted! Keep your distance and enjoy the view.', 'info');
    });
    
    $('.cruise-item').on('click', function() {
        showNotification('🛳️ Aboard the Holland America Noordam - enjoy the journey!', 'info');
    });
    
    // Keyboard navigation for accessibility
    $('.timeline-item, .day-card, .photo-album-card').on('keydown', function(e) {
        if (e.keyCode === 13 || e.keyCode === 32) { // Enter or Space
            e.preventDefault();
            $(this).find('a').first().click();
        }
    });
    
    // Add tabindex for keyboard navigation
    $('.timeline-item, .day-card, .photo-album-card').attr('tabindex', '0');
    
    console.log('🏔️ Alaska Adventure Theme loaded successfully!');
});