<?php
/**
 * The Header for our theme.
 *
 * Displays all of the <head> section and everything up till <div id="main">
 *
 * @package ProGo
 * @subpackage Ecommerce
 * @since Ecommerce 1.0
 */
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php bloginfo( 'charset' ); ?>" />
<title><?php
	/*
	 * Print the <title> tag based on what is being viewed.
	 */
	global $page, $paged;

	wp_title( '|', true, 'right' );

	// Add the blog name.
	bloginfo( 'name' );

	// Add the blog description for the home/front page.
	$site_description = get_bloginfo( 'description' );
	if ( $site_description && ( is_home() || is_front_page() ) )
		echo " | $site_description";

	// Add a page number if necessary:
	if ( $paged >= 2 || $page >= 2 )
		echo ' | ' . sprintf( __( 'Page %s', 'twentyten' ), max( $paged, $page ) );

	?></title>
<link rel="profile" href="http://gmpg.org/xfn/11" />
<link rel="stylesheet" type="text/css" media="all" href="<?php bloginfo( 'stylesheet_url' ); ?>" />
<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />
<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<div id="hrap">
	<div id="top">
        <div class="inside">
            <span class="alignleft">Need Help? <a href="/forums/forum/progo-support/">Visit the Forum.</a></span>
            <!--span class="alignright"><a href="#">Email Us</a> or <strong>Give Us a Call at (800) XXX-XXXX</strong></span-->
        </div>
    </div>
    <div id="hdr">
    	<div class="inside">
            <a href="<?php bloginfo('url') ?>" id="logo"><? bloginfo( 'name' ) ?></a>
            <div id="slogan"><?php bloginfo( 'description' ); ?></div>
            <?php /*
            <ul id="nav">
                <li><a href="#fb">Facebook<br />Pages</a></li>
                <li><a href="#ppc">PPC<br />Pages</a></li>
                <li><a href="#leads">Lead Forms<br />&amp; Funnels</a></li>
                <li><a href="#local">Google<br />Local</a></li>
            </ul>
            <ul class="btns">
                <li><a href="#">Demo</a></li>
                <li><a href="#" class="buy">Buy</a></li>
            </ul>
			*/ ?>
        </div>
    </div>
</div>
<div id="fx">
<div id="wrap" class="container_12">
	<div id="page" class="container_12">
