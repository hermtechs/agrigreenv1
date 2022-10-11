<?php

use CreativeMail\CreativeMail;

$available_integrations = CreativeMail::get_instance()->get_integration_manager()->get_active_plugins();
$activated_integrations = CreativeMail::get_instance()->get_integration_manager()->get_activated_integrations();

?>

<script type="application/javascript">
  function showConsentModal () {
    var form = document.getElementById("activated_plugins_form");
    var checkboxes = form.querySelectorAll("input[type='checkbox']:checked");
    if (checkboxes.length > 0) {
        document.getElementById('consent-modal').style.display = "block";
    } else {
        submitForm();
    }
  }

  function closeConsentModal () {
    document.getElementById('consent-modal').style.display = "none";
  }

  function submitForm() {
    document.getElementById('activated_plugins_form').submit()
  }
</script>

<p class="ce4wp-typography-root ce4wp-body2" style="color: rgba(0, 0, 0, 0.6);">
  <?= __( 'We will sync your contacts from the following plugins:', 'ce4wp') ?>
</p>

<form id="activated_plugins_form" name="plugins" action="" method="post">
  <input type="hidden" name="action" value="change_activated_plugins" />
  <ul style="color: rgba(0, 0, 0, 0.6);">
    <?php
    foreach ($available_integrations as $available_integration) {
        $active = in_array($available_integration, $activated_integrations);
        $checked = $active === true ? 'checked' : '';

        echo '<li><label class="ce4wp-checkbox"><input type="checkbox" name="activated_plugins[]" value="' . esc_attr($available_integration->get_slug()) . '" '.esc_attr($checked).' /><span>' . esc_html($available_integration->get_name()) . '</span></label></li>';
    }
    ?>
  </ul>
    <div class="ce-kvp">
    <input name="save_button" type="submit" class="ce4wp-button-text-primary ce4wp-right" id="save-activated-plugins" value="Save" onclick="showConsentModal(); return false;" />
    <!--  -->
  </div>

  <!-- Consent modal -->
  <div id="consent-modal" role="presentation" class="ce4wp-dialog-root" height="auto" variant="default" style="display: none;">
    <div class="ce4wp-backdrop-root" aria-hidden="true" style="opacity: 1; "></div>

    <div class="ce4wp-dialog-container" role="none presentation" tabindex="-1"
      style="opacity: 1; ">

      <div class="ce4wp-dialog-wrapper" role="dialog">
        <div width="100%" class="ce4wp-dialog-header">
          <div class="ce4wp-dialog-header-title">
            <div class="ce4wp-dialog-header-title-wrapper">
              <div class="ce4wp-dialog-header-title-wrapper-content">
                <h3 class="ce4wp-typography-root ce4wp-typography-h3"><?= __( 'Yes, these contacts expect to hear from me', 'ce4wp') ?></h3>
              </div>
            </div>
          </div>
          <div class="ce4wp-dialog-header-close">
            <div class="ce4wp-dialog-header-close-wrapper" onclick="closeConsentModal()">
              <div class="ce4wp-dialog-header-close-wrapper-button">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="black" xmlns="http://www.w3.org/2000/svg">
                  <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z" />
                </svg>
              </div>
            </div>
          </div>
        </div>
        <div height="auto" class="ce4wp-dialog-content">
          <div class="ce4wp-pb-3">
            <span><?= __( 'Each time you add contacts, they must meet the following conditions.', 'ce4wp') ?></span>
          </div>
          <div class="ce4wp-consent">
            <div class="ce4wp-pb-3">
              <h4 class="ce4wp-typography-root ce4wp-typography-h4"><?= __('I have the consent of each contact on my list', 'ce4wp') ?></h4>
              <span><?= __( 'You must have the prior consent of each contact added to your Constant Contact account. Your account cannot contain purchased, rented, third party or appended lists. In addition, you may not add auto-response addresses, transactional addresses, or user group addresses.', 'ce4wp') ?></span>
            </div>
            <h4 class="ce4wp-typography-root ce4wp-typography-h4"><?= __('I am not adding role addresses or distribution lists', 'ce4wp') ?></h4>
            <span><?= __( 'Role addresses, such as sales@ or marketing@, and distribution lists often mail to more than one person and result in higher than normal spam complaints. You must remove these from your list prior to upload.', 'ce4wp') ?></span>
          </div>
          <div class="ce4wp-pb-3">
            <span><?= __('Getting your email delivered is important to us. We may contact you to review your list before we send your email, if you add contacts that are likely to cause higher than normal bounces or for other reasons that we know may cause spam complaints. Thanks for helping to eliminate spam.', 'ce4wp') ?></span>
          </div>
        </div>
        <div class="ce4wp-dialog-footer">
          <div class="ce4wp-dialog-footer-close">
            <div class="ce4wp-dialog-footer-close-wrapper">
              <button class="ce4wp-button-base-root ce4wp-button-root ce4wp-button-contained ce4wp-button-contained-primary" type="button" onclick="submitForm()" >
                <span class="MuiButton-label"><?= __( 'Got it!', 'ce4wp') ?></span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</form>

