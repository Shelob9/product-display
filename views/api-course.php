<?php

add_filter( 'excerpt_length', function () {
	return 9999;
} );
add_filter( 'excerpt_more', '__return_empty_string' );
$bundle_id   = 9;
$add_to_cart = add_query_arg( 'edd_action', 'add_to_cart', home_url( 'checkout' ) );

$args     = array(
	'post_type'    => 'download',
	'tax_query'    => array(
		array(
			'taxonomy' => 'download_category',
			'field'    => 'slug',
			'terms'    => array( 'rest-api-course' ),
		),
	),
	'orderby'      => 'meta_value_num',
	'order'        => 'ASC',
	'meta_key'     => 'rest_course_order',
	'post__not_in' => array( $bundle_id )
);
$products = new WP_Query( $args );

if ( $products->have_posts() ) :
	$x = 0;
	?>

		<div class="row">

			<?php while ( $products->have_posts() ) : $products->the_post(); ?>
				<div class="col-sm-12 col-md-6 product" id="<?php echo esc_attr( 'product-' . get_the_ID() ); ?>">
					<a href="<?php echo esc_url( get_the_permalink() ); ?>">
						<h2 class="title">
					<span itemprop="name">
						<?php echo esc_html( get_the_title() ); ?>
					</span>
						</h2>
					</a>

					<div class="product-image">
						<a href="<?php the_permalink(); ?>">
							<?php the_post_thumbnail( 'product-image' ); ?>
						</a>
					</div>
					<div class="product-excerpt">

						<?php the_excerpt(); ?>

					</div>
					<div class="product-price">
						<?php echo ljp_add_to_cart_button( get_the_ID() ) ?>
					</div>
					<!--end .product-price-->
				</div>


				<?php
				if ( 1 == $x ) {
					echo '</div><div class="row">';
				}
				$x ++;
			endwhile; ?>

		</div>


	<!--end .product-price-->
<?php endif; ?>
<div style="clear:both;"></div>
