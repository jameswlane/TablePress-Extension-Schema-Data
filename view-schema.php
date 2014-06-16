<?php
/**
 * TablesPress Schema Data View
 *
 * @package TablePress
 * @subpackage TablesPress Schema Data View
 * @author James W. Lane
 * @since 1.0.0
 */

// Prohibit direct script loading
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );

/**
 * TablesPress Schema Data View class
 *
 * @since 1.0.0
 */
class TablePress_Schema_View extends TablePress_View {

	/**
	 * Set up the view with data and do things that are specific for this view
	 *
	 * @since 1.0.0
	 *
	 * @param string $action Action for this view
	 * @param array $data Data for this view
	 */
	public function setup( $action, array $data ) {
		parent::setup( $action, $data );

		$params = array(
			'option_name' => 'tablepress_schema',
			'default_value' => array()
		);
		$this->auto_import_config = TablePress::load_class( 'TablePress_WP_Option', 'class-wp_option.php', 'classes', $params );

		$this->add_meta_box( 'tables-schema-data', 'Schema Data', array( $this, 'postbox_schema_data' ), 'additional' );

		$this->process_action_messages( array(
			'error_auto_import' => 'Error: The Auto Import configuration could not be saved.',
			'success_auto_import' => 'The Auto Import configuration was saved successfully.'
		) );

	}

	/**
	 * Print the form for the Auto Update tables list
	 *
	 * @since 1.0.0
	 */
	public function postbox_schema_data( $data, $box ) { ?>

	<span>Hello World</span>
	<span>Hello World</span>


<?php	}

} // class TablePress_Schema_View