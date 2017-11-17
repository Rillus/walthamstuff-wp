<?php
/**
 * The header for our theme.
 *
 * Displays all of the <head> section and everything up till <div id="content">
 *
 * @package Poseidon
 */

?><!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="profile" href="http://gmpg.org/xfn/11">
<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">

<?php wp_head(); ?>

<?php
#twitter cards hack
if(is_single() || is_page()) {
   $twitter_url    = get_permalink();
   $twitter_title  = get_the_title();
   $twitter_desc   = esc_html(get_the_content());
   $twitter_thumbs = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), full );
    $twitter_thumb  = $twitter_thumbs[0];
      if(!$twitter_thumb) {
      $twitter_thumb = 'http://walthamstuff.com/wp-content/uploads/2016/11/Walthamstuff-logo-twittercard.jpg';
    }
  $twitter_name   = str_replace('@', '', get_the_author_meta('twitter'));
?>
<meta name="twitter:card" value="summary_large_image" />
<meta name="twitter:url" value="<?php echo $twitter_url; ?>" />
<meta name="twitter:title" value="<?php echo $twitter_title; ?> - Walthamstuff" />
<meta name="twitter:description" value="<?php echo $twitter_desc; ?>" />
<meta name="twitter:image" value="<?php echo $twitter_thumb; ?>" />
<meta name="twitter:site" value="@walthamstuff" />
<?
  if($twitter_name) {
?>
<meta name="twitter:creator" value="@<?php echo $twitter_name; ?>" />
<?
  }
}
?>
</head>

<body <?php body_class(); ?>>

	<div id="page" class="hfeed site">

		<a class="skip-link screen-reader-text" href="#content"><?php esc_html_e( 'Skip to content', 'poseidon' ); ?></a>

		<div id="header-top" class="header-bar-wrap"><?php do_action( 'poseidon_header_bar' ); ?></div>

		<header id="masthead" class="site-header clearfix" role="banner">

			<div class="header-main container clearfix">

				<div id="logo" class="site-branding clearfix">

					<?php poseidon_site_logo(); ?>
					
				</div><!-- .site-branding -->
				<?php poseidon_site_title(); ?>
				<nav id="main-navigation" class="primary-navigation navigation clearfix" role="navigation">
					<?php
						// Display Main Navigation.
						wp_nav_menu( array(
							'theme_location' => 'primary',
							'container' => false,
							'menu_class' => 'main-navigation-menu',
							'echo' => true,
							'fallback_cb' => 'poseidon_default_menu',
							)
						);
					?>
				</nav><!-- #main-navigation -->

			</div><!-- .header-main -->

		</header><!-- #masthead -->

		<?php poseidon_header_image(); ?>

		<?php poseidon_slider(); ?>

		<?php poseidon_breadcrumbs(); ?>

		<div id="content" class="site-content container clearfix">
