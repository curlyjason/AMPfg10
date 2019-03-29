<?php
/**
 * Description of Logger
 *
 * @author dondrake
 */
Trait Logger {
	
	public $quoteAttemptMessage = "\n** Prepared to request Quote %s, for %s for %s items (%s) in cart %s.\n-----------------------END----------------------------\n";
    public $cronJob = "\n Called %s, that returned %s.\n";

	/**
	 * Log a Quote Acknowledgement email
	 * 
	 * @param string $mode attempt or success
	 * @param array $cart Standard cart array and tool object
	 */
	public function logQuoteEmail($mode, $cart) {
		$toolkit = $cart['toolkit'];
		if ($mode == 'Attempt') {
			$this->log(sprintf($this->{"quote{$mode}Message"}, 
							$toolkit->orderNumber(), 
							$toolkit->customerName(), 
							$toolkit->itemCount(), 
							$toolkit->itemInList(), 
							$toolkit->cartId()), 
					'order_email');
		} else {
			$this->log(sprintf($this->{"quote{$mode}Message"}, $toolkit->orderNumber(), $toolkit->customerName()), 'order_email');
		}
	}
    
    public function logCronJob($callPoint, $message) {
        $this->log(sprintf($this->cronJob, $callPoint, $message), 'robotIO');
    }
    
    public function logVarlog($message){
        $this->log($message, 'varlog');
    }
	
}
