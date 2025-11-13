<?php
/**
 * Plugin Name: KG Video Preloader
 * Description: Use an MP4 video as a site preloader overlay. Admin can upload/select the video and control behavior.
 * Version:     1.0.0
 * Author:      KEEP GOING Solutions
 */

if (!defined('ABSPATH')) exit;

class KG_Video_Preloader {
  const OPT_KEY = 'kgvp_options';

  public function __construct() {
    // Admin
    add_action('admin_menu', [$this, 'add_menu']);
    add_action('admin_init', [$this, 'register_settings']);
    add_action('admin_enqueue_scripts', [$this, 'admin_scripts']);

    // Front
    add_action('wp_body_open', [$this, 'render_preloader']);
    add_action('wp_footer', [$this, 'render_preloader_fallback']); // in case theme lacks wp_body_open
  }

  /* ---------------- Admin UI ---------------- */

  public function add_menu() {
    add_options_page('Video Preloader', 'Video Preloader', 'manage_options', 'kg-video-preloader', [$this, 'settings_page']);
  }

  public function register_settings() {
    register_setting(self::OPT_KEY, self::OPT_KEY, [
      'type' => 'array',
      'sanitize_callback' => [$this, 'sanitize'],
      'default' => [
        'enabled' => 0,
        'scope' => 'all',            // all | home
        'video_url' => '',
        'poster_url' => '',
        'fit' => 'cover',            // cover | contain
        'wait_mode' => 'load',       // load | ended
        'bg' => '#000000',
        'loop' => 0,
        'show_once' => 0,
        'disable_mobile' => 0,       // disable on mobile devices
      ],
    ]);
  }

  public function admin_scripts($hook) {
    if ($hook !== 'settings_page_kg-video-preloader') return;
    wp_enqueue_media();
    wp_add_inline_script('jquery-core', "
      jQuery(function($){
        function bindUploader(btnId, inputId){
          $('#'+btnId).on('click', function(e){
            e.preventDefault();
            const frame = wp.media({ title: 'Select media', multiple: false });
            frame.on('select', function(){
              const file = frame.state().get('selection').first().toJSON();
              $('#'+inputId).val(file.url);
            });
            frame.open();
          });
        }
        bindUploader('kgvp_pick_video','kgvp_video_url');
        bindUploader('kgvp_pick_poster','kgvp_poster_url');
      });
    ");
  }

  public function settings_page() {
    if (!current_user_can('manage_options')) return;
    $opt = get_option(self::OPT_KEY, []);
    ?>
    <div class="wrap">
      <h1>Video Preloader</h1>
      <form method="post" action="options.php">
        <?php settings_fields(self::OPT_KEY); ?>
        <?php $o = wp_parse_args($opt, [
          'enabled'=>0,'scope'=>'all','video_url'=>'','poster_url'=>'','fit'=>'cover','wait_mode'=>'load','bg'=>'#000000','loop'=>0,'show_once'=>0,'disable_mobile'=>0
        ]); ?>

<table class="form-table" role="presentation">
  <tr>
    <th scope="row">Enable</th>
    <td>
      <label>
        <input type="checkbox" name="<?php echo self::OPT_KEY; ?>[enabled]" value="1" <?php checked($o['enabled'],1); ?>>
        Activate video preloader
      </label>
      <p class="description">Turns the MP4 preloader on or off globally. When disabled, the video overlay will not load on any page.</p>
    </td>
  </tr>

  <tr>
    <th scope="row">Scope</th>
    <td>
      <label><input type="radio" name="<?php echo self::OPT_KEY; ?>[scope]" value="all" <?php checked($o['scope'],'all'); ?>> All pages</label><br>
      <label><input type="radio" name="<?php echo self::OPT_KEY; ?>[scope]" value="home" <?php checked($o['scope'],'home'); ?>> Home page only</label>
      <p class="description">Choose whether the video preloader should appear on every page or only on the home page.</p>
    </td>
  </tr>

  <tr>
    <th scope="row">MP4 Video</th>
    <td>
      <input type="url" id="kgvp_video_url" name="<?php echo self::OPT_KEY; ?>[video_url]" value="<?php echo esc_attr($o['video_url']); ?>" class="regular-text" placeholder="https://.../preloader.mp4">
      <button class="button" id="kgvp_pick_video">Select / Upload</button>
      <p class="description">Upload or select your preloader MP4 video from the Media Library. This video will play before the page becomes visible.</p>
    </td>
  </tr>

  <tr>
    <th scope="row">Poster (optional)</th>
    <td>
      <input type="url" id="kgvp_poster_url" name="<?php echo self::OPT_KEY; ?>[poster_url]" value="<?php echo esc_attr($o['poster_url']); ?>" class="regular-text" placeholder="https://.../poster.jpg">
      <button class="button" id="kgvp_pick_poster">Select / Upload</button>
      <p class="description">A static image displayed before the video starts loading. Ideal for slow connections or mobile browsers that delay autoplay.</p>
    </td>
  </tr>

  <tr>
    <th scope="row">Fit</th>
    <td>
      <select name="<?php echo self::OPT_KEY; ?>[fit]">
        <option value="cover" <?php selected($o['fit'],'cover'); ?>>Fullscreen (cover)</option>
        <option value="contain" <?php selected($o['fit'],'contain'); ?>>Contain (no crop)</option>
      </select>
      <p class="description">Choose how the video fills the screen. “Cover” crops edges to fill the viewport, while “Contain” keeps the full frame visible with possible black bars.</p>
    </td>
  </tr>

  <tr>
    <th scope="row">Fade-out trigger</th>
    <td>
      <select name="<?php echo self::OPT_KEY; ?>[wait_mode]">
        <option value="load" <?php selected($o['wait_mode'],'load'); ?>>When page finishes loading</option>
        <option value="ended" <?php selected($o['wait_mode'],'ended'); ?>>When video ends</option>
      </select>
      <p class="description">Determines when the preloader fades out: either when the web page fully loads, or when the video finishes playing.</p>
    </td>
  </tr>

  <tr>
    <th scope="row">Background color</th>
    <td>
      <input type="text" name="<?php echo self::OPT_KEY; ?>[bg]" value="<?php echo esc_attr($o['bg']); ?>" class="regular-text" placeholder="#000000">
      <p class="description">The background color behind your video (visible before playback or if the video is transparent).</p>
    </td>
  </tr>

  <tr>
    <th scope="row">Loop video</th>
    <td>
      <label><input type="checkbox" name="<?php echo self::OPT_KEY; ?>[loop]" value="1" <?php checked($o['loop'],1); ?>> Loop while waiting</label>
      <p class="description">When enabled, the preloader video will repeat continuously until the fade-out condition is met.</p>
    </td>
  </tr>

  <tr>
    <th scope="row">Show once per session</th>
    <td>
      <label><input type="checkbox" name="<?php echo self::OPT_KEY; ?>[show_once]" value="1" <?php checked($o['show_once'],1); ?>> Don't show again until browser/tab is closed</label>
      <p class="description">Prevents the preloader from showing again after the first view during the same browser session.</p>
    </td>
  </tr>

  <tr>
    <th scope="row">Disable on mobile</th>
    <td>
      <label><input type="checkbox" name="<?php echo self::OPT_KEY; ?>[disable_mobile]" value="1" <?php checked($o['disable_mobile'],1); ?>> Hide preloader on mobile devices</label>
      <p class="description">When enabled, the video preloader will not display on mobile devices (phones and tablets). Useful for performance or autoplay restrictions.</p>
    </td>
  </tr>
</table>


        <?php submit_button(); ?>
      </form>
    </div>
    <?php
  }

  public function sanitize($input) {
    $out = [];
    $out['enabled']   = empty($input['enabled']) ? 0 : 1;
    $out['scope']     = in_array(($input['scope'] ?? 'all'), ['all','home'], true) ? $input['scope'] : 'all';
    $out['video_url'] = esc_url_raw($input['video_url'] ?? '');
    $out['poster_url']= esc_url_raw($input['poster_url'] ?? '');
    $out['fit']       = in_array(($input['fit'] ?? 'cover'), ['cover','contain'], true) ? $input['fit'] : 'cover';
    $out['wait_mode'] = in_array(($input['wait_mode'] ?? 'load'), ['load','ended'], true) ? $input['wait_mode'] : 'load';
    $out['bg']        = sanitize_text_field($input['bg'] ?? '#000000');
    $out['loop']      = empty($input['loop']) ? 0 : 1;
    $out['show_once'] = empty($input['show_once']) ? 0 : 1;
    $out['disable_mobile'] = empty($input['disable_mobile']) ? 0 : 1;
    return $out;
  }

  /* ---------------- Frontend ---------------- */

  private function allowed_to_show($o) {
    if (empty($o['enabled']) || empty($o['video_url'])) return false;
    if ($o['scope'] === 'home' && !is_front_page() && !is_home()) return false;
    return true;
  }

  public function render_preloader() {
    $o = get_option(self::OPT_KEY, []);
    if (!$this->allowed_to_show($o)) return;

    // Output once (body open) and mark so footer fallback doesn't duplicate
    if (did_action('wp_body_open')) {
      $this->output_markup($o);
      add_filter('kgvp_done', '__return_true');
    }
  }

  public function render_preloader_fallback() {
    if (apply_filters('kgvp_done', false)) return;
    $o = get_option(self::OPT_KEY, []);
    if (!$this->allowed_to_show($o)) return;
    $this->output_markup($o);
  }

  private function output_markup($o) {
    $bg   = esc_attr($o['bg'] ?? '#000000');
    $fit  = ($o['fit'] ?? 'cover') === 'contain' ? 'contain' : 'cover';
    $loop = !empty($o['loop']) ? 'loop' : '';
    $poster = !empty($o['poster_url']) ? ' poster="'.esc_url($o['poster_url']).'"' : '';
    $wait_mode = $o['wait_mode'] ?? 'load';
    $show_once = !empty($o['show_once']) ? '1' : '0';
    $disable_mobile = !empty($o['disable_mobile']) ? '1' : '0';
    $video_url = esc_url($o['video_url']);

    ?>
    <div id="kgvp-preloader" aria-hidden="true">
      <video id="kgvp-video" autoplay muted playsinline <?php echo $loop . $poster; ?>>
        <source src="<?php echo $video_url; ?>" type="video/mp4">
      </video>
    </div>
    <style>
      #kgvp-preloader{
        position:fixed; inset:0; z-index:999999;
        display:flex; align-items:center; justify-content:center;
        background: <?php echo $bg; ?>;
        transition: opacity .6s ease;
      }
      #kgvp-preloader.hidden{ opacity:0; pointer-events:none; }
      #kgvp-video{ width:100vw; height:100vh; object-fit: <?php echo $fit; ?>; }
      html,body{ height:100%; }
      body.kgvp-no-scroll{ overflow:hidden; }
    </style>
    <script>
      (function(){
        try {
          // Mobile device detection
          function isMobileDevice() {
            // Check user agent for mobile devices
            var ua = navigator.userAgent || navigator.vendor || window.opera;
            var mobileRegex = /android|webos|iphone|ipad|ipod|blackberry|iemobile|opera mini/i;
            
            // Also check screen width (mobile typically < 768px)
            var isSmallScreen = window.innerWidth <= 768;
            
            // Check for touch support
            var hasTouch = 'ontouchstart' in window || navigator.maxTouchPoints > 0;
            
            return mobileRegex.test(ua.toLowerCase()) || (isSmallScreen && hasTouch);
          }
          
          var disableMobile = <?php echo $disable_mobile; ?>;
          if (disableMobile && isMobileDevice()) {
            // Remove preloader on mobile if disabled
            var el = document.getElementById('kgvp-preloader');
            if (el) {
              el.remove();
              document.body.classList.remove('kgvp-no-scroll');
            }
            return;
          }
          
          var showOnce = <?php echo $show_once; ?>;
          if (showOnce && window.sessionStorage && sessionStorage.getItem('kgvp_seen')) {
            // Skip showing
            var el = document.getElementById('kgvp-preloader'); if (el) el.remove();
            return;
          }
          document.body.classList.add('kgvp-no-scroll');

          function hidePreloader(){
            var el = document.getElementById('kgvp-preloader');
            if(!el) return;
            el.classList.add('hidden');
            setTimeout(function(){
              el.remove();
              document.body.classList.remove('kgvp-no-scroll');
              try { if (showOnce) sessionStorage.setItem('kgvp_seen','1'); } catch(e){}
            }, 650);
          }

          var mode = "<?php echo esc_js($wait_mode); ?>";
          var vid  = document.getElementById('kgvp-video');

          if (mode === 'ended') {
            // fade when video ends, with safety timeout
            var safety = setTimeout(hidePreloader, 12000);
            if (vid) {
              vid.addEventListener('ended', function(){ clearTimeout(safety); hidePreloader(); }, {once:true});
              // If cannot play, fallback to load event
              vid.addEventListener('error', function(){ hidePreloader(); }, {once:true});
            } else {
              window.addEventListener('load', hidePreloader, {once:true});
            }
          } else {
            // default: when page loads (faster UX)
            window.addEventListener('load', hidePreloader, {once:true});
            // Safety if load never fires
            setTimeout(hidePreloader, 8000);
          }
        } catch(e){}
      })();
    </script>
    <?php
  }
}

new KG_Video_Preloader();
