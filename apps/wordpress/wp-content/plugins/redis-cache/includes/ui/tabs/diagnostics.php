<?php
/**
 * Diagnostics tab template
 *
 * @package Rhubarb\RedisCache
 */

defined( '\\ABSPATH' ) || exit;

?>

<div class="card">
    <pre id="redis-cache-diagnostics"><?php require __DIR__ . '/../../diagnostics.php'; ?></pre>
</div>

<p id="redis-cache-copy-button">
    <span class="copy-button-wrapper">
        <button type="button" class="button copy-button" data-clipboard-target="#redis-cache-diagnostics">
            <?php _e( 'Copy diagnostics to clipboard', 'redis-cache' ); ?>
        </button>
        <span class="success hidden" aria-hidden="true"><?php _e( 'Copied!' ); ?></span>
    </span>
</p>
