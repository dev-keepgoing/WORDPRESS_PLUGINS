# KG Post Popup Extension

A WordPress plugin that creates a shortcode to display dynamic popup content from Portfolio posts. Designed to work seamlessly with Popup Maker.

## Description

KG Post Popup Extension allows you to create dynamic popup content by pulling data from WordPress posts. It displays an image, title, excerpt, and call-to-action button, all customizable through shortcode parameters or custom fields.

## Features

- ✅ Display post content in popups via shortcode
- ✅ Override title, excerpt, and CTA button text directly in shortcode
- ✅ Support for custom field meta keys
- ✅ Automatic fallback to post featured image
- ✅ Responsive design (mobile-friendly)
- ✅ Clean, modern styling with sharp-cornered buttons

## Requirements

- WordPress 5.0 or higher
- Popup Maker plugin (for displaying popups)

## Installation

1. Upload the `kg_post_popup_ext` folder as zip file using the upload pluging feature in add plugin page.
2. Activate the plugin through the 'Plugins' menu in WordPress
3. No configuration needed - start using the shortcode immediately!

## Usage

### Basic Usage

Place the shortcode inside your Popup Maker popup content:

```
[kg_highlight_popup id="123"]
```

**Important:** You must provide either an `id` or `slug` parameter. The plugin will not work without one.

### Shortcode Parameters

| Parameter | Description | Example | Required |
|-----------|-------------|---------|----------|
| `id` | Post ID to display | `id="2998"` | Yes* |
| `slug` | Post slug to display | `slug="my-post"` | Yes* |
| `title` | Override the title | `title="Custom Title"` | No |
| `excerpt` | Override the excerpt | `excerpt="Custom text here"` | No |
| `cta` | Override CTA button text | `cta="Download Now"` | No |
| `title_key` | Custom field key for title | `title_key="custom_title"` | No |
| `image_key` | Custom field key for image | `image_key="hero_image"` | No |
| `excerpt_key` | Custom field key for excerpt | `excerpt_key="summary"` | No |
| `link_key` | Custom field key for link URL | `link_key="external_url"` | No |
| `cta_key` | Custom field key for CTA text | `cta_key="button_text"` | No |
| `cta_fallback` | Default CTA text if none found | `cta_fallback="Read More"` | No |

\* Either `id` or `slug` is required.

### Examples

#### Example 1: Basic Usage with Post ID
```
[kg_highlight_popup id="2998"]
```

#### Example 2: Override Title and Excerpt
```
[kg_highlight_popup id="2998" title="Featured Article" excerpt="Discover the latest insights in our comprehensive report."]
```

#### Example 3: Override CTA Button Text
```
[kg_highlight_popup id="2998" excerpt="Click here to learn more." cta="Download the report"]
```

#### Example 4: Using Post Slug
```
[kg_highlight_popup slug="featured-article" title="Check This Out!"]
```

#### Example 5: Custom Meta Keys
```
[kg_highlight_popup id="2998" title_key="popup_title" image_key="popup_image" cta_key="popup_button"]
```

### Data Priority

The plugin uses the following priority order for each field:

**Title:**
1. Shortcode `title` parameter
2. Custom field (from `title_key`, default: `kg_hp_title`)
3. Post title

**Excerpt:**
1. Shortcode `excerpt` parameter
2. Custom field (from `excerpt_key`, default: `kg_hp_excerpt`)
3. Post excerpt
4. Auto-generated from post content (24 words)

**Image:**
1. Custom field (from `image_key`, default: `kg_hp_image`) - can be attachment ID or URL
2. Post featured image

**CTA Button Text:**
1. Shortcode `cta` parameter
2. Custom field (from `cta_key`, default: `kg_hp_cta`)
3. Fallback default: "Read Article"

**Link:**
1. Custom field (from `link_key`, default: `kg_hp_link`)
2. Post permalink

### Custom Fields

The plugin uses WordPress custom fields (post meta) to store additional data. Default meta keys:

- `kg_hp_title` - Custom title
- `kg_hp_image` - Image (attachment ID or URL)
- `kg_hp_excerpt` - Custom excerpt
- `kg_hp_link` - Custom link URL
- `kg_hp_cta` - Custom CTA button text

**Image Field:**
- If you enter a number (e.g., `123`), it's treated as an attachment ID
- If you enter a URL (e.g., `https://example.com/image.jpg`), it's used directly

## Styling

The plugin includes built-in responsive styling:
- Desktop: Max width 50vw
- Mobile: Max width 90vw
- Image: Responsive, max 90% of container (500px desktop, 400px mobile)
- Button: Sharp corners, red gradient background, white uppercase text

## Common Issues & Solutions

### Popup Not Showing

**Problem:** The popup doesn't appear when triggered.

**Solutions:**
- Ensure Popup Maker is installed and activated
- Check that the shortcode is placed inside Popup Maker's popup content area
- Verify Popup Maker trigger settings (click, page load, etc.)
- Make sure you've provided a valid `id` or `slug` parameter

### No Content Displayed

**Problem:** Shortcode returns empty content.

**Solutions:**
- Verify the post ID or slug exists and is published
- Check that the post has content (title, image, or excerpt)
- Ensure the post ID is numeric: `id="123"` not `id="post-123"`

### Image Not Showing

**Problem:** Image doesn't appear in the popup.

**Solutions:**
- Check that the post has a featured image set
- If using custom field, verify the `image_key` value is correct
- For attachment ID, ensure the ID exists in Media Library
- For URL, verify the URL is accessible

### Button Text Not Changing

**Problem:** CTA button shows default "Read Article" instead of custom text.

**Solutions:**
- Use the `cta` parameter in shortcode: `cta="Your Text"`
- Or set the custom field `kg_hp_cta` (or your custom `cta_key`) on the post
- Check that the custom field value is saved correctly

## Do's and Don'ts

### ✅ Do's

- **Do** always provide `id` or `slug` parameter
- **Do** use numeric IDs: `id="123"` not `id="post-123"`
- **Do** place the shortcode inside Popup Maker's popup content
- **Do** set a featured image on posts as a fallback
- **Do** use custom fields for reusable content across multiple popups
- **Do** test on mobile devices to ensure responsive design works

### ❌ Don'ts

- **Don't** use the shortcode without `id` or `slug` - it will return empty
- **Don't** use the shortcode outside of Popup Maker - it will just display content inline
- **Don't** use HTML in shortcode parameters (they are escaped for security)
- **Don't** use the same meta keys for different purposes - stick to the defaults or be consistent
- **Don't** forget to publish the post before using it in the shortcode
- **Don't** use unpublished or draft posts - they won't be accessible

## Support

For issues, questions, or contributions, please contact KEEP GOING Solutions.

## Changelog

### 1.0.0
- Initial release
- Shortcode functionality
- Custom field support
- Responsive styling
- Sharp-cornered button design

## License

This plugin is proprietary software developed by KEEP GOING Solutions.

