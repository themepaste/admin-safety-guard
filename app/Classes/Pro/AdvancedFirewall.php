<?php

namespace ThemePaste\SecureAdmin\Classes\Pro;

defined( 'ABSPATH' ) || exit;

use ThemePaste\SecureAdmin\Interfaces\FeatureInterface;
use ThemePaste\SecureAdmin\Traits\Hook;

class AdvancedFirewall implements FeatureInterface {

    use Hook;

    /**
     * Unique feature ID for settings reference.
     *
     * This key must match the section key we add into tpsa_settings_fields.
     *
     * @since 1.0.0
     * @var string
     */
    private $feature_id = 'web-application-firewall';

    /**
     * Register hooks.
     *
     * @since 1.0.0
     */
    public function register_hooks() {
        // 1) Extend settings UI
        $this->filter( 'tpsa_settings_fields', [$this, 'extend_pro_fields'] );
    }

    /**
     * Extend plugin settings fields array with Advanced Firewall feature.
     *
     * @param array $fields The fields array to be modified.
     *
     * @return array
     */
    public function extend_pro_fields( $fields ) {

        // If section already exists, we merge; if not, we create a new one.
        if ( !isset( $fields[$this->feature_id] ) ) {
            $fields[$this->feature_id] = [
                'fields' => [],
            ];
        }

        $fields[$this->feature_id]['fields'] = array_merge(
            $fields[$this->feature_id]['fields'],
            [
                'enable'              => [
                    'type'    => 'switch',
                    'label'   => __( 'Enable Firewall', 'admin-safety-guard-pro' ),
                    'class'   => '',
                    'id'      => '',
                    'desc'    => __( 'Turn the Web Application Firewall on or off.', 'admin-safety-guard-pro' ),
                    'default' => 0,
                ],

                'mode'                => [
                    'type'    => 'option',
                    'label'   => __( 'Mode', 'admin-safety-guard-pro' ),
                    'class'   => '',
                    'id'      => '',
                    'desc'    => __( 'Monitor only (log requests) or Block (actively block suspicious requests).', 'admin-safety-guard-pro' ),
                    'default' => 'monitor',
                    'options' => [
                        'monitor' => __( 'Monitor only', 'admin-safety-guard-pro' ),
                        'block'   => __( 'Block & log', 'admin-safety-guard-pro' ),
                    ],
                ],

                'protected-areas'     => [
                    'type'    => 'multi-check',
                    'label'   => __( 'Protected Areas', 'admin-safety-guard-pro' ),
                    'class'   => '',
                    'id'      => '',
                    'desc'    => __( 'Choose which areas of the site should be protected by the firewall rules.', 'admin-safety-guard-pro' ),
                    'default' => ['login', 'admin', 'xmlrpc', 'rest'],
                    'options' => [
                        'login'  => __( 'Login page (wp-login.php)', 'admin-safety-guard-pro' ),
                        'admin'  => __( 'Admin area (wp-admin)', 'admin-safety-guard-pro' ),
                        'xmlrpc' => __( 'XML-RPC endpoint', 'admin-safety-guard-pro' ),
                        'rest'   => __( 'REST API (/wp-json/)', 'admin-safety-guard-pro' ),
                        'front'  => __( 'Front-end pages', 'admin-safety-guard-pro' ),
                    ],
                ],

                'whitelist-ip'        => [
                    'type'    => 'single-repeater',
                    'label'   => __( 'Whitelist IP Addresses', 'admin-safety-guard-pro' ),
                    'class'   => '',
                    'id'      => '',
                    'desc'    => __( 'These IP addresses will bypass firewall checks.', 'admin-safety-guard-pro' ),
                    'default' => '',
                ],

                'block-ip-address'    => [
                    'type'    => 'single-repeater',
                    'label'   => __( 'Block IP Addresses', 'admin-safety-guard-pro' ),
                    'class'   => '',
                    'id'      => '',
                    'desc'    => __( 'Requests from these IP addresses will always be blocked.', 'admin-safety-guard-pro' ),
                    'default' => '',
                ],

                'blocked-user-agents' => [
                    'type'    => 'textarea',
                    'label'   => __( 'Blocked User Agents', 'admin-safety-guard-pro' ),
                    'class'   => '',
                    'id'      => '',
                    'desc'    => __( 'One user agent per line. Matching user agents will be blocked.', 'admin-safety-guard-pro' ),
                    'default' => '',
                ],

                'enable-sqli'         => [
                    'type'    => 'switch',
                    'label'   => __( 'SQL Injection Protection', 'admin-safety-guard-pro' ),
                    'class'   => '',
                    'id'      => '',
                    'desc'    => __( 'Scan request parameters for common SQL injection patterns.', 'admin-safety-guard-pro' ),
                    'default' => 1,
                ],

                'enable-xss'          => [
                    'type'    => 'switch',
                    'label'   => __( 'XSS Protection', 'admin-safety-guard-pro' ),
                    'class'   => '',
                    'id'      => '',
                    'desc'    => __( 'Scan request parameters for common cross-site scripting payloads.', 'admin-safety-guard-pro' ),
                    'default' => 1,
                ],

                'max-request-size'    => [
                    'type'    => 'number',
                    'label'   => __( 'Max Request Size (KB)', 'admin-safety-guard-pro' ),
                    'class'   => '',
                    'id'      => '',
                    'desc'    => __( 'Block requests with a body larger than this size. Use 0 to disable.', 'admin-safety-guard-pro' ),
                    'default' => 512, // 512 KB
                ],
            ]
        );

        return $fields;
    }

}