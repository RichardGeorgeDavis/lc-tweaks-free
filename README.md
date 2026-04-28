# LC Tweaks Free

LC Tweaks Free is a WordPress plugin for practical WordPress, Divi, and WooCommerce site tweaks. It is built from the private LC Tweaks source repository and published as a public free edition through GitHub Releases.

LC Tweaks Free is **not Divi-dependent**. It can run on a standard WordPress site without Divi. Divi-specific tools only appear and run when Divi is active, and WooCommerce-specific tools only appear and run when WooCommerce is active.

## Why This Plugin

After working on many sites that needed similar customizations over the years, I created LC Tweaks as a go-to toolkit for quickly applying common fixes, cleanup tasks, and quality-of-life improvements without rebuilding the same snippets for every project.

## Installation

1. Download the latest `lc-tweaks-free-{version}.zip` from the GitHub Releases page.
2. In WordPress, go to **Plugins > Add New > Upload Plugin**.
3. Upload the zip and activate **LC Tweaks**.
4. Open **LC Tweaks** in the WordPress admin menu and enable only the options you need.

Future free-edition updates are delivered through this repository's GitHub Releases.

## Feature Overview

LC Tweaks Free includes focused toggles for:

- WordPress cleanup, editor, upload, comments, and admin behavior.
- Divi frontend, builder, media, video, layout, and dashboard tweaks.
- WooCommerce performance, catalog, checkout, admin, order, email, and product identifier tools.
- Maintenance tooling for cache clearing, settings import/export, diagnostics, presets, scope rules, and migration helpers.

## WordPress Options

- **Antispam Email Shortcode:** add an obfuscated email shortcode.
- **Footer Date Shortcode:** add a reusable date/year shortcode for footer content.
- **Mobile Browser Theme Color:** set the mobile browser theme color meta value.
- **Click-to-Call Phone Numbers:** make phone number output easier to use on mobile.
- **SVG Uploads:** allow SVG upload support.
- **JSON Uploads:** allow JSON upload support.
- **TTF/Font Uploads:** allow font file upload support.
- **Unfiltered Uploads:** allow broader unfiltered uploads for trusted administrators.
- **Disable RSS Feed:** disable WordPress RSS feed output.
- **Disable WordPress Search:** disable native WordPress frontend search.
- **Disable Gutenberg:** disable the block editor where the plugin option applies.
- **Disable Block Editor for Widgets:** restore the classic widgets admin flow.
- **Disable All Comments:** remove comment support and comment-related surfaces.
- **Disable Plugin Auto Updates:** stop plugin auto-update behavior.
- **Disable Theme Auto Updates:** stop theme auto-update behavior.
- **Skip New Bundled Core Files:** skip installing new bundled files during core upgrades.
- **WordPress Auto Update Core:** manage core auto-update behavior.
- **Body Class User Role:** add user role data to body classes.
- **Disable Admin New User Notification Emails:** stop admin-facing new-user notification emails.
- **Hide Dashboard Welcome Panel:** remove the WordPress dashboard welcome panel.
- **Kill Jetpack Cron:** prevent Jetpack cron behavior from returning.
- **Speed Up Scheduled Actions:** improve scheduled action handling.
- **All Settings Page:** expose the WordPress all-options settings page.
- **Replace Image Tool:** replace a media attachment file while preserving the attachment ID and URL.
- **Rank Math Schema Enrichment:** enrich Rank Math schema and optional `llms.txt` data from LC Tweaks settings.

## Divi Options

These options appear when Divi is active.

- **Builder Safe Mode:** reduce moving parts while troubleshooting builder issues.
- **Disable Divi Premade Layouts:** remove premade layout loading.
- **Disable Divi Upsells Dashboard:** remove Divi upsell dashboard surfaces.
- **Disable Divi AI:** disable Divi AI surfaces where supported.
- **Stop Map Module Excerpts Loading:** prevent map module excerpt loading.
- **Hide Divi Cloud:** hide Divi Cloud surfaces.
- **Edit in Visual Builder Row Action:** add visual builder access from admin lists.
- **Divi Library View:** adjust Divi Library admin behavior.
- **WOFF Uploads:** allow WOFF upload support.
- **Custom Divi Icons:** add custom icon support.
- **Divi Lazy Loading:** lazy-load Divi sections.
- **Divi Lazy Defer Sections:** defer Divi section loading where configured.
- **Full Width Divi Footer:** make the Divi footer full width.
- **Sticky Footer:** keep footer positioning stable on short pages.
- **Social Links New Tab:** open Divi social links in a new tab.
- **Hide Projects:** hide Divi Projects where configured.
- **Move Sidebar to Top on Mobile:** improve sidebar ordering on mobile.
- **Fix Divi Anchor Links:** improve anchor link behavior.
- **Contact Form Copy to Sender:** send contact form copies to senders.
- **Accordions Closed by Default:** make Divi accordions start closed.
- **Disable WordPress Image Sizes:** disable selected generated image sizes.
- **Remove Divi Resize Image Gallery:** prevent gallery resize output.
- **Remove Divi Resize Image Portfolio:** prevent portfolio resize output.
- **Remove Divi Resize Image Post:** prevent post resize output.
- **Stop Divi Image Crop Portfolio:** stop portfolio image cropping.
- **Stop Divi Image Crop Gallery:** stop gallery image cropping.
- **Stop Divi Image Crop Blog:** stop blog image cropping.
- **Hide Related YouTube Video Suggestions:** adjust related YouTube suggestion behavior.
- **Disable Related YouTube Video Suggestions:** use overlay behavior to avoid related suggestions.
- **Autoplay Video Module Clips on Hover:** autoplay Divi video module clips on hover.
- **Autoplay Standard Videos and Hide Controls:** autoplay standard Divi video module videos and hide controls.
- **Fix YouTube Loading Height:** stabilize YouTube video module height.

## WooCommerce Options

These options appear when WooCommerce is active.

### Performance & Cleanup

- **Resave All Products:** admin tool for resaving product data.
- **Clean and Optimize WooCommerce Sessions Table:** maintain WooCommerce session data.
- **Disable Persistent Carts:** disable persistent cart behavior.
- **Woo Cart Script Policy:** control Woo cart script loading rules.
- **Disable WooCommerce Admin:** disable the WooCommerce Admin package.
- **Stop WooCommerce Files from Loading Safely:** remove Woo assets from non-Woo pages.
- **Stop All WooCommerce Files from Loading:** more aggressive Woo asset removal.
- **Stop WooCommerce Blocks from Loading:** remove WooCommerce block assets.
- **WP Rocket Side Cart Exclusion:** add side cart cache exclusions for Xootix-style carts.
- **Disable Reviews:** disable WooCommerce review tab behavior.
- **Disable Brands Feature:** disable WooCommerce brands surfaces.

### Admin Columns & Search

- **Orders: Search by SKU:** search orders by product SKU.
- **Orders: User Role Column:** add customer role data to orders.
- **Users: Order Counts Column:** show user order counts in admin users.
- **Products: Stock Status Column:** add product stock status admin data.
- **Products: Last Edited Column & Meta:** track and display last edited product data.
- **Google Listings & Ads Sync Column:** add Google sync status data where available.

### Catalog Layout & Display

- **Shop Single Column on Mobile:** make shop grids single-column on mobile.
- **Display Add to Cart Button on Archives:** show add-to-cart buttons in archives.
- **Shop Masonry Layout:** enable masonry-style shop layout.
- **Hide Price & Add to Cart for Logged Out Users:** restrict purchase UI for logged-out visitors.
- **Add Line Break in Product Titles:** insert title line breaks.
- **Capitalize Product Titles:** normalize product title capitalization.
- **Change Out of Stock Button to Read More:** adjust out-of-stock button text.
- **Product Category Body Class on Single Product:** add category data to product page body classes.
- **Hide Legacy Custom Fields Metabox:** hide the classic custom fields metabox on products.
- **Hide Products Without Featured Image:** hide products missing featured images.
- **Redirect Empty Category Pagination:** redirect invalid empty category pagination.

### Checkout UX

- **Restrict Store to Logged-in Users:** require login for store access.
- **Disable Checkout Field Autocomplete:** disable browser autocomplete on checkout fields.
- **Empty Checkout Defaults:** clear default checkout field values.
- **Change City Label to Suburb:** relabel city field as suburb while keeping the underlying field key.
- **Move Labels Inside Inputs:** move checkout labels into fields.
- **Hide Woo Menu Item for Non-Administrators:** restrict Woo admin menu visibility.
- **Allow Guest Checkout for Existing Customers:** allow guest checkout for emails that already have accounts.
- **Remove Add-to-Cart URL Parameter:** clean add-to-cart parameters from URLs.
- **Remove Tax Suffix Labels:** remove tax suffix labels.
- **Prevent Duplicate Orders:** reduce duplicate order submissions.

### Single Product, Account, Orders, and Email

- **Buy Now Button:** add a direct purchase button.
- **Add to Cart Click Counter:** track add-to-cart clicks.
- **Refund Request Button:** add account refund request access.
- **Hide Downloads Tab Without Downloads:** hide empty downloads tab.
- **Custom Logout Redirect:** redirect users after logout.
- **Get Order IDs by Product:** admin utility for product/order lookup.
- **Move Orders Menu Item:** adjust Woo orders menu placement.
- **Complete Order Button:** add faster complete-order admin action.
- **Order History Meta Box:** show order history context in admin.
- **Sticky Product Update Button:** keep product update action accessible while editing.
- **Filter Products by Sale Status:** add sale-status filtering.
- **Allow Only Simple Products:** restrict product type behavior.
- **Remove Payments Menu:** hide Woo payments menu item.
- **Store Admin View:** adjust store admin access/view behavior.
- **Sort Order Items:** sort line items in orders.
- **Email Item Meta Tags:** adjust item meta output in emails.
- **Fix Email Product Name Symbols:** make trademark, registered, and copyright symbols email-friendly.
- **Cancelled Order Email to Customer:** send cancelled-order emails to customers.
- **Auto-send Pending Order Email:** trigger pending order emails.
- **Email Admin on Woo Fatal Errors:** notify admins about Woo fatal errors.
- **Set Product GTIN from SKU:** use SKUs as product identifiers for search/schema.
- **Set Google Sync Identifiers from SKU:** use SKUs for Google sync identifiers where supported.

## Maintenance and Settings

- **Settings Import/Export:** export, import, and migrate LC Tweaks settings.
- **Settings Snapshots:** keep recent settings snapshots for recovery.
- **Preset Apply/Restore:** apply preset settings and restore previous settings.
- **Diagnostics Export:** export diagnostic data for support.
- **Scope Rules:** limit selected tweaks by login state, role, include paths, and exclude paths.
- **Cache Tools:** clear plugin-managed caches and selected external caches where supported.
- **Force Update Checks:** force fresh WordPress plugin/theme update checks.
- **Divi Accessibility Migration:** support migration from the previously bundled Divi Accessibility module to the standalone plugin.

## Pro-Only Exclusions

LC Tweaks Free intentionally excludes these pro/current features:

- LC Tweaks pro licensing and private updater code.
- The bundled **Modules** tab and all bundled modules:
  - WP Page Transitions Advanced
  - Divi Woo Product Carousel
  - TM Divi Shop Extended
  - Text-On-A-Path
  - Content Intense
  - Map Module Extended
  - Custom Fullwidth Header Extended

## Updates

LC Tweaks Free updates from GitHub Releases. Each release must include a zip asset named:

```text
lc-tweaks-free-{version}.zip
```

For example:

```text
lc-tweaks-free-1.5.1.zip
```

Release tags should use the matching version, preferably with a `v` prefix:

```text
v1.5.1
```
