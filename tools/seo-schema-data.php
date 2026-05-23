<?php

$dlck_settings_snapshot = function_exists( 'dlck_get_settings_snapshot' ) ? dlck_get_settings_snapshot() : array();
$dlck_setting = static function ( string $key, $default = '' ) use ( $dlck_settings_snapshot ) {
	if ( array_key_exists( $key, $dlck_settings_snapshot ) ) {
		$value = $dlck_settings_snapshot[ $key ];
		return ( $value === '' && $default !== '' ) ? $default : $value;
	}
	return $default;
};

if ( ! function_exists( 'dlck_rank_math_seo_schema_not_set_label' ) ) {
	function dlck_rank_math_seo_schema_not_set_label(): string {
		return __( 'Not set in Rank Math', 'lc-tweaks' );
	}
}

if ( ! function_exists( 'dlck_rank_math_seo_schema_normalize_scalar' ) ) {
	function dlck_rank_math_seo_schema_normalize_scalar( $value ): string {
		if ( ! is_scalar( $value ) ) {
			return '';
		}

		return trim( wp_strip_all_tags( (string) $value ) );
	}
}

if ( ! function_exists( 'dlck_rank_math_seo_schema_humanize_key' ) ) {
	function dlck_rank_math_seo_schema_humanize_key( string $value ): string {
		$value = preg_replace( '/(?<!^)([A-Z])/', ' $1', $value );
		$value = str_replace( array( '-', '_' ), ' ', $value );

		return ucwords( trim( $value ) );
	}
}

if ( ! function_exists( 'dlck_rank_math_seo_schema_format_list' ) ) {
	function dlck_rank_math_seo_schema_format_list( array $items, string $separator = '; ' ): string {
		$items = array_values(
			array_filter(
				array_map(
					static function ( $item ) {
						return dlck_rank_math_seo_schema_normalize_scalar( $item );
					},
					$items
				)
			)
		);

		return ! empty( $items ) ? implode( $separator, $items ) : dlck_rank_math_seo_schema_not_set_label();
	}
}

if ( ! function_exists( 'dlck_rank_math_seo_schema_format_address' ) ) {
	function dlck_rank_math_seo_schema_format_address( $address ): string {
		if ( ! is_array( $address ) ) {
			return dlck_rank_math_seo_schema_not_set_label();
		}

		$parts = array();
		foreach ( array( 'streetAddress', 'addressLocality', 'addressRegion', 'postalCode', 'addressCountry' ) as $key ) {
			if ( empty( $address[ $key ] ) ) {
				continue;
			}

			$parts[] = dlck_rank_math_seo_schema_normalize_scalar( $address[ $key ] );
		}

		return dlck_rank_math_seo_schema_format_list( $parts, ', ' );
	}
}

if ( ! function_exists( 'dlck_rank_math_seo_schema_format_page' ) ) {
	function dlck_rank_math_seo_schema_format_page( $page_id ): string {
		$page_id = absint( $page_id );
		if ( ! $page_id ) {
			return dlck_rank_math_seo_schema_not_set_label();
		}

		$title = wp_strip_all_tags( get_the_title( $page_id ) );
		$url   = get_permalink( $page_id );

		if ( $title === '' ) {
			$title = sprintf( __( 'Page #%d', 'lc-tweaks' ), $page_id );
		}

		return $url ? $title . ' (' . esc_url_raw( $url ) . ')' : $title;
	}
}

if ( ! function_exists( 'dlck_rank_math_seo_schema_format_preview' ) ) {
	function dlck_rank_math_seo_schema_format_preview( $value ): string {
		$value = dlck_rank_math_seo_schema_normalize_scalar( preg_replace( '/\s+/', ' ', (string) $value ) );
		if ( $value === '' ) {
			return dlck_rank_math_seo_schema_not_set_label();
		}

		return wp_trim_words( $value, 30, '...' );
	}
}

if ( ! function_exists( 'dlck_rank_math_seo_schema_format_json_preview' ) ) {
	function dlck_rank_math_seo_schema_format_json_preview( $value ): string {
		$value = trim( (string) $value );
		if ( $value === '' ) {
			return __( 'No advanced JSON saved in LC Tweaks.', 'lc-tweaks' );
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

if ( ! function_exists( 'dlck_rank_math_seo_schema_render_summary_rows' ) ) {
	function dlck_rank_math_seo_schema_render_summary_rows( array $rows ): void {
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

if ( ! function_exists( 'dlck_agent_readiness_seo_schema_signal_value' ) ) {
	function dlck_agent_readiness_seo_schema_signal_value( $value, string $default ): string {
		$value = sanitize_key( is_scalar( $value ) ? (string) $value : '' );

		return in_array( $value, array( 'yes', 'no', 'unset' ), true ) ? $value : $default;
	}
}

if ( ! function_exists( 'dlck_agent_readiness_seo_schema_signal_string' ) ) {
	function dlck_agent_readiness_seo_schema_signal_string( array $signals ): string {
		$parts = array();
		foreach ( $signals as $name => $value ) {
			if ( $value === 'unset' ) {
				continue;
			}

			$parts[] = $name . '=' . $value;
		}

		return implode( ', ', $parts );
	}
}

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
$dlck_rank_math_current_graph_type       = $dlck_rank_math_current_graph_type_key === 'company' ? __( 'Organization', 'lc-tweaks' ) : __( 'Person', 'lc-tweaks' );
$dlck_rank_math_current_org_name         = dlck_rank_math_seo_schema_normalize_scalar( $dlck_rank_math_titles_settings['knowledgegraph_name'] ?? '' );
$dlck_rank_math_current_website_name     = dlck_rank_math_seo_schema_normalize_scalar( $dlck_rank_math_titles_settings['website_name'] ?? '' );
$dlck_rank_math_current_website_alt_name = dlck_rank_math_seo_schema_normalize_scalar( $dlck_rank_math_titles_settings['website_alternate_name'] ?? '' );
$dlck_rank_math_current_description      = dlck_rank_math_seo_schema_normalize_scalar( $dlck_rank_math_titles_settings['organization_description'] ?? '' );
$dlck_rank_math_current_url              = dlck_rank_math_seo_schema_normalize_scalar( $dlck_rank_math_titles_settings['url'] ?? '' );
$dlck_rank_math_current_email            = dlck_rank_math_seo_schema_normalize_scalar( $dlck_rank_math_titles_settings['email'] ?? '' );
$dlck_rank_math_current_logo             = dlck_rank_math_seo_schema_normalize_scalar( $dlck_rank_math_titles_settings['knowledgegraph_logo'] ?? '' );
$dlck_rank_math_current_address          = dlck_rank_math_seo_schema_format_address( $dlck_rank_math_titles_settings['local_address'] ?? array() );
$dlck_rank_math_current_business_type    = '';
$dlck_rank_math_current_geo              = dlck_rank_math_seo_schema_normalize_scalar( $dlck_rank_math_titles_settings['geo'] ?? '' );
$dlck_rank_math_current_price_range      = dlck_rank_math_seo_schema_normalize_scalar( $dlck_rank_math_titles_settings['price_range'] ?? '' );
$dlck_rank_math_current_about_page       = dlck_rank_math_seo_schema_format_page( $dlck_rank_math_titles_settings['local_seo_about_page'] ?? 0 );
$dlck_rank_math_current_contact_page     = dlck_rank_math_seo_schema_format_page( $dlck_rank_math_titles_settings['local_seo_contact_page'] ?? 0 );
$dlck_rank_math_current_contact_numbers  = array();
$dlck_rank_math_current_opening_hours    = array();
$dlck_rank_math_current_additional_info  = array();
$dlck_rank_math_current_social_profiles  = array();
$dlck_rank_math_current_llms_post_types  = array();
$dlck_rank_math_current_llms_taxonomies  = array();
$dlck_rank_math_current_llms_limit       = absint( $dlck_rank_math_general_settings['llms_limit'] ?? 100 );
$dlck_rank_math_current_llms_extra       = dlck_rank_math_seo_schema_format_preview( $dlck_rank_math_general_settings['llms_extra_content'] ?? '' );

if ( $dlck_rank_math_current_org_name === '' ) {
	$dlck_rank_math_current_org_name = get_bloginfo( 'name' );
}

if ( $dlck_rank_math_current_website_name === '' ) {
	$dlck_rank_math_current_website_name = $dlck_rank_math_current_org_name;
}

if ( $dlck_rank_math_current_description === '' ) {
	$dlck_rank_math_current_description = dlck_rank_math_seo_schema_normalize_scalar( get_bloginfo( 'description' ) );
}

if ( $dlck_rank_math_current_url === '' ) {
	$dlck_rank_math_current_url = home_url( '/' );
}

if ( $dlck_rank_math_current_llms_limit < 1 ) {
	$dlck_rank_math_current_llms_limit = 100;
}

if ( $dlck_rank_math_current_logo === '' && ! empty( $dlck_rank_math_titles_settings['knowledgegraph_logo_id'] ) ) {
	$dlck_rank_math_current_logo = dlck_rank_math_seo_schema_normalize_scalar( wp_get_attachment_url( absint( $dlck_rank_math_titles_settings['knowledgegraph_logo_id'] ) ) );
}

if ( ! empty( $dlck_rank_math_titles_settings['local_business_type'] ) ) {
	$dlck_rank_math_business_type_key  = dlck_rank_math_seo_schema_normalize_scalar( $dlck_rank_math_titles_settings['local_business_type'] );
	$dlck_rank_math_current_business_type = $dlck_rank_math_business_type_labels[ $dlck_rank_math_business_type_key ] ?? dlck_rank_math_seo_schema_humanize_key( $dlck_rank_math_business_type_key );
}

if ( ! empty( $dlck_rank_math_titles_settings['phone'] ) ) {
	$dlck_rank_math_current_contact_numbers[] = dlck_rank_math_seo_schema_normalize_scalar( $dlck_rank_math_titles_settings['phone'] );
}

if ( ! empty( $dlck_rank_math_titles_settings['phone_numbers'] ) && is_array( $dlck_rank_math_titles_settings['phone_numbers'] ) ) {
	foreach ( $dlck_rank_math_titles_settings['phone_numbers'] as $phone_entry ) {
		if ( ! is_array( $phone_entry ) || empty( $phone_entry['number'] ) ) {
			continue;
		}

		$phone_number = dlck_rank_math_seo_schema_normalize_scalar( $phone_entry['number'] );
		$phone_type   = dlck_rank_math_seo_schema_normalize_scalar( $phone_entry['type'] ?? '' );
		$phone_label  = $dlck_rank_math_phone_type_labels[ $phone_type ] ?? '';
		if ( $phone_label === '' && $phone_type !== '' ) {
			$phone_label = dlck_rank_math_seo_schema_humanize_key( $phone_type );
		}

		$dlck_rank_math_current_contact_numbers[] = $phone_label !== '' ? $phone_label . ': ' . $phone_number : $phone_number;
	}
}

if ( ! empty( $dlck_rank_math_titles_settings['opening_hours'] ) && is_array( $dlck_rank_math_titles_settings['opening_hours'] ) ) {
	foreach ( $dlck_rank_math_titles_settings['opening_hours'] as $opening_hour ) {
		if ( ! is_array( $opening_hour ) ) {
			continue;
		}

		$day  = dlck_rank_math_seo_schema_normalize_scalar( $opening_hour['day'] ?? '' );
		$time = dlck_rank_math_seo_schema_normalize_scalar( $opening_hour['time'] ?? '' );
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

		$property_type  = dlck_rank_math_seo_schema_normalize_scalar( $property['type'] ?? '' );
		$property_label = $dlck_rank_math_additional_info_labels[ $property_type ] ?? '';
		if ( $property_label === '' && $property_type !== '' ) {
			$property_label = dlck_rank_math_seo_schema_humanize_key( $property_type );
		}

		$property_value = dlck_rank_math_seo_schema_normalize_scalar( $property['value'] );
		$dlck_rank_math_current_additional_info[] = $property_label !== '' ? $property_label . ': ' . $property_value : $property_value;
	}
}

if ( ! empty( $dlck_rank_math_titles_settings['social_url_facebook'] ) ) {
	$dlck_rank_math_current_social_profiles[] = dlck_rank_math_seo_schema_normalize_scalar( $dlck_rank_math_titles_settings['social_url_facebook'] );
}

if ( ! empty( $dlck_rank_math_titles_settings['twitter_author_names'] ) ) {
	$twitter_handle = ltrim( dlck_rank_math_seo_schema_normalize_scalar( $dlck_rank_math_titles_settings['twitter_author_names'] ), '@' );
	if ( $twitter_handle !== '' ) {
		$dlck_rank_math_current_social_profiles[] = 'https://twitter.com/' . $twitter_handle;
	}
}

if ( ! empty( $dlck_rank_math_titles_settings['social_additional_profiles'] ) ) {
	$additional_profiles = preg_split( '/\r\n|\r|\n/', (string) $dlck_rank_math_titles_settings['social_additional_profiles'] );
	if ( is_array( $additional_profiles ) ) {
		foreach ( $additional_profiles as $profile_url ) {
			$profile_url = dlck_rank_math_seo_schema_normalize_scalar( $profile_url );
			if ( $profile_url === '' ) {
				continue;
			}

			$dlck_rank_math_current_social_profiles[] = $profile_url;
		}
	}
}

if ( ! empty( $dlck_rank_math_general_settings['llms_post_types'] ) && is_array( $dlck_rank_math_general_settings['llms_post_types'] ) ) {
	foreach ( $dlck_rank_math_general_settings['llms_post_types'] as $post_type ) {
		$post_type = dlck_rank_math_seo_schema_normalize_scalar( $post_type );
		if ( $post_type === '' ) {
			continue;
		}

		$post_type_object = get_post_type_object( $post_type );
		$dlck_rank_math_current_llms_post_types[] = ( $post_type_object && ! empty( $post_type_object->labels->name ) ) ? $post_type_object->labels->name : $post_type;
	}
}

if ( ! empty( $dlck_rank_math_general_settings['llms_taxonomies'] ) && is_array( $dlck_rank_math_general_settings['llms_taxonomies'] ) ) {
	foreach ( $dlck_rank_math_general_settings['llms_taxonomies'] as $taxonomy ) {
		$taxonomy = dlck_rank_math_seo_schema_normalize_scalar( $taxonomy );
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
$dlck_rank_math_schema_advanced_json_preview = dlck_rank_math_seo_schema_format_json_preview( $dlck_rank_math_schema_advanced_json_val );
$dlck_rank_math_local_summary_rows           = array(
	array(
		'label' => __( 'Person or Company', 'lc-tweaks' ),
		'value' => $dlck_rank_math_current_graph_type,
	),
	array(
		'label' => __( 'Website Name', 'lc-tweaks' ),
		'value' => $dlck_rank_math_current_website_name !== '' ? $dlck_rank_math_current_website_name : dlck_rank_math_seo_schema_not_set_label(),
	),
	array(
		'label' => __( 'Website Alternate Name', 'lc-tweaks' ),
		'value' => $dlck_rank_math_current_website_alt_name !== '' ? $dlck_rank_math_current_website_alt_name : dlck_rank_math_seo_schema_not_set_label(),
	),
	array(
		'label' => __( 'Person/Organization Name', 'lc-tweaks' ),
		'value' => $dlck_rank_math_current_org_name !== '' ? $dlck_rank_math_current_org_name : dlck_rank_math_seo_schema_not_set_label(),
	),
	array(
		'label' => __( 'Description', 'lc-tweaks' ),
		'value' => $dlck_rank_math_current_description !== '' ? $dlck_rank_math_current_description : dlck_rank_math_seo_schema_not_set_label(),
	),
	array(
		'label' => __( 'URL', 'lc-tweaks' ),
		'value' => $dlck_rank_math_current_url !== '' ? $dlck_rank_math_current_url : dlck_rank_math_seo_schema_not_set_label(),
	),
	array(
		'label' => __( 'Email', 'lc-tweaks' ),
		'value' => $dlck_rank_math_current_email !== '' ? $dlck_rank_math_current_email : dlck_rank_math_seo_schema_not_set_label(),
	),
	array(
		'label' => __( 'Current Logo', 'lc-tweaks' ),
		'value' => $dlck_rank_math_current_logo !== '' ? $dlck_rank_math_current_logo : dlck_rank_math_seo_schema_not_set_label(),
	),
	array(
		'label' => __( 'Phone / Contact Numbers', 'lc-tweaks' ),
		'value' => dlck_rank_math_seo_schema_format_list( $dlck_rank_math_current_contact_numbers ),
	),
	array(
		'label' => __( 'Address', 'lc-tweaks' ),
		'value' => $dlck_rank_math_current_address,
	),
	array(
		'label' => __( 'Business Type', 'lc-tweaks' ),
		'value' => $dlck_rank_math_current_business_type !== '' ? $dlck_rank_math_current_business_type : dlck_rank_math_seo_schema_not_set_label(),
	),
	array(
		'label' => __( 'Geo Coordinates', 'lc-tweaks' ),
		'value' => $dlck_rank_math_current_geo !== '' ? $dlck_rank_math_current_geo : dlck_rank_math_seo_schema_not_set_label(),
	),
	array(
		'label' => __( 'Opening Hours', 'lc-tweaks' ),
		'value' => dlck_rank_math_seo_schema_format_list( $dlck_rank_math_current_opening_hours ),
	),
	array(
		'label' => __( 'Price Range', 'lc-tweaks' ),
		'value' => $dlck_rank_math_current_price_range !== '' ? $dlck_rank_math_current_price_range : dlck_rank_math_seo_schema_not_set_label(),
	),
	array(
		'label' => __( 'Additional Organization Info', 'lc-tweaks' ),
		'value' => dlck_rank_math_seo_schema_format_list( $dlck_rank_math_current_additional_info ),
	),
	array(
		'label' => __( 'Social Profiles (sameAs)', 'lc-tweaks' ),
		'value' => dlck_rank_math_seo_schema_format_list( $dlck_rank_math_current_social_profiles ),
	),
	array(
		'label' => __( 'About Page', 'lc-tweaks' ),
		'value' => $dlck_rank_math_current_about_page,
	),
	array(
		'label' => __( 'Contact Page', 'lc-tweaks' ),
		'value' => $dlck_rank_math_current_contact_page,
	),
);
$dlck_rank_math_llms_summary_rows            = array(
	array(
		'label' => __( 'Module Enabled', 'lc-tweaks' ),
		'value' => $dlck_rank_math_llms_enabled ? __( 'Yes', 'lc-tweaks' ) : __( 'No', 'lc-tweaks' ),
	),
	array(
		'label' => __( 'Header Name', 'lc-tweaks' ),
		'value' => $dlck_rank_math_current_org_name !== '' ? $dlck_rank_math_current_org_name : dlck_rank_math_seo_schema_not_set_label(),
	),
	array(
		'label' => __( 'Header Description', 'lc-tweaks' ),
		'value' => $dlck_rank_math_current_description !== '' ? $dlck_rank_math_current_description : dlck_rank_math_seo_schema_not_set_label(),
	),
	array(
		'label' => __( 'Sitemap Section', 'lc-tweaks' ),
		'value' => $dlck_rank_math_sitemap_enabled ? __( 'Included when /llms.txt is generated', 'lc-tweaks' ) : __( 'Not included because the Rank Math Sitemap module is disabled', 'lc-tweaks' ),
	),
	array(
		'label' => __( 'Selected Post Types', 'lc-tweaks' ),
		'value' => dlck_rank_math_seo_schema_format_list( $dlck_rank_math_current_llms_post_types ),
	),
	array(
		'label' => __( 'Selected Taxonomies', 'lc-tweaks' ),
		'value' => dlck_rank_math_seo_schema_format_list( $dlck_rank_math_current_llms_taxonomies ),
	),
	array(
		'label' => __( 'Posts / Terms Limit', 'lc-tweaks' ),
		'value' => (string) $dlck_rank_math_current_llms_limit,
	),
	array(
		'label' => __( 'Additional Content Preview', 'lc-tweaks' ),
		'value' => $dlck_rank_math_current_llms_extra,
	),
);

$dlck_agent_readiness_enabled_val           = $dlck_setting( 'dlck_agent_readiness_enabled' );
$dlck_agent_readiness_markdown_accept_val   = $dlck_setting( 'dlck_agent_readiness_markdown_accept', '1' );
$dlck_agent_readiness_index_md_val          = $dlck_setting( 'dlck_agent_readiness_index_md', '1' );
$dlck_agent_readiness_exclude_woo_val       = $dlck_setting( 'dlck_agent_readiness_exclude_woo', '0' );
$dlck_agent_readiness_woo_markdown_val      = $dlck_setting( 'dlck_agent_readiness_woo_markdown', '1' );
$dlck_agent_readiness_robots_signals_val    = $dlck_setting( 'dlck_agent_readiness_robots_signals', '1' );
$dlck_agent_readiness_discovery_headers_val = $dlck_setting( 'dlck_agent_readiness_discovery_headers', '1' );
$dlck_agent_readiness_llms_enrichment_val   = $dlck_setting( 'dlck_agent_readiness_llms_enrichment', '1' );
$dlck_agent_readiness_signal_search_val     = dlck_agent_readiness_seo_schema_signal_value( $dlck_setting( 'dlck_agent_readiness_signal_search', 'yes' ), 'yes' );
$dlck_agent_readiness_signal_ai_input_val   = dlck_agent_readiness_seo_schema_signal_value( $dlck_setting( 'dlck_agent_readiness_signal_ai_input', 'yes' ), 'yes' );
$dlck_agent_readiness_signal_ai_train_val   = dlck_agent_readiness_seo_schema_signal_value( $dlck_setting( 'dlck_agent_readiness_signal_ai_train', 'no' ), 'no' );
$dlck_agent_readiness_content_signal        = dlck_agent_readiness_seo_schema_signal_string(
	array(
		'search'   => $dlck_agent_readiness_signal_search_val,
		'ai-input' => $dlck_agent_readiness_signal_ai_input_val,
		'ai-train' => $dlck_agent_readiness_signal_ai_train_val,
	)
);
$dlck_agent_readiness_sitemap_url           = $dlck_rank_math_sitemap_enabled ? home_url( '/sitemap_index.xml' ) : ( function_exists( 'wp_sitemaps_get_server' ) && get_option( 'blog_public' ) ? home_url( '/wp-sitemap.xml' ) : '' );
$dlck_agent_readiness_physical_robots       = file_exists( ABSPATH . 'robots.txt' );
$dlck_agent_readiness_cloudflare_request    = ! empty( $_SERVER['HTTP_CF_RAY'] ) || ! empty( $_SERVER['HTTP_CF_CONNECTING_IP'] ) || ! empty( $_SERVER['HTTP_CF_VISITOR'] );
$dlck_agent_readiness_pretty_permalinks     = get_option( 'permalink_structure' ) !== '';
$dlck_agent_readiness_woo_active            = class_exists( 'WooCommerce' ) || function_exists( 'WC' ) || function_exists( 'wc_get_page_id' );
$dlck_agent_readiness_home_url              = home_url( '/' );
$dlck_agent_readiness_index_md_url          = home_url( '/index.md' );
$dlck_agent_readiness_robots_url            = home_url( '/robots.txt' );
$dlck_agent_readiness_link_header_command   = 'curl -I ' . $dlck_agent_readiness_home_url . ' | grep -i "^link:"';
$dlck_agent_readiness_htaccess_status       = function_exists( 'dlck_agent_readiness_get_htaccess_status' ) ? dlck_agent_readiness_get_htaccess_status() : array();
$dlck_agent_readiness_htaccess_snippet      = isset( $dlck_agent_readiness_htaccess_status['snippet'] ) ? (string) $dlck_agent_readiness_htaccess_status['snippet'] : '';
$dlck_agent_readiness_htaccess_installed    = ! empty( $dlck_agent_readiness_htaccess_status['installed'] );
$dlck_agent_readiness_htaccess_writable     = ! empty( $dlck_agent_readiness_htaccess_status['writable'] );
$dlck_agent_readiness_htaccess_path         = isset( $dlck_agent_readiness_htaccess_status['path'] ) ? (string) $dlck_agent_readiness_htaccess_status['path'] : ABSPATH . '.htaccess';
$dlck_agent_readiness_htaccess_install_url  = wp_nonce_url( admin_url( 'admin-post.php?action=dlck_agent_readiness_install_htaccess' ), 'dlck_agent_readiness_install_htaccess' );
$dlck_agent_readiness_htaccess_remove_url   = wp_nonce_url( admin_url( 'admin-post.php?action=dlck_agent_readiness_remove_htaccess' ), 'dlck_agent_readiness_remove_htaccess' );
$dlck_agent_readiness_htaccess_label        = $dlck_agent_readiness_htaccess_installed
	? __( 'Installed in .htaccess. Purge page/CDN caches after updates.', 'lc-tweaks' )
	: ( $dlck_agent_readiness_htaccess_writable ? __( 'Not installed. LC Tweaks can add it because .htaccess is writable.', 'lc-tweaks' ) : __( 'Not installed. Copy the snippet manually because .htaccess is not writable.', 'lc-tweaks' ) );
$dlck_agent_readiness_woo_label             = __( 'WooCommerce is not active.', 'lc-tweaks' );
if ( $dlck_agent_readiness_woo_active ) {
	$dlck_agent_readiness_woo_label = $dlck_agent_readiness_exclude_woo_val === '1'
		? __( 'Enabled. Products and configured WooCommerce pages are excluded from Markdown and discovery links.', 'lc-tweaks' )
		: __( 'WooCommerce is active; products and store pages are currently eligible unless this exclusion is enabled.', 'lc-tweaks' );
}
$dlck_agent_readiness_woo_markdown_label    = __( 'WooCommerce is not active.', 'lc-tweaks' );
if ( $dlck_agent_readiness_woo_active ) {
	if ( $dlck_agent_readiness_exclude_woo_val === '1' ) {
		$dlck_agent_readiness_woo_markdown_label = __( 'Not applied while WooCommerce exclusions are enabled.', 'lc-tweaks' );
	} elseif ( $dlck_agent_readiness_woo_markdown_val === '1' ) {
		$dlck_agent_readiness_woo_markdown_label = __( 'Enabled. Included product and shop pages get product/catalog Markdown.', 'lc-tweaks' );
	} else {
		$dlck_agent_readiness_woo_markdown_label = __( 'Disabled. Included WooCommerce pages use generic page Markdown.', 'lc-tweaks' );
	}
}
$dlck_agent_readiness_diagnostic_rows       = array(
	array(
		'label' => __( 'Content Signals', 'lc-tweaks' ),
		'value' => $dlck_agent_readiness_content_signal !== '' ? $dlck_agent_readiness_content_signal : __( 'No signals configured', 'lc-tweaks' ),
	),
	array(
		'label' => __( 'WordPress robots.txt', 'lc-tweaks' ),
		'value' => $dlck_agent_readiness_physical_robots ? __( 'A physical robots.txt file exists and may bypass WordPress virtual robots output.', 'lc-tweaks' ) : __( 'WordPress virtual robots output can be filtered by LC Tweaks.', 'lc-tweaks' ),
	),
	array(
		'label' => __( 'Cloudflare Request', 'lc-tweaks' ),
		'value' => $dlck_agent_readiness_cloudflare_request ? __( 'This admin request appears to be behind Cloudflare. Check the public robots.txt response because Cloudflare managed robots may prepend edge rules.', 'lc-tweaks' ) : __( 'Not detected on this admin request. Public traffic may still pass through Cloudflare.', 'lc-tweaks' ),
	),
	array(
		'label' => __( 'Permalink Fallbacks', 'lc-tweaks' ),
		'value' => $dlck_agent_readiness_pretty_permalinks ? __( 'Pretty permalinks are enabled, so singular /page/index.md fallbacks can resolve.', 'lc-tweaks' ) : __( 'Pretty permalinks are disabled; use Accept: text/markdown and the homepage /index.md fallback.', 'lc-tweaks' ),
	),
	array(
		'label' => __( 'WooCommerce Exclusions', 'lc-tweaks' ),
		'value' => $dlck_agent_readiness_woo_label,
	),
	array(
		'label' => __( 'WooCommerce Markdown', 'lc-tweaks' ),
		'value' => $dlck_agent_readiness_woo_markdown_label,
	),
	array(
		'label' => __( 'Rank Math LLMS Txt', 'lc-tweaks' ),
		'value' => $dlck_rank_math_llms_enabled ? __( 'Available at /llms.txt.', 'lc-tweaks' ) : __( 'Not available from Rank Math right now.', 'lc-tweaks' ),
	),
	array(
		'label' => __( 'Sitemap URL', 'lc-tweaks' ),
		'value' => $dlck_agent_readiness_sitemap_url !== '' ? $dlck_agent_readiness_sitemap_url : __( 'No public sitemap URL detected.', 'lc-tweaks' ),
	),
	array(
		'label' => __( 'Apache Link Header Fallback', 'lc-tweaks' ),
		'value' => $dlck_agent_readiness_htaccess_label,
	),
);
