<div id="ce4wp-wc-dashboard-notice" class="ce4wp-notice ce4wp-notice-primary">
    <img class="icon" src="<?= CE4WP_PLUGIN_URL . 'assets/images/woocommerce-banners/setup-cm-wc.png'; ?>"  alt="Setup"/>
    <section class="content">
        <h1>
            <strong><?= __( 'Supercharge your WooCommerce store', 'ce4wp' ); ?></strong>
        </h1>
        <p>
            <?= __( 'Salvage abandoned carts, and make your WooCommerce order emails look amazing with the flip of a switch.', 'ce4wp' ); ?>
        </p>
        <button class="button button-primary" onclick="ce4wpNavigateToDashboard(this, 'd25f690a-217a-4d68-9c58-8693965d4673', { source: 'woocommerce_setup_notice' }, ce4wpWidgetStartCallback, ce4wpWidgetFinishCallback)"><?= __( 'Setup Creative Mail now', 'ce4wp' ); ?></button>
    </section>
    <span id="close" onclick="hideAdminWooCommerceNotice('woocommerce_dashboard_setup_notice')"></span>
</div>
