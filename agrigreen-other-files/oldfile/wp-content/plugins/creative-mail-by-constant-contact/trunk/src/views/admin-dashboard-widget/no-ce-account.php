<div style="display: flex;">
    <section style="flex: 1;">
        <p style="margin-top: 0;">
            <?= __('Our intelligent email editor makes it easy to create professional emails to engage your audience.', 'ce4wp'); ?>
        </p>
        <button class="button button-primary" onclick="ce4wpNavigateToDashboard(this, undefined, undefined, ce4wpWidgetStartCallback, ce4wpWidgetFinishCallback)">
            <?= __("Let's go!", 'ce4wp'); ?>
        </button>
    </section>
    <img
        src="<?= CE4WP_PLUGIN_URL . 'assets/images/admin-dashboard-widget/creative-mail.png'; ?>"
        style="margin-top: -11px; margin-right: -12px; height: 10em;"
    />
</div>
