<?php
/**
 * Plugin Rating Message Template
 *
 * Displays a message encouraging users to rate the plugin on the WordPress.org repository.
 *
 * @package Shipping_Manager
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

?>

<p class="tpsa-rating-message">
    <?php
/**
 * Output a translatable plugin rating message with:
 * - Plugin name in bold.
 * - 5-star graphic using Unicode.
 * - Link to the WordPress plugin reviews section.
 *
 * The message format is:
 * "If you like [Plugin Name], you can rate us ★★★★★ in plugins repository →"
 */

printf(
    /* translators: 1: Plugin name (bold), 2: Star symbols, 3: Opening <a> tag with URL, 4: Closing </a> tag */
    esc_html__(
        'If you like %1$s you can rate us %2$s %3$sin plugins repository →%4$s',
        'admin-safety-guard'
    ),
    '<strong>' . esc_html__( 'Admin Safety Guard', 'admin-safety-guard' ) . '</strong>',
    '<span class="tpsa-stars" aria-label="5 stars">★★★★★</span>',
    '<strong><a href="' . esc_url( 'https://wordpress.org/plugins/admin-safety-guard/#reviews' ) . '" target="_blank" rel="noopener noreferrer">',
    '</a></strong>'
);
?>
</p>