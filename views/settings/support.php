<?php
/**
 * Support request screen.
 *
 * @package ThemePaste\SecureAdmin
 */

defined( 'ABSPATH' ) || exit;

use ThemePaste\SecureAdmin\Helpers\Utility;

if ( !current_user_can( 'manage_options' ) ) {
    wp_die( esc_html__( 'You do not have permission to view this page.', 'admin-safety-guard' ) );
}

$current_user = wp_get_current_user();

// State carried into the template.
$sent      = false;
$errors    = [];
$api_error = '';

// Repopulate on failure so nothing typed is ever lost.
$values = [
    'name'    => $current_user->display_name,
    'email'   => $current_user->user_email,
    'phone'   => '',
    'message' => '',
    'consent' => true,
];

$request_method = isset( $_SERVER['REQUEST_METHOD'] ) ? strtoupper( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) ) : '';

if ( 'POST' === $request_method && isset( $_POST['tpsa_support_submit'] ) ) {

    $nonce = isset( $_POST['tpsa_support_form_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['tpsa_support_form_nonce'] ) ) : '';

    if ( !wp_verify_nonce( $nonce, 'tpsa_support_form_nonce' ) ) {
        $errors['general'] = __( 'Your session expired while the form was open. Please send it again.', 'admin-safety-guard' );
    } else {
        $values['name']    = isset( $_POST['tpsa_support_name'] ) ? sanitize_text_field( wp_unslash( $_POST['tpsa_support_name'] ) ) : '';
        $values['email']   = isset( $_POST['tpsa_support_email'] ) ? sanitize_email( wp_unslash( $_POST['tpsa_support_email'] ) ) : '';
        $values['phone']   = isset( $_POST['tpsa_support_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['tpsa_support_phone'] ) ) : '';
        $values['message'] = isset( $_POST['tpsa_support_message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['tpsa_support_message'] ) ) : '';
        $values['consent'] = !empty( $_POST['tpsa_support_consent'] );

        // Field-level validation, so each problem is shown against its own input
        // rather than as one message at the top of the page.
        if ( '' === $values['name'] ) {
            $errors['name'] = __( 'Please tell us your name.', 'admin-safety-guard' );
        }

        if ( '' === $values['email'] ) {
            $errors['email'] = __( 'We need an email address to reply to.', 'admin-safety-guard' );
        } elseif ( !is_email( $values['email'] ) ) {
            $errors['email'] = __( 'That email address does not look right.', 'admin-safety-guard' );
        }

        if ( '' === $values['message'] ) {
            $errors['message'] = __( 'Please describe the problem.', 'admin-safety-guard' );
        } elseif ( strlen( $values['message'] ) < 20 ) {
            $errors['message'] = __( 'A little more detail will help us answer faster — at least 20 characters.', 'admin-safety-guard' );
        }

        if ( empty( $errors ) ) {
            $body = [
                'name'        => $values['name'],
                'email'       => $values['email'],
                'phone'       => $values['phone'],
                'message'     => $values['message'],
                'plugin_name' => 'Admin Safety Guard',
            ];

            // Only attach diagnostics when the box is ticked.
            if ( $values['consent'] ) {
                global $wp_version;

                $body['site_url']       = home_url();
                $body['wp_version']     = $wp_version;
                $body['php_version']    = PHP_VERSION;
                $body['plugin_version'] = TPSA_PLUGIN_VERSION;
                $body['is_multisite']   = is_multisite() ? 'yes' : 'no';
                $body['active_theme']   = wp_get_theme()->get( 'Name' );
            }

            $response = wp_remote_post(
                'https://themepaste.com/wp-json/tpsa-support/v1/ticket',
                [
                    'timeout' => 20,
                    'headers' => ['tpsaapikey' => 'tpsa-support-api'],
                    'body'    => $body,
                ]
            );

            if ( is_wp_error( $response ) ) {
                // Never show a raw transport error to a site owner.
                $api_error = __( 'We could not reach our support server. Your internet connection or a firewall may be blocking it.', 'admin-safety-guard' );
            } else {
                $code = (int) wp_remote_retrieve_response_code( $response );

                if ( 200 === $code || 201 === $code ) {
                    $sent = true;
                } else {
                    $api_error = __( 'Our support server did not accept the request. Please email us directly using the address below.', 'admin-safety-guard' );
                }
            }
        }
    }
}

// Fallback mailto, pre-filled, for when the API is unreachable.
$mailto = 'mailto:support@themepaste.com?subject=' . rawurlencode( 'Admin Safety Guard support' )
    . '&body=' . rawurlencode( $values['message'] );
?>

<div class="wrap">
    <h1 class="screen-reader-text"><?php esc_html_e( 'Support', 'admin-safety-guard' ); ?></h1>

    <div class="tp-secure-admin_wrapper">
        <?php echo Utility::get_template( 'settings/parts/topbar.php' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

        <div class="tpsa-support">

            <?php if ( $sent ) : ?>

                <!-- Success replaces the form entirely: there is nothing left to do here. -->
                <div class="tpsa-support__done" role="status">
                    <div class="tpsa-support__doneIcon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 6L9 17l-5-5" />
                        </svg>
                    </div>

                    <h2><?php esc_html_e( 'Your message is on its way', 'admin-safety-guard' ); ?></h2>

                    <p class="tpsa-support__doneLead">
                        <?php
                        printf(
                            /* translators: %s: the email address the reply will go to. */
                            esc_html__( 'We usually reply within one business day. Look out for an email at %s — check your spam folder if it does not arrive.', 'admin-safety-guard' ),
                            '<strong>' . esc_html( $values['email'] ) . '</strong>'
                        );
                        ?>
                    </p>

                    <div class="tpsa-support__doneActions">
                        <a class="tpsa-support__btn"
                            href="<?php echo esc_url( add_query_arg( ['page' => 'tp-admin-safety-guard', 'tab' => 'analytics', 'tpsa-setting' => 'analytics'], admin_url( 'admin.php' ) ) ); ?>">
                            <?php esc_html_e( 'Back to dashboard', 'admin-safety-guard' ); ?>
                        </a>
                        <a class="tpsa-support__link"
                            href="<?php echo esc_url( admin_url( 'admin.php?page=asg-support' ) ); ?>">
                            <?php esc_html_e( 'Send another message', 'admin-safety-guard' ); ?>
                        </a>
                    </div>
                </div>

            <?php else : ?>

                <div class="tpsa-support__grid">

                    <!-- Form -->
                    <div class="tpsa-support__main">
                        <h2 class="tpsa-support__title"><?php esc_html_e( 'Contact support', 'admin-safety-guard' ); ?></h2>
                        <p class="tpsa-support__intro">
                            <?php esc_html_e( 'Tell us what is happening and we will help. The more detail you give, the faster we can answer.', 'admin-safety-guard' ); ?>
                        </p>

                        <?php if ( $api_error ) : ?>
                            <div class="tpsa-support__alert is-error" role="alert">
                                <strong><?php esc_html_e( 'We could not send your message.', 'admin-safety-guard' ); ?></strong>
                                <span><?php echo esc_html( $api_error ); ?></span>
                                <a href="<?php echo esc_url( $mailto ); ?>">
                                    <?php esc_html_e( 'Email support@themepaste.com instead', 'admin-safety-guard' ); ?>
                                </a>
                            </div>
                        <?php elseif ( !empty( $errors['general'] ) ) : ?>
                            <div class="tpsa-support__alert is-error" role="alert">
                                <?php echo esc_html( $errors['general'] ); ?>
                            </div>
                        <?php elseif ( !empty( $errors ) ) : ?>
                            <div class="tpsa-support__alert is-error" role="alert">
                                <?php esc_html_e( 'Please check the highlighted fields below.', 'admin-safety-guard' ); ?>
                            </div>
                        <?php endif; ?>

                        <form method="post" id="tpsa-support-form" novalidate>
                            <?php wp_nonce_field( 'tpsa_support_form_nonce', 'tpsa_support_form_nonce' ); ?>
                            <input type="hidden" name="tpsa_support_submit" value="1">

                            <div class="tpsa-support__field<?php echo isset( $errors['name'] ) ? ' has-error' : ''; ?>">
                                <label for="tpsa_support_name">
                                    <?php esc_html_e( 'Your name', 'admin-safety-guard' ); ?>
                                    <span class="tpsa-support__req" aria-hidden="true">*</span>
                                </label>
                                <input type="text" id="tpsa_support_name" name="tpsa_support_name" required
                                    value="<?php echo esc_attr( $values['name'] ); ?>"
                                    <?php echo isset( $errors['name'] ) ? 'aria-invalid="true"' : ''; ?>>
                                <?php if ( isset( $errors['name'] ) ) : ?>
                                    <p class="tpsa-support__err"><?php echo esc_html( $errors['name'] ); ?></p>
                                <?php endif; ?>
                            </div>

                            <div class="tpsa-support__field<?php echo isset( $errors['email'] ) ? ' has-error' : ''; ?>">
                                <label for="tpsa_support_email">
                                    <?php esc_html_e( 'Email address', 'admin-safety-guard' ); ?>
                                    <span class="tpsa-support__req" aria-hidden="true">*</span>
                                </label>
                                <input type="email" id="tpsa_support_email" name="tpsa_support_email" required
                                    value="<?php echo esc_attr( $values['email'] ); ?>"
                                    <?php echo isset( $errors['email'] ) ? 'aria-invalid="true"' : ''; ?>>
                                <p class="tpsa-support__hint"><?php esc_html_e( 'This is where our reply will go.', 'admin-safety-guard' ); ?></p>
                                <?php if ( isset( $errors['email'] ) ) : ?>
                                    <p class="tpsa-support__err"><?php echo esc_html( $errors['email'] ); ?></p>
                                <?php endif; ?>
                            </div>

                            <div class="tpsa-support__field">
                                <label for="tpsa_support_phone">
                                    <?php esc_html_e( 'Phone number', 'admin-safety-guard' ); ?>
                                    <span class="tpsa-support__opt"><?php esc_html_e( 'optional', 'admin-safety-guard' ); ?></span>
                                </label>
                                <input type="tel" id="tpsa_support_phone" name="tpsa_support_phone"
                                    placeholder="+8801XXXXXXXXX"
                                    value="<?php echo esc_attr( $values['phone'] ); ?>">
                            </div>

                            <div class="tpsa-support__field<?php echo isset( $errors['message'] ) ? ' has-error' : ''; ?>">
                                <label for="tpsa_support_message">
                                    <?php esc_html_e( 'How can we help?', 'admin-safety-guard' ); ?>
                                    <span class="tpsa-support__req" aria-hidden="true">*</span>
                                </label>
                                <textarea id="tpsa_support_message" name="tpsa_support_message" rows="7" required
                                    placeholder="<?php esc_attr_e( 'What were you doing, what did you expect, and what happened instead?', 'admin-safety-guard' ); ?>"
                                    <?php echo isset( $errors['message'] ) ? 'aria-invalid="true"' : ''; ?>><?php echo esc_textarea( $values['message'] ); ?></textarea>
                                <p class="tpsa-support__hint" id="tpsa-support-count"></p>
                                <?php if ( isset( $errors['message'] ) ) : ?>
                                    <p class="tpsa-support__err"><?php echo esc_html( $errors['message'] ); ?></p>
                                <?php endif; ?>
                            </div>

                            <div class="tpsa-support__consent">
                                <label>
                                    <input type="checkbox" name="tpsa_support_consent" value="1"
                                        <?php checked( $values['consent'] ); ?>>
                                    <span>
                                        <?php esc_html_e( 'Include my site details to help diagnose the problem', 'admin-safety-guard' ); ?>
                                    </span>
                                </label>
                                <details>
                                    <summary><?php esc_html_e( 'What gets sent?', 'admin-safety-guard' ); ?></summary>
                                    <ul>
                                        <li><?php echo esc_html( home_url() ); ?></li>
                                        <li><?php printf( 'WordPress %s', esc_html( get_bloginfo( 'version' ) ) ); ?></li>
                                        <li><?php printf( 'PHP %s', esc_html( PHP_VERSION ) ); ?></li>
                                        <li><?php printf( 'Admin Safety Guard %s', esc_html( TPSA_PLUGIN_VERSION ) ); ?></li>
                                        <li><?php echo esc_html( wp_get_theme()->get( 'Name' ) ); ?></li>
                                    </ul>
                                    <p><?php esc_html_e( 'No passwords, keys or user data are ever included.', 'admin-safety-guard' ); ?></p>
                                </details>
                            </div>

                            <div class="tpsa-support__actions">
                                <button type="submit" class="tpsa-support__btn" id="tpsa-support-send">
                                    <span class="tpsa-support__btnText"><?php esc_html_e( 'Send message', 'admin-safety-guard' ); ?></span>
                                    <span class="tpsa-support__spinner" aria-hidden="true"></span>
                                </button>
                                <span class="tpsa-support__reply"><?php esc_html_e( 'Typical reply: within 1 business day', 'admin-safety-guard' ); ?></span>
                            </div>
                        </form>
                    </div>

                    <!-- Self-serve options, so the form is not the only path. -->
                    <aside class="tpsa-support__aside">
                        <div class="tpsa-support__card">
                            <h3><?php esc_html_e( 'Faster than waiting', 'admin-safety-guard' ); ?></h3>
                            <ul class="tpsa-support__links">
                                <li>
                                    <a href="https://themepaste.com/documentation/admin-safety-guard/" target="_blank" rel="noopener noreferrer">
                                        <?php esc_html_e( 'Read the documentation', 'admin-safety-guard' ); ?>
                                    </a>
                                </li>
                                <li>
                                    <a href="https://wordpress.org/support/plugin/admin-safety-guard/" target="_blank" rel="noopener noreferrer">
                                        <?php esc_html_e( 'Community support forum', 'admin-safety-guard' ); ?>
                                    </a>
                                </li>
                                <li>
                                    <a href="<?php echo esc_url( $mailto ); ?>">
                                        <?php esc_html_e( 'Email us directly', 'admin-safety-guard' ); ?>
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <div class="tpsa-support__card is-tip">
                            <h3><?php esc_html_e( 'Locked out of your site?', 'admin-safety-guard' ); ?></h3>
                            <p>
                                <?php esc_html_e( 'If a security setting has locked you out, deactivate the plugin over FTP by renaming its folder in wp-content/plugins. Your settings are kept.', 'admin-safety-guard' ); ?>
                            </p>
                        </div>
                    </aside>

                </div>

            <?php endif; ?>
        </div>
    </div>
</div>
