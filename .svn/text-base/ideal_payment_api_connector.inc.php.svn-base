<?php
// $Id$

/**
 * @file
 * iDEAL payment module for Ubercart. No extra gateway needed.
 * Include For iDEAL ING/PB Advanced Connector
 *
 * Development by Qrios | http://www.qrios.nl | c.kodde {at} qrios {dot} nl
 *
 *
 */

function ideal_payment_api_dirreq_call() {

  $path_module = drupal_get_path('module', 'ideal_payment_api');
  ////Set errors on so we can see if there is a PHP error goes wrong
  //ini_set('display_errors',1);
  //error_reporting(E_ALL & ~E_NOTICE);

  //include connector
  require_once($path_module.'/lib/iDEALConnector.php');

  // Initialise connector
  $iDEALConnector = new iDEALConnector();

  //Process directory request
	$response = $iDEALConnector->GetIssuerList();

  if ($response->errCode){
    $return.=t('Payment through iDEAL gateway not possible.').' ';
    $return.=t('Error message iDEAL').': ';
    $return.= $response->errMsg .'('. $response->errCode .')';
  }
  else{
    //Get issuerlist
    $issuerArray = $response->issuerShortList;
    if(count($issuerArray) == 0){
      $return.=t('List with banks not available, payment through iDEAL gateway not possible.');
    }
    else{
      $return = $issuerArray;
    }
  }
  /*END ThinMPI code for DirReq*/
 return $return;

}


function ideal_payment_api_transreq_call($issuer_id) {
  //Init
  $order_data = $_SESSION['ideal_payment_api_order_data'];
  //$path_back_succes = $order_data['path_back_succes'];
  $path_back_error = $order_data['path_back_error'];

  //issuerid is min. 4 chars, add leading 0's
  $issuer_id = str_pad($issuer_id, 4, '0', STR_PAD_LEFT);

  //Get user ID
  global $user;
  if ($user){
    $user_id = $user->uid;
  }

  if ($_SESSION['ideal_payment_api_order_data']) {

    //Add issuer id to sesion order_data
    $_SESSION['ideal_payment_api_order_data']['issuer_id'] = $issuer_id;

    $path_module = drupal_get_path('module', 'ideal_payment_api');

    //include connector
    require_once($path_module.'/lib/iDEALConnector.php');
    //Initialise connector
    $iDEALConnector = new iDEALConnector();
    //print_r($iDEALConnector);
    $order_data = $_SESSION['ideal_payment_api_order_data'];
    $order_id = $order_data['order_id'];
    $amount = $order_data['amount'];
    $description = $order_data['description'];
    if (drupal_strlen($description) > 32) {//@TODO: run this trough a general error handler.
      $description = drupal_substr($description, 0, 32);
      watchdog('ideal_api', t('iDEAL decription too long. Changed from %orig to %shortened'), array('%orig' => $order_data['description'], '%shortened' => $description));
    }
    $entrance_code = $order_id;
    $expiration_period = $iDEALConnector->config['EXPIRATIONPERIOD'];
    $merchant_return_url = $iDEALConnector->config['MERCHANTRETURNURL'];

    if(!$issuer_id){
      drupal_set_message(t('You have not chosen a bank for IDEAL payment. Please try again'));
      drupal_goto($path_back_error);
    }

    //Send TransactionRequest
    $response = $iDEALConnector->RequestTransaction(
      $issuer_id,
      $order_id,
      $amount,
      $description,
      $entrance_code,
      $expiration_period,
      $merchant_return_url
    );

    if (!$response->errCode){
  		$transaction_id = $response->getTransactionID();

      //Add transaction id to sesion order_data
      $_SESSION['ideal_payment_api_order_data']['transaction_id'] = $transaction_id;

  		//Get IssuerURL and decode it
  		$ISSURL = $response->getIssuerAuthenticationURL();
  		$ISSURL = html_entity_decode($ISSURL);

      //Save order as 'payed = 0'
      ideal_payment_api_order_save($order_id, $user_id, $description, $amount, $issuer_id, $transaction_id, 0);

  		//Redirect the browser to the issuer URL
  		header("Location: $ISSURL");
  		exit();

  	}
    else{
  		//TransactionRequest failed, inform the consumer
  		$msg = $response->consumerMsg;
      watchdog('ideal_api', $response->errCode.': '.$response->errMsg, NULL, WATCHDOG_ERROR);
  		drupal_set_message('Something went wrong in processing your IDEAL payment. IDEAL error:'.'<br>'.$msg, 'error');
      drupal_goto($path_back_error);
  	}

    return($ideal_payment_api_form );

  }
  else{
    drupal_set_message('iDEAL error: No order data available.', 'error');
    drupal_goto($path_back_error);
  }
}


function ideal_payment_api_statreq_call($order_data = FALSE, $unattended = FALSE) {
  //Init
  if(!is_array($order_data)){
    $order_data = $_SESSION['ideal_payment_api_order_data'];
    $transaction_id = check_plain($_GET['trxid']);
    $order_id = check_plain($_GET['ec']);
  }
  else{
    $transaction_id = $order_data['transaction_id'];
    $order_id = $order_data['order_id'];
  }
  $amount = $order_data['amount'];
  
  $description = $order_data['description'];
  if (drupal_strlen($description) > 32) {//@TODO: run this trough a general error handler.
    $description = drupal_substr($description, 0, 32);
    watchdog('ideal_api', t('iDEAL decription too long. Changed from %orig to %shortened'), array('%orig' => $order_data['description'], '%shortened' => $description));
  }
  $issuer_id = $order_data['issuer_id'];
  $path_back_succes = $order_data['path_back_succes'];
  $path_back_error = $order_data['path_back_error'];
  //print_r($_SESSION['ideal_payment_api_order_data']);
  //exit;
  //print_r($unattended);
  //Get user ID
  global $user;
  if ($user){
    $user_id = $user->uid;
  }

  $path_module = drupal_get_path('module', 'ideal_payment_api');

  //include connector
  require_once($path_module.'/lib/iDEALConnector.php');
  //Initialise connector
  $iDEALConnector = new iDEALConnector();

	//Create StatusRequest
	$response = $iDEALConnector->RequestTransactionStatus($transaction_id);

  //$transID = str_pad($transaction_id, 16, "0"); //Delete??

  if ($response->errCode){
		//StatusRequest failed
    $msg = $response->consumerMsg;
    watchdog('ideal_api', $response->errCode.': '.$response->errMsg, NULL, WATCHDOG_ERROR);
    if($unattended){
      return FALSE;
    }
    drupal_set_message(t('We could not verify the payment status automaticaly, You can check your payment manualy with the button below. Please contact us if you keep getting this message. IDEAL error:')).'<br>'.$msg;
    drupal_goto('ideal/payment_statreq_recheck');
	}
	elseif($response->status != 1){
		//Transaction failed
    if($unattended){
      watchdog('ideal_api', 'Your IDEAL payment has been canceled by you or by the IDEAL process. Please try again. Contact us if you have problems.', NULL, WATCHDOG_WARNING);
      return FALSE;
    }
    //inform the consumer
		drupal_set_message(t('Your IDEAL payment has been canceled by you or by the IDEAL process. Please try again. Contact us if you have problems.'), 'warning');
    drupal_goto($path_back_error);
	}
  else{
    //Update order as 'payed = 1'
    ideal_payment_api_order_update($order_id, 1);

    module_invoke_all('ideal_payed', $order_data);
    
    if($unattended){
      return FALSE;
    }
	drupal_set_message(t('Thank you for shopping with us, your payment is processed sucessfuly'));

    // This lets us know it's a legitimate access of the complete page.
    $_SESSION['do_complete'] = TRUE;

    drupal_goto($path_back_succes);
  }
}
