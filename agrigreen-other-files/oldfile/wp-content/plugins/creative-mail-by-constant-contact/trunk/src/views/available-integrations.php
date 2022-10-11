<?php

use CreativeMail\CreativeMail;

$supported_integrations = CreativeMail::get_instance()->get_integration_manager()->get_supported_integrations();

?>

<p class="ce4wp-typography-root ce4wp-body2" style="color: rgba(0, 0, 0, 0.6);">
    <?= __( 'We couldn\'t find any plugins that we support.', 'ce4wp') ?> <br/>
    <?= __( 'In order to help you sync your contacts to Creative Mail we have build integrations with the following plugins:', 'ce4wp') ?>
</p>
<ul style="color: rgba(0, 0, 0, 0.6);">
    <?php
    foreach ($supported_integrations as $supported_integration) {
        if ($supported_integration->is_hidden_from_suggestions()) {
            continue;
        }
        echo '<li>- ' . esc_html($supported_integration->get_name()) . '</li>';
    }
    ?>
</ul>
