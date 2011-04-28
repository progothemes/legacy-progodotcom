<?php
/**
 * The Sidebar containing the primary and secondary widget areas.
 *
 * @package ProGo
 * @subpackage ProGoDotCom
 * @since ProGoDotCom 1.0
 */
?>
<div class="grid_4">
<div id="secondary">
<?php
/* When we call the dynamic_sidebar() function, it'll spit out
 * the widgets for that widget area. If it instead returns false,
 * then the sidebar simply doesn't exist, so we'll hard-code in
 * some default sidebar stuff just in case.
 */
if ( is_page('checkout') ) {
	dynamic_sidebar('checkout');
} else {
if ( ! dynamic_sidebar( 'main' ) ) :
// do SHOPPING CART widget ?
?>
 <?php endif; // end primary widget area
}
?>
</div>
</div>