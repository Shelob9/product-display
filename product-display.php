<?php
/*
Plugin Name: Product Display
Version: 0.1.0
Description: Display related modifications for for learn.joshpress.net
 */

/**
 * Don't use the microdata filter on the title for main download, as it busts up our markup
 */
add_action( 'template_redirect', function(){
	$is_bundle = is_singular( 'download' ) && 9 == get_queried_object_id();
	if(  $is_bundle ){
		remove_filter( 'the_title', 'edd_microdata_title', 10, 2 );

	}

	if( $is_bundle || is_front_page() ){
		add_filter( 'post_thumbnail_html', '__return_empty_string' );
	}
});

/**
 * Replace add to cart button
 */
remove_action( 'edd_after_download_content', 'edd_append_purchase_link' );
add_action( 'edd_after_download_content', function(){
	global $post;
	if( is_object( $post ) ) {
		if( 9 != $post->ID ) {
			echo ljp_add_to_cart_button( $post->ID, true );
		}elseif( 9 == $post->ID ){
			//echo ljp_rest_course_bundle();
		}
	}

} );

/**
 * HTML for the course bundle buy button
 *
 * @return string
 */
function ljp_rest_course_bundle(){
	$add_to_cart = add_query_arg( array(
		'edd_action' => 'add_to_cart',
		'download_id' => 9
	), home_url( 'checkout' ) );
	return sprintf( '<button id="rest-bundle-button"><a href="%s" title="Buy The Full Course">Buy All Four Parts & Save: $100</a></button>', $add_to_cart );
}

/**
 * Shortcode for course bundle buy button
 */
add_shortcode( 'rest_course_bundle', 'ljp_rest_course_bundle' );

/**
 * Shortcode for all parts of course display
 */
add_shortcode( 'rest_course', function(){
	return ljp( 'api-course' );
});

/**
 * Generic add to cart button
 *
 * @param null|int $id Optional. ID of download. Defualt is current post.
 * @param bool $extra_wrap Optional. If true, adds .product-price wrapping HTML element. Default is false.
 *
 * @return string|void
 */
function ljp_add_to_cart_button( $id = null, $extra_wrap = false ){
	if( !  $id  ) {
		global $post;
		if( is_object( $post ) ){
			$id = $post->ID;
		}else{
			return;
		}

	}
	$add_to_cart = add_query_arg( 'edd_action', 'add_to_cart', home_url( 'checkout' ) );

	$html =  sprintf( '<button><a href="%s" title="Buy This Part of The Course">%s</a></button>', esc_url( add_query_arg( 'download_id', $id, $add_to_cart ) ), 'Buy Now: $30' );
	if( $extra_wrap ){
		$html = sprintf( '<div class="product-price">%s</div>', $html );
	}

	return $html;

}

/**
 * Load a partial
 *
 * @param $display
 *
 * @return mixed|string
 */
function ljp( $display ) {

	$key = md5(( __FUNCTION__ . $display ) );
	if( ! WP_DEBUG && false == ( $view = get_transient( $key ) ) ) {
		ob_start();
		include( dirname( __FILE__ ) . '/views/'. $display . '.php' );
		$view = ob_get_clean();
		set_transient( $key, $view, DAY_IN_SECONDS );
	}

	return $view;
}

/**
 * Load grid CSS based on bootstrap
 */
add_action( 'wp_enqueue_scripts',  function(){
	wp_enqueue_style( 'josh-bootstrap', plugin_dir_url( __FILE__ ) . 'grid.min.css' );
});

/**
 * Inline styles
 */
add_action( 'wp_head', function() {
	$key = md5( __FUNCTION__ . __FILE__ );
	if( false == ( $styles = get_transient( $key ) ) ) :
		ob_start();
		?>
		<style>


			.home footer.entry-footer, .single-download footer.entry-footer {
				display: none;
			}

			button#rest-bundle-button {
				width: 100%;
				text-align: center;
				color: white;
			}

			#rest-bundle-button a {
				color: white;

			}

			@media screen and (min-width: 61.5625em) {
				.home .entry-content, .single-download .entry-content {
					float: left !important;
					margin-right: -100% !important;
					margin-left: 20% !important;
					width: 60.00000001% !important;
				}
				.single-download .entry-content {
					width: 71.42857144% !important;
				}
			}

			@media screen and (max-width: 61.5625em) {
				.site-header-main {
					background: #fff !important;
					background-image: none !important;
				}

				.site-header-main .menu-main-container li.menu-item a:hover, .menu-main-container li.menu-item a:active {
					color: #fff;
					background: #000;
					padding-left: 2px;
				}

				.site-header-main .menu-main-container li.menu-item a:hover, .menu-main-container li.menu-item {
					padding-left: 2px;
				}
			}

			.home h2.title, .postid-9 h2.title {
				color: #000;
				text-align: center;
			}


			.home header h2.entry-title {
				display: none;
				visibility: hidden;
			}


			.product-price a {
				color: #FFF;
				text-align: center;
			}

			.product-price button {
				text-align: center;
				width: 100%;
				margin-bottom: 24px;
			}
			.site-header {
				padding-bottom: 0;
			}

		</style>
		<?php
		$styles = ob_get_clean();
		set_transient( $key, $styles, DAY_IN_SECONDS );
	endif;
	echo $styles;
});


/**
 * Make bundle the front-page
 */
add_action( 'pre_get_posts', function ( $query ) {
	if ( ! is_admin() && $query->is_home() && $query->is_main_query() ) {
		$query->set( 'post_type','download' );
		$query->set( 'post__in', [9] );

	}
});

