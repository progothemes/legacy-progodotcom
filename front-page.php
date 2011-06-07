<?php
/**
 * @package ProGo
 * @subpackage ProGoDotCom
 * @since ProGoDotCom 1.0
 */
 if(!is_user_logged_in()) { ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>ProGo | Coming Soon</title>
<style type="text/css">
body { margin: 0; padding: 0; min-width: 960px; background: #ec7d1b url(/soon.jpg) no-repeat 50% 0; min-height: 480px }
</style>
</head>

<body>
</body>
</html><?php } else {
get_header();
global $wp_query, $post;
$options = get_option( 'progo_options' );
?>
<div id="htop">
    <h1>High Performance <em>WordPress Themes</em></h1>
    <a href="http://www.progo.com/themes/">Shop Themes</a>
</div>
<div id="pagetop" class="slides">
<?php
$original_query = $wp_query;
$slides = (array) get_option( 'progo_slides' );
unset($slides['count']);
$count = count($slides);
$oneon = false;
$stitles = array();

$marketing_terms = get_option( 'progo_pmm_terms' );
for ( $i = 0; $i < $count; $i++ ) {
	$show = $slides[$i]['show'];
	$on = ' s'. $i;
	if($oneon == false && $show != 'new') {
		$oneon = true;
		$on .= ' on';
	}
	$stitles[] = $slides[$i]['title'];
	switch($show) {
		case 'text':
			echo "<div class='slide$on page-title'>". wp_kses($slides[$i]['text'],array()) ."</div>";
			break;
		case 'image':
			echo "<div class='slide$on image'><img src='". esc_url($slides[$i]['image']) ."' width='988' height='424' alt='". $slides[$i]['title'] ."' /></div>";
			break;
		case 'product':
			$oldpost = $post;
			wpsc_the_product();
			echo "<div class='slide$on product'>";
			$post = get_post($slides[$i]['product']);
			$excerpt = $post->post_content;
			$excerpt = substr( $excerpt, 0, strpos( $excerpt, '<!--more-->' ) );
			echo '<div class="desc">'. esc_html($excerpt) .'</div>';
			
			$custom = get_post_meta($post->ID,'_progo_pmm');
			$pmm_ratings = $custom[0];
			if(is_array($pmm_ratings)) {
				unset($pmm_ratings['arrowd']);
				echo '<div class="meter p'. $post->ID .'"><strong>Performance Marketing Meter</strong><ul>';
				$c = 0;
				foreach( $pmm_ratings as $k => $v ) {
					if ( $c < 5 ) {
						echo '<li><span>'. $marketing_terms[$k] .'</span><span class="stars">';
						for($j=0; $j<5; $j++) {
							if($j < $v) {
							echo '<span class="on">*</span>';
							} else {
							echo '<span></span>';
							}
						}
						echo '</span></li>';
					}
					$c++;
				}
				echo '</ul></div>';
			}
			if(wpsc_product_external_link(wpsc_the_product_id()) != '') {
				$action =  wpsc_product_external_link(wpsc_the_product_id());
			} else {
				$action = htmlentities(wpsc_this_page_url(), ENT_QUOTES, 'UTF-8' );
			}
			?>
			<form class="product_form"  enctype="multipart/form-data" action="<?php echo $action; ?>" method="post" name="product_<?php echo wpsc_the_product_id(); ?>f" id="product_<?php echo wpsc_the_product_id(); ?>f" >
                        <?php if (wpsc_have_variation_groups()) {
							echo '<a href="'. wpsc_the_product_permalink() .'" class="morebutton">Buy</a>'; 
						} else { ?>
							<input type="hidden" value="add_to_cart" name="wpsc_ajax_action"/>
							<input type="hidden" value="<?php echo wpsc_the_product_id(); ?>" name="product_id"/>
					
											<?php if(wpsc_product_external_link(wpsc_the_product_id()) != '') { ?>
											<input class="wpsc_buy_button" type="submit" value="<?php echo wpsc_product_external_link_text( wpsc_the_product_id(), __( 'Buy Now', 'wpsc' ) ); ?>" onclick="return gotoexternallink('<?php echo $action; ?>', '<?php echo wpsc_product_external_link_target( wpsc_the_product_id() ); ?>')">
											<?php } else { ?>
										<input type="submit" value="<?php _e('Buy Now', 'wpsc'); ?>" name="Buy" class="wpsc_buy_button" id="product_<?php echo wpsc_the_product_id(); ?>f_submit_button"/>
											<?php }
											} ?>
						</form><!--close product_form-->
            <?php
			echo "</div>";
			$post = $oldpost;
			break;
	}
}
if ( $oneon == true && $count > 1 ) { ?>
<div class="ar"><a href="#p" title="Previous Slide" class="r"></a><?php
$firston = true;
foreach($stitles as $k => $v ) {
	echo '<a href="#s'. $k .'" class="s s'. $k . ($firston ? ' on here' : '') .'"><span class="on">'. $v .'</span><span class="off">'. $v .'</span></a>';
	$firston = false;
}
?><a href="#n" class="r n" title="Next Slide"></a></div>
<script type="text/javascript">
progo_timing = <?php $hsecs = absint($options['homeseconds']); echo $hsecs > 0 ? $hsecs * 1000 : "0"; ?>;
</script>
<?php
}
do_action('progo_pagetop'); ?>
</div>
<div id="container" class="container_12">
<div id="main" role="main" class="grid_12">
<?php
rewind_posts();
switch ( $options['frontpage'] ) {
	case 'featured':
		$sticky_array = get_option( 'sticky_products' );
		if ( !empty( $sticky_array ) ) {
			$old_query = $wp_query;
			$wp_query = new WP_Query( array(
						'post__in' => $sticky_array,
						'post_type' => 'wpsc-product',
						'numberposts' => -1,
						'order' => 'ASC'
					) );
					
		
				$GLOBALS['nzshpcrt_activateshpcrt'] = true;
				$image_width = get_option( 'product_image_width' );
				$image_height = get_option( 'product_image_height' );
				$featured_product_theme_path = wpsc_get_template_file_path( 'wpsc-products_page.php' );
		ob_start();
			include_once($featured_product_theme_path);
			$is_single = false;
			$output .= ob_get_contents();
			ob_end_clean();
			
				//Begin outputting featured product.  We can worry about templating later, or folks can just CSS it up.
				echo $output;
				//End output
				
				$wp_query = $old_query;
		}
		break;
	case 'posts':
		get_template_part( 'loop', 'index' );
		break;
	case 'page':
		if ( have_posts() ) while ( have_posts() ) : the_post(); ?>
		<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
		<div class="entry">
		<?php the_content(); ?>
		</div><!-- .entry -->
		</div><!-- #post-## -->
		<?php
		endwhile;
		break;
}
?>
</div><!-- #main -->
</div><!-- #container -->
<?php get_footer(); 
 }
?>