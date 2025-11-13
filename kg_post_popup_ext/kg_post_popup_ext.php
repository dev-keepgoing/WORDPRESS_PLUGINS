<?php
/**
 * Plugin Name: KG Post Popup Extension
 * Description: Shortcode to render a dynamic Popup Maker highlight from a Portfolio post (uses native custom fields).
 * Author: KEEP GOING Solutions
 * Version: 1.0.0
 */

if (!defined('ABSPATH')) exit;

class KG_Highlight_Popup {
  public static function init() {
    add_shortcode('kg_highlight_popup', [__CLASS__, 'shortcode']);
    add_action('wp_enqueue_scripts', [__CLASS__, 'enqueue_styles']);
  }

  /** --------  PUBLIC STYLES  -------- */
  public static function enqueue_styles() {
    // Single-column layout: image, title, excerpt, CTA
    $css = "
      .kg-popup{ 
        max-width:50vw; 
        margin:0 auto; 
        text-align:center; 
      }
      .kg-popup__title{ 
        margin:0 0 16px; 
        font-size:1.5rem; 
        line-height:1.2; 
      }
      .kg-popup__image{ 
        max-width:min(90%, 500px); 
        width:auto; 
        height:auto; 
        border-radius:12px; 
        display:block; 
        margin:0 auto 16px; 
        object-fit:cover; 
      }
      .kg-popup__excerpt{ 
        margin:0 0 20px; 
        color:#444; 
        line-height:1.5; 
        text-align:center; 
      }
      .kg-popup__cta{ 
        display:inline-block; 
        padding:16px 36px; 
        font-size:20px; 
        letter-spacing:.5px; 
        font-weight:700; 
        color:#ffffff; 
        background:linear-gradient(180deg, #ff523d 0%, #e12e1e 100%); 
        border:none; 
        border-radius:0; 
        cursor:pointer; 
        text-transform:uppercase; 
        text-decoration:none; 
        transition:opacity 0.2s; 
      }
      .kg-popup__cta:hover{ 
        filter:brightness(1.05); 
      }
      .kg-popup__cta:active{ 
        transform:translateY(1px); 
      }
      @media (max-width:768px){
        .kg-popup{ 
          max-width:90vw; 
        }
        .kg-popup__image{ 
          max-width:min(85%, 400px); 
          width:auto; 
        }
        .kg-popup__excerpt{ 
          text-align:center; 
        }
        .kg-popup__cta{ 
          width:100%; 
        }
      }
    ";
    wp_register_style('kg-hp-inline', false);
    wp_enqueue_style('kg-hp-inline');
    wp_add_inline_style('kg-hp-inline', $css);
  }

  /** --------  SHORTCODE  -------- */
  public static function shortcode($atts) {
    $a = shortcode_atts([
      'id'        => '',                // override with a specific post ID
      'slug'      => '',                // override with a slug
      'title'     => '',                // direct title override
      'excerpt'   => '',                // direct excerpt override
      'cta'       => '',                // direct CTA button text override
      // Use prefixed keys to avoid collisions with Popup Maker internals
      'title_key'   => 'kg_hp_title',
      'image_key'   => 'kg_hp_image',
      'excerpt_key' => 'kg_hp_excerpt',
      'link_key'    => 'kg_hp_link',
      'cta_key'     => 'kg_hp_cta',
      'cta_fallback' => 'Read Article',
    ], $atts, 'kg_highlight_popup');

    // Resolve post - must provide id or slug
    $post = null;
    if (!empty($a['id']) && is_numeric($a['id'])) {
      $post = get_post((int)$a['id']);
    } elseif (!empty($a['slug'])) {
      $post = get_page_by_path(sanitize_title($a['slug']), OBJECT, get_post_types(['public'=>true]));
    }
    if (!$post) return '';

    $post_id = $post->ID;

    // Read native custom fields
    $title_override   = get_post_meta($post_id, $a['title_key'], true);
    $image_meta       = get_post_meta($post_id, $a['image_key'], true);
    $excerpt_override = get_post_meta($post_id, $a['excerpt_key'], true);
    $link_override    = get_post_meta($post_id, $a['link_key'], true);
    $cta_label        = get_post_meta($post_id, $a['cta_key'], true);

    // Title: shortcode param > meta > post title
    if (!empty($a['title'])) {
      $title = $a['title'];
    } else {
      $title = $title_override ?: get_the_title($post_id);
    }

    $link  = $link_override ?: get_permalink($post_id);

    // Image can be an attachment ID or a URL
    $image_url = '';
    if ($image_meta) {
      if (is_numeric($image_meta)) $image_url = wp_get_attachment_image_url((int)$image_meta, 'large');
      else $image_url = esc_url($image_meta);
    }
    if (!$image_url) $image_url = get_the_post_thumbnail_url($post_id, 'large');

    // Excerpt: shortcode param > meta > post excerpt
    if (!empty($a['excerpt'])) {
      $excerpt = $a['excerpt'];
    } else {
      $excerpt = $excerpt_override ?: get_the_excerpt($post_id);
      if (!$excerpt) {
        $content = get_post_field('post_content', $post_id);
        $excerpt = wp_trim_words(wp_strip_all_tags($content), 24, '…');
      }
    }

    // CTA: shortcode param > meta > fallback
    if (!empty($a['cta'])) {
      $cta_label = $a['cta'];
    } else {
      $cta_label = $cta_label ?: $a['cta_fallback'];
    }

    ob_start(); ?>
      <div class="kg-popup">
        <?php if ($image_url): ?>
          <img class="kg-popup__image" src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($title); ?>">
        <?php endif; ?>
        <h2 class="kg-popup__title"><?php echo esc_html($title); ?></h2>
        <?php if ($excerpt): ?>
          <p class="kg-popup__excerpt"><?php echo esc_html($excerpt); ?></p>
        <?php endif; ?>
        <a class="kg-popup__cta" href="<?php echo esc_url($link); ?>"><?php echo esc_html($cta_label); ?></a>
      </div>
    <?php
    return ob_get_clean();
  }
}

KG_Highlight_Popup::init();
