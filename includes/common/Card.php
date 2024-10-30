<?php namespace mintpayments\gateway\common; ?>
<?php ob_start(); ?>
<?php class Card{
	
		const PROP_OWNER='card_name';
		const PROP_NUMBER = 'card_number';
		const PROP_EXPIRY_MONTH = 'card_exp_month';
		const PROP_EXPIRY_YEAR = 'card_exp_year';
		const PROP_CVV = 'card_cvv';
		const PROP_TERMS='terms_conditions';
	
		protected $owner;
		protected $number;
		protected $expiry_month;
		protected $expiry_year;
		protected $cvv;
		protected $terms;
		
		public function setOwner($owner){$this->owner = $owner;}
		public function setNumber($num){$this->number = $num;} 
		public function setExpiry($exp){$this->expiry = $exp; }
		public function setCvv($cvv){$this->cvv = $cvv;}
		
		
		public function getOwner(){return $this->owner;}
		public function getNumber(){return $this->number;}
		public function getExpiryMonth(){return $this->expiry_month;}
		public function getExpiryYear(){return $this->expiry_year;}
		public function getCvv(){return $this->cvv;}
		public function getTerms(){return $this->terms;}
		
		public static function fromRequest(){
			$card = new Card();
			$card->owner = sanitize_text_field($_REQUEST[self::PROP_OWNER]);
			$card->number = sanitize_text_field($_REQUEST[self::PROP_NUMBER]);
			$card->expiry_month = sanitize_text_field($_REQUEST[self::PROP_EXPIRY_MONTH]);
			$card->expiry_year = sanitize_text_field($_REQUEST[self::PROP_EXPIRY_YEAR]);
			$card->cvv = sanitize_text_field($_REQUEST[self::PROP_CVV]);
			$card->terms = isset($_REQUEST[self::PROP_TERMS]);
			return $card;
		}

}?>
<?php ob_end_clean(); ?>