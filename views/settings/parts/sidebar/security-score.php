<?php
defined( 'ABSPATH' ) || exit;

$security_score = tpsa_get_security_score();
$security_label = tpsa_get_security_label( $security_score );
?>

<div class="asg-ss_wrapper">
    <div class="asg-ss_gradient-border">
        <div class="asg-ss_card">

            <div class="asg-ss_card-header">
                <svg class="asg-ss_shield-icon" viewBox="0 0 24 24" fill="none">
                    <path d="M12 2L4 5V11C4 16.55 7.84 21.74 12 22C16.16 21.74 20 16.55 20 11V5L12 2Z"
                        fill="currentColor" />
                </svg>
                <span class="asg-ss_card-title">Security Score</span>
            </div>

            <div class="asg-ss_score">
                <span class="asg-ss_score-value">
                    <?php echo esc_html( $security_score ); ?>
                </span>
                <span class="asg-ss_score-total">/100</span>
            </div>

            <div class="asg-ss_progress-bar">
                <div class="asg-ss_progress-fill" style="width: <?php echo esc_attr( $security_score ); ?>%;">
                </div>
            </div>

            <p class="asg-ss_status-text">
                <?php echo esc_html( $security_label ); ?>
            </p>

        </div>
    </div>
</div>