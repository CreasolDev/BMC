<?php
/**
 * Author: Renzo Roso (Cheridum)
 * e-mail: rbe.roso@creasoldevelopment.com
 * BTC   : 15vVwpBAp5hSWr2pPWB2DQJ9dGGMKrjMQR
 * LTC   : LSuYbxjytHvQcKERf1A6ETZqYsmjiP5c6i
 * donations are welcome =)
 */ 

// Include Bit-Mining.co PHP API
	include_once("bmcApi.class.php");

// Create API Object
    $email = '';
    $key = '';
	$api = new bmcApi($email, $key);
	
	/**
     * Here are some Base API Methods you can test with, uncomment them to use them:
     */
	echo "Ticker:<pre>", json_encode($api->ticker('gh')), "</pre>";
	echo "Order Book:<pre>", json_encode($api->orderbook()), "</pre>";
	//echo "Balance:<pre>", $api->printBalances(), "</pre>";

    /**
     * These are the methods used to BUY and SELL.
     * Use them with caution as they will actually BUY or SELL for you!
     * Check the bmcApi.class.php for documentation how to use them.
     */
    //echo "Buy:<pre>", json_encode($api->new_order('buy', $amount, $price, $pair)), "</pre>";
    //echo "Sell:<pre>", json_encode($api->new_order('sell', $amount, $price, $pair)), "</pre>";
        

    
?>