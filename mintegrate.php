<?php 
ob_start();
/**
 * Plugin Name: Mint HPP Payment Gateway
 * Plugin URI: https://www.mintpayments.com/
 * Description: Mint Payment Gateway for WooCommerce
 * Version: 1.0.1
 * Requires at least: 5.0
 * Tested up to: 6.4.3
 * Requires PHP: 5.6
 * Author: developer@mintpayments.com
 * Author URI: https://developers.mintpayments.com/
 * License:    GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

 

define('MINTHPP_MODULE' , dirname( __FILE__ )  );
$pathinfo = pathinfo( dirname( plugin_basename( __FILE__ ) ) );
if ( !defined( 'MINTHPP_PLUGIN_NAME' ) ) define( 'MINTHPP_PLUGIN_NAME', $pathinfo['filename'] );
if ( !defined( 'MINTHPP_PLUGIN_URL' ) ) define( 'MINTHPP_PLUGIN_URL', plugins_url(MINTHPP_PLUGIN_NAME) . '/' );

require_once(__DIR__ .'/includes/types/index.php');
require_once(__DIR__ .'/includes/shortcodes/index.php');
require_once(__DIR__ .'/includes/common/index.php');
require_once(__DIR__ .'/includes/listtables/index.php');		
add_action( 'plugins_loaded', 'init_minthpp' );
function init_minthpp(){
	
	 class MintHppGateway extends WC_Payment_Gateway{

		 
		
		const _ICON=MINTHPP_PLUGIN_URL."assets/img/cards.png";
		
		//TODO:get this from settings page
		const _APPID= "minthpp";
		const _TITLE = "Mint HPP";
		const _DESC = "Accept credit card payments thru mint";
		const PROP_COMPANY_TOKEN="company_token";
		
		public function __construct(){
			add_action('woocommerce_api_'.strtolower(get_class($this)), array(&$this, 'onResponseReceived'));
			//add_action('mintpayments_hpp_on_response_received','handle_response'); //this is just a test, this will be used by client
			
			$this->init_wc_defaults();
			
		}
		
		
		
		
		private  function init_wc_defaults(){
			$this->id=self::_APPID;
			$this->icon =self::_ICON;
			$this->has_fields=false;
			$this->method_title=self::_TITLE;
			
			$this->supports = array(
			  'products',
			  'refunds'
			);
			
			//
			
			$this->init_form_fields();
			$this->init_settings();
			$this->title = $this->get_option( 'title' );
			$this->description = $this->get_option( 'description' );
			$this->method_description=$this->get_option( 'description' );
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );

			
			
		}
		
		public  function init_form_fields(){
				$this->form_fields = array(
					'title' => array(
						'title'       => 'Title',
						'type'        => 'text',
						'description' => 'This controls the title which the user sees during checkout.',
						'default'     => '',
						'desc_tip'    => true
					),
					'description' => array(
						'title'       => 'Description',
						'type'        => 'textarea',
						'description' => 'This controls the description which the user sees during checkout.',
						'default'     => 'Pay with your credit card via our  payment gateway.'
					),
					'testmode' => array(
						'title'       => 'Test Mode',
						'type'        => 'checkbox',
						'description' => 'Test Mode.',
						'desc_tip'    => true,
						'default'     => 'yes'
					),
					'testtoken' => array(
						'title'       => 'Test Token',
						'type'        => 'text',
						'description' => 'The test company token provided by Mint as seen on the Merchant Portal',
						'default'  => $this->get_option('testtoken','V4e1Z8jmCpVrV19qXIWf62doqohM8UQ')
						
					),
					'prodtoken' => array(
						'title'       => 'Prod Token',
						'type'        => 'text',
						'description' => 'The prod company token provided by Mint as seen on the Merchant Portal',
						'default'  => 'test'
					)
					
				);
				
		}

		
		

		/***
		*@Overrides WooCommerce:process_payment
		**/
		public function process_payment( $order_id ) {
			$_SESSION['order_id'] =$order_id;
			
			return array(
				'result' => 'success',
				'redirect' => \mintpayments\gateway\common\HppForm::forContext($this)
									->withOrder($order_id)
									->load()
									->toString()
			);
		}
		
		/***
		*@Overrides WooCommerce:process_refund
		**/
		public function process_refund($order_id, $amount = NULL, $reason = '' ) {
		  // Do your refund here. Refund $amount for the order with ID $order_id
		  
		  $ret = \mintpayments\gateway\common\MpayTransaction::forContext($this)
								->withOrder($order_id)
								->withAmount($amount)
								->refund();
		  return $ret['state']==\mintpayments\gateway\common\PaymentStatus::APPROVED;
		}
		
		public function can_refund_order($order){
			return true;
		}
		
		public function onResponseReceived(){
			
			$ret = \mintpayments\gateway\common\HppForm::forContext($this)
					->onResponseReceived();
			if($ret['status']=='APPROVED'){
				wp_redirect( $this->get_return_url(  ) );
			}else{
				wp_redirect($ret['order']->get_cancel_order_url());
			}
			//wp_redirect( $this->get_return_url( $this->order ) );
			
			//wp_redirect($this->get_cancel_order_url());
			exit();	
			
		}
		
	

	}
	
	/***
	*	function to register this plugin as a WC payment gateway
	***/
	function attach_mintpayments( $methods ) {
		
		$methods[] = 'MintHppGateway'; 
		return $methods;
	}
	add_filter( 'woocommerce_payment_gateways','attach_mintpayments' );
	
}


ob_end_clean();

?>