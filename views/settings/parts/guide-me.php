<?php
// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

// Determine the corresponding documentation URL based on the current screen
$doc_url = 'https://themepaste.com/documentation/admin-safety-guard/'; ?>

<!-- Button linking to the relevant documentation section -->
<button class="tpsm-guide-me-button" id="tpsm-guide-me-button">
    <a href="<?php echo esc_url( $doc_url ); ?>" target="_blank" rel="noopener noreferrer">
        <?php esc_html_e( 'Guide Me', 'tp-secure-plugin' ); ?>
    </a>
</button>