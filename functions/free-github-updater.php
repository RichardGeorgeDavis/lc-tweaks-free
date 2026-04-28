<?php
/**
 * GitHub Releases updater for the free edition.
 *
 * @package LC Tweaks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'DLCK_FREE_GITHUB_OWNER' ) ) {
	define( 'DLCK_FREE_GITHUB_OWNER', 'RichardGeorgeDavis' );
}

if ( ! defined( 'DLCK_FREE_GITHUB_REPO' ) ) {
	define( 'DLCK_FREE_GITHUB_REPO', 'lc-tweaks-free' );
}

if ( ! defined( 'DLCK_FREE_GITHUB_ASSET_PREFIX' ) ) {
	define( 'DLCK_FREE_GITHUB_ASSET_PREFIX', 'lc-tweaks-free-' );
}

if ( ! defined( 'DLCK_FREE_GITHUB_CACHE_TTL' ) ) {
	define( 'DLCK_FREE_GITHUB_CACHE_TTL', 21600 );
}

if ( ! class_exists( 'DLCK_Free_GitHub_Updater' ) ) {
	class DLCK_Free_GitHub_Updater {
		private string $plugin_file;
		private string $plugin_basename;
		private string $slug = 'lc-tweaks';
		private string $transient_key = 'dlck_free_github_release';
		private string $version = '';

		public function __construct( string $plugin_file ) {
			$this->plugin_file     = $plugin_file;
			$this->plugin_basename = plugin_basename( $plugin_file );
			$this->version         = $this->get_current_version();

			add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'filter_update_transient' ) );
			add_filter( 'plugins_api', array( $this, 'filter_plugin_information' ), 10, 3 );
			add_action( 'upgrader_process_complete', array( $this, 'clear_release_cache_after_update' ), 10, 2 );
		}

		/**
		 * Add GitHub release metadata to WordPress' plugin update transient.
		 *
		 * @param object $transient Update transient object.
		 * @return object
		 */
		public function filter_update_transient( $transient ) {
			if ( ! is_object( $transient ) ) {
				return $transient;
			}

			$release = $this->get_latest_release();
			if ( ! $release ) {
				return $transient;
			}

			$plugin_data = $this->build_update_data( $release );

			if ( version_compare( $release['version'], $this->version, '>' ) ) {
				if ( empty( $transient->response ) || ! is_array( $transient->response ) ) {
					$transient->response = array();
				}

				if ( ! empty( $transient->no_update ) && is_array( $transient->no_update ) ) {
					unset( $transient->no_update[ $this->plugin_basename ] );
				}

				$transient->response[ $this->plugin_basename ] = $plugin_data;
			} else {
				if ( empty( $transient->no_update ) || ! is_array( $transient->no_update ) ) {
					$transient->no_update = array();
				}

				if ( ! empty( $transient->response ) && is_array( $transient->response ) ) {
					unset( $transient->response[ $this->plugin_basename ] );
				}

				$transient->no_update[ $this->plugin_basename ] = $plugin_data;
			}

			return $transient;
		}

		/**
		 * Provide the modal shown by "View version details".
		 *
		 * @param false|object|array $result Existing API result.
		 * @param string             $action Requested plugins_api action.
		 * @param object             $args Request arguments.
		 * @return false|object|array
		 */
		public function filter_plugin_information( $result, string $action, $args ) {
			if ( $action !== 'plugin_information' || empty( $args->slug ) || $args->slug !== $this->slug ) {
				return $result;
			}

			$release = $this->get_latest_release();
			if ( ! $release ) {
				return $result;
			}

			return (object) array(
				'name'          => 'LC Tweaks',
				'slug'          => $this->slug,
				'version'       => $release['version'],
				'author'        => '<a href="https://lucidity.design">Lucidity Design</a>',
				'author_profile' => 'https://lucidity.design',
				'homepage'      => $this->get_repository_url(),
				'download_link' => $release['package'],
				'trunk'         => $release['package'],
				'last_updated'  => $release['published_at'],
				'sections'      => array(
					'description' => '<p>' . esc_html__( 'Free edition of LC Tweaks.', 'divi-lc-kit' ) . '</p>',
					'changelog'   => $this->format_release_notes( $release ),
				),
			);
		}

		/**
		 * Clear cached release data after this plugin is updated.
		 *
		 * @param WP_Upgrader $upgrader Upgrader instance.
		 * @param array       $hook_extra Upgrader context.
		 */
		public function clear_release_cache_after_update( $upgrader, $hook_extra ): void {
			unset( $upgrader );

			if ( empty( $hook_extra['type'] ) || $hook_extra['type'] !== 'plugin' ) {
				return;
			}

			$plugins = array();
			if ( ! empty( $hook_extra['plugin'] ) ) {
				$plugins[] = $hook_extra['plugin'];
			}
			if ( ! empty( $hook_extra['plugins'] ) && is_array( $hook_extra['plugins'] ) ) {
				$plugins = array_merge( $plugins, $hook_extra['plugins'] );
			}

			if ( in_array( $this->plugin_basename, $plugins, true ) ) {
				delete_site_transient( $this->transient_key );
			}
		}

		/**
		 * Fetch and normalize the latest public GitHub release.
		 *
		 * @return array<string,string>|null
		 */
		private function get_latest_release(): ?array {
			$cached = get_site_transient( $this->transient_key );
			if ( is_array( $cached ) && array_key_exists( 'release', $cached ) ) {
				return $cached['release'];
			}

			$response = wp_remote_get(
				$this->get_api_url(),
				array(
					'headers' => array(
						'Accept'     => 'application/vnd.github+json',
						'User-Agent' => 'LC-Tweaks-Free-Updater/' . $this->version,
					),
					'timeout' => 10,
				)
			);

			if ( is_wp_error( $response ) || (int) wp_remote_retrieve_response_code( $response ) !== 200 ) {
				set_site_transient( $this->transient_key, array( 'release' => null ), HOUR_IN_SECONDS );
				return null;
			}

			$release = json_decode( wp_remote_retrieve_body( $response ), true );
			$release = $this->normalize_release( $release );

			set_site_transient( $this->transient_key, array( 'release' => $release ), DLCK_FREE_GITHUB_CACHE_TTL );
			return $release;
		}

		/**
		 * Normalize a GitHub release response into WordPress update metadata.
		 *
		 * @param array|null $release GitHub release response.
		 * @return array<string,string>|null
		 */
		private function normalize_release( $release ): ?array {
			if (
				! is_array( $release )
				|| ! empty( $release['draft'] )
				|| ! empty( $release['prerelease'] )
				|| empty( $release['tag_name'] )
				|| empty( $release['assets'] )
				|| ! is_array( $release['assets'] )
			) {
				return null;
			}

			$version = preg_replace( '/^v/i', '', (string) $release['tag_name'] );
			if ( ! is_string( $version ) || ! preg_match( '/^\d+(?:\.\d+){1,3}(?:[-+][0-9A-Za-z.-]+)?$/', $version ) ) {
				return null;
			}

			$package = $this->find_release_asset_url( $release['assets'], $version );
			if ( empty( $package ) ) {
				return null;
			}

			return array(
				'version'      => $version,
				'tag'          => (string) $release['tag_name'],
				'name'         => ! empty( $release['name'] ) ? (string) $release['name'] : (string) $release['tag_name'],
				'body'         => ! empty( $release['body'] ) ? (string) $release['body'] : '',
				'url'          => ! empty( $release['html_url'] ) ? (string) $release['html_url'] : $this->get_repository_url(),
				'published_at' => ! empty( $release['published_at'] ) ? (string) $release['published_at'] : '',
				'package'      => $package,
			);
		}

		/**
		 * Return the matching release asset URL.
		 *
		 * @param array<int,array<string,mixed>> $assets Release assets from GitHub.
		 * @param string                         $version Release version.
		 */
		private function find_release_asset_url( array $assets, string $version ): ?string {
			$preferred_name = DLCK_FREE_GITHUB_ASSET_PREFIX . $version . '.zip';

			foreach ( $assets as $asset ) {
				if ( ! is_array( $asset ) || empty( $asset['name'] ) || empty( $asset['browser_download_url'] ) ) {
					continue;
				}

				if ( $preferred_name === (string) $asset['name'] ) {
					return (string) $asset['browser_download_url'];
				}
			}

			return null;
		}

		/**
		 * Build WordPress plugin update data.
		 *
		 * @param array<string,string> $release Normalized release data.
		 */
		private function build_update_data( array $release ): object {
			return (object) array(
				'id'          => $this->get_repository_url(),
				'slug'        => $this->slug,
				'plugin'      => $this->plugin_basename,
				'new_version' => $release['version'],
				'url'         => $release['url'],
				'package'     => $release['package'],
			);
		}

		/**
		 * Format release notes for WordPress' plugin information modal.
		 *
		 * @param array<string,string> $release Normalized release data.
		 */
		private function format_release_notes( array $release ): string {
			if ( empty( $release['body'] ) ) {
				return sprintf(
					'<p><a href="%s">%s</a></p>',
					esc_url( $release['url'] ),
					esc_html__( 'View release notes on GitHub.', 'divi-lc-kit' )
				);
			}

			return wp_kses_post( wpautop( esc_html( $release['body'] ) ) );
		}

		private function get_current_version(): string {
			if ( ! function_exists( 'get_file_data' ) ) {
				require_once ABSPATH . 'wp-includes/functions.php';
			}

			$data = get_file_data( $this->plugin_file, array( 'Version' => 'Version' ), 'plugin' );
			return ! empty( $data['Version'] ) ? (string) $data['Version'] : '0.0.0';
		}

		private function get_api_url(): string {
			return sprintf(
				'https://api.github.com/repos/%s/%s/releases/latest',
				rawurlencode( DLCK_FREE_GITHUB_OWNER ),
				rawurlencode( DLCK_FREE_GITHUB_REPO )
			);
		}

		private function get_repository_url(): string {
			return sprintf(
				'https://github.com/%s/%s',
				rawurlencode( DLCK_FREE_GITHUB_OWNER ),
				rawurlencode( DLCK_FREE_GITHUB_REPO )
			);
		}
	}
}

new DLCK_Free_GitHub_Updater( dirname( __DIR__ ) . '/lc-tweaks.php' );
