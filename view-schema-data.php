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
class TablePress_Schema_Data_View extends TablePress_Edit_View {

	protected $schema_data;

	/**
	 * Set up the view with data and do things that are specific for this view
	 *
	 * @since 1.0.0
	 *
	 * @param string $action Action for this view
	 * @param array $data Data for this view
	 */
	public function setup( $action, array $data ) {
		$params = array(
			'option_name' => 'tablepress_schema_data',
			'default_value' => array()
		);
		$this->schema_data = TablePress::load_class( 'TablePress_WP_Option', 'class-wp_option.php', 'classes', $params );
		parent::setup( $action, $data );
		$this->add_meta_box( 'tablepress-schema-data', 'Schema Data', array( $this, 'postbox_schema_data' ), 'additional' );
		$this->process_action_messages( array(
			'error_schema_data' => 'Error: Your tables Schema data could not be saved.',
			'success_schema_data' => 'Your tables Schema data was saved successfully.'
		) );
	}

	/**
	 * Print the form for the Schema Data
	 *
	 * @since 1.0.0
	 */
	public function postbox_schema_data( $data, $box ) {
		$id = $data['table']['id'];
		$table = $data['table']['data'];
		$options = $data['table']['options'];
		$rows = count( $table );
		$columns = count( $table[0] );
		echo '<table class="widefat" cellspacing="0">' . "\n";
		echo '<tr id="">' . "\n";
		echo '<th>Itemtype</th>' . "\n";
		foreach ( $table[0] as $itm_typ => $item_prop ) {
			$item_prop = esc_textarea( $item_prop ); // sanitize, so that HTML is possible in table cells
			echo "\t\t\t" . '<th class="head">' . $item_prop . ' Itemprop</th>' . "\n";
		}
		echo '</tr>' . "\n";
		echo '</thead>' . "\n";
		echo '<tbody id="">' . "\n";
		echo "\t\t<tr>\n";
		for ( $col_idx = 0; $col_idx < $columns; $col_idx++ ) {
			//Build Our Header
			$data_id = $id . '-' . $col_idx;
			echo '<td><input type="text" id="' . $data_id . '" class="" name="" value="' . esc_attr( $options[$data_id] ) . '" /></td>';
		}
		echo "\t\t" . '</tr>' . "\n";
		echo '</tbody>' . "\n";
		echo '</table>' . "\n";
		echo '<input type="hidden" id="number-rows" name="table[number][rows]" value="' . $rows . '" />' . "\n";
		echo '<input type="hidden" id="number-columns" name="table[number][columns]" value="' . $columns . '" />';
		echo '<input type="submit" value="Save Schema Data" class="button button-large submit_schema_data" name="submit_schema_data" />';

		echo '<pre>' . "\n";
		print_r($options);
		echo '</pre>' . "\n";


	}
} // class TablePress_Schema_Data_View