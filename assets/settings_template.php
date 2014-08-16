<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top" style="float:right">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="hosted_button_id" value="YPUHSQ2G3YHQQ">
<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
</form>
<?php
$tab = isset( $_GET['tab'] ) ? $_GET['tab'] : $this->general_settings_key;
?>
<div class="wrap" id="youtube_channel_settings">
<h2><?php _e(sprintf('%s Settings',YTCNAME),YTCTDOM); ?></h2>
    <?php $this->options_tabs(); ?>
    <form method="post" action="options.php">
    <?php wp_nonce_field( 'update-options' ); ?>
    <?php settings_fields( $tab ); ?>
    <?php do_settings_sections( $tab ); ?>
    <?php submit_button(); ?>
    </form>
</div>
