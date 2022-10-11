/**
 * WooCommerce notice javascript.
 *
 * @package CreativeMail
 */

jQuery(function($){

    window.addEventListener('load', () => {
        const parent = document.getElementById('woocommerce-layout__primary')
        const wooNoticeList = document.getElementById('woocommerce-layout__notice-list')

        const notice = document.getElementById('ce4wp-wc-dashboard-notice')

        if ([parent, wooNoticeList, notice].some(element => element == null)) {
            return
        }

        parent.insertBefore(notice, wooNoticeList);
    });

});

function hideAdminWooCommerceNotice (banner) {
    const el = document.querySelector('#ce4wp-wc-dashboard-notice')
        el.hidden = true;
        el.style.display = "none";

    const { hide_banner_url } = ce4wp_data
    fetch(`${hide_banner_url}${banner}`, { method: 'POST' })
}
