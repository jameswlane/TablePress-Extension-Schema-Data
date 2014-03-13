<?php
/*
Plugin Name: TablePress Extension: Schema Data
Plugin URI: http://tablepress.org/extensions/
Description: Extension for TablePress to allow adding Schema.org data to tables
Version: 1.0.0
Author: James W. Lane
Author URI: http://jameswlane.com/
*/

/**
 * This TablePress extension is base on Table Auto Update extension by Tobias Bäthge.
 *
 * I want to personally thank him for his help on making this plugin possible.
 *
 * http://tablepress.org/extensions/table-auto-import/
 *
 * http://tobias.baethge.com/
 */

/**
 * PHP class that wraps the TablePress Schema Data functionality
 */
class TablePress_Schema_Data {

	/**
	 * Instance of the Table Model
	 *
	 * @var object
	 *
	 * @since 1.0.0
	 */
	protected static $model_table;

	/**
	 * Constructor function, called when plugin is loaded
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		// Register hook to add and remove cron hooks, when the plugin is deactivated
		register_deactivation_hook( __FILE__, array( __CLASS__, 'deactivation_hook' ) );

		// Adding a filter to tablepress_cell_css_class in class-render.php
		// add_filter( '', array( __CLASS__, '' ) );
		// add_action( '', array( __CLASS__, '' ) );


		// Load the Auto Import View
		if ( is_admin() && ! ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
			add_action( 'tablepress_run', array( $this, 'run' ) );
		}
	}

	/**
	 * Clear/Unschedule the cron hook on plugin deactivation, and delete options
	 *
	 * @since 1.0.0
	 */
	public static function deactivation_hook() {
		delete_option( 'tablepress_schema_data' );
	}

	/**
	 * Start-up the TablePress Auto Import Controller, which is run when TablePress is run
	 *
	 * @since 1.0.0
	 */
	public function run() {
		add_filter( 'tablepress_load_file_full_path', array( $this, 'change_import_view_full_path' ), 10, 3 );
		add_filter( 'tablepress_load_class_name', array( $this, 'change_view_import_class_name' ) );
		add_action( 'admin_post_tablepress_import', array( $this, 'handle_post_action_auto_import' ), 9 ); // do this before intended TablePress method is called, to be able to remove the action
	}

	/**
	 * Change View Import file path, to load extended view
	 *
	 * @since 1.0.0
	 */
	public function change_import_view_full_path( $full_path, $file, $folder ) {
		if ( 'view-edit.php' == $file ) {
			require_once $full_path; // load desired file first, as we derive from it in the new $full_path file
			$full_path = plugin_dir_path( __FILE__ ) . 'view-auto-import.php';
		}
		return $full_path;
	}

	/**
	 * Change View Import class name, to load extended view
	 *
	 * @since 1.0.0
	 */
	public function change_view_import_class_name( $class ) {
		if ( 'TablePress_Edit_View' == $class ) {
			$class = 'TablePress_Auto_Import_View';
		}
		return $class;
	}

	/**
	 * Save Auto Import Configuration
	 *
	 * @since 1.0.0
	 */
	public function handle_post_action_auto_import() {
		if ( ! isset( $_POST['submit_auto_import_config'] ) ) {
			return;
		}

		// remove TablePress Import action handling
		remove_action( 'admin_post_tablepress_import', array( TablePress::$controller, 'handle_post_action_import' ) );

		TablePress::check_nonce( 'import' );

		if ( ! current_user_can( 'tablepress_import_tables' ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}

		if ( empty( $_POST['auto_import'] ) || ! is_array( $_POST['auto_import'] ) ) {
			TablePress::redirect( array( 'action' => 'import', 'message' => 'error_auto_import' ) );
		} else {
			$auto_import = stripslashes_deep( $_POST['auto_import'] );
		}

		$params = array(
			'option_name' => 'tablepress_schema_data',
			'default_value' => array()
		);
		$schema_data = TablePress::load_class( 'TablePress_WP_Option', 'class-wp_option.php', 'classes', $params );

		$schedule = isset( $_POST['auto_import_schedule'] ) ? $_POST['auto_import_schedule'] : 'daily';
		$config = array( '#schedule' => $schedule ); // '#' makes sure that this is not overwritten by a table ID, as these can not contain '#'
		foreach ( $auto_import as $table_id => $table ) {
			$table['auto_import'] = ( isset( $table['auto_import'] ) && 'true' == $table['auto_import'] ) ? true : false;
			$table['last_auto_import'] = '-';
			if ( ! isset( $table['source'] ) ) {
				$table['source'] = 'http://';
			}
			if ( ! isset( $table['source_type'] ) ) {
				$table['source_type'] = 'url';
			}
			if ( ! isset( $table['source_format'] ) ) {
				$table['source_format'] = 'csv';
			}
			// Only save things for tables that have changes and not just the default settings
			if ( $table['auto_import'] || 'http://' != $table['source'] || 'url' != $table['source_type'] || 'csv' != $table['source_format'] ) {
				$config[ (string) $table['id'] ] = $table;
			}
		}
		$result = $schema_data->update( $config );

		wp_clear_scheduled_hook( 'tablepress_table_auto_import_hook' );
		if ( ! wp_next_scheduled( 'tablepress_table_auto_import_hook' ) ) {
			wp_schedule_event( time(), $schedule, 'tablepress_table_auto_import_hook' );
		}

		TablePress::redirect( array( 'action' => 'import', 'message' => 'success_auto_import' ) );
	}

} // end class

// Bootstrap, instantiates the plugin
new TablePress_Schema_Data;