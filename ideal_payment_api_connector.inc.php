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

/**
 * Calls a transaction request
 */
function ideal_payment_api_transreq_call($order) {
  //Get user ID
  global $user;
  if ($user) {
    $user_id = $user->uid;
  }

  $path_module = drupal_get_path('module', 'ideal_payment_api');
  require_once($path_module.'/lib/iDEALConnector.php');
  $iDEALConnector = new iDEALConnector();
  
  $order['description'] = check_plain($order['description']);
  if (drupal_strlen($order['description']) > 32) {//@TODO: run this trough a general error handler.
    $order['description_orig'] = $order['description'];
    $order['description'] = drupal_substr($order['description'], 0, 32);
    watchdog('ideal_api', t('iDEAL decription too long. Changed from %orig to %shortened', array('%orig' => $order['description_orig'], '%shortened' => $order['description'])));
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
    return $response;
	}
  else {
    watchdog('ideal_api', $response->errCode.': '.$response->errMsg, NULL, WATCHDOG_ERROR);
    return $response;
	}
}

/** 
 * Calls the STATUS request
 **/
function ideal_payment_api_statreq_call($order) {
  //include connector
  $path_module = drupal_get_path('module', 'ideal_payment_api');
  require_once($path_module.'/lib/iDEALConnector.php');
  //Initialise connector
  $iDEALConnector = new iDEALConnector();
dvm($order);
	//Create StatusRequest
	$response = $iDEALConnector->RequestTransactionStatus($order['transaction_id']);

  //$transID = str_pad($transaction_id, 16, "0"); //Delete??
  return $response;
}
