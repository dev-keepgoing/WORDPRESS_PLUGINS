<?php
/*
Plugin Name: KG Static HTML to Page
Description: Upload an HTML file and assign it to a WordPress page. The page will render that HTML inline (no iframe).
Version: 1.0.0
Author: KEEP GOING Solutions
*/

if (!defined('ABSPATH')) exit;

class KG_Static_HTML_To_Page {
  const META_KEY = '_kg_static_html_file';

  public function __construct() {
    // Admin UI
    add_action('admin_menu', [$this, 'add_menu']);
    add_action('admin_init', [$this, 'handle_upload']);

    // Allow .html uploads for admins
    add_filter('upload_mimes', [$this, 'allow_html_mime']);

    // Render: replace page content with uploaded HTML (keeps theme header/footer)
    add_filter('the_content', [$this, 'maybe_replace_content'], 9999);
  }

  public function add_menu() {
    add_management_page(
      'Static HTML to Page',
      'Static HTML to Page',
      'manage_options',
      'kg-static-html-to-page',
      [$this, 'render_admin_page']
    );
  }

  public function allow_html_mime($mimes) {
    if (current_user_can('manage_options')) {
      $mimes['html'] = 'text/html';
      $mimes['htm']  = 'text/html';
    }
    return $mimes;
  }

  public function render_admin_page() {
    if (!current_user_can('manage_options')) wp_die('Insufficient permissions');

    $pages = get_pages(['post_status' => ['publish','draft','pending','private']]);
    
    // Get pages that already have HTML assigned
    $assigned_pages = get_posts([
      'post_type'      => 'page',
      'posts_per_page' => -1,
      'meta_key'       => self::META_KEY,
      'post_status'    => ['publish','draft','pending','private'],
      'fields'         => 'ids'
    ]);
    $assigned_ids = array_flip($assigned_pages);

    ?>
    <div class="wrap">
      <h1>Static HTML → WordPress Page</h1>
      <p>Upload an <code>.html</code> file and assign it to a page. The page will render the HTML inline (no iframe) inside your theme.</p>
      <p><strong>Note:</strong> If a page already has HTML assigned, uploading a new file will <strong>replace</strong> the existing one.</p>

      <form method="post" enctype="multipart/form-data">
        <?php wp_nonce_field('kg_static_html_upload', 'kg_static_html_nonce'); ?>

        <table class="form-table" role="presentation">
          <tr>
            <th scope="row"><label for="kg_page_id">Select Page</label></th>
            <td>
              <select name="kg_page_id" id="kg_page_id" required>
                <option value="">— Choose a page —</option>
                <?php foreach ($pages as $p): 
                  $has_html = isset($assigned_ids[$p->ID]);
                  $suffix = $has_html ? ' (has HTML - will replace)' : '';
                ?>
                  <option value="<?php echo esc_attr($p->ID); ?>">
                    <?php echo esc_html($p->post_title . ' (ID: '.$p->ID.')' . $suffix); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </td>
          </tr>

          <tr>
            <th scope="row"><label for="kg_html_file">HTML file (.html)</label></th>
            <td><input type="file" id="kg_html_file" name="kg_html_file" accept=".html,.htm" required /></td>
          </tr>

          <tr>
            <th scope="row">Replace or Append</th>
            <td>
              <label><input type="radio" name="kg_mode" value="replace" checked> Replace page content</label><br>
              <label><input type="radio" name="kg_mode" value="append"> Append below page content</label>
            </td>
          </tr>
        </table>

        <p class="submit">
          <button type="submit" class="button button-primary">Upload & Assign</button>
        </p>
      </form>

      <hr>

      <h2>Assigned Pages</h2>
      <table class="widefat">
        <thead>
          <tr><th>Page</th><th>File</th><th>Mode</th><th>Actions</th></tr>
        </thead>
        <tbody>
          <?php
          $assigned = get_posts([
            'post_type'      => 'page',
            'posts_per_page' => -1,
            'meta_key'       => self::META_KEY,
            'post_status'    => ['publish','draft','pending','private']
          ]);
          if ($assigned) {
            foreach ($assigned as $p) {
              $meta = get_post_meta($p->ID, self::META_KEY, true);
              $file = $meta['file'] ?? '';
              $mode = $meta['mode'] ?? 'replace';
              // Display just filename for cleaner UI
              $file_display = $file ? basename($file) : '';
              echo '<tr>';
              echo '<td><a href="'.esc_url(get_edit_post_link($p->ID)).'">'.esc_html($p->post_title).' (ID '.$p->ID.')</a></td>';
              echo '<td>'.esc_html($file_display).'</td>';
              echo '<td>'.esc_html($mode).'</td>';
              echo '<td>
                      <form method="post" style="display:inline;">
                        '.wp_nonce_field('kg_static_html_remove','kg_static_html_remove_nonce', true, false).'
                        <input type="hidden" name="kg_remove_page_id" value="'.esc_attr($p->ID).'" />
                        <button class="button">Remove</button>
                      </form>
                    </td>';
              echo '</tr>';
            }
          } else {
            echo '<tr><td colspan="4">No pages assigned.</td></tr>';
          }
          ?>
        </tbody>
      </table>
    </div>
    <?php
  }

  public function handle_upload() {
    // Remove assignment
    if (isset($_POST['kg_remove_page_id']) && check_admin_referer('kg_static_html_remove','kg_static_html_remove_nonce')) {
      $pid = intval($_POST['kg_remove_page_id']);
      delete_post_meta($pid, self::META_KEY);
      add_action('admin_notices', function () {
        echo '<div class="notice notice-success"><p>Static HTML assignment removed.</p></div>';
      });
      return;
    }

    // New upload
    if (
      !isset($_POST['kg_page_id'], $_FILES['kg_html_file'])
      || !isset($_POST['kg_mode'])
      || !isset($_POST['kg_static_html_nonce'])
    ) return;

    if (!current_user_can('manage_options')) return;
    if (!wp_verify_nonce($_POST['kg_static_html_nonce'], 'kg_static_html_upload')) return;

    $page_id = intval($_POST['kg_page_id']);
    $mode    = $_POST['kg_mode'] === 'append' ? 'append' : 'replace';

    if (empty($page_id)) return;

    $file = $_FILES['kg_html_file'];
    if ($file['error'] !== UPLOAD_ERR_OK) {
      add_action('admin_notices', function () {
        echo '<div class="notice notice-error"><p>Upload failed.</p></div>';
      });
      return;
    }
    
    // Validate file extension
    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($file_ext, ['html', 'htm'])) {
      add_action('admin_notices', function () {
        echo '<div class="notice notice-error"><p>Invalid file type. Only .html and .htm files are allowed.</p></div>';
      });
      return;
    }

    // Put file in /uploads/kg-static/{pageID}.html
    $upload_dir = wp_upload_dir();
    $dir = trailingslashit($upload_dir['basedir']) . 'kg-static';
    if (!wp_mkdir_p($dir)) {
      add_action('admin_notices', function () use ($dir) {
        echo '<div class="notice notice-error"><p>Could not create directory: '.esc_html($dir).'</p></div>';
      });
      return;
    }

    $dest = trailingslashit($dir) . 'page-' . $page_id . '.html';

    // Use WP's file handling
    require_once ABSPATH . 'wp-admin/includes/file.php';
    $overrides = ['test_form' => false, 'mimes' => ['html'=>'text/html','htm'=>'text/html']];
    $handled = wp_handle_upload($file, $overrides);

    if (isset($handled['file'])) {
      // Move to deterministic name
      $moved = false;
      if (@rename($handled['file'], $dest)) {
        $moved = true;
      } else {
        // fallback: copy+unlink
        if (@copy($handled['file'], $dest)) {
          @unlink($handled['file']);
          $moved = true;
        }
      }
      
      if (!$moved) {
        add_action('admin_notices', function () {
          echo '<div class="notice notice-error"><p>Could not move uploaded file to destination.</p></div>';
        });
        return;
      }
      
      // Set file permissions (readable by web server)
      @chmod($dest, 0644);
      
      // Save meta: store full absolute path for reliability
      update_post_meta($page_id, self::META_KEY, [
        'file' => $dest,  // Store absolute path
        'mode' => $mode,
      ]);

      add_action('admin_notices', function () use ($page_id) {
        $url = get_permalink($page_id);
        echo '<div class="notice notice-success"><p>HTML assigned. <a target="_blank" href="'.esc_url($url).'">View page</a></p></div>';
      });
    } else {
      add_action('admin_notices', function () use ($handled) {
        $msg = isset($handled['error']) ? $handled['error'] : 'Unknown error';
        echo '<div class="notice notice-error"><p>Upload error: '.esc_html($msg).'</p></div>';
      });
    }
  }

  public function maybe_replace_content($content) {
    if (!is_page()) return $content;

    $pid  = get_queried_object_id();
    $meta = get_post_meta($pid, self::META_KEY, true);
    if (!$meta || empty($meta['file'])) return $content;

    // Handle both old relative paths and new absolute paths
    $file_path = $meta['file'];
    if (!file_exists($file_path)) {
      // Try legacy relative path reconstruction for backward compatibility
      $file_path = trailingslashit(ABSPATH) . ltrim($meta['file'], '/');
      if (!file_exists($file_path)) return $content;
    }

    // Read HTML file
    $html = file_get_contents($file_path);
    if ($html === false) {
      // Log error for debugging (only if WP_DEBUG is enabled)
      if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('KG Static HTML: Could not read file: ' . $file_path);
      }
      return $content;
    }

    // SECURITY NOTE:
    // We trust admins to upload safe HTML. If you need sanitization, run wp_kses_post() or a custom KSES allowlist.
    $html_output = '<div class="kg-static-html">' . $html . '</div>';

    if (($meta['mode'] ?? 'replace') === 'append') {
      return $content . $html_output;
    }
    return $html_output; // replace
  }
}

new KG_Static_HTML_To_Page();
