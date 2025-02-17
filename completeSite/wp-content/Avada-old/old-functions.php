<?php

/**
 * Autoloader
 *
 * Automatically load files when their classes are required.
 */
spl_autoload_register( 'avada_register_classes' );
function avada_register_classes( $class_name ) {
	if ( class_exists( $class_name ) ) {
		return;
	}
	if ( 'TGM_Plugin_Activation' == $class_name ) {
		return;
	}
	$class_path = get_template_directory() . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'class-' . strtolower( str_replace( '_', '-', $class_name ) ) . '.php';
	if ( file_exists( $class_path ) ) {
		include $class_path;
	}
}

/**
 * Must-use Plugins
 */
include_once get_template_directory() . '/framework/plugins/multiple_sidebars.php';
require_once get_template_directory() . '/framework/plugins/post-link-plus.php';
require_once get_template_directory() . '/framework/plugins/multiple-featured-images/multiple-featured-images.php';

/*
 * Include the Options Framework
 */
require_once( get_template_directory() . '/framework/admin/index.php' );

/**
 * Instantiate the Avada class
 * Make sure the class is properly set-up.
 * The Avada class is a singular object so we can directly access the one true Avada object using this function.
 */
function Avada() {

	// Instantiate the class
	$avada = Avada::get_instance();
	// Properly add the settings
	$avada->settings = new Avada_Settings();
	$avada->mfi      = new Avada_Multiple_Featured_Images();

	return $avada;

}
$avada = Avada();

global $social_icons;
$social_icons = $avada->social_icons;

/*
 * Include the TGM configuration
 */
require_once( get_template_directory() . '/includes/class-tgm-plugin-activation.php' );
require_once( get_template_directory() . '/includes/avada-tgm.php' );


/**
 * The main Avada class
 */
require_once( get_template_directory() . '/includes/class-avada.php' );

/*
 * Include deprecated functions
 */
require_once( get_template_directory() . '/includes/deprecated.php' );

/*
 * Include compatibility tweaks
 */
require_once( get_template_directory() . '/includes/avada-compatibility.php' );

/**
 * Metaboxes
 */
include_once get_template_directory() . '/framework/metaboxes/metaboxes.php' ;

/**
 * Initialize the mega menu framework
 */
require_once( get_template_directory() . '/framework/plugins/megamenu/mega-menu-framework.php' );

/**
 * Load the Fusion Framework main object to have all functions of it available for later usage
 */
require_once( 'framework/fusion-framework.php' );

/**
 * Custom Functions
 */
get_template_part( 'framework/custom_functions' );
require_once( 'includes/avada-functions.php' );

/**
 * WPML Config
 */
if ( defined( 'ICL_SITEPRESS_VERSION' ) ) {
	include_once get_template_directory() . '/framework/plugins/wpml.php';
}

/**
 * Include the importer
 */
include get_template_directory() . '/framework/plugins/importer/importer.php';

/**
 * Woo Config
 */
if ( class_exists( 'WooCommerce' ) ) {
	include_once( get_template_directory() . '/includes/woo-config.php' );
}

/**
 * The dynamic CSS
 */
require_once get_template_directory() . '/includes/dynamic_css.php';

$image = new Fusion_Image_Resizer();

//define('WOOCOMMERCE_USE_CSS', false);

/**
 * Avada Header Template
 */

get_template_part( 'templates/header' );

// Content Width
if ( ! isset( $content_width ) ) {
	$content_width = '669';
}

/**
 * Font-Awesome icon handler.
 * Adds compatibility with order versions of FA icon names.
 */
function avada_font_awesome_name_handler( $icon ) {

	$old_icons = Avada_Data::old_icons();

	if ( 'icon-' == substr( $icon, 0, 5 ) || 'fa=' != substr( $icon, 0, 3 ) ) {

		// Replace old prefix with new one
		$icon = str_replace( 'icon-', 'fa-', $icon );

		if ( array_key_exists( str_replace( 'fa-', '', $icon ), $old_icons ) ) {
			$fa_icon = 'fa-' . $old_icons[str_replace( 'fa-', '', $icon )];
		} else {
			$fa_icon = ( 'fa-' != substr( $icon, 0, 3 ) ) ? 'fa-' . $icon : $icon;
		}

	} else {

		$fa_icon = ( 'fa-' != substr( $icon, 0, 3 ) ) ? 'fa-' . $icon : $icon;

	}

	return $fa_icon;

}




add_filter( 'get_archives_link', 'avada_cat_count_span' );
add_filter( 'wp_list_categories', 'avada_cat_count_span' );
function avada_cat_count_span( $links ) {
	$get_count = preg_match_all( '#\((.*?)\)#', $links, $matches );

	if ( $matches ) {
		$i = 0;
		foreach( $matches[0] as $val ) {
			$links = str_replace( '</a> ' . $val, ' ' . $val . '</a>', $links );
			$links = str_replace( '</a>&nbsp;' . $val, ' ' . $val . '</a>', $links );
			$i++;
		}
	}

	return $links;
}

remove_action('wp_head', 'adjacent_posts_rel_link_wp_head');

if ( ! is_admin() ) {
	add_filter( 'pre_get_posts', 'avada_SearchFilter' );
	function avada_SearchFilter( $query ) {
		if ( is_search() && $query->is_search ) {
			if ( isset( $_GET ) && 1 < count( $_GET ) ) {
				return $query;
			}

			if ( 'Only Posts' == Avada()->settings->get( 'search_content' ) ) {
				$query->set('post_type', 'post');
			} elseif ( 'Only Pages' == Avada()->settings->get( 'search_content' ) ) {
				$query->set('post_type', 'page');

			}
		}
		return $query;
	}
}

function is_events_archive() {
	if ( class_exists( 'Tribe__Events__Main' ) ) {
		return ( tribe_is_month() || tribe_is_day() || tribe_is_past() || tribe_is_upcoming() || ( class_exists( 'Tribe__Events__Pro__Main' ) && ( tribe_is_week() || tribe_is_photo() || tribe_is_map() ) ) ) ? true : false;
	} else {
		return false;
	}
}

/**
 * Adding the Open Graph in the Language Attributes
 */
function avada_add_opengraph_doctype( $output ) {
	return Avada()->head->add_opengraph_doctype( $output );
}

function fusion_insert_og_meta() {
	Avada()->head->insert_og_meta();
}

if ( ! Avada()->settings->get( 'status_opengraph' ) ) {
	add_filter( 'language_attributes', 'avada_add_opengraph_doctype' );
	add_action( 'wp_head', 'fusion_insert_og_meta', 5 );
}

function modify_contact_methods( $profile_fields ) {
	// Add new fields
	$profile_fields['author_facebook'] = 'Facebook ';
	$profile_fields['author_twitter']  = 'Twitter';
	$profile_fields['author_linkedin'] = 'LinkedIn';
	$profile_fields['author_dribble']  = 'Dribble';
	$profile_fields['author_gplus']    = 'Google+';
	$profile_fields['author_custom']   = 'Custom Message';

	return $profile_fields;
}
add_filter( 'user_contactmethods', 'modify_contact_methods' );

/* Change admin css */
function avada_custom_admin_styles() {
	echo '<style type="text/css">.widget input { border-color: #DFDFDF !important; }</style>';
}
add_action( 'admin_head', 'avada_custom_admin_styles' );

function avada_admin_notice() {
	$url = admin_url( 'themes.php?page=optionsframework#of-option-advanced' );
	$page = '';
	if ( array_key_exists( 'page', $_GET ) ) {
		$page = $_GET['page'];
	}

	if ( array_key_exists( 'imported', $_GET ) && isset( $_GET['imported'] ) && 'success' == $_GET['imported'] ) {
		echo '<div id="setting-error-settings_updated" class="updated settings-error"><p>' . __( 'Sucessfully imported demo data!', 'Avada' ) . '</p></div>';
	}
}
add_action( 'admin_notices', 'avada_admin_notice' );

function avada_nag_ignore() {
	global $current_user;
	$user_id = $current_user->ID;

	/* If user clicks to ignore the notice, add that to their user meta */
	if ( isset( $_GET['fusion_richedit_nag_ignore'] ) && '0' == $_GET['fusion_richedit_nag_ignore'] ) {
		add_user_meta( $user_id, 'fusion_richedit_nag_ignore', 'true', true );

		//$referer = esc_url($_SERVER["HTTP_REFERER"]);
		//wp_redirect($referer);
	}

	/* If user clicks to ignore the notice, add that to their user meta */
	if ( isset( $_GET['avada_uber_nag_ignore'] ) && '0' == $_GET['avada_uber_nag_ignore'] ) {
		update_option( 'avada_ubermenu_notice', true );
		update_option( 'avada_ubermenu_notice_hidden', true );
		$referer = esc_url( $_SERVER["HTTP_REFERER"] );
		wp_redirect( $referer );
	}
}
add_action( 'admin_init', 'avada_nag_ignore' );

if ( function_exists( 'rev_slider_shortcode' ) ) {
	add_action( 'admin_init', 'avada_disable_revslider_notice' );
	add_action( 'admin_init', 'avada_revslider_styles' );
}

/* Disable revslider notice */
function avada_disable_revslider_notice() {
	update_option( 'revslider-valid-notice', 'false' );
}

/* Add revslider styles */
function avada_revslider_styles() {
	global $wpdb, $revSliderVersion;

	$plugin_version = $revSliderVersion;

	$table_name = $wpdb->prefix . 'revslider_css';
	if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) == $table_name && function_exists( 'rev_slider_shortcode' ) && $plugin_version != get_option('avada_revslider_version' ) ) {

		$old_styles = array( '.avada_huge_white_text', '.avada_huge_black_text', '.avada_big_black_text', '.avada_big_white_text', '.avada_big_black_text_center', '.avada_med_green_text', '.avada_small_gray_text', '.avada_small_white_text', '.avada_block_black', '.avada_block_green', '.avada_block_white', '.avada_block_white_trans' );

		foreach( $old_styles as $handle ) {
			$wpdb->delete( $table_name, array( 'handle' => '.tp-caption' . $handle ) );
		}

		$styles = array(
			'.tp-caption.avada_huge_white_text'       => '{"position":"absolute","color":"#ffffff","font-size":"130px","line-height":"45px","font-family":"museoslab500regular"}',
			'.tp-caption.avada_huge_black_text'       => '{"position":"absolute","color":"#000000","font-size":"130px","line-height":"45px","font-family":"museoslab500regular"}',
			'.tp-caption.avada_big_black_text'        => '{"position":"absolute","color":"#333333","font-size":"42px","line-height":"45px","font-family":"museoslab500regular"}',
			'.tp-caption.avada_big_white_text'        => '{"position":"absolute","color":"#fff","font-size":"42px","line-height":"45px","font-family":"museoslab500regular"}',
			'.tp-caption.avada_big_black_text_center' => '{"position":"absolute","color":"#333333","font-size":"38px","line-height":"45px","font-family":"museoslab500regular","text-align":"center"}',
			'.tp-caption.avada_med_green_text'        => '{"position":"absolute","color":"#A0CE4E","font-size":"24px","line-height":"24px","font-family":"PTSansRegular, Arial, Helvetica, sans-serif"}',
			'.tp-caption.avada_small_gray_text'       => '{"position":"absolute","color":"#747474","font-size":"13px","line-height":"20px","font-family":"PTSansRegular, Arial, Helvetica, sans-serif"}',
			'.tp-caption.avada_small_white_text'      => '{"position":"absolute","color":"#fff","font-size":"13px","line-height":"20px","font-family":"PTSansRegular, Arial, Helvetica, sans-serif","text-shadow":"0px 2px 5px rgba(0, 0, 0, 0.5)","font-weight":"700"}',
			'.tp-caption.avada_block_black'           => '{"position":"absolute","color":"#A0CE4E","text-shadow":"none","font-size":"22px","line-height":"34px","padding":["1px", "10px", "0px", "10px"],"margin":"0px","border-width":"0px","border-style":"none","background-color":"#000","font-family":"PTSansRegular, Arial, Helvetica, sans-serif"}',
			'.tp-caption.avada_block_green'           => '{"position":"absolute","color":"#000","text-shadow":"none","font-size":"22px","line-height":"34px","padding":["1px", "10px", "0px", "10px"],"margin":"0px","border-width":"0px","border-style":"none","background-color":"#A0CE4E","font-family":"PTSansRegular, Arial, Helvetica, sans-serif"}',
			'.tp-caption.avada_block_white'           => '{"position":"absolute","color":"#fff","text-shadow":"none","font-size":"22px","line-height":"34px","padding":["1px", "10px", "0px", "10px"],"margin":"0px","border-width":"0px","border-style":"none","background-color":"#000","font-family":"PTSansRegular, Arial, Helvetica, sans-serif"}',
			'.tp-caption.avada_block_white_trans'     => '{"position":"absolute","color":"#fff","text-shadow":"none","font-size":"22px","line-height":"34px","padding":["1px", "10px", "0px", "10px"],"margin":"0px","border-width":"0px","border-style":"none","background-color":"rgba(0, 0, 0, 0.6)","font-family":"PTSansRegular, Arial, Helvetica, sans-serif"}',
		);

		foreach( $styles as $handle => $params ) {
			$test = $wpdb->get_var( $wpdb->prepare( 'SELECT handle FROM ' . $table_name . ' WHERE handle = %s', $handle ) );

			if ( $test != $handle ) {
				$wpdb->replace(
					$table_name,
					array(
						'handle' => $handle,
						'params' => $params,
						'settings' => '{"hover":"false","type":"text","version":"custom","translated":"5"}',
					),
					array(
						'%s',
						'%s',
						'%s',
					)
				);
			}
		}
		update_option( 'avada_revslider_version', $plugin_version );
	}
}

//////////////////////////////////////////////////////////////////
// Woo Products Shortcode Recode
//////////////////////////////////////////////////////////////////
function avada_woo_product( $atts, $content = null ) {
	global $woocommerce_loop;

	if ( empty( $atts ) ) {
		return;
	}

	$args = array(
		'post_type'       => 'product',
		'posts_per_page'  => 1,
		'no_found_rows'   => 1,
		'post_status'     => 'publish',
		'meta_query'      => array(
			array(
				'key'     => '_visibility',
				'value'   => array('catalog', 'visible'),
				'compare' => 'IN'
			)
		),
		'columns'         => 1
	);

	if ( isset( $atts['sku'] ) ) {
		$args['meta_query'][] = array(
			'key'     => '_sku',
			'value'   => $atts['sku'],
			'compare' => '='
		);
	}

	if ( isset( $atts['id'] ) ) {
		$args['p'] = $atts['id'];
	}

	ob_start();

	if ( isset( $columns ) ) {
		$woocommerce_loop['columns'] = $columns;
	}

	$products = new WP_Query( $args );

	if ( $products->have_posts() ) : ?>

		<?php woocommerce_product_loop_start(); ?>

			<?php while ( $products->have_posts() ) : $products->the_post(); ?>

				<?php woocommerce_get_template_part( 'content', 'product' ); ?>

			<?php endwhile; // end of the loop. ?>

		<?php woocommerce_product_loop_end(); ?>

	<?php endif;

	wp_reset_postdata();

	return '<div class="woocommerce">' . ob_get_clean() . '</div>';
}

add_action( 'wp_loaded', 'remove_product_shortcode' );
function remove_product_shortcode() {
	if ( class_exists( 'WooCommerce' ) ) {
		// First remove the shortcode
		remove_shortcode( 'product' );
		// Then recode it
		add_shortcode( 'product', 'avada_woo_product' );
	}
}


// Support email login on my account dropdown
if( isset( $_POST['fusion_woo_login_box'] ) && $_POST['fusion_woo_login_box'] == 'true' ) {
	add_filter( 'authenticate', 'avada_email_login_auth', 10, 3 );
}
function avada_email_login_auth( $user, $username, $password ) {
	if ( is_a( $user, 'WP_User' ) ) {
		return $user;
	}

	if ( !empty( $username ) ) {
		$username = str_replace( '&', '&amp;', stripslashes( $username ) );
		$user = get_user_by( 'email', $username );
		if ( isset( $user, $user->user_login, $user->user_status ) && 0 == (int) $user->user_status ) {
			$username = $user->user_login;
		}
	}

	return wp_authenticate_username_password( null, $username, $password );
}

// No redirect on woo my account dropdown login when it fails
if( isset( $_POST['fusion_woo_login_box'] ) && $_POST['fusion_woo_login_box'] == 'true' ) {
	add_action( 'init', 'avada_load_login_redirect_support' );
}

function avada_load_login_redirect_support() {
	if ( class_exists( 'WooCommerce' ) ) {

		// When on the my account page, do nothing
		if ( ! empty( $_POST['login'] ) && ! empty( $_POST['_wpnonce'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'woocommerce-login' ) ) {
			return;
		}

		add_action( 'login_redirect', 'avada_login_fail', 10, 3 );
	}
}

// Avada Login Fail Test
function avada_login_fail( $url = '', $raw_url = '', $user = '' ) {
	if ( ! is_account_page() ) {

		if ( isset( $_SERVER ) && isset( $_SERVER['HTTP_REFERER'] ) && $_SERVER['HTTP_REFERER'] ) {
			$referer_array = parse_url( $_SERVER['HTTP_REFERER'] );
			$referer = '//' . $referer_array['host'] . $referer_array['path'];

			// If there's a valid referrer, and it's not the default log-in screen
			if ( ! empty( $referer ) && ! strstr( $referer, 'wp-login' ) && ! strstr( $referer, 'wp-admin' ) ) {
				if ( is_wp_error( $user ) ) {
					// Let's append some information (login=failed) to the URL for the theme to use
					wp_redirect( add_query_arg( array( 'login' => 'failed' ), $referer ) );
				} else {
					wp_redirect( $referer );
				}
				exit;
			} else {
				return $url;
			}
		} else {
			return $url;
		}
	}
}

/**
 * Show a shop page description on product archives
 */
function woocommerce_product_archive_description() {
	if ( is_post_type_archive( 'product' ) && 0 == get_query_var( 'paged' ) ) {
		$shop_page   = get_post( woocommerce_get_page_id( 'shop' ) );
		if ( $shop_page ) {
			$description = apply_filters( 'the_content', $shop_page->post_content );
			if ( $description ) {
				echo '<div class="post-content">' . $description . '</div>';
			}
		}
	}
}

/**
 * Layerslider API
 */
function avada_layerslider_ready() {
	if ( class_exists('LS_Sources') ) {
		LS_Sources::addSkins( get_template_directory().'/includes/ls-skins' );
	}
	if ( defined( 'LS_PLUGIN_BASE' ) ) {
		remove_action( 'after_plugin_row_' . LS_PLUGIN_BASE, 'layerslider_plugins_purchase_notice', 10, 3 );
	}
}
add_action( 'layerslider_ready', 'avada_layerslider_ready' );

/**
 * Custom Excerpt function for Sermon Manager
 */
function avada_get_sermon_content( $archive = false ) {
	global $post;

	$sermon_content = '';
	ob_start();
	?>

	<p>
		<?php
			_e( 'Date: ', 'Avada' );
			wpfc_sermon_date( get_option( 'date_format' ), '<span class="sermon_date">', '</span> ' ); echo the_terms( $post->ID, 'wpfc_service_type',  ' <span class="service_type">(', ' ', ')</span>');
	?></p><p><?php
			wpfc_sermon_meta( 'bible_passage', '<span class="bible_passage">' . __( 'Bible Text: ', 'Avada' ), '</span> | ' );
			echo the_terms( $post->ID, 'wpfc_preacher',  '<span class="preacher_name">', ', ', '</span>');
			echo the_terms( $post->ID, 'wpfc_sermon_series', '<p><span class="sermon_series">'  .__( 'Series: ', 'Aavada' ), ' ', '</span></p>' );
		?>
	</p>

	<?php
	if ( $archive ) {
		$sermonoptions = get_option( 'wpfc_options' );
		if ( isset( $sermonoptions['archive_player'] ) == '1' ) { ?>
			<div class="wpfc_sermon cf">
				<?php wpfc_sermon_files(); ?>
			</div>
		<?php }
	} ?>

	<?php if ( ! $archive ) { ?>

	<?php wpfc_sermon_files(); ?>

	<?php wpfc_sermon_description(); ?>

	<?php wpfc_sermon_attachments(); ?>

	<?php echo the_terms( $post->ID, 'wpfc_sermon_topics', '<p class="sermon_topics">'.__( 'Topics: ', 'sermon-manager'), ',', '', '</p>' ); ?>

	<?php }
	$sermon_content = ob_get_clean();

	if ( $archive ) {
		$description = '';
		ob_start();
		wpfc_sermon_description();
		$description .= ob_get_clean();

		$excerpt_length = fusion_get_theme_option( 'excerpt_length_blog' );

		$sermon_content .= Avada()->blog->get_content_stripped_and_excerpted( $excerpt_length, $description );
	}

	return $sermon_content;
}

// WIP, please ignore below:

add_theme_support( 'fusion-builder-demos' );

if ( get_option( 'avada_imported_demo' ) == 'true' ) {
	flush_rewrite_rules();

	update_option( 'avada_imported_demo', 'false' );
}

// Omit closing PHP tag to avoid "Headers already sent" issues.
