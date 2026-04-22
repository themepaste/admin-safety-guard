<?php
/**
 * Social Login — OAuth Credentials Panel
 *
 * This view always renders so the popup overlay can gate access for free/unlicensed users.
 * Credential values and nonce are loaded from the pro plugin when it is active.
 * The AJAX save action (tpsa_save_social_credentials) is registered by the pro plugin.
 *
 * @package ThemePaste\SecureAdmin
 */

defined( 'ABSPATH' ) || exit;

use ThemePaste\AdminSafetyGuard\Pro\Classes\Features\SocialLogin;

// Load credentials and nonce from pro when active; otherwise use empty defaults.
$credentials = [];
$nonce       = '';
if ( class_exists( SocialLogin::class ) ) {
    $social_login = new SocialLogin();
    $credentials  = $social_login->get_credentials();
    $nonce        = $social_login->get_credentials_nonce();
}

$ajax_url = admin_url( 'admin-ajax.php' );

$providers = [
    'google'   => [
        'label'    => 'Google',
        'logo'     => tp_svg_google(),
        'id_label' => __( 'Client ID', 'admin-safety-guard' ),
        'sc_label' => __( 'Client Secret', 'admin-safety-guard' ),
        'guide'    => __( 'Create credentials at Google Cloud Console → APIs & Services → Credentials. Set the authorised redirect URI to the callback URL shown below.', 'admin-safety-guard' ),
        'callback' => admin_url( 'admin-post.php?action=tp_sa_google_oauth_callback' ),
    ],
    'facebook' => [
        'label'    => 'Facebook',
        'logo'     => tp_svg_facebook(),
        'id_label' => __( 'App ID', 'admin-safety-guard' ),
        'sc_label' => __( 'App Secret', 'admin-safety-guard' ),
        'guide'    => __( 'Create an app at Meta for Developers. Under Facebook Login, add the callback URL below as a valid OAuth redirect URI.', 'admin-safety-guard' ),
        'callback' => admin_url( 'admin-post.php?action=tp_sa_facebook_oauth_callback' ),
    ],
    'linkedin' => [
        'label'    => 'LinkedIn',
        'logo'     => tp_svg_linkedin(),
        'id_label' => __( 'Client ID', 'admin-safety-guard' ),
        'sc_label' => __( 'Client Secret', 'admin-safety-guard' ),
        'guide'    => __( 'Create an app at LinkedIn Developers. Enable "Sign In with LinkedIn using OpenID Connect" and add the callback URL below.', 'admin-safety-guard' ),
        'callback' => admin_url( 'admin-post.php?action=tp_sa_linkedin_oauth_callback' ),
    ],
    'twitter'  => [
        'label'    => 'Twitter / X',
        'logo'     => tp_svg_twitter(),
        'id_label' => __( 'Client ID', 'admin-safety-guard' ),
        'sc_label' => __( 'Client Secret', 'admin-safety-guard' ),
        'guide'    => __( 'Create an app at developer.twitter.com. Under User authentication settings, enable OAuth 2.0 and add the callback URL below.', 'admin-safety-guard' ),
        'callback' => admin_url( 'admin-post.php?action=tp_sa_twitter_oauth_callback' ),
    ],
];
?>

<div class="tpsa-setting-wrapper tpsa-social-credentials-wrapper" style="margin-top:24px;">
    <div class="tpsa-general-settings-wrapper">

        <h2><?php esc_html_e( 'OAuth App Credentials', 'admin-safety-guard' ); ?></h2>

        <p style="color:#5c5c7b;margin:0 0 20px;">
            <?php esc_html_e( 'Enter the Client ID and Secret for each provider you want to use. Credentials are stored separately from the main settings and are never shown in logs.', 'admin-safety-guard' ); ?>
        </p>

        <div class="tpsa-social-provider-cards" style="display:grid;gap:16px;">

            <?php foreach ( $providers as $slug => $provider ) :
                $saved_id     = esc_attr( $credentials[ $slug ]['client_id'] ?? '' );
                $saved_secret = esc_attr( $credentials[ $slug ]['client_secret'] ?? '' );
                $has_creds    = ! empty( $saved_id ) && ! empty( $saved_secret );
            ?>

            <div class="tpsa-provider-card"
                 style="background:#fff;border:1px solid #e2d8fb;border-radius:10px;padding:20px 24px;"
                 data-provider="<?php echo esc_attr( $slug ); ?>">

                <!-- Header: logo + name + status badge -->
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
                    <div style="display:flex;align-items:center;gap:10px;">
                        <span style="display:inline-flex;width:28px;height:28px;flex-shrink:0;">
                            <?php echo wp_kses_post( $provider['logo'] ); ?>
                        </span>
                        <strong style="font-size:15px;color:#1d2327;">
                            <?php echo esc_html( $provider['label'] ); ?>
                        </strong>
                    </div>
                    <span class="tpsa-cred-status"
                          style="font-size:12px;padding:3px 10px;border-radius:20px;font-weight:600;
                                 background:<?php echo $has_creds ? '#e8f5e9' : '#fce8e8'; ?>;
                                 color:<?php echo $has_creds ? '#2e7d32' : '#c62828'; ?>;">
                        <?php echo $has_creds
                            ? esc_html__( 'Configured', 'admin-safety-guard' )
                            : esc_html__( 'Not configured', 'admin-safety-guard' ); ?>
                    </span>
                </div>

                <!-- Guide text -->
                <p style="font-size:12px;color:#6b7280;background:#f6f3ff;border-left:3px solid #814bfe;
                           padding:8px 12px;border-radius:0 6px 6px 0;margin:0 0 16px;">
                    <?php echo esc_html( $provider['guide'] ); ?>
                </p>

                <!-- Authorised Redirect URI (read-only) -->
                <div class="tp-field" style="margin-bottom:14px;">
                    <div class="tp-field-label">
                        <label style="font-size:12px;color:#6b7280;">
                            <?php esc_html_e( 'Authorised Redirect URI', 'admin-safety-guard' ); ?>
                        </label>
                    </div>
                    <div class="tp-field-input">
                        <div style="display:flex;gap:8px;align-items:center;">
                            <input type="text" readonly
                                   value="<?php echo esc_attr( $provider['callback'] ); ?>"
                                   style="flex:1;background:#f1f5f9;border:1px solid #ddd;padding:7px 10px;
                                          border-radius:6px;font-size:12px;color:#555;"
                                   onclick="this.select();" />
                            <button type="button"
                                    class="tpsa-copy-uri"
                                    data-uri="<?php echo esc_attr( $provider['callback'] ); ?>"
                                    style="padding:7px 14px;background:#f1eef9;border:1px solid #814bfe;color:#814bfe;
                                           border-radius:6px;cursor:pointer;font-size:12px;white-space:nowrap;">
                                <?php esc_html_e( 'Copy', 'admin-safety-guard' ); ?>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Client ID -->
                <div class="tp-field" style="margin-bottom:14px;">
                    <div class="tp-field-label">
                        <label for="tpsa_cred_<?php echo esc_attr( $slug ); ?>_client_id">
                            <?php echo esc_html( $provider['id_label'] ); ?>
                        </label>
                    </div>
                    <div class="tp-field-input">
                        <input type="text"
                               id="tpsa_cred_<?php echo esc_attr( $slug ); ?>_client_id"
                               name="<?php echo esc_attr( $slug ); ?>_client_id"
                               value="<?php echo $saved_id; ?>"
                               placeholder="<?php echo esc_attr( $provider['id_label'] ); ?>"
                               autocomplete="off"
                               style="width:100%;box-sizing:border-box;" />
                    </div>
                </div>

                <!-- Client Secret -->
                <div class="tp-field" style="margin-bottom:4px;">
                    <div class="tp-field-label">
                        <label for="tpsa_cred_<?php echo esc_attr( $slug ); ?>_client_secret">
                            <?php echo esc_html( $provider['sc_label'] ); ?>
                        </label>
                    </div>
                    <div class="tp-field-input">
                        <div style="display:flex;gap:8px;align-items:center;">
                            <input type="password"
                                   id="tpsa_cred_<?php echo esc_attr( $slug ); ?>_client_secret"
                                   name="<?php echo esc_attr( $slug ); ?>_client_secret"
                                   value="<?php echo $saved_secret; ?>"
                                   placeholder="<?php echo esc_attr( $provider['sc_label'] ); ?>"
                                   autocomplete="new-password"
                                   style="flex:1;" />
                            <button type="button"
                                    class="tpsa-toggle-secret"
                                    data-target="tpsa_cred_<?php echo esc_attr( $slug ); ?>_client_secret"
                                    style="padding:7px 14px;background:#f1eef9;border:1px solid #814bfe;color:#814bfe;
                                           border-radius:6px;cursor:pointer;font-size:12px;white-space:nowrap;">
                                <?php esc_html_e( 'Show', 'admin-safety-guard' ); ?>
                            </button>
                        </div>
                    </div>
                </div>

            </div><!-- /.tpsa-provider-card -->

            <?php endforeach; ?>

        </div><!-- /.tpsa-social-provider-cards -->

        <div class="tpsa-save-button" style="margin-top:24px;">
            <button type="button" id="tpsa-save-credentials"
                    style="background:#814bfe;color:#fff;border:none;padding:10px 24px;border-radius:6px;
                           font-size:14px;font-weight:600;cursor:pointer;">
                <?php esc_html_e( 'Save Credentials', 'admin-safety-guard' ); ?>
            </button>
            <span id="tpsa-cred-feedback"
                  style="display:none;margin-left:14px;font-size:13px;font-weight:600;"></span>
        </div>

    </div>
</div>

<script>
(function () {
    'use strict';

    // Copy URI buttons
    document.querySelectorAll('.tpsa-copy-uri').forEach(function (btn) {
        btn.addEventListener('click', function () {
            navigator.clipboard.writeText(btn.dataset.uri).then(function () {
                var original = btn.textContent;
                btn.textContent = <?php echo wp_json_encode( __( 'Copied!', 'admin-safety-guard' ) ); ?>;
                setTimeout(function () { btn.textContent = original; }, 2000);
            });
        });
    });

    // Toggle secret visibility
    document.querySelectorAll('.tpsa-toggle-secret').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var input = document.getElementById(btn.dataset.target);
            if (!input) return;
            var isPassword = input.type === 'password';
            input.type     = isPassword ? 'text' : 'password';
            btn.textContent = isPassword
                ? <?php echo wp_json_encode( __( 'Hide', 'admin-safety-guard' ) ); ?>
                : <?php echo wp_json_encode( __( 'Show', 'admin-safety-guard' ) ); ?>;
        });
    });

    // Save credentials via AJAX (handler registered by pro plugin)
    var saveBtn  = document.getElementById('tpsa-save-credentials');
    var feedback = document.getElementById('tpsa-cred-feedback');

    if (!saveBtn) return;

    saveBtn.addEventListener('click', function () {
        saveBtn.disabled    = true;
        saveBtn.textContent = <?php echo wp_json_encode( __( 'Saving…', 'admin-safety-guard' ) ); ?>;
        feedback.style.display = 'none';

        var formData = new FormData();
        formData.append('action', 'tpsa_save_social_credentials');
        formData.append('nonce',  <?php echo wp_json_encode( $nonce ); ?>);

        document.querySelectorAll('.tpsa-provider-card').forEach(function (card) {
            var slug = card.dataset.provider;
            ['client_id', 'client_secret'].forEach(function (field) {
                var input = card.querySelector('[name="' + slug + '_' + field + '"]');
                if (input) formData.append(slug + '_' + field, input.value);
            });
        });

        fetch(<?php echo wp_json_encode( $ajax_url ); ?>, {
            method:      'POST',
            credentials: 'same-origin',
            body:        formData,
        })
        .then(function (r) { return r.json(); })
        .then(function (resp) {
            if (resp.success) {
                feedback.style.color = '#2e7d32';
                feedback.textContent = resp.data.message;

                // Refresh status badges
                document.querySelectorAll('.tpsa-provider-card').forEach(function (card) {
                    var slug  = card.dataset.provider;
                    var idVal = (card.querySelector('[name="' + slug + '_client_id"]')?.value ?? '').trim();
                    var scVal = (card.querySelector('[name="' + slug + '_client_secret"]')?.value ?? '').trim();
                    var badge = card.querySelector('.tpsa-cred-status');
                    if (!badge) return;
                    var ok = idVal && scVal;
                    badge.textContent      = ok
                        ? <?php echo wp_json_encode( __( 'Configured', 'admin-safety-guard' ) ); ?>
                        : <?php echo wp_json_encode( __( 'Not configured', 'admin-safety-guard' ) ); ?>;
                    badge.style.background = ok ? '#e8f5e9' : '#fce8e8';
                    badge.style.color      = ok ? '#2e7d32' : '#c62828';
                });
            } else {
                feedback.style.color = '#c62828';
                feedback.textContent = resp.data?.message
                    ?? <?php echo wp_json_encode( __( 'Save failed.', 'admin-safety-guard' ) ); ?>;
            }
        })
        .catch(function () {
            feedback.style.color = '#c62828';
            feedback.textContent = <?php echo wp_json_encode( __( 'Network error — please try again.', 'admin-safety-guard' ) ); ?>;
        })
        .finally(function () {
            feedback.style.display = 'inline';
            saveBtn.disabled       = false;
            saveBtn.textContent    = <?php echo wp_json_encode( __( 'Save Credentials', 'admin-safety-guard' ) ); ?>;
        });
    });
}());
</script>
