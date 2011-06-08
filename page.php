<?php
/**
 * The template for displaying all pages.
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site will use a
 * different template.
 *
 * @package ProGo
 * @subpackage ProGoDotCom
 * @since ProGoDotCom 1.0
 */

get_header();
?>
<?php if ( have_posts() ) while ( have_posts() ) : the_post();
if(is_singular('wpsc-product')) { ?>
<div id="container" class="container_12"><!-- progotemplate: page.php wpsc-product -->
<div id="main" class="grid_12">
<?php } else { ?>
<div id="pagetop">
<h1 class="page-title"><?php the_title(); ?></h1>
<?php do_action('progo_pagetop'); ?>
</div>
<div id="container" class="container_12"><!-- progotemplate: page.php -->
<div id="main" class="grid_8">
<?php } ?>
<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
<div class="entry">
<?php the_content(); ?>
</div><!-- .entry -->
</div><!-- #post-## -->
</div><!-- #main -->
<?php endwhile;
if(is_singular('wpsc-product') == false) {
get_sidebar();
} ?>
</div><!-- #container -->
<?php get_footer(); ?>
