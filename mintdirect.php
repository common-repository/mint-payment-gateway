<?php 
ob_start();

/**
*	Plugin Name: Mint API Payment Gateway
*	Plugin URI: https://www.mintpayments.com/
*	Description: Mint Payment Gateway for WooCommerce
*	Version: 1.0
*	Requires at least: 5.0
*   Tested up to: 5.7.1
*   Requires PHP: 5.6
*	Author: developer@mintpayments.com
*	Author URI: https://developers.mintpayments.com/
*	License:    GPL v2 or later
*	License URI: https://www.gnu.org/licenses/gpl-2.0.html
**/

 

define('MINTDIRECT_MODULE' , dirname( __FILE__ )  );
$pathinfo = pathinfo( dirname( plugin_basename( __FILE__ ) ) );
if ( !defined( 'MINTDIRECT_PLUGIN_NAME' ) ) define( 'MINTDIRECT_PLUGIN_NAME', $pathinfo['filename'] );
if ( !defined( 'MINTDIRECT_PLUGIN_URL' ) ) define( 'MINTDIRECT_PLUGIN_URL', plugins_url(MINTDIRECT_PLUGIN_NAME) . '/' );

require_once(__DIR__ .'/includes/types/index.php');
require_once(__DIR__ .'/includes/common/index.php');
require_once(__DIR__ .'/includes/listtables/index.php');

add_action( 'plugins_loaded', 'init_mintdirect' );
function init_mintdirect(){
	
	 class MintDirectGateway extends WC_Payment_Gateway{

		 
		const _ICON=MINTDIRECT_PLUGIN_URL."assets/img/cards.png";
		
		//TODO:get this from settings page
		const _APPID= "mintdirect";
		const _TITLE = "Mint API";
		const _DESC = "Accept credit card payments thru mint";
		const PROP_COMPANY_TOKEN="company_token";
		
		public function __construct(){
			$this->init_wc_defaults();
			
			
		}
		
		
		
		
		private  function init_wc_defaults(){
			$this->id=self::_APPID;
			$this->icon =self::_ICON;
			$this->has_fields=true;
			$this->method_title=self::_TITLE;
			$this->method_description=$this->get_option( 'description' );
			$this->supports = array(
		 	  'products',
			  'refunds'
			);
			
			$this->init_form_fields();
			$this->init_settings();
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
			
			/*$this->title = $this->get_option( 'title' );
			$this->description = $this->get_option( 'description' );
			$this->enabled = $this->get_option( 'enabled' );
			$this->testmode = 'yes' === $this->get_option( 'testmode' );
			$this->test_token = $this->testmode ? $this->get_option( 'test_token' ) : $this->get_option( 'test_token' );
			$this->prod_token = $this->testmode ? $this->get_option( 'prod_token' ) : $this->get_option( 'prod_token' );
			*/
			$this->title = $this->get_option( 'title' );
			$this->description = $this->get_option( 'description' );
		}
		
		
		public function payment_fields(){
			woocommerce_form_field( 
				'card.name', array(
				'type'          => 'text',
				'class'         => array('mint-card-name form-row-wide'),
				'label'         => _('Card Holder Name'),
				'required'		=> true
			) );
			
			woocommerce_form_field( 
				'card_number'
				, array(
						'type'          => 'text',
						'maxlength'		=> 16,
						'class'         => array('mint-card-name form-row-wide'),
						'label'         => _('Card Number'),
						'required'		=> true
					) 
			);
			woocommerce_form_field( 
				'card_exp_month'
				, array(
						'type'          => 'text',
						'maxlength'		=> 2,
						'class'         => array('mint-card-exp'),
						'label'         => _('Exp Month'),
						'required'		=> true
					) 
			);
			woocommerce_form_field( 
				'card_exp_year'
				, array(
						'type'          => 'text',
						'maxlength'		=> 2,
						'class'         => array('mint-card-exp'),
						'label'         => _('Exp Year'),
						'required'		=> true
					) 
			);
			woocommerce_form_field( 
				'card_cvc'
				, array(
						'type'          => 'text',
						'maxlength'		=> 4,
						'class'         => array('mint-card-cvc'),
						'label'         => _('CVC Code'),
						'required'		=> true
					) 
			);
			woocommerce_form_field( 
				'terms_conditions'
				, array(
						'type'          => 'checkbox',
						'class'         => array('mint-terms-conditions'),
						'label'         => _('<span> I confirm by paying this invoice that I have read, understood and agreed to be bound by the <a href="https://www.mintpayments.com/index.php/terms/" target="_blank"> Agreements, Terms and Conditions</a> enforced by Mint Sales Australia Pty Ltd and its Acquirers/Partners.  </span>'),
						'required'		=> true
					) 
			);
		}
		
		
		public  function init_form_fields(){
				
				$this->form_fields = array(
					'title' => array(
						'title'       => 'Title',
						'type'        => 'text',
						'description' => 'This controls the title which the user sees during checkout.',
						'default'     => 'Credit Card',
						'desc_tip'    => true,
					),
					'description' => array(
						'title'       => 'Description',
						'type'        => 'textarea',
						'description' => 'This controls the description which the user sees during checkout.',
						'default'     => 'Pay with your credit card via our  payment gateway.',
					),
					'testmode' => array(
						'title'       => 'Test mode',
						'label'       => 'Enable Test Mode',
						'type'        => 'checkbox',
						'description' => 'Place the payment gateway in test mode using test API keys.',
						'default'     => 'yes',
						'desc_tip'    => true,
					),
					'test_token' => array(
						'title'       => 'Test Token',
						'type'        => 'text'
					),
					'prod_token' => array(
						'title'       => 'Production Token',
						'type'        => 'text',
					),
					'test_bearer' => array(
						'title'       => 'Test Bearer Token',
						'type'        => 'text',
					),
					'prod_bearer' => array(
						'title'       => 'Prod Bearer Token',
						'type'        => 'text',
					),
					'currency' => array(
						'title'       => 'Currency',
						'type'        => 'text',
					)
					
				);
				//parent::init_form_fields();
		}

		
			
				
		
		

		/***
		*@Overrides WooCommerce:process_payment
		**/
		public function process_payment( $order_id ) {
			
			$ret = \mintpayments\gateway\common\MpayTransaction::forContext($this)
								->withOrder($order_id)
								->withCard(\mintpayments\gateway\common\Card::fromRequest())
								->send();
			 $success = $ret['state']==\mintpayments\gateway\common\PaymentStatus::APPROVED;
			 if(!$success){
				wc_add_notice(
                        __($ret['message']?$ret['message']:"error processing checkout please try again", 'wc'),
                        'error'
                    );
			 }
			 return array(
				'result' => $success?'success':'failed',
				'redirect' => $success?$this->get_return_url( '' ):''
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

	}
	
	/***
	*	function to register this plugin as a WC payment gateway
	***/
	function attach_mintdirect( $methods ) {
		
		$methods[] = 'MintDirectGateway'; 
		return $methods;
	}
	add_filter( 'woocommerce_payment_gateways','attach_mintdirect' );
	
}


ob_end_clean();


?>