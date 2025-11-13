# KG Static HTML to Page

A WordPress plugin that allows administrators to upload HTML files and assign them to WordPress pages. The HTML is rendered inline (not in an iframe) within your theme's structure.

## Description

KG Static HTML to Page enables you to upload static HTML files and display them on WordPress pages. Perfect for embedding interactive content, maps, charts, or any custom HTML that needs to be displayed within your WordPress theme's header and footer.

## Features

- ✅ Upload `.html` or `.htm` files via WordPress admin
- ✅ Assign HTML files to any WordPress page
- ✅ Render HTML inline (no iframe) - maintains theme header/footer
- ✅ Replace or append HTML to page content
- ✅ Easy management interface with assignment tracking
- ✅ Automatic file organization in uploads directory
- ✅ Remove assignments with one click

## Requirements

- WordPress 5.0 or higher
- Administrator access (manage_options capability)

## Installation

1. Upload the `kg_static_html_to_page` folder as zip file using the upload plugin feature in add plugin page.
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Navigate to **Tools → Static HTML to Page** to start uploading

## Usage

### Uploading and Assigning HTML

1. Go to **Tools → Static HTML to Page** in your WordPress admin
2. Select a page from the dropdown menu
3. Choose your `.html` or `.htm` file
4. Choose display mode:
   - **Replace page content** - HTML replaces all page content
   - **Append below page content** - HTML appears after existing content
5. Click **Upload & Assign**

### Managing Assignments

The plugin shows all pages with assigned HTML files in the "Assigned Pages" table:
- View which file is assigned to each page
- See the display mode (replace/append)
- Remove assignments with the **Remove** button

### Display Modes

**Replace Mode:**
- Completely replaces the page content with your HTML
- Original page content is hidden
- Theme header and footer remain visible

**Append Mode:**
- Adds your HTML below the existing page content
- Original page content appears first
- HTML appears after the content

## File Storage

Uploaded HTML files are stored in:
```
/wp-content/uploads/kg-static/page-{ID}.html
```

Each page gets its own file named after the page ID. If you upload a new file to the same page, it replaces the existing one.

## Examples

### Example 1: Interactive Map
Upload a Leaflet/Folium map HTML file and assign it to a "Locations" page. The map will display inline with your theme's navigation.

### Example 2: Custom Dashboard
Create a custom HTML dashboard and assign it to a private page for internal use.

### Example 3: Embedded Widget
Upload an HTML widget (e.g., a calculator, chart, or form) and append it to an existing page.

## Security Notes

⚠️ **Important Security Information:**

- Only administrators can upload HTML files
- Uploaded HTML is **not sanitized** by default (trusted admin content)
- The HTML is wrapped in `<div class="kg-static-html">` for styling isolation
- If you need sanitization, you can modify the plugin to use `wp_kses_post()` or custom KSES allowlists

**Best Practices:**
- Only upload HTML files from trusted sources
- Review HTML content before uploading
- Use append mode when possible to maintain WordPress content
- Regularly audit assigned pages

## Styling

The uploaded HTML is wrapped in a container:
```html
<div class="kg-static-html">
  <!-- Your HTML content here -->
</div>
```

You can style this container in your theme's CSS:
```css
.kg-static-html {
  /* Your custom styles */
}
```

## Common Issues & Solutions

### HTML Not Displaying

**Problem:** The page shows original content instead of uploaded HTML.

**Solutions:**
- Verify the page has an HTML file assigned (check "Assigned Pages" table)
- Ensure the file exists in `/wp-content/uploads/kg-static/`
- Check file permissions (should be readable: 0644)
- Clear any caching plugins
- Verify you're viewing the correct page

### Upload Fails

**Problem:** File upload returns an error.

**Solutions:**
- Check file size limits in PHP settings
- Verify file extension is `.html` or `.htm`
- Ensure `/wp-content/uploads/` directory is writable
- Check WordPress upload directory permissions
- Try a different HTML file to rule out file corruption

### HTML Appears Broken

**Problem:** HTML displays but styling/functionality doesn't work.

**Solutions:**
- Check browser console for JavaScript errors
- Verify all external resources (CSS, JS, images) use absolute URLs
- Ensure relative paths in HTML are correct
- Check that external scripts are allowed (CSP headers)
- Test the HTML file directly in a browser first

### File Not Found Error

**Problem:** Plugin shows "file not found" or similar error.

**Solutions:**
- Re-upload the HTML file to the page
- Check that the file exists in `/wp-content/uploads/kg-static/`
- Verify file permissions (should be 0644)
- Check WordPress upload directory is accessible

### Theme Conflicts

**Problem:** HTML doesn't display correctly with your theme.

**Solutions:**
- Check theme's `the_content` filter priority
- Verify theme doesn't strip HTML tags
- Test with a default WordPress theme to isolate the issue
- Use browser DevTools to inspect the rendered HTML

## Do's and Don'ts

### ✅ Do's

- **Do** test your HTML file in a browser before uploading
- **Do** use absolute URLs for external resources (CSS, JS, images)
- **Do** keep HTML files organized and documented
- **Do** use append mode when you want to keep WordPress content visible
- **Do** remove assignments when no longer needed
- **Do** backup your site before uploading complex HTML
- **Do** check file size (large files may cause performance issues)

### ❌ Don'ts

- **Don't** upload HTML files from untrusted sources
- **Don't** upload files with server-side includes (PHP, etc.) - only static HTML
- **Don't** use relative paths for external resources
- **Don't** upload files with embedded PHP code (won't execute)
- **Don't** forget to remove old assignments when updating pages
- **Don't** upload extremely large HTML files (>10MB may cause issues)
- **Don't** rely on this for dynamic content - it's for static HTML only
- **Don't** upload files with inline styles that conflict with your theme

## Technical Details

### File Handling
- Files are validated for `.html` and `.htm` extensions only
- Files are stored with deterministic names: `page-{ID}.html`
- Supports both absolute and relative path storage (backward compatible)
- File permissions set to 0644 (readable by web server)

### Content Filtering
- Uses `the_content` filter with priority 9999
- Only processes pages (not posts)
- Checks for assigned HTML before processing
- Gracefully falls back to original content if file is missing

### Admin Interface
- Located under **Tools → Static HTML to Page**
- Shows all pages (published, draft, pending, private)
- Indicates which pages already have HTML assigned
- Provides one-click removal of assignments

## Limitations

- **Static HTML only** - No server-side processing (PHP won't execute)
- **No automatic updates** - Must manually re-upload to update content
- **File size limits** - Subject to PHP upload limits
- **No versioning** - Uploading a new file replaces the old one
- **Theme dependency** - HTML renders within theme structure

## Support

For issues, questions, or contributions, please contact KEEP GOING Solutions.

## Changelog

### 0.0.4
- Improved file path handling (absolute paths)
- Backward compatibility for relative paths
- Better error logging for debugging
- File validation for .html and .htm extensions
- Cleaner admin UI (filename display)

### 0.0.3
- Added file extension validation
- Improved error handling
- File permissions management
- Better admin notices

### 0.0.2
- Initial release with replace/append modes
- Admin interface for file management
- Inline HTML rendering

## License

This plugin is proprietary software developed by KEEP GOING Solutions.

