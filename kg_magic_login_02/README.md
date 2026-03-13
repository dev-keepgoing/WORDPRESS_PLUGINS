# KG Magic Login

A WordPress plugin that provides passwordless email-based magic link authentication with automatic user creation and profile completion checking.

## Description

KG Magic Login eliminates the need for passwords by sending secure, one-time login links via email. The plugin automatically creates users if they don't exist, checks for complete profile metadata, and redirects users appropriately based on their profile status.

## Features

- ✅ **Passwordless Authentication** - No passwords required, just email
- ✅ **Automatic User Creation** - Users are created automatically on first login
- ✅ **Profile Completion Check** - Validates required user metadata
- ✅ **Smart Redirects** - Different redirects for complete/incomplete profiles
- ✅ **PDF Report Downloads** - Automatic download button for logged-in users with complete profiles
- ✅ **AJAX Form Submission** - Smooth user experience without page reloads
- ✅ **Secure Token System** - Time-limited tokens (15 minutes)
- ✅ **Responsive Design** - Mobile-friendly form with customizable max-heights
- ✅ **Customizable Messages** - Override titles, subtitles, and success messages

## Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- Working email configuration (wp_mail must be functional)
- A page for profile completion (default: `/complete-profile/`)

## Installation

1. Upload the `kg_magic_login_02` folder as zip file using the upload plugin feature in add plugin page.
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Place the shortcode `[kg_magic_login_form]` on any page or post
4. Create a profile completion page (default slug: `complete-profile`)

## Usage

### Basic Usage

Place the shortcode on any page or post:

```
[kg_magic_login_form]
```

### Shortcode Parameters

| Parameter | Description | Default | Example |
|-----------|-------------|---------|---------|
| `redirect` | Redirect URL after login (fallback) | Home URL | `redirect="/members"` |
| `title` | Form title text | "Enter your email to get Access" | `title="Get Started"` |
| `subtitle` | Form subtitle text | "We'll email you a secure, one-time link..." | `subtitle="Sign in with your email"` |
| `success` | Success message after email sent | "Check your inbox for your login link." | `success="Email sent!"` |
| `redirect_complete` | Redirect for users with complete profiles | Home URL | `redirect_complete="/dashboard"` |
| `redirect_incomplete` | Redirect for users with incomplete profiles | `/complete-profile/` | `redirect_incomplete="/signup"` |
| `maxh_desktop` | Max height on desktop | `80vh` | `maxh_desktop="720px"` |
| `maxh_tablet` | Max height on tablet | `75vh` | `maxh_tablet="600px"` |
| `maxh_mobile` | Max height on mobile | `70vh` | `maxh_mobile="500px"` |

### Examples

#### Example 1: Basic Login Form
```
[kg_magic_login_form]
```

#### Example 2: Custom Redirects
```
[kg_magic_login_form redirect="/members" redirect_complete="/dashboard" redirect_incomplete="/complete-profile"]
```

#### Example 3: Custom Messages
```
[kg_magic_login_form title="Welcome Back" subtitle="Enter your email to continue" success="Check your email!"]
```

#### Example 4: Full Customization
```
[kg_magic_login_form 
    title="Access Your Report" 
    subtitle="We'll send you a secure login link" 
    success="Email sent! Check your inbox."
    redirect="/home"
    redirect_complete="/reports"
    redirect_incomplete="/complete-profile"
    maxh_desktop="90vh"
    maxh_mobile="80vh"]
```

## How It Works

### 1. User Submits Email
- User enters their email address in the form
- Form submits via AJAX (no page reload)

### 2. User Creation (if needed)
- Plugin checks if user exists by email
- If user doesn't exist, automatically creates a new WordPress user
- Username is generated from email address (e.g., `user@example.com` → `user`)

### 3. Magic Link Generation
- Plugin generates a secure, random token
- Token is stored as a transient (expires in 15 minutes)
- Magic link is sent via email with the token

### 4. User Clicks Magic Link
- User clicks the link in their email
- Plugin validates the token
- User is automatically logged in

### 5. Profile Check & Redirect
- Plugin checks if user has complete profile metadata
- **Complete profile**: Redirects to `redirect_complete` or PDF report
- **Incomplete profile**: Redirects to `redirect_incomplete` (profile completion page)

### 6. Download Button (if applicable)
- If user is logged in with complete profile AND the current post has a `report_pdf` custom field
- Form is replaced with a "Download Report" button
- Button opens the PDF in a new tab

## Required Profile Fields

The plugin checks for these user meta fields to determine if a profile is complete:

- `first_name`
- `last_name`
- `company`
- `phone`
- `postal_code`
- `news_consent`

**All fields must be filled** for the profile to be considered complete.

## PDF Report Feature

The plugin automatically shows a download button when:
1. User is logged in
2. User has complete profile metadata
3. Current post/page has a `report_pdf` custom field with a value

The download button replaces the login form and opens the PDF in a new tab.

### Using a different post as the report context (`?ref=`)

If the magic login form is on a **generic login page** (no `report_pdf` custom field), you can pass the **post ID** of the report page in the URL. The plugin will use that post for the rest of the flow: `report_pdf` meta for redirect when profile is complete, and post title for the `report` query param when redirecting to the profile completion page.

**Usage:** Add `?ref=<post_id>` to the login page URL. The post must exist (published or draft). If `ref` is missing or invalid, the current page (where the form is shown) is used. The parameter is named `ref` (not `pid`) to avoid conflicts with WordPress or other plugins that might strip it.

**Example:** From a report listing, link to the login page with a specific report's post ID:

```
https://yoursite.com/login/?ref=42
```

Or from a template:

```php
<a href="<?php echo esc_url( add_query_arg( 'ref', get_the_ID(), home_url( '/login/' ) ) ); ?>">Get this report</a>
```

The plugin will use that post's `report_pdf` (and title for the `report` param) for the magic link flow.


## Email Template

The magic link email uses this format:

```
Subject: Your Keep Going login link

Click below to sign in:

[magic link URL]

This link expires in 15 minutes.
```

To customize the email, modify the `wp_mail()` call in the plugin code.

## Security Features

- **Time-limited tokens** - Links expire after 15 minutes
- **One-time use tokens** - Tokens are deleted after successful validation
- **Nonce verification** - All form submissions are protected with WordPress nonces
- **Secure token generation** - Uses `wp_generate_password()` for cryptographically secure tokens
- **Hash comparison** - Uses `hash_equals()` to prevent timing attacks
- **Input sanitization** - All user inputs are sanitized
- **URL validation** - All redirect URLs are validated and sanitized

## Customization

### Changing Link Expiration Time

Edit the constant in the plugin file:

```php
define('KG_LINK_TTL', 15 * MINUTE_IN_SECONDS); // Change 15 to desired minutes
```

### Changing Profile Completion Page Slug

Edit the constant in the plugin file:

```php
define('KG_INCOMPLETE_PROFILE_SLUG', 'complete-profile'); // Change slug here
```

### Styling

The plugin includes scoped CSS. You can override styles in your theme:

```css
.kg-wrap { /* Main container */ }
.kg-title { /* Form title */ }
.kg-subtitle { /* Form subtitle */ }
.kg-input { /* Email input field */ }
.kg-btn { /* Submit/Download button */ }
.kg-alert { /* Success/error messages */ }
```

## Common Issues & Solutions

### Email Not Sending

**Problem:** Users don't receive magic link emails.

**Solutions:**
- Verify WordPress email configuration (`wp_mail()`)
- Check spam/junk folders
- Test with a plugin like WP Mail SMTP
- Verify server can send emails (check PHP mail() function)
- Check email server logs

### Link Expires Too Quickly

**Problem:** Users complain links expire before they can use them.

**Solutions:**
- Increase `KG_LINK_TTL` constant (default: 15 minutes)
- Consider user timezone differences
- Add reminder in email about expiration time

### User Not Redirected Correctly

**Problem:** Users aren't redirected to the expected page after login.

**Solutions:**
- Check that `redirect_complete` and `redirect_incomplete` parameters are correct
- Verify profile completion page exists and is accessible
- Check if PDF report custom field exists on the post
- Review redirect logic in plugin code

### Download Button Not Showing

**Problem:** Download button doesn't appear for logged-in users.

**Solutions:**
- Verify user has complete profile (all 6 required fields)
- Check that current post has `report_pdf` custom field
- Ensure `report_pdf` contains a valid URL
- Check user is actually logged in

### Profile Always Incomplete

**Problem:** Users always redirected to profile completion page.

**Solutions:**
- Verify all 6 required fields are saved in user meta
- Check field names match exactly (case-sensitive)
- Use a plugin like "User Meta" to inspect user meta values
- Ensure profile completion form saves data correctly

### AJAX Not Working

**Problem:** Form doesn't submit via AJAX, shows page reload.

**Solutions:**
- Check browser console for JavaScript errors
- Verify jQuery is loaded (if required)
- Check for JavaScript conflicts with other plugins
- Test with default WordPress theme to isolate issues

## Do's and Don'ts

### ✅ Do's

- **Do** test email delivery before going live
- **Do** create the profile completion page before activating
- **Do** ensure all required profile fields are collected
- **Do** test the full flow: email → link → login → redirect
- **Do** customize messages to match your brand
- **Do** set appropriate redirect URLs for your use case
- **Do** test on mobile devices (responsive design)
- **Do** monitor email delivery rates

### ❌ Don'ts

- **Don't** use this on sites without working email
- **Don't** skip the profile completion page setup
- **Don't** forget to test with new users (auto-creation)
- **Don't** use extremely short token expiration times
- **Don't** modify the token validation logic (security risk)
- **Don't** use this as the only authentication method without backup
- **Don't** ignore email delivery issues
- **Don't** forget to test the PDF download feature

## Technical Details

### Token Storage
- Tokens stored as WordPress transients
- Format: `kg_token_{user_id}`
- Expiration: 15 minutes (configurable)
- Deleted immediately after successful validation

### User Creation
- Username generated from email (before @ symbol)
- If username exists, appends number (e.g., `user1`, `user2`)
- Random password generated (20 characters)
- User role: Subscriber (default WordPress role)

### AJAX Endpoint
- Action: `kg_request_magic_link`
- Available to: Both logged-in and logged-out users
- Requires: Valid nonce verification

### Redirect Priority
1. If user has incomplete profile → `redirect_incomplete` (with `report` = post title from `ref` if set)
2. If user has complete profile AND post (`ref`) has `report_pdf` meta → that PDF URL
3. If user has complete profile → `redirect_complete`
4. Fallback → `redirect` parameter

## Limitations

- **Email dependency** - Requires working email configuration
- **15-minute expiration** - Links expire after 15 minutes
- **One-time use** - Each link can only be used once
- **Profile fields** - Hardcoded list of required fields
- **No password recovery** - This is passwordless (by design)
- **Single email per user** - One email address = one account

## Support

For issues, questions, or contributions, please contact KEEP GOING Solutions.

## Changelog

### 1.0.1
- **Report context via `?ref=`:** Support `?ref=<post_id>` on the login page. When set, that post is used as the report context (its `report_pdf` and title drive redirects). When not set, the current page is used. Uses `ref` as the URL parameter name to avoid conflicts with WordPress or other plugins.

### 1.0.0
- Initial release
- Passwordless magic link authentication
- Automatic user creation
- Profile completion checking
- PDF report download feature
- AJAX form submission
- Responsive design with customizable heights
- Sharp-cornered button styling

## License

This plugin is proprietary software developed by KEEP GOING Solutions.

