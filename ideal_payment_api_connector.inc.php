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

  if ($response->errCode) {
    $return.=t('Payment through iDEAL gateway not possible.').' ';
    $return.=t('Error message iDEAL').': ';
    $return.= $response->errMsg .'('. $response->errCode .')';
  }
  else {
    //Get issuerlist
    $issuerArray = $response->issuerShortList;
    if (count($issuerArray) == 0) {
      $return.=t('List with banks not available, payment through iDEAL gateway not possible.');
    }
    else {
      $return = $issuerArray;
    }
  }
  /*END ThinMPI code for DirReq*/
 return $return;

}


function ideal_payment_api_transreq_call($order) {
  //Get user ID
  global $user;
  if ($user) {
    $user_id = $user->uid;
  }

  $path_module = drupal_get_path('module', 'ideal_payment_api');
  require_once($path_module.'/lib/iDEALConnector.php');
  $iDEALConnector = new iDEALConnector();
  
  $order['description'] = check_plain($order['description']); //$string is passed into XMLrpc call, thus cannot contain HTML.
  if (drupal_strlen($order['description']) > 32) {//@TODO: run this trough a general error handler.
    $order['description_orig'] = $order['description'];
    $order['description'] = drupal_substr($order['description'], 0, 32);
    watchdog('ideal_api', t('iDEAL decription too long. Changed from %orig to %shortened'), array('%orig' => $order['description_orig'], '%shortened' => $order['description']));
  }
  
  //issuerid is min. 4 chars, add leading 0's
  $order['issuer_id'] = str_pad($order['issuer_id'], 4, '0', STR_PAD_LEFT);
  
  //Send TransactionRequest
  $response = $iDEALConnector->RequestTransaction(
    $order['issuer_id'],
    $order['order_id'],
    $order['amount'],
    $order['description'],
    $order['order_id'],
    $iDEALConnector->config['EXPIRATIONPERIOD'],
    $iDEALConnector->config['MERCHANTRETURNURL']
  );
  if (!$response->errCode) {
		$transaction_id = $response->getTransactionID();
		ideal_payment_api_order_update($order_id, $payment_status, $transaction_id);

		//Get IssuerURL and decode it
		$ISSURL = $response->getIssuerAuthenticationURL();
		$ISSURL = html_entity_decode($ISSURL);

		//Redirect the browser to the issuer URL
		header("Location: $ISSURL");
		exit();
		//@TODO: prolly best to use Drupal_goto or at least drupal_exit is required.
	}
  else {
		//TransactionRequest failed, inform the consumer
		$msg = $response->consumerMsg;
    watchdog('ideal_api', $response->errCode.': '.$response->errMsg, NULL, WATCHDOG_ERROR);
		drupal_set_message('Something went wrong in processing your IDEAL payment. IDEAL error:'.'<br>'.$msg, 'error');
    drupal_goto($path_back_error);
	}

  return($ideal_payment_api_form );
}


function ideal_payment_api_statreq_call($order_data = FALSE, $unattended = FALSE) {
  if (!is_array($order_data)) {
    $transaction_id = check_plain($_GET['trxid']);
    $order_id = check_plain($_GET['ec']); //@TODO: this MUST be made a lot sturdier. You cannot assume ec == order_id.
    $order_data = ideal_payment_api_order_load($order_id);
  }
  else {
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

  //Get user ID
  global $user;
  if ($user) {
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

  if ($response->errCode) {
		//StatusRequest failed
    $msg = $response->consumerMsg;
    watchdog('ideal_api', $response->errCode .': '. $response->errMsg, NULL, WATCHDOG_ERROR);
    if ($unattended) {
      return FALSE;
    }
    drupal_set_message(t('We could not verify the payment status automaticaly, You can check your payment manualy with the button below. Please contact us if you keep getting this message. IDEAL error:')).'<br>'.$msg;
    drupal_goto('ideal/payment_statreq_recheck');
	}
	elseif ($response->status != 1) {
		//Transaction failed
    if ($unattended) {
      watchdog('ideal_api', 'Your IDEAL payment has been canceled by you or by the IDEAL process. Please try again. Contact us if you have problems.', NULL, WATCHDOG_WARNING);
      return FALSE;
    }
    //inform the consumer
		drupal_set_message(t('Your IDEAL payment has been canceled by you or by the IDEAL process. Please try again. Contact us if you have problems.'), 'warning');
    drupal_goto($path_back_error);
	}
  else {
    //Update order as 'payed = 1'
    ideal_payment_api_order_update($order_id, 1);

    module_invoke_all('ideal_payed', $order_data);
    
    if ($unattended) {
      return FALSE;
    }
	  drupal_set_message(t('Thank you for shopping with us, your payment is processed sucessfuly'));

    // This lets us know it's a legitimate access of the complete page.
    $_SESSION['do_complete'] = TRUE;

    drupal_goto($path_back_succes);
  }
}
