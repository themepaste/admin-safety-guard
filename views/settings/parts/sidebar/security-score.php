<?php
defined( 'ABSPATH' ) || exit;

$report = tpsa_get_security_report();
$score  = (int) $report['score'];
$issues = $report['issues'];

$severity_labels = [
    'critical' => __( 'Critical', 'admin-safety-guard' ),
    'high'     => __( 'High', 'admin-safety-guard' ),
    'medium'   => __( 'Medium', 'admin-safety-guard' ),
    'low'      => __( 'Low', 'admin-safety-guard' ),
];
?>

<div class="asg-ss_wrapper">
    <div class="asg-ss_gradient-border">
        <div class="asg-ss_card">

            <div class="asg-ss_card-header">
                <svg class="asg-ss_shield-icon" viewBox="0 0 24 24" fill="none">
                    <path d="M12 2L4 5V11C4 16.55 7.84 21.74 12 22C16.16 21.74 20 16.55 20 11V5L12 2Z"
                        fill="currentColor" />
                </svg>
                <span class="asg-ss_card-title"><?php esc_html_e( 'Security Score', 'admin-safety-guard' ); ?></span>
            </div>

            <div class="asg-ss_score">
                <span class="asg-ss_score-value"><?php echo esc_html( $score ); ?></span>
                <span class="asg-ss_score-total">/100</span>
            </div>

            <div class="asg-ss_progress-bar">
                <div class="asg-ss_progress-fill" style="width: <?php echo esc_attr( $score ); ?>%;"></div>
            </div>

            <p class="asg-ss_status-text"><?php echo esc_html( $report['label'] ); ?></p>

            <?php if ( !empty( $issues ) ) : ?>
                <button type="button" class="asg-ss_view" id="asg-ss_view">
                    <?php
                    printf(
                        /* translators: %d: number of issues found. */
                        esc_html( _n( 'View %d issue', 'View %d issues', count( $issues ), 'admin-safety-guard' ) ),
                        count( $issues )
                    );
                    ?>
                </button>
            <?php endif; ?>

        </div>
    </div>
</div>

<?php if ( !empty( $issues ) ) : ?>
<!-- Issue list. Hidden until the button above is used. -->
<div class="asg-issues" id="asg-issues" hidden role="dialog" aria-modal="true" aria-labelledby="asg-issues_title">
    <div class="asg-issues_panel">
        <header class="asg-issues_head">
            <div>
                <h2 class="asg-issues_title" id="asg-issues_title">
                    <?php esc_html_e( 'How to improve your score', 'admin-safety-guard' ); ?>
                </h2>
                <p class="asg-issues_sub">
                    <?php
                    printf(
                        /* translators: 1: checks passed, 2: total checks, 3: score out of 100. */
                        esc_html__( '%1$d of %2$d checks passed - currently %3$d/100', 'admin-safety-guard' ),
                        (int) $report['passed'],
                        (int) $report['total'],
                        $score
                    );
                    ?>
                </p>
            </div>
            <button type="button" class="asg-issues_close" id="asg-issues_close"
                aria-label="<?php esc_attr_e( 'Close', 'admin-safety-guard' ); ?>">&times;</button>
        </header>

        <div class="asg-issues_body">
            <?php foreach ( $issues as $issue ) : ?>
                <div class="asg-issues_item is-<?php echo esc_attr( $issue['severity'] ); ?>">
                    <div class="asg-issues_itemHead">
                        <span class="asg-issues_badge is-<?php echo esc_attr( $issue['severity'] ); ?>">
                            <?php echo esc_html( $severity_labels[$issue['severity']] ?? $issue['severity'] ); ?>
                        </span>
                        <strong class="asg-issues_name"><?php echo esc_html( $issue['problem'] ?? $issue['title'] ); ?></strong>
                        <span class="asg-issues_points">
                            <?php
                            printf(
                                /* translators: %d: points this check is worth. */
                                esc_html__( '+%d pts', 'admin-safety-guard' ),
                                (int) $issue['weight']
                            );
                            ?>
                        </span>
                    </div>

                    <?php if ( !empty( $issue['fix'] ) ) : ?>
                        <p class="asg-issues_fix"><?php echo esc_html( $issue['fix'] ); ?></p>
                    <?php endif; ?>

                    <?php if ( !empty( $issue['url'] ) ) : ?>
                        <a class="asg-issues_action" href="<?php echo esc_url( $issue['url'] ); ?>">
                            <?php esc_html_e( 'Fix this', 'admin-safety-guard' ); ?> &rarr;
                        </a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>

            <?php if ( (int) $report['passed'] > 0 ) : ?>
                <details class="asg-issues_passed">
                    <summary>
                        <?php
                        printf(
                            /* translators: %d: number of checks already passing. */
                            esc_html__( '%d checks already passing', 'admin-safety-guard' ),
                            (int) $report['passed']
                        );
                        ?>
                    </summary>
                    <ul>
                        <?php foreach ( $report['checks'] as $check ) : ?>
                            <?php if ( $check['pass'] ) : ?>
                                <li><?php echo esc_html( $check['title'] ); ?></li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ul>
                </details>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php endif; ?>
