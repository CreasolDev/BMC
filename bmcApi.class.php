<?php

/**
 * Author: Renzo Roso (Cheridum)
 * e-mail: rbe.roso@creasoldevelopment.com
 * BTC   : 15vVwpBAp5hSWr2pPWB2DQJ9dGGMKrjMQR
 * LTC   : LSuYbxjytHvQcKERf1A6ETZqYsmjiP5c6i
 * donations are welcome =)
 * 
 * This class automatically sets the current BTC, LTC, GHS and KHS balance on making the object.
 * This is to reduce the amount of API Calls that needing to be handled by the Bit-mining.co server
 * Since they work with a daily pay-out, it shouldn't be a big problem :)
 * Might you need to update it, call setBalances() again.
 *
 */

class bmcApi
{
	private $email;
	private $api_key;
	private $nonce_v;
    private $btcBalance;
    private $ltcBalance;
    private $ghsBalance;
    private $khsBalance;

	/**
	 * Create bmcapi object
	 * @param string $email
	 * @param string $api_key
	 */
	public function __construct($email, $api_key)
    {
		$this->email = $email;
		$this->api_key = $api_key;
		$this->nonce();
        $this->setBalances();
	}

	/**
	 * Create signature for API call validation
	 * @return string hash
	 */
	private function signature($type)
    {
        // Create string
		$string = $this->nonce_v . $this->email . $type.$this->api_key;
        
        // Create hash
		$hash = hash('sha256', $string); 
        
        // Return the hash
        return $hash;
	}    
    
	/**
	 * Set nonce as timestamp
	 */
	private function nonce()
    {
        // Set the nonce by the current microtime
		$this->nonce_v = round(microtime(true)*100);
	}
	 
	/**
	 * Send get request to Bit-mining.co API.
	 * @param string $url
	 * @param array $param
	 * @return array JSON results
	 */
	private function get($url, $param = array())
    {
        // Making the $get String
		$get = '';
        
        // Setting all the URL parameters from the $param sent.
		if (!empty($param))
        {
	    	foreach($param as $k => $v)
            {
				$get .= $k . '=' . $v . '&'; //Dirty, but work
	    	}
			$get = substr($get, 0, strlen($get)-1);
		}
		
        // Making the URL
        $url = $url.'?'.$get;
        
        
        // Preparing the API call
		$ch = curl_init();
		curl_setopt ($ch, CURLOPT_URL,            $url    );
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true    );
		curl_setopt ($ch, CURLOPT_USERAGENT,      'phpAPI');
        curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0       );
        curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0       ); 
        
        // API Call
		$out = curl_exec($ch);
		
		if (curl_errno($ch))
        {
			trigger_error("cURL failed. Error #".curl_errno($ch).": ".curl_error($ch), E_USER_ERROR);
		}
		
        // Closing the API call
		curl_close($ch);	
        
		return $out;
	} 
	
	/**
	 * Send API call (over get request), to Bit-mining.co server.
	 * @param array $param
	 * @return array JSON results
	 */
	public function api_call($param)
    {
        // Setting the base URL where the API is located
        $url = "https://bit-mining.co/api.php"; //Create url
        
        // Calling the API and decoding the JSON object to a normal Array
	    $answer = $this->get($url, $param);
		$answer = json_decode($answer, true);
	   
		return $answer;
	}
	
	/**
	 * Get the current ticker results for the given orderType (Buy / Sell)
	 * @param string $orderType
	 * @return array JSON results
	 */
	public function ticker($orderType)
    {
        if ($orderType == '' || $orderType == null)
        {
            $orderType = 'gh';
        }
		return $this->api_call(
                    array(
                        'a' => 'ticker',
                        'type' => $orderType
                    )
        );
	}
	
	/**
	 * Get the current bids and asks
	 * @return array JSON results
	 */
	public function orderbook()
    {
		return $this->api_call(
                    array(
                        'a' => 'orderbook'
                    )
        );
	}
	
	/**
	 * Get the trade history rom the current "candle"
	 * @return array JSON results
	 */
	public function history()
    {
		return $this->api_call(
                    array(
                        'a' => 'history'
                    )
        );
	}
	
	/**
	 * Get the current account balance.
	 * @return array JSON results
	 */
	public function balance()
    {
        // Setting the parameters needed to get the balance of the account
        $param = array(
                'a'     => 'balance',
                'email' => $this->email,
                'nonce' => $this->nonce_v,
                'hash'  => $this->signature('balance')
        );
		return $this->api_call($param);
	}
	
	/**
	 * Place an order, with the given orderType (Buy / Sell), amount, price, and pair(gh / ks / ltc)
	 * @param string $orderType
	 * @param float $amount
	 * @param float $price
	 * @param string $pair
	 * @return array JSON order data
	 */
	public function new_order($orderType, $amount, $price, $pair)
    {
        return $this->api_call(
                    array(
                        'a'     => $orderType,
                        'amnt'  => $amount,
                        'price' => $price,
                        'type'  => $pair,
                        'email' => $this->email,
                        'nonce' => $this->nonce_v,
                        'hash'  => $this->signature($orderType)
                    )
        );
	}
    
    /**
	 * Retreives and sets the currently known balances
     * Call this function to reset all the balances to the actuall balances
	 */
    public function setBalances()
    {
        $balanceArray = $this->balance();
        $this->btcBalance = $balanceArray['btc'];
        $this->ltcBalance = $balanceArray['ltc'];
        $this->ghsBalance = $balanceArray['gh'];
        $this->khsBalance = $balanceArray['ks'];
    }
    
    /**
	 * You can call this function to have it print the balances all together
     * Also, every time you call this function, all balances will be requested from the server to be up-to-date
	 */
    
    public function printBalances()
    {
        $this->setBalances();
        echo '<br><br>';
        echo 'BTC Balance: '.number_format($this->btcBalance, 8).'<br>';
        echo 'LTC Balance: '.number_format($this->ltcBalance, 8).'<br>';
        echo 'GHS Balance: '.number_format($this->ghsBalance, 8).'<br>';
        echo 'KHS Balance: '.number_format($this->khsBalance, 8).'<br>';
        echo '<br><br>';
    }
    
    /**
	 * Returns the known BTC Balance from the last setBalance() call
	 * @returns Integer with BTC Balance
	 */
    
    public function getBTCBalance()
    {
        return  number_format($this->btcBalance, 8);
    }
    
    /**
	 * Returns the known LTC Balance from the last setBalance() call
	 * @returns Integer with LTC Balance
	 */
    
    public function getLTCBalance()
    {
        return number_format($this->ltcBalance, 8);
    }
    
    /**
	 * Returns the known GHS Balance from the last setBalance() call
	 * @returns Integer with GHS Balance
	 */
    
    public function getGHSBalance()
    {
        return number_format($this->ghsBalance, 8);
    }
    
    /**
	 * Returns the known KHS Balance from the last setBalance() call
	 * @returns Integer with KHS Balance
	 */
    
    public function getKHSBalance()
    {
        return number_format($this->khsBalance, 8);
    }
    
}

?>
