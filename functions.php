<?php
/**
 * @package ProGo
 * @subpackage ProGoDotCom
 * @since ProGoDotCom 1.0
 *
 * Defines all the functions, actions, filters, widgets, etc., for ProGoDotCom theme.
 *
 * Some actions for Child Themes to hook in to are:
 * progo_frontend_scripts, progo_frontend_styles, progo_direct_after_arrow (called on directresponse.php page)
 *
 * Some overwriteable functions ( wrapped by "if(!function_exists(..." ) are:
 * progo_sitelogo, progo_posted_on, progo_posted_in, progo_productimage, progo_prepare_transaction_results,
 * progo_admin_menu_cleanup, progo_custom_login_logo, progo_custom_login_url, progo_metabox_cleanup ...
 *
 * Most Action / Filters hooks are set in the progo_setup function, below. overwriting that could cause quite a few things to go wrong.
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
	add_action('wp_print_scripts', 'progo_add_scripts');
	add_action('wp_print_styles', 'progo_add_styles');
	add_action( 'admin_notices', 'progo_admin_notices' );
	add_action( 'wp_before_admin_bar_render', 'progo_admin_bar_render' );
	
	// add custom filters
	add_filter( 'body_class', 'progo_bodyclasses' );
	add_filter( 'wp_nav_menu_objects', 'progo_menuclasses' );
	add_filter( 'wpsc_pre_transaction_results', 'progo_prepare_transaction_results' );
	add_filter( 'wp_mail_content_type', 'progo_mail_content_type' );
}
endif;

/********* Front-End Functions *********/

if ( ! function_exists( 'progo_sitelogo' ) ):
/**
 * prints out the HTML for the #logo area in the header of the front-end of the site
 * wrapped so child themes can overwrite if desired
 * @since ProGoDotCom 1.0.46
 */
function progo_sitelogo() {
	$options = get_option( 'progo_options' );
	$progo_logo = $options['logo'];
	$upload_dir = wp_upload_dir();
	$dir = trailingslashit($upload_dir['baseurl']);
	$imagepath = $dir . $progo_logo;
	if($progo_logo) {
		echo '<table id="logo"><tr><td><a href="'. get_bloginfo('url') .'"><img src="'. esc_attr( $imagepath ) .'" alt="'. esc_attr( get_bloginfo( 'name' ) ) .'" /></a></td></tr></table>';
	} else {
		echo '<a href="'. get_bloginfo('url') .'" id="logo">'. esc_html( get_bloginfo( 'name' ) ) .'<span class="g"></span></a>';
	}
}
endif;
if ( ! function_exists( 'progo_posted_on' ) ):
/**
 * Prints HTML with meta information for the current post—date/time and author.
 * @since ProGoDotCom 1.0
 */
function progo_posted_on() {
	printf( __( '<span class="meta-sep">Posted by</span> %1$s <span class="%2$s">on</span> %3$s', 'progo' ),
		sprintf( '<span class="author vcard"><a class="url fn n" href="%1$s" title="%2$s">%3$s</a></span>',
			get_author_posts_url( get_the_author_meta( 'ID' ) ),
			sprintf( esc_attr__( 'View all posts by %s', 'progo' ), get_the_author() ),
			get_the_author()
		),
		'meta-prep meta-prep-author',
		sprintf( '<a href="%1$s" title="%2$s" rel="bookmark"><span class="entry-date">%3$s</span></a>',
			get_permalink(),
			esc_attr( get_the_time() ),
			get_the_date()
		)
	);
	edit_post_link( __( 'Edit', 'progo' ), '<span class="meta-sep"> : </span> <span class="edit-link">', '</span>' );
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
	echo 'Categories : '. get_the_category_list( ', ' );
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
	$purchase_log['find_us'] = '<table><tr class="firstrow"><td>Our Company Info</td></tr><tr><td>'. esc_html( $options['companyinfo'] ) .'</td></tr></table>';
}
endif;
if ( ! function_exists( 'progo_summary' ) ):
/**
 * chops off (product) text either @ <!-- more --> or last space before 152 characters
 * @since ProGoDotCom 1.0
 */
function progo_summary( $morelink, $limit = 150 ) {
	global $post;
	$content = $post->post_content;
	$lbrat = strpos( $content, "\n" );
	if( $lbrat > 0 && $lbrat < $limit ) {
		$content = substr( $content, 0, $lbrat );
	} else {
		$content = substr( $content, 0, strrpos( substr( $content, 0, $limit ), ' ' ) ) ."...";
	}
	if( $morelink != false ) {
		$content .= "\n<a href='". wpsc_the_product_permalink() ."' class='more-link'>$morelink</a>";
	}
	echo wpautop($content);
}
endif;
/********* Back-End Functions *********/
if ( ! function_exists( 'progo_admin_menu_cleanup' ) ):
/**
 * hooked to 'admin_menu' by add_action in progo_setup()
 * @since ProGoDotCom 1.0
 */
function progo_admin_menu_cleanup() {
	global $menu;
	global $submenu;
	// move POSTS to farther down the line...
	$menu[7] = $menu[5];
	
	add_menu_page( 'Installation', 'ProGo Themes', 'edit_theme_options', 'progo_admin', 'progo_admin_page', get_bloginfo( 'template_url' ) .'/images/logo_menu.png', 5 );
	add_submenu_page( 'progo_admin', 'Installation', 'Installation', 'edit_theme_options', 'progo_admin', 'progo_admin_page' );
	add_submenu_page( 'progo_admin', 'Shipping Settings', 'Shipping Settings', 'edit_theme_options', 'progo_shipping', 'progo_admin_page' );
	add_submenu_page( 'progo_admin', 'Payment Gateway', 'Payment Gateway', 'edit_theme_options', 'progo_gateway', 'progo_admin_page' );
	add_submenu_page( 'progo_admin', 'Appearance', 'Appearance', 'edit_theme_options', 'progo_appearance', 'progo_admin_page' );
	add_submenu_page( 'progo_admin', 'Homepage Slides', 'Homepage Slides', 'edit_theme_options', 'progo_home_slides', 'progo_home_slides_page' );
	add_submenu_page( 'progo_admin', 'Menus', 'Menus', 'edit_theme_options', 'nav-menus.php' );
	add_submenu_page( 'progo_admin', 'Widgets', 'Widgets', 'edit_theme_options', 'widgets.php' );
	
	// add an extra dividing line...
	$menu[6] = $menu[4];
	
	//wp_die('<pre>'. print_r($menu,true) .'</pre>');
}
endif;
if ( ! function_exists( 'progo_admin_page_tabs' ) ):
/**
 * helper function to print tabs atop ProGo Admin Pages
 *
 * @param which tab we are on
 *
 * @since ProGoDotCom 1.0.3
 */
function progo_admin_page_tabs($thispage) {
	$tabs = array(
		'Installation' => 'progo_admin',
		'Shipping' => 'progo_shipping',
		'Payment' => 'progo_gateway',
		'Appearance' => 'progo_appearance',
		'Products' => 'progo_products'
	);
	if ( !in_array($thispage,$tabs) ) {
		echo '<h2>Huh?</h2>';
	} else {
		echo '<h2 class="nav-tab-wrapper">';
		foreach ( $tabs as $n => $p ) {
			$l = ( $n == 'Products' ) ? 'edit.php?post_type=wpsc-product' : 'admin.php?page='. $p;
			echo '<a class="nav-tab'. ($thispage == $p ? ' nav-tab-active' : '') .'" href="'. $l .'">'. $n .'</a>';
		}
		echo '</h2>';
	}
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
    <div class="icon32" id="icon-options-general"><br /></div>
    <?php progo_admin_page_tabs($thispage); ?>
    <div id="dashboard-widgets-wrap">
    <div id="dashboard-widgets" class="metabox-holder">
    	<div class="postbox-container" style="width:64%">
        	<div id="progo_setup_steps" class="postbox">
            	<h3 class="hndle"><span>WP e-Commerce Plugin</span></h3>
                <div class="inside">
                    <?php
	$options = get_option( 'progo_options' );
	// Display whatever it is you want to show
	$greyout = ' style="color: #999;"';
	echo "<h3>Your <em>Ecommerce</em> ProGo Theme works hand-in-hand with the <strong>WP e-Commerce</strong> Plugin.</h3><p>";
	// check for wp-e-commerce installed..
	$plugs = get_plugins();
	$goon = isset( $plugs['wp-e-commerce/wp-shopping-cart.php'] );
	if( $goon ) {
		echo "<strong>Congratulations!</strong> WP e-Commerce appears to be installed.";
	} else {
		$lnk = ( function_exists( 'wp_nonce_url' ) ) ? wp_nonce_url( 'update.php?action=install-plugin&amp;plugin=wp-e-commerce', 'install-plugin_wp-e-commerce' ) : 'plugin-install.php';
		echo '<a href="'. esc_url( $lnk ) .'" class="button-primary">Install the WP e-Commerce Plugin now</a></strong>';
		$goon = false;
	}
	echo '</p>';
	
	//is WP e-Commerce Plugin activated?
	if ( $goon ) {
		if ( function_exists('wpsc_admin_pages')) {
			echo "<p>WP e-Commerce Plugin is activated!</p>";
			
			//check wpsc settings dimensions for thumbnail (product_image) & product image (single_view_image)
			if ( get_option( 'product_image_width' ) == 70 && get_option( 'product_image_height' ) == 70 && get_option( 'single_view_image_width' ) == 290 && get_option( 'single_view_image_height' ) == 290 ) {
				echo "<p>WP e-Commerce settings match ProGo Themes' Recommended Settings!</p>";
			} else {
				echo "<p><strong>A few WP e-Commerce Store Settings, like Product Thumbnail Sizes, differ from ProGo Themes' Recommended Settings</strong><br /><br />";
				echo '<a href="'.wp_nonce_url("admin.php?progo_admin_action=reset_wpsc", 'progo_reset_wpsc').'" class="button-primary">Click Here to Reset</a></p>';
			}
			echo '<p><br /></p>';
			wpsc_options_general();
        } else {
			$lnk = ( function_exists( 'wp_nonce_url' ) ) ? wp_nonce_url('plugins.php?action=activate&amp;plugin=wp-e-commerce/wp-shopping-cart.php&amp;plugin_status=all&amp;paged=1', 'activate-plugin_wp-e-commerce/wp-shopping-cart.php') : 'plugins.php';
			echo '<p><a href="'. esc_url($lnk) .'" class="button-primary">Click Here to activate the WP e-Commerce Plugin</a><p>';
			$goon = false;
		}
	}
					?>
                </div>
            </div>
         </div>
         <div class="postbox-container" style="width:35%">
         	<div id="progo_welcome" class="postbox">
            	<h3 class="hndle"><span>Welcome</span></h3>
                <div class="inside">
                    <p><img src="<?php bloginfo( 'template_url' ); ?>/images/logo_progo.png" style="float:right; margin: 0 0 21px 21px" alt="ProGo Themes" />ProGo Themes are Easy and Quick to Set Up using our Step-by-Step Process.<br /><br /><a href="http://www.progo.com/resources/QuickStartGuide-Ecommerce.pdf" target="_blank">Download the ProGoDotCom Theme Quick Start Guide (PDF)</a></p>
                </div>
            </div>
        	<div id="progo_plugs" class="postbox">
            	<h3 class="hndle"><span>Recommended Plugins</span></h3>
                <div class="inside">
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
                </div>
            </div>
        </div>
    </div>
    <div class="clear"></div>
    </div>
	<?php
			break;
		case 'progo_shipping': ?>
	<div class="wrap">
    <div class="icon32" id="icon-index"><br /></div>
    <?php progo_admin_page_tabs($thispage); ?>
    <div id="dashboard-widgets-wrap">
				<?php
				require_once( WPSC_FILE_PATH . '/wpsc-admin/includes/settings-pages/shipping.php' );
				wpsc_options_shipping(); ?>
    </div>
    <div class="clear"></div>
    </div>
		</form>
        <?php
			break;
		case 'progo_gateway': ?>
	<div class="wrap">
    <div class="icon32" id="icon-ms-admin"><br /></div>
    <?php progo_admin_page_tabs($thispage); ?>
    <div id="dashboard-widgets-wrap">
				<?php
				require_once( WPSC_FILE_PATH . '/wpsc-admin/includes/settings-pages/gateway.php' );
				wpsc_options_gateway(); ?>
    </div>
    <div class="clear"></div>
    </div>
		</form>
        <?php
			break;
		case 'progo_appearance': ?>
	<div class="wrap">
    <div class="icon32" id="icon-themes"><br /></div>
    <?php progo_admin_page_tabs($thispage);
	
	$options = get_option( 'progo_options' );
	echo '<pre style="display:none">'. print_r($options,true) .'</pre>';
	?>
    <form action="options.php" method="post" enctype="multipart/form-data" class="progo-form"><?php settings_fields( 'progo_options' ); ?>
    <div id="dashboard-widgets-wrap">
    <div id="dashboard-widgets" class="metabox-holder">
    	<div class="postbox-container" style="width:75%">
        	<div id="progo_theme" class="postbox">
            	<h3 class="hndle"><span>Theme Customization</span></h3>
                <div class="inside noh3">
				<?php	do_settings_sections( 'progo_theme' ); ?>
                <p class="submit"><input type="submit" name="updateoption" value="Update &raquo;" /></p>
                </div>
            </div>
        	<div id="progo_info" class="postbox">
            	<h3 class="hndle"><span>Site Info</span></h3>
                <div class="inside noh3">
				<?php	do_settings_sections( 'progo_info' ); ?>
                <p class="submit"><input type="submit" name="updateoption" value="Update &raquo;" /></p>
                </div>
            </div>
        </div>
         <div class="postbox-container" style="width:24%">
        	<div id="progo_hometop" class="postbox">
            	<h3 class="hndle"><span>Homepage</span></h3>
                <div class="inside noh3">
				<?php	do_settings_sections( 'progo_hometop' ); ?>
                <p class="submit"><input type="submit" name="updateoption" value="Update &raquo;" /></p>
                <p>The Featured / Promotional area at the top of your Homepage can display multiple Slides of content.<br /><br /><a href="admin.php?page=progo_home_slides" class="button">Manage Homepage Slides Here &raquo;</a></p>
                </div>
            </div>
         	<div id="progo_menus" class="postbox">
            	<h3 class="hndle"><span>Menus</span></h3>
                <div class="inside">
                    <p>Menus control the Links in the header &amp; footer of your site.<br /><br /><a href="nav-menus.php" class="button">Configure Menu Links Here &raquo;</a></p>
                </div>
            </div>
         	<div id="progo_widgets" class="postbox">
            	<h3 class="hndle"><span>Widgets</span></h3>
                <div class="inside">
                    <p>Widgets control the right column on various areas of your site.<br /><br /><a href="widgets.php" class="button">Configure Widgets Here &raquo;</a></p>
                </div>
            </div>
        </div>
    </div>
    <div class="clear"></div>
    </div>
		</form>
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
	$slideproduct = $slideimg = 0;
	$slidetext = '';
	if(is_array($slidedata)) {
		if(isset($slidedata['product'])) $slideproduct = absint($slidedata['product']);
		if(isset($slidedata['text'])) $slidetext = $slidedata['text'];
		if(isset($slidedata['image'])) $slideimg = absint($slidedata['image']);
	}
?><div class="postbox">
<div class="handlediv" title="Click to toggle"><br /></div><h3 class="hndle"><span>Slide <?php echo $num; ?></span></h3>
<div class="inside">
<p><a href="#" onclick="return progo_slideremove(jQuery(this));" style="float:right">Delete This Slide</a>Slide shows :<br /><select class="homeslideshows" name="progo_slides[<?php echo $num; ?>][show]" onchange="progo_slidefor(jQuery(this));"><option value="">- please select -</option>
<?php
$slidetypes = array(
	"product" => "Product",
	"text" => "Text Area"/*,
	"image" => "Image Banner"*/
);
foreach ( $slidetypes as $k => $v ) {
	$s = $sel == $k ? " selected='selected'" : "";
	echo "<option value='$k'$s>$v</option>";
}
?></select></p>
<p class="product" style="<?php if($sel!='product') echo 'display:none'; ?>">Select a Product for this Slide<br />
<select name="progo_slides[<?php echo $num; ?>][product]">
<?php
$prods = get_posts(array('numberposts' => -1, 'post_type' => 'wpsc-product'));
foreach ( $prods as $p ) {
	$s = $slideproduct == $p->ID ? ' selected="selected"' : '';
	echo '<option value="'. $p->ID .'"'. $s .'>'. esc_attr($p->post_title) .'</option>';
}
?>
</select></p>
<p class="text" style="<?php if($sel!='text') echo 'display:none'; ?>">Text to Display<br />
<textarea name="progo_slides[<?php echo $num; ?>][text]" rows="3" style="width: 100%"><?php echo esc_attr($slidetext); ?></textarea></p>
<p class="image" style="<?php if($sel!='image') echo 'display:none'; ?>">Choose an Image to display on this Slide. Images should be 960px width.<br />
<input type="hidden" name="progo_slides[<?php echo $num; ?>][image]" /></p>
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
	
	// ACTION hooks
	add_action( 'admin_print_styles', 'progo_admin_page_styles' );
	add_action( 'admin_print_scripts', 'progo_admin_page_scripts' );
	
	// Appearance settings
	register_setting( 'progo_options', 'progo_options', 'progo_validate_options' );
	
	add_settings_section( 'progo_theme', 'Theme Customization', 'progo_section_text', 'progo_theme' );
	add_settings_field( 'progo_logo', 'Logo', 'progo_field_logo', 'progo_theme', 'progo_theme' );

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
		wp_redirect( get_option( 'siteurl' ) . '/wp-admin/admin.php?page=progo_admin' );
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
			}
			break;
	}
}
endif;
add_action( 'do_meta_boxes', 'progo_metabox_cleanup' );

/********* core ProGoDotCom functions *********/

if ( ! function_exists( 'progo_add_scripts' ) ):
/**
 * hooked to 'wp_print_scripts' by add_action in progo_setup()
 * adds front-end js
 * @since BookIt 1.0
 */
function progo_add_scripts() {
	if ( !is_admin() ) {
		wp_register_script( 'progo', get_bloginfo('template_url') .'/js/progo-frontend.js', array('jquery'), '1.0' );
		wp_enqueue_script( 'progo' );
		do_action('progo_frontend_scripts');
	} else {
		//
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
		wp_redirect( get_option('siteurl') .'/wp-admin/admin.php?page=progo_admin' );
		exit();
	}
}
endif;
if ( ! function_exists( 'progo_reset_logo' ) ):
/**
 * wipe out any custom logo image setting
 * @since ProGoDotCom 1.0
 */
function progo_reset_logo(){
	check_admin_referer( 'progo_reset_logo' );
	
	// reset logo settings
	$options = get_option('progo_options');
	$options['logo'] = '';
	update_option( 'progo_options', $options );
	update_option( 'progo_settings_just_saved', 1 );
	
	wp_redirect( get_option('siteurl') .'/wp-admin/admin.php?page=progo_admin' );
	exit();
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
/**
 * ProGo Site Settings Options defaults
 * @since ProGoDotCom 1.0
 */
function progo_options_defaults() {
	// Define default option settings
	$tmp = get_option( 'progo_options' );
    if ( !is_array( $tmp ) ) {
		$def = array(
			"logo" => "",
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
		$newslide['product'] = isset($slide['product']) ? absint($slide['product']) : 0;
		$newslide['text'] = isset($slide['text']) ? wp_kses($slide['text'], array()) : '';
		$newslide['image'] = isset($slide['image']) ? absint($slide['image']) : 0;
		$newslides[] = $newslide;
		$count++;
	}
	// check for new slide addition ...
	for ( $i = $count; $i < $counto; $i++ ) {
		$newslides[] = array(
			'show' => '',
			'product' => 0,
			'text' => '',
			'image' => 0
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
	
		// upload error?
		$error = '';
	// upload the file - BASED OFF WP USERPHOTO PLUGIN
	if ( isset($_FILES['progo_options']) && @$_FILES['progo_options']['name']['logotemp'] ) {
		if ( $_FILES['progo_options']['error']['logotemp'] ) {
			switch ( $_FILES['progo_options']['error']['logotemp'] ) {
				case UPLOAD_ERR_INI_SIZE:
				case UPLOAD_ERR_FORM_SIZE:
					$error = "The uploaded file exceeds the max upload size.";
					break;
				case UPLOAD_ERR_PARTIAL:
					$error = "The uploaded file was only partially uploaded.";
					break;
				case UPLOAD_ERR_NO_FILE:
					$error = "No file was uploaded.";
					break;
				case UPLOAD_ERR_NO_TMP_DIR:
					$error = "Missing a temporary folder.";
					break;
				case UPLOAD_ERR_CANT_WRITE:
					$error = "Failed to write file to disk.";
					break;
				case UPLOAD_ERR_EXTENSION:
					$error = "File upload stopped by extension.";
					break;
				default:
					$error = "File upload failed due to unknown error.";
			}
		} elseif ( !$_FILES['progo_options']['size']['logotemp'] ) {
			$error = "The file &ldquo;". $_FILES['progo_options']['name']['logotemp'] ."&rdquo; was not uploaded. Did you provide the correct filename?";
		} elseif ( !in_array( $_FILES['progo_options']['type']['logotemp'], array( "image/jpeg", "image/pjpeg", "image/gif", "image/png", "image/x-png" ) ) ) {
			$error = "The uploaded file type &ldquo;". $_FILES['progo_options']['type']['logotemp'] ."&rdquo; is not allowed.";
		}
		$tmppath = $_FILES['progo_options']['tmp_name']['logotemp'];
		
		$imageinfo = null;
		if(!$error){			
			$imageinfo = getimagesize($tmppath);
			if ( !$imageinfo || !$imageinfo[0] || !$imageinfo[1] ) {
				$error = __("Unable to get image dimensions.", 'user-photo');
			} else if( $imageinfo[0] > 598 || $imageinfo[1] > 75 ) {
				/*
				if(userphoto_resize_image($tmppath, null, $userphoto_maximum_dimension, $error)) {
					$imageinfo = getimagesize($tmppath);
				}
				*/
				$filename = $tmppath;
				$newFilename = $filename;
				$jpeg_compression = 86;
				#if(empty($userphoto_jpeg_compression))
				#	$userphoto_jpeg_compression = USERPHOTO_DEFAULT_JPEG_COMPRESSION;
				
				$info = @getimagesize($filename);
				if(!$info || !$info[0] || !$info[1]){
					$error = __("Unable to get image dimensions.", 'user-photo');
				}
				//From WordPress image.php line 22
				else if (
					!function_exists( 'imagegif' ) && $info[2] == IMAGETYPE_GIF
					||
					!function_exists( 'imagejpeg' ) && $info[2] == IMAGETYPE_JPEG
					||
					!function_exists( 'imagepng' ) && $info[2] == IMAGETYPE_PNG
				) {
					$error = __( 'Filetype not supported.', 'user-photo' );
				}
				else {
					// create the initial copy from the original file
					if ( $info[2] == IMAGETYPE_GIF ) {
						$image = imagecreatefromgif( $filename );
					}
					elseif ( $info[2] == IMAGETYPE_JPEG ) {
						$image = imagecreatefromjpeg( $filename );
					}
					elseif ( $info[2] == IMAGETYPE_PNG ) {
						$image = imagecreatefrompng( $filename );
					}
					if(!isset($image)){
						$error = __("Unrecognized image format.", 'user-photo');
						return false;
					}
					if ( function_exists( 'imageantialias' ))
						imageantialias( $image, TRUE );
			
					// make sure logo is within max 598 x 75 dimensions
					
					// figure out the longest side
					if ( ( $info[0] / $info[1] ) > 8 ) { // resize width to fit 
						$image_width = $info[0];
						$image_height = $info[1];
						$image_new_width = 598;
			
						$image_ratio = $image_width / $image_new_width;
						$image_new_height = round( $image_height / $image_ratio );
					} else { // resize height to fit
						$image_width = $info[0];
						$image_height = $info[1];
						$image_new_height = 75;
			
						$image_ratio = $image_height / $image_new_height;
						$image_new_width = round( $image_width / $image_ratio );
					}
			
					$imageresized = imagecreatetruecolor( $image_new_width, $image_new_height);
					@ imagecopyresampled( $imageresized, $image, 0, 0, 0, 0, $image_new_width, $image_new_height, $info[0], $info[1] );
			
					// move the thumbnail to its final destination
					if ( $info[2] == IMAGETYPE_GIF ) {
						if (!imagegif( $imageresized, $newFilename ) ) {
							$error = __( "Logo path invalid" );
						}
					}
					elseif ( $info[2] == IMAGETYPE_JPEG ) {
						if (!imagejpeg( $imageresized, $newFilename, $jpeg_compression ) ) {
							$error = __( "Logo path invalid" );
						}
					}
					elseif ( $info[2] == IMAGETYPE_PNG ) {
						@ imageantialias($imageresized,true);
						@ imagealphablending($imageresized, false);
						@ imagesavealpha($imageresized,true);
						$transparent = imagecolorallocatealpha($imageresized, 255, 255, 255, 0);
						for($x=0;$x<$image_new_width;$x++) {
							for($y=0;$y<$image_new_height;$y++) {
							@ imagesetpixel( $imageresized, $x, $y, $transparent );
							}
						}
						@ imagecopyresampled( $imageresized, $image, 0, 0, 0, 0, $image_new_width, $image_new_height, $info[0], $info[1] );

						if (!imagepng( $imageresized, $newFilename ) ) {
							$error = __( "Logo path invalid" );
						}
					}
				}
				if(empty($error)) {
					$imageinfo = getimagesize($tmppath);
				}
			}
		}
		
		if ( !$error ){
			$upload_dir = wp_upload_dir();
			$dir = trailingslashit( $upload_dir['basedir'] );
			$imagepath = $dir . $_FILES['progo_options']['name']['logotemp'];
			
			if ( !move_uploaded_file( $tmppath, $imagepath ) ) {
				$error = "Unable to place the user photo at: ". $imagepath;
			}
			else {
				chmod($imagepath, 0666);
				
				$input['logo'] = $_FILES['progo_options']['name']['logotemp'];
	
				/*
				if($oldFile && $oldFile != $newFile)
					@unlink($dir . '/' . $oldFile);
				*/
			}
		}
	}
	update_option('progo_settings_just_saved',1);
	
	return $input;
}
endif;

/********* more helper functions *********/
if ( ! function_exists( 'progo_field_logo' ) ):
/**
 * outputs HTML for custom "Logo" on Site Settings page
 * @since ProGoDotCom 1.0
 */
function progo_field_logo() {
	$options = get_option('progo_options');
	if ( $options['logo'] != '' ) {
		$upload_dir = wp_upload_dir();
		$dir = trailingslashit( $upload_dir['baseurl'] );
		$imagepath = $dir . $options['logo'];
		echo '<img src="'. esc_attr( $imagepath ) .'" /> [<a href="'. wp_nonce_url("admin.php?progo_admin_action=reset_logo", 'progo_reset_logo') .'">Delete Logo</a>]<br /><span class="description">Replace Logo</span><br />';
	} ?>
<input type="hidden" id="progo_logo" name="progo_options[logo]" value="<?php echo esc_attr( $options['logo'] ); ?>" />
<input type="file" id="progo_logotemp" name="progo_options[logotemp]" />
<span class="description">Upload your logo here.<br />
Maximum dimensions: 598px Width x 75px Height. Larger images will be automatically scaled down to fit size.<br />
Maximum upload file size: <?php echo ini_get( "upload_max_filesize" ); ?>. Allowable formats: gif/jpg/png. Transparent png's / gif's are recommended.</span>
<?php
#needswork
}
endif;

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
	if ( is_front_page() ) {
		$options = get_option( 'progo_options' );
		if( $options['frontpage'] == 'featured' ) {
			$classes[] = 'wpsc';
		}
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
		if ( $i->post_content == '[productspage]' && !is_front_page() ) {
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
	$wp_admin_bar->add_menu( array( 'id' => 'appearance', 'title' => __('Appearance'), 'href' => admin_url('admin.php?page=progo_appearance') ) );
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