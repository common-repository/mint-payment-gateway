<?php namespace mintpayments\gateway\common; ?>
<?php 
ob_start();
class HppForm{
	const EVENT_ON_RESPONSE="hpp_on_response_received";
	const _TEST_URL ="https://hpp-uatsb.mintpayments.net/";
	const _PROD_URL ="https://hpp.mintpayments.com/";

	private $order = null;
	private $company = null;
	private $context = null;
	private $path = null;
	private $testmode='yes';
	
	public static function forContext($context){
		$me = new HppForm();
		$me->context=$context;
		$me->testmode = 'yes' === $context->get_option( 'testmode' );
		$me->company = $me->testmode?$context->get_option('testtoken'):$context->get_option('prodtoken');
		return $me;
	}
	
	
	
	public  function withOrder($order){
		if($order instanceof \WC_Order){
			$this->order = $order;
		}else{
			$this->order = new \WC_Order($order);
		}
		
		return $this;
	}
	

	public function load(){
		ob_end_clean();
		$this->testmode = 'yes' === $this->context->get_option( 'testmode' );
		$this->order->update_status('on-hold');	
		
		$url = $this->testmode?self::_TEST_URL:self::_PROD_URL;
		
		$this->path = sprintf("%s/%s?email=%s&amount=%s&reference1=%s&reference2=%s&redirectUrlMerchant=%s"
						, $url
						,$this->company
						,$this->order->get_billing_email()
						,$this->order->get_total()*100
						,$this->order->get_id()
						,''
						,home_url('').'/wc-api/minthppgateway');
						
		
		return $this;
	}
	
	public function redirect(){
		header('Location: ' . $this->path);
	}
	
	
	
	public function toString(){
		return $this->path;
	}
	
	/**this would be the handler for the callback**/
	public function onResponseReceived(){

        $orderId = \WC()->session->get('order_awaiting_payment');
        if(!$orderId && isset($_REQUEST['reference1'])) {
            $orderId = sanitize_text_field($_REQUEST['reference1']);
        }

        $this->order = new \WC_Order($orderId);
		$this->testmode = 'yes' === $this->context->get_option( 'testmode' );
		error_log(print_r(array_map('sanitize_text_field',$_REQUEST),true));
		$status = strtoupper(sanitize_text_field($_REQUEST['transactionStatus']));
		\WC()->session->set('checkout_message',sanitize_text_field($_REQUEST['errorMessage']));
		switch($status){
			case PaymentStatus::APPROVED:
				$this->order->update_status('processing');	
				break;
				
			case PaymentStatus::DECLINED:
				$this->order->update_status('failed');
                $this->order -> add_order_note(sanitize_text_field($_REQUEST['errorMessage']));
				
				break;
								
			case PaymentStatus::FAILED:
				$this->order->update_status('failed');
                $this->order -> add_order_note(sanitize_text_field($_REQUEST['errorMessage']));
				
				break;
			
			
			case PaymentStatus::ONPROCESS:
				$this->order->update_status('processing');	
				//TODO:	
			default:
				//unknown status. let's leave it as processing. and manually update it via dashboard.
				$this->order->update_status('processing');
				break;
		}
		
		//save payment details as CPT so that it can be accessed via native wordpress commands.
		$secret  = $this->testmode?$this->context->get_option( 'test_secret' ):$this->context->get_option( 'prod_secret' );
		\mintpayments\gateway\types\CardPayments::fromRequest()
					->withKey($secret)
					->save();
		
		//trigger an event to the client so they can handle the received data*/
		do_action(self::EVENT_ON_RESPONSE,$this->order);
		return array('status'=>$status,'order'=>$this->order);
	}
	
	
	
}
ob_end_clean(); ?>