<?php namespace mintpayments\gateway\common; ?>
<?php class MpayTransaction{
	const PROP_TESTMODE = 'testmode';
	const PROP_TEST_COMPANY 	= 'test_token';
	const PROP_PROD_COMPANY 	= 'prod_token';
	const PROP_TEST_BEARER	= 'test_bearer';
	const PROP_PROD_BEARER	= 'prod_bearer';
	const PROP_CURRENCY	= 'currency';
	
	const _URL_UATSB='https://secure-uatsb.mintpayments.net/mpay';
	const _URL_PROD='https://secure.mintpayments.com/mpay';
	
	
	protected $paymentinfo = array();
	protected $context='';
	protected $amount=0;
	

	private $basepath='';
	private $testmode=true;
	private $headers = array();
	private $company = '';
	private $post=null; //card payment post type to save changes.
	private $card = null;
	
	public static function forContext($context){
			$me = new MpayTransaction();
			$me->context 	= $context;
			$me->testmode	= $context->get_option(self::PROP_TESTMODE)=='yes';
			$me->basepath	= $me->testmode?self::_URL_UATSB:self::_URL_PROD;
			$auth = $me->testmode?$context->get_option(self::PROP_TEST_BEARER):$context->get_option(self::PROP_PROD_BEARER);
			$me->headers 	= array('Content-Type'=>'application/json; charset=utf-8',
									'Authorization'=>'Bearer ' . $auth
									); 
			
			$me->company	= $me->testmode?$context->get_option(self::PROP_TEST_COMPANY):$context->get_option(self::PROP_PROD_COMPANY);
			$me->currency	= $context->get_option(self::PROP_CURRENCY);
			
			//load all config here;
			

			return $me;
	}
	
	
	
	public  function withOrder($order){
			if($order instanceof \WC_Order){
				$this->order = $order;
			}else{
				$this->order = new \WC_Order($order);
			}
			
			//query from Card Payments CPT
			$filter = array( 
				'post_type' => 'mint-payments',
				'post_title' =>$this->order->get_id()
				 ); 
				$q = new \WP_Query($filter);
				
			if($q->have_posts()){
				$this->post = $q->get_posts(array('numberposts'=>1))[0];	
			}
			error_log(print_r($this->post,true));
			return $this;
	}
	
	public function withCard($card){
		$this->card = $card;
		return $this;
	}
	
	public function withAmount($amt){
		$this->amount=$amt;
		return $this;
	}
	
		
	public function refund(){
		$token = $this->fetchTransactionToken();
		$ret = array('state'=>PaymentStatus::ONPROCESS,'message'=>'On Process');
			
		if($token==null){
			
			$ret['state']=PaymentStatus::FAILED;
			$ret['message']='call to transaction token failed';
			
			return $ret;
		}
		
		if($this->post == null){
			$ret['state']=PaymentStatus::FAILED;
			$ret['message']='unable to load product';
			return $ret;
		}
		
		$invoice = get_post_meta($this->post->ID,\mintpayments\gateway\types\CardPayments::PROP_RECEIPTNUMBER);
		
		$url=$this->basepath.'/v4/purchase/'. $invoice[0] .'/refund';
		$req=array(
			'token'=>array(
					'company_token'=>$this->company,
					'transaction_token'=>$token
			),
			'customer'=>array(
					'customer_reference'=>'',
					'ip_address'=>$_SERVER['REMOTE_ADDR'],
					'timezone'=>self::getTimezone()
			),
			'refund'=>array(
					'invoice_number'=>$this->order->id,
					'amount'=>$this->amount,
					'currency'=>$this->currency
			)
		);
		
		$res = wp_remote_post($url, array(
			'headers'     => $this->headers,
			'body'        => json_encode($req),
			'method'      => 'POST',
			'data_format' => 'body',
			'timeout' => 10,
		));
		error_log("==============================================");
		error_log(print_r($res,true));
		error_log("==============================================");
		
		if(is_wp_error($res)){
			$error_code = array_key_first( $res->errors );
			$error_message = $res->errors[$res][0];
			$ret = array('state'=>PaymentStatus::FAILED,'message'=>$error_message);
			return $ret;
		}
		$body = wp_remote_retrieve_body( $res);
		$jres = json_decode($body,true);
		error_log(print_r($jres,true));
		if($jres){
			//TODO RECORD TRANS TO NEW CPT
			if(array_key_exists('refund',$jres)){
				switch(strtoupper($jres['refund']['status'])){
					case 'APPROVED';
						$ret = array('state'=>PaymentStatus::APPROVED,'message'=>'APPROVED');
						break;
					case 'DECLINED';
						$ret = array('state'=>PaymentStatus::DECLINED,'message'=>'DECLINED');
						break;
					case 'FAILED';
						$ret = array('state'=>PaymentStatus::FAILED,'message'=>'FAILED');
						break;
				}
			
			}else if(array_key_exists('error_code',$jres)){
				$ret = array('state'=>PaymentStatus::FAILED,'message'=>'FAILED');
			}
			
		
		}
		
		return $ret;
		
	}
	
	
	public function send(){
		$token = $this->fetchTransactionToken();
		$ret = array('state'=>PaymentStatus::ONPROCESS,'message'=>'On Process');
			
		if($token==null){
			
			$ret['state']=PaymentStatus::FAILED;
			$ret['message']='call to transaction token failed';
			
			return $ret;
		}
		
		if($this->post == null){
			$ret['state']=PaymentStatus::FAILED;
			$ret['message']='unable to load product';
			return $ret;
		}
		
		
		
		$url=$this->basepath.'/v4/purchase';

		$req=array(
			'token'=>array(
					'company_token'=>$this->company,
					'transaction_token'=>$token
			),
			'customer'=>array(
					'customer_reference'=>$this->order->get_id(),
					'email'=>$this->order->get_billing_email(),
					'accepted_terms_and_conditions'=>$this->card->getTerms(),
					'should_mint_apply_authentication'=>'false',
					'ip_address'=>$_SERVER['REMOTE_ADDR'],
					'timezone'=>self::getTimezone()
			),
			'purchase'=>array(
					'invoice_number'=>$this->order->id,
					'amount'=>$this->order->get_total(),
					'currency'=>$this->currency,
					'should_mint_apply_surcharge'=>true
			),
			'card'=>array(
				'number'=> $this->card->getNumber(),
				'expiry_month'=> $this->card->getExpiryMonth(),
				'expiry_year'=> $this->card->getExpiryYear(),
				'cvc'=> $this->card->getCvv(),
				'holder_name'=> $this->card->getOwner(),
				'token'=> null
				)
				
		);
		error_log(json_encode($req));
		$res = wp_remote_post($url, array(
			'headers'     => $this->headers,
			'body'        => json_encode($req),
			'method'      => 'POST',
			'data_format' => 'body',
			'timeout' => 10,
		));
		
		
		error_log("==============================================");
		error_log(print_r($res,true));
		error_log("==============================================");
		
		if(is_wp_error($res)){
			$error_code = array_key_first( $res->errors );
			$error_message = $res->errors[$res][0];
			$ret = array('state'=>PaymentStatus::FAILED,'message'=>$error_message);
			return $ret;
		}
		$body = wp_remote_retrieve_body( $res);
		$jres = json_decode($body,true);
		error_log(print_r($jres,true));
		if($jres){
			//TODO RECORD TRANS TO NEW CPT
			\mintpayments\gateway\types\CardPayments::fromResponse($this->order->get_id(),$jres)
						->save();
			if($jres['purchase']){
				
				switch($jres['purchase']['status']){
					case 'APPROVED':
						$ret['state']=PaymentStatus::APPROVED;
						$ret['message']=$jres['response_message'];
						$this->order->update_status('processing');	
						break;
					case 'DECLINED':
						$ret['state']=PaymentStatus::DECLINED;
						$ret['message']=$jres['response_message'];
						$this->order->update_status('failed');
						$this->order -> add_order_note($jres['response_message']);
						break;
					case 'FAILED':
					default:
						$ret['state']=PaymentStatus::DECLINED;
						$ret['message']=$jres['response_message'];
						$this->order->update_status('failed');
						$this->order -> add_order_note($jres['response_message']);
						break;
				}
				
			}else{
						
						$ret['state']=PaymentStatus::FAILED;
						$ret['message']=$jres['error_message'];
						$this->order->update_status('failed');
						$this->order -> add_order_note($jres['error_message']);
			
			}
			
		
		}
		
		return $ret;
		
	}
	
	private function fetchTransactionToken(){
		$url=$this->basepath.'/v4/transaction_token';
		$req=array(
			'company_token'=>$this->company
		);
		
		$res = wp_remote_post($url, array(
			'headers'     => $this->headers,
			'body'        => json_encode($req),
			'method'      => 'POST',
			'data_format' => 'body',
			'timeout' => 10,
		));
		error_log(print_r($res,true));
		$body = wp_remote_retrieve_body( $res);
		if(is_wp_error($res)){
			return null;	
		}
		
		$jres = json_decode($body,true);
		if($jres)
			return $jres['transaction_token'];
		
		return null;
		
	}
	
	private static function getTimezone(){
		$date = new \DateTime();
		$timeZone = $date->getTimezone();
		return $timeZone->getName();
	}
}?>