<?php
/**
 * Template Name: One Column
 *
 * @package ProGo
 * @subpackage ProGoDotCom
 * @since ProGoDotCom 1.0
 */

get_header();
?>
<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>
<div id="pagetop">
<h1 class="page-title"><?php the_title(); ?></h1>
<?php do_action('progo_pagetop'); ?>
</div>
<div id="container" class="container_12">
<div id="main" class="grid_12">
<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
<div class="entry">
<?php the_content(); ?>
</div><!-- .entry -->
</div><!-- #post-## -->
</div><!-- #main -->
<?php endwhile; ?>
</div><!-- #container -->
<?php get_footer(); ?>
