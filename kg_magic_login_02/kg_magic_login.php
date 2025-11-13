<?php
/**
 * Plugin Name: KG Magic Login
 * Description: Passwordless email-based magic link login with profile completion check.
 * Version: 1.0.0
 * Author: KEEP GOING Solutions
 */

if (!defined('ABSPATH')) exit;

// ====== Settings ======
define('KG_INCOMPLETE_PROFILE_SLUG', 'complete-profile'); // Page slug for profile completion
define('KG_LINK_TTL', 15 * MINUTE_IN_SECONDS); // 15-minute link validity
define('KG_REDIRECT_OK', home_url('/'));        // Default redirect after login

// ====== Helpers ======
function kg_generate_token($user_id) {
    $token = wp_generate_password(32, false);
    set_transient("kg_token_{$user_id}", $token, KG_LINK_TTL);
    return $token;
}

function kg_validate_token($user_id, $token) {
    $stored = get_transient("kg_token_{$user_id}");
    if ($stored && hash_equals($stored, $token)) {
        delete_transient("kg_token_{$user_id}");
        return true;
    }
    return false;
}

function kg_user_has_complete_metadata($user_id) {
    $required_fields = ['first_name', 'last_name', 'company', 'phone', 'postal_code', 'news_consent'];
    
    foreach ($required_fields as $field) {
        $value = get_user_meta($user_id, $field, true);
        if (empty($value)) {
            return false;
        }
    }
    
    return true;
}


// ====== Shortcode: [kg_magic_login_form redirect="/members"] ======
function kg_magic_login_form_shortcode($atts = []) {
    $atts = shortcode_atts([
        'redirect'      => KG_REDIRECT_OK,
        'title'         => 'Enter your email to get Access',
        'subtitle'      => 'We’ll email you a secure, one-time link to access your account.',
        'success'       => 'Check your inbox for your login link.',
        // Redirects after clicking magic link
        'redirect_complete'   => KG_REDIRECT_OK,
        'redirect_incomplete' => KG_INCOMPLETE_PROFILE_SLUG,
        // Responsive max-height controls (accepts any valid CSS size: e.g. 80vh, 720px)
        'maxh_desktop'  => '80vh',
        'maxh_tablet'   => '75vh',
        'maxh_mobile'   => '70vh',
    ], $atts);

    // if (is_user_logged_in()) {
    //     return '<p>You are already logged in.</p>';
    // }

    // Get current post ID and metadata (available anywhere in this function)
    $current_post_id = get_queried_object_id();
    $report_pdf = get_post_meta($current_post_id, 'report_pdf', true);
    
    // Check if user is logged in and has complete metadata
    $show_download_button = false;
    if (is_user_logged_in()) {
        $user_id = get_current_user_id();
        if (kg_user_has_complete_metadata($user_id) && !empty($report_pdf)) {
            $show_download_button = true;
        }
    }

    // Prepare notice (success or error) BEFORE rendering the form
    $notice_html = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['kg_email'])) {
        if (!wp_verify_nonce($_POST['_kg_nonce'], 'kg_request_link')) {
            $notice_html = '<div class="kg-alert kg-error" role="alert">Security check failed. Please try again.</div>';
        } else {
            $email    = sanitize_email($_POST['kg_email']);
            $redirect = esc_url_raw($_POST['kg_redirect']);
            $redirect_complete   = isset($_POST['kg_redirect_complete']) ? esc_url_raw($_POST['kg_redirect_complete']) : KG_REDIRECT_OK;
            $redirect_incomplete = isset($_POST['kg_redirect_incomplete']) ? esc_url_raw($_POST['kg_redirect_incomplete']) : home_url('/' . KG_INCOMPLETE_PROFILE_SLUG . '/');
            $refer_post = isset($_POST['kg_post_id']) ? absint($_POST['kg_post_id']) : 0;

            $user = get_user_by('email', $email);
            if (!$user) {
                $username = sanitize_user(current(explode('@', $email)));
                $i = 1; while (username_exists($username)) $username = $username . $i++;
                $user_id = wp_create_user($username, wp_generate_password(20), $email);
                if (!is_wp_error($user_id)) {
                    $user = get_user_by('id', $user_id);
                } else {
                    $notice_html = '<div class="kg-alert kg-error" role="alert">Could not create user. Please contact support.</div>';
                }
            }

            if ($user && empty($notice_html)) {
                $user_id = $user->ID;
                $token   = kg_generate_token($user_id);
                $url     = add_query_arg([
                    'kgml'     => 1,
                    'uid'      => $user_id,
                    'token'    => $token,
                    'redirect' => rawurlencode($redirect),
                    'rc'       => rawurlencode($redirect_complete),
                    'ri'       => rawurlencode($redirect_incomplete),
                    'pid'      => $refer_post,
                ], home_url('/'));

                $subject = 'Your Keep Going login link';
                $message = "Click below to sign in:\n\n{$url}\n\nThis link expires in 15 minutes.";
                $sent    = wp_mail($email, $subject, $message);

                $notice_html = $sent
                    ? '<div class="kg-alert kg-success" role="status">'.esc_html($atts['success']).'</div>'
                    : '<div class="kg-alert kg-error" role="alert">Unable to send email. Please try again later.</div>';
            }
        }
    }

    // Render form + notice inside the card
    ob_start(); ?>
    <style>
      /* Scoped styles for the KG Magic Login form */
      .kg-wrap { max-width: 840px; margin: 40px auto; padding: 40px 28px; background: #2f3941; border-radius: 10px; max-height: <?php echo esc_attr($atts['maxh_desktop']); ?>; overflow: auto; }
      .kg-title { margin: 0 0 8px; font-size: 28px; color: #ffffff; text-align: center; }
      .kg-subtitle { margin: 0 0 28px; color: #cfd6dc; text-align: center; }
      .kg-form { display: block; }
      .kg-row { display: flex; gap: 16px; }
      .kg-row--single { display: block; }
      .kg-field { width: 100%; }
      .kg-label { display: block; margin: 0 0 10px; color: #cfd6dc; font-weight: 500; }
      .kg-input { width: 100%; padding: 18px 16px; font-size: 18px; color: #ffffff; background: #3a444c; border: 1px solid #55616a; border-radius: 8px; outline: none; }
      .kg-input::placeholder { color: #9aa6af; }
      .kg-btn { display: inline-block; margin: 26px auto 0; padding: 16px 36px; font-size: 20px; letter-spacing: .5px; font-weight: 700; color: #ffffff; background: linear-gradient(180deg, #ff523d 0%, #e12e1e 100%); border: none; border-radius: 0px; cursor: pointer; text-transform: uppercase; transition: opacity 0.2s; }
      .kg-btn:hover:not(:disabled) { filter: brightness(1.05); }
      .kg-btn:active:not(:disabled) { transform: translateY(1px); }
      .kg-btn:disabled { opacity: 0.6; cursor: not-allowed; }
      .kg-notice-slot { margin-top: 16px; width: 100%; }
      .kg-alert { padding: 12px 16px; border-radius: 8px; width: 100%; box-sizing: border-box; }
      .kg-success { background: #0a8a40; color: #e9fff2; }
      .kg-error { background: #7a1a1a; color: #ffe9e9; }
      .kg-actions { display: flex; justify-content: center; }
      /* Tablet */
      @media (max-width: 1024px) {
        .kg-wrap { max-height: <?php echo esc_attr($atts['maxh_tablet']); ?>; }
      }
      /* Mobile */
      @media (max-width: 640px) {
        .kg-wrap { max-height: <?php echo esc_attr($atts['maxh_mobile']); ?>; padding: 28px 20px; }
        .kg-title { font-size: 24px; }
        .kg-btn { width: 100%; }
        .kg-alert { padding: 12px 16px; }
      }
    </style>
    <section class="kg-wrap">
      <?php if (!$show_download_button): ?>
        <h1 class="kg-title"><?php echo esc_html($atts['title']); ?></h1>
        <p class="kg-subtitle"><?php echo esc_html($atts['subtitle']); ?></p>
      <?php endif; ?>

      <?php if ($show_download_button): ?>
        <!-- Show download button when user is logged in and has complete metadata -->
        <div class="kg-download-section">
          <div class="kg-actions">
            <button type="button" class="kg-btn kg-btn-download" onclick="window.open('<?php echo esc_url($report_pdf); ?>', '_blank')">
              Download Report
            </button>
          </div>
        </div>
      <?php else: ?>
        <!-- Show login form when user is not logged in or metadata incomplete -->
        <form method="post" class="kg-form kg-login-form" novalidate>
          <?php wp_nonce_field('kg_request_link', '_kg_nonce'); ?>

          <div class="kg-row kg-row--single">
              <div class="kg-field">
                  <label class="kg-label" for="kg_email">Work Email Address</label>
                  <input class="kg-input" id="kg_email" type="email" name="kg_email"
                         placeholder="you@domain.com" required>
              </div>
          </div>

          <input type="hidden" name="kg_redirect" value="<?php echo esc_attr($atts['redirect']); ?>">
          <input type="hidden" name="kg_redirect_complete" value="<?php echo esc_attr($atts['redirect_complete']); ?>">
          <input type="hidden" name="kg_redirect_incomplete" value="<?php echo esc_attr($atts['redirect_incomplete']); ?>">
          <input type="hidden" name="kg_post_id" value="<?php echo (int) get_queried_object_id(); ?>">
          <div class="kg-actions">
            <button type="submit" class="kg-btn">SUBMIT</button>
          </div>

          <div class="kg-notice-slot">
            <?php if ($notice_html) : ?>
              <?php echo $notice_html; // safe prebuilt HTML - fallback for non-JS ?>
            <?php endif; ?>
          </div>
      </form>

      <script>
      (function() {
        var form = document.querySelector('.kg-login-form');
        if (!form) return;

        form.addEventListener('submit', function(e) {
          e.preventDefault();

          var noticeSlot = form.querySelector('.kg-notice-slot');
          var submitBtn = form.querySelector('.kg-btn');
          var emailInput = form.querySelector('input[name="kg_email"]');
          var originalBtnText = submitBtn.textContent;

          // Disable button and show loading
          submitBtn.disabled = true;
          submitBtn.textContent = 'Sending...';

          // Clear previous messages
          if (noticeSlot) {
            noticeSlot.innerHTML = '';
          }

          // Prepare form data
          var formData = new FormData();
          formData.append('action', 'kg_request_magic_link');
          formData.append('nonce', '<?php echo wp_create_nonce('kg_request_link'); ?>');
          formData.append('email', emailInput.value);
          formData.append('redirect', form.querySelector('input[name="kg_redirect"]').value);
          formData.append('redirect_complete', form.querySelector('input[name="kg_redirect_complete"]').value);
          formData.append('redirect_incomplete', form.querySelector('input[name="kg_redirect_incomplete"]').value);
          formData.append('post_id', form.querySelector('input[name="kg_post_id"]').value);
          formData.append('success_message', '<?php echo esc_js($atts['success']); ?>');

          // Send AJAX request
          fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
            method: 'POST',
            body: formData
          })
          .then(function(response) {
            return response.json();
          })
          .then(function(data) {
            submitBtn.disabled = false;
            submitBtn.textContent = originalBtnText;

            if (noticeSlot) {
              if (data.success) {
                noticeSlot.innerHTML = '<div class="kg-alert kg-success" role="status">' + data.data.message + '</div>';
                form.reset();
              } else {
                noticeSlot.innerHTML = '<div class="kg-alert kg-error" role="alert">' + (data.data.message || 'An error occurred. Please try again.') + '</div>';
              }
              // Scroll to notice
              noticeSlot.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
          })
          .catch(function(error) {
            submitBtn.disabled = false;
            submitBtn.textContent = originalBtnText;
            if (noticeSlot) {
              noticeSlot.innerHTML = '<div class="kg-alert kg-error" role="alert">An error occurred. Please try again.</div>';
            }
          });
        });
      })();
      </script>
      <?php endif; ?>
    </section>
    <?php
    return ob_get_clean();
}


add_shortcode('kg_magic_login_form', 'kg_magic_login_form_shortcode');

// ====== AJAX Handler for Magic Login Form ======
function kg_ajax_request_magic_link() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'kg_request_link')) {
        wp_send_json_error(['message' => 'Security check failed. Please try again.']);
    }

    $email               = sanitize_email($_POST['email']);
    $redirect            = esc_url_raw($_POST['redirect']);
    $redirect_complete   = isset($_POST['redirect_complete']) ? esc_url_raw($_POST['redirect_complete']) : KG_REDIRECT_OK;
    $redirect_incomplete = isset($_POST['redirect_incomplete']) ? esc_url_raw($_POST['redirect_incomplete']) : home_url('/' . KG_INCOMPLETE_PROFILE_SLUG . '/');
    $refer_post          = isset($_POST['post_id']) ? absint($_POST['post_id']) : 0;

    if (empty($email)) {
        wp_send_json_error(['message' => 'Email address is required.']);
    }

    $user = get_user_by('email', $email);
    if (!$user) {
        $username = sanitize_user(current(explode('@', $email)));
        $i = 1;
        while (username_exists($username)) {
            $username = $username . $i++;
        }
        $user_id = wp_create_user($username, wp_generate_password(20), $email);
        if (is_wp_error($user_id)) {
            wp_send_json_error(['message' => 'Could not create user. Please contact support.']);
        }
        $user = get_user_by('id', $user_id);
    }

    if ($user) {
        $user_id = $user->ID;
        $token   = kg_generate_token($user_id);
        $url     = add_query_arg([
            'kgml'     => 1,
            'uid'      => $user_id,
            'token'    => $token,
            'redirect' => rawurlencode($redirect),
            'rc'       => rawurlencode($redirect_complete),
            'ri'       => rawurlencode($redirect_incomplete),
            'pid'      => $refer_post,
        ], home_url('/'));

        $subject = 'Your Keep Going login link';
        $message = "Click below to sign in:\n\n{$url}\n\nThis link expires in 15 minutes.";
        $sent    = wp_mail($email, $subject, $message);

        if ($sent) {
            $success_msg = isset($_POST['success_message']) ? sanitize_text_field($_POST['success_message']) : 'Check your inbox for your login link.';
            wp_send_json_success(['message' => esc_html($success_msg)]);
        } else {
            wp_send_json_error(['message' => 'Unable to send email. Please try again later.']);
        }
    }

    wp_send_json_error(['message' => 'An error occurred. Please try again.']);
}
add_action('wp_ajax_kg_request_magic_link', 'kg_ajax_request_magic_link');
add_action('wp_ajax_nopriv_kg_request_magic_link', 'kg_ajax_request_magic_link');

// ====== Handle the magic link ======
function kg_handle_magic_link() {
    if (!isset($_GET['kgml'])) return;

    $uid = absint($_GET['uid']);
    $token = sanitize_text_field($_GET['token']);
    $redirect = esc_url_raw(wp_unslash($_GET['redirect'] ?? KG_REDIRECT_OK));
    $redirect_complete   = esc_url_raw(wp_unslash($_GET['rc'] ?? ''));
    $redirect_incomplete = esc_url_raw(wp_unslash($_GET['ri'] ?? ''));

    if (!kg_validate_token($uid, $token)) wp_die('Invalid or expired link.');

    wp_set_auth_cookie($uid, true);
    wp_set_current_user($uid);

    // Check if user has complete metadata
    if (!kg_user_has_complete_metadata($uid)) {
        // Redirect to incomplete profile page if metadata is missing
        $final_redirect = $redirect_incomplete ?: home_url('/complete-profile/');
        
        // Add report parameter if post ID is available
        $post_id = isset($_GET['pid']) ? absint($_GET['pid']) : 0;
        if ($post_id) {
            $post_title = get_the_title($post_id);
            if (!empty($post_title)) {
                $final_redirect = add_query_arg('report', urlencode($post_title), $final_redirect);
            }
        }
        
        wp_safe_redirect($final_redirect);
        exit;
    }

    // User has complete metadata - redirect to report_pdf from the post ID
    $post_id = isset($_GET['pid']) ? absint($_GET['pid']) : 0;
    $report_pdf = '';
    
    // Get report_pdf from the post metadata
    if ($post_id) {
        $report_pdf = get_post_meta($post_id, 'report_pdf', true);
    }
    
    // Priority: Always redirect to report_pdf if it exists, otherwise fall back to rc
    if (!empty($report_pdf)) {
        wp_safe_redirect(esc_url_raw($report_pdf));
    } else {
        // Fallback to complete redirect URL if report_pdf doesn't exist
        $final_redirect = $redirect_complete ?: $redirect;
        wp_safe_redirect($final_redirect);
    }
    exit;
}
add_action('init', 'kg_handle_magic_link');



