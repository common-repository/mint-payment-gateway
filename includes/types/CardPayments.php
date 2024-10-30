<?php namespace mintpayments\gateway\types; ?>
<?php  
if ( ! defined( 'ABSPATH' ) ) exit;
require_once(__DIR__.'/CustomType.php');
/***
* Title			: Card Payments CPT
* Description	: captures card payments callback response and save it as post
*
***/
class CardPayments extends CustomType{
	
	
	/***
	*@Override
	**/
	public function init(){
		parent::init();
		remove_post_type_support($this->getName(), 'editor');
	}
	
	public function getName() :string{
		return "mint-payments";
	}
	
	public function options() :array{
		return ['public'		=> false,
				'publicly_queryable' => false,
				'exclude_from_search' => true,
				'show_ui' => true,
				'show_in_menu' =>true,
				'query_var' => true,
				'menu_icon' => 'dashicons-editor-table', /* the icon for the custom post type menu. uses built-in dashicons (CSS class name) */
				'rewrite'	=> array( 'slug' => 'm-payments', 'with_front' => false ), /* you can specify its url slug */
				'has_archive' => false, /* you can rename the slug here */
				'capability_type' => 'post',
				 'capabilities' => array(
					'create_posts' => 'do_not_allow'
				),
				'hierarchical' => false,
				];
	}
	
	public function supports(): array{
		return array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'revisions', 'sticky');
		//disable edits;
		//return array( 'title',  'author', 'thumbnail', 'excerpt', 'revisions', 'sticky');
	}
	
	public function labels() : array{
	return 	
			array(
			'name' => __('Card Payments', 'jointswp'), /* This is the Title of the Group */
			'singular_name' => __('Payment', 'jointswp'), /* This is the individual type */
			'all_items' => __('Card Payments', 'jointswp'), /* the all items menu item */
			'add_new' => __('Add New', 'jointswp'), /* The add new menu item */
			'add_new_item' => __('Add New Payment', 'jointswp'), /* Add New Display Title */
			'edit' => __( 'Edit', 'jointswp' ), /* Edit Dialog */
			'edit_item' => __('Edit Payment', 'jointswp'), /* Edit Display Title */
			'new_item' => __('New Payment', 'jointswp'), /* New Display Title */
			'view_item' => __('View Payment', 'jointswp'), /* View Display Title */
			'search_items' => __('Search Payment', 'jointswp'), /* Search Custom Type Title */ 
			'not_found' =>  __('Nothing found in the Database.', 'jointswp'), /* This displays if there are no entries yet */ 
			'not_found_in_trash' => __('Nothing found in Trash', 'jointswp'), /* This displays if there is nothing in the trash */
			'parent_item_colon' => ''
			)
		;
	}
	
	/****OBJECT PROPERTIES HERE****/
	
	protected $fields;
	protected $privateKey=null;
	
	
	const PROP_ORDERID = 'order_id';
	const PROP_RECEIPTNUMBER='receipt_number';
	const PROP_MESSAGE = 'message';
	const PROP_TOTAL = 'total_amount';
	const PROP_REFERENCE = 'reference';
	const PROP_CARD_BRAND = 'card_brand';
	const PROP_CARD_EXPIRY = 'card_expiry';
	const PROP_CARD_NUMBER = 'card_number';
	
	const PROP_CARD_CURRENCY = 'card_currency';
	const PROP_STATUS = 'status';
	
	
	
	
	
	
	


	public static function fromRequest(){
		$me = new CardPayments();
		$me->fields[self::PROP_ORDERID] = sanitize_text_field($_REQUEST['reference1']);
		$me->fields[self::PROP_REFERENCE] = sanitize_text_field($_REQUEST['reference2']);
		$me->fields[self::PROP_RECEIPTNUMBER] = sanitize_text_field($_REQUEST['receiptNumber']);
		$me->fields[self::PROP_MESSAGE]= sanitize_text_field($_REQUEST['errorMessage']);
		$me->fields[self::PROP_TOTAL] = sanitize_text_field($_REQUEST['totalTransactionAmount']);
		$me->fields[self::PROP_CARD_BRAND]=sanitize_text_field($_REQUEST['cardBrand']);
		$me->fields[self::PROP_CARD_EXPIRY]=sanitize_text_field($_REQUEST['cardExpiry']);
		$me->fields[self::PROP_CARD_NUMBER]=sanitize_text_field($_REQUEST['cardNumber']);
		$me->fields[self::PROP_CARD_CURRENCY]=sanitize_text_field($_REQUEST['cardCurrency']);
		$me->fields[self::PROP_STATUS]=strtoupper(sanitize_text_field($_REQUEST['transactionStatus']));
		
		return $me;
	}
	
	
	public static function fromResponse($oid,$jres){
		$me = new CardPayments();
		$me->fields[self::PROP_ORDERID] = $oid;
		$me->fields[self::PROP_REFERENCE] = '';
		$me->fields[self::PROP_RECEIPTNUMBER] = $jres['purchase']['purchase_reference'];
		$me->fields[self::PROP_MESSAGE]= $jres['response_message'];
		$me->fields[self::PROP_TOTAL] = $jres['purchase']['amount'];
		$me->fields[self::PROP_CARD_BRAND]=$jres['card']['brand'];
		$me->fields[self::PROP_CARD_EXPIRY]=$jres['card']['expiry_month'].$jres['card']['expiry_year'];
		$me->fields[self::PROP_CARD_NUMBER]=$jres['card']['holder_name'];
		$me->fields[self::PROP_CARD_CURRENCY]=$jres['purchase']['currency'];
		$me->fields[self::PROP_STATUS]=$jres['purchase']['status'];
		
		return $me;
	}
	public function withKey($key){
		if(strlen($key)>2){
			//dont assign if the key is too small
			$this->privateKey = $key;
		}
		return $this;
	}
	
	private function valid(){
		$last4 = substr($me->fields[self::PROP_CARD_NUMBER],-4,4);
		$str = sprintf('%s:%s:%s:%s'
							,$me->fields[self::PROP_ORDERID]
							,$me->fields[self::PROP_TOTAL]
							,$me->fields[self::PROP_STATUS]
							,$last4);
		return base64_encode(hash_hmac('sha256',$str,$this->privateKey,true));
	}
	
	public function save(){
		if($this->privateKey!=null && !$me->valid()){
			return 0;
		}
		$id = wp_insert_post( 
						array('post_title' => $this->fields[self::PROP_ORDERID],
						'post_content' => '',
						'post_status' => 'publish',
						'post_type' => $this->getName()));
						
	
		foreach($this->fields as $key => $data) {
			update_post_meta( $id, $key, $data );
		}
		//save meta to WooCommerce Orders, so it will be available for export
		update_post_meta($this->fields[self::PROP_ORDERID],self::PROP_RECEIPTNUMBER,$this->fields[self::PROP_RECEIPTNUMBER]);
	}
}
$cardtype= new CardPayments();
add_action('init',array($cardtype,'init'));
?>