=== MRZ Product Filter Exp ===
Contributors: m0r3z
Tags: woocommerce, product filter, layered nav, filters, ajax
Requires at least: 6.3
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.5.1
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Filter WooCommerce products by category, attributes, tags and price, with color swatches, shown in a left column or above the shop, with AJAX updates.

== Description ==

MRZ Product Filter is a minimalist product filter for WooCommerce. It builds a filter panel from your product categories, attributes and tags, plus a price slider, and lets shoppers narrow down the shop or category archives with instant AJAX updates. A chosen "color" attribute is rendered as color swatches.

Each filter set is configured from a dedicated Custom Post Type and can be displayed automatically on the shop / archives, or placed manually with a shortcode. The markup is intentionally minimal (wireframe, black & white) so you can style it to match your theme.

= Features =

* Filter by product categories, tags, brands and product attributes (checkboxes, radio buttons or dropdowns).
* Price range slider (min / max).
* Color swatches for a chosen attribute — the color is stored on each attribute term and reusable.
* AJAX refresh of the product loop, with native URL parameters (shareable, SEO-friendly) and a no-JavaScript fallback that filters through WooCommerce natively.
* Collapsible sections (native `<details>`), with a per-section and global "expanded by default" setting.
* Automatic display on the shop and category / tag archives (left column or above the products), or manual placement with `[mrzpfe_product_filter id="X"]`.
* Multiple filter sets via a dedicated Custom Post Type — a different configuration per context.
* Translation-ready (text-domain `mrz-product-filter-exp`, .pot file provided).

= Requirements =

* WordPress 6.3 or later, PHP 7.4 or later.
* WooCommerce.

= Privacy / external calls =

MRZ Product Filter makes no external HTTP calls, loads no remote resources, and collects no telemetry. Everything runs locally on your site.

= Source code and contributions =

Development happens publicly on GitHub: https://github.com/m0r3z/mrz-product-filter-exp

== Installation ==

1. Install and activate WooCommerce.
2. Upload the plugin to `/wp-content/plugins/mrz-product-filter-exp` and activate it.
3. Open the "MRZ Product Filter" menu, create a filter set, add the sources (categories, tags, attributes), and choose a color attribute if needed.
4. Enable automatic display on the shop / archives, or copy the shortcode into a page.

== Changelog ==

= 1.5.1 =
* Declared compatibility with WooCommerce High-Performance Order Storage (HPOS). The plugin does not read or write orders.
* Added the `WC requires at least` and `WC tested up to` headers.
* Tested up to WordPress 7.1.

= 1.5.0 =
* Declared WooCommerce as a required plugin (`Requires Plugins` header, WordPress 6.5+).
* All source strings (admin, front end and plugin description) are now in English, as expected by translate.wordpress.org. French is provided as a fr_FR translation.
* The French (fr_FR) translation now ships with the plugin and is loaded from its `languages/` folder, so French sites stay in French. A translate.wordpress.org language pack, when available, takes precedence.
* Admin JavaScript messages (color attribute terms loading) are now translatable.

= 1.4.1 =
* Maintenance: build/CI update only (deploy workflow). No functional change to the plugin.

= 1.4.0 =
* All internal code identifiers now use the `mrzpfe` / `MRZPFE` / `Mrzpfe` prefix (4+ characters) per the WordPress.org guidelines: functions, classes, namespace, constants, options/transients, shortcode (`[mrzpfe_product_filter]`), AJAX action and the custom post type (now `mrzpfe_filter`). Note: an existing filter set created with a previous version must be recreated.

= 1.3.1 =
* Renamed the plugin to "MRZ Product Filter Exp" so the WordPress.org slug matches the text domain (`mrz-product-filter-exp`). Set the `Contributors` line to the owning wordpress.org account. Cosmetic / metadata only.

= 1.3.0 =
* New: contextual filters — on a category / brand / tag / attribute archive, every filter (categories, brands, materials, attributes…) only lists the terms actually present among that context's products, with the real per-context counts. Avoids offering a choice that would return no products.

= 1.2.0 =
* New: WooCommerce brands (the native `product_brand` taxonomy) can be added as a filter source, like categories and tags. Shown only when the taxonomy exists.
* New: optional "sticky" left column — the filter panel follows the scroll, with a configurable top offset (desktop / tablet).

= 1.1.1 =
* Fix: the "products per page" override is now forced via pre_get_posts, so it applies even when the theme sets its own products-per-page (e.g. Salient).
* New: extra "large screen" column level (≥ 1600px) in the responsive columns option.

= 1.1.0 =
* New: option to control the number of products per page and the number of columns per row, with responsive breakpoints (desktop / tablet / mobile). Applies on the shop and archives targeted by the filter set.

= 1.0.0 =
* First release.
* Filter by categories, tags, attributes and price, with color swatches for a chosen attribute.
* AJAX product loop refresh with native URL parameters and a no-JS fallback.
* Automatic display (left column / above products) or shortcode placement, configured from a Custom Post Type.
