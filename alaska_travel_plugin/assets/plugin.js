/**
 * Alaska Travel Plugin JavaScript
 */

jQuery(document).ready(function($) {

    // Photo upload functionality
    if (!alaska_travel_ajax.is_logged_in) {
        $('#photo-upload-form').remove();
    } else {
        $('#photo-upload-form').on('submit', function(e) {
            e.preventDefault();
        
        var albumUrl = $('#google_album_url').val();
        var travelDayId = $(this).data('travel-day-id') || get_the_ID();
        
        if (!albumUrl) {
            alert('Please enter a Google Photos album URL.');
            return;
        }
        
        $('#upload-results').html('<p>Uploading photos...</p>');
        
        $.ajax({
            url: alaska_travel_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'upload_google_photos',
                album_url: albumUrl,
                travel_day_id: travelDayId,
                nonce: alaska_travel_ajax.nonce
            },
            success: function(response) {
                $('#upload-results').html('<p style="color: green;">' + response + '</p>');
                $('#google_album_url').val('');
            },
            error: function() {
                $('#upload-results').html('<p style="color: red;">Error uploading photos. Please try again.</p>');
            }
        });
        });
    }
    
    // Photo filtering functionality
    $('.photo-filter-form').on('submit', function(e) {
        e.preventDefault();
        
        var formData = $(this).serialize();
        var resultsContainer = $('.photo-results');
        
        resultsContainer.html('<p>Loading photos...</p>');
        
        $.ajax({
            url: alaska_travel_ajax.ajax_url,
            type: 'POST',
            data: formData + '&action=alaska_travel_filter_photos&nonce=' + alaska_travel_ajax.nonce,
            success: function(response) {
                if (response.success) {
                    resultsContainer.html(response.data);
                } else {
                    resultsContainer.html('<p>Error loading photos.</p>');
                }
            },
            error: function() {
                resultsContainer.html('<p>Error loading photos. Please try again.</p>');
            }
        });
    });
    
    // Admin functionality
    if ($('#bulk-import-form').length) {
        $('#bulk-import-form').on('submit', function(e) {
            e.preventDefault();
            
            var urls = $('#google_album_urls').val();
            if (!urls.trim()) {
                alert('Please enter at least one Google Photos album URL.');
                return;
            }
            
            $('#import-results').html('<p>Processing albums...</p>');
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'bulk_import_photos',
                    album_urls: urls,
                    nonce: alaska_travel_ajax.nonce
                },
                success: function(response) {
                    $('#import-results').html(response);
                    $('#google_album_urls').val('');
                },
                error: function() {
                    $('#import-results').html('<p style="color: red;">Error processing albums. Please try again.</p>');
                }
            });
        });
    }
    
    // Traveler registration
    if ($('#register-traveler-form').length) {
        $('#register-traveler-form').on('submit', function(e) {
            e.preventDefault();
            
            var formData = {
                action: 'register_traveler',
                username: $('#traveler_username').val(),
                email: $('#traveler_email').val(),
                first_name: $('#traveler_first_name').val(),
                last_name: $('#traveler_last_name').val(),
                phone: $('#traveler_phone').val(),
                nonce: alaska_travel_ajax.nonce
            };
            
            $('#registration-results').html('<p>Registering traveler...</p>');
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: formData,
                success: function(response) {
                    $('#registration-results').html(response);
                    if (response.includes('successfully')) {
                        $('#register-traveler-form')[0].reset();
                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    }
                },
                error: function() {
                    $('#registration-results').html('<p style="color: red;">Error registering traveler. Please try again.</p>');
                }
            });
        });
    }
    
    // Photo lightbox functionality
    $('.photo-lightbox').on('click', function(e) {
        e.preventDefault();
        
        var imageUrl = $(this).attr('href');
        var caption = $(this).find('img').attr('alt') || '';
        
        // Create lightbox overlay
        var lightbox = $('<div class="photo-lightbox-overlay">' +
            '<div class="lightbox-content">' +
            '<img src="' + imageUrl + '" alt="' + caption + '">' +
            '<div class="lightbox-caption">' + caption + '</div>' +
            '<button class="lightbox-close">&times;</button>' +
            '</div>' +
            '</div>');
        
        $('body').append(lightbox);
        $('body').addClass('lightbox-open');
        
        // Close lightbox
        lightbox.on('click', function(e) {
            if (e.target === this || $(e.target).hasClass('lightbox-close')) {
                lightbox.remove();
                $('body').removeClass('lightbox-open');
            }
        });
        
        // Close on escape key
        $(document).on('keyup.lightbox', function(e) {
            if (e.keyCode === 27) {
                lightbox.remove();
                $('body').removeClass('lightbox-open');
                $(document).off('keyup.lightbox');
            }
        });
    });
    
    // Helper function to get post ID
    function get_the_ID() {
        return $('body').attr('class').match(/postid-(\d+)/) ? 
               $('body').attr('class').match(/postid-(\d+)/)[1] : 0;
    }
    
}); 