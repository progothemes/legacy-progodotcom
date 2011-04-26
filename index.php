<?php
/**
 * The main template file.
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query. 
 * E.g., it puts together the home page when no home.php file exists.
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 *
 * @package ProGo
 * @subpackage ProGoDotCom
 * @since ProGoDotCom 1.0
 */
get_header(); ?>
<div id="pagetop">
<h1 class="page-title"><?php echo get_the_title( get_option('progo_blog_id') ); ?></h1>
<?php do_action('progo_pagetop'); ?>
</div>
<div id="container" class="container_12">
<div id="main" role="main" class="grid_8">
<?php
/* Run the loop to output the posts.
* If you want to overload this in a child theme then include a file
* called loop-index.php and that will be used instead.
*/
get_template_part( 'loop', 'index' );
?>
</div><!-- #main -->
<?php get_sidebar('blog'); ?>
</div><!-- #container -->
<?php get_footer(); ?>
