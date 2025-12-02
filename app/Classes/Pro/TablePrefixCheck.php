<?php

namespace ThemePaste\SecureAdmin\Classes\Pro;

defined( 'ABSPATH' ) || exit;

use ThemePaste\SecureAdmin\Interfaces\FeatureInterface;
use ThemePaste\SecureAdmin\Traits\Hook;

/**
 * Feature: TablePrefixCheck.php
 *
 * Implements
 *
 * @package
 * @since   1.0.0
 */
class TablePrefixCheck implements FeatureInterface {

    use Hook;

    /**
     * Unique feature ID for settings reference.
     *
     * @since 1.0.0
     * @var string
     */
    private $feature_id = 'table-prefix-check';

    /**
     * Register hooks for the feature.
     *
     * @return void
     */
    public function register_hooks() {
        $this->filter( 'tpsa_settings_option', [$this, 'extend_pro_settings'] );
        $this->filter( 'tpsa_settings_fields', [$this, 'extend_pro_fields'] );
    }

    /**
     * Extend the settings array with a new option for checking the DB table prefix.
     *
     * @param array $settings The settings array to extend.
     *
     * @return array The extended settings array.
     */
    public function extend_pro_settings( $settings ) {
        $settings['table-prefix-check'] = [
            'label'  => __( 'Check DB Table Prefix', 'tp-secure-plugin' ),
            'class'  => '',
            'is_pro' => true,
        ];

        return $settings;
    }

    /**
     * Extends the settings fields with a new field for checking the DB table prefix.
     *
     * Adds two fields to the settings array: 'new-prefix' and 'i-understand'. The 'new-prefix' field allows
     * the user to input a new prefix for the DB tables, while the 'i-understand' field requires the user
     * to type "I UNDERSTAND" to confirm they understand the implications of changing the DB table prefix.
     *
     * @param array $fields The settings fields array to extend.
     *
     * @return array The extended settings fields array.
     */
    public function extend_pro_fields( $fields ) {

        //DB table prefix check
        $fields['table-prefix-check']['fields']['new-prefix'] = array(
            'type'    => 'text',
            'label'   => __( 'New Prefix', 'tp-secure-plugin' ),
            'class'   => '',
            'id'      => '',
            'desc'    => __( 'Make a full backup first. The plugin will try to update wp-config.php and references in options/usermeta', 'tp-secure-plugin' ),
            'default' => '',
        );

        $fields['table-prefix-check']['fields']['i-understand'] = array(
            'type'    => 'text',
            'label'   => __( 'Type "I UNDERSTAND"', 'tp-secure-plugin' ),
            'class'   => '',
            'id'      => '',
            'desc'    => __( 'Type "I UNDERSTAND" in this field', 'tp-secure-plugin' ),
            'default' => '',
        );

        return $fields;
    }

    /**
     * Get plugin settings for this feature.
     *
     * @return array
     */
    private function get_settings() {
        $option_name = get_tpsa_settings_option_name( $this->feature_id );
        return get_option( $option_name, [] );
    }
}