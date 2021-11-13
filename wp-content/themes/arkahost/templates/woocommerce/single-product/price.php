<?php
/**
 * Single Product Price, including microdata for SEO
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     1.6.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $product;

?>

	<div itemprop="offers" itemscope itemtype="http://schema.org/Offer" class="price_wrapper">

		<p class="price price size-lg"><?php print( $product->get_price_html() ); ?></p>

		<meta itemprop="price" content="<?php print( $product->get_price() ); ?>" />
		<meta itemprop="priceCurrency" content="<?php echo get_woocommerce_currency(); ?>" />
		<link itemprop="availability" href="http://schema.org/<?php print( $product->is_in_stock() ? 'InStock' : 'OutOfStock' ); ?>" />

	</div>