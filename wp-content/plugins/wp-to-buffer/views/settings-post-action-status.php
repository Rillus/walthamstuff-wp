<div class="option sortable<?php echo ( $key == 0 ? ' first' : '' ); ?>" data-status-index="<?php echo $key; ?>">
    <!-- Count + Delete -->
    <div class="left number">
        <a href="#" class="count" title="<?php _e( 'Drag status to reorder', $this->base->plugin->name ); ?>">#<?php echo ( $key + 1 ); ?></a>
        <a href="#" class="dashicons dashicons-trash delete" title="<?php _e( 'Delete Condition', $this->base->plugin->name ); ?>"></a>
    </div>

    <!-- Status -->
    <div class="right status">
        <!-- Tags and Feat. Image -->
        <div class="full">
            <!-- Tags -->
            <select size="1" class="left tags">
                <option value=""><?php _e( '--- Insert Tag ---', $this->base->plugin->name ); ?></option>
                <?php
                foreach ( $tags as $tag_group => $tag_group_tags ) {
                    ?>
                    <optgroup label="<?php echo $tag_group; ?>">
                        <?php
                        foreach ( $tag_group_tags as $tag => $tag_label ) {
                            ?>
                            <option value="<?php echo $tag; ?>"><?php echo $tag_label; ?></option>
                            <?php
                        }
                        ?>
                    </optgroup>
                    <?php
                }
                ?>
            </select>
        </div>

        <!-- Status Message -->
        <div class="full">
        	<textarea name="<?php echo $this->base->plugin->name; ?>[<?php echo $profile_id; ?>][<?php echo $action; ?>][status][message][]" class="widefat" style="width:100%"><?php echo $this->get_setting( $post_type, '[' . $profile_id . '][' . $action . '][status][' . $key . '][message]', $this->base->plugin->publish_default_status ); ?></textarea>
        </div>

        <!-- Scheduling -->
        <div class="full">
            <select name="<?php echo $this->base->plugin->name; ?>[<?php echo $profile_id; ?>][<?php echo $action; ?>][status][schedule][]" size="1" data-conditional="schedule" data-conditional-value="custom">
                <?php
                foreach ( $schedule as $schedule_option => $label ) {
                    ?>
                    <option value="<?php echo $schedule_option; ?>"<?php selected( $this->get_setting( $post_type, '[' . $profile_id . '][' . $action . '][status][' . $key . '][schedule]', '' ), $schedule_option ); ?>><?php echo $label; ?></option>
                    <?php
                }
                ?>
            </select>
        </div>
    </div>
</div>