<?php
/**
 * @file
 * Database functions for ideal_payment API. 
 * CRUD for orders
 */

/**
 * Save order
 */
function ideal_payment_api_order_save(&$order) {
  $now = time();
  $order['order_id'] = db_next_id('ideal_payment_api_orders');
  $order['description'] = theme('ideal_payment_api_description', $order);
  
	$inserted = db_query("INSERT INTO {ideal_payment_api_orders} (oid, user_id, description, amount, issuer_id, transaction_id, payment_status, foreign_id, created_at, updated_at) VALUES
					(%d, %d,'%s', %d, '%s', '%s', %d, %d, %d, %d)", $order['order_id'], $order['user_id'], $order['description'],$order['amount'], $order['issuer_id'], $order['transaction_id'], $order['payment_status'], $order['foreign_id'], $now, $now);

	  
	if ($inserted) {
		return TRUE;
	}
	else {
		return FALSE;
	}
}

/**
 * Update order status
 */
function ideal_payment_api_order_update($order_id, $payment_status, $transaction_id = NULL) {
  $status = FALSE;
  $now = time();
  if (db_query("UPDATE {ideal_payment_api_orders} SET payment_status = %d, transaction_id = '%s', updated_at = %d WHERE oid = %d", $payment_status, $transaction_id, $now, $order_id)) {
    $status = TRUE;
  }
  return $status;
}

/**
 * Delete order
 */
function ideal_payment_api_order_delete($order_id) {
	if (db_query("DELETE FROM {ideal_payment_api_orders} WHERE oid = %d", $order_id)) {
		return TRUE;
	}
	else {
		return FALSE;
	}
}

/**
 * Load order
 */
function ideal_payment_api_order_load($order_id) {
	$result = db_query("SELECT * FROM {ideal_payment_api_orders} WHERE oid = %d", $order_id);
	$order = db_fetch_array($result);
	if (is_array($order)) {
		return $order;
	}
	else {
		return FALSE;
	}
}
