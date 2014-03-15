<?php
/**
 * TablesPress Schema Data View class
 *
 * @since 1.0.0
 */

// Prohibit direct script loading
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );

class TablePress_Render_Schema_Data extends TablePress_Render {

	/**
	 * Initialize the Rendering class, include the EvalMath class
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_filter( 'tablepress_table_render_data', array( __CLASS__, 'tablepress_render_data' ), 10, 3 );
		add_filter('tbody_custom_attr', array( __CLASS__, 'tag_custom_attr' ), 10, 7  );
		add_filter('tag_custom_attr', array( __CLASS__, 'tag_custom_attr' ), 10, 7  );
		add_filter('tr_custom_attr', array( __CLASS__, 'tr_custom_attr' ), 10, 4  );
	}

	function tablepress_render_data( $table, $orig_table, $render_options ) {
		return $table;
	}

    public function tbody_custom_attr( $data, $table_id, $cell_content, $row_idx, $col_idx, $colspan_row_idx, $rowspan_col_idx ) {
		$id = $table_id['id'];
		$value = '$id="' . $id . '"$col_idx="' . $col_idx . '" $colspan_row_idx="' . $colspan_row_idx . '"';
		return $value;
	}

    public function tag_custom_attr( $data, $table_id, $cell_content, $row_idx, $col_idx, $colspan_row_idx, $rowspan_col_idx ) {
		$id = $table_id['id'];
		$value = '$id="' . $id . '"$col_idx="' . $col_idx . '" $colspan_row_idx="' . $colspan_row_idx . '"';
		return $value;
	}

    public function tr_custom_attr( $data, $table_id, $row_idx, $table_data_row_idx ) {
		$id = $table_id['id'];
		$value = '$id="' . $id . '"$table_data_row_idx="' . $table_data_row_idx . '"';
		return $value;
	}

} // class TablePress_Schema_Data_View



/*
tbody	id
th		/id	/row
tr		/id	/row
td		id	/row	/column
*/