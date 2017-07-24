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
if (!defined('_PS_VERSION_'))
	exit ;
class nextpay extends PaymentModule {

	private $_html = '';
	private $_postErrors = array();

	public function __construct() {

		$this->name = 'nextpay';
		$this->tab = 'payments_gateways';
		$this->version = '1.0';
		$this->author = 'Nextpay Co.';
		$this->currencies = true;
		$this->currencies_mode = 'radio';
		parent::__construct();
		$this->displayName = $this->l('NextPay Payment Modlue');
		$this->description = $this->l('Online Payment With NextPay');
		$this->confirmUninstall = $this->l('Are you sure you want to delete your details?');
		if (!sizeof(Currency::checkPaymentCurrencies($this->id)))
			$this->warning = $this->l('No currency has been set for this module');
		$config = Configuration::getMultiple(array('nextpay_API'));
		if (!isset($config['nextpay_API']))
			$this->warning = $this->l('You have to enter your nextpay merchant key to use nextpay for your online payments.');

	}

	public function install() {
		if (!parent::install() || !Configuration::updateValue('nextpay_API', '') || !Configuration::updateValue('nextpay_LOGO', '')  || !$this->registerHook('payment') || !$this->registerHook('paymentReturn'))
			return false;
		else
			return true;
	}

	public function uninstall() {
		if (!Configuration::deleteByName('nextpay_API') || !Configuration::deleteByName('nextpay_LOGO') || !parent::uninstall())
			return false;
		else
			return true;
	}

	public function hash_key() {
		$en = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z');
		$one = rand(1, 26);
		$two = rand(1, 26);
		$three = rand(1, 26);
		return $hash = $en[$one] . rand(0, 9) . rand(0, 9) . $en[$two] . $en[$tree] . rand(0, 9) . rand(10, 99);
	}

	public function getContent() {

		if (Tools::isSubmit('nextpay_setting')) {

			Configuration::updateValue('nextpay_API', $_POST['nextpay_API']);
			Configuration::updateValue('nextpay_LOGO', $_POST['zp_LOGO']);
			$this->_html .= '<div class="conf confirm">' . $this->l('Settings updated') . '</div>';
		}

		$this->_generateForm();
		return $this->_html;
	}

	private function _generateForm() {
		$this->_html .= '<div align="center"><form action="' . $_SERVER['REQUEST_URI'] . '" method="post">';
		$this->_html .= $this->l('Enter your API Key :') . '<br/><br/>';
		$this->_html .= '<input type="text" name="nextpay_API" value="' . Configuration::get('nextpay_API') . '" ><br/><br/>';
		$this->_html .= '<input type="submit" name="nextpay_setting"';
		$this->_html .= 'value="' . $this->l('Save it!') . '" class="button" />';
		$this->_html .= '</form><br/></div>';
	}

	public function do_payment($cart) {
		error_reporting(E_ALL);
		ini_set('display_errors', 1);
		
		include_once("nextpay_payment.php");

		
		$api_key = Configuration::get('nextpay_API');
		$amount = floatval(number_format($cart ->getOrderTotal(true, 3), 2, '.', ''));
		$callbackUrl = (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . __PS_BASE_URI__ . 'modules/nextpay/nextpay.php?do=call_back&id=' . $cart ->id . '&amount=' . $amount;
		$order_id = $cart ->id;
		$params = array(
			'api_key'  =>  $api_key,
		        'amount'      => $amount,
                        'order_id'      => $order_id,
			'callback_uri' => $callbackUrl
		);
		
		$nxClass = new Nextpay_Payment($params);

		$result = $nxClass->token();
		$trans_id = $result->trans_id;
		$code = intval($result->code);
		if($code == -1){		  
		  echo $this->success($this->l('Redirecting...'));
		  $result = $nxClass->send($trans_id);
		}
		else{		  
		  echo $this->error($this->l('There is a problem.') . ' (' . $nxClass->code_error($code) . ')');
		}
		
	}

	public function error($str) {
		return '<div class="alert error">' . $str . '</div>';
	}

	public function success($str) {
		echo '<div class="conf confirm">' . $str . '</div>';
	}

	public function hookPayment($params) {
		global $smarty;
		$smarty ->assign('nextpay_logo', Configuration::get('nextpay_LOGO'));
		if ($this->active)
			return $this->display(__FILE__, 'nextpay_payment.tpl');
	}

	public function hookPaymentReturn($params) {
		if ($this->active)
			return $this->display(__FILE__, 'nextpay_confirm.tpl');
	}

}

// End of: nextpay.php
?>
