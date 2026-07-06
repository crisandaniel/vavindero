<?php
/**
 * Plugin Name: Vavinde Storefront Template
 * Description: Standard storefront behavior/layout for every vavinde.ro shop subdomain - shop as homepage, guest checkout only. Network activate.
 * Version: 1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Every shop's homepage is its WooCommerce shop archive, not a blog post
 * list - forced via pre_option so it can't drift per-site regardless of
 * what's stored in Reading Settings (applies to every tier; this is a
 * structural template requirement, not a paid-tier feature).
 */
add_filter( 'pre_option_show_on_front', 'vavinde_force_shop_as_homepage' );
function vavinde_force_shop_as_homepage() {
	return 'page';
}

add_filter( 'pre_option_page_on_front', 'vavinde_force_shop_page_as_front_page' );
function vavinde_force_shop_page_as_front_page( $value ) {
	$shop_page_id = (int) get_option( 'woocommerce_shop_page_id' );

	return $shop_page_id ? $shop_page_id : $value;
}

/**
 * Guest checkout only - no buyer accounts. There's no order
 * history/loyalty use case yet to justify the added friction of
 * passwords/email verification.
 */
add_filter( 'pre_option_woocommerce_enable_guest_checkout', 'vavinde_force_guest_checkout_only' );
function vavinde_force_guest_checkout_only() {
	return 'yes';
}

add_filter( 'pre_option_woocommerce_enable_signup_and_login_from_checkout', 'vavinde_force_no_buyer_accounts' );
add_filter( 'pre_option_woocommerce_enable_myaccount_registration', 'vavinde_force_no_buyer_accounts' );
function vavinde_force_no_buyer_accounts() {
	return 'no';
}

/**
 * Every shop gets the same two standard pages ("Despre noi", "Plată și
 * livrare"), created once per site with placeholder content the owner is
 * expected to fill in. The created page's ID is tracked in its own
 * option (same pattern WooCommerce itself uses for woocommerce_shop_page_id),
 * so the header nav below can link to them without guessing by title/slug.
 *
 * "Plată și livrare" starts as a draft, not published - this service
 * targets non-technical owners, so nothing with placeholder-only content
 * should show up live in the nav before they've actually filled it in.
 * "Despre noi" is generic enough to publish immediately; shipping/payment
 * terms are the kind of thing a buyer could act on if shown wrong.
 */
add_action( 'admin_init', 'vavinde_seed_standard_pages' );
function vavinde_seed_standard_pages() {
	vavinde_seed_page( 'vavinde_about_page_id', __( 'Despre noi', 'vavinde' ), __( 'Adaugă aici câteva rânduri despre magazinul tău.', 'vavinde' ), 'publish' );
	vavinde_seed_page( 'vavinde_shipping_page_id', __( 'Plată și livrare', 'vavinde' ), __( 'Descrie aici cum se face plata și livrarea comenzilor.', 'vavinde' ), 'draft' );
}

function vavinde_seed_page( $option_name, $title, $placeholder_content, $post_status ) {
	$page_id = (int) get_option( $option_name );

	if ( $page_id && get_post( $page_id ) ) {
		return;
	}

	$page_id = wp_insert_post(
		array(
			'post_type'    => 'page',
			'post_title'   => $title,
			'post_content' => $placeholder_content,
			'post_status'  => $post_status,
		)
	);

	if ( $page_id && ! is_wp_error( $page_id ) ) {
		update_option( $option_name, $page_id );
	}
}

/**
 * Only returns a URL if the page is actually published - a draft page
 * (e.g. "Plată și livrare" before the owner fills it in) still has a
 * permalink, but shouldn't show up live in the header nav.
 */
function vavinde_get_published_page_url( $option_name ) {
	$page_id = (int) get_option( $option_name );

	if ( ! $page_id || 'publish' !== get_post_status( $page_id ) ) {
		return '';
	}

	return get_permalink( $page_id );
}

/**
 * WordPress creates a generic "Sample Page" on every fresh install - zero
 * value for a shop. Matched by title, not slug (sample-page) - WordPress
 * localizes both the title and the slug based on site language (this
 * network's sites use the Romanian default, "Pagină exemplu", with slug
 * "pagină-exemplu", not "sample-page"). Runs on every admin_init but is a
 * no-op once deleted, same self-limiting pattern as the seeding above.
 */
add_action( 'admin_init', 'vavinde_delete_default_sample_page' );
function vavinde_delete_default_sample_page() {
	foreach ( array( 'Sample Page', 'Pagină exemplu' ) as $sample_page_title ) {
		$sample_pages = get_posts(
			array(
				'post_type'      => 'page',
				'post_status'    => 'any',
				'title'          => $sample_page_title,
				'posts_per_page' => 1,
			)
		);

		if ( $sample_pages ) {
			wp_delete_post( $sample_pages[0]->ID, true );
		}
	}
}

/**
 * The standard header: brand (the site's own Site Title, e.g. "Ovidiu" -
 * not the raw subdomain, so it reads as a real producer name rather than
 * a technical hostname), nav (Despre noi / Magazin / Plată și livrare),
 * cart, plus a floating WhatsApp contact button. Injected via
 * wp_body_open rather than editing theme template files, so it works the
 * same regardless of active theme and can't be removed via Appearance on
 * basic tier. The active theme's own header/footer template parts are
 * hidden via CSS (see vavinde_storefront_styles()) rather than replaced,
 * to avoid depending on FSE template-part internals.
 */
add_action( 'wp_body_open', 'vavinde_render_storefront_header' );
function vavinde_render_storefront_header() {
	if ( is_admin() || ! function_exists( 'wc_get_page_permalink' ) ) {
		return;
	}

	$about_url    = vavinde_get_published_page_url( 'vavinde_about_page_id' );
	$shipping_url = vavinde_get_published_page_url( 'vavinde_shipping_page_id' );
	$shop_url     = wc_get_page_permalink( 'shop' );
	$cart_url     = wc_get_cart_url();
	$cart_count   = ( WC()->cart ) ? WC()->cart->get_cart_contents_count() : 0;

	$whatsapp_settings = get_option( 'woocommerce_whatsapp_order_settings', array() );
	$whatsapp_number   = isset( $whatsapp_settings['whatsapp_number'] ) ? preg_replace( '/\D/', '', $whatsapp_settings['whatsapp_number'] ) : '';
	?>
	<header class="vavinde-header">
		<div class="vavinde-header__row">
			<a class="vavinde-header__brand" href="<?php echo esc_url( home_url( '/' ) ); ?>">
				<?php echo esc_html( get_bloginfo( 'name' ) ); ?>
			</a>
			<button type="button" class="vavinde-header__toggle" aria-expanded="false" aria-controls="vavinde-header-nav">
				<span aria-hidden="true">&#9776;</span>
				<span class="screen-reader-text"><?php esc_html_e( 'Meniu', 'vavinde' ); ?></span>
			</button>
			<nav id="vavinde-header-nav" class="vavinde-header__nav">
				<?php if ( $about_url ) : ?>
					<a href="<?php echo esc_url( $about_url ); ?>"><?php esc_html_e( 'Despre noi', 'vavinde' ); ?></a>
				<?php endif; ?>
				<a href="<?php echo esc_url( $shop_url ); ?>"><?php esc_html_e( 'Magazin', 'vavinde' ); ?></a>
				<?php if ( $shipping_url ) : ?>
					<a href="<?php echo esc_url( $shipping_url ); ?>"><?php esc_html_e( 'Plată și livrare', 'vavinde' ); ?></a>
				<?php endif; ?>
				<a class="vavinde-header__cart" href="<?php echo esc_url( $cart_url ); ?>">
					<?php
					printf(
						/* translators: %d: number of items in the cart. */
						esc_html__( 'Coș (%d)', 'vavinde' ),
						(int) $cart_count
					);
					?>
				</a>
			</nav>
		</div>
	</header>
	<?php if ( $whatsapp_number ) : ?>
		<a class="vavinde-whatsapp-float" href="<?php echo esc_url( 'https://wa.me/' . $whatsapp_number ); ?>" target="_blank" rel="noopener">
			<svg viewBox="0 0 32 32" width="32" height="32" aria-hidden="true" focusable="false">
				<circle cx="16" cy="16" r="16" fill="#25D366"/>
				<path fill="#fff" d="M16 8c-4.4 0-8 3.6-8 8 0 1.4.4 2.8 1.1 4L8 24l4.2-1.1c1.2.7 2.5 1.1 3.8 1.1 4.4 0 8-3.6 8-8s-3.6-8-8-8zm4.7 11.4c-.2.6-1.1 1.1-1.5 1.1-.4 0-.9.1-1.5-.1-.3-.1-.8-.3-1.3-.5-2.3-1-3.8-3.3-3.9-3.5-.1-.2-.9-1.2-.9-2.3s.6-1.6.8-1.9c.2-.2.5-.3.6-.3h.5c.2 0 .4 0 .5.4.2.4.6 1.5.7 1.6.1.1.1.3 0 .4-.1.1-.1.2-.2.4-.1.1-.2.3-.3.4-.1.1-.2.3-.1.5.2.3.7 1.1 1.5 1.8 1 .9 1.8 1.2 2.1 1.3.2.1.4.1.5-.1.2-.2.6-.7.8-.9.2-.2.4-.2.6-.1.2.1 1.4.7 1.6.8.2.1.4.2.4.3.1.2.1.6-.1 1.1z"/>
			</svg>
			<span class="screen-reader-text"><?php esc_html_e( 'Scrie-ne pe WhatsApp', 'vavinde' ); ?></span>
		</a>
	<?php endif; ?>
	<?php
}

/**
 * Any published page beyond the ones already linked explicitly (Despre
 * noi, Plată și livrare) or used internally by WooCommerce (Shop, Cart,
 * Checkout, My Account) - e.g. the Refund and Returns Policy WooCommerce
 * creates as a draft, once the owner fills it in and publishes it, or any
 * page a pro-tier owner creates themselves. Surfaced automatically so
 * there's always somewhere for it to link from, without us having to
 * special-case every possible extra page by name.
 */
function vavinde_get_extra_footer_pages() {
	$excluded_ids = array_filter(
		array(
			(int) get_option( 'vavinde_about_page_id' ),
			(int) get_option( 'vavinde_shipping_page_id' ),
			(int) wc_get_page_id( 'shop' ),
			(int) wc_get_page_id( 'cart' ),
			(int) wc_get_page_id( 'checkout' ),
			(int) wc_get_page_id( 'myaccount' ),
		)
	);

	return array_filter(
		get_pages( array( 'post_status' => 'publish' ) ),
		function ( $page ) use ( $excluded_ids ) {
			return ! in_array( $page->ID, $excluded_ids, true );
		}
	);
}

/**
 * Fixed, inline icon markup per social network - simple monochrome glyphs
 * (currentColor), styled/colored via CSS. Kept as one function rather
 * than inline in the footer loop so the (fairly long) SVG paths don't
 * clutter the template markup.
 */
function vavinde_social_icon_svg( $network ) {
	$icons = array(
		'facebook'  => '<svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true" focusable="false"><path fill="currentColor" d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.891h-2.33v6.987C18.343 21.128 22 16.991 22 12z"/></svg>',
		'instagram' => '<svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true" focusable="false"><path fill="currentColor" d="M12 2c-2.716 0-3.056.012-4.123.06-1.064.049-1.79.218-2.428.465a4.902 4.902 0 0 0-1.772 1.153A4.902 4.902 0 0 0 2.525 5.45c-.247.637-.416 1.363-.465 2.428C2.012 8.944 2 9.284 2 12s.012 3.056.06 4.123c.049 1.064.218 1.79.465 2.428a4.902 4.902 0 0 0 1.153 1.772 4.902 4.902 0 0 0 1.772 1.153c.637.247 1.363.416 2.428.465C8.944 21.988 9.284 22 12 22s3.056-.012 4.123-.06c1.064-.049 1.79-.218 2.428-.465a4.902 4.902 0 0 0 1.772-1.153 4.902 4.902 0 0 0 1.153-1.772c.247-.637.416-1.363.465-2.428.048-1.067.06-1.407.06-4.123s-.012-3.056-.06-4.123c-.049-1.064-.218-1.79-.465-2.428a4.902 4.902 0 0 0-1.153-1.772A4.902 4.902 0 0 0 18.55 2.525c-.637-.247-1.363-.416-2.428-.465C15.056 2.012 14.716 2 12 2zm0 1.802c2.67 0 2.987.01 4.042.059.976.045 1.505.207 1.858.344.467.182.8.399 1.15.748.35.35.566.683.748 1.15.137.353.3.882.344 1.858.048 1.055.058 1.372.058 4.042 0 2.67-.01 2.987-.058 4.042-.045.976-.207 1.505-.344 1.858a3.09 3.09 0 0 1-.748 1.15c-.35.35-.683.566-1.15.748-.353.137-.882.3-1.858.344-1.054.048-1.371.058-4.042.058-2.67 0-2.987-.01-4.042-.058-.976-.045-1.505-.207-1.858-.344a3.09 3.09 0 0 1-1.15-.748 3.09 3.09 0 0 1-.748-1.15c-.137-.353-.3-.882-.344-1.858-.048-1.055-.058-1.372-.058-4.042 0-2.67.01-2.987.058-4.042.045-.976.207-1.505.344-1.858.182-.467.399-.8.748-1.15.35-.35.683-.566 1.15-.748.353-.137.882-.3 1.858-.344 1.055-.048 1.372-.059 4.042-.059zm0 3.063a5.135 5.135 0 1 0 0 10.27 5.135 5.135 0 0 0 0-10.27zm0 8.468a3.333 3.333 0 1 1 0-6.666 3.333 3.333 0 0 1 0 6.666zm6.538-8.671a1.2 1.2 0 1 1-2.4 0 1.2 1.2 0 0 1 2.4 0z"/></svg>',
		'tiktok'    => '<svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true" focusable="false"><path fill="currentColor" d="M16.6 5.82c-1.02-.87-1.62-2.14-1.62-3.55h-3.15v13.4c0 1.5-1.22 2.72-2.72 2.72a2.72 2.72 0 0 1 0-5.44c.28 0 .55.04.8.12v-3.19a5.87 5.87 0 0 0-.8-.06 5.87 5.87 0 1 0 5.87 5.87V9.14a8.87 8.87 0 0 0 5.17 1.65V7.65a5.57 5.57 0 0 1-3.55-1.83z"/></svg>',
		'x'         => '<svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true" focusable="false"><path fill="currentColor" d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>',
	);

	return isset( $icons[ $network ] ) ? $icons[ $network ] : '';
}

/**
 * The standard footer: extra page links, social links (if the owner
 * filled them in via "Setările magazinului meu"), and copyright. No link
 * to the main site's disclaimer page yet - that page doesn't exist until
 * the vavinde.ro landing page subproject is built.
 */
add_action( 'wp_footer', 'vavinde_render_storefront_footer' );
function vavinde_render_storefront_footer() {
	if ( is_admin() || ! function_exists( 'wc_get_page_permalink' ) ) {
		return;
	}

	// Duplicated from vavinde-site-tiers' VAVINDE_SOCIAL_NETWORKS to avoid a load-order dependency between the two plugins.
	$social_networks = array(
		'facebook'  => 'Facebook',
		'instagram' => 'Instagram',
		'tiktok'    => 'TikTok',
		'x'         => 'X (Twitter)',
	);

	$social_links = array();
	foreach ( $social_networks as $network => $label ) {
		$url = get_option( 'vavinde_social_' . $network, '' );
		if ( $url ) {
			$social_links[ $network ] = array(
				'label' => $label,
				'url'   => $url,
			);
		}
	}

	$extra_pages = vavinde_get_extra_footer_pages();
	?>
	<footer class="vavinde-footer">
		<div class="vavinde-footer__row">
			<?php if ( $extra_pages ) : ?>
				<p class="vavinde-footer__pages">
					<?php foreach ( $extra_pages as $page ) : ?>
						<a href="<?php echo esc_url( get_permalink( $page ) ); ?>"><?php echo esc_html( get_the_title( $page ) ); ?></a>
					<?php endforeach; ?>
				</p>
			<?php endif; ?>
			<?php if ( $social_links ) : ?>
				<p class="vavinde-footer__social">
					<?php esc_html_e( 'Ne găsești și pe:', 'vavinde' ); ?>
					<?php foreach ( $social_links as $network => $social_link ) : ?>
						<a href="<?php echo esc_url( $social_link['url'] ); ?>" target="_blank" rel="noopener" aria-label="<?php echo esc_attr( $social_link['label'] ); ?>">
							<?php echo vavinde_social_icon_svg( $network ); // phpcs:ignore -- fixed inline SVG markup, not user input. ?>
						</a>
					<?php endforeach; ?>
				</p>
			<?php endif; ?>
			<p class="vavinde-footer__copyright">
				&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?>
			</p>
		</div>
	</footer>
	<?php
}

/**
 * Hides the active theme's own header/footer template parts (rendered as
 * <header class="wp-block-template-part"> / <footer ...> by every FSE
 * block theme, including Twenty Twenty-Five) rather than replacing them,
 * so this doesn't depend on editing theme files or FSE template-part
 * internals. Also styles our injected header/footer and the mobile nav
 * toggle - collapsed by default below 700px, since customers arrive
 * overwhelmingly on mobile.
 */
add_action( 'wp_head', 'vavinde_storefront_styles' );
function vavinde_storefront_styles() {
	if ( is_admin() || ! function_exists( 'wc_get_page_permalink' ) ) {
		return;
	}
	?>
	<style>
		header.wp-block-template-part,
		footer.wp-block-template-part {
			display: none;
		}
		.vavinde-header__row {
			display: flex;
			align-items: center;
			justify-content: space-between;
			flex-wrap: wrap;
			gap: 1rem;
			padding: 1rem 1.5rem;
		}
		.vavinde-header__brand {
			font-weight: 600;
			text-decoration: none;
		}
		.vavinde-header__toggle {
			display: none;
			background: none;
			border: 1px solid currentColor;
			border-radius: 4px;
			font-size: 1.25rem;
			line-height: 1;
			padding: 0.25rem 0.6rem;
			cursor: pointer;
		}
		.vavinde-header__nav {
			display: flex;
			align-items: center;
			flex-wrap: wrap;
			gap: 1.25rem;
		}
		.vavinde-whatsapp-float {
			position: fixed;
			right: 1.25rem;
			bottom: 1.25rem;
			z-index: 999;
			display: inline-flex;
			box-shadow: 0 2px 8px rgba(0, 0, 0, 0.25);
			border-radius: 50%;
		}
		.vavinde-footer__row {
			text-align: center;
			padding: 1.5rem;
		}
		.vavinde-footer__pages a {
			margin: 0 0.5rem;
		}
		.vavinde-footer__social a {
			display: inline-flex;
			margin: 0 0.4rem;
			color: currentColor;
			vertical-align: middle;
		}
		@media (max-width: 700px) {
			.vavinde-header__toggle {
				display: inline-flex;
			}
			.vavinde-header__nav {
				display: none;
				flex-direction: column;
				align-items: flex-start;
				width: 100%;
			}
			.vavinde-header__nav.is-open {
				display: flex;
			}
		}
	</style>
	<?php
}

/**
 * Toggles .is-open on the nav when the mobile menu button is tapped.
 * Vanilla JS, no jQuery dependency.
 */
add_action( 'wp_footer', 'vavinde_storefront_mobile_menu_script' );
function vavinde_storefront_mobile_menu_script() {
	if ( is_admin() || ! function_exists( 'wc_get_page_permalink' ) ) {
		return;
	}
	?>
	<script>
	(function () {
		var toggle = document.querySelector( '.vavinde-header__toggle' );
		var nav = document.getElementById( 'vavinde-header-nav' );
		if ( ! toggle || ! nav ) {
			return;
		}
		toggle.addEventListener( 'click', function () {
			var isOpen = nav.classList.toggle( 'is-open' );
			toggle.setAttribute( 'aria-expanded', isOpen ? 'true' : 'false' );
		} );
	})();
	</script>
	<?php
}
