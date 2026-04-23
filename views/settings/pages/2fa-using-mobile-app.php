<?php
defined( 'ABSPATH' ) || exit;

use ThemePaste\SecureAdmin\Helpers\Utility;

$screen_slug             = $args['current_screen'];
$current_settings_fields = $args['settings_fields'][ $screen_slug ]['fields'] ?? [];
$saved_settings          = get_option( $args['option_name'], [] );
$page_label              = isset( $args['page_label_forsub'] ) && ! empty( $args['page_label_forsub'] )
    ? $args['page_label_forsub']
    : $args['page_label'];

$is_pro_active    = tp_is_pro_active();
$is_valid_license = is_valid_license_available();
$is_licensed      = $is_pro_active && $is_valid_license;
$upgrade_url      = 'https://themepaste.com/product/admin-safety-guard-pro#pricePlanSection';
$license_url      = add_query_arg( ['page' => 'tp-admin-safety-guard-pro'], admin_url( 'admin.php' ) );
?>

<div class="tpsa-setting-wrapper">
    <div class="tpsa-general-settings-wrapper">

        <h2><span>
            <a href="<?php echo esc_url( $args['current_url'] ); ?>"><?php echo esc_html( $args['current_tab_label'] ); ?></a>
            <?php echo esc_html( ' / ' . $page_label . ' Settings' ); ?>
        </span>
            <div class="tp-feature">
                <button class="tp-help-icon">?</button>
                <div class="tp-tooltip">
                    <p><?php esc_html_e( 'Protect your login with TOTP-based two-factor authentication using Google Authenticator, Authy, or any compatible app.', 'admin-safety-guard' ); ?></p>
                </div>
            </div>
        </h2>

        <?php if ( $is_licensed ) : ?>

            <?php
            $current_user    = wp_get_current_user();
            $is_2fa_verified = (bool) get_user_meta( $current_user->ID, '_2fa_app_verified', true );
            $ajax_nonce      = wp_create_nonce( 'tpsa-nonce' );
            ?>

            <!-- ── Section card: Feature Settings ──────────────────────── -->
            <div style="border:1px solid #dcdcde;border-radius:8px;background:#fff;margin-bottom:20px;overflow:hidden;">
                <div style="padding:18px 24px;border-bottom:1px solid #f0f0f0;background:#fafafa;">
                    <h3 style="margin:0;font-size:14px;font-weight:600;color:#1d2327;text-transform:uppercase;letter-spacing:.4px;">
                        <?php esc_html_e( 'Feature Settings', 'admin-safety-guard' ); ?>
                    </h3>
                </div>
                <div style="padding:24px;">
                    <form method="POST" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                        <?php wp_nonce_field( 'tpsa-nonce_action', 'tpsa-nonce_name' ); ?>
                        <input type="hidden" name="action"      value="tpsa_process_form">
                        <input type="hidden" name="screen_tab"  value="<?php echo esc_attr( $args['current_tab'] ); ?>">
                        <input type="hidden" name="screen_slug" value="<?php echo esc_attr( $screen_slug ); ?>">

                        <div class="tpsa-setting-row">
                            <?php
                            if ( is_array( $current_settings_fields ) && ! empty( $current_settings_fields ) ) {
                                foreach ( $current_settings_fields as $field_key => $field ) {
                                    $field_args = [
                                        'prefix'              => $args['prefix'],
                                        'key'                 => $field_key,
                                        'field'               => $field,
                                        'value'               => $saved_settings[ $field_key ] ?? $field['default'],
                                        'current_screen_slug' => $screen_slug,
                                    ];
                                    echo Utility::get_template( 'settings/fields/' . $field['type'] . '.php', $field_args );
                                }
                            }
                            ?>
                        </div>

                        <div class="tpsa-save-button">
                            <button type="submit"><?php esc_html_e( 'Save Settings', 'admin-safety-guard' ); ?></button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- ── Section card: Your 2FA Setup ────────────────────────── -->
            <div style="border:1px solid #dcdcde;border-radius:8px;background:#fff;margin-bottom:20px;overflow:hidden;">
                <div style="padding:18px 24px;border-bottom:1px solid #f0f0f0;background:#fafafa;display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
                    <h3 style="margin:0;font-size:14px;font-weight:600;color:#1d2327;text-transform:uppercase;letter-spacing:.4px;">
                        <?php esc_html_e( 'Your 2FA Setup', 'admin-safety-guard' ); ?>
                    </h3>
                    <span id="tpsa-admin-own-status">
                        <?php if ( $is_2fa_verified ) : ?>
                            <span style="color:#2e7d32;font-size:12px;font-weight:600;background:#f0faf0;border:1px solid #b7dfb8;padding:2px 10px;border-radius:20px;">
                                &#10004; <?php esc_html_e( 'Active', 'admin-safety-guard' ); ?>
                            </span>
                        <?php else : ?>
                            <span style="color:#c62828;font-size:12px;font-weight:600;background:#fff0f0;border:1px solid #f5c6c6;padding:2px 10px;border-radius:20px;">
                                &#10008; <?php esc_html_e( 'Not configured', 'admin-safety-guard' ); ?>
                            </span>
                        <?php endif; ?>
                    </span>
                </div>
                <div style="padding:24px;">
                    <p style="margin:0 0 16px;font-size:13px;color:#555;">
                        <?php esc_html_e( 'Link your admin account to an authenticator app. Once active, you will be asked for a 6-digit code on every login.', 'admin-safety-guard' ); ?>
                    </p>
                    <button type="button" id="tpsa-admin-own-toggle" class="button">
                        <?php echo $is_2fa_verified
                            ? esc_html__( 'Re-configure', 'admin-safety-guard' )
                            : esc_html__( 'Set Up 2FA', 'admin-safety-guard' ); ?>
                    </button>

                    <div id="tpsa-admin-own-panel" style="display:none;margin-top:20px;">
                        <p style="margin:0 0 12px;font-size:13px;color:#555;">
                            <strong>1.</strong> <?php esc_html_e( 'Scan this QR code with Google Authenticator, Authy, or any TOTP-compatible app.', 'admin-safety-guard' ); ?>
                        </p>
                        <div id="tpsa-admin-qr-wrap" style="display:inline-block;text-align:center;padding:16px;background:#f9f9f9;border:1px solid #e2d8fb;border-radius:8px;margin-bottom:16px;">
                            <span id="tpsa-admin-qr-load" style="display:block;color:#888;font-size:13px;min-width:148px;min-height:148px;line-height:148px;"><?php esc_html_e( 'Loading\xe2\x80\xa6', 'admin-safety-guard' ); ?></span>
                            <img id="tpsa-admin-qr-img" src="" alt="<?php esc_attr_e( 'QR Code', 'admin-safety-guard' ); ?>" style="display:none;width:180px;height:180px;border-radius:4px;" />
                        </div>
                        <p style="font-size:12px;color:#666;margin:0 0 16px;">
                            <?php esc_html_e( 'Or enter the key manually:', 'admin-safety-guard' ); ?>
                            <code id="tpsa-admin-secret" style="font-size:13px;letter-spacing:2px;background:#fff;border:1px solid #ddd;padding:2px 8px;border-radius:3px;"></code>
                        </p>
                        <p style="margin:0 0 10px;font-size:13px;color:#555;">
                            <strong>2.</strong> <?php esc_html_e( 'Enter the 6-digit code shown in your app to verify and enable:', 'admin-safety-guard' ); ?>
                        </p>
                        <div style="display:flex;gap:10px;align-items:center;margin-bottom:12px;">
                            <input type="text"
                                   id="tpsa-admin-code"
                                   maxlength="6"
                                   placeholder="000000"
                                   inputmode="numeric"
                                   autocomplete="off"
                                   style="width:140px;font-size:24px;letter-spacing:8px;text-align:center;padding:8px;border:1px solid #8c8f94;border-radius:4px;" />
                            <button type="button" id="tpsa-admin-verify" class="button button-primary" style="height:40px;">
                                <?php esc_html_e( 'Verify & Enable', 'admin-safety-guard' ); ?>
                            </button>
                        </div>
                        <p id="tpsa-admin-msg" style="display:none;font-size:13px;font-weight:600;margin:0;"></p>
                    </div>
                </div>
            </div>

            <!-- ── Section card: Users 2FA Status ──────────────────────── -->
            <div style="border:1px solid #dcdcde;border-radius:8px;background:#fff;overflow:hidden;">
                <div style="padding:18px 24px;border-bottom:1px solid #f0f0f0;background:#fafafa;">
                    <h3 style="margin:0;font-size:14px;font-weight:600;color:#1d2327;text-transform:uppercase;letter-spacing:.4px;">
                        <?php esc_html_e( 'Users 2FA Status', 'admin-safety-guard' ); ?>
                    </h3>
                </div>
                <div id="tpsa-2fa-users-section" style="padding:24px;">

                <div style="display:flex;gap:10px;margin-bottom:14px;flex-wrap:wrap;">
                    <input type="text"
                           id="tpsa-2fa-users-search"
                           placeholder="<?php esc_attr_e( 'Search users\xe2\x80\xa6', 'admin-safety-guard' ); ?>"
                           style="width:220px;padding:6px 10px;border:1px solid #8c8f94;border-radius:3px;" />
                    <select id="tpsa-2fa-users-role" style="padding:6px 10px;border:1px solid #8c8f94;border-radius:3px;">
                        <option value=""><?php esc_html_e( 'All Roles', 'admin-safety-guard' ); ?></option>
                        <?php foreach ( get_tps_all_user_roles() as $role_slug => $role_name ) : ?>
                            <option value="<?php echo esc_attr( $role_slug ); ?>"><?php echo esc_html( $role_name ); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select id="tpsa-2fa-users-filter2fa" style="padding:6px 10px;border:1px solid #8c8f94;border-radius:3px;">
                        <option value=""><?php esc_html_e( '2FA: All', 'admin-safety-guard' ); ?></option>
                        <option value="active"><?php esc_html_e( '2FA: Active', 'admin-safety-guard' ); ?></option>
                        <option value="inactive"><?php esc_html_e( '2FA: Not Set', 'admin-safety-guard' ); ?></option>
                    </select>
                </div>

                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'User', 'admin-safety-guard' ); ?></th>
                            <th><?php esc_html_e( 'Email', 'admin-safety-guard' ); ?></th>
                            <th><?php esc_html_e( 'Role', 'admin-safety-guard' ); ?></th>
                            <th style="width:130px;"><?php esc_html_e( '2FA Status', 'admin-safety-guard' ); ?></th>
                            <th style="width:110px;"><?php esc_html_e( 'Actions', 'admin-safety-guard' ); ?></th>
                        </tr>
                    </thead>
                    <tbody id="tpsa-2fa-users-tbody">
                        <tr><td colspan="5" style="text-align:center;padding:20px;color:#888;"><?php esc_html_e( 'Loading\xe2\x80\xa6', 'admin-safety-guard' ); ?></td></tr>
                    </tbody>
                </table>

                <div id="tpsa-2fa-users-pagination" style="margin-top:12px;display:flex;gap:6px;align-items:center;flex-wrap:wrap;"></div>
                </div><!-- /.tpsa-2fa-users-section -->
            </div><!-- /.users card -->

            <script>
            (function () {
                'use strict';

                var ajaxUrl = (typeof tpsaAdmin !== 'undefined' && tpsaAdmin.ajax_url) ? tpsaAdmin.ajax_url : '<?php echo esc_js( admin_url( 'admin-ajax.php' ) ); ?>';
                var nonce   = <?php echo wp_json_encode( $ajax_nonce ); ?>;
                var adminId = <?php echo wp_json_encode( $current_user->ID ); ?>;

                function post(action, extra) {
                    var fd = new FormData();
                    fd.append('action', action);
                    fd.append('nonce',  nonce);
                    if (extra) {
                        Object.keys(extra).forEach(function (k) { fd.append(k, extra[k]); });
                    }
                    return fetch(ajaxUrl, { method: 'POST', credentials: 'same-origin', body: fd })
                           .then(function (r) { return r.json(); });
                }

                // ── Admin own setup ──────────────────────────────────────────
                var ownToggle = document.getElementById('tpsa-admin-own-toggle');
                var ownPanel  = document.getElementById('tpsa-admin-own-panel');
                var ownQrLoad = document.getElementById('tpsa-admin-qr-load');
                var ownQrImg  = document.getElementById('tpsa-admin-qr-img');
                var ownSecret = document.getElementById('tpsa-admin-secret');
                var ownCode   = document.getElementById('tpsa-admin-code');
                var ownVerify = document.getElementById('tpsa-admin-verify');
                var ownMsg    = document.getElementById('tpsa-admin-msg');
                var ownStatus = document.getElementById('tpsa-admin-own-status');

                function showMsg(el, text, color) {
                    el.textContent    = text;
                    el.style.color    = color;
                    el.style.display  = 'block';
                }

                function loadAdminQr() {
                    ownQrLoad.style.display = 'block';
                    ownQrImg.style.display  = 'none';
                    post('tpsa_2fa_get_setup', { user_id: adminId }).then(function (resp) {
                        ownQrLoad.style.display = 'none';
                        if (resp.success) {
                            ownQrImg.src           = resp.data.qr_url;
                            ownQrImg.style.display = 'block';
                            if (ownSecret) ownSecret.textContent = resp.data.secret;
                        } else {
                            ownQrLoad.textContent    = (resp.data && resp.data.message) || <?php echo wp_json_encode( __( 'Error loading QR code.', 'admin-safety-guard' ) ); ?>;
                            ownQrLoad.style.display  = 'block';
                        }
                    }).catch(function () {
                        ownQrLoad.textContent   = <?php echo wp_json_encode( __( 'Network error — please refresh.', 'admin-safety-guard' ) ); ?>;
                        ownQrLoad.style.display = 'block';
                    });
                }

                if (ownToggle) {
                    ownToggle.addEventListener('click', function () {
                        var isHidden = ownPanel.style.display === 'none';
                        ownPanel.style.display = isHidden ? 'block' : 'none';
                        if (isHidden) loadAdminQr();
                    });
                }

                if (ownCode) {
                    ownCode.addEventListener('input', function () {
                        this.value = this.value.replace(/\D/g, '').slice(0, 6);
                    });
                }

                if (ownVerify) {
                    ownVerify.addEventListener('click', function () {
                        var code = ownCode ? ownCode.value.trim() : '';
                        if (code.length !== 6) {
                            showMsg(ownMsg, <?php echo wp_json_encode( __( 'Enter the 6-digit code from your app.', 'admin-safety-guard' ) ); ?>, '#c62828');
                            return;
                        }
                        ownVerify.disabled    = true;
                        ownVerify.textContent = <?php echo wp_json_encode( __( 'Verifying\xe2\x80\xa6', 'admin-safety-guard' ) ); ?>;
                        post('tpsa_2fa_verify_setup', { user_id: adminId, otp: code })
                        .then(function (resp) {
                            if (resp.success) {
                                showMsg(ownMsg, resp.data.message, '#2e7d32');
                                if (ownStatus) ownStatus.innerHTML = '<span style="color:#2e7d32;font-size:13px;font-weight:600;">&#10004; <?php echo esc_js( __( 'Active', 'admin-safety-guard' ) ); ?></span>';
                                if (ownToggle) ownToggle.textContent = <?php echo wp_json_encode( __( 'Re-configure', 'admin-safety-guard' ) ); ?>;
                                setTimeout(function () { ownPanel.style.display = 'none'; }, 2200);
                            } else {
                                showMsg(ownMsg, (resp.data && resp.data.message) || <?php echo wp_json_encode( __( 'Invalid code. Try again.', 'admin-safety-guard' ) ); ?>, '#c62828');
                            }
                        })
                        .catch(function () {
                            showMsg(ownMsg, <?php echo wp_json_encode( __( 'Network error. Please try again.', 'admin-safety-guard' ) ); ?>, '#c62828');
                        })
                        .finally(function () {
                            ownVerify.disabled    = false;
                            ownVerify.textContent = <?php echo wp_json_encode( __( 'Verify & Enable', 'admin-safety-guard' ) ); ?>;
                        });
                    });
                }

                // ── Users table ──────────────────────────────────────────────
                var tbody      = document.getElementById('tpsa-2fa-users-tbody');
                var pagination = document.getElementById('tpsa-2fa-users-pagination');
                var searchEl   = document.getElementById('tpsa-2fa-users-search');
                var roleEl     = document.getElementById('tpsa-2fa-users-role');
                var filter2fa  = document.getElementById('tpsa-2fa-users-filter2fa');
                var searchTimer;

                var state = { page: 1, per_page: 10, search: '', role: '', filter2fa: '' };

                var i18n = {
                    loading:      <?php echo wp_json_encode( __( 'Loading\xe2\x80\xa6', 'admin-safety-guard' ) ); ?>,
                    noUsers:      <?php echo wp_json_encode( __( 'No users found.', 'admin-safety-guard' ) ); ?>,
                    loadFail:     <?php echo wp_json_encode( __( 'Failed to load users.', 'admin-safety-guard' ) ); ?>,
                    netError:     <?php echo wp_json_encode( __( 'Network error. Please refresh.', 'admin-safety-guard' ) ); ?>,
                    active:       <?php echo wp_json_encode( __( 'Active', 'admin-safety-guard' ) ); ?>,
                    notSet:       <?php echo wp_json_encode( __( 'Not set', 'admin-safety-guard' ) ); ?>,
                    reset2fa:     <?php echo wp_json_encode( __( 'Reset 2FA', 'admin-safety-guard' ) ); ?>,
                    confirmReset: <?php echo wp_json_encode( __( 'Reset 2FA for', 'admin-safety-guard' ) ); ?>,
                };

                function esc(str) {
                    var d = document.createElement('div');
                    d.appendChild(document.createTextNode(String(str)));
                    return d.innerHTML;
                }

                function renderPagination(total, perPage, current) {
                    pagination.innerHTML = '';
                    var totalPages = Math.ceil(total / perPage);
                    if (totalPages <= 1) return;
                    for (var i = 1; i <= totalPages; i++) {
                        (function (pg) {
                            var btn = document.createElement('button');
                            btn.type      = 'button';
                            btn.textContent = String(pg);
                            btn.className = 'button button-small' + (pg === current ? ' button-primary' : '');
                            btn.addEventListener('click', function () { state.page = pg; loadUsers(); });
                            pagination.appendChild(btn);
                        }(i));
                    }
                }

                function bindResetBtns() {
                    document.querySelectorAll('.tpsa-2fa-reset-btn').forEach(function (btn) {
                        btn.addEventListener('click', function () {
                            var uid   = this.dataset.uid;
                            var uname = this.dataset.name;
                            if (!confirm(i18n.confirmReset + ' ' + uname + '?')) return;
                            var self = this;
                            self.disabled = true;
                            post('tpsa_2fa_reset_user', { user_id: uid }).then(function (resp) {
                                if (resp.success) { loadUsers(); } else { self.disabled = false; }
                            }).catch(function () { self.disabled = false; });
                        });
                    });
                }

                function applyFilter2fa(users) {
                    if (state.filter2fa === 'active')   return users.filter(function (u) { return u.is_verified; });
                    if (state.filter2fa === 'inactive') return users.filter(function (u) { return !u.is_verified; });
                    return users;
                }

                function renderTable(resp) {
                    if (!resp.success || !resp.data) {
                        tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;color:#c62828;">' + esc(i18n.loadFail) + '</td></tr>';
                        return;
                    }
                    var users = applyFilter2fa(resp.data.users);
                    if (!users.length) {
                        tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;padding:20px;color:#888;">' + esc(i18n.noUsers) + '</td></tr>';
                        renderPagination(resp.data.total, resp.data.per_page, resp.data.page);
                        return;
                    }
                    tbody.innerHTML = users.map(function (u) {
                        var statusHtml = u.is_verified
                            ? '<span style="color:#2e7d32;font-weight:600;">&#10004; ' + esc(i18n.active) + '</span>'
                            : '<span style="color:#999;">&#10008; ' + esc(i18n.notSet) + '</span>';
                        var resetBtn = u.is_verified
                            ? '<button type="button" class="button button-small tpsa-2fa-reset-btn" data-uid="' + esc(u.id) + '" data-name="' + esc(u.name) + '">' + esc(i18n.reset2fa) + '</button>'
                            : '\xe2\x80\x94';
                        return '<tr>' +
                            '<td><strong>' + esc(u.name) + '</strong><br><small style="color:#888;">' + esc(u.username) + '</small></td>' +
                            '<td>' + esc(u.email) + '</td>' +
                            '<td>' + esc((u.roles || []).join(', ')) + '</td>' +
                            '<td>' + statusHtml + '</td>' +
                            '<td>' + resetBtn + '</td>' +
                        '</tr>';
                    }).join('');
                    renderPagination(resp.data.total, resp.data.per_page, resp.data.page);
                    bindResetBtns();
                }

                function loadUsers() {
                    tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;padding:20px;color:#888;">' + esc(i18n.loading) + '</td></tr>';
                    post('tpsa_2fa_get_users_status', {
                        page:     state.page,
                        per_page: state.per_page,
                        search:   state.search,
                        role:     state.role,
                    }).then(renderTable).catch(function () {
                        tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;color:#c62828;">' + esc(i18n.netError) + '</td></tr>';
                    });
                }

                var debounce = function (fn, delay) {
                    return function () {
                        clearTimeout(searchTimer);
                        searchTimer = setTimeout(fn, delay);
                    };
                };

                if (searchEl) {
                    searchEl.addEventListener('input', debounce(function () {
                        state.search = searchEl.value;
                        state.page   = 1;
                        loadUsers();
                    }, 400));
                }

                if (roleEl) {
                    roleEl.addEventListener('change', function () {
                        state.role = roleEl.value;
                        state.page = 1;
                        loadUsers();
                    });
                }

                if (filter2fa) {
                    filter2fa.addEventListener('change', function () {
                        state.filter2fa = filter2fa.value;
                        // Re-render current data with client-side filter
                        state.page = 1;
                        loadUsers();
                    });
                }

                loadUsers();
            }());
            </script>

        <?php else : ?>

            <!-- ── Locked / upgrade card ──────────────────────────────── -->
            <div style="border:1px dashed #814bfe;border-radius:10px;padding:28px 24px;background:#faf8ff;">
                <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;">
                    <h3 style="margin:0;font-size:15px;color:#1d2327;">
                        <?php esc_html_e( 'Two-Factor Authentication via Mobile App', 'admin-safety-guard' ); ?>
                    </h3>
                    <span style="background:#814bfe;color:#fff;font-size:11px;font-weight:700;
                                 padding:2px 8px;border-radius:20px;letter-spacing:.5px;text-transform:uppercase;">
                        <?php esc_html_e( 'Pro', 'admin-safety-guard' ); ?>
                    </span>
                </div>
                <p style="color:#5c5c7b;font-size:13px;margin:0 0 18px;max-width:580px;">
                    <?php esc_html_e( 'Add TOTP-based 2FA for any user role. Users scan a QR code with Google Authenticator, Authy, or any compatible app and enter a 6-digit code at login. Control which roles must use 2FA and manage all users from this page.', 'admin-safety-guard' ); ?>
                </p>

                <!-- Blurred preview -->
                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:12px;margin-bottom:20px;
                            opacity:.35;pointer-events:none;filter:blur(1px);">
                    <?php
                    $previews = [
                        __( 'Enable 2FA', 'admin-safety-guard' ),
                        __( 'Enforce by Role', 'admin-safety-guard' ),
                        __( 'QR Code Setup', 'admin-safety-guard' ),
                        __( 'User Status Table', 'admin-safety-guard' ),
                    ];
                    foreach ( $previews as $label ) :
                    ?>
                    <div style="background:#fff;border:1px solid #e2d8fb;border-radius:6px;padding:12px;">
                        <strong style="font-size:12px;color:#1d2327;display:block;margin-bottom:6px;"><?php echo esc_html( $label ); ?></strong>
                        <div style="height:36px;background:#f1f5f9;border-radius:4px;"></div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div style="display:flex;gap:10px;flex-wrap:wrap;">
                    <a href="<?php echo esc_url( $upgrade_url ); ?>" target="_blank" rel="noopener noreferrer"
                       style="display:inline-block;background:#814bfe;color:#fff;font-size:13px;font-weight:600;
                              padding:9px 22px;border-radius:6px;text-decoration:none;">
                        <?php esc_html_e( 'Purchase Pro', 'admin-safety-guard' ); ?>
                    </a>
                    <?php if ( $is_pro_active ) : ?>
                    <a href="<?php echo esc_url( $license_url ); ?>"
                       style="display:inline-block;background:#f1eef9;color:#814bfe;font-size:13px;font-weight:600;
                              padding:9px 22px;border-radius:6px;text-decoration:none;border:1px solid #814bfe;">
                        <?php esc_html_e( 'Activate License', 'admin-safety-guard' ); ?>
                    </a>
                    <?php endif; ?>
                </div>
            </div>

        <?php endif; ?>

    </div>
</div>
