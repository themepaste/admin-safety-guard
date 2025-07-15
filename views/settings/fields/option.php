<?php
    /**
     * Output a select field.
     *
     * @package ThemePaste
     */

    defined( 'ABSPATH' ) || exit;

    $id_name = esc_attr( $args['prefix'] . $args['current_screen_slug'] . '_' . $args['key'] );
    $value   = isset( $args['value'] ) && ! empty( $args['value'] ) ? $args['value'] : '';
    $options = isset( $args['field']['options'] ) && is_array( $args['field']['options'] ) ? $args['field']['options'] : [];

    $select_options_html = '';
    foreach ( $options as $option_key => $option_label ) {
        $selected            = selected( $value, $option_key, false );
        $select_options_html .= sprintf(
            '<option value="%s"%s>%s</option>',
            esc_attr( $option_key ),
            $selected,
            esc_html( $option_label )
        );
    }

    $field_template = '
        <div class="tp-field">
            <div class="tp-field-label">
                <label for="%1$s">%2$s</label>
            </div>
            <div class="tp-field-input">
                <select id="%1$s" name="%1$s">
                    %3$s
                </select>
                <p class="tp-field-desc">%4$s</p>
            </div>
        </div>';

    printf(
        $field_template,
        $id_name,                                 // %1$s == ID & Name
        esc_html( $args['field']['label'] ),      // %2$s == Label
        $select_options_html,                     // %3$s == <option> list
        esc_html( $args['field']['desc'] )        // %4$s == Description
    );
?>
