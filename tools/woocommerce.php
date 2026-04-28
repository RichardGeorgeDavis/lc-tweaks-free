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
	$dlck_product_header_layout = get_option('dlck_product_header_layout');?>
	
<div id="woocommerce" class="tool <?php echo $active_tab == 'woocommerce' ? 'tool-active' : ''; ?>">
	<h2 class="tool-section"><?php _e('Custom Layouts', 'divi-lc-kit'); ?></h2>
	
	<div class="lc-kit">
		<div class="box-title">
			<h3><?php _e('After Header - Woo Shop Page Layout', 'divi-lc-kit'); ?></h3>
			
			<div class="box-descr"><p><?php _e("This layout will be displayed after the main header and navigation on WooCommerce Shop Page.", "divi-lc-kit"); ?></p></div>			
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
			<h3><?php _e('After Header - Woo Shop Category Layout', 'divi-lc-kit'); ?></h3>
			
			<div class="box-descr"><p><?php _e("This layout will be displayed after the main header and navigation when viewing a WooCommerce product category.", "divi-lc-kit"); ?></p></div>			
		</div>
		<div class="box-content">
			<?php	
				if ($layouts = get_posts($layout_query)) {
					?>
					<select name="dlck_shop_cat_header_layout">
					<option value="">----- <?php _e('None', 'divi-lc-kit'); ?> -----</option>
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
			<h3><?php _e('After Header - Woo Product Page Layout', 'divi-lc-kit'); ?></h3>
			
			<div class="box-descr"><p><?php _e("This layout will be displayed after the main header and navigation on WooCommerce product pages.", "divi-lc-kit"); ?></p></div>			
		</div>
		<div class="box-content">
			<?php	
				if ($layouts = get_posts($layout_query)) {
					?>
					<select name="dlck_product_header_layout">
					<option value="">----- <?php _e('None', 'divi-lc-kit'); ?> -----</option>
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