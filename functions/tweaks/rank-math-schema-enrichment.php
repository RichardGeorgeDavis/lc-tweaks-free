<?php
/**
 * Enrich Rank Math schema output with additional Organization, LocalBusiness,
 * Place, WebSite, WebPage, and Article fields.
 *
 * @package LC Tweaks
 */

add_filter( 'rank_math/json_ld', 'dlck_rank_math_schema_enrich_graph', 120, 2 );
add_filter( 'rank_math/llms_txt/extra_content', 'dlck_rank_math_schema_enrich_llms_extra_content', 20 );

/**
 * Allow admin-only schema preview requests to fetch the Rank Math base graph
 * without LC Tweaks enrichment, so the admin preview can show a diff summary.
 *
 * @return bool
 */
function dlck_rank_math_schema_is_base_preview_request(): bool {
	$mode = isset( $_GET['dlck_rank_math_schema_preview'] ) ? sanitize_key( wp_unslash( $_GET['dlck_rank_math_schema_preview'] ) ) : '';
	if ( $mode !== 'base' ) {
		return false;
	}

	$nonce = isset( $_GET['_dlck_preview_nonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_dlck_preview_nonce'] ) ) : '';
	if ( $nonce === '' || ! wp_verify_nonce( $nonce, 'dlck_rank_math_schema_preview_base' ) ) {
		return false;
	}

	return current_user_can( 'manage_options' );
}

/**
 * Enrich Rank Math's JSON-LD graph after the core graph has been assembled.
 *
 * @param array $data   Rank Math schema graph.
 * @param mixed $jsonld Rank Math JsonLD instance.
 * @return array
 */
function dlck_rank_math_schema_enrich_graph( $data, $jsonld ) {
	if ( ! is_array( $data ) || empty( $data ) ) {
		return $data;
	}

	if ( dlck_rank_math_schema_is_base_preview_request() ) {
		return $data;
	}

	$config = dlck_rank_math_schema_get_enrichment_config();
	if ( empty( $config['enabled'] ) ) {
		return $data;
	}

	$page_description = '';
	if ( is_object( $jsonld ) && isset( $jsonld->parts ) && is_array( $jsonld->parts ) ) {
		$page_description = dlck_rank_math_schema_clean_text( $jsonld->parts['desc'] ?? '' );
	}

	$site_description = dlck_rank_math_schema_clean_text( get_bloginfo( 'description' ) );
	$org_description  = $config['description'];

	if ( $org_description === '' ) {
		if ( is_front_page() && $page_description !== '' ) {
			$org_description = $page_description;
		} else {
			$org_description = $site_description;
		}
	}

	foreach ( $data as $entity_key => $entity ) {
		if ( ! is_array( $entity ) || empty( $entity['@type'] ) ) {
			continue;
		}

		$extra = array();

		if ( dlck_rank_math_schema_is_local_business_entity( $entity ) ) {
			$extra = dlck_rank_math_schema_get_local_business_extras( $config, $org_description, $page_description, $entity );
		} elseif ( dlck_rank_math_schema_is_organization_entity( (string) $entity_key, $entity ) ) {
			$extra = dlck_rank_math_schema_get_organization_extras( $config, $org_description, $entity );
		} elseif ( dlck_rank_math_schema_is_website_entity( $entity ) ) {
			$extra = dlck_rank_math_schema_get_website_extras( $config, $org_description, $site_description, $page_description, $entity );
		} elseif ( dlck_rank_math_schema_is_place_entity( $entity_key, $entity ) ) {
			$extra = dlck_rank_math_schema_get_place_extras( $config, $page_description, $entity );
		} elseif ( dlck_rank_math_schema_is_webpage_entity( $entity ) ) {
			$extra = dlck_rank_math_schema_get_webpage_extras( $config, $page_description, $site_description, $entity );
		} elseif ( dlck_rank_math_schema_is_article_entity( $entity ) ) {
			$extra = dlck_rank_math_schema_get_article_extras( $config );
		}

		if ( ! empty( $extra['contactPoint'] ) ) {
			$entity             = dlck_rank_math_schema_merge_contact_points( $entity, $extra['contactPoint'] );
			$data[ $entity_key ] = $entity;
			unset( $extra['contactPoint'] );
		}

		if ( ! empty( $extra ) ) {
			$data[ $entity_key ] = dlck_rank_math_schema_merge_entity_data( $entity, $extra );
		}
	}

	return $data;
}

/**
 * Build normalized settings for Rank Math schema enrichment.
 *
 * @return array<string,mixed>
 */
function dlck_rank_math_schema_get_enrichment_config(): array {
	static $config = null;

	if ( null !== $config ) {
		return $config;
	}

	$config = array(
		'enabled'            => function_exists( 'dlck_get_option' ) && dlck_get_option( 'dlck_rank_math_schema_enrichment' ) === '1',
		'description'        => dlck_rank_math_schema_get_rank_math_description(),
		'same_as'            => dlck_rank_math_schema_get_rank_math_social_profiles(),
		'knows_about'        => dlck_rank_math_schema_parse_text_lines( function_exists( 'dlck_get_option' ) ? dlck_get_option( 'dlck_rank_math_schema_knows_about' ) : '' ),
		'area_served'        => dlck_rank_math_schema_parse_text_lines( function_exists( 'dlck_get_option' ) ? dlck_get_option( 'dlck_rank_math_schema_area_served' ) : '' ),
		'founders'           => dlck_rank_math_schema_parse_people_lines( function_exists( 'dlck_get_option' ) ? dlck_get_option( 'dlck_rank_math_schema_founders' ) : '' ),
		'employees'          => dlck_rank_math_schema_parse_people_lines( function_exists( 'dlck_get_option' ) ? dlck_get_option( 'dlck_rank_math_schema_employees' ) : '' ),
		'contact_url'        => dlck_rank_math_schema_get_rank_math_contact_url(),
		'contact_languages'  => dlck_rank_math_schema_parse_text_lines( function_exists( 'dlck_get_option' ) ? dlck_get_option( 'dlck_rank_math_schema_contact_languages' ) : '' ),
		'contact_points'     => dlck_rank_math_schema_parse_contact_point_lines( function_exists( 'dlck_get_option' ) ? dlck_get_option( 'dlck_rank_math_schema_contact_points' ) : '' ),
		'advanced_entities'  => dlck_rank_math_schema_parse_advanced_entities( function_exists( 'dlck_get_option' ) ? dlck_get_option( 'dlck_rank_math_schema_advanced_json' ) : '' ),
	);

	return $config;
}

/**
 * Return Rank Math's built-in organization description.
 *
 * @return string
 */
function dlck_rank_math_schema_get_rank_math_description(): string {
	$settings = get_option( 'rank-math-options-titles', array() );
	if ( ! is_array( $settings ) ) {
		return '';
	}

	return dlck_rank_math_schema_clean_text( $settings['organization_description'] ?? '' );
}

/**
 * Return Rank Math's configured contact page URL.
 *
 * @return string
 */
function dlck_rank_math_schema_get_rank_math_contact_url(): string {
	$settings = get_option( 'rank-math-options-titles', array() );
	if ( ! is_array( $settings ) ) {
		return '';
	}

	$page_id = absint( $settings['local_seo_contact_page'] ?? 0 );
	if ( ! $page_id ) {
		return '';
	}

	return esc_url_raw( (string) get_permalink( $page_id ) );
}

/**
 * Return Rank Math's built-in social profile URLs for sameAs usage.
 *
 * @return string[]
 */
function dlck_rank_math_schema_get_rank_math_social_profiles(): array {
	$settings = get_option( 'rank-math-options-titles', array() );
	if ( ! is_array( $settings ) ) {
		return array();
	}

	$profiles = array();

	$facebook = esc_url_raw( (string) ( $settings['social_url_facebook'] ?? '' ) );
	if ( $facebook !== '' ) {
		$profiles[] = $facebook;
	}

	$twitter = trim( (string) ( $settings['twitter_author_names'] ?? '' ) );
	$twitter = ltrim( $twitter, '@' );
	if ( $twitter !== '' ) {
		$profiles[] = 'https://twitter.com/' . $twitter;
	}

	$additional_profiles = dlck_rank_math_schema_parse_url_lines( $settings['social_additional_profiles'] ?? '' );
	if ( ! empty( $additional_profiles ) ) {
		$profiles = array_merge( $profiles, $additional_profiles );
	}

	return array_values( array_unique( array_filter( $profiles ) ) );
}

/**
 * Return additional Organization fields.
 *
 * @param array  $config          Parsed config.
 * @param string $org_description Resolved description.
 * @param array  $entity          Existing entity.
 * @return array<string,mixed>
 */
function dlck_rank_math_schema_get_organization_extras( array $config, string $org_description, array $entity = array() ): array {
	$extra = array();

	if ( $org_description !== '' && ( ! empty( $config['description'] ) || empty( $entity['description'] ) ) ) {
		$extra['description'] = $org_description;
	}

	if ( ! empty( $config['knows_about'] ) ) {
		$extra['knowsAbout'] = count( $config['knows_about'] ) === 1 ? $config['knows_about'][0] : $config['knows_about'];
	}

	if ( ! empty( $config['area_served'] ) ) {
		$extra['areaServed'] = count( $config['area_served'] ) === 1 ? $config['area_served'][0] : $config['area_served'];
	}

	if ( ! empty( $config['founders'] ) ) {
		$extra['founder'] = $config['founders'];
	}

	if ( ! empty( $config['employees'] ) ) {
		$extra['employee'] = $config['employees'];
	}

	$contact_points = array();

	if ( ! empty( $config['contact_url'] ) || ! empty( $config['contact_languages'] ) ) {
		$contact_point = array(
			'@type'       => 'ContactPoint',
			'contactType' => 'customer support',
		);

		if ( ! empty( $config['contact_url'] ) ) {
			$contact_point['url'] = $config['contact_url'];
		}

		if ( ! empty( $config['contact_languages'] ) ) {
			$contact_point['availableLanguage'] = count( $config['contact_languages'] ) === 1 ? $config['contact_languages'][0] : $config['contact_languages'];
		}

		$contact_points[] = $contact_point;
	}

	if ( ! empty( $config['contact_points'] ) ) {
		$contact_points = dlck_rank_math_schema_merge_lists( $contact_points, $config['contact_points'] );
	}

	if ( ! empty( $contact_points ) ) {
		$extra['contactPoint'] = $contact_points;
	}

	if ( ! empty( $config['advanced_entities']['organization'] ) ) {
		$extra = dlck_rank_math_schema_merge_entity_data( $extra, $config['advanced_entities']['organization'] );
	}

	return $extra;
}

/**
 * Return additional WebSite fields.
 *
 * @param array  $config           Parsed config.
 * @param string $org_description  Organization description.
 * @param string $site_description Site tagline/description.
 * @param string $page_description Current page description.
 * @param array  $entity           Existing entity.
 * @return array<string,mixed>
 */
function dlck_rank_math_schema_get_website_extras( array $config, string $org_description, string $site_description, string $page_description, array $entity = array() ): array {
	$extra               = array();
	$website_description = $org_description !== '' ? $org_description : $site_description;

	if ( $website_description === '' ) {
		$website_description = $page_description;
	}

	if ( $website_description !== '' && ( ! empty( $config['description'] ) || empty( $entity['description'] ) ) ) {
		$extra['description'] = $website_description;
	}

	if ( ! empty( $config['advanced_entities']['website'] ) ) {
		$extra = dlck_rank_math_schema_merge_entity_data( $extra, $config['advanced_entities']['website'] );
	}

	return $extra;
}

/**
 * Return additional WebPage fields.
 *
 * @param array  $config           Parsed config.
 * @param string $page_description Current page description.
 * @param string $site_description Site tagline/description.
 * @param array  $entity           Existing entity.
 * @return array<string,mixed>
 */
function dlck_rank_math_schema_get_webpage_extras( array $config, string $page_description, string $site_description, array $entity = array() ): array {
	$extra               = array();
	$webpage_description = $page_description !== '' ? $page_description : $site_description;

	if ( $webpage_description !== '' && empty( $entity['description'] ) ) {
		$extra['description'] = $webpage_description;
	}

	if ( ! empty( $config['advanced_entities']['webpage'] ) ) {
		$extra = dlck_rank_math_schema_merge_entity_data( $extra, $config['advanced_entities']['webpage'] );
	}

	return $extra;
}

/**
 * Return additional Article fields.
 *
 * @param array $config Parsed config.
 * @return array<string,mixed>
 */
function dlck_rank_math_schema_get_article_extras( array $config ): array {
	return ! empty( $config['advanced_entities']['article'] ) && is_array( $config['advanced_entities']['article'] )
		? $config['advanced_entities']['article']
		: array();
}

/**
 * Enrich Rank Math's llms.txt additional content with key organization context.
 *
 * @param mixed $extra Existing additional content.
 * @return string
 */
function dlck_rank_math_schema_enrich_llms_extra_content( $extra ): string {
	$config = dlck_rank_math_schema_get_enrichment_config();
	if ( empty( $config['enabled'] ) ) {
		return is_scalar( $extra ) ? (string) $extra : '';
	}

	$existing  = is_scalar( $extra ) ? trim( (string) $extra ) : '';
	$sections  = dlck_rank_math_schema_build_llms_sections( $config );

	if ( empty( $sections ) ) {
		return $existing;
	}

	if ( $existing !== '' ) {
		$existing_headings = dlck_rank_math_schema_extract_llms_headings( $existing );
		foreach ( array_keys( $sections ) as $heading ) {
			if ( isset( $existing_headings[ dlck_rank_math_schema_normalize_llms_heading( $heading ) ] ) ) {
				unset( $sections[ $heading ] );
			}
		}
	}

	if ( empty( $sections ) ) {
		return $existing;
	}

	$generated = dlck_rank_math_schema_render_llms_sections( $sections );
	if ( $existing === '' ) {
		return $generated;
	}

	return $existing . "\n\n" . $generated;
}

/**
 * Build organization-focused llms.txt markdown from saved schema settings.
 *
 * @param array $config Parsed config.
 * @return string
 */
function dlck_rank_math_schema_build_llms_extra_content( array $config ): string {
	return dlck_rank_math_schema_render_llms_sections( dlck_rank_math_schema_build_llms_sections( $config ) );
}

/**
 * Build organization-focused llms.txt sections keyed by heading.
 *
 * @param array $config Parsed config.
 * @return array<string,string>
 */
function dlck_rank_math_schema_build_llms_sections( array $config ): array {
	$sections = array();

	if ( ! empty( $config['description'] ) ) {
		$sections['Organization'] = $config['description'];
	}

	if ( ! empty( $config['founders'] ) ) {
		$sections['Leadership'] = dlck_rank_math_schema_format_llms_people_list( $config['founders'] );
	}

	if ( ! empty( $config['knows_about'] ) ) {
		$sections['Expertise'] = dlck_rank_math_schema_format_llms_text_list( $config['knows_about'] );
	}

	if ( ! empty( $config['area_served'] ) ) {
		$sections['Areas Served'] = dlck_rank_math_schema_format_llms_text_list( $config['area_served'] );
	}

	$contact_lines = array();
	if ( ! empty( $config['contact_url'] ) ) {
		$contact_lines[] = '- [Contact and Support](' . $config['contact_url'] . ')';
	}

	if ( ! empty( $config['contact_points'] ) ) {
		$formatted_contact_points = dlck_rank_math_schema_format_llms_contact_points( $config['contact_points'] );
		if ( $formatted_contact_points !== '' ) {
			$contact_lines[] = $formatted_contact_points;
		}
	}

	if ( ! empty( $contact_lines ) ) {
		$sections['Contact'] = implode( "\n", $contact_lines );
	}

	if ( ! empty( $config['same_as'] ) ) {
		$sections['Official Profiles'] = dlck_rank_math_schema_format_llms_url_list( $config['same_as'] );
	}

	return array_filter(
		$sections,
		static function ( $section ): bool {
			return is_string( $section ) && trim( $section ) !== '';
		}
	);
}

/**
 * Render llms.txt sections into markdown.
 *
 * @param array<string,string> $sections Parsed sections keyed by heading.
 * @return string
 */
function dlck_rank_math_schema_render_llms_sections( array $sections ): string {
	$output = array();

	foreach ( $sections as $heading => $body ) {
		$heading = trim( (string) $heading );
		$body    = trim( (string) $body );
		if ( $heading === '' || $body === '' ) {
			continue;
		}

		$output[] = '## ' . $heading . "\n" . $body;
	}

	return implode( "\n\n", $output );
}

/**
 * Extract normalized level-2 headings from llms markdown content.
 *
 * @param string $markdown Existing markdown content.
 * @return array<string,bool>
 */
function dlck_rank_math_schema_extract_llms_headings( string $markdown ): array {
	$headings = array();

	if ( preg_match_all( '/^##\s+(.+)$/m', $markdown, $matches ) && ! empty( $matches[1] ) ) {
		foreach ( $matches[1] as $heading ) {
			$normalized = dlck_rank_math_schema_normalize_llms_heading( $heading );
			if ( $normalized === '' ) {
				continue;
			}

			$headings[ $normalized ] = true;
		}
	}

	return $headings;
}

/**
 * Normalize llms section headings for reliable comparisons.
 *
 * @param mixed $heading Raw heading value.
 * @return string
 */
function dlck_rank_math_schema_normalize_llms_heading( $heading ): string {
	if ( ! is_scalar( $heading ) ) {
		return '';
	}

	$heading = strtolower( trim( (string) $heading ) );
	$heading = preg_replace( '/\s+/', ' ', $heading );

	return trim( (string) $heading );
}

/**
 * Return additional LocalBusiness fields for Rank Math Pro location schemas.
 *
 * @param array  $config           Parsed config.
 * @param string $org_description  Organization description.
 * @param string $page_description Current page description.
 * @param array  $entity           Existing entity.
 * @return array<string,mixed>
 */
function dlck_rank_math_schema_get_local_business_extras( array $config, string $org_description, string $page_description, array $entity = array() ): array {
	$extra = dlck_rank_math_schema_get_organization_extras( $config, $org_description, $entity );

	if ( empty( $extra['description'] ) && $page_description !== '' && empty( $entity['description'] ) ) {
		$extra['description'] = $page_description;
	}

	if ( ! empty( $config['advanced_entities']['localbusiness'] ) ) {
		$extra = dlck_rank_math_schema_merge_entity_data( $extra, $config['advanced_entities']['localbusiness'] );
	}

	return $extra;
}

/**
 * Return additional Place fields for Rank Math Pro place entities.
 *
 * @param array  $config           Parsed config.
 * @param string $page_description Current page description.
 * @param array  $entity           Existing entity.
 * @return array<string,mixed>
 */
function dlck_rank_math_schema_get_place_extras( array $config, string $page_description, array $entity = array() ): array {
	$extra = array();

	if ( $page_description !== '' && empty( $entity['description'] ) ) {
		$extra['description'] = $page_description;
	}

	if ( ! empty( $config['advanced_entities']['place'] ) ) {
		$extra = dlck_rank_math_schema_merge_entity_data( $extra, $config['advanced_entities']['place'] );
	}

	return $extra;
}

/**
 * Parse newline-separated text into a normalized array.
 *
 * @param mixed $raw Raw textarea value.
 * @return string[]
 */
function dlck_rank_math_schema_parse_text_lines( $raw ): array {
	if ( ! is_scalar( $raw ) || $raw === '' ) {
		return array();
	}

	$lines = preg_split( '/\r\n|\r|\n/', (string) $raw );
	if ( ! is_array( $lines ) ) {
		return array();
	}

	$normalized = array();
	foreach ( $lines as $line ) {
		$line = dlck_rank_math_schema_clean_text( $line );
		if ( $line === '' ) {
			continue;
		}
		$normalized[] = $line;
	}

	return array_values( array_unique( $normalized ) );
}

/**
 * Parse newline-separated URLs into a normalized array.
 *
 * @param mixed $raw Raw textarea value.
 * @return string[]
 */
function dlck_rank_math_schema_parse_url_lines( $raw ): array {
	$lines = dlck_rank_math_schema_parse_text_lines( $raw );
	$urls  = array();

	foreach ( $lines as $line ) {
		$url = esc_url_raw( $line );
		if ( $url === '' ) {
			continue;
		}
		$urls[] = $url;
	}

	return array_values( array_unique( $urls ) );
}

/**
 * Parse newline-separated people in the format "Name | Job Title".
 *
 * @param mixed $raw Raw textarea value.
 * @return array<int,array<string,string>>
 */
function dlck_rank_math_schema_parse_people_lines( $raw ): array {
	if ( ! is_scalar( $raw ) || $raw === '' ) {
		return array();
	}

	$lines  = preg_split( '/\r\n|\r|\n/', (string) $raw );
	$people = array();

	if ( ! is_array( $lines ) ) {
		return $people;
	}

	foreach ( $lines as $line ) {
		$line = trim( (string) $line );
		if ( $line === '' ) {
			continue;
		}

		$parts = array_map( 'trim', explode( '|', $line, 2 ) );
		$name  = dlck_rank_math_schema_clean_text( $parts[0] ?? '' );

		if ( $name === '' ) {
			continue;
		}

		$person = array(
			'@type' => 'Person',
			'name'  => $name,
		);

		$job_title = dlck_rank_math_schema_clean_text( $parts[1] ?? '' );
		if ( $job_title !== '' ) {
			$person['jobTitle'] = $job_title;
		}

		$people[] = $person;
	}

	return dlck_rank_math_schema_merge_lists( array(), $people );
}

/**
 * Parse newline-separated contact points.
 *
 * Format per line:
 * Contact Type | Email | Telephone | URL | Languages
 *
 * @param mixed $raw Raw textarea value.
 * @return array<int,array<string,mixed>>
 */
function dlck_rank_math_schema_parse_contact_point_lines( $raw ): array {
	if ( ! is_scalar( $raw ) || $raw === '' ) {
		return array();
	}

	$lines          = preg_split( '/\r\n|\r|\n/', (string) $raw );
	$contact_points = array();

	if ( ! is_array( $lines ) ) {
		return $contact_points;
	}

	foreach ( $lines as $line ) {
		$line = trim( (string) $line );
		if ( $line === '' ) {
			continue;
		}

		$parts        = array_map( 'trim', explode( '|', $line, 5 ) );
		$contact_type = dlck_rank_math_schema_clean_text( $parts[0] ?? '' );
		if ( $contact_type === '' ) {
			continue;
		}

		$contact_point = array(
			'@type'       => 'ContactPoint',
			'contactType' => $contact_type,
		);

		$email = sanitize_email( (string) ( $parts[1] ?? '' ) );
		if ( $email !== '' ) {
			$contact_point['email'] = $email;
		}

		$telephone = dlck_rank_math_schema_clean_text( $parts[2] ?? '' );
		if ( $telephone !== '' ) {
			$contact_point['telephone'] = $telephone;
		}

		$url = esc_url_raw( (string) ( $parts[3] ?? '' ) );
		if ( $url !== '' ) {
			$contact_point['url'] = $url;
		}

		$languages = array();
		if ( ! empty( $parts[4] ) ) {
			foreach ( preg_split( '/\s*,\s*/', (string) $parts[4] ) as $language ) {
				$language = dlck_rank_math_schema_clean_text( $language );
				if ( $language === '' ) {
					continue;
				}

				$languages[] = $language;
			}
		}

		if ( ! empty( $languages ) ) {
			$languages                        = array_values( array_unique( $languages ) );
			$contact_point['availableLanguage'] = count( $languages ) === 1 ? $languages[0] : $languages;
		}

		if ( count( $contact_point ) <= 2 ) {
			continue;
		}

		$contact_points[] = $contact_point;
	}

	return dlck_rank_math_schema_merge_lists( array(), $contact_points );
}

/**
 * Format people arrays as markdown bullet points for llms.txt output.
 *
 * @param array<int,array<string,string>> $people Parsed people data.
 * @return string
 */
function dlck_rank_math_schema_format_llms_people_list( array $people ): string {
	$lines = array();

	foreach ( $people as $person ) {
		if ( empty( $person['name'] ) ) {
			continue;
		}

		$line = '- ' . $person['name'];
		if ( ! empty( $person['jobTitle'] ) ) {
			$line .= ': ' . $person['jobTitle'];
		}

		$lines[] = $line;
	}

	return implode( "\n", $lines );
}

/**
 * Format text arrays as markdown bullet points for llms.txt output.
 *
 * @param string[] $items Text items.
 * @return string
 */
function dlck_rank_math_schema_format_llms_text_list( array $items ): string {
	$lines = array();

	foreach ( $items as $item ) {
		if ( ! is_string( $item ) || $item === '' ) {
			continue;
		}

		$lines[] = '- ' . $item;
	}

	return implode( "\n", $lines );
}

/**
 * Format URL arrays as markdown bullet points for llms.txt output.
 *
 * @param string[] $urls URL items.
 * @return string
 */
function dlck_rank_math_schema_format_llms_url_list( array $urls ): string {
	$lines = array();

	foreach ( $urls as $url ) {
		$url = esc_url_raw( $url );
		if ( $url === '' ) {
			continue;
		}

		$label  = wp_parse_url( $url, PHP_URL_HOST );
		$path   = wp_parse_url( $url, PHP_URL_PATH );
		$label  = is_string( $label ) ? preg_replace( '/^www\./', '', $label ) : '';
		$path   = is_string( $path ) ? trim( $path, '/' ) : '';
		$label  = $label !== '' ? $label : $url;
		$label .= $path !== '' ? '/' . $path : '';

		$lines[] = '- [' . $label . '](' . $url . ')';
	}

	return implode( "\n", $lines );
}

/**
 * Format contact points as markdown bullet points for llms.txt output.
 *
 * @param array<int,array<string,mixed>> $contact_points ContactPoint data.
 * @return string
 */
function dlck_rank_math_schema_format_llms_contact_points( array $contact_points ): string {
	$lines = array();

	foreach ( $contact_points as $contact_point ) {
		if ( ! is_array( $contact_point ) ) {
			continue;
		}

		$contact_type = dlck_rank_math_schema_clean_text( $contact_point['contactType'] ?? '' );
		if ( $contact_type === '' ) {
			$contact_type = 'Contact';
		}

		$details = array();

		$email = sanitize_email( (string) ( $contact_point['email'] ?? '' ) );
		if ( $email !== '' ) {
			$details[] = $email;
		}

		$telephone = dlck_rank_math_schema_clean_text( $contact_point['telephone'] ?? '' );
		if ( $telephone !== '' ) {
			$details[] = $telephone;
		}

		$url = esc_url_raw( (string) ( $contact_point['url'] ?? '' ) );
		if ( $url !== '' ) {
			$details[] = '[Contact Link](' . $url . ')';
		}

		$languages = array();
		if ( isset( $contact_point['availableLanguage'] ) ) {
			foreach ( dlck_rank_math_schema_force_list( $contact_point['availableLanguage'] ) as $language ) {
				$language = dlck_rank_math_schema_clean_text( $language );
				if ( $language === '' ) {
					continue;
				}

				$languages[] = $language;
			}
		}

		if ( ! empty( $languages ) ) {
			$languages = array_values( array_unique( $languages ) );
			$details[] = 'Languages: ' . implode( ', ', $languages );
		}

		if ( empty( $details ) ) {
			continue;
		}

		$lines[] = '- ' . $contact_type . ': ' . implode( ' | ', $details );
	}

	return implode( "\n", $lines );
}

/**
 * Parse advanced JSON merge settings.
 *
 * Expected shape:
 * {
 *   "organization": {...},
 *   "localbusiness": {...},
 *   "place": {...},
 *   "website": {...},
 *   "webpage": {...},
 *   "article": {...}
 * }
 *
 * @param mixed $raw Raw JSON string.
 * @return array<string,array<string,mixed>>
 */
function dlck_rank_math_schema_parse_advanced_entities( $raw ): array {
	$entities = array(
		'organization' => array(),
		'localbusiness' => array(),
		'place'        => array(),
		'website'      => array(),
		'webpage'      => array(),
		'article'      => array(),
	);

	if ( ! is_scalar( $raw ) ) {
		return $entities;
	}

	$raw = trim( (string) $raw );
	if ( $raw === '' ) {
		return $entities;
	}

	$decoded = json_decode( $raw, true );
	if ( ! is_array( $decoded ) ) {
		return $entities;
	}

	foreach ( $entities as $key => $default ) {
		if ( isset( $decoded[ $key ] ) && is_array( $decoded[ $key ] ) ) {
			$entities[ $key ] = $decoded[ $key ];
		}
	}

	return $entities;
}

/**
 * Merge extra schema data into an entity.
 *
 * Arrays are merged recursively and list-like arrays are appended uniquely.
 *
 * @param array $entity Existing entity.
 * @param array $extra  Extra data to merge.
 * @return array
 */
function dlck_rank_math_schema_merge_entity_data( array $entity, array $extra ): array {
	foreach ( $extra as $key => $value ) {
		if ( $value === '' || $value === null || ( is_array( $value ) && empty( $value ) ) ) {
			continue;
		}

		if ( ! array_key_exists( $key, $entity ) ) {
			$entity[ $key ] = $value;
			continue;
		}

		if ( is_array( $entity[ $key ] ) && is_array( $value ) ) {
			if ( dlck_rank_math_schema_is_list( $entity[ $key ] ) || dlck_rank_math_schema_is_list( $value ) ) {
				$entity[ $key ] = dlck_rank_math_schema_merge_lists(
					dlck_rank_math_schema_force_list( $entity[ $key ] ),
					dlck_rank_math_schema_force_list( $value )
				);
			} else {
				$entity[ $key ] = dlck_rank_math_schema_merge_entity_data( $entity[ $key ], $value );
			}
			continue;
		}

		if ( is_array( $value ) ) {
			$entity[ $key ] = dlck_rank_math_schema_merge_lists(
				dlck_rank_math_schema_force_list( $entity[ $key ] ),
				dlck_rank_math_schema_force_list( $value )
			);
			continue;
		}

		$entity[ $key ] = $value;
	}

	return $entity;
}

/**
 * Merge additional contact point fields into a matching ContactPoint entry.
 *
 * @param array $entity         Existing entity.
 * @param array $contact_points Incoming contact points.
 * @return array
 */
function dlck_rank_math_schema_merge_contact_points( array $entity, array $contact_points ): array {
	$existing = array();
	if ( ! empty( $entity['contactPoint'] ) ) {
		$existing = dlck_rank_math_schema_force_list( $entity['contactPoint'] );
	}

	foreach ( dlck_rank_math_schema_force_list( $contact_points ) as $incoming_point ) {
		if ( ! is_array( $incoming_point ) ) {
			continue;
		}

		$incoming_contact_type = dlck_rank_math_schema_normalize_contact_type( $incoming_point['contactType'] ?? '' );
		$merged = false;
		foreach ( $existing as $index => $existing_point ) {
			if ( ! is_array( $existing_point ) ) {
				continue;
			}

			$existing_contact_type = dlck_rank_math_schema_normalize_contact_type( $existing_point['contactType'] ?? '' );
			if ( $incoming_contact_type !== '' && $existing_contact_type !== $incoming_contact_type ) {
				continue;
			}

			$existing[ $index ] = dlck_rank_math_schema_merge_entity_data( $existing_point, $incoming_point );
			$merged             = true;
			break;
		}

		if ( ! $merged ) {
			$existing[] = $incoming_point;
		}
	}

	if ( ! empty( $existing ) ) {
		$entity['contactPoint'] = $existing;
	}

	return $entity;
}

/**
 * Normalize contact types for reliable comparisons.
 *
 * @param mixed $contact_type Raw contact type value.
 * @return string
 */
function dlck_rank_math_schema_normalize_contact_type( $contact_type ): string {
	if ( ! is_scalar( $contact_type ) ) {
		return '';
	}

	$contact_type = strtolower( trim( (string) $contact_type ) );
	$contact_type = preg_replace( '/\s+/', ' ', $contact_type );

	return trim( (string) $contact_type );
}

/**
 * Merge list-like arrays while keeping unique items in original order.
 *
 * @param array $existing Existing items.
 * @param array $incoming Incoming items.
 * @return array
 */
function dlck_rank_math_schema_merge_lists( array $existing, array $incoming ): array {
	$merged = array();
	$seen   = array();

	foreach ( array_merge( $existing, $incoming ) as $item ) {
		$hash = is_scalar( $item ) || null === $item
			? 'scalar:' . (string) $item
			: 'json:' . wp_json_encode( $item );

		if ( isset( $seen[ $hash ] ) ) {
			continue;
		}

		$seen[ $hash ] = true;
		$merged[]      = $item;
	}

	return $merged;
}

/**
 * Normalize a value into a list.
 *
 * @param mixed $value Value to normalize.
 * @return array
 */
function dlck_rank_math_schema_force_list( $value ): array {
	if ( ! is_array( $value ) ) {
		return array( $value );
	}

	return dlck_rank_math_schema_is_list( $value ) ? $value : array( $value );
}

/**
 * Determine whether an array is list-like.
 *
 * @param array $value Candidate array.
 * @return bool
 */
function dlck_rank_math_schema_is_list( array $value ): bool {
	if ( function_exists( 'array_is_list' ) ) {
		return array_is_list( $value );
	}

	return array_keys( $value ) === range( 0, count( $value ) - 1 );
}

/**
 * Check if the current entity should be treated as Organization.
 *
 * Rank Math can emit a main knowledge graph entity as both Person and
 * Organization on personal-brand sites. Allow that root entity through, while
 * still avoiding author/person nodes that should not receive organization data.
 *
 * @param string $entity_key Entity key in the graph.
 * @param array  $entity     Entity data.
 * @return bool
 */
function dlck_rank_math_schema_is_organization_entity( string $entity_key, array $entity ): bool {
	$types = dlck_rank_math_schema_get_entity_types( $entity );
	if ( ! in_array( 'organization', $types, true ) ) {
		return false;
	}

	if ( ! in_array( 'person', $types, true ) ) {
		return true;
	}

	$id = strtolower( (string) ( $entity['@id'] ?? '' ) );
	$entity_key = strtolower( $entity_key );

	return $entity_key === 'organization'
		|| $entity_key === 'person'
		|| $entity_key === 'publisher'
		|| str_contains( $id, '#organization' )
		|| ( str_contains( $id, '#person' ) && ! str_contains( $id, '#author' ) );
}

/**
 * Check if the current entity is a WebSite.
 *
 * @param array $entity Entity data.
 * @return bool
 */
function dlck_rank_math_schema_is_website_entity( array $entity ): bool {
	return in_array( 'website', dlck_rank_math_schema_get_entity_types( $entity ), true );
}

/**
 * Check if the current entity is a Place entity.
 *
 * @param string $entity_key Entity key in the graph.
 * @param array  $entity     Entity data.
 * @return bool
 */
function dlck_rank_math_schema_is_place_entity( string $entity_key, array $entity ): bool {
	if ( in_array( 'place', dlck_rank_math_schema_get_entity_types( $entity ), true ) ) {
		return true;
	}

	return strtolower( $entity_key ) === 'place';
}

/**
 * Check if the current entity looks like a LocalBusiness or one of its subtypes.
 *
 * Rank Math Pro location entities often use a specific business subtype without
 * explicitly including Organization in the @type array.
 *
 * @param array $entity Entity data.
 * @return bool
 */
function dlck_rank_math_schema_is_local_business_entity( array $entity ): bool {
	$types = dlck_rank_math_schema_get_entity_types( $entity );
	if ( empty( $types ) ) {
		return false;
	}

	if ( in_array( 'person', $types, true ) ) {
		return false;
	}

	if ( in_array( 'localbusiness', $types, true ) ) {
		return true;
	}

	if ( in_array( 'organization', $types, true ) || in_array( 'place', $types, true ) ) {
		return false;
	}

	if ( empty( $entity['address'] ) ) {
		return false;
	}

	$signals = array(
		'openingHours',
		'openingHoursSpecification',
		'priceRange',
		'telephone',
		'geo',
		'currenciesAccepted',
		'paymentAccepted',
		'servesCuisine',
	);

	foreach ( $signals as $signal ) {
		if ( isset( $entity[ $signal ] ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Check if the current entity is a WebPage-like page entity.
 *
 * @param array $entity Entity data.
 * @return bool
 */
function dlck_rank_math_schema_is_webpage_entity( array $entity ): bool {
	foreach ( dlck_rank_math_schema_get_entity_types( $entity ) as $type ) {
		if ( $type === 'webpage' || str_ends_with( $type, 'page' ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Check if the current entity is an Article-like entity.
 *
 * @param array $entity Entity data.
 * @return bool
 */
function dlck_rank_math_schema_is_article_entity( array $entity ): bool {
	$types = dlck_rank_math_schema_get_entity_types( $entity );
	return (bool) array_intersect( $types, array( 'article', 'blogposting', 'newsarticle' ) );
}

/**
 * Return normalized lowercase entity types.
 *
 * @param array $entity Entity data.
 * @return string[]
 */
function dlck_rank_math_schema_get_entity_types( array $entity ): array {
	if ( empty( $entity['@type'] ) ) {
		return array();
	}

	$types = is_array( $entity['@type'] ) ? $entity['@type'] : array( $entity['@type'] );
	$types = array_map(
		static function ( $type ) {
			return strtolower( trim( (string) $type ) );
		},
		$types
	);

	return array_values( array_unique( array_filter( $types ) ) );
}

/**
 * Normalize free text for schema values.
 *
 * @param mixed $value Raw value.
 * @return string
 */
function dlck_rank_math_schema_clean_text( $value ): string {
	if ( ! is_scalar( $value ) ) {
		return '';
	}

	$value = wp_strip_all_tags( (string) $value, true );
	$value = preg_replace( '/\s+/', ' ', $value );

	return trim( (string) $value );
}
