<?php
/**
 * TablesPress Render Schema Data class
 *
 * @package TablePress
 * @subpackage TablesPress Schema Data View
 * @author James W. Lane
 *
 * @since 1.0.0
 */

// Prohibit direct script loading
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );

class TablePress_Render_Schema_Data extends TablePress_Render {

	/**
	 * We hook into the tablepress_cell_tag_attributes and tablepress_row_tag_attributes to add the Schema Data
	 *
	 * @since 1.1.0
	 */
	public function __construct() {
		add_filter('tablepress_cell_tag_attributes', array( __CLASS__, 'tag_custom_attr' ), 10, 7  );
		add_filter('tablepress_row_tag_attributes', array( __CLASS__, 'tr_custom_attr' ), 10, 4  );
	}

	// <td> Schema Data is generated here

    public function tag_custom_attr( $tag_attributes, $table_id, $cell_content, $row_idx, $col_idx, $colspan_row, $rowspan_col ) {
		$value = array();

		// Check to see if we are in a header column
		if ( 1 !== $row_idx ){
			global $wpdb;
			$schema_data = unserialize( $wpdb->get_var( "SELECT schema_data FROM {$wpdb->prefix}tablepress_schema_data WHERE table_id = $table_id" ) );
			// Check to make sure a itemprop value has been set
			if ( !empty( $schema_data[$col_idx] ) ){
				$value = array( 'itemprop' => $schema_data[$col_idx] );
			}
		}
		return $value;
	}

	// <tr> Schema Data is generated here
    public function tr_custom_attr( $tr_attributes, $table_id, $row_idx, $row_data ) {
		$value = array();

		// Check to see if we are in a header column
		if ( 1 !== $row_idx ){
			global $wpdb;
			$schema_data = unserialize( $wpdb->get_var( "SELECT schema_data FROM {$wpdb->prefix}tablepress_schema_data WHERE table_id = $table_id" ) );

			// Check to make sure a itemtype value has been set
			if ( !empty( $schema_data[0] ) ){
				$schema_data_itemtype = $schema_data[0];
				$value = array( 'itemscope itemtype' => $schema_data_itemtype );
			}
		}
		return $value;
	}

} // class TablePress_Render_Schema_Data