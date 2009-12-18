<?php
// $Id$

/**
 * @file
 * iDEAL payment module for Ubercart. No extra gateway needed. 
 * Include for iDEAL ING/PB Advanced & RABO Professional ThinMPI
 *
 * Development by Qrios | http://www.qrios.nl | c.kodde {at} qrios {dot} nl
 * 
 * 
 */

function uc_ideal_payment_api_call(&$arg1, $arg2) {

  $url_base = url(NULL, NULL, NULL, TRUE);
  $path_module = drupal_get_path('module', 'ideal_payment_api');
  ////Set errors on so we can see if there is a PHP error goes wrong
  //ini_set('display_errors',1);
  //error_reporting(E_ALL & ~E_NOTICE);

  //include ThinMPI and Directory-request en -response
  require_once($path_module.'/lib/ThinMPI.php');
  require_once($path_module.'/lib/DirectoryRequest.php');
  require_once($path_module.'/lib/DirectoryResponse.php');

  //Create a directory request
  $q_data = & new DirectoryRequest();
  
  //Create thinMPI instance
  $rule = new ThinMPI();

  //Process directory request
  $result = $rule->ProcessRequest($q_data);

	if(!$result->isOK()){
    $form_output.=t('Payment through iDEAL gateway not possible.').'<br>';
    $form_output.=t('Error message iDEAL').': ';
    $msg = $result->getErrorMessage();
    $form_output.=("$msg<br>");
  }
  else{
    //Get issuerlist
    $issuerArray = $result->getIssuerList();
    if(count($issuerArray) == 0){
      $form_output.=t('List with banks not available, payment through iDEAL gateway not possible.');
    }
    else{
      //Directory request succesful and at least 1 issuer
      $form_output.='<form action="'.$url_base.'ideal/ideal_payment_api_transreq" method="post" name="OrderForm">';
      
      for($i=0;$i<count($issuerArray);$i++){
        if($issuerArray[$i]->issuerList == "Short"){
          $issuerArrayShort[]=$issuerArray[$i];
        }
        else{
          $issuerArrayLong[]=$issuerArray[$i];
        }
      }
      //Create a selection list
      $form_output.='<select name="issuerID"  class="ideal_payment_api_dirreq_message_field">';
      $form_output.='<option value="0">'.t('Choose your bank...').'</option>';
      //Create an option tag for every issuer
      for($i=0;$i<count($issuerArrayShort);$i++){
        $form_output.=("<option value=\"{$issuerArrayShort[$i]->issuerID}\"> {$issuerArrayShort[$i]->issuerName} </option>");
      }
      if(count($issuerArrayLong) > 0){
        $form_output.='<option value="0">---'.t('Other banks').'---</option>';
      }
      for($i=0;$i<count($issuerArrayLong);$i++){
        $form_output.=("<option value=\"{$issuerArrayLong[$i]->issuerID}\"> {$issuerArrayLong[$i]->issuerName} </option>");
      }
      $form_output.='</select><br /><input class="ideal_payment_api_dirreq_message_button" name="Submit" type="submit" value="'.t('Go to my bank').' ->"></form>';
    }
  }
  /*END ThinMPI code for DirReq*/
 
  $url_base = url(NULL, NULL, NULL, TRUE);

  $redirect_declineurl = $url_base.'ideal';
  $redirect_exceptionurl = $url_base.'ideal';
  $redirect_cancelurl = $url_base.'ideal/ideal_payment_api_cancel';

  $redirect_message1 = t('Please choose the bank you have an account with...');
  $redirect_message2 = t('You will be returned to our shop after completing your IDEAL payment transaction.');

  $orderid = $arg1->order_id;
  $amount = $arg1->order_total * 100;   //amount *100

  $_SESSION['ideal_payment_api_order_id'] = $arg1->order_id;
  //Fill DirReq form session var
  $_SESSION['ideal_payment_api_dirreq_form']='
  <div class="ideal_payment_api_dirreq_message_top">
  '.$redirect_message1.'
  </div>
  <div class="ideal_payment_api_dirreq_container">
  <div align="right"><img src="https://www.qspeed.nl/httpsimg/lock.gif" alt="Secure Payment by Qrios" /></div>
  <div align="center" class="ideal_payment_api_dirreq_form">
  '.$form_output.'
  </div>
  <div class="ideal_payment_api_dirreq_message_bottom">
  '.$redirect_message2.'
  </div>
  </div>'
  ;
  //Fill TransReq session var
  $_SESSION['ideal_payment_api_transreq_data']= array(
    'orderid' => $arg1->order_id,
    'amount' => $arg1->order_total * 100,   //amount *100
  );
  drupal_goto('ideal/ideal_payment_api_dirreq');
  exit;
}


function uc_ideal_payment_api_transreq_call() {
  if ($_SESSION['ideal_payment_api_transreq_data'] !== FALSE) {
    $order_data = $_SESSION['ideal_payment_api_transreq_data'];
    $orderid = $order_data['orderid'];
    $amount = $order_data['amount'];
    
    unset($_SESSION['ideal_payment_api_transreq_data']);
    
    /*START ThinMPI code for TransrReq*/
    require_once(drupal_get_path('module', 'ideal_payment_api')."/lib/ThinMPI.php");
    require_once(drupal_get_path('module', 'ideal_payment_api')."/lib/AcquirerTrxRequest.php");

    $issuerID = check_plain($_POST['issuerID']);
    if(!$issuerID){
      drupal_set_message(t('You have not chosen a bank for IDEAL payment. For security reasons your input is cleared, please try again'));
      drupal_goto('ideal');
    }
    
    //Create TransactionRequest
    $q_data = & new AcquirerTrxRequest();
    
    //Set parameters for TransactionRequest
    $q_data -> setIssuerID($issuerID);
  	$q_data -> setPurchaseID($orderid);
  	$q_data -> setAmount($amount );
  	//Create ThinMPI instance
  	$rule = new ThinMPI();
  	$result = new AcquirerTrxResponse();
    
  	//Process Request
  	$result = $rule->ProcessRequest( $q_data );
  	
  	if($result->isOK()){
  		$transactionID = $result->getTransactionID();
      $status = 0;
      //transactionID save in dbs
      db_query("INSERT INTO uc_payment_ideal_payment_api (order_id, description, order_status, transaction_id) VALUES('$orderid','$description','$status','$transactionID')");

  		//Get IssuerURL and decode it
  		$ISSURL = $result->getIssuerAuthenticationURL();
  		$ISSURL = html_entity_decode($ISSURL);

  		//Redirect the browser to the issuer URL
  		header("Location: $ISSURL"); 
  		exit();
      
  	}else{
  		//TransactionRequest failed, inform the consumer
  		$Msg = $result->getErrorMessage();
  		drupal_set_message(t('Something went wrong in processing your IDEAL payment. IDEAL error:').'<br>'.$Msg);
      drupal_goto('ideal');
  	}
    
    /*END ThinMPI code for TransrReq*/
    
    return($ideal_payment_api_form );
  }
  else{
    drupal_goto('cart/');
  }
}


function uc_ideal_payment_api_statreq_call($arg1, $arg2) {
  $transaction_id= $_GET['trxid'];
  $order_id = $_GET['ec'];
  //echo $transaction_id;

  /*START ThinMPI code for TransrReq*/
  require_once(drupal_get_path('module', 'ideal_payment_api')."/lib/ThinMPI.php");
  require_once(drupal_get_path('module', 'ideal_payment_api')."/lib/AcquirerStatusRequest.php");

	//Create StatusRequest
	$q_data = & new AcquirerStatusRequest();
  
  $transID = str_pad($transaction_id, 16, "0");
	$q_data -> setTransactionID($transID);

	//Create ThinMPI instance and process request
	$rule = new ThinMPI();
	$result = $rule->ProcessRequest( $q_data );
	
	if(!$result->isOK())
	{
		//StatusRequest failed, let the consumer click to try again
    $Msg = $result->getErrorMessage();
    drupal_set_message(t('We could not verify the payment status automaticaly, we will check your payment manualy, pleas contact us regarding this. IDEAL error:')).'<br>'.$Msg;
    drupal_goto('ideal');
	}
	else if(!$result->isAuthenticated())
	{
		//Transaction failed, inform the consumer
		drupal_set_message(t('Your IDEAL payment has been canceled by you or by the IDEAL process. Please try again or go back to select another payment method.'), 'ERROR');
    if ($order_id == $_SESSION['ideal_payment_api_order_id']) { //Check if orer_id is valid
      // This lets us know it's a legitimate access of the review page.
      $_SESSION['do_review'] = TRUE;
      // Ensure the cart we're looking at is the one that payment was attempted for.
      $_SESSION['cart_order'] = uc_cart_get_id();
      drupal_goto('ideal/review');
    }else{
        drupal_goto('cart');
    }
	}else{
		drupal_set_message(t('Thank you for shopping with us, your payment is processed sucessfuly'));
		$transactionID = $result->getTransactionID();
		//Here you should retrieve the order from the database, mark it as "payed"
    $order = uc_order_load($order_id);
    if ($order == FALSE) { //Check if order exist
      watchdog('ideal_api', t('iDeal payment completion attempted for non-existent order.'), WATCHDOG_ERROR);
      return;
    }
    //uc_order_update_status($order_id, 1);   *Uitgezet 281107 KK
    uc_order_update_status($order->order_id, uc_order_state_default('post_checkout'));
    
    //Todo??
    //uc_payment_enter($order_id, 'ideal_payment_api', $payment_amount, $order->uid, NULL, $comment);
    //uc_cart_complete_sale($order);
    //uc_order_comment_save($order_id, 0, t('iDeal Pro reported a payment of !amount !currency.', array('!amount' => uc_currency_format($payment_amount, FALSE), '!currency' => $payment_currency)), 'admin');
    
    unset($_SESSION['ideal_payment_api_order_id']);
    // This lets us know it's a legitimate access of the complete page.
    $_SESSION['do_complete'] = TRUE;

    drupal_goto('ideal/complete');
    exit();
  }
}
