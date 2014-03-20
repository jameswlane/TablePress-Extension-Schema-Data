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
	 * TablePress Extension: Schema Data version
	 *
	 * Increases whenever a new plugin version is released
	 *
	 * @since 1.0.0
	 *
	 * @const string
	 */
	const PLUGIN_VERSION = '1.0.0';

	/**
	 * TablePress Extension: Schema Data internal plugin version ("options scheme" version)
	 *
	 * Increases whenever the scheme for the plugin options changes, or on a plugin update
	 *
	 * @since 1.0.0
	 *
	 * @const int
	 */
	const OPTIONS_DB_VERSION = 1;

	/**
	 * TablePress Extension: Schema Data "table scheme" (data format structure) version
	 *
	 * Increases whenever the scheme for a $table changes,
	 * used to be able to update plugin options and table scheme independently
	 *
	 * @since 1.0.0
	 *
	 * @const int
	 */
	const SCHEMA_DB_VERSION = 1;


	/**
	 * Constructor function, called when plugin is loaded
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// Register hook to add Schema Data tables, when the plugin is activates
		register_activation_hook( __FILE__, array( __CLASS__, 'activation_hook' ) );

		// @TODO: Need to build this hook once I figure out how I am saving data
		// Register hook to remove Schema Data, when the plugin is deactivated
		register_deactivation_hook( __FILE__, array( __CLASS__, 'deactivation_hook' ) );

		// update check, in all controllers (frontend and admin), to make sure we always have up-to-date options
		$this->plugin_update_check(); // should be done very early

		// @TODO: Need to build this once we get data to save
		// Adding a filter to tablepress_cell_css_class in class-render.php
		// add_filter( '', array( __CLASS__, '' ) );
		// add_action( '', array( __CLASS__, '' ) );

		// Load the Schema Data View
		add_action( 'tablepress_run', array( $this, 'run' ) );
	}


	/**
	 *
	 * @TODO: Will build this once I figure out a solid way to save data.
	 *
	 * Remove Schema Data on plugin deactivation, and delete options
	 *
	 * @since 1.0.0
	 */
	public static function activation_hook() {
		global $wpdb;
		$table_name = $wpdb->prefix . "tablepress_schema_data";
		$sql = "CREATE TABLE $table_name (table_id bigint(20) NOT NULL,	schema_data varchar(1000) NOT NULL);";
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
		add_option( "tablepress_db_version", self::SCHEMA_DB_VERSION );
	}

	/**
	 *
	 * @TODO: Will build this once I figure out a solid way to save data.
	 *
	 * Remove Schema Data on plugin deactivation, and delete options
	 *
	 * @since 1.0.0
	 */
	public static function deactivation_hook() {
		delete_option( 'tablepress_db_version' );
	}

	/**
	 * Check if the plugin was updated and perform necessary actions, like updating the options
	 *
	 * @since 1.0.0
	 */
	protected function plugin_update_check() {

		$schema_db_ver = get_option( 'tablepress_db_version' );

		if( $schema_db_ver != self::SCHEMA_DB_VERSION ) {
			global $wpdb;
			$table_name = $wpdb->prefix . "tablepress_schema_data";
			$sql = "CREATE TABLE $table_name (table_id bigint(20) NOT NULL,	schema_data varchar(1000) NOT NULL);";
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql );
			update_option( "tablepress_db_version", self::SCHEMA_DB_VERSION );
		}

	}

	/**
	 * Start-up the TablePress Schema Data Controller, which is run when TablePress is run
	 *
	 * @since 1.0.0
	 */
	public function run() {
		add_filter('tablepress_admin_view_actions', array( __CLASS__, 'tablepress_schema_data_tab' ), 10, 1  );
		add_filter( 'tablepress_load_file_full_path', array( $this, 'tablepress_schema_full_path' ), 10, 3 );

		add_filter( 'tablepress_load_file_full_path', array( $this, 'render_full_path' ), 10, 3 );
		add_filter( 'tablepress_load_class_name', array( $this, 'render_class_name' ) );


		// Change View Edit file path, to load extended view
		// Change View Edit class name, to load extended view
		add_filter( 'tablepress_load_file_full_path', array( $this, 'change_edit_view_full_path' ), 10, 3 );
		add_filter( 'tablepress_load_class_name', array( $this, 'change_view_edit_class_name' ) );

		//
		add_action( 'admin_post_tablepress_edit', array( $this, 'handle_post_action_schema_data' ), 9 ); // do this before intended TablePress method is called, to be able to remove the action


	}

    public function tablepress_schema_data_tab( $data ) {
		$data['schema'] = array(
				'show_entry' => true,
				'page_title' => __( 'Schema Data', 'tpsd' ),
				'admin_menu_title' => __( 'Schema Data', 'tpsd' ),
				'nav_tab_title' => __( 'Schema Data', 'tpsd' ),
				'required_cap' => 'tablepress_edit_tables'
			);
		return $data;
	}

	public function tablepress_schema_full_path( $full_path, $file, $folder ) {
		if ( 'view-schema.php' == $file ) {
			$full_path = plugin_dir_path( __FILE__ ) . 'view-schema.php';
		}
		return $full_path;
	}





	public function render_full_path( $full_path, $file, $folder ) {
		if ( 'class-render.php' == $file ) {
			require_once $full_path; // load desired file first, as we derive from it in the new $full_path file
			$full_path = plugin_dir_path( __FILE__ ) . 'render-schema-data.php';
		}
		return $full_path;
	}
	public function render_class_name( $class ) {
		if ( 'TablePress_Render' == $class ) {
			$class = 'TablePress_Render_Schema_Data';
		}
		return $class;
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
	public function handle_post_action_schema_data() {
		if ( ! isset( $_POST['submit_schema_data'] ) ) {
			echo 'Houston we have a problem!';
		}
		// remove TablePress Import action handling
		remove_action( 'admin_post_tablepress_edit', array( TablePress::$controller, 'handle_post_action_edit' ) );

		$table = $_POST['table'];
		$id = $_POST['table']['id'];
		$edit_table = wp_unslash( $_POST['table'] );
		TablePress::check_nonce( 'edit', $edit_table['id'], 'nonce-edit-table' );
		if ( ! current_user_can( 'tablepress_edit_table', $edit_table['id'] ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.', 'default' ) );
		}
		$test_id = $_POST['schema']['data']['id'];
		$test_col = $_POST['schema']['data']['columns'];
		$columns = $test_col;
		$schemadata_fields = array( );
		for ( $col_idx = 0; $col_idx < $columns; $col_idx++ ) {
			$data = $_POST['schema'][$id][$col_idx];
			$schemadata_fields[$col_idx] = $data;
		}
		$schemadata_fields = serialize($schemadata_fields);
		global $wpdb;

		$schema_db_data = $wpdb->get_var("SELECT schema_data FROM {$wpdb->prefix}tablepress_schema_data WHERE table_id = $id");

		if ( empty( $schema_db_data ) &&  !empty( $schemadata_fields ) ){
			$wpdb->insert(
				"{$wpdb->prefix}tablepress_schema_data",
				array(
					'table_id' => $id,
					'schema_data' => $schemadata_fields
				),
				array(
					'%d',
					'%s'
				)
			);
		}elseif ( !empty( $schema_db_data ) &&  !empty( $schemadata_fields ) ){
				$wpdb->update(
					"{$wpdb->prefix}tablepress_schema_data",
					array(
						'table_id' => $id,
						'schema_data' => $schemadata_fields
					),
					array( 'table_id' => $id ),
					array(
						'%d',
						'%s'
					)
				);
		}elseif ( empty( $schema_db_data ) &&  empty( $schemadata_fields ) ){

			TablePress::redirect( array( 'action' => 'import', 'message' => 'error_schema_data' ) );

		}

		TablePress::redirect( array( 'action' => 'edit', 'table_id' => $id, 'message' => 'success_schema_data' ) );

	}

	// <td> Schema Data is generated here

} // end class

// Bootstrap, instantiates the plugin
new TablePress_Schema_Data;