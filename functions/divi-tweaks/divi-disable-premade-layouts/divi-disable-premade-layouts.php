<?php
/**
 * @package Divi Disable Premade Layouts
 * @version 2.1.0
 */
 /**
  * Do not allow direct access
  *
  * @since	1.0.0
  */
	if ( ! defined( 'ABSPATH' ) ) die( 'Don\'t try to load this file directly!' );

 /**
  * Divi - Disable Premade Layouts - Initialize
  *
  * @since	1.0.0
  */
 if ( ! class_exists( 'ddplPluginInitialize' ) )
 {

 	class ddplPluginInitialize
 	{

 		/**
 		 * Define properties
 		 *
 		 * @since	1.0.0
 		 */
 		private $notice;

 		private $assets;

 		/**
 		 * Define plugin version
 		 *
 		 * @since	1.0.0
 		 */
 		const VERSION = '2.0.0';

 		/**
 		 * Define this file
 		 *
 		 * @since	1.0.0
 		 */
 		const FILE    = __FILE__;

 		/**
 		 * Define plugin prefix
 		 *
 		 * @since	1.0.1
 		 */
 		const SUFFIX  = '.min';

 		/**
 		 * Define plugin prefix
 		 *
 		 * @since	1.0.0
 		 */
 		const PREFIX  = 'ddpl';

 		/**
 		 * Define Textdomain
 		 *
 		 * @since	1.0.0
 		 */
 		const DOMAIN  = 'divi-disable-premade-layouts';

 		/**
 		 * Constructor
 		 *
 		 * @since	1.0.0
 		 */
 		public function __construct()
 		{

 			/**
 			 * Set properties
 			 *
 			 * @since	1.0.1
 			 */
 		 	$this->assets = plugin_dir_url( self::FILE ) . 'assets/';

 			/**
 			 * Let the magic happens
 			 *
 			 * @since	1.0.0
 			 */
 			$this->initialize();

 		} // end constructor

 		/**
 		 * Initialize
 		 *
 		 * @since 1.0.1
 		 */
 		private function initialize()
 		{

 			/**
 			 * Route to Admin or Frontend
 			 *
 			 * @since	1.0.0
 			 */
 			if ( is_admin() ) :

 				add_action( 'init', [ $this, 'admin' ] );

 			elseif ( isset( $_REQUEST['et_fb'] ) ) :

 				add_action( 'wp_enqueue_scripts', [ $this, 'enqueue' ], PHP_INT_MAX );

 			endif;

 			/**
 			 * Enqueue to the admin scripts
 			 *
 			 * @since	1.0.0
 			 */
 			add_action( 'admin_enqueue_scripts', [ $this, 'enqueue' ] );

 			/**
 			 * Add the settings to the settings panel
 			 *
 			 * @since	1.0.0
 			 */
 			add_action( 'et_epanel_changing_options', [ $this, 'addSettings' ] );

			/**
			 * Disable Divi Cloud/premade layouts globally.
			 *
			 * @since 2.0.0
			 */
			add_action( 'init', [ $this, 'disableCloudAndPremades' ] );

 		} // end initialize

		/**
		 * Disable Divi Cloud and premade layout packs.
		 *
		 * @since 2.0.0
		 */
		public function disableCloudAndPremades() {
			if ( function_exists( 'et_core_enable_cloud' ) ) {
				add_filter( 'et_core_enable_cloud', '__return_false' );
			}
			if ( function_exists( 'et_pb_enable_cloud_library' ) ) {
				add_filter( 'et_pb_enable_cloud_library', '__return_false' );
			}

			add_filter( 'et_builder_enable_premade_layouts', '__return_false' );
			add_filter( 'et_core_page_resource_localization', [ $this, 'disableCloudResourceFlag' ] );
		}

		/**
		 * Ensure builder resource data has Divi Cloud disabled.
		 *
		 * @since 2.0.0
		 *
		 * @param array $data Resource localization array.
		 *
		 * @return array
		 */
		public function disableCloudResourceFlag( $data ) {
			if ( isset( $data['cloud'] ) ) {
				$data['cloud']['enabled'] = false;
			}

			return $data;
		}

 		/**
 		 * Start the admin tasks
 		 *
 		 * @since 1.0.0
 		 */
 		public function admin()
 		{

 			$rq = $_REQUEST;

 			$this->translate();

 			if ( ( isset( $rq['action'] ) && 'save_epanel' == $rq['action'] ) || ( isset( $rq['page'] ) && in_array( $rq['page'], [ 'et_divi_options', 'et_extra_options' ] ) ) )
 					add_action( 'admin_init', [ $this, 'addSettings' ] );

 		} // end admin

 		/**
 		 * Check the users permission
 		 *
 		 * @since 1.0.1
 		 */
 		private function permission()
 		{

 			global $shortname;

 			$cabs = et_get_option( $shortname . '_ddpl_user_role', false );

 			if ( 'on' != et_get_option( $shortname . '_ddpl_user_check', false ) )
 			{

 				if ( ( false === $cabs || '' === $cabs ) && current_user_can( 'administrator' ) )
 					return true;

 				else
 				{

 					$roles = explode( ',', esc_attr( str_replace( ' ', '', $cabs ) ) );

 					foreach ( $roles as $role )
 					{

 						if ( '' == $role )
 							continue;

 						if ( current_user_can( $role ) )
 						{

 							$bool = true;

 							break;

 						} // end if
 					} // end foreach

 					if ( isset( $bool ) )
 						return true;

 				} // end else
 			} // end if

 			return false;

 		} // end permission

 		/**
 		 * Enqueue styles and scripts
 		 *
 		 * @since 1.0.0
 		 */
 		public function enqueue()
 		{

 			if ( $this->permission() )
 				return;

 			if ( is_admin() )
 			{

 				global $typenow, $pagenow;

 				if ( 'edit.php' != $pagenow && in_array( $typenow, et_builder_get_builder_post_types() ) )
 				{

 					global $post;

 					if ( $post )
 					{

 						$this->checkLayouts();

 						$this->addFiles( self::PREFIX . '-admin' );

 					} // end if
 				} // end if
 			} // end if

 			else
 			{

 				$this->checkLayouts();

 				$this->addFiles( self::PREFIX . '-vb' );

 			} // end else
 		} // end enqueue

 		/**
 		 * Add the files
 		 *
 		 * @since 1.0.5
 		 */
 		private function checkLayouts()
 		{

 			$existLayout = get_posts(
 			[

 				'post_type'   => 'et_pb_layout',
 				'post_status' => 'publish',
 				'showposts'   => 1,
 				'tax_query'   =>

 				[
 					[

 					'taxonomy' => 'layout_type',
 					'terms'    => 'layout',
 					'field'    => 'slug',

 					]
 				]]
 			);

 			if ( count( $existLayout ) < 1 ) :

 				if ( is_admin() ) :

 					add_filter('admin_body_class', function ()
 					{

 					    return 'no-et-layouts';

 					}); // end add_filter

 				else :

 					add_filter('body_class', function ( $classes )
 					{

 						$classes[] = 'no-et-layouts';

 					    return $classes;

 					}); // end add_filter

 				endif;

 			endif;

 		} // end checkLayouts

 		/**
 		 * Add the files
 		 *
 		 * @since 1.0.1
 		 */
 		private function addFiles( $slug )
 		{

 			$this->enqueueStyle( $slug );

 			$this->enqueueScript( $slug );

 		} // end addFiles

 		/**
 		 * Enqueue styles
 		 *
 		 * @since 1.0.1
 		 */
 		private function enqueueStyle( $slug )
 		{

 			wp_enqueue_style( $slug, $this->assets .'css/' .  $slug . self::SUFFIX . '.css', [], self::VERSION );

 		} // end enqueueStyle

 		/**
 		 * Enqueue scripts
 		 *
 		 * @since 1.0.1
 		 */
 		private function enqueueScript( $slug, $dep = [ 'jquery' ], $footer = true )
 		{

 			wp_enqueue_script( $slug, $this->assets . 'js/' . $slug . self::SUFFIX . '.js', $dep, self::VERSION, $footer );

 		} // end enqueueScript

 		/**
 		 * Adjust options array
 		 *
 		 * @since 1.0.0
 		 */
 		public function addSettings()
 		{

 			if ( ! current_user_can( 'administrator' ) && ! $this->permission() )
 				return;

 			global $shortname, $options;

 			$pos   = 0;

 			$found = false;

 			foreach ( $options as $key )
 			{

 			   if ( isset( $key['id'] ) && $shortname . '_smooth_scroll' == $key['id'] )
 			   {

 			   		$found = true;

 					break;

 			   } // end if

 			   $pos++;

 			} // end if

 			if ( $found && $pos > 0 )
 			{

 				$array1 = array_slice( $options, 0, $pos + 1 );
 				$array2 = array_slice( $options, $pos + 1 );

 				$settings =
 				[

 					[], // fix to display the settings properly

 					[

 						'name' => esc_html__( 'Layouts Disable User Check', self::DOMAIN ),
 						'id' => $shortname . '_ddpl_user_check',
 						'type' => 'checkbox',
 						'std' => 'true',
 						'desc' => esc_html__( 'Here you can specify if the user check is disabled. Then the predefined layouts are not displayed for any user of this page.', self::DOMAIN ),

 					],

 					[

 						'name' => esc_html__( 'Layouts Change User Role', self::DOMAIN ),
 						'id' => $shortname . '_ddpl_user_role',
 						'type' => 'text',
 						'std' => 'administrator',
 						'desc' => esc_html__( 'Here you can define with which user role ( hierarchical, e.g. \'editor\', \'author\' etc. ) or which capability ( \'current_user_can( \'$cab\' ) ) users can access the predefined layouts. Multiple roles or permissions can be entered, separated by commas. The default is set to \'administrator\'.', self::DOMAIN ),
 						'validation_type' => 'nohtml',

 					],

 				];
 			} // end if

 			$this->setOptions( array_merge( $array1, $settings, $array2 ) );

 		} // end addSettings

 		/**
 		 * Set the global setting-options
 		 *
 		 * @since 1.0.0
 		 */
 		private function setOptions( $settings )
 		{

 			global $options;

 			$options = $settings;

 		} // end setOptions

 		/**
 		 * Add admin notice
 		 *
 		 * @since 1.0.0
 		 */
 		private function adminNotice()
 		{

 			add_action( 'admin_notices', function()
 			{

 				echo '<div class="error"><p>' . implode( ' ', $this->notice ) . '</p></div>';

 			}); // end add_action
 		} // end adminNotice

 		/**
 		 * Load the textdomains
 		 *
 		 * @since 1.0.0
 		 */
 		private function translate()
 		{

 			$locale = apply_filters( self::PREFIX . '_translation_locale', get_locale(), self::DOMAIN );

 			load_textdomain( self::DOMAIN, WP_LANG_DIR . '/plugins/' . self::DOMAIN . '-' . $locale . '.mo' );

 			load_plugin_textdomain( self::DOMAIN, false, basename( dirname( self::FILE ) ) . '/assets/lang/' );

 		} // end translate
 	} // end class
 } // end if

 new ddplPluginInitialize;
