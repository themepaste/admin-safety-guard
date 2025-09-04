<?php
/**
 * Output an image upload field with preview.
 *
 * @package ThemePaste
 */

defined( 'ABSPATH' ) || exit;

$id_name = esc_attr( $args['prefix'] . $args['current_screen_slug'] . '_' . $args['key'] );
$value   = isset( $args['value'] ) && ! empty( $args['value'] ) ? $args['value'] : '';


// Normalize $value to a string URL
$raw_value = $value;

if ( is_array( $raw_value ) ) {
    // common shapes: ['url' => '...'] or ['0' => '...']
    if ( isset( $raw_value['url'] ) && is_string( $raw_value['url'] ) ) {
        $value = $raw_value['url'];
    } else {
        // take the first scalar string in the array, or empty string
        $first = reset( $raw_value );
        $value = is_string( $first ) ? $first : '';
    }
} elseif ( is_object( $raw_value ) ) {
    // very defensive: some code might pass an object with ->url
    $value = isset( $raw_value->url ) && is_string( $raw_value->url ) ? $raw_value->url : '';
} else {
    $value = (string) $raw_value;
}


$image_preview = $value !== ''
    ? '<img src="' . esc_url( $value ) . '" style="max-width:150px;height:auto;" class="tp-image-preview" />'
    : '<img src="" style="display:none;max-width:150px;height:auto;" class="tp-image-preview" />';


$field_template = '
    <div class="tp-field">
        <div class="tp-field-label">
            <label>%1$s</label>
        </div>
        <div class="tp-field-input">
            <div class="tp-upload-wrapper">
                <input type="hidden" id="%2$s" name="%2$s" value="%3$s" class="tp-image-url" />
                <button type="button" class="button tp-upload-button" data-target="%2$s">Upload Image</button>
                <div class="tp-image-container">%5$s</div>
            </div>
            <p class="tp-field-desc">%4$s</p>
        </div>
    </div>';

$field_template = apply_filters( $id_name, $field_template, $args );

printf( $field_template,
    esc_html( $args['field']['label'] ), // %1$s == Label
    $id_name,                            // %2$s == ID & Name
    esc_attr( $value ),                  // %3$s == Value (Image URL)
    $args['field']['desc'],             // %4$s == Description
    $image_preview                      // %5$s == Image Preview HTML
);
?>


<?php if ( is_admin() ) : ?>
    <script>
    jQuery(document).ready(function($) {
        $('.tp-upload-button').on('click', function(e) {
            e.preventDefault();
            var button = $(this);
            var target = $('#' + button.data('target'));
            var preview = button.siblings('.tp-image-container').find('img');

            var frame = wp.media({
                title: 'Select or Upload Image',
                button: { text: 'Use this image' },
                multiple: false
            });

            frame.on('select', function() {
                var attachment = frame.state().get('selection').first().toJSON();
                target.val(attachment.url);
                preview.attr('src', attachment.url).css('display', 'block');
            });

            frame.open();
        });
    });
    </script>
<?php endif; ?>
