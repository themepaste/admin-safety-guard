<?php
defined( 'ABSPATH' ) || exit;

use ThemePaste\SecureAdmin\Helpers\Utility;

$screen_slug        = $args['current_screen'];
$page_label         = isset( $args['page_label_forsub'] ) && !empty( $args['page_label_forsub'] ) ? $args['page_label_forsub'] : $args['page_label'];
$current_url        = $args['current_url'];
$current_tab_label  = $args['current_tab_label'];
$is_pro             = $args['is_pro'] ?? false;
$is_valid_license_available = is_valid_license_available();

global $wpdb;
$db_prefix   = tp_asg_pro_current_prefix();
$is_good     = tp_asg_pro_is_prefix_good( $db_prefix );
$like        = $wpdb->esc_like( $db_prefix ) . '%';
$table_count = count( $wpdb->get_col( $wpdb->prepare( 'SHOW TABLES LIKE %s', $like ) ) );

$suggestions = ThemePaste\SecureAdmin\Classes\Admin::generate_valid_prefix_suggestions();

$nonce    = wp_create_nonce( 'tpsa-nonce' );
$ajax_url = admin_url( 'admin-ajax.php' );
?>

<div class="tpsa-setting-wrapper">
    <div class="tpsa-general-settings-wrapper">

        <!-- BREADCRUMB -->
        <h2><span><a href="<?php echo esc_url( $current_url ); ?>"><?php echo esc_html( $current_tab_label ); ?></a>
                <?php echo esc_html( ' / ' . $page_label . ' Settings' ); ?>
            </span>
            <div class="tp-feature">
                <button class="tp-help-icon">?</button>
                <div class="tp-tooltip">
                    <p><?php esc_html_e( 'Change the WordPress database table prefix to reduce the risk of SQL injection attacks that target default prefix names.', 'admin-safety-guard' ); ?></p>
                </div>
            </div>
        </h2>

        <!-- CURRENT PREFIX STATUS -->
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;
                    background:#fff;border:1px solid #e2d8fb;border-radius:10px;padding:18px 22px;margin-bottom:20px;">
            <div>
                <p style="margin:0 0 4px;font-size:12px;color:#6b7280;text-transform:uppercase;letter-spacing:.05em;">
                    <?php esc_html_e( 'Current Table Prefix', 'admin-safety-guard' ); ?>
                </p>
                <code style="font-size:18px;font-weight:700;color:#1d2327;background:#f1f5f9;
                             padding:4px 12px;border-radius:6px;border:1px solid #e2e8f0;">
                    <?php echo esc_html( $db_prefix ); ?>
                </code>
            </div>
            <div style="display:flex;align-items:center;gap:16px;">
                <div style="text-align:center;">
                    <p style="margin:0 0 2px;font-size:12px;color:#6b7280;">
                        <?php esc_html_e( 'Tables', 'admin-safety-guard' ); ?>
                    </p>
                    <strong style="font-size:20px;color:#1d2327;"><?php echo esc_html( $table_count ); ?></strong>
                </div>
                <span style="display:inline-flex;align-items:center;gap:6px;padding:6px 14px;border-radius:20px;font-size:13px;font-weight:600;
                             background:<?php echo $is_good ? '#e8f5e9' : '#fce8e8'; ?>;
                             color:<?php echo $is_good ? '#2e7d32' : '#c62828'; ?>;">
                    <?php if ( $is_good ) : ?>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                        <?php esc_html_e( 'Secure', 'admin-safety-guard' ); ?>
                    <?php else : ?>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        <?php esc_html_e( 'Insecure', 'admin-safety-guard' ); ?>
                    <?php endif; ?>
                </span>
            </div>
        </div>

        <?php if ( ! $is_good ) : ?>
        <!-- WHY IT MATTERS -->
        <div style="background:#fffbeb;border:1px solid #fcd34d;border-radius:8px;padding:14px 18px;margin-bottom:20px;">
            <p style="margin:0;font-size:13px;color:#92400e;">
                <strong><?php esc_html_e( 'Why change it?', 'admin-safety-guard' ); ?></strong>
                <?php esc_html_e( ' The default "wp_" prefix is widely known and targeted by automated SQL injection attacks. Changing it to a random value makes it significantly harder for attackers to guess your table names.', 'admin-safety-guard' ); ?>
            </p>
        </div>
        <?php endif; ?>

        <!-- WHAT MAKES A SECURE PREFIX -->
        <div style="background:#f6f3ff;border:1px solid #c4b5fd;border-radius:10px;padding:16px 20px;margin-bottom:20px;">
            <h4 style="margin:0 0 10px;font-size:13px;color:#5b21b6;display:flex;align-items:center;gap:6px;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
                <?php esc_html_e( 'What makes a prefix secure?', 'admin-safety-guard' ); ?>
            </h4>
            <ul style="margin:0;padding:0 0 0 16px;font-size:12px;color:#4c1d95;line-height:1.9;">
                <li><?php esc_html_e( 'Starts with a letter (e.g. x4k9a_)', 'admin-safety-guard' ); ?></li>
                <li><?php esc_html_e( 'Contains at least one number', 'admin-safety-guard' ); ?></li>
                <li><?php esc_html_e( 'Ends with an underscore _', 'admin-safety-guard' ); ?></li>
                <li><?php esc_html_e( 'Only lowercase letters, numbers, and underscores', 'admin-safety-guard' ); ?></li>
                <li><?php esc_html_e( 'At least 4 characters before the underscore', 'admin-safety-guard' ); ?></li>
                <li><?php esc_html_e( 'No common words: wp_, admin_, blog_, test_, secure_', 'admin-safety-guard' ); ?></li>
            </ul>
        </div>

        <!-- SUGGESTED PREFIXES -->
        <div style="background:#fff;border:1px solid #e2d8fb;border-radius:10px;padding:18px 22px;margin-bottom:20px;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
                <h4 style="margin:0;font-size:14px;color:#1d2327;">
                    <?php esc_html_e( 'Suggested Secure Prefixes', 'admin-safety-guard' ); ?>
                </h4>
                <button type="button" id="tpsa-regenerate-suggestions"
                        style="background:none;border:1px solid #814bfe;color:#814bfe;padding:5px 12px;
                               border-radius:6px;cursor:pointer;font-size:12px;font-weight:600;">
                    <?php esc_html_e( '↺ Regenerate', 'admin-safety-guard' ); ?>
                </button>
            </div>
            <p style="margin:0 0 12px;font-size:12px;color:#6b7280;">
                <?php esc_html_e( 'Click any suggestion to use it. 4–6 random characters + underscore, starts with a letter, contains at least one digit.', 'admin-safety-guard' ); ?>
            </p>
            <div id="tpsa-suggestion-pills" style="display:flex;flex-wrap:wrap;gap:8px;">
                <?php foreach ( $suggestions as $s ) : ?>
                <button type="button" class="tpsa-prefix-pill"
                        data-prefix="<?php echo esc_attr( $s ); ?>"
                        style="background:#f6f3ff;border:1px solid #c4b5fd;color:#5b21b6;padding:6px 14px 6px 10px;
                               border-radius:20px;cursor:pointer;font-family:monospace;font-size:13px;font-weight:600;
                               display:inline-flex;align-items:center;gap:6px;transition:background .15s,border-color .15s;">
                    <span style="display:inline-flex;align-items:center;justify-content:center;
                                 width:16px;height:16px;background:#16a34a;border-radius:50%;flex-shrink:0;">
                        <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3.5"><polyline points="20 6 9 17 4 12"/></svg>
                    </span>
                    <?php echo esc_html( $s ); ?>
                </button>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- DANGER ZONE -->
        <div style="background:#fff;border:2px solid #fca5a5;border-radius:10px;padding:20px 22px;">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:4px;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                <h3 style="margin:0;font-size:15px;color:#dc2626;"><?php esc_html_e( 'Change Prefix', 'admin-safety-guard' ); ?></h3>
            </div>
            <p style="font-size:12px;color:#6b7280;margin:0 0 16px;padding-left:28px;">
                <?php esc_html_e( 'This renames all database tables, updates usermeta keys, option names, and rewrites wp-config.php. Make a full database backup before proceeding.', 'admin-safety-guard' ); ?>
            </p>

            <!-- Checklist -->
            <div style="background:#fef2f2;border-radius:8px;padding:12px 16px;margin-bottom:18px;">
                <p style="margin:0 0 8px;font-size:12px;font-weight:600;color:#b91c1c;">
                    <?php esc_html_e( 'Before you continue:', 'admin-safety-guard' ); ?>
                </p>
                <ul style="margin:0;padding:0 0 0 16px;font-size:12px;color:#7f1d1d;line-height:1.8;">
                    <li><?php esc_html_e( 'Back up your entire database.', 'admin-safety-guard' ); ?></li>
                    <li><?php esc_html_e( 'Back up wp-config.php.', 'admin-safety-guard' ); ?></li>
                    <li><?php printf( esc_html__( 'You have %d tables that will be renamed.', 'admin-safety-guard' ), $table_count ); ?></li>
                    <li><?php esc_html_e( 'You may be logged out after the change — log back in with the same credentials.', 'admin-safety-guard' ); ?></li>
                </ul>
            </div>

            <!-- New Prefix Input -->
            <div class="tp-field" style="margin-bottom:14px;">
                <div class="tp-field-label">
                    <label for="tpsa-new-prefix-input"><?php esc_html_e( 'New Prefix', 'admin-safety-guard' ); ?></label>
                </div>
                <div class="tp-field-input">
                    <div style="display:flex;gap:8px;align-items:center;">
                        <input type="text" id="tpsa-new-prefix-input"
                               placeholder="e.g. x4k9a_"
                               autocomplete="off" spellcheck="false"
                               style="flex:1;max-width:260px;" />
                        <span id="tpsa-prefix-format-msg" style="font-size:12px;font-weight:600;"></span>
                    </div>
                    <p class="tp-field-desc"><?php esc_html_e( 'Must end with an underscore, start with a letter, and contain at least one number. Min 4 characters before the underscore.', 'admin-safety-guard' ); ?></p>
                </div>
            </div>

            <!-- Confirmation Input -->
            <div class="tp-field" style="margin-bottom:18px;">
                <div class="tp-field-label">
                    <label for="tpsa-prefix-confirm"><?php esc_html_e( 'Type to confirm', 'admin-safety-guard' ); ?></label>
                </div>
                <div class="tp-field-input">
                    <input type="text" id="tpsa-prefix-confirm"
                           placeholder="<?php esc_attr_e( 'CHANGE PREFIX', 'admin-safety-guard' ); ?>"
                           autocomplete="off"
                           style="max-width:260px;" />
                    <p class="tp-field-desc"><?php esc_html_e( 'Type CHANGE PREFIX (uppercase) to unlock the button.', 'admin-safety-guard' ); ?></p>
                </div>
            </div>

            <!-- Submit -->
            <div class="tpsa-save-button" style="display:flex;align-items:center;gap:14px;">
                <button type="button" id="tpsa-change-prefix-btn" disabled
                        style="background:#dc2626;color:#fff;border:none;padding:10px 24px;border-radius:6px;
                               font-size:14px;font-weight:600;cursor:pointer;opacity:.5;transition:opacity .2s;">
                    <?php esc_html_e( 'Change Prefix', 'admin-safety-guard' ); ?>
                </button>
                <span id="tpsa-prefix-feedback" style="font-size:13px;font-weight:600;display:none;"></span>
            </div>

            <!-- Rename log -->
            <div id="tpsa-rename-log"
                 style="display:none;margin-top:16px;background:#f0fdf4;border:1px solid #86efac;
                        border-radius:8px;padding:14px 16px;">
                <p style="margin:0 0 8px;font-size:12px;font-weight:700;color:#15803d;">
                    <?php esc_html_e( 'Operation Log', 'admin-safety-guard' ); ?>
                </p>
                <ul id="tpsa-log-items" style="margin:0;padding:0 0 0 16px;font-size:12px;color:#166534;line-height:1.8;"></ul>
            </div>
        </div>

    </div>
</div>

<!-- PRO POPUP OVERLAY -->
<?php echo $is_pro && !$is_valid_license_available ? Utility::get_template( 'popup/pro-features-popup.php' ) : ''; ?>

<script>
(function () {
    'use strict';

    var nonce    = <?php echo wp_json_encode( $nonce ); ?>;
    var ajaxUrl  = <?php echo wp_json_encode( $ajax_url ); ?>;
    var suggestions = <?php echo wp_json_encode( $suggestions ); ?>;

    function badgeHtml() {
        return '<span style="display:inline-flex;align-items:center;justify-content:center;' +
            'width:16px;height:16px;background:#16a34a;border-radius:50%;flex-shrink:0;">' +
            '<svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3.5">' +
            '<polyline points="20 6 9 17 4 12"/></svg></span>';
    }

    var newPrefixInput = document.getElementById( 'tpsa-new-prefix-input' );
    var confirmInput   = document.getElementById( 'tpsa-prefix-confirm' );
    var formatMsg      = document.getElementById( 'tpsa-prefix-format-msg' );
    var submitBtn      = document.getElementById( 'tpsa-change-prefix-btn' );
    var feedback       = document.getElementById( 'tpsa-prefix-feedback' );
    var renameLog      = document.getElementById( 'tpsa-rename-log' );
    var logItems       = document.getElementById( 'tpsa-log-items' );

    // ── Suggestion pills: fill input on click ──────────────────────────────
    document.getElementById( 'tpsa-suggestion-pills' ).addEventListener( 'click', function ( e ) {
        var pill = e.target.closest( '.tpsa-prefix-pill' );
        if ( ! pill ) return;
        newPrefixInput.value = pill.dataset.prefix;
        newPrefixInput.dispatchEvent( new Event( 'input' ) );
        newPrefixInput.focus();
    } );

    // ── Regenerate suggestions ─────────────────────────────────────────────
    document.getElementById( 'tpsa-regenerate-suggestions' ).addEventListener( 'click', function () {
        var btn = this;
        btn.disabled = true;
        btn.textContent = <?php echo wp_json_encode( __( 'Generating…', 'admin-safety-guard' ) ); ?>;

        var data = new FormData();
        data.append( 'action', 'tpsa_generate_prefix_suggestions' );
        data.append( 'nonce',  nonce );

        fetch( ajaxUrl, { method: 'POST', credentials: 'same-origin', body: data } )
            .then( function ( r ) { return r.json(); } )
            .then( function ( resp ) {
                if ( resp.success && Array.isArray( resp.data ) ) {
                    var pills = document.querySelectorAll( '.tpsa-prefix-pill' );
                    resp.data.forEach( function ( prefix, i ) {
                        if ( ! pills[i] ) return;
                        // prefix is validated [a-z0-9_]+ — no XSS risk
                        pills[i].dataset.prefix = prefix;
                        pills[i].innerHTML = badgeHtml() + prefix;
                    } );
                }
            } )
            .catch( function () {} )
            .finally( function () {
                btn.disabled    = false;
                btn.textContent = <?php echo wp_json_encode( __( '↺ Regenerate', 'admin-safety-guard' ) ); ?>;
            } );
    } );

    // ── Live prefix format validation ─────────────────────────────────────
    function validatePrefix( value ) {
        if ( ! value ) return null;
        if ( ! /^[a-z]/i.test( value ) )          return <?php echo wp_json_encode( __( 'Must start with a letter', 'admin-safety-guard' ) ); ?>;
        if ( ! value.endsWith( '_' ) )             return <?php echo wp_json_encode( __( 'Must end with _', 'admin-safety-guard' ) ); ?>;
        var body = value.slice( 0, -1 );
        if ( body.length < 3 )                     return <?php echo wp_json_encode( __( 'Min 4 characters (including _)', 'admin-safety-guard' ) ); ?>;
        if ( ! /[0-9]/.test( body ) )              return <?php echo wp_json_encode( __( 'Must contain at least one number', 'admin-safety-guard' ) ); ?>;
        if ( ! /^[a-z0-9_]+$/i.test( body ) )     return <?php echo wp_json_encode( __( 'Only letters, numbers, underscores', 'admin-safety-guard' ) ); ?>;
        return '';
    }

    function updateButtonState() {
        var err      = validatePrefix( newPrefixInput.value );
        var confirmed = confirmInput.value.trim().toUpperCase() === 'CHANGE PREFIX';
        var valid     = err === '';
        submitBtn.disabled = ! ( valid && confirmed );
        submitBtn.style.opacity = ( valid && confirmed ) ? '1' : '.5';
    }

    newPrefixInput.addEventListener( 'input', function () {
        var err = validatePrefix( this.value );
        if ( err === null ) {
            formatMsg.textContent = '';
            formatMsg.style.color = '';
        } else if ( err === '' ) {
            formatMsg.textContent = <?php echo wp_json_encode( __( '✓ Valid format', 'admin-safety-guard' ) ); ?>;
            formatMsg.style.color = '#16a34a';
        } else {
            formatMsg.textContent = err;
            formatMsg.style.color = '#dc2626';
        }
        updateButtonState();
    } );

    confirmInput.addEventListener( 'input', updateButtonState );

    // ── AJAX: change prefix ────────────────────────────────────────────────
    submitBtn.addEventListener( 'click', function () {
        var newPrefix = newPrefixInput.value.trim();

        submitBtn.disabled    = true;
        submitBtn.textContent = <?php echo wp_json_encode( __( 'Changing…', 'admin-safety-guard' ) ); ?>;
        feedback.style.display = 'none';
        renameLog.style.display = 'none';

        var data = new FormData();
        data.append( 'action', 'table_prefix_change' );
        data.append( 'nonce',  nonce );
        data.append( 'prefix', newPrefix );

        fetch( ajaxUrl, { method: 'POST', credentials: 'same-origin', body: data } )
            .then( function ( r ) { return r.json(); } )
            .then( function ( resp ) {
                if ( resp.success ) {
                    feedback.style.color   = '#16a34a';
                    feedback.textContent   = resp.data.message;
                    feedback.style.display = 'inline';

                    // Show log
                    if ( Array.isArray( resp.data.logs ) && resp.data.logs.length ) {
                        logItems.innerHTML = '';
                        resp.data.logs.forEach( function ( line ) {
                            var li = document.createElement( 'li' );
                            li.textContent = line;
                            logItems.appendChild( li );
                        } );
                        renameLog.style.display = 'block';
                    }

                    // Reset inputs
                    newPrefixInput.value = '';
                    confirmInput.value   = '';
                    formatMsg.textContent = '';
                    submitBtn.style.opacity = '.5';
                } else {
                    feedback.style.color   = '#dc2626';
                    feedback.textContent   = resp.data?.message ?? <?php echo wp_json_encode( __( 'An error occurred.', 'admin-safety-guard' ) ); ?>;
                    feedback.style.display = 'inline';
                    submitBtn.disabled     = false;
                    submitBtn.style.opacity = '1';
                }
            } )
            .catch( function () {
                feedback.style.color   = '#dc2626';
                feedback.textContent   = <?php echo wp_json_encode( __( 'Network error — please try again.', 'admin-safety-guard' ) ); ?>;
                feedback.style.display = 'inline';
                submitBtn.disabled     = false;
                submitBtn.style.opacity = '1';
            } )
            .finally( function () {
                submitBtn.textContent = <?php echo wp_json_encode( __( 'Change Prefix', 'admin-safety-guard' ) ); ?>;
            } );
    } );
}());
</script>
