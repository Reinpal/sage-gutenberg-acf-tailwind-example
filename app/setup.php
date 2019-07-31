<?php

namespace App;

use StoutLogic\AcfBuilder\FieldsBuilder;
use KMDigital\AcfBlockBuilder\Block;
use function Roots\app;
use function Roots\asset;
use function Roots\config;
use function Roots\view;

/**
 * Theme assets
 */
add_action('wp_enqueue_scripts', function () {
    wp_enqueue_script('sage/vendor', asset('scripts/vendor.js')->uri(), ['jquery'], null, true);
    wp_enqueue_script('sage/app', asset('scripts/app.js')->uri(), ['sage/vendor'], null, true);

    wp_add_inline_script('sage/vendor', asset('scripts/manifest.js')->contents(), 'before');

    if (is_single() && comments_open() && get_option('thread_comments')) {
        wp_enqueue_script('comment-reply');
    }

    $styles = ['styles/app.css'];

    foreach ($styles as $stylesheet) {
        if (asset($stylesheet)->exists()) {
            wp_enqueue_style('sage/' . basename($stylesheet, '.css'), asset($stylesheet)->uri(), false, null);
        }
    }
}, 100);

/**
 * Block editor assets
 */
add_action('enqueue_block_editor_assets', function () {
    wp_enqueue_style('sage/editor', asset('styles/editor.css')->uri(), false, null);
});

/**
 * Theme setup
 */
add_action('after_setup_theme', function () {
    /**
     * Enable features from Soil when plugin is activated
     * @link https://roots.io/plugins/soil/
     */
    add_theme_support('soil-clean-up');
    add_theme_support('soil-jquery-cdn');
    add_theme_support('soil-nav-walker');
    add_theme_support('soil-nice-search');
    add_theme_support('soil-relative-urls');

    /**
     * Enable plugins to manage the document title
     * @link https://developer.wordpress.org/reference/functions/add_theme_support/#title-tag
     */
    add_theme_support('title-tag');

    /**
     * Register navigation menus
     * @link https://developer.wordpress.org/reference/functions/register_nav_menus/
     */
    register_nav_menus([
        'primary_navigation' => __('Primary Navigation', 'sage')
    ]);

    /**
     * Enable post thumbnails
     * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
     */
    add_theme_support('post-thumbnails');

    /**
     * Enable HTML5 markup support
     * @link https://developer.wordpress.org/reference/functions/add_theme_support/#html5
     */
    add_theme_support('html5', ['caption', 'comment-form', 'comment-list', 'gallery', 'search-form']);

    /**
     * Enable selective refresh for widgets in customizer
     * @link https://developer.wordpress.org/themes/advanced-topics/customizer-api/#theme-support-in-sidebars
     */
    add_theme_support('customize-selective-refresh-widgets');

    /**
     * Allow full width blocks
     */
    add_theme_support('align-wide');
}, 20);

/**
 * Register sidebars
 */
add_action('widgets_init', function () {
    $config = [
        'before_widget' => '<section class="widget %1$s %2$s">',
        'after_widget' => '</section>',
        'before_title' => '<h3>',
        'after_title' => '</h3>'
    ];

    register_sidebar([
        'name' => __('Primary', 'sage'),
        'id' => 'sidebar-primary'
    ] + $config);

    register_sidebar([
        'name' => __('Footer', 'sage'),
        'id' => 'sidebar-footer'
    ] + $config);
});

/**
 * Register ACF Field Groups and Blocks
 */
add_action('acf/init', function () {
    if (function_exists('acf_add_local_field_group')) {
        collect(glob(app()->resourcePath('fields/{blocks,pages}/*.php'), GLOB_BRACE))->map(function ($field) {
            return require_once($field);
        })->map(function ($field) {
            if ($field instanceof FieldsBuilder || $field instanceof Block) {
                acf_add_local_field_group($field->build());
            }
        });
    }
});
