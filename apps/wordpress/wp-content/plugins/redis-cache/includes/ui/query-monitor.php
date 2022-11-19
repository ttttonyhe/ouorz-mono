<?php
/**
 * Query Montior output template
 *
 * @package Rhubarb\RedisCache
 */

defined( '\\ABSPATH' ) || exit;

/**
 * @var \Rhubarb\RedisCache\QM_Output $this
 * @var array<mixed> $data
 */

// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
echo $this->before_non_tabular_output();
?>

    <section>
        <h3><?php esc_html_e( 'Status', 'redis-cache' ); ?></h3>
        <p class="qm-ltr"><code><?php echo esc_html( $data['status'] ); ?></code></p>
    </section>

    <section>
        <h3><?php esc_html_e( 'Hit Ratio', 'redis-cache' ); ?></h3>
        <p class="qm-ltr"><code><?php echo esc_html( $data['ratio'] ); ?>%</code></p>
    </section>

    <section>
        <h3><?php esc_html_e( 'Hits', 'redis-cache' ); ?></h3>
        <p class="qm-ltr"><code><?php echo (int) $data['hits']; ?></code></p>
    </section>

    <section>
        <h3><?php esc_html_e( 'Misses', 'redis-cache' ); ?></h3>
        <p class="qm-ltr"><code><?php echo (int) $data['misses']; ?></code></p>
    </section>

    <section>
        <h3><?php esc_html_e( 'Size', 'redis-cache' ); ?></h3>
        <p class="qm-ltr"><code><?php echo esc_html( size_format( $data['bytes'], 2 ) ); ?></code></p>
    </section>

</div>

<?php if ( ! empty( $data['errors'] ) ) : ?>
    <div class="qm-boxed qm-boxed-wrap">

        <section>
            <h3><?php esc_html_e( 'Errors', 'redis-cache' ); ?></h3>

            <table>
                <tbody>
                    <?php foreach ( $data['errors'] as $err ) : ?>
                        <tr class="qm-warn">
                            <td class="qm-ltr qm-wrap">
                                <span class="dashicons dashicons-warning" aria-hidden="true"></span>
                                <?php echo esc_html( $err ); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>

    </div>
<?php endif; ?>

<div class="qm-boxed qm-boxed-wrap">

    <?php if ( ! empty( $data['groups']['global'] ) ) : ?>
        <section>
            <h3><?php esc_html_e( 'Global Groups', 'redis-cache' ); ?></h3>

            <ul class="qm-ltr">
                <?php foreach ( $data['groups']['global'] as $group ) : ?>
                    <li>
                        <?php echo esc_html( $group ); ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>
    <?php endif; ?>

    <?php if ( ! empty( $data['groups']['non_persistent'] ) ) : ?>
        <section>
            <h3><?php esc_html_e( 'Non-persistent Groups', 'redis-cache' ); ?></h3>

            <ul class="qm-ltr">
                <?php foreach ( $data['groups']['non_persistent'] as $group ) : ?>
                    <li>
                        <?php echo esc_html( $group ); ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>
    <?php endif; ?>

    <?php if ( ! empty( $data['groups']['unflushable'] ) ) : ?>
        <section>
            <h3><?php esc_html_e( 'Unflushable Groups', 'redis-cache' ); ?></h3>

            <ul class="qm-ltr">
                <?php foreach ( $data['groups']['unflushable'] as $group ) : ?>
                    <li>
                        <?php echo esc_html( $group ); ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>
    <?php endif; ?>

    <?php if ( ! empty( $data['meta'] ) ) : ?>
        <section>
            <h3><?php esc_html_e( 'Metadata', 'redis-cache' ); ?></h3>

            <table>
                <tbody>
                    <?php foreach ( $data['meta'] as $label => $value ) : ?>
                        <tr>
                            <th scope="row"><?php echo esc_html( $label ); ?></th>
                            <td class="qm-ltr qm-wrap"><?php echo esc_html( $value ); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    <?php endif; ?>

<?php

// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
echo $this->after_non_tabular_output();
