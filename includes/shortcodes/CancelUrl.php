<?php namespace mintpayments\gateway\shortcodes; ?>
<?php class CancelUrl extends ShortCode{

	public function __construct(){
		
	}

	public   function render($atts){
		
		$msg = '';
		if(isset(WC()->session)){
			$msg = WC()->session->get('checkout_message');
		}
		$ret= self::loadview($atts,$msg);
		return $ret;
	}
	
	
	 static function loadview($atts,$msg){ob_start();?>
		<?php echo $msg;?>
		<?php $ret =  ob_get_contents(); ob_end_clean();return $ret;
	 }
}
$mint_cancel_url = new CancelUrl();
add_shortcode('mint-cancel-url' , array($mint_cancel_url,'render'));
?>