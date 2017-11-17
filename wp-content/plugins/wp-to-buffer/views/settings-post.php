<?php
/**
* Outputs Settings View for a Post Type
*
* @since 3.0
*/
?>
	
<!-- Post Type -->
<div id="<?php echo $post_type; ?>-panel" class="panel">
	
	<!-- Second level tabs -->
	<h3 class="nav-tab-wrapper needs-js" data-panel="sub-panel">  
		<!-- Default Settings -->
		<a href="#<?php echo $post_type; ?>-default" class="nav-tab nav-tab-active">
			<?php _e( 'Defaults', $this->base->plugin->name ); ?><br /><br />
		</a>
	                        			
		<?php
        // Account tabs
        foreach ( $profiles as $key => $profile ) {
           ?>
            <a href="#<?php echo $post_type; ?>-<?php echo $profile['id']; ?>" class="nav-tab image" title="<?php echo $profile['formatted_service'] . ': ' . $profile['formatted_username']; ?>" data-tooltip="<?php echo $profile['formatted_service'] . ': ' . $profile['formatted_username']; ?>">
                <img src="<?php echo $profile['avatar']; ?>" width="48" height="48" alt="<?php echo $profile['formatted_username']; ?>" />
                <span class="<?php echo $profile['service']; ?>"></span>  
            </a>
            <?php 
        }
        unset( $profile );
    	?>
	</h3>
	
	<!-- Defaults -->
    <?php
    $profile_id = 'default';
    ?>
	<div id="<?php echo $post_type; ?>-<?php echo $profile_id; ?>-panel" class="sub-panel">
        <?php
        // Iterate through Post Actions (Publish, Update etc)
        foreach ( $post_actions as $action => $action_label ) {
            require( $this->base->plugin->folder . 'views/settings-post-action.php' );
        }
        ?>
    </div>
    <!-- /Defaults -->

    <!-- Profiles -->
    <?php
    foreach ( $profiles as $key => $profile ) {
        $profile_id = $profile['id'];
        ?>
        <div id="<?php echo $post_type; ?>-<?php echo $profile_id; ?>-panel" class="sub-panel">
            <?php
            require( $this->base->plugin->folder . 'views/settings-post-actionheader.php' );
            ?>
        </div>
        <?php
    }
    ?>
    <!-- /Profiles -->
</div>
<!-- /post_type -->