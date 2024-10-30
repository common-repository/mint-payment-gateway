<?php namespace mintpayments\gateway\types; ?>
<?php  
if ( ! defined( 'ABSPATH' ) ) exit;

/****
	registers custom post types to wp.
**/
abstract class CustomType{
	public  function init(){

		$opts= $this->options();
		$opts['labels']=$this->labels();
		$opts['supports'] = $this->supports();
		
		$ret =  register_post_type($this->getName(), $opts);
		remove_post_type_support($this->getName(), 'excerpt');
		return $ret;
	}
	protected abstract function getName() : string;
	protected abstract function options(): array;
	protected abstract function labels(): array;
	protected abstract function supports(): array;
}

?>
