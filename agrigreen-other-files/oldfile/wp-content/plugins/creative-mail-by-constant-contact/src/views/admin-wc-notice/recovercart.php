<div id="ce4wp-wc-dashboard-notice" class="ce4wp-notice ce4wp-notice-secondary">
    <img class="icon" src="<?= CE4WP_PLUGIN_URL . 'assets/images/woocommerce-banners/active-cm-wc.png'; ?>"  alt="Abandoned cart"/>
    <section class="content">
        <h1>
            <strong><?= __( 'Reclaim your profits. Increase store conversion by 10%*', 'ce4wp' ); ?></strong>
        </h1>
        <p>
            <?= __( '3 out of 4 people add something to their shopping cart, but leave before the actual purchase.', 'ce4wp' ); ?>
        </p>
        <button class="button button-primary" onclick="ce4wpNavigateToDashboard(this, '1fabdbe2-95ed-4e1e-a2f3-ba0278f5096f', { source: 'woocommerce_abandoned_cart_notice' }, ce4wpWidgetStartCallback, ce4wpWidgetFinishCallback)"><?= __( 'Recover abandoned carts with Creative Mail', 'ce4wp' ); ?></button>
    </section>
    <span id="close" onclick="hideAdminWooCommerceNotice('woocommerce_abandoned_cart_notice')"></span>
</div>
