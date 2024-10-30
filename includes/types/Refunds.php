<?php namespace mintpayments\gateway\types; ?>
<?php  
if ( ! defined( 'ABSPATH' ) ) exit;
require_once(__DIR__.'/CustomType.php');
/***
* Title			: Refunds CPT
* Description	: captures card payments callback response and save it as post
*
***/
class CardRefunds extends CustomType{
	
	
	/***
	*@Override
	**/
	public function init(){
		parent::init();
		remove_post_type_support($this->getName(), 'editor');
	}
	
	public function getName() :string{
		return "mint-refunds";
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
			'name' => __('Refunds', 'jointswp'), /* This is the Title of the Group */
			'singular_name' => __('Refund', 'jointswp'), /* This is the individual type */
			'all_items' => __('Refunds', 'jointswp'), /* the all items menu item */
			'add_new' => __('Add New', 'jointswp'), /* The add new menu item */
			'add_new_item' => __('Add New Refund', 'jointswp'), /* Add New Display Title */
			'edit' => __( 'Edit', 'jointswp' ), /* Edit Dialog */
			'edit_item' => __('Edit Refund', 'jointswp'), /* Edit Display Title */
			'new_item' => __('New Refund', 'jointswp'), /* New Display Title */
			'view_item' => __('View Refund', 'jointswp'), /* View Display Title */
			'search_items' => __('Search Refund', 'jointswp'), /* Search Custom Type Title */ 
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
		$me = new CardRefunds();
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
	}
}
$cardtype= new CardRefunds();
add_action('init',array($cardtype,'init'));
?>