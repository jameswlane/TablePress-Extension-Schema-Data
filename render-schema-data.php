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
	 * We hook into the tag_custom_attr and tr_custom_attr to add the Schema Data
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_filter('tag_custom_attr', array( __CLASS__, 'tag_custom_attr' ), 10, 7  );
		add_filter('tr_custom_attr', array( __CLASS__, 'tr_custom_attr' ), 10, 4  );
	}

	// <td> Schema Data is generated here
    public function tag_custom_attr( $data, $table_id, $cell_content, $row_idx, $col_idx, $colspan_row_idx, $rowspan_col_idx ) {
		$id = $table_id['id'];
		$value = '';

		// Check to see if we are in a header column
		if ( 1 !== $col_idx ){
			global $wpdb;
			$schema_data = $wpdb->get_var("SELECT schema_data FROM {$wpdb->prefix}tablepress_schema_data WHERE table_id = $id");
			$schema_data = unserialize( $schema_data );

			// Check to make sure a itemprop value has been set
			if ( !empty( $schema_data[$colspan_row_idx] ) ){
				$schema_data_itemprop = $schema_data[$colspan_row_idx];
				$value = 'itemprop="' . $schema_data_itemprop . '"';
			}
		}
		return $value;
	}

	// <tr> Schema Data is generated here
    public function tr_custom_attr( $data, $table_id, $row_idx, $table_data_row_idx ) {
		$id = $table_id['id'];
		$value = '';

		// Check to see if we are in a header column
		if ( 1 !== $table_data_row_idx ){
			global $wpdb;
			$schema_data = $wpdb->get_var("SELECT schema_data FROM {$wpdb->prefix}tablepress_schema_data WHERE table_id = $id");
			$schema_data = unserialize( $schema_data );

			// Check to make sure a itemtype value has been set
			if ( !empty( $schema_data[0] ) ){
				$schema_data_itemtype = $schema_data[0];
				$value = 'itemscope itemtype="' . $schema_data_itemtype . '"';
			}
		}
		return $value;
	}

} // class TablePress_Render_Schema_Data