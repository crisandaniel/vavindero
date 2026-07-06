<?php
/**
 * Plugin Name: Vavinde Site Tiers
 * Description: Restricts subdomain admin capabilities based on a per-site tier (basic/pro). Network activate; super admins are always exempt.
 * Version: 1.0.0
 */

defined( 'ABSPATH' ) || exit;

function vavinde_site_tier() {
	return get_option( 'vavinde_site_tier', 'basic' );
}

function vavinde_is_basic_tier() {
	return ! is_super_admin() && 'basic' === vavinde_site_tier();
}

/**
 * 'post' and 'page' don't have their own "create_posts" capability by
 * default - WordPress core points $post_type_object->cap->create_posts at
 * the same capability as edit_posts/edit_pages (see
 * get_post_type_capabilities() in wp-includes/post.php), so filtering
 * edit_posts/edit_pages to block creation would also block editing
 * existing content. Give post/page their own distinct capability instead,
 * so it can be toggled independently of edit_posts/edit_pages.
 */
add_filter( 'register_post_type_args', 'vavinde_split_create_posts_capability', 10, 2 );
function vavinde_split_create_posts_capability( $args, $post_type ) {
	if ( ! in_array( $post_type, array( 'post', 'page' ), true ) ) {
		return $args;
	}

	$capabilities                  = is_array( $args['capabilities'] ?? null ) ? $args['capabilities'] : array();
	$capabilities['create_posts']  = 'vavinde_create_' . $post_type . 's';
	$args['capabilities']          = $capabilities;

	return $args;
}

/**
 * Nobody has vavinde_create_posts/vavinde_create_pages via their role -
 * grant it dynamically here, for everyone except basic-tier site admins.
 * Every WordPress admin-menu entry, list-table button, admin-bar item, and
 * the post-new.php screen itself all check
 * $post_type_object->cap->create_posts (now this capability) before
 * showing/allowing "Add New" - so this one filter handles hiding the UI
 * and blocking the request.
 */
add_filter( 'user_has_cap', 'vavinde_grant_create_posts_capability', 10, 2 );
function vavinde_grant_create_posts_capability( $allcaps, $caps ) {
	if ( in_array( 'vavinde_create_posts', $caps, true ) || in_array( 'vavinde_create_pages', $caps, true ) ) {
		if ( ! vavinde_is_basic_tier() ) {
			$allcaps['vavinde_create_posts'] = true;
			$allcaps['vavinde_create_pages'] = true;
		}
	}

	return $allcaps;
}

/**
 * Appearance (wp-admin/menu.php checks switch_themes/edit_theme_options) is
 * shown purely based on capability - no separate admin_menu hiding needed.
 * Basic tier loses it entirely (protects the standard storefront template
 * from being changed); pro tier gets it back, same as full native
 * WordPress admin access.
 *
 * Installing, updating, deleting, or editing plugin files is deliberately
 * NOT managed here. WordPress core hardcodes those to super-admin-only on
 * multisite (`is_multisite() && ! is_super_admin()` -> do_not_allow in
 * map_meta_cap(), see wp-includes/capabilities.php) - no capability grant
 * can override that, and it stays permanently super-admin-only, no code
 * needed.
 */
add_filter( 'user_has_cap', 'vavinde_restrict_appearance', 10, 2 );
function vavinde_restrict_appearance( $allcaps, $caps ) {
	$managed_caps = array( 'edit_theme_options', 'switch_themes' );

	if ( ! array_intersect( $managed_caps, $caps ) ) {
		return $allcaps;
	}

	$grant = ! vavinde_is_basic_tier();
	foreach ( $managed_caps as $managed_cap ) {
		$allcaps[ $managed_cap ] = $grant;
	}

	return $allcaps;
}

/**
 * (De)activating an already-installed plugin normally requires
 * manage_network_plugins in addition to activate_plugins on multisite
 * (see the 'activate_plugins' case in map_meta_cap()) - a Network Admin
 * capability that would also unlock the Network Admin Plugins screen
 * (affecting every site), so we never grant it. Instead, drop that extra
 * requirement from the capability check itself, leaving only
 * 'activate_plugins' - which the site's own administrator role already has
 * by default. This only affects (de)activation on the site's own
 * wp-admin/plugins.php; Network Admin's screen checks
 * manage_network_plugins directly and is unaffected.
 *
 * Applies to every tier, not just pro: whatever Daniel installs (without
 * network-activating) is by definition vetted for any site to toggle on
 * itself - most importantly, payment method plugins (Stripe, NETOPIA),
 * which every owner needs to be able to pick alongside WhatsApp Order
 * regardless of tier, same reasoning as leaving Payments open to everyone.
 */
add_filter( 'map_meta_cap', 'vavinde_allow_activate_plugins', 10, 2 );
function vavinde_allow_activate_plugins( $caps, $cap ) {
	$plugin_activation_caps = array( 'activate_plugins', 'deactivate_plugins', 'activate_plugin', 'deactivate_plugin' );

	if ( ! in_array( $cap, $plugin_activation_caps, true ) ) {
		return $caps;
	}

	return array_diff( $caps, array( 'manage_network_plugins' ) );
}

/**
 * All of WooCommerce's review checks (post type comment support, the
 * frontend reviews tab/widget, wc_reviews_enabled()) read
 * get_option( 'woocommerce_enable_reviews' ) - force it to 'no' on basic
 * tier without touching the stored value, so whatever the owner had is
 * still there if the site is upgraded to pro later.
 */
add_filter( 'pre_option_woocommerce_enable_reviews', 'vavinde_disable_reviews_on_basic_tier' );
function vavinde_disable_reviews_on_basic_tier( $value ) {
	if ( vavinde_is_basic_tier() ) {
		return 'no';
	}

	return $value;
}

/**
 * WooCommerce's Products -> Reviews moderation screen
 * (edit.php?post_type=product / page=product-reviews) is registered
 * unconditionally - it doesn't check woocommerce_enable_reviews, since
 * it's meant for moderating reviews even while new submissions are off.
 * Basic-tier shops have no use for a moderation queue they can't receive
 * submissions into, so hide the submenu entry too.
 */
add_action( 'admin_menu', 'vavinde_hide_reviews_submenu_on_basic_tier', 999 );
function vavinde_hide_reviews_submenu_on_basic_tier() {
	if ( vavinde_is_basic_tier() ) {
		remove_submenu_page( 'edit.php?post_type=product', 'product-reviews' );
	}
}

/**
 * The core "Comments" menu moderates both post comments and product
 * reviews - on basic tier neither can ever have anything in it (Add
 * Post is blocked, reviews are disabled), so it's just clutter. Left
 * alone on pro tier, where reviews are enabled and posts can exist, so
 * there's real content to moderate.
 */
add_action( 'admin_menu', 'vavinde_hide_comments_menu_on_basic_tier', 999 );
function vavinde_hide_comments_menu_on_basic_tier() {
	if ( vavinde_is_basic_tier() ) {
		remove_menu_page( 'edit-comments.php' );
	}
}

/**
 * The homepage is the shop, not a blog - Posts has no role in this
 * business model, and Add Post is already blocked on basic tier anyway.
 * Left alone on pro tier, where content-marketing/SEO blogging is a
 * legitimate reason to unlock full access.
 */
add_action( 'admin_menu', 'vavinde_hide_posts_menu_on_basic_tier', 999 );
function vavinde_hide_posts_menu_on_basic_tier() {
	if ( vavinde_is_basic_tier() ) {
		remove_menu_page( 'edit.php' );
	}
}

/**
 * Analytics and Marketing gate on 'manage_woocommerce' - the same
 * capability Products/Orders use - so they can't be blocked by revoking a
 * capability without also breaking product/order management. Payments is
 * deliberately left alone (and out of $hidden_labels below): every site
 * owner, regardless of tier, should be able to pick and configure their
 * own payment methods (Stripe, PayPal, etc.) alongside WhatsApp Order.
 * Analytics/Marketing's exact menu slugs vary with WooCommerce version
 * and site state, so hide by matching the visible label instead of a
 * hardcoded slug.
 */
add_action( 'admin_menu', 'vavinde_hide_woocommerce_top_menus_on_basic_tier', 9999 );
function vavinde_hide_woocommerce_top_menus_on_basic_tier() {
	if ( ! vavinde_is_basic_tier() ) {
		return;
	}

	global $menu;
	if ( ! is_array( $menu ) ) {
		return;
	}

	$hidden_labels = array( 'analytics', 'marketing' );

	foreach ( $menu as $position => $menu_item ) {
		if ( empty( $menu_item[0] ) ) {
			continue;
		}

		$label = strtolower( wp_strip_all_tags( $menu_item[0] ) );

		foreach ( $hidden_labels as $hidden_label ) {
			if ( false !== strpos( $label, $hidden_label ) ) {
				unset( $menu[ $position ] );
				break;
			}
		}
	}
}

/**
 * Hiding the menu only removes the link - block the destinations directly
 * too, in case of a bookmarked/typed URL. Payments is intentionally not
 * blocked here (see above) - every owner can reach wc-settings&tab=checkout
 * regardless of tier.
 */
add_action( 'admin_init', 'vavinde_block_hidden_woocommerce_pages_on_basic_tier' );
function vavinde_block_hidden_woocommerce_pages_on_basic_tier() {
	if ( ! vavinde_is_basic_tier() ) {
		return;
	}

	$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';
	$path = isset( $_GET['path'] ) ? sanitize_text_field( wp_unslash( $_GET['path'] ) ) : '';

	$is_analytics = 'wc-admin' === $page && 0 === strpos( $path, '/analytics' );
	$is_marketing = ( 'wc-admin' === $page && 0 === strpos( $path, '/marketing' ) ) || 'woocommerce-marketing' === $page;

	if ( $is_analytics || $is_marketing ) {
		wp_die( esc_html__( 'Această secțiune nu este disponibilă pentru acest magazin.', 'vavinde' ), '', array( 'response' => 403 ) );
	}
}

/**
 * The social networks offered on "Setările magazinului meu", each stored
 * as its own vavinde_social_{network} option. Also read by
 * vavinde-storefront-template's footer - duplicated there rather than
 * shared, to avoid a load-order dependency between the two plugins.
 */
define(
	'VAVINDE_SOCIAL_NETWORKS',
	array(
		'facebook'  => 'Facebook',
		'instagram' => 'Instagram',
		'tiktok'    => 'TikTok',
		'x'         => 'X (Twitter)',
	)
);

/**
 * A minimal, standalone shortcut for the one setting every owner needs
 * most often - their WhatsApp number and whether WhatsApp Order is
 * enabled - without navigating the full Payments screen. Reads/writes the
 * exact same option the WC_Gateway_WhatsApp_Order gateway itself uses, so
 * changes here take effect immediately. Shown to everyone, all tiers.
 */
add_action( 'admin_menu', 'vavinde_add_store_settings_page' );
function vavinde_add_store_settings_page() {
	add_menu_page(
		__( 'Setările magazinului meu', 'vavinde' ),
		__( 'Setările magazinului meu', 'vavinde' ),
		'manage_options',
		'vavinde-store-settings',
		'vavinde_render_store_settings_page',
		'dashicons-whatsapp',
		56
	);
}

function vavinde_render_store_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$option_name = 'woocommerce_whatsapp_order_settings';
	$settings    = get_option( $option_name, array() );

	if ( isset( $_POST['vavinde_whatsapp_number'] ) && check_admin_referer( 'vavinde_store_settings' ) ) {
		$settings['whatsapp_number'] = sanitize_text_field( wp_unslash( $_POST['vavinde_whatsapp_number'] ) );
		$settings['enabled']         = isset( $_POST['vavinde_whatsapp_enabled'] ) ? 'yes' : 'no';
		update_option( $option_name, $settings );

		foreach ( VAVINDE_SOCIAL_NETWORKS as $network => $label ) {
			$field_name = 'vavinde_social_' . $network;
			update_option( $field_name, isset( $_POST[ $field_name ] ) ? sanitize_url( wp_unslash( $_POST[ $field_name ] ) ) : '' );
		}

		echo '<div class="notice notice-success"><p>' . esc_html__( 'Salvat.', 'vavinde' ) . '</p></div>';
	}

	$whatsapp_number  = isset( $settings['whatsapp_number'] ) ? $settings['whatsapp_number'] : '';
	$whatsapp_enabled = isset( $settings['enabled'] ) && 'yes' === $settings['enabled'];
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Setările magazinului meu', 'vavinde' ); ?></h1>
		<form method="post">
			<?php wp_nonce_field( 'vavinde_store_settings' ); ?>
			<table class="form-table">
				<tr>
					<th scope="row">
						<label for="vavinde_whatsapp_enabled"><?php esc_html_e( 'Activă', 'vavinde' ); ?></label>
					</th>
					<td>
						<label>
							<input type="checkbox" id="vavinde_whatsapp_enabled" name="vavinde_whatsapp_enabled" <?php checked( $whatsapp_enabled ); ?> />
							<?php esc_html_e( 'Activează plata prin WhatsApp la finalizarea comenzii', 'vavinde' ); ?>
						</label>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="vavinde_whatsapp_number"><?php esc_html_e( 'Număr WhatsApp', 'vavinde' ); ?></label>
					</th>
					<td>
						<input type="text" id="vavinde_whatsapp_number" name="vavinde_whatsapp_number" value="<?php echo esc_attr( $whatsapp_number ); ?>" class="regular-text" />
						<p class="description">
							<?php esc_html_e( 'Format internațional, fără +, fără 0 la început (ex. 407xxxxxxxx).', 'vavinde' ); ?>
						</p>
					</td>
				</tr>
				<?php foreach ( VAVINDE_SOCIAL_NETWORKS as $network => $label ) : ?>
					<tr>
						<th scope="row">
							<label for="vavinde_social_<?php echo esc_attr( $network ); ?>"><?php echo esc_html( $label ); ?></label>
						</th>
						<td>
							<input type="url" id="vavinde_social_<?php echo esc_attr( $network ); ?>" name="vavinde_social_<?php echo esc_attr( $network ); ?>" value="<?php echo esc_attr( get_option( 'vavinde_social_' . $network, '' ) ); ?>" class="regular-text" placeholder="https://<?php echo esc_attr( $network ); ?>.com/..." />
						</td>
					</tr>
				<?php endforeach; ?>
			</table>
			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}

/**
 * The fixed, network-wide product category list. Daniel curates this list
 * manually (edit here + reactivate) when a new product type needs adding -
 * there's no self-service "request a category" flow. Applies to every
 * site, every tier - this is a data-consistency rule for the future
 * cross-shop search, not a paid-feature restriction.
 */
define(
	'VAVINDE_PRODUCT_CATEGORIES',
	array(
		'Produs apicol',
		'Carne-Mezeluri',
		'Lactate',
		'Legume-Fructe',
		'Cereale',
		'Compoturi',
		'Murături',
		'Produse de panificație',
		'Băuturi tradiționale',
		'Dulciuri-Patiserie',
		'Condimente și plante aromatice',
		'Ulei presat la rece',
		'Nuci și semințe',
		'Altele-Meșteșugărit',
	)
);

/**
 * Seeds the fixed category list into the current site's own product_cat
 * taxonomy (each site has its own copy in Multisite - there's no shared
 * "global terms" table since WP 6.1). Runs once per site (guarded by a
 * site option) and re-runs harmlessly if new categories are added to the
 * constant above and the plugin is reactivated, since it skips terms that
 * already exist. Covers both existing sites (runs the first time an admin
 * visits) and future ones (runs the first time their first admin visits),
 * with no manual per-site step needed.
 */
add_action( 'admin_init', 'vavinde_seed_product_categories' );
function vavinde_seed_product_categories() {
	if ( ! taxonomy_exists( 'product_cat' ) ) {
		return;
	}

	if ( get_option( 'vavinde_categories_seeded' ) ) {
		return;
	}

	foreach ( VAVINDE_PRODUCT_CATEGORIES as $category_name ) {
		if ( ! term_exists( $category_name, 'product_cat' ) ) {
			wp_insert_term( $category_name, 'product_cat' );
		}
	}

	update_option( 'vavinde_categories_seeded', true );
}

/**
 * Nobody but Daniel (super admin) can create/edit/delete product_cat
 * terms - keeps the category list identical across every shop, which the
 * future cross-shop search depends on. assign_product_terms (ticking an
 * existing category on a product) is deliberately left untouched.
 */
add_filter( 'user_has_cap', 'vavinde_block_managing_product_categories', 10, 2 );
function vavinde_block_managing_product_categories( $allcaps, $caps ) {
	$managed_caps = array( 'manage_product_terms', 'edit_product_terms', 'delete_product_terms' );

	if ( ! array_intersect( $managed_caps, $caps ) || is_super_admin() ) {
		return $allcaps;
	}

	foreach ( $managed_caps as $managed_cap ) {
		$allcaps[ $managed_cap ] = false;
	}

	return $allcaps;
}

/**
 * Requires at least one *real* product_cat term before a product can go
 * live - checked after save (works regardless of which editor/API saved
 * the post), not via $_POST parsing. Demotes back to draft rather than
 * blocking the save outright, so the owner doesn't lose their work.
 *
 * WooCommerce registers product_cat with a default_term ("Fără
 * categorie") - WordPress core auto-assigns it to any product saved with
 * no category, inside wp_insert_post(), before save_post_product even
 * fires. So wp_get_post_terms() is never actually empty; the default
 * term's ID (tracked per-site in the default_product_cat option) has to
 * be excluded explicitly, or this check would never trigger.
 */
add_action( 'save_post_product', 'vavinde_require_product_category_before_publish', 20, 2 );
function vavinde_require_product_category_before_publish( $post_id, $post ) {
	if ( 'publish' !== $post->post_status ) {
		return;
	}

	$term_ids       = wp_get_post_terms( $post_id, 'product_cat', array( 'fields' => 'ids' ) );
	$default_cat_id = (int) get_option( 'default_product_cat' );
	$real_term_ids  = array_diff( $term_ids, array( $default_cat_id ) );

	if ( ! empty( $real_term_ids ) ) {
		return;
	}

	remove_action( 'save_post_product', __FUNCTION__, 20 );
	wp_update_post( array( 'ID' => $post_id, 'post_status' => 'draft' ) );
	add_action( 'save_post_product', __FUNCTION__, 20, 2 );

	add_filter(
		'redirect_post_location',
		function ( $location ) {
			return add_query_arg( 'vavinde_missing_category', '1', $location );
		}
	);
}

add_action( 'admin_notices', 'vavinde_missing_category_notice' );
function vavinde_missing_category_notice() {
	if ( isset( $_GET['vavinde_missing_category'] ) ) {
		echo '<div class="notice notice-error"><p>' . esc_html__( 'Produsul a fost salvat ca ciornă - selectează cel puțin o categorie înainte de publicare.', 'vavinde' ) . '</p></div>';
	}
}
