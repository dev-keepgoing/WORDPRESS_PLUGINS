# KG Video Preloader

A WordPress plugin that displays an MP4 video as a fullscreen preloader overlay before your website content loads. Perfect for creating engaging first impressions with branded video content.

## Description

KG Video Preloader allows you to upload and display a custom MP4 video that plays as a preloader animation while your website loads. The video automatically fades out once the page is ready or when the video completes, providing a smooth, professional user experience.

## Features

- ✅ **Fullscreen Video Preloader** - Display MP4 videos as site preloaders
- ✅ **Flexible Display Options** - Show on all pages or home page only
- ✅ **Customizable Fade-out** - Fade when page loads or when video ends
- ✅ **Poster Image Support** - Show static image before video loads
- ✅ **Video Fit Options** - Cover (fullscreen) or Contain (no crop)
- ✅ **Loop Control** - Option to loop video continuously
- ✅ **Session Control** - Show once per browser session
- ✅ **Mobile Detection** - Option to disable on mobile devices
- ✅ **Custom Background** - Set background color behind video
- ✅ **Automatic Cleanup** - Removes itself after fade-out
- ✅ **Theme Compatible** - Works with themes that support `wp_body_open` or falls back to footer

## Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- MP4 video file (recommended: optimized for web, H.264 codec)
- Administrator access (manage_options capability)

## Installation

1. Upload the `kg-video-preloader` folder as zip file using the upload plugin feature in add plugin page.
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Navigate to **Settings → Video Preloader** to configure

## Usage

### Basic Setup

1. Go to **Settings → Video Preloader**
2. Check **"Activate video preloader"** to enable
3. Upload or select your MP4 video file
4. Configure display options
5. Click **Save Changes**

### Settings Explained

#### Enable
- **On**: Video preloader is active and will display
- **Off**: Preloader is disabled globally

#### Scope
- **All pages**: Preloader appears on every page of your site
- **Home page only**: Preloader appears only on the homepage

#### MP4 Video
- Upload or select an MP4 video from your Media Library
- Recommended: Optimize video for web (smaller file size, faster loading)
- Format: MP4 with H.264 codec for best compatibility

#### Poster (optional)
- A static image shown before the video starts loading
- Useful for:
  - Slow connections
  - Mobile browsers with autoplay restrictions
  - Providing a preview of the video content
- Formats: JPG, PNG, WebP

#### Fit
- **Fullscreen (cover)**: Video fills entire screen, may crop edges
- **Contain (no crop)**: Full video visible, may show black bars

#### Fade-out trigger
- **When page finishes loading**: Preloader fades as soon as page is ready (faster UX)
- **When video ends**: Preloader fades only after video completes playing

#### Background color
- Color shown behind the video
- Visible before video loads or if video has transparency
- Format: Hex color code (e.g., `#000000` for black)

#### Loop video
- **Enabled**: Video repeats continuously until fade-out condition
- **Disabled**: Video plays once

#### Show once per session
- **Enabled**: Preloader shows only once per browser session
- **Disabled**: Preloader shows on every page load/refresh

#### Disable on mobile
- **Enabled**: Preloader is hidden on mobile devices (phones/tablets)
- **Disabled**: Preloader shows on all devices
- **Why disable on mobile?**
  - Better performance on slower connections
  - Avoids autoplay restrictions
  - Reduces data usage
  - Faster page load times

## Mobile Detection

The plugin uses advanced mobile detection when "Disable on mobile" is enabled:

- **User Agent Detection**: Identifies iOS, Android, and other mobile devices
- **Screen Size**: Detects screens ≤ 768px wide
- **Touch Support**: Checks for touch capability

If mobile is detected and the option is enabled, the preloader is immediately removed without loading the video.

## Video Recommendations

### Best Practices

1. **File Size**: Keep videos under 5MB for faster loading
2. **Duration**: 3-10 seconds works best for preloaders
3. **Resolution**: 1920x1080 (1080p) is sufficient for most screens
4. **Codec**: Use H.264 codec for maximum compatibility
5. **Frame Rate**: 24-30 fps is standard
6. **Compression**: Use tools like HandBrake or FFmpeg to optimize

### Video Optimization Tools

- **HandBrake**: Free, open-source video transcoder
- **FFmpeg**: Command-line video processing
- **Adobe Media Encoder**: Professional video encoding
- **Online Tools**: CloudConvert, FreeConvert

## How It Works

### Display Flow

1. **Page Load Starts**: Preloader appears immediately
2. **Video Begins**: MP4 video starts playing (autoplay, muted)
3. **Page Loading**: Website content loads in background
4. **Fade-out Trigger**: Based on your setting:
   - Page load completes, OR
   - Video ends
5. **Fade Animation**: Preloader fades out smoothly (0.6s)
6. **Cleanup**: Preloader is removed from DOM
7. **Content Visible**: Website content is now visible

### Technical Details

- **Z-index**: 999999 (appears above all content)
- **Position**: Fixed, full viewport coverage
- **Autoplay**: Enabled with `muted` and `playsinline` attributes
- **Session Storage**: Used for "show once" feature
- **Safety Timeouts**: Prevents preloader from staying forever (8-12 seconds)

## Common Issues & Solutions

### Video Not Playing

**Problem:** Video doesn't start or shows black screen.

**Solutions:**
- Verify video format is MP4 with H.264 codec
- Check video file is accessible (not broken link)
- Ensure video URL is correct (full URL, not relative path)
- Test video in browser directly
- Check browser console for errors
- Try a different video file to rule out corruption

### Preloader Stays Forever

**Problem:** Preloader doesn't fade out.

**Solutions:**
- Check "Fade-out trigger" setting
- Verify page is actually loading (check Network tab)
- Clear browser cache and reload
- Check for JavaScript errors in console
- Safety timeout should remove it after 8-12 seconds

### Video Shows on Mobile When It Shouldn't

**Problem:** Preloader appears on mobile despite "Disable on mobile" being checked.

**Solutions:**
- Clear browser cache
- Verify setting is saved correctly
- Test on actual mobile device (not just browser resize)
- Check if device is actually detected as mobile
- Try different mobile device/browser

### Video Doesn't Autoplay

**Problem:** Video requires user interaction to play.

**Solutions:**
- Ensure video has `muted` attribute (required for autoplay)
- Check browser autoplay policies
- Verify `playsinline` attribute is present
- Some browsers block autoplay even with muted videos
- Consider using poster image as fallback

### Preloader Shows Every Time

**Problem:** "Show once per session" doesn't work.

**Solutions:**
- Verify setting is enabled and saved
- Check if browser supports sessionStorage
- Clear sessionStorage: `sessionStorage.clear()` in console
- Try in different browser
- Check for JavaScript errors preventing sessionStorage

### Video Quality Issues

**Problem:** Video looks pixelated or blurry.

**Solutions:**
- Upload higher resolution video (1080p recommended)
- Check "Fit" setting (Cover vs Contain)
- Ensure video isn't being compressed too much
- Verify original video quality is good
- Test video file directly in browser

### Performance Issues

**Problem:** Site loads slowly with preloader.

**Solutions:**
- Optimize video file size (compress video)
- Use poster image to reduce initial load
- Consider disabling on mobile
- Use shorter video duration
- Host video on CDN for faster delivery
- Enable "Show once per session" to reduce repeat loads

## Do's and Don'ts

### ✅ Do's

- **Do** optimize your video file size (under 5MB recommended)
- **Do** test on multiple browsers and devices
- **Do** use a poster image for better mobile experience
- **Do** keep video duration short (3-10 seconds)
- **Do** test with "Show once per session" enabled
- **Do** consider disabling on mobile for performance
- **Do** use H.264 codec for maximum compatibility
- **Do** test fade-out behavior with both trigger options

### ❌ Don'ts

- **Don't** use extremely large video files (>10MB)
- **Don't** use videos longer than 30 seconds
- **Don't** forget to test on mobile devices
- **Don't** use unsupported video formats (AVI, MOV, etc.)
- **Don't** rely solely on video autoplay (use poster as backup)
- **Don't** forget to optimize videos for web
- **Don't** use videos with audio (will be muted anyway)
- **Don't** enable on slow connections without testing

## Browser Compatibility

### Supported Browsers
- Chrome/Edge (latest)
- Firefox (latest)
- Safari (latest)
- Opera (latest)
- Mobile browsers (iOS Safari, Chrome Mobile)

### Autoplay Policies
- **Desktop**: Autoplay with muted video works in most browsers
- **Mobile**: Autoplay restrictions vary by browser and OS
- **iOS Safari**: May require user interaction
- **Android Chrome**: Generally allows muted autoplay

## Customization

### CSS Styling

You can customize the preloader appearance with CSS:

```css
/* Preloader container */
#kgvp-preloader {
  /* Your custom styles */
}

/* Video element */
#kgvp-video {
  /* Your custom styles */
}
```

### JavaScript Hooks

The plugin uses WordPress filters for extensibility:

```php
// Prevent preloader from showing
add_filter('kgvp_done', '__return_true');
```

## Performance Tips

1. **Optimize Video**: Use compression tools to reduce file size
2. **Use Poster**: Poster image loads faster than video
3. **Disable on Mobile**: Better performance on slower connections
4. **Show Once**: Reduces repeat video loads
5. **CDN Hosting**: Host video on CDN for faster delivery
6. **Lazy Load**: Consider lazy loading for non-critical videos

## Security

- All user inputs are sanitized
- Video URLs are validated and escaped
- Settings are stored securely in WordPress options
- No external scripts or dependencies

## Limitations

- **MP4 Only**: Only MP4 format is supported
- **Autoplay Restrictions**: Some browsers may block autoplay
- **File Size**: Large videos may cause slow loading
- **Mobile Autoplay**: Mobile browsers have stricter autoplay policies
- **Session Storage**: Requires browser support for "show once" feature

## Support

For issues, questions, or contributions, please contact KEEP GOING Solutions.

## Changelog

### 0.0.2
- Added mobile device detection
- Added "Disable on mobile" option
- Improved mobile detection algorithm
- Enhanced error handling
- Better fallback mechanisms

### 0.0.1
- Initial release
- Basic video preloader functionality
- Admin settings interface
- Fade-out triggers
- Session storage support

## License

This plugin is proprietary software developed by KEEP GOING Solutions.

