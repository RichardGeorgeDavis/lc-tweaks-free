<?php

$dlck_settings_snapshot = function_exists( 'dlck_get_settings_snapshot' ) ? dlck_get_settings_snapshot() : array();
$dlck_setting = static function ( string $key, $default = '' ) use ( $dlck_settings_snapshot ) {
	if ( array_key_exists( $key, $dlck_settings_snapshot ) ) {
		$value = $dlck_settings_snapshot[ $key ];
		return ( $value === '' && $default !== '' ) ? $default : $value;
	}
	return $default;
};

if ( ! function_exists( 'dlck_rank_math_maintenance_not_set_label' ) ) {
	function dlck_rank_math_maintenance_not_set_label(): string {
		return __( 'Not set in Rank Math', 'divi-lc-kit' );
	}
}

if ( ! function_exists( 'dlck_rank_math_maintenance_normalize_scalar' ) ) {
	function dlck_rank_math_maintenance_normalize_scalar( $value ): string {
		if ( ! is_scalar( $value ) ) {
			return '';
		}

		return trim( wp_strip_all_tags( (string) $value ) );
	}
}

if ( ! function_exists( 'dlck_rank_math_maintenance_humanize_key' ) ) {
	function dlck_rank_math_maintenance_humanize_key( string $value ): string {
		$value = preg_replace( '/(?<!^)([A-Z])/', ' $1', $value );
		$value = str_replace( array( '-', '_' ), ' ', $value );

		return ucwords( trim( $value ) );
	}
}

if ( ! function_exists( 'dlck_rank_math_maintenance_format_list' ) ) {
	function dlck_rank_math_maintenance_format_list( array $items, string $separator = '; ' ): string {
		$items = array_values(
			array_filter(
				array_map(
					static function ( $item ) {
						return dlck_rank_math_maintenance_normalize_scalar( $item );
					},
					$items
				)
			)
		);

		return ! empty( $items ) ? implode( $separator, $items ) : dlck_rank_math_maintenance_not_set_label();
	}
}

if ( ! function_exists( 'dlck_rank_math_maintenance_format_address' ) ) {
	function dlck_rank_math_maintenance_format_address( $address ): string {
		if ( ! is_array( $address ) ) {
			return dlck_rank_math_maintenance_not_set_label();
		}

		$parts = array();
		foreach ( array( 'streetAddress', 'addressLocality', 'addressRegion', 'postalCode', 'addressCountry' ) as $key ) {
			if ( empty( $address[ $key ] ) ) {
				continue;
			}

			$parts[] = dlck_rank_math_maintenance_normalize_scalar( $address[ $key ] );
		}

		return dlck_rank_math_maintenance_format_list( $parts, ', ' );
	}
}

if ( ! function_exists( 'dlck_rank_math_maintenance_format_page' ) ) {
	function dlck_rank_math_maintenance_format_page( $page_id ): string {
		$page_id = absint( $page_id );
		if ( ! $page_id ) {
			return dlck_rank_math_maintenance_not_set_label();
		}

		$title = wp_strip_all_tags( get_the_title( $page_id ) );
		$url   = get_permalink( $page_id );

		if ( $title === '' ) {
			$title = sprintf( __( 'Page #%d', 'divi-lc-kit' ), $page_id );
		}

		return $url ? $title . ' (' . esc_url_raw( $url ) . ')' : $title;
	}
}

if ( ! function_exists( 'dlck_rank_math_maintenance_format_preview' ) ) {
	function dlck_rank_math_maintenance_format_preview( $value ): string {
		$value = dlck_rank_math_maintenance_normalize_scalar( preg_replace( '/\s+/', ' ', (string) $value ) );
		if ( $value === '' ) {
			return dlck_rank_math_maintenance_not_set_label();
		}

		return wp_trim_words( $value, 30, '...' );
	}
}

if ( ! function_exists( 'dlck_rank_math_maintenance_format_json_preview' ) ) {
	function dlck_rank_math_maintenance_format_json_preview( $value ): string {
		$value = trim( (string) $value );
		if ( $value === '' ) {
			return __( 'No advanced JSON saved in LC Tweaks.', 'divi-lc-kit' );
		}

		$decoded = json_decode( $value, true );
		if ( is_array( $decoded ) ) {
			$pretty = wp_json_encode( $decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
			if ( is_string( $pretty ) && $pretty !== '' ) {
				return $pretty;
			}
		}

		return $value;
	}
}

if ( ! function_exists( 'dlck_rank_math_maintenance_render_summary_rows' ) ) {
	function dlck_rank_math_maintenance_render_summary_rows( array $rows ): void {
		echo '<div class="dlck-rank-math-summary-list">';
		foreach ( $rows as $row ) {
			$label = isset( $row['label'] ) ? (string) $row['label'] : '';
			$value = isset( $row['value'] ) ? (string) $row['value'] : '';

			echo '<div class="dlck-rank-math-summary-row">';
			echo '<div class="dlck-rank-math-summary-label">' . esc_html( $label ) . '</div>';
			echo '<div class="dlck-rank-math-summary-value">' . esc_html( $value ) . '</div>';
			echo '</div>';
		}
		echo '</div>';
	}
}

$dlck_kill_jetpack_cron_val          = $dlck_setting( 'dlck_kill_jetpack_cron' );
$dlck_speedup_scheduled_actions_val  = $dlck_setting( 'dlck_speedup_scheduled_actions' );
$dlck_wprocket_force_page_caching_val = $dlck_setting( 'dlck_wprocket_force_page_caching' );
$dlck_wprocket_cache_wp_rest_api_val = $dlck_setting( 'dlck_wprocket_cache_wp_rest_api' );
$dlck_wprocket_disable_above_fold_opt_val = $dlck_setting( 'dlck_wprocket_disable_above_fold_opt' );
$dlck_wprocket_disable_priority_elements_val = $dlck_setting( 'dlck_wprocket_disable_priority_elements' );
$dlck_disable_ssl_curl_error_60_in_wpallimport_val = $dlck_setting( 'dlck_disable_ssl_curl_error_60_in_wpallimport' );
$dlck_exactdn_image_downsize_scale_val = $dlck_setting( 'dlck_exactdn_image_downsize_scale' );
$dlck_disable_plugin_auto_updates_val = $dlck_setting( 'dlck_disable_plugin_auto_updates' );
$dlck_disable_theme_auto_updates_val  = $dlck_setting( 'dlck_disable_theme_auto_updates' );
$dlck_allow_unfiltered_uploads_val    = $dlck_setting( 'dlck_allow_unfiltered_uploads' );
$dlck_replace_image_tool_val          = $dlck_setting( 'dlck_replace_image_tool' );
$dlck_svg_uploads_val                 = $dlck_setting( 'dlck_svg_uploads' );
$dlck_json_uploads_val                = $dlck_setting( 'dlck_json_uploads' );
$dlck_ttf_uploads_val                 = $dlck_setting( 'dlck_ttf_uploads' );
$dlck_core_upgrade_skip_new_bundled_val = $dlck_setting( 'dlck_core_upgrade_skip_new_bundled' );
$dlck_wp_auto_update_core_val         = $dlck_setting( 'dlck_wp_auto_update_core' );
$dlck_hide_dashboard_welcome_panel_val = $dlck_setting( 'dlck_hide_dashboard_welcome_panel' );
$dlck_all_wp_settings_page_val        = $dlck_setting( 'dlck_all_wp_settings_page' );
$dlck_builder_safe_mode_val           = $dlck_setting( 'dlck_builder_safe_mode' );
$dlck_divi_theme_active               = function_exists( 'dlck_is_divi_theme_active' ) && dlck_is_divi_theme_active();
$dlck_rank_math_schema_enrichment_val        = $dlck_setting( 'dlck_rank_math_schema_enrichment' );
$dlck_rank_math_schema_knows_about_val       = $dlck_setting( 'dlck_rank_math_schema_knows_about' );
$dlck_rank_math_schema_area_served_val       = $dlck_setting( 'dlck_rank_math_schema_area_served' );
$dlck_rank_math_schema_founders_val          = $dlck_setting( 'dlck_rank_math_schema_founders' );
$dlck_rank_math_schema_employees_val         = $dlck_setting( 'dlck_rank_math_schema_employees' );
$dlck_rank_math_schema_contact_languages_val = $dlck_setting( 'dlck_rank_math_schema_contact_languages' );
$dlck_rank_math_schema_contact_points_val    = $dlck_setting( 'dlck_rank_math_schema_contact_points' );
$dlck_rank_math_schema_advanced_json_val     = $dlck_setting( 'dlck_rank_math_schema_advanced_json' );
$dlck_rank_math_active                       = class_exists( 'RankMath' ) || function_exists( 'rank_math' );
$dlck_rank_math_schema_settings_url          = admin_url( 'admin.php?page=rank-math-options-titles' );
$dlck_rank_math_llms_settings_url            = admin_url( 'admin.php?page=rank-math-options-general#setting-panel-llms' );
$dlck_rank_math_llms_url                     = home_url( '/llms.txt' );
$dlck_updates_page_url                       = admin_url( 'update-core.php' );
$dlck_force_update_check_permissions         = function_exists( 'dlck_force_update_check_permissions' )
	? dlck_force_update_check_permissions()
	: array(
		'plugins' => current_user_can( 'update_plugins' ),
		'themes'  => current_user_can( 'update_themes' ),
	);
$dlck_can_force_update_check                 = ! empty( $dlck_force_update_check_permissions['plugins'] ) || ! empty( $dlck_force_update_check_permissions['themes'] );
$dlck_force_update_scope_label               = __( 'plugins and themes', 'divi-lc-kit' );
if ( ! empty( $dlck_force_update_check_permissions['plugins'] ) && empty( $dlck_force_update_check_permissions['themes'] ) ) {
	$dlck_force_update_scope_label = __( 'plugins', 'divi-lc-kit' );
} elseif ( empty( $dlck_force_update_check_permissions['plugins'] ) && ! empty( $dlck_force_update_check_permissions['themes'] ) ) {
	$dlck_force_update_scope_label = __( 'themes', 'divi-lc-kit' );
}
$dlck_rank_math_modules                      = get_option( 'rank_math_modules', array() );
$dlck_rank_math_titles_settings              = get_option( 'rank-math-options-titles', array() );
$dlck_rank_math_general_settings             = get_option( 'rank-math-options-general', array() );
$dlck_rank_math_phone_type_labels            = array();
$dlck_rank_math_business_type_labels         = array();
$dlck_rank_math_additional_info_labels       = array();

if ( ! is_array( $dlck_rank_math_modules ) ) {
	$dlck_rank_math_modules = array();
}

if ( ! is_array( $dlck_rank_math_titles_settings ) ) {
	$dlck_rank_math_titles_settings = array();
}

if ( ! is_array( $dlck_rank_math_general_settings ) ) {
	$dlck_rank_math_general_settings = array();
}

$dlck_rank_math_llms_enabled    = $dlck_rank_math_active && in_array( 'llms-txt', $dlck_rank_math_modules, true );
$dlck_rank_math_sitemap_enabled = $dlck_rank_math_active && in_array( 'sitemap', $dlck_rank_math_modules, true );

if ( class_exists( '\RankMath\Helper' ) ) {
	if ( method_exists( '\RankMath\Helper', 'choices_phone_types' ) ) {
		$dlck_rank_math_phone_type_labels = array_map(
			static function ( $label ) {
				return html_entity_decode( wp_strip_all_tags( (string) $label ), ENT_QUOTES, 'UTF-8' );
			},
			\RankMath\Helper::choices_phone_types()
		);
	}

	if ( method_exists( '\RankMath\Helper', 'choices_business_types' ) ) {
		$dlck_rank_math_business_type_labels = array_map(
			static function ( $label ) {
				return html_entity_decode( wp_strip_all_tags( (string) $label ), ENT_QUOTES, 'UTF-8' );
			},
			\RankMath\Helper::choices_business_types( true )
		);
	}

	if ( method_exists( '\RankMath\Helper', 'choices_additional_organization_info' ) ) {
		$dlck_rank_math_additional_info_labels = array_map(
			static function ( $label ) {
				return html_entity_decode( wp_strip_all_tags( (string) $label ), ENT_QUOTES, 'UTF-8' );
			},
			\RankMath\Helper::choices_additional_organization_info()
		);
	}
}

$dlck_rank_math_current_graph_type_key   = ( isset( $dlck_rank_math_titles_settings['knowledgegraph_type'] ) && $dlck_rank_math_titles_settings['knowledgegraph_type'] === 'company' ) ? 'company' : 'person';
$dlck_rank_math_current_graph_type       = $dlck_rank_math_current_graph_type_key === 'company' ? __( 'Organization', 'divi-lc-kit' ) : __( 'Person', 'divi-lc-kit' );
$dlck_rank_math_current_org_name         = dlck_rank_math_maintenance_normalize_scalar( $dlck_rank_math_titles_settings['knowledgegraph_name'] ?? '' );
$dlck_rank_math_current_website_name     = dlck_rank_math_maintenance_normalize_scalar( $dlck_rank_math_titles_settings['website_name'] ?? '' );
$dlck_rank_math_current_website_alt_name = dlck_rank_math_maintenance_normalize_scalar( $dlck_rank_math_titles_settings['website_alternate_name'] ?? '' );
$dlck_rank_math_current_description      = dlck_rank_math_maintenance_normalize_scalar( $dlck_rank_math_titles_settings['organization_description'] ?? '' );
$dlck_rank_math_current_url              = dlck_rank_math_maintenance_normalize_scalar( $dlck_rank_math_titles_settings['url'] ?? '' );
$dlck_rank_math_current_email            = dlck_rank_math_maintenance_normalize_scalar( $dlck_rank_math_titles_settings['email'] ?? '' );
$dlck_rank_math_current_logo             = dlck_rank_math_maintenance_normalize_scalar( $dlck_rank_math_titles_settings['knowledgegraph_logo'] ?? '' );
$dlck_rank_math_current_address          = dlck_rank_math_maintenance_format_address( $dlck_rank_math_titles_settings['local_address'] ?? array() );
$dlck_rank_math_current_business_type    = '';
$dlck_rank_math_current_geo              = dlck_rank_math_maintenance_normalize_scalar( $dlck_rank_math_titles_settings['geo'] ?? '' );
$dlck_rank_math_current_price_range      = dlck_rank_math_maintenance_normalize_scalar( $dlck_rank_math_titles_settings['price_range'] ?? '' );
$dlck_rank_math_current_about_page       = dlck_rank_math_maintenance_format_page( $dlck_rank_math_titles_settings['local_seo_about_page'] ?? 0 );
$dlck_rank_math_current_contact_page     = dlck_rank_math_maintenance_format_page( $dlck_rank_math_titles_settings['local_seo_contact_page'] ?? 0 );
$dlck_rank_math_current_contact_numbers  = array();
$dlck_rank_math_current_opening_hours    = array();
$dlck_rank_math_current_additional_info  = array();
$dlck_rank_math_current_social_profiles  = array();
$dlck_rank_math_current_llms_post_types  = array();
$dlck_rank_math_current_llms_taxonomies  = array();
$dlck_rank_math_current_llms_limit       = absint( $dlck_rank_math_general_settings['llms_limit'] ?? 100 );
$dlck_rank_math_current_llms_extra       = dlck_rank_math_maintenance_format_preview( $dlck_rank_math_general_settings['llms_extra_content'] ?? '' );

if ( $dlck_rank_math_current_org_name === '' ) {
	$dlck_rank_math_current_org_name = get_bloginfo( 'name' );
}

if ( $dlck_rank_math_current_website_name === '' ) {
	$dlck_rank_math_current_website_name = $dlck_rank_math_current_org_name;
}

if ( $dlck_rank_math_current_description === '' ) {
	$dlck_rank_math_current_description = dlck_rank_math_maintenance_normalize_scalar( get_bloginfo( 'description' ) );
}

if ( $dlck_rank_math_current_url === '' ) {
	$dlck_rank_math_current_url = home_url( '/' );
}

if ( $dlck_rank_math_current_llms_limit < 1 ) {
	$dlck_rank_math_current_llms_limit = 100;
}

if ( $dlck_rank_math_current_logo === '' && ! empty( $dlck_rank_math_titles_settings['knowledgegraph_logo_id'] ) ) {
	$dlck_rank_math_current_logo = dlck_rank_math_maintenance_normalize_scalar( wp_get_attachment_url( absint( $dlck_rank_math_titles_settings['knowledgegraph_logo_id'] ) ) );
}

if ( ! empty( $dlck_rank_math_titles_settings['local_business_type'] ) ) {
	$dlck_rank_math_business_type_key  = dlck_rank_math_maintenance_normalize_scalar( $dlck_rank_math_titles_settings['local_business_type'] );
	$dlck_rank_math_current_business_type = $dlck_rank_math_business_type_labels[ $dlck_rank_math_business_type_key ] ?? dlck_rank_math_maintenance_humanize_key( $dlck_rank_math_business_type_key );
}

if ( ! empty( $dlck_rank_math_titles_settings['phone'] ) ) {
	$dlck_rank_math_current_contact_numbers[] = dlck_rank_math_maintenance_normalize_scalar( $dlck_rank_math_titles_settings['phone'] );
}

if ( ! empty( $dlck_rank_math_titles_settings['phone_numbers'] ) && is_array( $dlck_rank_math_titles_settings['phone_numbers'] ) ) {
	foreach ( $dlck_rank_math_titles_settings['phone_numbers'] as $phone_entry ) {
		if ( ! is_array( $phone_entry ) || empty( $phone_entry['number'] ) ) {
			continue;
		}

		$phone_number = dlck_rank_math_maintenance_normalize_scalar( $phone_entry['number'] );
		$phone_type   = dlck_rank_math_maintenance_normalize_scalar( $phone_entry['type'] ?? '' );
		$phone_label  = $dlck_rank_math_phone_type_labels[ $phone_type ] ?? '';
		if ( $phone_label === '' && $phone_type !== '' ) {
			$phone_label = dlck_rank_math_maintenance_humanize_key( $phone_type );
		}

		$dlck_rank_math_current_contact_numbers[] = $phone_label !== '' ? $phone_label . ': ' . $phone_number : $phone_number;
	}
}

if ( ! empty( $dlck_rank_math_titles_settings['opening_hours'] ) && is_array( $dlck_rank_math_titles_settings['opening_hours'] ) ) {
	foreach ( $dlck_rank_math_titles_settings['opening_hours'] as $opening_hour ) {
		if ( ! is_array( $opening_hour ) ) {
			continue;
		}

		$day  = dlck_rank_math_maintenance_normalize_scalar( $opening_hour['day'] ?? '' );
		$time = dlck_rank_math_maintenance_normalize_scalar( $opening_hour['time'] ?? '' );
		if ( $day === '' && $time === '' ) {
			continue;
		}

		$dlck_rank_math_current_opening_hours[] = trim( $day . ' ' . $time );
	}
}

if ( ! empty( $dlck_rank_math_titles_settings['additional_info'] ) && is_array( $dlck_rank_math_titles_settings['additional_info'] ) ) {
	foreach ( $dlck_rank_math_titles_settings['additional_info'] as $property ) {
		if ( ! is_array( $property ) || empty( $property['value'] ) ) {
			continue;
		}

		$property_type  = dlck_rank_math_maintenance_normalize_scalar( $property['type'] ?? '' );
		$property_label = $dlck_rank_math_additional_info_labels[ $property_type ] ?? '';
		if ( $property_label === '' && $property_type !== '' ) {
			$property_label = dlck_rank_math_maintenance_humanize_key( $property_type );
		}

		$property_value = dlck_rank_math_maintenance_normalize_scalar( $property['value'] );
		$dlck_rank_math_current_additional_info[] = $property_label !== '' ? $property_label . ': ' . $property_value : $property_value;
	}
}

if ( ! empty( $dlck_rank_math_titles_settings['social_url_facebook'] ) ) {
	$dlck_rank_math_current_social_profiles[] = dlck_rank_math_maintenance_normalize_scalar( $dlck_rank_math_titles_settings['social_url_facebook'] );
}

if ( ! empty( $dlck_rank_math_titles_settings['twitter_author_names'] ) ) {
	$twitter_handle = ltrim( dlck_rank_math_maintenance_normalize_scalar( $dlck_rank_math_titles_settings['twitter_author_names'] ), '@' );
	if ( $twitter_handle !== '' ) {
		$dlck_rank_math_current_social_profiles[] = 'https://twitter.com/' . $twitter_handle;
	}
}

if ( ! empty( $dlck_rank_math_titles_settings['social_additional_profiles'] ) ) {
	$additional_profiles = preg_split( '/\r\n|\r|\n/', (string) $dlck_rank_math_titles_settings['social_additional_profiles'] );
	if ( is_array( $additional_profiles ) ) {
		foreach ( $additional_profiles as $profile_url ) {
			$profile_url = dlck_rank_math_maintenance_normalize_scalar( $profile_url );
			if ( $profile_url === '' ) {
				continue;
			}

			$dlck_rank_math_current_social_profiles[] = $profile_url;
		}
	}
}

if ( ! empty( $dlck_rank_math_general_settings['llms_post_types'] ) && is_array( $dlck_rank_math_general_settings['llms_post_types'] ) ) {
	foreach ( $dlck_rank_math_general_settings['llms_post_types'] as $post_type ) {
		$post_type = dlck_rank_math_maintenance_normalize_scalar( $post_type );
		if ( $post_type === '' ) {
			continue;
		}

		$post_type_object = get_post_type_object( $post_type );
		$dlck_rank_math_current_llms_post_types[] = ( $post_type_object && ! empty( $post_type_object->labels->name ) ) ? $post_type_object->labels->name : $post_type;
	}
}

if ( ! empty( $dlck_rank_math_general_settings['llms_taxonomies'] ) && is_array( $dlck_rank_math_general_settings['llms_taxonomies'] ) ) {
	foreach ( $dlck_rank_math_general_settings['llms_taxonomies'] as $taxonomy ) {
		$taxonomy = dlck_rank_math_maintenance_normalize_scalar( $taxonomy );
		if ( $taxonomy === '' ) {
			continue;
		}

		$taxonomy_object = get_taxonomy( $taxonomy );
		$dlck_rank_math_current_llms_taxonomies[] = ( $taxonomy_object && ! empty( $taxonomy_object->labels->name ) ) ? $taxonomy_object->labels->name : $taxonomy;
	}
}

$dlck_rank_math_current_contact_numbers = array_values( array_unique( $dlck_rank_math_current_contact_numbers ) );
$dlck_rank_math_current_opening_hours   = array_values( array_unique( $dlck_rank_math_current_opening_hours ) );
$dlck_rank_math_current_additional_info = array_values( array_unique( $dlck_rank_math_current_additional_info ) );
$dlck_rank_math_current_social_profiles = array_values( array_unique( $dlck_rank_math_current_social_profiles ) );
$dlck_rank_math_current_llms_post_types = array_values( array_unique( $dlck_rank_math_current_llms_post_types ) );
$dlck_rank_math_current_llms_taxonomies = array_values( array_unique( $dlck_rank_math_current_llms_taxonomies ) );
$dlck_rank_math_schema_advanced_json_preview = dlck_rank_math_maintenance_format_json_preview( $dlck_rank_math_schema_advanced_json_val );
$dlck_rank_math_local_summary_rows           = array(
	array(
		'label' => __( 'Person or Company', 'divi-lc-kit' ),
		'value' => $dlck_rank_math_current_graph_type,
	),
	array(
		'label' => __( 'Website Name', 'divi-lc-kit' ),
		'value' => $dlck_rank_math_current_website_name !== '' ? $dlck_rank_math_current_website_name : dlck_rank_math_maintenance_not_set_label(),
	),
	array(
		'label' => __( 'Website Alternate Name', 'divi-lc-kit' ),
		'value' => $dlck_rank_math_current_website_alt_name !== '' ? $dlck_rank_math_current_website_alt_name : dlck_rank_math_maintenance_not_set_label(),
	),
	array(
		'label' => __( 'Person/Organization Name', 'divi-lc-kit' ),
		'value' => $dlck_rank_math_current_org_name !== '' ? $dlck_rank_math_current_org_name : dlck_rank_math_maintenance_not_set_label(),
	),
	array(
		'label' => __( 'Description', 'divi-lc-kit' ),
		'value' => $dlck_rank_math_current_description !== '' ? $dlck_rank_math_current_description : dlck_rank_math_maintenance_not_set_label(),
	),
	array(
		'label' => __( 'URL', 'divi-lc-kit' ),
		'value' => $dlck_rank_math_current_url !== '' ? $dlck_rank_math_current_url : dlck_rank_math_maintenance_not_set_label(),
	),
	array(
		'label' => __( 'Email', 'divi-lc-kit' ),
		'value' => $dlck_rank_math_current_email !== '' ? $dlck_rank_math_current_email : dlck_rank_math_maintenance_not_set_label(),
	),
	array(
		'label' => __( 'Current Logo', 'divi-lc-kit' ),
		'value' => $dlck_rank_math_current_logo !== '' ? $dlck_rank_math_current_logo : dlck_rank_math_maintenance_not_set_label(),
	),
	array(
		'label' => __( 'Phone / Contact Numbers', 'divi-lc-kit' ),
		'value' => dlck_rank_math_maintenance_format_list( $dlck_rank_math_current_contact_numbers ),
	),
	array(
		'label' => __( 'Address', 'divi-lc-kit' ),
		'value' => $dlck_rank_math_current_address,
	),
	array(
		'label' => __( 'Business Type', 'divi-lc-kit' ),
		'value' => $dlck_rank_math_current_business_type !== '' ? $dlck_rank_math_current_business_type : dlck_rank_math_maintenance_not_set_label(),
	),
	array(
		'label' => __( 'Geo Coordinates', 'divi-lc-kit' ),
		'value' => $dlck_rank_math_current_geo !== '' ? $dlck_rank_math_current_geo : dlck_rank_math_maintenance_not_set_label(),
	),
	array(
		'label' => __( 'Opening Hours', 'divi-lc-kit' ),
		'value' => dlck_rank_math_maintenance_format_list( $dlck_rank_math_current_opening_hours ),
	),
	array(
		'label' => __( 'Price Range', 'divi-lc-kit' ),
		'value' => $dlck_rank_math_current_price_range !== '' ? $dlck_rank_math_current_price_range : dlck_rank_math_maintenance_not_set_label(),
	),
	array(
		'label' => __( 'Additional Organization Info', 'divi-lc-kit' ),
		'value' => dlck_rank_math_maintenance_format_list( $dlck_rank_math_current_additional_info ),
	),
	array(
		'label' => __( 'Social Profiles (sameAs)', 'divi-lc-kit' ),
		'value' => dlck_rank_math_maintenance_format_list( $dlck_rank_math_current_social_profiles ),
	),
	array(
		'label' => __( 'About Page', 'divi-lc-kit' ),
		'value' => $dlck_rank_math_current_about_page,
	),
	array(
		'label' => __( 'Contact Page', 'divi-lc-kit' ),
		'value' => $dlck_rank_math_current_contact_page,
	),
);
$dlck_rank_math_llms_summary_rows            = array(
	array(
		'label' => __( 'Module Enabled', 'divi-lc-kit' ),
		'value' => $dlck_rank_math_llms_enabled ? __( 'Yes', 'divi-lc-kit' ) : __( 'No', 'divi-lc-kit' ),
	),
	array(
		'label' => __( 'Header Name', 'divi-lc-kit' ),
		'value' => $dlck_rank_math_current_org_name !== '' ? $dlck_rank_math_current_org_name : dlck_rank_math_maintenance_not_set_label(),
	),
	array(
		'label' => __( 'Header Description', 'divi-lc-kit' ),
		'value' => $dlck_rank_math_current_description !== '' ? $dlck_rank_math_current_description : dlck_rank_math_maintenance_not_set_label(),
	),
	array(
		'label' => __( 'Sitemap Section', 'divi-lc-kit' ),
		'value' => $dlck_rank_math_sitemap_enabled ? __( 'Included when /llms.txt is generated', 'divi-lc-kit' ) : __( 'Not included because the Rank Math Sitemap module is disabled', 'divi-lc-kit' ),
	),
	array(
		'label' => __( 'Selected Post Types', 'divi-lc-kit' ),
		'value' => dlck_rank_math_maintenance_format_list( $dlck_rank_math_current_llms_post_types ),
	),
	array(
		'label' => __( 'Selected Taxonomies', 'divi-lc-kit' ),
		'value' => dlck_rank_math_maintenance_format_list( $dlck_rank_math_current_llms_taxonomies ),
	),
	array(
		'label' => __( 'Posts / Terms Limit', 'divi-lc-kit' ),
		'value' => (string) $dlck_rank_math_current_llms_limit,
	),
	array(
		'label' => __( 'Additional Content Preview', 'divi-lc-kit' ),
		'value' => $dlck_rank_math_current_llms_extra,
	),
);

?>

<div id="maintenance" class="tool <?php echo $active_tab === 'maintenance' ? 'tool-active' : ''; ?>">

	<div class="toolbox" style="padding:0 0 30px;">
		<div class="info" style="background:transparent;">
			<h4><?php echo esc_html_e( 'What is the maintenance area?', 'divi-lc-kit' ); ?></h4>
			<p><?php echo esc_html_e( 'Recovery and safety toggles to fix cron, caching, and integration issues.', 'divi-lc-kit' ); ?></p>
			<p><?php echo esc_html_e( 'Use sparingly—these change core behaviours to keep a site stable during troubleshooting.', 'divi-lc-kit' ); ?></p>
		</div>
	</div>

		<h2 class="tool-section"><?php echo esc_html_e( 'Updates', 'divi-lc-kit' ); ?></h2>
		<div class="tool-wrap">

			<div class="lc-kit trigger">
				<div class="box-title">
					<h3><?php echo esc_html_e( 'Force Plugin & Theme Update Check', 'divi-lc-kit' ); ?></h3>
					<div class="box-descr">
						<p>
							<?php
							echo esc_html(
								sprintf(
									/* translators: %s: plugins/themes scope label */
									__( 'Run a fresh WordPress.org %s update check immediately instead of waiting for the normal cache window.', 'divi-lc-kit' ),
									$dlck_force_update_scope_label
								)
							);
							?>
						</p>
					</div>
				</div>
				<div class="box-content minibox">
					<?php if ( $dlck_can_force_update_check ) : ?>
						<a class="dlck-settings-button" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=dlck_force_update_check' ), 'dlck_force_update_check' ) ); ?>">
							<?php echo esc_html_e( 'Run Check Now', 'divi-lc-kit' ); ?>
						</a>
					<?php else : ?>
						<button type="button" class="dlck-settings-button" disabled><?php echo esc_html_e( 'Not Available', 'divi-lc-kit' ); ?></button>
					<?php endif; ?>
				</div>
			</div>
			<div class="dlck-hide">
				<div class="lc-kit first nopad">
					<div class="box-title">
					</div>
					<div class="box-content">
						<div class="info">
							<p>
								<?php
								echo esc_html(
									sprintf(
										/* translators: %s: plugins/themes scope label */
										__( 'Useful when you have just released or installed an update and want WordPress to refresh %s availability immediately.', 'divi-lc-kit' ),
										$dlck_force_update_scope_label
									)
								);
								?>
							</p>
							<p><?php echo esc_html_e( 'This refreshes core plugin/theme checks only. Some commercial or custom updaters may keep their own caches.', 'divi-lc-kit' ); ?></p>
							<?php if ( ! $dlck_can_force_update_check ) : ?>
								<p><?php echo esc_html_e( 'This action is only available to users who can update plugins and/or themes on this site.', 'divi-lc-kit' ); ?></p>
							<?php endif; ?>
							<p><a class="button" href="<?php echo esc_url( $dlck_updates_page_url ); ?>"><?php echo esc_html_e( 'Open Updates Page', 'divi-lc-kit' ); ?></a></p>
						</div>
					</div>
				</div>
			</div>

			<div class="lc-kit">
				<div class="box-title">
					<h3><span class="new">top</span><?php echo esc_html_e('Skip New Bundled Themes on Core Updates','divi-lc-kit'); ?></h3>
					<div class="box-descr">
					<p><?php echo esc_html_e( 'Define CORE_UPGRADE_SKIP_NEW_BUNDLED to avoid installing new default themes.', 'divi-lc-kit' ); ?></p>
				</div>
			</div>
			<div class="box-content minibox">
				<div class="checkbox">
					<input name="dlck_core_upgrade_skip_new_bundled" type="checkbox" value="1" <?php checked( '1', $dlck_core_upgrade_skip_new_bundled_val ); ?> />
				</div>
			</div>
		</div>

		<div class="lc-kit">
			<div class="box-title">
				<h3><span class="new">top</span><?php echo esc_html_e('Disable Core Auto Updates','divi-lc-kit'); ?></h3>
				<div class="box-descr">
					<p><?php echo esc_html_e( 'Define WP_AUTO_UPDATE_CORE to false to stop WordPress core auto-updates.', 'divi-lc-kit' ); ?></p>
				</div>
			</div>
			<div class="box-content minibox">
				<div class="checkbox">
					<input name="dlck_wp_auto_update_core" type="checkbox" value="1" <?php checked( '1', $dlck_wp_auto_update_core_val ); ?> />
				</div>
			</div>
		</div>

		<div class="lc-kit">
			<div class="box-title">
				<h3><span class="new">top</span><?php echo esc_html_e('Disable Plugin Auto Updates','divi-lc-kit'); ?></h3>
				<div class="box-descr">
					<p><?php echo esc_html_e( 'Prevent WordPress from automatically updating plugins.', 'divi-lc-kit' ); ?></p>
				</div>
			</div>
			<div class="box-content minibox">
				<div class="checkbox">
					<input name="dlck_disable_plugin_auto_updates" type="checkbox" value="1" <?php checked( '1', $dlck_disable_plugin_auto_updates_val ); ?> />
				</div>
			</div>
		</div>

		<div class="lc-kit">
			<div class="box-title">
				<h3><span class="new">top</span><?php echo esc_html_e('Disable Theme Auto Updates','divi-lc-kit'); ?></h3>
				<div class="box-descr">
					<p><?php echo esc_html_e( 'Prevent WordPress from automatically updating themes.', 'divi-lc-kit' ); ?></p>
				</div>
			</div>
			<div class="box-content minibox">
				<div class="checkbox">
					<input name="dlck_disable_theme_auto_updates" type="checkbox" value="1" <?php checked( '1', $dlck_disable_theme_auto_updates_val ); ?> />
				</div>
			</div>
		</div>

	</div>

	<h2 class="tool-section"><?php echo esc_html_e( 'Admin', 'divi-lc-kit' ); ?></h2>
	<div class="tool-wrap">

		<?php if ( $dlck_divi_theme_active ) : ?>
			<div class="lc-kit trigger">
				<div class="box-title">
					<h3><span class="new">new</span><?php echo esc_html_e( 'Builder-safe mode', 'divi-lc-kit' ); ?></h3>
					<div class="box-descr">
						<p><?php echo esc_html_e( 'Temporarily disable most LC Tweaks runtime hooks while Divi Visual Builder/editor requests are active.', 'divi-lc-kit' ); ?></p>
					</div>
				</div>
				<div class="box-content minibox">
					<div class="checkbox">
						<input name="dlck_builder_safe_mode" type="checkbox" value="1" <?php checked( '1', $dlck_builder_safe_mode_val ); ?> />
					</div>
				</div>
			</div>
			<div class="dlck-hide">
				<div class="lc-kit first nopad">
					<div class="box-title">
					</div>
					<div class="box-content">
						<div class="info">
							<p><?php echo esc_html_e( 'Use this when the Divi builder becomes unstable after enabling multiple tweaks.', 'divi-lc-kit' ); ?></p>
							<p><?php echo esc_html_e( 'Normal frontend behavior remains unchanged; this only gates builder/editor request contexts.', 'divi-lc-kit' ); ?></p>
						</div>
					</div>
				</div>
			</div>
		<?php endif; ?>

			<div class="lc-kit">
				<div class="box-title">
					<h3><span class="new">top</span><?php echo esc_html_e('Hide Dashboard Welcome Panel','divi-lc-kit'); ?></h3>
				<div class="box-descr">
					<p><?php echo esc_html_e( 'Remove the default Welcome panel from the WordPress dashboard.', 'divi-lc-kit' ); ?></p>
				</div>
			</div>
				<div class="box-content minibox">
					<div class="checkbox">
						<input name="dlck_hide_dashboard_welcome_panel" type="checkbox" value="1" <?php checked( '1', $dlck_hide_dashboard_welcome_panel_val ); ?> />
					</div>
				</div>
			</div>

			<div class="lc-kit trigger">
				<div class="box-title">
					<h3><?php echo esc_html_e( 'All WP Settings', 'divi-lc-kit' ); ?></h3>
				<div class="box-descr">
					<p><?php echo esc_html_e( 'WordPress’s raw “all options” settings (the full wp_options list) for debugging.', 'divi-lc-kit' ); ?></p>
				</div>
			</div>
			<div class="box-content minibox">
				<div class="checkbox">
					<input name="dlck_all_wp_settings_page" type="checkbox" value="1" <?php checked( '1', $dlck_all_wp_settings_page_val ); ?> />
				</div>
			</div>
			<a class="dlck-cust-link" href="<?php echo esc_attr( admin_url( 'options.php' ) ); ?>" target="_blank"><?php include DLCK_LC_KIT_PLUGIN_DIR . '/assets/img/gear-icon.php'; ?></a>
		</div>
		<div class="dlck-hide">
			<div class="lc-kit first nopad">
				<div class="box-title">
				</div>
				<div class="box-content">
					<div class="info">
						<h4><?php echo esc_html_e('IMPORTANT NOTE:', 'divi-lc-kit'); ?></h4>
						<p><?php echo esc_html_e( 'This page lets you view/edit any option value directly.', 'divi-lc-kit' ); ?></p>
						<p><?php echo esc_html_e( 'Useful for debugging.', 'divi-lc-kit' ); ?></p>
						<p><?php echo esc_html_e( 'It’s also dangerous: changing the wrong option can break the site (URLs, active plugins, serialized arrays, etc.).', 'divi-lc-kit' ); ?></p>
					</div>
				</div>
			</div>
		</div>

	</div>

	<h2 class="tool-section"><?php echo esc_html_e( 'SEO & Schema', 'divi-lc-kit' ); ?></h2>
	<div class="tool-wrap">

		<div class="lc-kit trigger">
			<div class="box-title">
				<h3><span class="new">new</span><?php echo esc_html_e( 'Enrich Rank Math Schema Graph', 'divi-lc-kit' ); ?></h3>
				<div class="box-descr">
					<p><?php echo esc_html_e( 'Merge additional Organization, LocalBusiness, WebSite, WebPage, and Place fields into Rank Math JSON-LD for richer SEO and LLM context.', 'divi-lc-kit' ); ?></p>
				</div>
			</div>
			<div class="box-content minibox">
				<div class="checkbox">
					<input name="dlck_rank_math_schema_enrichment" type="checkbox" value="1" <?php checked( '1', $dlck_rank_math_schema_enrichment_val ); ?> />
				</div>
			</div>
			<?php if ( $dlck_rank_math_active ) : ?>
				<a class="dlck-cust-link" href="<?php echo esc_attr( $dlck_rank_math_schema_settings_url ); ?>" target="_blank"><?php include DLCK_LC_KIT_PLUGIN_DIR . '/assets/img/gear-icon.php'; ?></a>
			<?php endif; ?>
		</div>
		<div class="dlck-hide">
			<div class="lc-kit first nopad">
				<div class="box-title"></div>
				<div class="box-content dlck-rank-math-schema-panel">
					<div class="info">
						<p><?php echo esc_html_e( 'Requires Rank Math. LC Tweaks extends Rank Math’s existing graph instead of outputting a second competing schema block.', 'divi-lc-kit' ); ?></p>
						<p><?php echo esc_html_e( 'Use the settings link for Rank Math’s own Local SEO Description, Social Profiles, and Additional Organization Info fields like founding date, employee count, website alternate name, and business identifiers. Leave LC Tweaks fields blank to keep Rank Math defaults.', 'divi-lc-kit' ); ?></p>
						<?php if ( ! $dlck_rank_math_active ) : ?>
							<p><?php echo esc_html_e( 'Rank Math is not active on this site. These values can still be saved here, but they will not affect frontend schema until Rank Math is active again.', 'divi-lc-kit' ); ?></p>
						<?php endif; ?>
					</div>

					<div class="info" style="margin-top:15px;">
						<h4><?php echo esc_html_e( 'Current Rank Math Local SEO', 'divi-lc-kit' ); ?></h4>
						<p><?php echo esc_html_e( 'These values are read-only here and come directly from Rank Math. If anything below is wrong, update it in Rank Math. Use the LC Tweaks fields further down only for additional enrichment.', 'divi-lc-kit' ); ?></p>
						<p><?php echo wp_kses_post( sprintf( __( '<strong>Open Rank Math:</strong> <a href="%1$s" target="_blank">Local SEO / Titles settings</a>', 'divi-lc-kit' ), esc_url( $dlck_rank_math_schema_settings_url ) ) ); ?></p>
						<?php dlck_rank_math_maintenance_render_summary_rows( $dlck_rank_math_local_summary_rows ); ?>
					</div>

					<div class="info" style="margin-top:15px;">
						<h4><?php echo esc_html_e( 'Current Rank Math LLMS Txt', 'divi-lc-kit' ); ?></h4>
						<p><?php echo esc_html_e( 'These values are read-only here and come directly from Rank Math. Manage the module, post types, taxonomies, limits, and extra content in Rank Math.', 'divi-lc-kit' ); ?></p>
						<p><?php echo wp_kses_post( sprintf( __( '<strong>Open Rank Math:</strong> <a href="%1$s" target="_blank">LLMS Txt settings</a> | <a href="%2$s" target="_blank">%3$s</a>', 'divi-lc-kit' ), esc_url( $dlck_rank_math_llms_settings_url ), esc_url( $dlck_rank_math_llms_url ), esc_html( $dlck_rank_math_llms_url ) ) ); ?></p>
						<?php dlck_rank_math_maintenance_render_summary_rows( $dlck_rank_math_llms_summary_rows ); ?>
					</div>

						<div class="info" style="margin-top:15px;">
							<h4><?php echo esc_html_e( 'Preview Final Schema', 'divi-lc-kit' ); ?></h4>
							<p><?php echo esc_html_e( 'Fetch a frontend URL on this site and inspect the final emitted JSON-LD after Rank Math and LC Tweaks have both run. Use a relative path or a same-origin full URL.', 'divi-lc-kit' ); ?></p>
							<div class="dlck-rank-math-preview-controls">
								<input type="text" id="dlck_rank_math_schema_preview_url" value="<?php echo esc_attr( home_url( '/' ) ); ?>" style="width:100%;" placeholder="https://example.com/" />
								<input type="text" id="dlck_rank_math_schema_preview_ignore_keys" value="" style="width:100%;" placeholder="Ignore summary keys: description, image, contactPoint" />
								<div class="dlck-rank-math-preview-filter-chips" aria-label="<?php echo esc_attr__( 'Common diff filter keys', 'divi-lc-kit' ); ?>">
									<button type="button" class="button dlck-rank-math-filter-chip" data-key="description">description</button>
									<button type="button" class="button dlck-rank-math-filter-chip" data-key="image">image</button>
									<button type="button" class="button dlck-rank-math-filter-chip" data-key="contactPoint">contactPoint</button>
									<button type="button" class="button dlck-rank-math-filter-chip" data-key="sameAs">sameAs</button>
									<button type="button" class="button dlck-rank-math-filter-chip" data-key="url">url</button>
									<button type="button" class="button dlck-rank-math-filter-chip" data-key="dateModified">dateModified</button>
								</div>
								<div class="dlck-rank-math-preview-actions">
									<button type="button" class="dlck-settings-button" id="dlck_rank_math_schema_preview_run"><?php echo esc_html_e( 'Preview Final JSON-LD', 'divi-lc-kit' ); ?></button>
									<button type="button" class="button" id="dlck_rank_math_schema_preview_copy_diff" style="display:none;"><?php echo esc_html_e( 'Copy Diff Summary', 'divi-lc-kit' ); ?></button>
									<button type="button" class="button" id="dlck_rank_math_schema_preview_copy_report" style="display:none;"><?php echo esc_html_e( 'Copy URL + Diff', 'divi-lc-kit' ); ?></button>
									<button type="button" class="button" id="dlck_rank_math_schema_preview_copy" style="display:none;"><?php echo esc_html_e( 'Copy Output', 'divi-lc-kit' ); ?></button>
								</div>
								<p class="description"><?php echo esc_html_e( 'Optional: ignore comma-separated top-level keys in the diff summary only. The raw JSON-LD output below is never filtered.', 'divi-lc-kit' ); ?></p>
								<p id="dlck_rank_math_schema_preview_status" class="description dlck-rank-math-preview-status"></p>
								<pre id="dlck_rank_math_schema_preview_diff" class="dlck-rank-math-json-preview dlck-rank-math-preview-diff" style="display:none;"></pre>
								<pre id="dlck_rank_math_schema_preview_output" class="dlck-rank-math-json-preview dlck-rank-math-preview-output" style="display:none;"></pre>
							</div>
					</div>

					<p><strong><?php echo esc_html_e( 'Knows About Topics', 'divi-lc-kit' ); ?></strong></p>
					<textarea name="dlck_rank_math_schema_knows_about" rows="4" cols="60" style="width:100%;" placeholder="Industry Expertise&#10;Customer Support&#10;Product Development&#10;Operations"><?php echo esc_textarea( $dlck_rank_math_schema_knows_about_val ); ?></textarea>

					<p><strong><?php echo esc_html_e( 'Areas Served', 'divi-lc-kit' ); ?></strong></p>
					<textarea name="dlck_rank_math_schema_area_served" rows="4" cols="60" style="width:100%;" placeholder="South Africa&#10;United States&#10;Europe&#10;Global"><?php echo esc_textarea( $dlck_rank_math_schema_area_served_val ); ?></textarea>

					<p><strong><?php echo esc_html_e( 'Founders / CEO', 'divi-lc-kit' ); ?></strong></p>
					<textarea name="dlck_rank_math_schema_founders" rows="3" cols="60" style="width:100%;" placeholder="Jane Founder | Founder&#10;John Doe | CEO"><?php echo esc_textarea( $dlck_rank_math_schema_founders_val ); ?></textarea>

					<p><strong><?php echo esc_html_e( 'Employees / Team', 'divi-lc-kit' ); ?></strong></p>
					<textarea name="dlck_rank_math_schema_employees" rows="5" cols="60" style="width:100%;" placeholder="Jane Doe | Senior Designer&#10;John Smith | Lead Developer"><?php echo esc_textarea( $dlck_rank_math_schema_employees_val ); ?></textarea>

					<p><strong><?php echo esc_html_e( 'Contact Languages', 'divi-lc-kit' ); ?></strong></p>
					<textarea name="dlck_rank_math_schema_contact_languages" rows="4" cols="60" style="width:100%;" placeholder="en-ZA&#10;en-GB&#10;en-US"><?php echo esc_textarea( $dlck_rank_math_schema_contact_languages_val ); ?></textarea>
					<p class="description"><?php echo esc_html_e( 'Applies to the default support/contact point built from Rank Math\'s Contact Page. Use the field below for channel-specific contact methods.', 'divi-lc-kit' ); ?></p>

					<p><strong><?php echo esc_html_e( 'Additional Contact Points', 'divi-lc-kit' ); ?></strong></p>
					<textarea name="dlck_rank_math_schema_contact_points" rows="5" cols="60" style="width:100%;" placeholder="Customer Support | support@example.com | +27 11 555 1234 | https://example.com/contact/ | en-ZA,en-GB,en-US&#10;WhatsApp Support |  | +27 82 123 4567 | https://wa.me/27821234567 | en-ZA&#10;Licensing | licensing@example.com |  | https://example.com/licensing/ | en-US"><?php echo esc_textarea( $dlck_rank_math_schema_contact_points_val ); ?></textarea>
					<p class="description"><?php echo esc_html_e( 'Public-facing only. One per line in this format: Contact Type | Email | Telephone | URL | Languages. Use the URL column for WhatsApp, booking, sales, or support links. Use a distinct Contact Type per channel if you want separate schema entries.', 'divi-lc-kit' ); ?></p>

					<p><strong><?php echo esc_html_e( 'Advanced JSON Merge', 'divi-lc-kit' ); ?></strong></p>
					<pre class="dlck-rank-math-json-preview"><?php echo esc_html( $dlck_rank_math_schema_advanced_json_preview ); ?></pre>
					<p class="description"><?php echo esc_html_e( 'Read-only preview of the currently saved advanced merge JSON.', 'divi-lc-kit' ); ?></p>
						<details class="dlck-rank-math-advanced-editor">
							<summary><?php echo esc_html_e( 'Edit Saved Advanced JSON', 'divi-lc-kit' ); ?></summary>
							<p class="description"><?php echo esc_html_e( 'This raw editor is intentionally separate from the main schema fields. Use a top-level JSON object keyed by entity type. Invalid JSON, or top-level arrays, will be rejected on save and the previously saved value will be kept.', 'divi-lc-kit' ); ?></p>
							<textarea name="dlck_rank_math_schema_advanced_json" rows="14" cols="60" style="width:100%;" placeholder="{&#10;  &quot;organization&quot;: {&#10;    &quot;location&quot;: [&#10;      {&#10;        &quot;@type&quot;: &quot;Place&quot;,&#10;        &quot;name&quot;: &quot;Cape Town Office&quot;&#10;      }&#10;    ]&#10;  },&#10;  &quot;localbusiness&quot;: {&#10;    &quot;areaServed&quot;: [&quot;South Africa&quot;, &quot;Europe&quot;]&#10;  },&#10;  &quot;place&quot;: {&#10;    &quot;publicAccess&quot;: true&#10;  },&#10;  &quot;webpage&quot;: {&#10;    &quot;speakable&quot;: {&#10;      &quot;@type&quot;: &quot;SpeakableSpecification&quot;&#10;    }&#10;  }&#10;}"><?php echo esc_textarea( $dlck_rank_math_schema_advanced_json_val ); ?></textarea>
						</details>
				</div>
			</div>
		</div>

	</div>

	<h2 class="tool-section"><?php echo esc_html_e( 'Uploads', 'divi-lc-kit' ); ?></h2>
	<div class="tool-wrap">

		<div class="lc-kit trigger">
			<div class="box-title">
				<h3><?php echo esc_html_e( 'Allow Unfiltered Uploads', 'divi-lc-kit' ); ?></h3>
				<div class="box-descr">
					<p><?php echo esc_html_e( 'Permit admins to upload any file type via capability instead of wp-config.', 'divi-lc-kit' ); ?></p>
				</div>
			</div>
			<div class="box-content minibox">
				<div class="checkbox">
					<input name="dlck_allow_unfiltered_uploads" type="checkbox" value="1" <?php checked( '1', $dlck_allow_unfiltered_uploads_val ); ?> />
				</div>
			</div>
		</div>
		<div class="dlck-hide">
			<div class="lc-kit first nopad">
				<div class="box-title">
				</div>
				<div class="box-content">
					<div class="info">
						<p><?php echo esc_html_e( 'This bypasses WordPress file restrictions for admins. It already allows SVG, JSON, and font uploads, so those toggles are disabled when this is on.', 'divi-lc-kit' ); ?></p>
					</div>
				</div>
			</div>
		</div>

		<div class="lc-kit">
			<div class="box-title">
				<h3><?php echo esc_html_e( 'Allow SVG File Type Uploads', 'divi-lc-kit' ); ?></h3>
				<div class="box-descr">
					<p><?php echo esc_html_e( 'Add SVG support in the WordPress Media Library.', 'divi-lc-kit' ); ?></p>
				</div>
			</div>
			<div class="box-content minibox">
				<div class="checkbox">
					<input name="dlck_svg_uploads" type="checkbox" value="1" <?php checked( '1', $dlck_svg_uploads_val ); ?> />
				</div>
			</div>
		</div>

		<div class="lc-kit">
			<div class="box-title">
				<h3><?php echo esc_html_e( 'Allow JSON File Uploads (Lottie)', 'divi-lc-kit' ); ?></h3>
				<div class="box-descr">
					<p><?php echo esc_html_e( 'Add JSON support in the WordPress Media Library for Lottie animations.', 'divi-lc-kit' ); ?></p>
				</div>
			</div>
			<div class="box-content minibox">
				<div class="checkbox">
					<input name="dlck_json_uploads" type="checkbox" value="1" <?php checked( '1', $dlck_json_uploads_val ); ?> />
				</div>
			</div>
		</div>

			<div class="lc-kit">
				<div class="box-title">
					<h3><?php echo esc_html_e( 'Allow All Font Files Uploads', 'divi-lc-kit' ); ?></h3>
				<div class="box-descr">
					<p><?php echo esc_html_e( 'Add TTF, OTF, WOFF, and WOFF2 support in WordPress.', 'divi-lc-kit' ); ?></p>
				</div>
			</div>
			<div class="box-content minibox">
				<div class="checkbox">
					<input name="dlck_ttf_uploads" type="checkbox" value="1" <?php checked( '1', $dlck_ttf_uploads_val ); ?> />
				</div>
				</div>
			</div>

			<div class="lc-kit trigger">
				<div class="box-title">
					<h3><?php echo esc_html_e( 'Replace Image Tool', 'divi-lc-kit' ); ?></h3>
					<div class="box-descr">
						<p><?php echo esc_html_e( 'Adds a Replace Image button to image Attachment Details so you can swap the file while keeping the same attachment ID and URL.', 'divi-lc-kit' ); ?></p>
					</div>
				</div>
				<div class="box-content minibox">
					<div class="checkbox">
						<input name="dlck_replace_image_tool" type="checkbox" value="1" <?php checked( '1', $dlck_replace_image_tool_val ); ?> />
					</div>
				</div>
			</div>
			<div class="dlck-hide">
				<div class="lc-kit first nopad">
					<div class="box-title">
					</div>
					<div class="box-content">
						<div class="info">
							<p><?php echo esc_html_e( 'After enabling, open an image in the Media Library and use the new Replace Image button in Attachment Details.', 'divi-lc-kit' ); ?></p>
							<p><?php echo esc_html_e( 'Disable browser and plugin caching while testing; old thumbnails can appear cached even when the replacement succeeded.', 'divi-lc-kit' ); ?></p>
							<p><?php echo esc_html_e( 'Only image attachments are supported. The replacement image is copied over the existing file and new sizes are regenerated for the original attachment.', 'divi-lc-kit' ); ?></p>
							<p><a class="button" href="<?php echo esc_url( admin_url( 'upload.php' ) ); ?>"><?php echo esc_html_e( 'Open Media Library', 'divi-lc-kit' ); ?></a></p>
						</div>
					</div>
				</div>
			</div>

		</div>

	<h2 class="tool-section"><?php echo esc_html_e( 'Caching & Integrations', 'divi-lc-kit' ); ?></h2>
	<div class="tool-wrap">

		<div class="lc-kit">
			<div class="box-title">
				<h3><?php echo esc_html_e( 'WP Rocket - Force Page Caching', 'divi-lc-kit' ); ?></h3>
				<div class="box-descr">
					<p><?php echo esc_html_e( 'Use when other plugins/themes set DONOTCACHEPAGE but you still want caching.', 'divi-lc-kit' ); ?></p>
					</div>
				</div>
				<div class="box-content minibox">
					<div class="checkbox">
						<input name="dlck_wprocket_force_page_caching" type="checkbox" value="1" <?php checked( '1', $dlck_wprocket_force_page_caching_val ); ?> />
					</div>
				</div>
		</div>

		<div class="lc-kit">
			<div class="box-title">
				<h3><?php echo esc_html_e( 'WP Rocket - Cache WP REST API', 'divi-lc-kit' ); ?></h3>
				<div class="box-descr">
					<p><?php echo esc_html_e( 'Cache REST API responses with WP Rocket for better performance.', 'divi-lc-kit' ); ?></p>
				</div>
			</div>
			<div class="box-content minibox">
				<div class="checkbox">
					<input name="dlck_wprocket_cache_wp_rest_api" type="checkbox" value="1" <?php checked( '1', $dlck_wprocket_cache_wp_rest_api_val ); ?> />
				</div>
			</div>
		</div>

		<div class="lc-kit">
			<div class="box-title">
				<h3><?php echo esc_html_e( 'WP Rocket - Disable Above The Fold Optimization', 'divi-lc-kit' ); ?></h3>
				<div class="box-descr">
					<p><?php echo esc_html_e( 'Treats “above-the-fold optimisation” as off, even if it’s enabled in the UI.', 'divi-lc-kit' ); ?></p>
				</div>
			</div>
			<div class="box-content minibox">
				<div class="checkbox">
					<input name="dlck_wprocket_disable_above_fold_opt" type="checkbox" value="1" <?php checked( '1', $dlck_wprocket_disable_above_fold_opt_val ); ?> />
				</div>
			</div>
		</div>

		<div class="lc-kit">
			<div class="box-title">
				<h3><?php echo esc_html_e( 'WP Rocket - Disable Priority Elements', 'divi-lc-kit' ); ?></h3>
				<div class="box-descr">
					<p><?php echo esc_html_e( 'Disable Priority Elements optimizations (lazy render, critical images, and preconnect).', 'divi-lc-kit' ); ?></p>
				</div>
			</div>
			<div class="box-content minibox">
				<div class="checkbox">
					<input name="dlck_wprocket_disable_priority_elements" type="checkbox" value="1" <?php checked( '1', $dlck_wprocket_disable_priority_elements_val ); ?> />
				</div>
			</div>
		</div>

		<div class="lc-kit">
			<div class="box-title">
				<h3><?php echo esc_html_e( 'Disable SSL cURL Error 60 in \"WP All Import\"', 'divi-lc-kit' ); ?></h3>
				<div class="box-descr">
					<p><?php echo esc_html_e( 'Disable peer verification to temporarily resolve error 60.', 'divi-lc-kit' ); ?></p>
				</div>
			</div>
			<div class="box-content minibox">
				<div class="checkbox">
					<input name="dlck_disable_ssl_curl_error_60_in_wpallimport" type="checkbox" value="1" <?php checked( '1', $dlck_disable_ssl_curl_error_60_in_wpallimport_val ); ?> />
				</div>
			</div>
		</div>

		<div class="lc-kit">
			<div class="box-title">
				<h3><?php echo esc_html_e( 'ExactDN - Image Downsize Scale', 'divi-lc-kit' ); ?></h3>
				<div class="box-descr">
					<p><?php echo esc_html_e( 'Prefer scaling (keeps the whole image, maintains aspect ratio) instead of cropping.', 'divi-lc-kit' ); ?></p>
				</div>
			</div>
			<div class="box-content minibox">
				<div class="checkbox">
					<input name="dlck_exactdn_image_downsize_scale" type="checkbox" value="1" <?php checked( '1', $dlck_exactdn_image_downsize_scale_val ); ?> />
				</div>
			</div>
		</div>
		<div class="dlck-hide">
			<div class="lc-kit first nopad">
				<div class="box-title">
				</div>
				<div class="box-content">
					<div class="info">
						<p><?php echo esc_html_e( 'Fewer “unexpected crops” on thumbnails/featured images generated via theme/page-builder code that passes hard dimensions.', 'divi-lc-kit' ); ?></p>
						<p><?php echo esc_html_e( 'You may see images not matching the exact box ratio (unless the layout enforces cropping via CSS).', 'divi-lc-kit' ); ?></p>
					</div>
				</div>
			</div>
		</div>

	</div>

	<h2 class="tool-section"><?php echo esc_html_e( 'Scheduling & Cron', 'divi-lc-kit' ); ?></h2>
	<div class="tool-wrap">

		<div class="lc-kit">
			<div class="box-title">
				<h3><?php echo esc_html_e( 'Kill Jetpack Cron', 'divi-lc-kit' ); ?></h3>
				<div class="box-descr">
					<p><?php echo esc_html_e( 'Clear existing Jetpack cron events and block new ones from being scheduled.', 'divi-lc-kit' ); ?></p>
				</div>
			</div>
			<div class="box-content minibox">
				<div class="checkbox">
					<input name="dlck_kill_jetpack_cron" type="checkbox" value="1" <?php checked( '1', $dlck_kill_jetpack_cron_val ); ?> />
				</div>
			</div>
		</div>

		<div class="lc-kit">
			<div class="box-title">
				<h3><?php echo esc_html_e( 'Speed Up Scheduled Actions', 'divi-lc-kit' ); ?></h3>
				<div class="box-descr">
					<p><?php echo esc_html_e( 'Increase Action Scheduler throughput and trim retention to reduce overhead.', 'divi-lc-kit' ); ?></p>
				</div>
			</div>
			<div class="box-content minibox">
				<div class="checkbox">
					<input name="dlck_speedup_scheduled_actions" type="checkbox" value="1" <?php checked( '1', $dlck_speedup_scheduled_actions_val ); ?> />
				</div>
			</div>
		</div>

	</div>

</div>
