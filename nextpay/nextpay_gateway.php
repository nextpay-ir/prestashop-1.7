<?php
/**
 * Created by NextPay.ir
 * author: Nextpay Company
 * ID: @nextpay
 * Date: 07/24/2017
 * Time: 12:35 PM
 * Website: NextPay.ir
 * Email: info@nextpay.ir
 * @copyright 2017
 * @package NextPay_Gateway
 * @version 1.0
 */
@session_start();
if (isset($_GET['do'])) {
	include (dirname(__FILE__) . '/../../config/config.inc.php');
	include (dirname(__FILE__) . '/../../header.php');
	include_once (dirname(__FILE__) . '/nextpay.php');
	include_once (dirname(__FILE__) . '/nextpay_payment.php');
	$nextpay = new nextpay;
	if ($_GET['do'] == 'payment') {
	  $nextpay -> do_payment($cart);
	} else {
		if (isset($_GET['id']) && isset($_GET['amount']) && isset($_POST['trans_id']) && isset($_POST['order_id'])) {
			$order_id = $_GET['id'];
			$amount = $_GET['amount'];
			$trans_id = $_POST['order_id'];
			if (isset($_SESSION['order' . $orderId])) {
				$api_key = Configuration::get('nextpay_API');
				
				$params = array(
					'api_key'  =>  $api_key,
					'amount'      => $amount,
					'order_id'      => $order_id,
					'trans_id' => $trans_id
				);
				
				$nxClass = new Nextpay_Payment();

				$result = $nxClass->verify_request($params);
				$code = intval($result);
				if($code == 0){		  
				  $nextpay -> validateOrder($orderId, _PS_OS_PAYMENT_, $amount, $nextpay -> displayName, "سفارش تایید شده / کد رهگیری {$trans_id}", array(), $cookie -> id_currency);
				  $_SESSION['order' . $orderId] = '';
				  Tools::redirect('history.php');
				}
				else{		  
				  echo $nextpay -> error($nextpay -> l('There is a problem.') . ' (' . $result . ')<br/>' . $nxClass -> code_error($result) . ' : ' . $result);
				  $nxClass -> show_error($nxClass -> code_error($result));
				}

			} else {
				echo $nextpay -> error($nextpay -> l('There is a problem.'));
			}
		} else {
			echo $nextpay -> error($nextpay -> l('There is a problem.'));
		}
	}
	include_once (dirname(__FILE__) . '/../../footer.php');
} else {
	_403();
}
function _403() {
	header('Status: 403 Forbidden');
	header('HTTP/1.1 403 Forbidden');
	exit();
}
