<?php 
// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

// Get the current settings screen key from arguments
$current_screen = $args['current_screen'];

// Determine the corresponding documentation URL based on the current screen
switch ( $current_screen ) {
    case 'limit-login-attempts':
        $doc_url = '#';
        break;
    case 'login-logs-activity':
        $doc_url = '#';
        break;
    case 'custom-login-url':
        $doc_url = '#';
        break;
    case 'recaptcha':
        $doc_url = '#';
        break;
    case 'two-factor-auth':
        $doc_url = '#';
        break;
    case 'password-protection':
        $doc_url = '#';
        break;
    case 'privacy-hardening':
        $doc_url = '#';
        break;
    case 'admin-bar':
        $doc_url = '#';
        break;
    case 'customize':
        $doc_url = '#';
        break;
    default:
        // Fallback to base documentation if no matching section
        $doc_url = 'https://themepaste.com/documentation/shipping-manager-documentation';
        break;
}

if( $current_screen != 'analytics' ) {
    ?>
        <!-- Button linking to the relevant documentation section -->
        <button class="tpsm-guide-me-button" id="tpsm-guide-me-button">
            <a href="<?php echo esc_url( $doc_url ); ?>" target="_blank" rel="noopener noreferrer">
                <?php esc_html_e( 'Guide Me', 'shipping-manager' ); ?>
            </a>
        </button>
    <?php 
}
?>
