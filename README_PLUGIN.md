# Alaska Travel Plugin - Installation & Troubleshooting Guide

## 🚨 **Critical Issues Fixed**

The plugin had several issues that were preventing proper functioning:

### 1. **Missing Asset Files** ✅ FIXED
- **Problem**: Plugin tried to load non-existent CSS/JS files
- **Solution**: Created `assets/plugin.js` and `assets/plugin.css`
- **Files Added**: 
  - `assets/plugin.js` - JavaScript functionality
  - `assets/plugin.css` - Styling for lightbox and admin interfaces

### 2. **Conflicting Shortcodes** ✅ FIXED
- **Problem**: Plugin defined shortcodes that conflicted with theme
- **Solution**: Commented out conflicting shortcodes in plugin
- **Theme Handles**: `travel_photo_uploader`, `travel_map`, `travel_timeline`

### 3. **Missing Custom User Role** ✅ FIXED
- **Problem**: Plugin referenced 'traveler' role that didn't exist
- **Solution**: Added automatic role creation in `init()` method

### 4. **Database Table Creation** ✅ FIXED
- **Problem**: Table creation could fail silently
- **Solution**: Added existence check before table creation

## 📦 **Installation Steps**

### 1. **Upload Plugin**
```bash
# Copy the plugin file to WordPress plugins directory
cp alaska_travel_plugin.php /path/to/wordpress/wp-content/plugins/alaska-travel-plugin/
```

### 2. **Activate Plugin**
- Go to WordPress Admin → Plugins
- Find "Alaska Travel Blog Functions"
- Click "Activate"

### 3. **Verify Installation**
- Check Admin Menu → "Alaska Travel" appears
- Verify custom user role "Traveler" is created
- Check database table `wp_alaska_travel_photos` exists

## 🔧 **Plugin Features**

### **Admin Features**
- **Alaska Travel Admin**: Main dashboard with stats
- **Photo Management**: View and manage uploaded photos
- **Traveler Management**: Register and manage travelers
- **Bulk Import**: Import multiple Google Photos albums

### **Frontend Features**
- **Photo Lightbox**: Click photos to view full-size
- **AJAX Photo Filtering**: Filter photos by location/activity
- **Travel Countdown Widget**: Shows days until trip
- **User Profile Fields**: Travel-specific user information

### **Database Features**
- **Custom Table**: `wp_alaska_travel_photos` for photo metadata
- **User Meta**: Phone, emergency contact, dietary restrictions
- **Post Meta**: Google Photos album URLs for travel days

## 🛠️ **Troubleshooting**

### **Plugin Not Working**
1. **Check Error Logs**: Look for PHP errors in WordPress debug log
2. **Verify File Permissions**: Ensure plugin files are readable
3. **Check Dependencies**: Ensure theme is active and functional

### **Photos Not Loading**
1. **Check AJAX**: Verify `alaska_travel_ajax` object is available
2. **Check Nonce**: Ensure nonce verification is working
3. **Check Permissions**: Verify user has proper capabilities

### **Admin Menu Missing**
1. **Check User Role**: Ensure user has 'manage_options' capability
2. **Check Plugin Activation**: Verify plugin is properly activated
3. **Check for Conflicts**: Disable other plugins temporarily

## 📋 **Required WordPress Setup**

### **Theme Requirements**
- Alaska Adventure Blog theme must be active
- Custom post type 'travel_day' must be registered
- Taxonomies 'travel_location' and 'activity_type' must exist

### **WordPress Settings**
- **Permalinks**: Set to "Post name" or custom structure
- **Media Library**: Ensure file uploads are working
- **User Registration**: Enable if using traveler registration

### **Server Requirements**
- **PHP**: 7.4 or higher
- **MySQL**: 5.6 or higher
- **Memory Limit**: 256MB or higher recommended

## 🔒 **Security Features**

### **Nonce Verification**
- All AJAX requests use WordPress nonces
- Form submissions are properly sanitized
- User capabilities are checked

### **Data Sanitization**
- URLs are sanitized with `sanitize_url()`
- Text fields use `sanitize_text_field()`
- User input is properly escaped

## 📱 **Mobile Compatibility**

### **Responsive Design**
- Photo grid adapts to mobile screens
- Lightbox works on touch devices
- Admin interface is mobile-friendly

### **Touch Support**
- Photo lightbox supports touch gestures
- Filter forms work on mobile browsers
- Upload functionality is touch-compatible

## 🚀 **Performance Optimization**

### **Asset Loading**
- CSS/JS only load when needed
- Images are optimized for web
- Database queries are efficient

### **Caching Compatibility**
- Works with popular caching plugins
- AJAX requests bypass cache when needed
- Static assets are cacheable

## 📞 **Support**

If you encounter issues:

1. **Check WordPress Debug Log**: Enable WP_DEBUG in wp-config.php
2. **Verify Theme Compatibility**: Ensure Alaska Adventure theme is active
3. **Test with Default Theme**: Temporarily switch to Twenty Twenty-Four
4. **Check Plugin Conflicts**: Disable other plugins one by one

## 🔄 **Updates**

### **Plugin Updates**
- Plugin version is checked on activation
- Database tables are updated automatically
- User roles are recreated if missing

### **Theme Compatibility**
- Plugin works with theme version 1.0+
- Shortcodes are handled by theme, not plugin
- Admin features are independent of theme

---

**Plugin Version**: 1.0  
**Theme Compatibility**: Alaska Adventure Blog 1.0+  
**WordPress Compatibility**: 5.0+  
**PHP Compatibility**: 7.4+ 