<?php namespace mintpayments\gateway\listtables; ?>
<?php 

if(!defined("ABSPATH")) exit;
if(!class_exists("CardPaymentsTable")):
	
class CardPaymentsTable {

	function __construct(){
		
	}

	

	function columns($cols){
		unset($cols["date"]);
		unset($cols["author"]);
	    $cols['order_id'] 	= __('Order Number');
	    $cols['receipt_number'] 	= __('Receipt Number');
	    
	    $cols['total_amount'] 	= __('Total Amount');
	    $cols['reference'] 	= __('reference');
	    $cols['card_brand'] 	= __('Card Brand');
	    $cols['card_number']	 		= __("Card Number");
		$cols['card_expiry']	 		= __("Card Expiry Date");
		$cols['card_currency']	 		= __("Card Currency");
		$cols['status']	 		= __("Status");
		$cols['message']	= __("Message");
	    return $cols;
	}

	function sortable($cols){
	    $cols['order_id'] 	= 'order_id';
	    $cols['receipt_number'] 	= 'receipt_number';
	    $cols['total_amount'] 	= 'total_amount';
	    $cols['reference'] 	= 'reference';
		return $cols;
	}

	/*function columnValues($col, $post_id){
		switch($col){
			case "company_name":
			case "contact_name":
			case "order_number":
			case "order_date":
			case "order status":
			case "order_total":
				$col_val = get_field($col, $post_id);
				echo !empty($col_val) ? $col_val : "-"; 
			break;
		}
	}*/
}

$cplt = new CardPaymentsTable();
add_action('edit.php',  'init_lt_card_payments' );
function init_lt_card_payments(){
		error_log("init_lt_card_payments EXECUTED");
	    $screen = get_current_screen();
		error_log(print_r($screen,true));
	    if (!isset($screen->post_type) || 'mint-payments' != $screen->post_type) {
	        return;
	    }
		add_filter( "manage_{$screen->id}_columns", array( $cplt,  'columns' ) );
	    add_filter( "manage_{$screen->id}_sortable_columns", array( $cplt, 'sortable' ) );
	    //add_action( "manage_{$screen->post_type}_posts_custom_column", array( $this, 'columnValues' ), 10, 2 );
	}
endif;

