/**
 * Feedback notice javascript.
 *
 * @package CreativeMail
 */
jQuery(function($){

    function hideAdminFeedbackNotice () {
        document.querySelector('#ce4wp-admin-feedback-notice').hidden = true;
    }

    window.addEventListener('load', () => {
        const parent = document.getElementById('wpbody-content')
        const screenMetaLinks = document.getElementById('screen-meta-links')

        const notice = document.getElementById('ce4wp-admin-feedback-notice')
        parent.insertBefore(notice, screenMetaLinks.nextSibling)
        notice.hidden = false
    });

});