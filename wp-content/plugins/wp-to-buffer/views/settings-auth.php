<div class="postbox panel">
    <h3 class="hndle"><?php _e( 'Buffer Authentication', $this->base->plugin->name ); ?></h3>
    
	<?php
    $access_token = $this->get_setting( '', 'access_token' );
    if ( ! empty ( $access_token ) ) {
        // Already authenticated
        ?>
        <div class="option">
            <?php _e( 'Thanks - you\'ve authorized the plugin to post updates to your Buffer account.', $this->base->plugin->name ); ?>
        </div>
        <div class="option">
            <a href="admin.php?page=<?php echo $this->base->plugin->name; ?>-settings&amp;wp-to-buffer-pro-disconnect=1" class="button button-red">
                <?php _e( 'Deauthorize Plugin', $this->base->plugin->name ); ?>
            </a>
        </div>
        <?php
    } else {
        // Need to authenticate with Buffer
        ?>
        <div class="option">
            <p class="description">
                <?php _e( 'To allow this Plugin to post updates to your Buffer account, please authorize it by clicking the button below.', $this->base->plugin->name ); ?>
            </p>
        </div>
        <div class="option">
            <?php
            if ( isset( $oauth_url ) ) {
                ?>
                <a href="<?php echo $oauth_url; ?>" class="button button-primary">
                    <?php _e( 'Authorize Plugin', $this->base->plugin->name ); ?>
                </a>
                <?php
            } else {
                echo sprintf( __( 'We\'re unable to fetch the oAuth URL needed to begin authorization with Buffer.  Please <a href="%s" target="_blank">contact us for support</a>.', $this->base->plugin->name ), 'https://www.wpzinc.com/support' );
            }
            ?>
        </div>
        <?php
    }
    ?>
</div>

<div class="postbox panel">
    <h3 class="hndle"><?php _e( 'Logging', $this->base->plugin->name ); ?></h3>

    <div class="option">
        <div class="left">
            <strong><?php _e( 'Enable Logging?', $this->base->plugin->name ); ?></strong>
        </div>
        <div class="right">
            <input type="checkbox" name="log" value="1" <?php checked( $this->get_setting( '', 'log' ), 1 ); ?> />
        </div>
        <div class="full">
            <p class="description">
                <?php _e( 'If enabled, each Post will display Log information detailing what information was sent to Buffer, and the response received. As this dataset can be quite large, we only recommend this be enabled when troubleshooting issues.', $this->base->plugin->name ); ?>
            </p>
        </div>
    </div>
</div>