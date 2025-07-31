<?php
/**
 * Output a repeater text input field.
 *
 * @package ThemePaste
 */

defined( 'ABSPATH' ) || exit;

$id_base = esc_attr( $args['prefix'] . $args['current_screen_slug'] . '_' . $args['key'] );
$values  = isset( $args['value'] ) && is_array( $args['value'] ) ? $args['value'] : ['']; // At least one field
?>

<div class="tp-field tp-repeater-field" data-id-base="<?php echo esc_attr($id_base); ?>">
    <div class="tp-field-label">
        <label><?php echo esc_html( $args['field']['label'] ); ?></label>
    </div>

    <div class="tp-field-input">
        <div class="tp-repeater-wrapper">
            <?php foreach ( $values as $val ) : ?>
                <div class="tp-repeater-item">
                    <input type="text" name="<?php echo esc_attr($id_base); ?>[]" value="<?php echo esc_attr($val); ?>" class="tp-repeater-text" placeholder="192.168.1.1" />
                    <button type="button" class="tp-repeater-remove">➖</button>
                </div>
            <?php endforeach; ?>
        </div>

        <button type="button" class="tp-repeater-add">➕ Add</button>
        <p class="tp-field-desc"><?php echo esc_html( $args['field']['desc'] ); ?></p>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll('.tp-repeater-field').forEach(function (fieldWrapper) {
        const repeaterWrapper = fieldWrapper.querySelector('.tp-repeater-wrapper');
        const addBtn = fieldWrapper.querySelector('.tp-repeater-add');
        const idBase = fieldWrapper.dataset.idBase;

        function updateRemoveButtons() {
            const items = repeaterWrapper.querySelectorAll('.tp-repeater-item');
            items.forEach(item => {
                const removeBtn = item.querySelector('.tp-repeater-remove');
                if (removeBtn) {
                    removeBtn.style.display = items.length > 1 ? 'inline-block' : 'none';
                }
            });
        }

        addBtn.addEventListener('click', function () {
            const newItem = document.createElement('div');
            newItem.classList.add('tp-repeater-item');

            newItem.innerHTML = `
                <input type="text" name="${idBase}[]" value="" class="tp-repeater-text" placeholder="192.168.1.1" />
                <button type="button" class="tp-repeater-remove">➖</button>
            `;

            repeaterWrapper.appendChild(newItem);
            updateRemoveButtons();
        });

        repeaterWrapper.addEventListener('click', function (e) {
            if (e.target.classList.contains('tp-repeater-remove')) {
                e.target.closest('.tp-repeater-item').remove();
                updateRemoveButtons();
            }
        });

        updateRemoveButtons(); // Initial check
    });
});
</script>

<style>
.tp-repeater-item {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 8px;
}

.tp-repeater-text {
    width: 100%;
    padding: 2px 10px;
    border: 1px solid #bba8e7;
    border-radius: 6px;
    font-size: 14px;
}

.tp-repeater-text::placeholder {
    opacity: 0.5;
}

.tp-repeater-remove,
.tp-repeater-add {
    background: #f3f1ff;
    border: 1px solid #bba8e7;
    padding: 5px 10px;
    border-radius: 6px;
    font-size: 14px;
    cursor: pointer;
    transition: background 0.2s ease;
}

.tp-repeater-remove:hover,
.tp-repeater-add:hover {
    background: #e0d4ff;
}
</style>
