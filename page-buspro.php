<?php
/**
 * Template Name: Business PRO One Pager
 *
 * @package ProGo
 * @subpackage ProGoDotCom
 * @since ProGoDotCom 1.0
 */

get_header('buspro');
?>
<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>
<?php the_content(); ?>
<?php endwhile; ?>
<div><div><div>
<?php get_footer(); ?>
