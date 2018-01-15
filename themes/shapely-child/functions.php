<?php
if($_GET['s']){
	$_GET['post_type']='product';
}
add_action( 'wp_enqueue_scripts', 'my_theme_enqueue_styles' );
function my_theme_enqueue_styles() {
    wp_enqueue_style( 'shapely-style', get_template_directory_uri() . '/style.css' );
}

add_action( 'after_setup_theme', 'shapely_child_woocommerce_gallery_support' );
 
function shapely_child_woocommerce_gallery_support() {
    add_theme_support( 'wc-product-gallery-zoom' );
    add_theme_support( 'wc-product-gallery-lightbox' );
    add_theme_support( 'wc-product-gallery-slider' );
}

function svg_mime_type($mimes) {
  $mimes['svg'] = 'image/svg+xml';
  return $mimes;
}
add_filter('upload_mimes', 'svg_mime_type');