<!-- Action Header -->
<div class="postbox">
	<h3 class="hndle">
		<?php echo _e( 'Settings', $this->base->plugin->name ); ?>
	</h3>

    <!-- Account Enabled -->
    <div class="option">
        <label for="<?php echo $profile_id; ?>_enabled">
            <div class="left">
                <strong><?php _e( 'Account Enabled', $this->base->plugin->name ); ?></strong>
            </div>
            <div class="right">
                <input type="checkbox" id="<?php echo $profile_id; ?>_enabled" name="<?php echo $this->base->plugin->name; ?>[<?php echo $profile_id; ?>][enabled]" id="<?php echo $profile_id; ?>_enabled" value="1"<?php checked( $this->get_setting( $post_type, '[' . $profile_id . '][enabled]', 0 ), 1, true ); ?> />
                <p class="description"><?php _e( 'Enabling this social media account means that Posts will be sent to this social media account, if the conditions in the Settings are met.', $this->base->plugin->name ); ?></p>
            </div>
        </label>
    </div>
</div>