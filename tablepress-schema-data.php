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

		// @TODO Need to build this hook once I figure out how I am saving data
		// Register hook to remove Schema Data, when the plugin is deactivated
		//register_deactivation_hook( __FILE__, array( __CLASS__, 'deactivation_hook' ) );

		// @TODO Need to build this once we get data to save
		// Adding a filter to tablepress_cell_css_class in class-render.php
		// add_filter( '', array( __CLASS__, '' ) );
		// add_action( '', array( __CLASS__, '' ) );

		// Load the Schema Data View
		if ( is_admin() && ! ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
			add_action( 'tablepress_run', array( $this, 'run' ) );
		}
	}

	/**
	 *
	 * @TODO Will build this once I figure out a solid way to save data.
	 *
	 * Remove Schema Data on plugin deactivation, and delete options
	 *
	 * @since 1.0.0
	 */
	//public static function deactivation_hook() {
	//	delete_option( 'tablepress_schema_data' );
	//}

	/**
	 * Start-up the TablePress Schema Data Controller, which is run when TablePress is run
	 *
	 * @since 1.0.0
	 */
	public function run() {

		// Change View Edit file path, to load extended view
		add_filter( 'tablepress_load_file_full_path', array( $this, 'change_edit_view_full_path' ), 10, 3 );

		// Change View Edit class name, to load extended view
		add_filter( 'tablepress_load_class_name', array( $this, 'change_view_edit_class_name' ) );

		//
		add_action( 'admin_post_tablepress_import', array( $this, 'handle_post_action_auto_import' ), 9 ); // do this before intended TablePress method is called, to be able to remove the action
	}

	/**
	 * Change View Edit file path, to load extended view
	 *
	 * @since 1.0.0
	 */
	public function change_edit_view_full_path( $full_path, $file, $folder ) {
		if ( 'view-edit.php' == $file ) {
			require_once $full_path; // load desired file first, as we derive from it in the new $full_path file
			$full_path = plugin_dir_path( __FILE__ ) . 'view-schema-data.php';
		}
		return $full_path;
	}

	/**
	 * Change View Import class name, to load extended view
	 *
	 * @since 1.0.0
	 */
	public function change_view_edit_class_name( $class ) {
		if ( 'TablePress_Edit_View' == $class ) {
			$class = 'TablePress_Schema_Data_View';
		}
		return $class;
	}

	/**
	 * Save Schema Data to table.
	 *
	 * @since 1.0.0
	 */
	public function handle_post_action_auto_import() {
		if ( ! isset( $_POST['submit_schema_data'] ) ) {
			return;
		}

		// remove TablePress Import action handling
		remove_action( 'admin_post_tablepress_import', array( TablePress::$controller, 'handle_post_action_edit' ) );

		TablePress::check_nonce( 'edit', $edit_table['id'], 'nonce-edit-table' );

		if ( ! current_user_can( 'tablepress_edit_table', $edit_table['id'] ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.', 'default' ) );
		}

		if ( empty( $_POST['schema_data'] ) || ! is_array( $_POST['schema_data'] ) ) {
			TablePress::redirect( array( 'action' => 'import', 'message' => 'error_schema_data' ) );
		} else {
			$auto_import = stripslashes_deep( $_POST['schema_data'] );
		}

		$params = array(
			'option_name' => 'tablepress_schema_data',
			'default_value' => array()
		);

		$schema_data = TablePress::load_class( 'TablePress_WP_Option', 'class-wp_option.php', 'classes', $params );

		$result = $schema_data->update( $config );

		TablePress::redirect( array( 'action' => 'import', 'message' => 'success_schema_data' ) );
	}

} // end class

// Bootstrap, instantiates the plugin
new TablePress_Schema_Data;