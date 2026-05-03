<?php
	$layout_query = array(
	'post_type'=>'et_pb_layout',
	'posts_per_page'=>-1,
	'meta_query' => array(
		array(
		'key' => '_et_pb_predefined_layout',
		'compare' => 'NOT EXISTS')
		)
	);
	$dlck_shop_header_layout = get_option('dlck_shop_header_layout');
	$dlck_shop_cat_header_layout = get_option('dlck_shop_cat_header_layout');
	$dlck_product_header_layout = get_option('dlck_product_header_layout');
	$dlck_woo_block_bad_cart_query_abuse_val = dlck_get_option( 'dlck_woo_block_bad_cart_query_abuse' );?>
	
<div id="woocommerce" class="tool <?php echo $active_tab == 'woo-tweaks' ? 'tool-active' : ''; ?>">
	<h2 class="tool-section"><?php echo esc_html_e( 'Request Protection', 'lc-tweaks' ); ?></h2>

	<div class="lc-kit">
		<div class="box-title">
			<h3><?php echo esc_html_e( 'Block Bad WooCommerce Query Abuse', 'lc-tweaks' ); ?></h3>
			<div class="box-descr">
				<p><?php echo esc_html_e( 'Return a 403 when WooCommerce cart-style query actions are combined with common ad click tracking parameters.', 'lc-tweaks' ); ?></p>
			</div>
		</div>
		<div class="box-content minibox">
			<div class="checkbox">
				<input name="dlck_woo_block_bad_cart_query_abuse" type="checkbox" value="1" <?php checked( '1', $dlck_woo_block_bad_cart_query_abuse_val ); ?> />
			</div>
		</div>
	</div>

	<h2 class="tool-section"><?php _e('Custom Layouts', 'lc-tweaks'); ?></h2>
	
	<div class="lc-kit">
		<div class="box-title">
			<h3><?php _e('After Header - Woo Shop Page Layout', 'lc-tweaks'); ?></h3>
			
			<div class="box-descr"><p><?php _e("This layout will be displayed after the main header and navigation on WooCommerce Shop Page.", "lc-tweaks"); ?></p></div>			
		</div>
		<div class="box-content">
			<?php	
				if ($layouts = get_posts($layout_query)) {
					?>
					<select name="dlck_shop_header_layout">
					<option value="">----- None -----</option>
					<?php
					foreach ($layouts as $layout) {
						echo '<option ' . selected($layout->ID, $dlck_shop_header_layout, false) . ' value="' . $layout->ID . '">' . $layout->post_title . '</option>';
					}
					echo '</select>';
					
				}
				else {
					echo '<p class="info">Sorry, your Divi Library is empty. Create and save some layouts first...</p>';	
				}
			?>
			</select>
		</div>
	</div>
	<div class="lc-kit">
		<div class="box-title">
			<h3><?php _e('After Header - Woo Shop Category Layout', 'lc-tweaks'); ?></h3>
			
			<div class="box-descr"><p><?php _e("This layout will be displayed after the main header and navigation when viewing a WooCommerce product category.", "lc-tweaks"); ?></p></div>			
		</div>
		<div class="box-content">
			<?php	
				if ($layouts = get_posts($layout_query)) {
					?>
					<select name="dlck_shop_cat_header_layout">
					<option value="">----- <?php _e('None', 'lc-tweaks'); ?> -----</option>
					<?php
					foreach ($layouts as $layout) {
						echo '<option ' . selected($layout->ID, $dlck_shop_cat_header_layout, false) . ' value="' . $layout->ID . '">' . $layout->post_title . '</option>';
					}
					echo '</select>';
					
				}
				else {
					echo '<p class="info">Sorry, your Divi Library is empty. Create and save some layouts first...</p>';	
				}
			?>
			</select>
		</div>
	</div>
	<div class="lc-kit">
		<div class="box-title">
			<h3><?php _e('After Header - Woo Product Page Layout', 'lc-tweaks'); ?></h3>
			
			<div class="box-descr"><p><?php _e("This layout will be displayed after the main header and navigation on WooCommerce product pages.", "lc-tweaks"); ?></p></div>			
		</div>
		<div class="box-content">
			<?php	
				if ($layouts = get_posts($layout_query)) {
					?>
					<select name="dlck_product_header_layout">
					<option value="">----- <?php _e('None', 'lc-tweaks'); ?> -----</option>
					<?php
					foreach ($layouts as $layout) {
						echo '<option ' . selected($layout->ID, $dlck_product_header_layout, false) . ' value="' . $layout->ID . '">' . $layout->post_title . '</option>';
					}
					echo '</select>';
					
				}
				else {
					echo '<p class="info">Sorry, your Divi Library is empty. Create and save some layouts first...</p>';	
				}
			?>
			</select>
		</div>
	</div>
		
</div>
