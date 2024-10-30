<?php namespace mintpayments\gateway\shortcodes; ?>
<?php 
abstract class ShortCode{
	public abstract function render($atts);
	
	public static function attrToString($attr){
		
		$ret = '';
		foreach($attr as $rows){
			$ret .= $rows['key']. "='" .$rows['value']."' "; 
		}
		
		return $ret;
	}
}?>