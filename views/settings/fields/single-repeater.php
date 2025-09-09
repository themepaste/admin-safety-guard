<?php
/**
 * Output a repeater text input field (multi-instance safe).
 *
 * @package ThemePaste
 */

defined( 'ABSPATH' ) || exit;

$id_base       = esc_attr( $args['prefix'] . $args['current_screen_slug'] . '_' . $args['key'] );
$values        = isset( $args['value'] ) && is_array( $args['value'] ) ? $args['value'] : ['']; // At least one field
$instance_uid  = esc_attr( wp_unique_id( 'rpt_' ) ); // unique per repeater instance on the page
?>

<div class="tp-field tp-repeater-field"
     data-id-base="<?php echo esc_attr( $id_base ); ?>"
     data-instance="<?php echo $instance_uid; ?>">
    <div class="tp-field-label">
        <label><?php echo esc_html( $args['field']['label'] ); ?></label>
    </div>

    <div class="tp-field-input">
        <div class="tp-repeater-wrapper">
            <?php foreach ( $values as $val ) : ?>
                <div class="tp-repeater-item">
                    <input type="text"
                           name="<?php echo esc_attr( $id_base . '[' . $instance_uid . '][]' ); ?>"
                           value="<?php echo esc_attr( $val ); ?>"
                           class="tp-repeater-text"
                           placeholder="192.168.1.1" />
                    <button type="button" class="tp-repeater-remove" aria-label="Remove">➖</button>
                </div>
            <?php endforeach; ?>
        </div>

        <button type="button" class="tp-repeater-add" aria-label="Add">➕ Add</button>
        <p class="tp-field-desc"><?php echo esc_html( $args['field']['desc'] ); ?></p>
    </div>
</div>

<script>
(function(){
    // Prevent double-binding if this block renders multiple times.
    if (window.__tpRepeaterBound) return;
    window.__tpRepeaterBound = true;

    function updateRemoveButtons(repeaterWrapper){
        const items = repeaterWrapper.querySelectorAll('.tp-repeater-item');
        items.forEach(item => {
            const btn = item.querySelector('.tp-repeater-remove');
            if (btn) btn.style.display = (items.length > 1) ? 'inline-block' : 'none';
        });
    }

    // Initialize existing repeaters on DOM ready
    function initAll(){
        document.querySelectorAll('.tp-repeater-field .tp-repeater-wrapper').forEach(updateRemoveButtons);
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAll);
    } else {
        initAll();
    }

    // Delegated click handling (works for any number of repeaters)
    document.addEventListener('click', function(e){
        const addBtn = e.target.closest('.tp-repeater-add');
        if (addBtn) {
            const fieldWrapper = addBtn.closest('.tp-repeater-field');
            if (!fieldWrapper) return;

            const repeaterWrapper = fieldWrapper.querySelector('.tp-repeater-wrapper');
            const idBase    = fieldWrapper.dataset.idBase;
            const instance  = fieldWrapper.dataset.instance;

            // Build one fresh item
            const newItem = document.createElement('div');
            newItem.className = 'tp-repeater-item';
            newItem.innerHTML = `
                <input type="text"
                       name="${idBase}[${instance}][]"
                       value=""
                       class="tp-repeater-text"
                       placeholder="192.168.1.1" />
                <button type="button" class="tp-repeater-remove" aria-label="Remove">➖</button>
            `;
            repeaterWrapper.appendChild(newItem);
            updateRemoveButtons(repeaterWrapper);
            return;
        }

        const removeBtn = e.target.closest('.tp-repeater-remove');
        if (removeBtn) {
            const repeaterWrapper = removeBtn.closest('.tp-repeater-wrapper');
            removeBtn.closest('.tp-repeater-item')?.remove();
            updateRemoveButtons(repeaterWrapper);
        }
    });
})();
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
.tp-repeater-text::placeholder { opacity: 0.5; }
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
.tp-repeater-add:hover { background: #e0d4ff; }
</style>
