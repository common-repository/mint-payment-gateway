<?php namespace mintpayments\gateway\common; ?>
<?php
class PaymentStatus{
		const APPROVED='APPROVED';
		const DECLINED='DECLINED';
		const FAILED='FAILED';
		const ONPROCESS='ONPROCESS'; /** this is a place holder for timeout transactions. **/
		//const REFUNDED='REFUNDED'; /** used by refund() to indicate that a transaction has previously refunded **/
	}
	
?>