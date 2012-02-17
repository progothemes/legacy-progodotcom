<?php
/**
 * @package ProGo
 * @subpackage ProGoDotCom
 * @since ProGoDotCom 1.0
 *
 * Defines all the functions, actions, filters, widgets, etc., for ProGoDotCom theme.
 */

$content_width = 594;

/** Tell WordPress to run progo_setup() when the 'after_setup_theme' hook is run. */
add_action( 'after_setup_theme', 'progo_setup' );

if ( ! function_exists( 'progo_setup' ) ):
/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * @uses register_nav_menus() To add support for navigation menus.
 * @uses add_custom_background() To add support for a custom background.
 * @uses add_theme_support( 'post-thumbnails' ) To add support for post thumbnails.
 *
 * @since ProGoDotCom 1.0
 */
function progo_setup() {
	// This theme styles the visual editor with editor-style.css to match the theme style.
	add_editor_style( 'css/editor-style.css' );
	
	// This theme uses wp_nav_menu() in one location.
	register_nav_menus( array(
		'mainmenu' => 'Main Menu',
		'footer' => 'Footer Links'
	) );
	
	// This theme uses post thumbnails
	add_theme_support( 'post-thumbnails' );
	add_image_size( 'large', 596, 397, true );
	add_image_size( 'prodL', 290, 290, true );
	add_image_size( 'prod3', 190, 190, true );
	add_image_size( 'thm', 70, 70, true );
	
	// add custom actions
	add_action( 'admin_init', 'progo_admin_init' );
	add_action( 'widgets_init', 'progo_dotcom_widgets' );
	add_action( 'admin_menu', 'progo_admin_menu_cleanup' );
	add_action( 'login_head', 'progo_custom_login_logo' );
	add_action( 'login_headerurl', 'progo_custom_login_url' );
	add_action( 'wp_print_scripts', 'progo_add_scripts' );
	add_action( 'wp_print_styles', 'progo_add_styles' );
	add_action( 'admin_notices', 'progo_admin_notices' );
	add_action( 'wp_before_admin_bar_render', 'progo_admin_bar_render' );
	add_action( 'progo_pre_gateways', 'progodotcom_gatewaycleanup' );
	add_action( 'save_post', 'progo_save_meta' );
	add_action( 'get_header', 'progo_header_check' );
	
	remove_action( 'wp_head', 'st_widget_head' );
	add_action( 'wp_head', 'progo_st_widget_head' );
	
	// add custom filters
	add_filter( 'body_class', 'progo_bodyclasses' );
	add_filter( 'wp_nav_menu_objects', 'progo_menuclasses' );
	add_filter( 'wpsc_pre_transaction_results', 'progo_prepare_transaction_results' );
	add_filter( 'wp_mail_content_type', 'progo_mail_content_type' );
	add_filter( 'wp_mail', 'progodotcom_mail' );
	add_filter( 'custom_menu_order', 'progo_admin_menu_order' );
	add_filter( 'menu_order', 'progo_admin_menu_order' );
	add_filter( 'wpsc_transaction_result_content', 'progo_transaction_keycheck', 20, 3 );
}
endif;
if ( ! function_exists( 'progodotcom_init' ) ):
function progodotcom_init() {
	$labels = array(
		'name' => _x( 'Performance Marketing Meter', 'taxonomy general name' ),
		'singular_name' => _x( 'Area', 'taxonomy singular name' ),
		'search_items' =>  __( 'Search Areas' ),
		'popular_items' => __( 'Popular Areas' ),
		'all_items' => __( 'All Areas' ),
		'parent_item' => null,
		'parent_item_colon' => null,
		'edit_item' => __( 'Edit Area' ), 
		'update_item' => __( 'Update Area' ),
		'add_new_item' => __( 'Add New Area' ),
		'new_item_name' => __( 'New Area Name' ),
		'separate_items_with_commas' => __( 'Separate areas with commas' ),
		'add_or_remove_items' => __( 'Add or remove areas' ),
		'choose_from_most_used' => __( 'Choose from the most used areas' ),
		'menu_name' => __( 'Marketing Meter' ),
	); 
	
	register_taxonomy('progo_pmm','wpsc-product',array(
		'hierarchical' => false,
		'labels' => $labels,
		'show_ui' => true
	));
  
  
	if(class_exists('MultiPostThumbnails')) {
		$thumb = new MultiPostThumbnails(array(
			'label' => 'Product Details Top Image',
			'id' => 'pbg-image',
			'post_type' => 'wpsc-product'
		));
	}
}
add_action( 'init', 'progodotcom_init', 0 );
endif;

/********* Front-End Functions *********/
if ( ! function_exists( 'progo_direct_submitbtn' ) ):
/**
 * helper function to allow children themes to overwrite the SUBMIT button html on Direct Response Pages
 * @param (int) ID of Product
 * @param text for the button
 * @return HTML for the submit button
 * @since Direct 1.0.45
 */
function progo_direct_submitbtn( $pid=0, $btxt = 'BUY NOW' ) {
	return function_exists('wpsc_have_checkout_items') ? '<input type="submit" id="product_'. absint($pid) .'_submit_button" name="Buy" value="'. esc_html($btxt) .'" class="buynow sbtn" />' : '';
}
endif;
if ( ! function_exists( 'progo_posted_on' ) ):
/**
 * Prints HTML with meta information for the current post—date/time and author.
 * @since ProGoDotCom 1.0
 */
function progo_posted_on() {
	echo 'Posted by '. get_the_author() .' on '. get_the_date() .'<br />';
}
endif;
if ( ! function_exists( 'progo_posted_in' ) ):
/**
 * Prints HTML with meta information for the current post (category, tags and permalink).
 * @since ProGoDotCom 1.0
 */
function progo_posted_in() {
	/* Retrieves tag list of current post, separated by commas.
	$tag_list = get_the_tag_list( '', ', ' );
	*/
	echo 'Topics : '. get_the_category_list( ', ' );
}
endif;
if ( ! function_exists( 'progo_productimage' ) ):
/**
 * echoes html for product image, or default product image if there isnt one
 * @since ProGoDotCom 1.0
 */
function progo_productimage($pID) {
	if(($pID==0) || has_post_thumbnail( $pID ) == false) {
		echo '<img src="'. get_bloginfo('template_url') .'/images/productimage.gif" alt="Product Image" />';
	} else {
		echo get_the_post_thumbnail( $pID, 'original');
	}
}
endif;
if ( ! function_exists( 'progo_prepare_transaction_results' ) ):
/**
 * filter for wpsc_pre_transaction_results
 * @since ProGoDotCom 1.0
 */
function progo_prepare_transaction_results() {
	global $purchase_log;
	$options = get_option( 'progo_options' );
	$purchase_log['find_us'] = '<table class="wpsc_checkout_table"><tr class="firstrow"><td><h4>Our Company Info</h4></td></tr><tr><td>'. esc_html( $options['companyinfo'] ) .'</td></tr></table>';
}
endif;
if ( ! function_exists( 'progo_direct_form_fields' ) ):
/**
 * outputs the BILLING/SHIPPING ADD TO CART form on the top right of DIRECT RESPONSE pages
 * @param whether or not to include the SHIPPING fields
 * @param whether or not to hide (display:none) both fieldsets
 * @since Direct 1.0.59
 */
function progo_direct_form_fields( $hideboth = false ) {
	if ( ! function_exists('wpsc_have_checkout_items') ) {
		if( current_user_can( 'activate_plugins' ) ) {
			echo '<h3>Please Install/Activate the WP E-Commerce Plugin</h3><br /><br /><a href="'. get_bloginfo('url') .'/wp-admin/plugins.php" class="sbtn buynow">Manage Plugins</a>';
		}
		return;
	}
	
	global $wpsc_checkout;
	$wpsc_checkout = new wpsc_checkout();
	$formfields = array(
		'billing' => array() /*,
		'shipping' => array() */
	);
	foreach ( $wpsc_checkout->checkout_items as $check ) {
		if( strpos($check->unique_name, 'billing') === 0 ) {
			$formfields[billing][] = $check;
		} /* elseif( strpos($check->unique_name, 'shipping') === 0 ) {
			$formfields[shipping][] = $check;
		} */
	}
	
	foreach($formfields as $k => $fieldset) {
		if ( $k == 'billing' ) {
			echo '<fieldset id="billing"'. ($hideboth ? ' style="display:none"' : '') .'>';
		} /*else {
			 if ( $includeshipping == false ) return;
			 ?>
				<fieldset id="shipping" style="display:none">
				<div class="inf">Shipping Info</div><?php
		}*/
	
		$shortenzip = false;
		//echo '<pre style="display:none">'. print_r($wpsc_checkout, true) .'</pre>';
	foreach( $fieldset as $i => $item ) {
		if ( $item->type == 'address' ) $item->type = 'text';
		$wpsc_checkout->checkout_item = $item;
		if(wpsc_disregard_shipping_state_fields() || wpsc_disregard_billing_state_fields()) { ?><div style="display:none">
			 <label for='<?php echo wpsc_checkout_form_element_id(); ?>'>
			 <?php echo wpsc_checkout_form_name();?>
			 </label>
			 <?php echo wpsc_checkout_form_field();?>
			  <?php if(wpsc_the_checkout_item_error() != ''): ?>
					 <p class='validation-error'><?php echo wpsc_the_checkout_item_error(); ?></p>
			 <?php endif; ?></div>
		<?php
        } else {
		if( (strpos($item->unique_name, 'postcode') > 0) && $shortenzip ) {
				echo '<span class="zip">';
			}
		?>
		<label for='<?php echo wpsc_checkout_form_element_id(); ?>'>
		<?php echo str_replace( 'Postal Code', 'Zip', wpsc_checkout_form_name() );?>
		</label>
		<?php
		
		$fieldcode = wpsc_checkout_form_field();
		
		if ( in_array($item->type, array( 'country', 'delivery_country' ) ) ) {
			// add extra STATE label
			$lookfor = "<div id='region_select_".$item->id."'><select";
			$statestart = strpos($fieldcode, $lookfor);
			if( $statestart > 0 ) {
				
				$selected_country = $_SESSION['wpsc_delivery_country'];
				$selected_region = $_SESSION['wpsc_delivery_region'];
			echo "<div style='display:none' title='sc $selected_country sr $selected_region'></div>";
				if ( empty( $selected_country ) )
					$selected_country = esc_attr( get_option( 'base_country' ) );
				if ( empty( $selected_region ) )
					$selected_region = esc_attr( get_option( 'base_region' ) );
				global $wpdb;
				$region_list = $wpdb->get_results( "SELECT `" . WPSC_TABLE_REGION_TAX . "`.* FROM `" . WPSC_TABLE_REGION_TAX . "`, `" . WPSC_TABLE_CURRENCY_LIST . "`  WHERE `" . WPSC_TABLE_CURRENCY_LIST . "`.`isocode` IN('" . $selected_country . "') AND `" . WPSC_TABLE_CURRENCY_LIST . "`.`id` = `" . WPSC_TABLE_REGION_TAX . "`.`country_id`", ARRAY_A );
				
				$statescode = substr($fieldcode, $statestart + strlen($lookfor));
				$states = array();
				$abbr = array();
				foreach($region_list as $s) {
					$states[] = $s['name'];
					$abbr[] = $s['code'];
				}
				$statescode = str_replace($states,$abbr,$statescode);
				// for whatever reason, wpsc does not set the state by default, so
				if(strpos($statescode,"selected") === false) {
					$statescode = str_replace("value='$selected_region'", "value='$selected_region' selected='selecte'", $statescode);
				}
				
				if(strpos($fieldset[$i+1]->unique_name, 'postcode') > 0) {
					$shortenzip = true;
					echo '<!-- shorterzipcoming -->';
				}
				$fieldcode = substr($fieldcode, 0, $statestart) ."<label class='statelabel label". $item->id ."' style='display:none'>State <span class='asterix'>*</span></label><div id='region_select_".$item->id."'><select". ($shortenzip ? " style='width:70px'" : ""). $statescode;
				$fieldcode = str_replace('set_shipping_country', 'progo_set_shipping_country', $fieldcode);
				$fieldcode = str_replace('set_billing_country', 'progo_set_billing_country', $fieldcode);
			}
		} else {
			// ProGo custom css classes
			$classes = array();
			if ( $item->type == 'text' ) $classes[] = 'txt';
			if ( $item->mandatory == 1 ) $classes[] = 'req';
			
			$classes = implode(' ',$classes) .' ';
			$fieldcode = str_replace("class='", "class='". $classes, $fieldcode);
		}
		echo $fieldcode;
		?>
		<?php if(wpsc_the_checkout_item_error() != ''): ?>
		<p class='validation-error'><?php echo wpsc_the_checkout_item_error(); ?></p>
		<?php endif;
		
			if( (strpos($item->unique_name, 'postcode') > 0) && $shortenzip ) {
				echo '</span>';
			}
		}
	} ?>
	</fieldset>
	<?php 
	}
}
endif;
if ( ! function_exists( 'progo_summary' ) ):
/**
 * chops off (product) text either @ <!-- more --> or last space before 152 characters
 * @since ProGoDotCom 1.0
 */
function progo_summary( $morelink, $limit = 150, $sanitize = false, $echo = true ) {
	global $post;
	$content = $post->post_content;
	if ( $sanitize == true ) {
		$content = wp_kses($content, array());
	}
	if ( $limit !== false ) {
		$lbrat = strpos( $content, "\n" );
		if( ( $lbrat > 0 ) && ( $lbrat < $limit ) && ( $sanitize == false ) ) {
			$content = substr( $content, 0, $lbrat );
		} else {
			$content = substr( $content, 0, strrpos( substr( $content, 0, $limit ), ' ' ) ) ."...";
		}
	}
	if( $morelink != false ) {
		$content .= "\n<a href='". wpsc_the_product_permalink() ."' class='more-link'>$morelink</a>";
	}
	if ( $echo == false ) {
		return wpautop($content);
	}
	echo wpautop($content);
}
endif;
if ( ! function_exists( 'progo_comments' ) ):
/**
 * walker function for comment display
 * @since Ecommerce 1.0.9
 */
function progo_comments($comment, $args, $depth) {	
	$GLOBALS['comment'] = $comment;
	extract($args, EXTR_SKIP);
	
	if ( 'div' == $args['style'] ) {
		$tag = 'div';
		$add_below = 'comment';
	} else {
		$tag = 'li';
		$add_below = 'div-comment';
	}
	?>
	<<?php echo $tag ?> <?php comment_class(empty( $args['has_children'] ) ? '' : 'parent') ?> id="comment-<?php comment_ID() ?>">
	<?php if ( 'div' != $args['style'] ) : ?>
	<div id="div-comment-<?php comment_ID() ?>" class="comment-body">
	<?php endif; ?>
	<div class="comment-meta"><div class="comment-author vcard">
	<?php echo get_comment_author_link() ?>
	</div>
	<div class="meta"><a href="<?php echo htmlspecialchars( get_comment_link( $comment->comment_ID ) ) ?>">
		<?php
			/* translators: 1: date, 2: time */
			printf( __('%1$s at %2$s'), get_comment_date(),  get_comment_time()) ?></a><?php edit_comment_link(__('(Edit)'),'&nbsp;&nbsp;','' );
		?>
	</div>
    </div>
	<?php if ($args['avatar_size'] != 0) echo get_avatar( $comment, $args['avatar_size'] ); ?>
	<?php if ($comment->comment_approved == '0') : ?>
	<em class="comment-awaiting-moderation"><?php _e('Your comment is awaiting moderation.') ?></em>
	<br />
	<?php endif; ?>
	<?php comment_text() ?>
	
	<div class="reply">
	<?php comment_reply_link(array_merge( $args, array('add_below' => $add_below, 'depth' => $depth, 'max_depth' => $args['max_depth']))) ?>
	</div>
	<?php if ( 'div' != $args['style'] ) : ?>
	</div>
	<?php endif; ?>
	<?php
}
endif;
if ( ! function_exists( 'progo_product_image' ) ):
/**
 * wpsc_the_product_image does not actually care
 * about the $width & $height args, at least as of 3.8.3,
 * so this fixes that
 *
 * @param image (thumbnail) width
 * @param image (thumbnail) height
 * @param product (post) id
 *
 * @since Ecommerce 1.1.0
 */
function progo_product_image( $width='', $height='', $product_id='' ) {
	if ( empty( $product_id ) )
		$product_id = get_the_ID();

	$imagesize = ( ( $width == '' ) && ( $height == '' ) ) ? 'large' : array( $width, $height );

	$product = get_post( $product_id );

	if ( $product->post_parent > 0 )
		$product_id = $product->post_parent;

	$attached_images = (array)get_posts( array(
				'post_type' => 'attachment',
				'numberposts' => 1,
				'post_status' => null,
				'post_parent' => $product_id,
				'orderby' => 'menu_order',
				'order' => 'ASC'
			) );


	$post_thumbnail_id = get_post_thumbnail_id( $product_id );

	$src = wp_get_attachment_image_src( $post_thumbnail_id, $imagesize );

	if ( ! empty( $src ) && is_string( $src[0] ) ) {
		$src = $src[0];
	} elseif ( ! empty( $attached_images ) ) {
		$attached_image = wp_get_attachment_image_src( $attached_images[0]->ID, $imagesize );
		$src = $attached_image[0];
	} else {
		$src = false;
	}
	
	if ( is_ssl() && ! empty( $src ) )
		$src = str_replace( 'http://', 'https://', $src );
	$src = apply_filters( 'wpsc_product_image', $src );
	
	return $src;
}
endif;
if ( ! function_exists('progo_twoblogs') ):
function progo_twoblogs( $atts ) {
	global $post;
	$oldpost = $post;
	$oot = '<div class="twoposts">';
	$posts = get_posts(array(
		'numberposts' => 2,
		'category' => 13
	));
	$alpha = true;
	foreach ( $posts as $post) {
		$oot .='<div class="post grid_6 '. ($alpha ? 'alpha' : 'omega') .'">';
		$alpha = false;
		$oot .= '<a href="'. get_permalink() .'">'. get_the_post_thumbnail($post->ID, 'thumbnail', array('class'=>'thm')) .'</a>';
		$oot .= '<h3><a href="'. get_permalink() .'">'. the_title('','',false) .'</a></h3>';
		/*
		$content = wp_kses($post->post_content, array());
		$content = substr( $content, 0, strrpos( substr( $content, 0, 240 ), ' ' ) );
		$oot .= '<p>'. $content  .'...</p>';
		*/
		$oot .= progo_summary('read more', 240, true, false);
		$oot .= '</div>';
    }
	$oot .= '</div>';
	return $oot;
}
endif;
add_shortcode( 'progo2blogs', 'progo_twoblogs' );
if ( ! function_exists('progo_theend') ):
function progo_theend( $atts, $content ) {
	if(strpos($content,'</p>') === 0) $content = substr($content,4);
	$strlen = strlen($content) - 3;
	if(strrpos($content,'<p>') == $strlen ) $content = substr($content, 0, $strlen);
	return '<div class="theend">'. do_shortcode($content) .'</div>';
}
endif;
add_shortcode( 'progoend', 'progo_theend' );
if ( ! function_exists('progo_pprice_shortcode') ):
function progo_pprice_shortcode( $atts ) {
	return '<div class="pprice"><strong>'. progo_price(wpsc_the_product_id()) .'</strong></div>';
}
endif;
add_shortcode( 'pprice', 'progo_pprice_shortcode' );
/********* Back-End Functions *********/
if ( ! function_exists( 'progo_admin_menu_cleanup' ) ):
/**
 * hooked to 'admin_menu' by add_action in progo_setup()
 * @since ProGoDotCom 1.0
 */
function progo_admin_menu_cleanup() {
	global $menu;
	global $submenu;
	
	// add Theme Options and Homepage Slides pages under APPEARANCE
	add_theme_page( 'Homepage Slides', 'Homepage Slides', 'edit_theme_options', 'progo_home_slides', 'progo_home_slides_page' );
	add_theme_page( 'Theme Options', 'Theme Options', 'edit_theme_options', 'progo_admin', 'progo_admin_page' );
	// and reorder that APPEARANCE submenu
	$sub = $submenu['themes.php'];
	$sub1 = array_shift($sub);
	rsort($sub);
	$sub1[0] = 'Change Theme';
	$sub[] = $sub1;
	$submenu['themes.php'] = $sub;
	
	unset($submenu['edit.php?post_type=wpsc-product'][18]);
//	wp_die('<pre>'.print_r($submenu,true).'</pre>');
}
endif;
if ( ! function_exists( 'progo_admin_menu_order' ) ):
function progo_admin_menu_order($menu_ord) {
    if (!$menu_ord) return true;
    return array(
     'index.php', // this represents the dashboard link
	 'separator1',
     'edit.php?post_type=wpsc-product', // this is a custom post type menu
     'edit.php?post_type=page', // this is the default page menu
     'edit.php', // this is the default POST admin menu
     'upload.php', // this is the default POST admin menu
     'edit-comments.php', // this is the default POST admin menu
     'link-manager.php' // this is the default POST admin menu
 );
}
endif;
if ( ! function_exists( 'progo_admin_page' ) ):
/**
 * ProGo Theme Admin Page function
 * switch statement creates Pages for Installation, Shipping, Payment, Products, Appearance
 * from admin_menu_cleanup()
 
 * @since ProGoDotCom 1.0.3
 */
function progo_admin_page() {
	//must check that the user has the required capability 
	if ( current_user_can('edit_theme_options') == false) {
		wp_die( __('You do not have sufficient permissions to access this page.') );
	} ?>
<script type="text/javascript">/* <![CDATA[ */
var wpsc_adminL10n = {
	unsaved_changes_detected: "Unsaved changes have been detected. Click OK to lose these changes and continue.",
	dragndrop_set: "false"
};
try{convertEntities(wpsc_adminL10n);}catch(e){};
/* ]]> */
</script>
    <?php
	$thispage = $_GET['page'];
	switch($thispage) {
		case "progo_admin":
	?>
	<div class="wrap">
    <div class="icon32" id="icon-themes"><br /></div>
    <h2>ProGoDotCom Theme Options</h2>
	<form action="options.php" method="post" enctype="multipart/form-data"><?php
		settings_fields( 'progo_options' );
		//do_settings_sections( 'progo_theme' );
		do_settings_sections( 'progo_info' );
		do_settings_sections( 'progo_hometop' );
		?>
        <p class="submit"><input type="submit" value="Save Changes" class="button-primary" /></p>
        <h3>Additional Options</h3>
        <table class="form-table">
        <?php
		$addl = array(
			'Homepage Slides' => array(
				'url' => 'themes.php?page=progo_home_slides',
				'btn' => 'Manage Homepage Slides',
				'desc' => ''
			),
			'Background' => array(
				'url' => 'themes.php?page=custom-background',
				'btn' => 'Customize Your Background',
				'desc' => 'Change the underlying color, or upload your own custom background image.'
			),
			'Menus' => array(
				'url' => 'nav-menus.php',
				'btn' => 'Manage Menu Links',
				'desc' => 'Control the links in the Header &amp; Footer area of your site.'
			),
			'Widgets' => array(
				'url' => 'widgets.php',
				'btn' => 'Manage Widgets',
				'desc' => 'Customize what appears in the right column on various areas of your site.'
			)
		);
		foreach ( $addl as $k => $v ) {
			echo '<tr><th scope="row">'. wp_kses($k,array()) .'</th><td><a href="'. esc_url($v['url']) .'" class="button">'. wp_kses($v['btn'],array()) .' &raquo;</a> <span class="description">'. wp_kses($v['desc'],array()) .'</span></td></tr>';
		} ?>
        </table><p><br /></p>
        <h3>WP e-Commerce</h3>
		<p>Your ProGo Theme works hand-in-hand with the <strong>WP e-Commerce</strong> Plugin.</p>
		<?php
	// check for wp-e-commerce installed..
	$plugs = get_plugins();
	if( isset( $plugs['wp-e-commerce/wp-shopping-cart.php'] ) == false ) {
		$lnk = ( function_exists( 'wp_nonce_url' ) ) ? wp_nonce_url( 'update.php?action=install-plugin&amp;plugin=wp-e-commerce', 'install-plugin_wp-e-commerce' ) : 'plugin-install.php';
		echo '<p><a href="'. esc_url( $lnk ) .'" class="button-primary">Install WP e-Commerce now &raquo;</a></p>';
	} else {
		if ( function_exists('wpsc_admin_pages')) {
			?><table class="form-table">
            <tr valign="top">
            <th scope="row">Store Settings</th>
            <td><?php
			//check wpsc settings dimensions for thumbnail (product_image) & product image (single_view_image)
			if ( get_option( 'product_image_width' ) == 70 && get_option( 'product_image_height' ) == 70 && get_option( 'single_view_image_width' ) == 290 && get_option( 'single_view_image_height' ) == '' ) {
				$wpec = '<a href="http://localhost/wp-admin/options-general.php?page=wpsc-settings&tab=';
				$tabs = array(
					"General",
					"Presentation",
					"Taxes",
					"Shipping",
					"Payment Gateway",
					"Checkout"
				);
				for ( $i = 0; $i < count($tabs); $i++ ) {
					$l = ($tabs[$i] == "Payment Gateway" ? "gateway" : strtolower($tabs[$i]) );
					echo ( $i > 0 ? ' &nbsp;|&nbsp; ' : '' ). $wpec . $l .'">'. $tabs[$i] .'</a>';
				}
			} else {
				echo "<p><strong>A few WP e-Commerce Store Settings, like Product Thumbnail Sizes, differ from ProGo Themes' Recommended Settings</strong></p><p>";
				echo '<a href="'.wp_nonce_url("admin.php?progo_admin_action=reset_wpsc", 'progo_reset_wpsc').'" class="button-primary">Click Here to Reset</a></p>';
			} ?></td></tr>
            </table><?php
        } else {
			$lnk = ( function_exists( 'wp_nonce_url' ) ) ? wp_nonce_url('plugins.php?action=activate&amp;plugin=wp-e-commerce/wp-shopping-cart.php&amp;plugin_status=all&amp;paged=1', 'activate-plugin_wp-e-commerce/wp-shopping-cart.php') : 'plugins.php';
			echo '<p><a href="'. esc_url($lnk) .'" class="button-primary">Activate WP e-Commerce &raquo;</a><p>';
			$goon = false;
		}
	}
		?>
		<p><br /></p>
		</form>
        <h3>Recommended Plugins</h3>
                <?php if ( function_exists( 'alex_recommends_widget' ) ) {
					alex_recommends_widget();
				} else { ?>
                    <p>The following plugins can help improve various aspects of your WordPress / ProGo Themes site:</p>
                    <ul style="list-style:outside; padding: 0 1em">
                    <?php
					$pRec = array();
					$pRec[] = array('name'=>'All in One SEO Pack','stub'=>'all-in-one-seo-pack','desc'=>'Out-of-the-box SEO. Easily control your pages\' keywords / meta description, and more');
					$pRec[] = array('name'=>'ShareThis','stub'=>'share-this','desc'=>'Let your visitors share your Products with others, posting to Facebook/Twitter/social bookmarking sites, and emailing to friends');
					$pRec[] = array('name'=>'Ultimate Google Analytics','stub'=>'ultimate-google-analytics','desc'=>'Add Google Analytics to your site, with options to track external links, mailto\'s, and downloads');
					$pRec[] = array('name'=>'Google XML Sitemaps','stub'=>'google-sitemap-generator','desc'=>'Generate an XML sitemap to help search engines like Google, Yahoo, Bing and Ask.com better index your site');
					$pRec[] = array('name'=>'WB DB Backup','stub'=>'wp-db-backup','desc'=>'On-demand backup of your WordPress database');
					$pRec[] = array('name'=>'Duplicate Post','stub'=>'duplicate-post','desc'=>'Add functionality to Save Page As...');
					$pRec[] = array('name'=>'Gold Cart for WP e-Commerce','stub'=>'','desc'=>'Extend your WP e-Commerce store with additional payment gateways and multiple product image');
					
					foreach( $pRec as $plug ) {
						echo '<li>';
						if ( $plug['name'] == 'Gold Cart for WP e-Commerce' ){
							echo '<a title="Learn more about '. esc_attr( $plug['name'] ) .'" target="_blank" href="http://getshopped.org/extend/premium-upgrades/premium-upgrades/gold-cart-plugin/">';
						} else echo '<a title="Learn more &amp; install '. esc_attr( $plug['name'] ) .'" class="thickbox" href="'. get_bloginfo('url') .'/wp-admin/plugin-install.php?tab=plugin-information&amp;plugin='. $plug['stub'] .'&amp;TB_iframe=true&amp;width=640&amp;height=560">';
						echo esc_html($plug['name']) .'</a> : '. esc_html($plug['desc']) .'</li>';
					}
					?>
                    </ul>
                    <?php } ?>
                    <p><br /></p>
    <div class="clear"></div>
    </div>
	<?php
			break;
		default: ?>
	<div class="wrap">
    <div class="icon32" id="icon-themes"><br /></div><h2>Huh?</h2>
    </div>
    <?php
			break;
	}
}
endif;
if ( ! function_exists( 'progo_custom_login_logo' ) ):
/**
 * hooked to 'login_head' by add_action in progo_setup()
 * @since ProGoDotCom 1.0
 */
function progo_custom_login_logo() {
	if ( get_option('progo_logo') != '' ) {
		#needswork
		echo "<!-- login screen here... overwrite logo with custom logo -->\n"; 
	} else { ?>
<style type="text/css">
#login { margin-top: 6em; }
h1 a { background: url(<?php bloginfo( 'template_url' ); ?>/images/logo_progo.png) no-repeat top center; height: 80px; }
</style>
<?php }
}
endif;
if ( ! function_exists( 'progo_custom_login_url' ) ):
/**
 * hooked to 'login_headerurl' by add_action in progo_setup()
 * @uses get_option() To check if a custom logo has been uploaded to the back end
 * @return the custom URL
 * @since ProGoDotCom 1.0
 */
function progo_custom_login_url() {
	if ( get_option( 'progo_logo' ) != '' ) {
		return get_bloginfo( 'url' );
	} // else
	return 'http://www.progo.com';
}
endif;
if ( ! function_exists( 'progo_site_settings_page' ) ):
/**
 * outputs HTML for ProGo Themes "Site Settings" page
 * @uses settings_fields() for hidden form items for 'progo_options'
 * @uses do_settings_sections() for 'progo_site_settings'
 * @since ProGoDotCom 1.0
 */
function progo_site_settings_page() {
?>
	<div class="wrap">
		<div class="icon32" id="icon-options-general"></div>
		<h2>Site Settings</h2>
		<form action="options.php" method="post" enctype="multipart/form-data"><?php
		settings_fields( 'progo_options' );
		do_settings_sections( 'progo_site_settings' );
		?><p class="submit"><input type="submit" name="updateoption" value="Update &raquo;" /></p>
		</form>
	</div>
<?php
}
endif;
if ( ! function_exists( 'progo_home_slides_page' ) ):
/**
 * outputs HTML for ProGo Themes "Site Settings" page
 * @uses settings_fields() for hidden form items for 'progo_slides'
 * @uses do_settings_sections() for 'progo_home_slides'
 * @since ProGoDotCom 1.0
 */
function progo_home_slides_page() {
?>
	<div class="wrap">
		<div class="icon32" id="icon-options-general"></div>
		<h2>Homepage Slides</h2>
		<form action="options.php" method="post" enctype="multipart/form-data"><?php
		settings_fields( 'progo_slides' );
		do_settings_sections( 'progo_home_slides' );
		?><p class="submit"><input type="submit" name="updateoption" value="Update &raquo;" /></p>
		</form>
	</div>
<?php
}
endif;
if ( ! function_exists( 'progo_homeslide_start' ) ):
/**
 * helper function
 * @since ProGoDotCom 1.0
 */
function progo_homeslide_action($num, $sel, $slidedata = false) {
	$slideproduct = 0;
	$slidetext = $slideimg = $slidetitle = '';
	if(is_array($slidedata)) {
		if(isset($slidedata['product'])) $slideproduct = absint($slidedata['product']);
		if(isset($slidedata['text'])) $slidetext = $slidedata['text'];
		if(isset($slidedata['image'])) $slideimg = $slidedata['image'];
		if(isset($slidedata['title'])) $slidetitle = $slidedata['title'];
	}
?><div class="postbox">
<div class="handlediv" title="Click to toggle"><br /></div><h3 class="hndle"><span>Slide <?php echo $num; ?></span></h3>
<div class="inside">
<p><a href="#" onclick="return progo_slideremove(jQuery(this));" style="float:right">Delete This Slide</a>Slide shows : <select class="homeslideshows" name="progo_slides[<?php echo $num; ?>][show]" onchange="progo_slidefor(jQuery(this));"><option value="">- please select -</option>
<?php
$slidetypes = array(
	"product" => "Product",
	"text" => "Text Area",
	"image" => "Image Banner"
);
foreach ( $slidetypes as $k => $v ) {
	$s = $sel == $k ? " selected='selected'" : "";
	echo "<option value='$k'$s>$v</option>";
}
?></select> &nbsp; &nbsp; 
Slide Title : <input type="text" name="progo_slides[<?php echo $num; ?>][title]" value="<?php echo esc_attr($slidetitle); ?>" size="20" /> (shown below slider area)</p>
<p class="product" style="<?php if($sel!='product') echo 'display:none'; ?>">Which Product? <select name="progo_slides[<?php echo $num; ?>][product]">
<?php
$prods = get_posts(array('numberposts' => -1, 'post_type' => 'wpsc-product'));
foreach ( $prods as $p ) {
	$s = $slideproduct == $p->ID ? ' selected="selected"' : '';
	echo '<option value="'. $p->ID .'"'. $s .'>'. esc_attr($p->post_title) .'</option>';
}
?>
</select></p>
<p class="text" style="<?php if($sel!='text') echo 'display:none'; ?>">Text to Display :<br /><textarea name="progo_slides[<?php echo $num; ?>][text]" rows="3" style="width: 100%"><?php echo esc_attr($slidetext); ?></textarea></p>
<p class="image" style="<?php if($sel!='image') echo 'display:none'; ?>">Image URL : <input type="text" name="progo_slides[<?php echo $num; ?>][image]" value="<?php echo esc_url($slideimg); ?>" size="40" /> (Images should be 988 x 424)</p>
</div>
</div>
<?php
}
endif;
if ( ! function_exists( 'progo_field_slides' ) ):
/**
 * outputs HTML for "Homepage Slides"
 * @since ProGoDotCom 1.0
 */
function progo_field_slides() {
	$slides = get_option( 'progo_slides' );
	$count = isset($slides['count']) ? absint($slides['count']) : 0;
	echo '<pre style="display:none">'. print_r($slides,true) .'</pre>';
	?>
<div id="poststuff" class="metabox-holder"><div id="normal-sortables" class="meta-box-sortables ui-sortable">
<?php
	if ( $count > 0 ) {
		unset($slides['count']);
		foreach($slides as $n => $s ) {
			progo_homeslide_action($n+1, $s['show'], $s);
		}
	}
?>
</div></div>
<p class="submit"><input type="submit" name="addmore" value="Add Another Slide &raquo;" onclick="return progo_anotherslide();" /><input type="hidden" name="progo_slides[count]" id="numslides" value="<?php echo $count; ?>" /></p>
<?php }
endif;
add_action('wp_ajax_progo_homeslide_ajax', 'progo_ajax_callback');
if(!function_exists('progo_ajax_callback')):
function progo_ajax_callback() {
	$slidenum = absint($_POST['slidenum']);
	$slideaction = $_POST['slideaction'];
	progo_homeslide_action($slidenum, $slideaction);

	die(); // this is required to return a proper result
}
endif;
if ( ! function_exists( 'progo_admin_page_styles' ) ):
/**
 * hooked to 'admin_print_styles' by add_action in progo_setup()
 * adds thickbox js for WELCOME screen styling
 * @since ProGoDotCom 1.0
 */
function progo_admin_page_styles() {
	global $pagenow;
	if ( $pagenow == 'admin.php' && isset( $_GET['page'] ) ) {
		$thispage = $_GET['page'];
		switch ( $thispage ) {
			case 'progo_admin' :
				wp_enqueue_style( 'dashboard' );
				wp_enqueue_style( 'global' );
				wp_enqueue_style( 'wp-admin' );
				wp_enqueue_style( 'thickbox' );
				wp_enqueue_style( 'wp-e-commerce-admin', WPSC_URL .'/wpsc-admin/css/admin.css', false, false, 'all' );
				break;
			case 'progo_shipping' :
			case 'progo_gateway' :
				wp_enqueue_style( 'dashboard' );
				wp_enqueue_style( 'global' );
				wp_enqueue_style( 'wp-admin' );
				wp_enqueue_style( 'thickbox' );
				wp_enqueue_style( 'wp-e-commerce-admin_2.7', WPSC_URL . '/wpsc-admin/css/settingspage.css', false, false, 'all' );
				wp_enqueue_style( 'wp-e-commerce-admin', WPSC_URL .'/wpsc-admin/css/admin.css', false, false, 'all' );
				break;
		}
	}
	wp_enqueue_style( 'progo_admin', get_bloginfo( 'template_url' ) .'/css/admin-style.css' );
}
endif;
if ( ! function_exists( 'progo_admin_page_scripts' ) ):
/**
 * hooked to 'admin_print_scripts' by add_action in progo_setup()
 * adds thickbox js for WELCOME screen Recommended Plugin info
 * @since ProGoDotCom 1.0
 */
function progo_admin_page_scripts() {
	global $pagenow;
	if ( $pagenow == 'admin.php' && isset( $_GET['page'] ) ) {
		if ( in_array( $_GET['page'], array( 'progo_admin', 'progo_shipping', 'progo_gateway' ) ) ) { ?>
<script type="text/javascript">//<![CDATA[
addLoadEvent = function(func){if(typeof jQuery!="undefined")jQuery(document).ready(func);else if(typeof wpOnload!='function'){wpOnload=func;}else{var oldonload=wpOnload;wpOnload=function(){oldonload();func();}}};
var userSettings = {
	'url': '<?php echo trailingslashit(get_bloginfo('url')); ?>',
	'uid': '<?php $us = wp_get_current_user(); echo $us->ID; ?>',
	'time':'<?php echo time(); ?>'
},
ajaxurl = '<?php echo trailingslashit(get_bloginfo('url')); ?>wp-admin/admin-ajax.php',
pagenow = 'settings_page_<?php echo $_GET['page']; ?>',
typenow = '',
adminpage = 'settings_page_<?php echo $_GET['page']; ?>',
thousandsSeparator = ',',
decimalPoint = '.',
isRtl = 0;
//]]>
</script>
<?php
            wp_enqueue_script( 'thickbox' );
            $version_identifier = WPSC_VERSION . "." . WPSC_MINOR_VERSION;
            wp_enqueue_script( 'livequery', WPSC_URL . '/wpsc-admin/js/jquery.livequery.js', array( 'jquery' ), '1.0.3' );
            wp_enqueue_script( 'wp-e-commerce-admin-parameters', $siteurl . '/wp-admin/admin.php?wpsc_admin_dynamic_js=true', false, $version_identifier );
            wp_enqueue_script( 'wp-e-commerce-admin', WPSC_URL . '/wpsc-admin/js/admin.js', array( 'jquery', 'jquery-ui-core', 'jquery-ui-sortable' ), $version_identifier, false );
            wp_enqueue_script( 'wp-e-commerce-legacy-ajax', WPSC_URL . '/wpsc-admin/js/ajax.js', false, $version_identifier );
        } elseif ( $_GET['page'] == 'progo_home_slides' ) {
			# here be drag'ns
            wp_enqueue_script('post');
            wp_enqueue_script('progo-homeslides-admin', get_bloginfo( 'template_url' ) .'/js/homeslides-admin.js', array ( 'jquery', 'post' ), false, true );
        }
	}
}
endif;
if ( ! function_exists( 'progo_admin_init' ) ):
/**
 * hooked to 'admin_init' by add_action in progo_setup()
 * sets admin action hooks
 * registers Site Settings
 * @since ProGoDotCom 1.0
 */
function progo_admin_init() {
	if ( isset( $_REQUEST['progo_admin_action'] ) ) {
		switch( $_REQUEST['progo_admin_action'] ) {
			case 'reset_wpsc':
				progo_reset_wpsc(true);
				break;
			case 'reset_logo':
				progo_reset_logo();
				break;
		}
	}
	
	if ( $pagenow == 'admin.php' && isset( $_GET['page'] ) ) {
		if ( $_GET['page'] == 'progo_admin' ) {
			wp_redirect( admin_url( 'themes.php?page=progo_admin' ) );
		}
	}
	
	// ACTION hooks
	add_action( 'admin_print_styles', 'progo_admin_page_styles' );
	add_action( 'admin_print_scripts', 'progo_admin_page_scripts' );
	
	// Appearance settings
	register_setting( 'progo_options', 'progo_options', 'progo_validate_options' );
	/*
	add_settings_section( 'progo_theme', 'Theme Customization', 'progo_section_text', 'progo_theme' );
	add_settings_field( 'progo_logo', 'Logo', 'progo_field_logo', 'progo_theme', 'progo_theme' );
	*/
	add_settings_section( 'progo_info', 'Site Info', 'progo_section_text', 'progo_info' );
	add_settings_field( 'progo_blogname', 'Site Name', 'progo_field_blogname', 'progo_info', 'progo_info' );
	add_settings_field( 'progo_blogdescription', 'Slogan', 'progo_field_blogdesc', 'progo_info', 'progo_info' );
	add_settings_field( 'progo_showdesc', 'Show/Hide Slogan', 'progo_field_showdesc', 'progo_info', 'progo_info' );
	add_settings_field( 'progo_support', 'Customer Support', 'progo_field_support', 'progo_info', 'progo_info' );
	add_settings_field( 'progo_copyright', 'Copyright Notice', 'progo_field_copyright', 'progo_info', 'progo_info' );
	add_settings_field( 'progo_secure', 'Security Logos', 'progo_field_cred', 'progo_info', 'progo_info' );
	add_settings_field( 'progo_companyinfo', 'Company Info', 'progo_field_compinf', 'progo_info', 'progo_info' );

	add_settings_section( 'progo_homepage', 'Homepage', 'progo_section_text', 'progo_hometop' );
	add_settings_field( 'progo_frontpage', 'Display', 'progo_field_frontpage', 'progo_hometop', 'progo_homepage' );
	add_settings_field( 'progo_homeseconds', 'Slide Rotation Speed', 'progo_field_homeseconds', 'progo_hometop', 'progo_homepage' );
	
	// Homepage Slides settings
	register_setting( 'progo_slides', 'progo_slides', 'progo_validate_homeslides' );
	add_settings_section( 'progo_slide', 'Homepage Slides', 'progo_section_text', 'progo_home_slides' );
	add_settings_field( 'progo_make_slides', 'Homepage Slides', 'progo_field_slides', 'progo_home_slides', 'progo_slide' );
	
	// since there does not seem to be an actual THEME_ACTIVATION hook, we'll fake it here
	if ( get_option( 'progo_dotcom_installed' ) != true ) {
		// also want to create a few other pages (Terms & Conditions, Privacy Policy), set up the FOOTER menu, and add these pages to it...
		
		$post_date = date( "Y-m-d H:i:s" );
		$post_date_gmt = gmdate( "Y-m-d H:i:s" );
		
		// create new menus in the Menu system
		$new_menus = array(
			'mainmenu' => 'Main Menu',
			'footer' => 'Footer Links'
		);
		$aok = 1;
		foreach ( $new_menus as $k => $m ) {
			$new_menus[$k] = wp_create_nav_menu($m);
			if ( is_numeric( $new_menus[$k] ) == false ) {
				$aok--;
			}
		}
		//set_theme_mod
		if ( $aok == 1 ) {
			// register the new menus as THE menus in theme's menu areas
			set_theme_mod( 'nav_menu_locations' , $new_menus );
		}
			
		// create a few new pages, and populate some menus
		$lipsum = "Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam...Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna\n\nLorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam...Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam";
		
		$new_pages = array(
			'home' => array(
				'title' => __( 'Home', 'progo' ),
				'content' => "<h3>This is your Homepage</h3>$lipsum",
				'id' => '',
				'menu' => 'mainmenu'
			),
			'about' => array(
				'title' => __( 'About', 'progo' ),
				'content' => "<h3>This Page could have info about your site/store</h3>$lipsum",
				'id' => '',
				'menu' => 'mainmenu'
			),
			'blog' => array(
				'title' => __( 'Blog', 'progo' ),
				'content' => "This Page pulls in your Blog posts",
				'id' => '',
				'menu' => 'mainmenu'
			),
			'terms' => array(
				'title' => __( 'Terms & Conditions', 'progo' ),
				'content' => "<h3>List your Terms and Conditions here</h3>$lipsum",
				'id' => '',
				'menu' => 'footer'
			),
			'privacy' => array(
				'title' => __( 'Privacy Policy', 'progo' ),
				'content' => "<h3>Put your Privacy Policy here</h3>$lipsum",
				'id' => '',
				'menu' => 'footer'
			),
			'customer-service' => array(
				'title' => __( 'Customer Service', 'progo' ),
				'content' => "<h3>This Page could have Customer Service info on it</h3>$lipsum",
				'id' => '',
				'menu' => 'footer'
			)
		);
		foreach ( $new_pages as $slug => $page ) {
			$new_pages[$slug]['id'] = wp_insert_post( array(
				'post_title' 	=>	$page['title'],
				'post_type' 	=>	'page',
				'post_name'		=>	$slug,
				'comment_status'=>	'closed',
				'ping_status' 	=>	'closed',
				'post_content' 	=>	$page['content'],
				'post_status' 	=>	'publish',
				'post_author' 	=>	1,
				'menu_order'	=>	1
			));
			
			if ( $new_pages[$slug]['id'] != false ) {
				// set "Home" & "Blog" page IDs
				switch ( $slug ) {
					case 'home':
						update_option( 'page_on_front', $new_pages[$slug]['id'] );
						update_option( 'progo_homepage_id', $new_pages[$slug]['id'] );
						break;
					case 'blog':
						update_option( 'page_for_posts', $new_pages[$slug]['id'] );
						update_option( 'progo_blog_id', $new_pages[$slug]['id'] );
						break;
				}
				
				$menu_args = array(
					'menu-item-object-id' => $new_pages[$slug]['id'],
					'menu-item-object' => 'page',
					'menu-item-parent-id' => 0,
					'menu-item-type' => 'post_type',
					'menu-item-title' => $page['title'],
					'menu-item-status' => 'publish',
				);
				$menu_id = $new_menus[$new_pages[$slug]['menu']];
				if ( is_numeric( $menu_id ) ) {
					wp_update_nav_menu_item( $menu_id , 0, $menu_args );
				}
			}
		}
		// set our default SITE options
		progo_options_defaults();
		
		// and send to WELCOME page
		wp_redirect( get_option( 'siteurl' ) . '/wp-admin/themes.php?page=progo_admin' );
	}
}
endif;

if ( ! function_exists( 'progo_dotcom_widgets' ) ):
/**
 * registers a sidebar area for the WIDGETS page
 * and registers various Widgets
 * @since ProGoDotCom 1.0
 */
function progo_dotcom_widgets() {
	register_sidebar(array(
		'name' => 'Main Sidebar',
		'id' => 'main',
		'description' => 'Main Sidebar for most of your site\'s pages',
		'before_widget' => '<div class="block %1$s %2$s">',
		'after_widget' => '</div></div>',
		'before_title' => '<h3 class="title"><span class="spacer">',
		'after_title' => '</span></h3><div class="inside">'
	));
	register_sidebar(array(
		'name' => 'Blog',
		'id' => 'blog',
		'description' => 'Sidebar for the Blog area',
		'before_widget' => '<div class="block %1$s %2$s">',
		'after_widget' => '</div></div>',
		'before_title' => '<h3 class="title"><span class="spacer">',
		'after_title' => '</span></h3><div class="inside">'
	));
	register_sidebar(array(
		'name' => 'Checkout',
		'id' => 'checkout',
		'description' => 'The CHECKOUT page could be even more streamlined',
		'before_widget' => '<div class="block %1$s %2$s">',
		'after_widget' => '</div></div>',
		'before_title' => '<h3 class="title"><span class="spacer">',
		'after_title' => '</span></h3><div class="inside">'
	));
	register_sidebar(array(
		'name' => 'Header',
		'id' => 'header',
		'description' => 'We can put a widget or two in the top right of the header',
		'before_widget' => '<div class="hblock %1$s %2$s">',
		'after_widget' => '</div></div>',
		'before_title' => '<h3 class="title"><span class="spacer">',
		'after_title' => '</span></h3><div class="inside">'
	));
	register_sidebar(array(
		'name' => 'Footer',
		'id' => 'footer',
		'description' => 'The Footer area has room for widgets as well',
		'before_widget' => '<div class="fblock %1$s %2$s">',
		'after_widget' => '</div>',
		'before_title' => '<div class="title">',
		'after_title' => '</div>'
	));
	register_sidebar(array(
		'name' => 'More',
		'id' => 'moreright',
		'description' => 'Alt Sidebar for MORE pages',
		'before_widget' => '<div class="block %1$s %2$s">',
		'after_widget' => '</div></div>',
		'before_title' => '<h3 class="title"><span class="spacer">',
		'after_title' => '</span></h3><div class="inside">'
	));
	
	$progo_widgets = array( 'FBLikeBox', 'Tweets', 'Share', 'Social', 'Support' );
	foreach ( $progo_widgets as $w ) {
		require_once( 'widgets/widget-'. strtolower($w) .'.php' );
		register_widget( 'ProGo_Widget_'. $w );
	}
}
endif;
if ( ! function_exists( 'progo_metabox_cleanup' ) ):
/**
 * fires after wpsc_meta_boxes hook, so we can overwrite a lil bit
 * @since ProGoDotCom 1.0
 */
function progo_metabox_cleanup() {
	global $wp_meta_boxes;
	global $post_type;
	global $post;
	
	switch($post_type) {
		case 'wpsc-product':
			if ( isset( $wp_meta_boxes['wpsc-product'] ) ) {
				// unhook wpsc's Product Images metabox and add our own instead
				remove_meta_box( 'wpsc_product_image_forms', 'wpsc-product', 'normal' );
				add_meta_box( 'progo_product_image_forms', 'Product Images', 'progo_product_image_forms', 'wpsc-product', 'normal', 'high' );
				// sort the wpsc-product main column meta boxes so Product Images is #1
				$wp_meta_boxes['wpsc-product']['normal']['high'] = progo_arraytotop( $wp_meta_boxes['wpsc-product']['normal']['high'], 'progo_product_image_forms' );
				
				// also move PRICE to just under SUBMITdiv on right
				// Backup and delete element from parent array
				$toparr = array(
					'submitdiv' => $wp_meta_boxes['wpsc-product']['side']['core']['submitdiv'],
					'wpsc_price_control_forms' => $wp_meta_boxes['wpsc-product']['side']['low']['wpsc_price_control_forms']
				);
				unset($wp_meta_boxes['wpsc-product']['side']['core']['submitdiv']);
				unset($wp_meta_boxes['wpsc-product']['side']['low']['wpsc_price_control_forms']);
				// Merge the two arrays together so our widget is at the beginning
				$wp_meta_boxes['wpsc-product']['side']['core'] = array_merge( $toparr, $wp_meta_boxes['wpsc-product']['side']['core'] );
				
				// remove default PERFORMANCE MARKETING METER box and add our own instead
				unset($wp_meta_boxes['wpsc-product']['side']['core']['tagsdiv-progo_pmm']);
				
				$post_categories = wp_get_object_terms( $post->ID, 'wpsc_product_category', array('fields' => 'ids') );
				if ( in_array( 9, $post_categories ) ) {
					add_meta_box("progo_pmm_box", "Theme Information", "progo_pmm_box", "wpsc-product", "side", "low");
				}
			}
			break;
		case 'page':
			add_meta_box( 'progo_sidebar_box', 'Sidebar', 'progo_sidebar_box', 'page', 'side', 'low' );
			break;
	}
}
endif;
add_action( 'do_meta_boxes', 'progo_metabox_cleanup' );
if ( ! function_exists( 'progo_pmm_box' ) ):
function progo_pmm_box() {
	global $post;
	// Get all the PERFORMANCE MARKETING METER terms
	$marketing_terms = get_option( 'progo_pmm_terms' ); //ca$hed
	$custom = get_post_meta($post->ID,'_progo_pmm');
	$pmm_ratings = $custom[0];
	$pmm_arrow = '';
	if(is_array($pmm_ratings) == false) {
		$pmm_ratings = array();
	} elseif(isset($pmm_ratings['arrowd'])) {
		$pmm_arrow = $pmm_ratings['arrowd'];
		unset($pmm_ratings['arrowd']);
	}
	if($pmm_arrow == '') {
		$pmm_arrow = 'get your new website today';
	}
	
	$custom = get_post_meta($post->ID,'_progo_demo');
	$pdemo = $custom[0];
	
	echo '<p><strong>Performance Marketing Meter</strong></p><table>';
	// Loop through each variation set
	foreach ( (array)$marketing_terms as $k => $n ) { ?>
		<tr><th scope="row"><label><?php echo $n; ?></label></th><td><select name="_progo_pmm[<?php echo $k; ?>]" style="width:120%"><?php
        $thislevel = isset($pmm_ratings[$k]) ? $pmm_ratings[$k] : 0;
		for ( $i = 0; $i < 6; $i++ ) {
			echo '<option value="'. $i .'"'. ($thislevel == $i ? ' selected="selected"' : '') .'>'. $i .'</option>';
		}
		?></select></td></tr>
	<?php
	}
	echo '</table><p><a href="'. admin_url('edit-tags.php?taxonomy=progo_pmm&post_type=wpsc-product') .'" target="_blank">Add more Marketing categories</a></p>';
	echo '<p><label for="_progo_pmm[arrowd]"><strong>Arrow Headline</strong></label><br /><input type="text" name="_progo_pmm[arrowd]" value="'. esc_attr($pmm_arrow) .'" size="38" /></p>';
	echo '<p><label for="_progo_demo"><strong>Theme Demo URL</strong></label><br /><input type="text" name="_progo_demo" value="'. esc_url($pdemo) .'" size="38" /></p>';
	
}
endif;
if ( ! function_exists( 'progo_sidebar_box' ) ):
/**
 * outputs html for "Sidebar" meta box on EDIT PAGE
 * lets Admins choose which Sidebar area is displayed on each Page
 * called by add_meta_box( "progo_direct_box", "Direct Response", "progo_direct_box"...
 * in progo_admin_init()
 * @uses progo_direct_meta_defaults()
 * @since Direct 1.0.9
 */
function progo_sidebar_box() {
	global $post;
	$custom = get_post_meta($post->ID,'_progo_sidebar');
	$sidebar = $custom[0];
	
	$ids = array('main', 'blog', 'checkout', 'moreright');
	$titles = array('Main Sidebar (default)', 'Blog', 'Checkout', 'More');
	
	if(!in_array($sidebar, $ids)) {
		$sidebar = 'main';
	}
	?>
	<p>Choose a Sidebar to display on this Page</p>
	<select name="_progo_sidebar"><?php
for ( $i = 0; $i < count($ids); $i++) {
		echo '<option value="'. $ids[$i] .'"'. ( $ids[$i] == $sidebar ? ' selected="selected"' : '' ) .'>'. esc_attr( $titles[$i] ) .'</option>';
	} ?></select>
    <p><a href="<?php echo admin_url('widgets.php'); ?>" target="_blank">Configure Widgets Here &raquo;</a></p>
	<?php
}
endif;

/********* core ProGoDotCom functions *********/

if ( ! function_exists( 'progo_add_scripts' ) ):
/**
 * hooked to 'wp_print_scripts' by add_action in progo_setup()
 * adds front-end js
 * @since BookIt 1.0
 */
function progo_add_scripts() {
	if ( !is_admin() ) {
		/*
		wp_enqueue_script( 'cufon-yui', get_bloginfo('template_url') .'/js/cufon-yui.js', array('jquery'), '1.0.9i' );
		wp_enqueue_script( 'cufon-Titillium', get_bloginfo('template_url') .'/js/TitilliumText.font.js', array('jquery', 'cufon-yui'), '1.0.9i' );
		wp_enqueue_script( 'progo', get_bloginfo('template_url') .'/js/progo-frontend.js', array('jquery', 'cufon-yui'), '1.0' );
		*/
		wp_enqueue_script( 'progo', get_bloginfo('template_url') .'/js/progo-frontend.js', array('jquery'), '1.0' );
				
		wp_register_script('jquery-cycle', NGGALLERY_URLPATH .'js/jquery.cycle.all.min.js', array('jquery'), '2.88');
		wp_enqueue_script('ngg-slideshow', NGGALLERY_URLPATH .'js/ngg.slideshow.min.js', array('jquery-cycle'), '1.05'); 
	} else {
		if ( $_GET['page'] == 'progo_home_slides' ) {
			# here be drag'ns
            wp_enqueue_script('post');
            wp_enqueue_script('progo-homeslides-admin', get_bloginfo( 'template_url' ) .'/js/homeslides-admin.js', array ( 'jquery', 'post' ), false, true );
        }
	}
}
endif;
if ( ! function_exists( 'progo_add_styles' ) ):
/**
 * hooked to 'wp_print_styles' by add_action in progo_setup()
 * checks for Color Scheme setting and adds appropriate front-end stylesheet
 * @since ProGoDotCom 1.0
 */
function progo_add_styles() {
	if ( !is_admin() ) {
		//
	}
}
endif;
if ( ! function_exists( 'progo_reset_wpsc' ) ):
/**
 * sets WPSC image/thumbnail sizes to ProGo recommended settings
 * also updates wpsc_email_receipt
 * @since ProGoDotCom 1.0
 */
function progo_reset_wpsc($fromlink = false){
	if ( $fromlink == true ) {
		check_admin_referer( 'progo_reset_wpsc' );
	}
	//set thumbnail & main image size to desired dimensions
	update_option( 'product_image_width', 70 );
	update_option( 'product_image_height', 70 );
	update_option( 'single_view_image_width', 290 );
	update_option( 'single_view_image_height', 290 );
	
	update_option( 'wpsc_email_receipt', "Any items to be shipped will be processed as soon as possible, any items that can be downloaded can be downloaded using the links on this page. All prices include tax and postage and packaging where applicable.\n\n%product_list%%total_price%%find_us%" );
	
	if ( $fromlink == true ) {
		wp_redirect( get_option('siteurl') .'/wp-admin/themes.php?page=progo_admin' );
		exit();
	}
}
endif;
if ( ! function_exists( 'progo_header_check' ) ):
/**
 * function mainly to redirect to checkout from directresponse.php pages
 * @since Direct 1.0.55
 */
function progo_header_check(){
	if($_REQUEST['progo_action'] == 'step2') {
		$formdata = $_REQUEST['collected_data'];
		/*
		// store form in progo_crm db table
		global $wpdb;
		$rows_affected = $wpdb->insert($wpdb->prefix . "progo_crm", array( 'id' => '', 'time' => current_time('mysql'), 'firstname' => $formdata[2], 'lastname' => $_REQUEST['lastname'], 'address' => $_REQUEST['address'], 'city' => $_REQUEST['city'], 'state' => $_REQUEST['state'], 'zip' => $_REQUEST['zip'], 'phone' => $_REQUEST['phone'], 'email' => $_REQUEST['email'], 'purchased' => 0 ));
		*/
		// and set wpsc...
		$_SESSION['wpsc_checkout_saved_values'] = $formdata;
    	global $wpdb;
   		$checkoutid = $wpdb->get_var("SELECT ID FROM $wpdb->posts WHERE post_name = 'checkout' AND post_status = 'publish' AND post_type = 'page'");
		$checkouturl = get_permalink($checkoutid);
		header('Location: '. $checkouturl);
		die;
	}
}
endif;
if ( ! function_exists( 'progo_arraytotop' ) ):
/**
 * helper function to bring a given element to the start of an array
 * @param parent array
 * @param element to bring to the top
 * @return sorted array
 * @since ProGoDotCom 1.0
 */
function progo_arraytotop($arr, $totop) {
	// Backup and delete element from parent array
	$toparr = array($totop => $arr[$totop]);
	unset($arr[$totop]);
	// Merge the two arrays together so our widget is at the beginning
	return array_merge( $toparr, $arr );
}
endif;

if ( ! function_exists( 'progo_save_meta' ) ):
/**
 * hooked to 'save_post' by add_action in progo_setup()
 * checks for _progo (direct) meta data, and performs validation & sanitization
 * @param post_id to check meta on
 * @return post_id
 * @since DotCom 1.0
 */
function progo_save_meta( $post_id ){
	// verify if this is an auto save routine. If it is,
	// our form has not been submitted, so we don't want to do anything
	if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) { 
		return $post_id;
	}
	switch ( $_POST['post_type'] ) {
		case 'page':
			if ( !current_user_can( 'edit_page', $post_id ) ) {
				return $post_id;
			}
		
			// OK, we're authenticated: we need to find and save the data
			if ( isset( $_POST['_progo_sidebar'] ) ) {
				$sidebar = $_POST['_progo_sidebar'];
				
				if ( in_array ( $sidebar, array('main', 'blog', 'checkout', 'moreright') ) ) {
					update_post_meta($post_id, "_progo_sidebar", $sidebar);
					return $sidebar;
				}
			}
			break;
		case 'wpsc-product':
			if ( isset( $_POST['_progo_demo'] ) ) {
				$pdemo = esc_url($_POST['_progo_demo']);
				update_post_meta($post_id, "_progo_demo", $pdemo);
			}
			if ( isset( $_POST['_progo_pmm'] ) ) {
				$progo_pmm = $_POST['_progo_pmm'];
				if(is_array($progo_pmm)) {
					arsort($progo_pmm);
					update_post_meta($post_id, "_progo_pmm", $progo_pmm);
					// i know this could be done upon ADDING a new term the PM terms but w/e
					$marketing_terms = get_terms( 'progo_pmm', array (
						'hide_empty' => 0,
						'parent'     => 0
					) );
//					$cached_terms = get_option( 'progo_pmm_terms' );
					$termcache = array();
					foreach ( $marketing_terms as $t ) {
						$termcache[$t->term_id] = $t->name;
					}
					update_option( 'progo_pmm_terms', $termcache );
					return $progo_pmm;
				}
			}
			break;
	}
	return $post_id;
}
endif;
/**
 * ProGo Site Settings Options defaults
 * @since ProGoDotCom 1.0
 */
function progo_options_defaults() {
	// Define default option settings
	$tmp = get_option( 'progo_options' );
    if ( !is_array( $tmp ) ) {
		$def = array(
			"blogname" => get_option( 'blogname' ),
			"blogdescription" => get_option( 'blogdescription' ),
			"showdesc" => 1,
			"support" => "123-555-7890",
			"copyright" => "© Copyright 2011, All Rights Reserved",
			"credentials" => "",
			"companyinfo" => "We sincerely thank you for your patronage.\nThe Our Company Staff\n\nOur Company, Inc.\n1234 Address St\nSuite 43\nSan Diego, CA 92107\n619-555-5555",
			"frontpage" => get_option( 'show_on_front' )
		);
		update_option( 'progo_options', $def );
	}
	$tmp = get_option( 'progo_slides' );
    if ( !is_array( $tmp ) ) {
		$def = array('count'=>0);	
		update_option( 'progo_slides', $def );
	}
	
	update_option( 'progo_dotcom_installed', true );
	
	update_option( 'wpsc_ignore_theme', true );
	
	// set large image size
	update_option( 'large_size_w', 650 );
	update_option( 'large_size_h', 413 );
}
if ( ! function_exists( 'progo_validate_homeslides' ) ):
/**
 * ProGo Homeslides Options settings validation function
 * @param $input options to validate
 * @return $input after validation has taken place
 * @since ProGoDotCom 1.0
 */
function progo_validate_homeslides( $input ) {
	$counto = absint( $input['count'] );
	unset( $input['count'] );
	$newslides = array();
	$count = 0;
	foreach ( $input as $slide ) {
		$newslide = array();
		$newslide['show'] = $slide['show'];
		$newslide['title'] = isset($slide['title']) ? wp_kses($slide['title'], array()) : '';
		$newslide['product'] = isset($slide['product']) ? absint($slide['product']) : 0;
		$newslide['text'] = isset($slide['text']) ? wp_kses($slide['text'], array()) : '';
		$newslide['image'] = isset($slide['image']) ? esc_url($slide['image']) : '';
		$newslides[] = $newslide;
		$count++;
	}
	// check for new slide addition ...
	for ( $i = $count; $i < $counto; $i++ ) {
		$newslides[] = array(
			'show' => '',
			'title' => '',
			'product' => 0,
			'text' => '',
			'image' => ''
		);
	}
	$newslides['count'] = $counto;
	$input = $newslides;
	return $input;
}
endif;
if ( ! function_exists( 'progo_validate_options' ) ):
/**
 * ProGo Site Settings Options validation function
 * from register_setting( 'progo_options', 'progo_options', 'progo_validate_options' );
 * in progo_admin_init()
 * also handles uploading of custom Site Logo
 * @param $input options to validate
 * @return $input after validation has taken place
 * @since ProGoDotCom 1.0
 */
function progo_validate_options( $input ) {
		// do validation here...
	$arr = array( 'blogname', 'blogdescription', 'support', 'copyright', 'companyinfo' );
	foreach ( $arr as $opt ) {
		$input[$opt] = wp_kses( $input[$opt], array() );
	}
	
	$choices = array(
		'posts',
		'featured',
		'page'
	);
	if ( !in_array( $input['frontpage'], $choices ) ) {
		$input['frontpage'] = get_option('show_on_front');
	}
	switch ( $input['frontpage'] ) {
		case 'posts':
			update_option( 'show_on_front', 'posts' );
			break;
		case 'featured':
		case 'page':
			update_option( 'show_on_front', 'page' );
			update_option( 'page_on_front', get_option('progo_homepage_id') );
			break;
	}
	
	// opt[showdesc] can only be 1 or 0
	if ( (int) $input['showdesc'] != 1 ) {
		$input['showdesc'] = 0;
	}
	
	// save blogname & blogdescription to other options as well
	$arr = array( 'blogname', 'blogdescription' );
	foreach ( $arr as $opt ) {
		if ( $input[$opt] != get_option( $opt ) ) {
			update_option( $opt, $input[$opt] );
		}
	}
	
	// check SUPPORT field & set option['support_email'] flag if we have an email
	$input['support_email'] = is_email( $input['support'] );
	
	update_option('progo_settings_just_saved',1);
	
	return $input;
}
endif;

/********* more helper functions *********/
if ( ! function_exists( 'progo_field_blogname' ) ):
/**
 * outputs HTML for "Site Name" field on Site Settings page
 * @since ProGoDotCom 1.0
 */
function progo_field_blogname() {
	$opt = get_option( 'blogname' );
	echo '<input id="blogname" name="progo_options[blogname]" class="regular-text" type="text" value="'. esc_html( $opt ) .'" />';
}
endif;
if ( ! function_exists( 'progo_field_blogdesc' ) ):
/**
 * outputs HTML for "Slogan" field on Site Settings page
 * @since ProGoDotCom 1.0
 */
function progo_field_blogdesc() {
	$opt = get_option( 'blogdescription' ); ?>
<input id="blogdescription" name="progo_options[blogdescription]" class="regular-text" type="text" value="<?php esc_html_e( $opt ); ?>" />
<?php }
endif;
if ( ! function_exists( 'progo_field_showdesc' ) ):
/**
 * outputs HTML for checkbox "Show Slogan" field on Site Settings page
 * @since ProGoDotCom 1.0
 */
function progo_field_showdesc() {
	$options = get_option( 'progo_options' ); ?>
<fieldset><legend class="screen-reader-text"><span>Show Slogan</span></legend><label for="progo_showdesc">
<input type="checkbox" value="1" id="progo_showdesc" name="progo_options[showdesc]"<?php
	if ( (int) $options['showdesc'] == 1 ) {
		echo ' checked="checked"';
	} ?> />
Show the Site Slogan next to the Logo at the top of <a target="_blank" href="<?php echo esc_url( trailingslashit( get_bloginfo( 'url' ) ) ); ?>">your site</a></label>
</fieldset>
<?php }
endif;
if ( ! function_exists( 'progo_field_support' ) ):
/**
 * outputs HTML for "Customer Support" field on Site Settings page
 * @since ProGoDotCom 1.0
 */
function progo_field_support() {
	$options = get_option( 'progo_options' );
	?>
<input id="progo_support" name="progo_options[support]" value="<?php esc_html_e( $options['support'] ); ?>" class="regular-text" type="text" />
<span class="description">Enter either a Phone # (like <em>222-333-4444</em>) or email address</span>
<?php }
endif;
if ( ! function_exists( 'progo_field_copyright' ) ):
/**
 * outputs HTML for "Copyright Notice" field on Site Settings page
 * @since ProGoDotCom 1.0
 */
function progo_field_copyright() {
	$options = get_option( 'progo_options' );
	?>
<input id="progo_copyright" name="progo_options[copyright]" value="<?php esc_html_e( $options['copyright'] ); ?>" class="regular-text" type="text" />
<span class="description">Copyright notice that appears on the right side of your site's footer.</span>
<?php }
endif;
if ( ! function_exists( 'progo_field_cred' ) ):
/**
 * outputs HTML for "Security Logos" field on Site Settings page
 * @since ProGoDotCom 1.0
 */
function progo_field_cred() {
	$options = get_option( 'progo_options' ); ?>
<textarea id="progo_secure" name="progo_options[credentials]" style="width: 95%;"><?php esc_html_e( $options['credentials'] ); ?></textarea><br />
<span class="description">Security Logos can help increase your site's conversion by over 20%. Paste any code that is associated with generating your credentials in the text box above. Please separate each credential's code by a space (ie. "&lt;script type="text/javascript" src="https://godaddy.com/..."&gt;&lt;/span&gt; &lt;script type="text/javascript" src="http://www.verisign.com/..."&gt;&lt;/script&gt;").</span>
<?php }
endif;
if ( ! function_exists( 'progo_field_compinf' ) ):
/**
 * outputs HTML for "Security Logos" field on Site Settings page
 * @since ProGoDotCom 1.0
 */
function progo_field_compinf() {
	$options = get_option( 'progo_options' ); ?>
<textarea id="progo_companyinfo" name="progo_options[companyinfo]" style="width: 95%;" rows="5"><?php esc_html_e( $options['companyinfo'] ); ?></textarea><br />
<span class="description">This text appears at the end of Transaction Results pages and email receipts.</span>
<?php }
endif;
if ( ! function_exists( 'progo_field_frontpage' ) ):
/**
 * outputs HTML for Homepage "Displays" field on Site Settings page
 * @since ProGoDotCom 1.0
 */
function progo_field_frontpage() {
	// Latest Blog Posts, (Featured Products), Static Content
	$choices = array(
		'posts' => 'Latest Blog Posts',
		'featured' => 'Featured Products',
		'page' => 'Static Content'
	);
	$msgs = array(
		'posts' => '<a href="edit.php">Edit Posts Here</a>',
		'featured' => '<a href="edit.php?post_type=wpsc-product">Designate Featured Products Here</a>',
		'page' => '<a href="post.php?post='. get_option('progo_homepage_id') .'&action=edit">Edit Homepage Content Here</a>'
	);
	$msg = '';
	if ( !function_exists('wpsc_admin_pages')) {
		unset($choices['featured']);
		$msg .= 'Your Homepage can display "Featured" Products, but WP E-Commerce Plugin appears to be inactive. ';
		$lnk = ( function_exists( 'wp_nonce_url' ) ) ? wp_nonce_url('plugins.php?action=activate&amp;plugin=wp-e-commerce/wp-shopping-cart.php&amp;plugin_status=all&amp;paged=1', 'activate-plugin_wp-e-commerce/wp-shopping-cart.php') : 'plugins.php';
		$msg .= '<a href="'. esc_url($lnk) .'">Click Here to Activate</a>';
	}
	
//	$msg .= '<pre>'. print_r(get_option('show_on_front'),true)  .'</pre>'. print_r(get_option('page_on_front'),true) .'</pre>'. print_r(get_option('page_for_posts'),true) .'</pre>';
	
	$options = get_option( 'progo_options' );
	// check just in case show_on_front changed since this was last updated?
	// $options['frontpage'] = get_option('show_on_front');
	
	?><p><select id="progo_frontpage" name="progo_options[frontpage]" onchange="progo_frontpage_msg();"><?php
    foreach ( $choices as $k => $c ) {
		echo '<option value="'. $k .'"';
		if( $k == $options['frontpage'] ) {
			echo ' selected="selected"';
		}
		echo '>'. esc_attr($c) .'</option>';
	}
    ?></select><span class="description"><?php echo ( $msg != '' ? $msg : $msgs[$options['frontpage']] ); ?></span></p>
<script type="text/javascript">
function progo_frontpage_msg() {
	var msg = '';
	var sel = jQuery('#progo_frontpage');
	switch( sel.val() ) { <?php
	foreach ( $msgs as $k => $v ) {
		echo "case '$k':\n";
			echo "msg = '$v';\n";
			echo "break;";
	} ?>
	}
	sel.next().html(msg);
}
</script>
<?php }
endif;
if ( ! function_exists( 'progo_field_homeseconds' ) ):
/**
 * outputs HTML for Homepage "Cycle Seconds" field on Site Settings page
 * @since ProGoDotCom 1.0
 */
function progo_field_homeseconds() {
	$options = get_option( 'progo_options' );
	// check just in case show_on_front changed since this was last updated?
	// $options['frontpage'] = get_option('show_on_front');
	?><p><input id="progo_homeseconds" name="progo_options[homeseconds]" type="text" size="2" value="<?php echo absint($options['homeseconds']); ?>"><span class="description"> sec. per slide. Enter "0" to disable auto-rotation.</span></p>
<?php }
endif;
if ( ! function_exists( 'progo_section_text' ) ):
/**
 * (dummy) function called by 
 * add_settings_section( [id] , [title], 'progo_section_text', 'progo_site_settings' );
 * echos anchor link for that section
 * @since ProGoDotCom 1.0
 */
function progo_section_text( $args ) {
	echo '<a name="'. $args['id'] .'"></a>';
}
endif;
if ( ! function_exists( 'progo_price' ) ):
/**
 * price formatting function
 * returns the given product's price, with appropriate HTML for display
 * @param integer product ID
 * @uses absint() To sanitize the input parameter.
 * @uses get_post_meta() To retrieve the _wpsc_price for given product ID.
 * @uses get_option() To get the decimal separator string, and the selected currency_type
 * @uses wpdb->get_results() To retrieve the proper currency symbol from the WPSC_TABLE_CURRENCY_LIST db
 * @return formatted html to display product's price
 * @since Direct 1.0
 */
function progo_price( $prod ) {
	$prod = absint( $prod );
	if ( $prod <= 0 ) {
		return '';
	}
	$price = get_post_meta( $prod, '_wpsc_price', true );
	
	$oot = progo_price_helper($price);
//meta[_wpsc_special_price]
/*
	$saleprice = get_post_meta( $prod, '_wpsc_special_price', true );
	if($saleprice) {
		$oot = '<span class="s">'. $oot .'</span>&nbsp;&nbsp;<span class="sale">'. progo_price_helper($saleprice) .'</span>';
	}
	*/
	return $oot;
}
endif;
if ( ! function_exists( 'progo_price_helper' ) ):
/**
 * price formatting function
 * returns the given product's price, with appropriate HTML for display
 * @param integer product ID
 * @uses absint() To sanitize the input parameter.
 * @uses get_post_meta() To retrieve the _wpsc_price for given product ID.
 * @uses get_option() To get the decimal separator string, and the selected currency_type
 * @uses wpdb->get_results() To retrieve the proper currency symbol from the WPSC_TABLE_CURRENCY_LIST db
 * @return formatted html to display product's price
 * @since Direct 1.0.34
 */
function progo_price_helper( $price ) {
	global $wpdb;
	$sep = get_option( 'wpsc_decimal_separator' );
	// even though WPeC stores the "decimal separator" in the DB, the price is always stored with a "." separator...
	$dot = strrpos( $price, '.' );
	
	if ( $dot !== false ) {
		$price = substr( $price, 0, $dot ) .'<u>'. substr( $price, $dot + 1 ).'</u>';
	} else {
		$price .= '<u>00</u>';
	}
	$currency_data = $wpdb->get_results( "SELECT `symbol`,`symbol_html`,`code` FROM `" . WPSC_TABLE_CURRENCY_LIST . "` WHERE `id`='" . absint( get_option( 'currency_type' ) ) . "' LIMIT 1", ARRAY_A );
	if ( $currency_data[0]['symbol'] != '' ) {
		$currency_sign = $currency_data[0]['symbol_html'];
	} else {
		$currency_sign = $currency_data[0]['code'];
	}
	
	return '<span>'.$currency_sign.'</span>'. $price;
}
endif;
if ( ! function_exists( 'progo_bodyclasses' ) ):
/**
 * adds some additional classes to the <body> based on what page we're on
 * @param array of classes to add to the <body> tag
 * @since ProGoDotCom 1.0
 */
function progo_bodyclasses($classes) {
	switch ( get_post_type() ) {
		case 'wpsc-product':
			$classes[] = 'wpsc';
			break;
		case 'post':
			$classes[] = 'blog';
			break;
	}
	if(is_page(120)) { // /themes page
		$classes[] = 'wpsc';
	}
	if(is_page(45)) { // transaction-results page
		$classes[] = 'wpsc-shopping-cart';
	}
	
	return $classes;
}
endif;
if ( ! function_exists( 'progo_menuclasses' ) ):
/**
 * adds some additional classes to Menu Items
 * so we can mark active menu trails easier
 * @param array of classes to add to the <body> tag
 * @since ProGoDotCom 1.0
 */
function progo_menuclasses($items) {
	$blogID = get_option('progo_blog_id');
	foreach ( $items as $i ) {
		if ( $i->post_content == "[wpsc_products category_id='9' ]" ) {
			$i->classes[] = 'wpsc';
		}
		if ( $i->object_id == $blogID ) {
			$i->classes[] = 'blog';
		}
	}
	//wp_die('<pre>'.print_r($items,true) .'</pre>');
	return $items;
}
endif;
/**
 * hooked to 'admin_notices' by add_action in progo_setup()
 * used to display "Settings updated" message after Site Settings page has been saved
 * @uses get_option() To check if our Site Settings were just saved.
 * @uses update_option() To save the setting to only show the message once.
 * @since ProGoDotCom 1.0
 */
function progo_admin_notices() {
	if( get_option('progo_settings_just_saved')==true ) {
	?>
	<div id="message" class="updated fade">
		<p>Settings updated. <a href="<?php bloginfo('url'); ?>/">View site</a></p>
	</div>
<?php
		update_option('progo_settings_just_saved',false);
	}
}

if ( ! function_exists( 'progo_product_image_forms' ) ):
/**
 * html for WPSC product images meta box
 * @since ProGoDotCom 1.0
 */
function progo_product_image_forms() {

    global $post;
    
    edit_multiple_image_gallery( $post );

	$tab = has_post_thumbnail($post->ID) ? 'gallery' : 'type';
    ?>
    <p><strong <?php if ( isset( $display ) ) echo $display; ?>><a href="media-upload.php?parent_page=wpsc-edit-products&post_id=<?php echo $post->ID; ?>&type=image&tab=<?php echo esc_attr($tab); ?>&TB_iframe=1&width=640&height=566" class="thickbox" title="Manage Your Product Images"><?php _e( 'Manage Product Images', 'wpsc' ); ?></a></strong></p>
<?php
}
endif;
/**
 * hooked by add_filter to 'wp_before_admin_bar_render'
 * to tweak the new WP 3.1 ADMIN BAR
 * @since ProGoDotCom 1.0
 */
function progo_admin_bar_render() {
	global $wp_admin_bar;
	
	$wp_admin_bar->remove_menu('widgets');
	$wp_admin_bar->add_menu( array( 'id' => 'appearance', 'title' => __('Appearance'), 'href' => admin_url('themes.php?page=progo_admin') ) );
	// move Appearance > Widgets & Menus submenus to below our new ones
	$wp_admin_bar->remove_menu('widgets');
	$wp_admin_bar->remove_menu('menus');
	$wp_admin_bar->add_menu( array( 'parent' => 'appearance', 'id' => 'homeslides', 'title' => __('Homepage Slides'), 'href' => admin_url('admin.php?page=progo_home_slides') ) );
	$wp_admin_bar->add_menu( array( 'parent' => 'appearance', 'id' => 'menus', 'title' => __('Menus'), 'href' => admin_url('nav-menus.php') ) );
	$wp_admin_bar->add_menu( array( 'parent' => 'appearance', 'id' => 'widgets', 'title' => __('Widgets'), 'href' => admin_url('widgets.php') ) );
}

if(!function_exists('progo_mail_content_type')):
function progo_mail_content_type( $content_type ) {
	return 'text/html';
}
endif;

if(!function_exists('progo_st_widget_head')) :
function progo_st_widget_head() {
	$widget = get_option('st_widget');
	if ($widget != '') {
		$widget = preg_replace(
			"/\<script\s([^\>]*)src\=\"http\:\/\/sharethis/"
			, "<script $1src=\"https://ws.sharethis"
			, $widget
		);
		$widget = preg_replace("/\&/", "&amp;", $widget);
		$widget = str_replace('http://w.sharethis.com/button/buttons.js', 'https://ws.sharethis.com/button/buttons.js', $widget);
	}
	print($widget);
}
endif;

function progodotcom_gatewaycleanup() {
	// custoom cleanup of Paypal Pro gateway fields...
	$years = $months = '';
	$curryear = date( 'Y' );
	//generate year options
	for ( $i = 0; $i < 10; $i++ ) {
		$years .= "<option value='" . $curryear . "'>" . $curryear . "</option>\r\n";
		$curryear++;
	}
	$oot = "<tr><td><label>" . __( 'Card Type *', 'wpsc' ) . "</label><select class='wpsc_ccBox' name='cctype'>
			<option value='Visa'>" . __( 'Visa', 'wpsc' ) . "</option>
			<option value='Mastercard'>" . __( 'MasterCard', 'wpsc' ) . "</option>
			<option value='Discover'>" . __( 'Discover', 'wpsc' ) . "</option>
			<option value='Amex'>" . __( 'Amex', 'wpsc' ) . "</option>
		</select><label>" . __( 'Card Number *', 'wpsc' ) . "</label><input type='text' value='' name='card_number' class='text txt req' />
		<label>" . __( 'Expiration *', 'wpsc' ) . "</label>
		<select class='wpsc_ccBox' name='expiry[month]'>
			" . $months . "
			<option value='01'>01</option>
			<option value='02'>02</option>
			<option value='03'>03</option>
			<option value='04'>04</option>
			<option value='05'>05</option>						
			<option value='06'>06</option>						
			<option value='07'>07</option>					
			<option value='08'>08</option>						
			<option value='09'>09</option>						
			<option value='10'>10</option>						
			<option value='11'>11</option>																			
			<option value='12'>12</option>																			
			</select><select class='wpsc_ccBox' name='expiry[year]'>
			" . $years . "
			</select>
		<label>" . __( 'CVV *', 'wpsc' ) . "</label><input type='text' size='4' value='' maxlength='4' name='card_code' class='txt text req cvv' />
		</td>
	</tr>";
	global $gateway_checkout_form_fields;
	$gateway_checkout_form_fields[wpsc_merchant_paypal_pro] = $oot;
}

// this is where keys come from
function progodotcom_mail( $msg ) {
	if($msg[subject] == 'Purchase Receipt') {
		$dlstart = strpos($msg['message'],'?downloadid=') + 12;
		$dlend = strpos($msg['message'],'Total:', $dlstart);
		$dlid = trim(substr($msg['message'],$dlstart,$dlend-$dlstart));
		//$dlend = 
		
		global $wpdb;
		
		$downloadid = preg_replace( "/[^a-z0-9]+/i", '', strtolower( $dlid ) );
		$download_data = $wpdb->get_row( "SELECT * FROM `" . WPSC_TABLE_DOWNLOAD_STATUS . "` WHERE `uniqueid` = '" . $downloadid . "' AND `downloads` > '0' AND `active`='1' LIMIT 1", ARRAY_A );

		if ( ($download_data == null) && is_numeric( $downloadid ) ) {
			$download_data = $wpdb->get_row( "SELECT * FROM `" . WPSC_TABLE_DOWNLOAD_STATUS . "` WHERE `id` = '" . $downloadid . "' AND `downloads` > '0' AND `active`='1' AND `uniqueid` IS NULL LIMIT 1", ARRAY_A );
		}
		
		$file_id = $download_data['fileid'];
		$file_data = wpsc_get_downloadable_files($download_data['product_id']);		
		
		$themefile = $file_data[0]->post_title;
		$theme = substr($themefile,0,strlen($themefile)-4);
		
		$currtime = date('Y-m-d H:i:s');
		$new_key = md5(crypt($msg['to'] ." : $currtime : $theme"));
		
		$db   = mysql_connect('localhost', 'progokeys', 'NFUh02y67U1') or die('Could not connect: ' . mysql_error());
		mysql_select_db('progokeys') or die('Could not select database');
		$server_ip = $_SERVER['SERVER_ADDR'];
		$url = 'newkey';
		$user_agent = $dlid;
		
		$found = 0;
		$query = "SELECT * FROM progo_keys WHERE user_agent = '$user_agent'";
		$result = mysql_query($query);		
		while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
			$found++;
			$new_key = $row[api_key];
		}
		if( $found == 0 ) {
			//new key!
			$sql  = "INSERT INTO progo_keys (";
			$sql .= "ID,";
			$sql .= "url,";
			$sql .= "server_ip,";
			$sql .= "api_key,";
			$sql .= "theme,";
			$sql .= "user_agent,";
			$sql .= "last_checked,";
			$sql .= "auth_code";
			$sql .= ") VALUES (";
			$sql .= "NULL,";
			$sql .= "'$url',";
			$sql .= "'$server_ip',";
			$sql .= "'$new_key',";
			$sql .= "'$themeslug',";
			$sql .= "'$user_agent',";
			$sql .= "'$currtime',";
			$sql .= "0";
			$sql .= ")";
			
			mysql_query($sql) || wp_die("Invalid query: $sql<br>\n" . mysql_error());
		}
		mysql_close($db);
		
		$nice_key = implode( '-', str_split( strtoupper( $new_key ), 4) );
		$msg['message'] = nl2br(substr($msg['message'],0,$dlend). "API KEY: $nice_key\n\n" .substr($msg['message'],$dlend));
		
		//wp_die('<pre>'.print_r($msg,true).'</pre>');
		
	}
	return $msg;
}

if(!function_exists('progodotcom_performance_meter')):
function progodotcom_performance_meter($postid) {
	$marketing_terms = get_option( 'progo_pmm_terms' );
	$custom = get_post_meta($postid,'_progo_pmm');
	$pmm_ratings = $custom[0];
	if(is_array($pmm_ratings)) {
		$pmm_arrow = isset($pmm_ratings['arrowd']) ? $pmm_ratings['arrowd'] : '';
		unset($pmm_ratings['arrowd']);
		echo '<div class="meter p'. $postid .'"><strong>Performance Marketing Meter</strong><ul>';
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
		if(is_front_page() == false) {
			if($pmm_arrow != '') {
				echo '<div id="tar"><span>'. $pmm_arrow .'</span></div>';
			}
		}
	}
}
endif;
if(!function_exists('progo_format_apikey')):
function progo_format_apikey($key) {
	return implode( '-', str_split( strtoupper( $key ), 4 ) );
}
endif;
if(!function_exists('progo_transaction_keycheck')):
function progo_transaction_keycheck( $additional_content, $purchase_id, $cart_item ) {
	global $sessionid, $purchase_log;
	//extract($args); // array( "purchase_id" => $purchase_log['id'], "cart_item" => $row, "purchase_log" => $purchase_log )
	$keydb = new wpdb('progokeys', 'NFUh02y67U1', 'progokeys', 'localhost');
	$key_row = $keydb->get_row( "SELECT * FROM `progo_keys` WHERE `wpec_id` = ". $sessionid );
	$apikey = '';
	$email = wpsc_get_buyers_email($purchase_log['id']);
	$theme = strtolower( str_replace( ' ', '', $cart_item['name'] ) );
	$currtime = date('Y-m-d H:i:s');
	
	if(is_null($key_row)) {
		$newkey = md5(crypt("$email : $currtime : $theme"));
		
		$server_ip = $_SERVER['SERVER_ADDR'];
		$user_agent = $_SERVER['HTTP_USER_AGENT'];
		
		$keydb->insert( 'progo_keys', array(
			'url' => 'newkey',
			'server_ip' => $server_ip,
			'api_key' => $newkey,
			'theme' => $theme,
			'user_agent' => $user_agent,
			'last_checked' => $currtime,
			'auth_code' => 0,
			'wpec_id' => $sessionid
		), array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s' ) );
		$apikey = progo_format_apikey($newkey);
		
		$email_headers = 'From: ProGo Themes <info@progo.com>' . "\r\n";
		$msg = "Thank you for purchasing the ". $cart_item['name'] ." theme.<br /><br />Your new API key is: ". $apikey;
		$msg .= "<br /><br />You may also want to <a href='http://www.progo.com/resources/QuickStartGuide-". str_replace(' ','-', $cart_item['name']) .".pdf'>download the Quick Start Guide (pdf)</a>.";
		$msg .="<br /><br />And if you have not yet downloaded your new theme files, you can also find that link on your <a href='http://www.progo.com/products-page/transaction-results/?sessionid=". $sessionid ."'>Transaction Results page</a>.<br /><br />Enjoy!<br />- ProGo";
		wp_mail($email, 'Your ProGo Theme API Key', $msg, $email_headers);
	} else {
		$apikey = progo_format_apikey($key_row->api_key);
	}
	$oot = "API Key: $apikey";
	return $additional_content ."\n$oot"; // ."\npurchase_log : <pre>". print_r($purchase_log,true) ."</pre>";
}
endif;
if(!function_exists('progo_change_tax') ) :
/**
 * overrides wpsc_change_tax so we can use state abbreviations instead of full names...
 * @since Direct 1.0.59
 */
function progo_change_tax() {
	global $wpdb, $wpsc_cart;

	$form_id = absint( $_POST['form_id'] );

	$wpsc_selected_country = $wpsc_cart->selected_country;
	$wpsc_selected_region = $wpsc_cart->selected_region;

	$wpsc_delivery_country = $wpsc_cart->delivery_country;
	$wpsc_delivery_region = $wpsc_cart->delivery_region;


	$previous_country = $_SESSION['wpsc_selected_country'];
	if ( isset( $_POST['billing_country'] ) ) {
		$wpsc_selected_country = $wpdb->escape( $_POST['billing_country'] );
		$_SESSION['wpsc_selected_country'] = $wpsc_selected_country;
	}

	if ( isset( $_POST['billing_region'] ) ) {
		$wpsc_selected_region = absint( $_POST['billing_region'] );
		$_SESSION['wpsc_selected_region'] = $wpsc_selected_region;
	}

	$check_country_code = $wpdb->get_var( " SELECT `country`.`isocode` FROM `" . WPSC_TABLE_REGION_TAX . "` AS `region` INNER JOIN `" . WPSC_TABLE_CURRENCY_LIST . "` AS `country` ON `region`.`country_id` = `country`.`id` WHERE `region`.`id` = '" . $_SESSION['wpsc_selected_region'] . "' LIMIT 1" );

	if ( $_SESSION['wpsc_selected_country'] != $check_country_code ) {
		$wpsc_selected_region = null;
	}

	if ( isset( $_POST['shipping_country'] ) ) {
		$wpsc_delivery_country = $wpdb->escape( $_POST['shipping_country'] );
		$_SESSION['wpsc_delivery_country'] = $wpsc_delivery_country;
	}
	if ( isset( $_POST['shipping_region'] ) ) {
		$wpsc_delivery_region = absint( $_POST['shipping_region'] );
		$_SESSION['wpsc_delivery_region'] = $wpsc_delivery_region;
	}

	$check_country_code = $wpdb->get_var( " SELECT `country`.`isocode` FROM `" . WPSC_TABLE_REGION_TAX . "` AS `region` INNER JOIN `" . WPSC_TABLE_CURRENCY_LIST . "` AS `country` ON `region`.`country_id` = `country`.`id` WHERE `region`.`id` = '" . $wpsc_delivery_region . "' LIMIT 1" );

	if ( $wpsc_delivery_country != $check_country_code ) {
		$wpsc_delivery_region = null;
	}


	$wpsc_cart->update_location();
	$wpsc_cart->get_shipping_method();
	$wpsc_cart->get_shipping_option();
	if ( $wpsc_cart->selected_shipping_method != '' ) {
		$wpsc_cart->update_shipping( $wpsc_cart->selected_shipping_method, $wpsc_cart->selected_shipping_option );
	}

	$tax = $wpsc_cart->calculate_total_tax();
	$total = wpsc_cart_total();
	$total_input = wpsc_cart_total(false);
	if($wpsc_cart->coupons_amount >= wpsc_cart_total() && !empty($wpsc_cart->coupons_amount)){
		$total = 0;
	}
	if ( $wpsc_cart->total_price < 0 ) { 
		$wpsc_cart->coupons_amount += $wpsc_cart->total_price; 
		$wpsc_cart->total_price = null; 
		$wpsc_cart->calculate_total_price(); 
	} 
	ob_start();

	include_once( wpsc_get_template_file_path( 'wpsc-cart_widget.php' ) );
	$output = ob_get_contents();

	ob_end_clean();

	$output = str_replace( Array( "\n", "\r" ), Array( "\\n", "\\r" ), addslashes( $output ) );
	if ( get_option( 'lock_tax' ) == 1 ) {
		echo "jQuery('#current_country').val('" . $_SESSION['wpsc_delivery_country'] . "'); \n";
		if ( $_SESSION['wpsc_delivery_country'] == 'US' && get_option( 'lock_tax' ) == 1 ) {
			$output = wpsc_shipping_region_list( $_SESSION['wpsc_delivery_country'], $_SESSION['wpsc_delivery_region'] );
			$output = str_replace( Array( "\n", "\r" ), Array( "\\n", "\\r" ), addslashes( $output ) );
			echo "jQuery('#region').remove();\n\r";
			echo "jQuery('#change_country').append(\"" . $output . "\");\n\r";
		}
	}


	foreach ( $wpsc_cart->cart_items as $key => $cart_item ) {
		echo "jQuery('#shipping_$key').html(\"" . wpsc_currency_display( $cart_item->shipping ) . "\");\n\r";
	}

	echo "jQuery('#checkout_shipping').html(\"" . wpsc_cart_shipping() . "\");\n\r";

	//echo "jQuery('div.shopping-cart-wrapper').html('$output');\n";
	if ( get_option( 'lock_tax' ) == 1 ) {
		echo "jQuery('.shipping_country').val('" . $_SESSION['wpsc_delivery_country'] . "') \n";
		$sql = "SELECT `country` FROM `" . WPSC_TABLE_CURRENCY_LIST . "` WHERE `isocode`='" . $_SESSION['wpsc_selected_country'] . "'";
		$country_name = $wpdb->get_var( $sql );
		echo "jQuery('.shipping_country_name').html('" . $country_name . "') \n";
	}


	$form_selected_country = null;
	$form_selected_region = null;
	$onchange_function = null;

	if ( ($_POST['billing_country'] != 'undefined') && !isset( $_POST['shipping_country'] ) ) {
		$form_selected_country = $wpsc_selected_country;
		$form_selected_region = $wpsc_selected_region;
		$onchange_function = 'progo_set_billing_country';
	} else if ( ($_POST['shipping_country'] != 'undefined') && !isset( $_POST['billing_country'] ) ) {
		$form_selected_country = $wpsc_delivery_country;
		$form_selected_region = $wpsc_delivery_region;
		$onchange_function = 'progo_set_billing_country';
	}

	if ( ($form_selected_country != null) && ($onchange_function != null) ) {
		$region_list = $wpdb->get_results( "SELECT `" . WPSC_TABLE_REGION_TAX . "`.* FROM `" . WPSC_TABLE_REGION_TAX . "`, `" . WPSC_TABLE_CURRENCY_LIST . "`  WHERE `" . WPSC_TABLE_CURRENCY_LIST . "`.`isocode` IN('" . $form_selected_country . "') AND `" . WPSC_TABLE_CURRENCY_LIST . "`.`id` = `" . WPSC_TABLE_REGION_TAX . "`.`country_id`", ARRAY_A );
		if ( $region_list != null ) {
			$title = (empty($_POST['billing_country']))?'shippingstate':'billingstate';
			$output = "<select name='collected_data[" . $form_id . "][1]' class='current_region' onchange='$onchange_function(\"region_country_form_$form_id\", \"$form_id\");' title='" . $title . "'>\n\r";

			foreach ( $region_list as $region ) {
				if ( $form_selected_region == $region['id'] ) {
					$selected = "selected='selected'";
				} else {
					$selected = "";
				}
				$output .= "   <option value='" . $region['id'] . "' $selected>" . htmlspecialchars( $region['code'] ) . "</option>\n\r";
			}
			$output .= "</select>\n\r";

			$output = str_replace( Array( "\n", "\r" ), Array( "\\n", "\\r" ), addslashes( $output ) );
			echo "jQuery('#region_select_$form_id').html(\"" . $output . "\");\n\r";
			echo "
				progo_selectcheck('$form_id', true);
			";
		} else {
			if ( get_option( 'lock_tax' ) == 1 ) {
				echo "jQuery('#region').hide();";
			}
			echo "jQuery('#region_select_$form_id').html('');\n\r";
			echo "
				progo_selectcheck('$form_id', false);
			";
		}
	}
	if ( $tax > 0 ) {
		echo "jQuery(\"tr.total_tax\").show();\n\r";
	} else {
		echo "jQuery(\"tr.total_tax\").hide();\n\r";
	}
	echo "jQuery('#checkout_tax').html(\"<span class='pricedisplay'>" . wpsc_cart_tax() . "</span>\");\n\r";
	echo "jQuery('#checkout_total').html(\"{$total}<input id='shopping_cart_total_price' type='hidden' value='{$total_input}' />\");\n\r";
	echo "if(jQuery(\"#shippingSameBilling\").is(\":checked\")) wpsc_shipping_same_as_billing();";
	exit();
}
endif;
// execute on POST and GET
if ( isset( $_REQUEST['wpsc_ajax_action'] ) && ($_REQUEST['wpsc_ajax_action'] == 'change_tax') ) {
	remove_action( 'init', 'wpsc_change_tax' );
	add_action( 'init', 'progo_change_tax' );
}