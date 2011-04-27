<?php
/**
 * The Header for our theme.
 *
 * Displays all of the <head> section and everything up till <div id="main">
 *
 * @package ProGo
 * @subpackage ProGoDotCom
 * @since ProGoDotCom 1.0
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>" />
<title><?php
	/*
	 * Print the <title> tag based on what is being viewed.
	 */
	global $page, $paged;

	wp_title( '|', true, 'right' );

	// Add the blog name.
	bloginfo( 'name' );

	// Add the blog description for the home/front page.
	$site_description = get_bloginfo( 'description', 'display' );
	if ( $site_description && ( is_home() || is_front_page() ) )
		echo " | $site_description";

	// Add a page number if necessary:
	if ( $paged >= 2 || $page >= 2 )
		echo ' | ' . sprintf( __( 'Page %s', 'twentyten' ), max( $paged, $page ) );

	?></title>
<link rel="profile" href="http://gmpg.org/xfn/11" />
<link rel="stylesheet" type="text/css" media="all" href="<?php bloginfo( 'stylesheet_url' ); ?>" />
<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />
<meta property="og:image" content="<?php bloginfo( 'template_url' ); ?>/images/logo.png"/>
<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>><div id="fx">
<div id="wrap" class="container_12">
	<div id="page" class="container_12">
        <div id="hdr" class="container_12">
        	<div class="grid_6 alpha">
            <?php progo_sitelogo();
            $options = get_option( 'progo_options' );
            if ( (int) $options['showdesc'] == 1 ) { ?>
            <div id="slogan"><?php bloginfo( 'description' ); ?></div>
            <?php } ?>
            </div>
            <?php get_sidebar('header');
			wp_nav_menu( array( 'container' => 'false', 'theme_location' => 'mainmenu', 'menu_id' => 'nav' ) ); ?>
        </div>
