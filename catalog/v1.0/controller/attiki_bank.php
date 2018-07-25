<?php
class ControllerPaymentAttikiBank extends Controller {
	protected function index() {
		
		$this->language->load('payment/attiki_bank');

		$this->data['button_confirm'] = $this->language->get('button_confirm');
		$this->data['initiate_error'] = $this->language->get('initiate_error');
		
		$this->data['initiate_url'] = $this->url->link('payment/attiki_bank/bankInitiateCallback');
		$this->data['return_url'] = $this->url->link('payment/attiki_bank/bankResponseCallback');
		
		$this->populateConfigData();
		
		$this->populateOrderInfo();
		
		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/attiki_bank.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/payment/attiki_bank.tpl';
		} else {
			$this->template = 'default/template/payment/attiki_bank.tpl';
		}

		$this->render();
	}

	public function bankInitiateCallback(){
		$this->load->model('payment/attiki_bank');
	
		$for_order_id = $this->request->get['order_id'];
	
		$response = array();
	
		if($this->model_payment_attiki_bank->getOrderDate($for_order_id)) {
			$this->language->load('payment/attiki_bank');
			$response['Error'] = $this->language->get('initiate_error_existing');
		}
		else {
			$order_info = $this->getActiveOrder();
			if($order_info['order_id'] == $for_order_id)
			{
				$date = date("Y:m:d-H:i:s");
				$response['Hash'] = $this->createSendHash($date, $order_info);
				$response['Date'] = $date;
				$this->model_payment_attiki_bank->insertOrder($for_order_id, $date);
			}
		}
	
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($response));
	}
	public function bankResponseCallback() {
		$this->language->load('payment/attiki_bank');
		$this->load->model('checkout/order');
		$this->load->model('payment/attiki_bank');
		
		if(!isset($this->request->post['oid']) || !isset($this->request->post['approval_code']) ||
				!isset($this->request->post['response_hash']))
		{
			if(isset($this->request->post['oid'])) 
				$this->model_payment_attiki_bank->updateOrder($this->request->post['oid'], AttikiBankOrderStatus::Failed);
			
			$this->redirect($this->url->link('common/home'));
		}
	
		$order_id = $this->request->post['oid'];
		$approval_code = $this->request->post['approval_code'];
		$hash = $this->request->post['response_hash'];
		$fail_details = isset($this->request->post['fail_reason']) ? $this->request->post['fail_reason'] : '';
	
		$this->load->model('checkout/order');
		$this->load->model('payment/attiki_bank');
	
		$order = $this->model_checkout_order->getOrder($order_id);
		$date = $this->model_payment_attiki_bank->getOrderDate($order_id);
	
		if(!$order || !$date)
		{
			$this->model_payment_attiki_bank->updateOrder($order_id, AttikiBankOrderStatus::NotFound);
			$this->redirect($this->url->link('common/home'));
		}
	
		if($hash != $this->createReceivedHash($date, $order, $approval_code))
		{
		    $this->model_payment_attiki_bank->updateOrder($order_id, AttikiBankOrderStatus::HashMismatch);
			$this->redirect($this->url->link('common/home'));
		}
	
		$this->processBankResponse($approval_code, $fail_details, $order);
	}
	
	/// Populate Data ///
	
	private function populateConfigData() {
		$this->data['action'] = 'https://test2.ipg-online.com/connect/gateway/processing';
		$this->data['store_name'] = $this->getConfigValue('store_id');
	}
	private function populateOrderInfo() {
		$order_info = $this->getActiveOrder();
		
		$this->data['order_id'] = $order_info['order_id'];
		$this->data['charge_total'] = $this->resolveTotalAmount($order_info);
		$this->data['currency'] = $this->resolveCurrency($order_info['currency_code']);
	}
	
	/// Order Manupilation ///
	
	private function getActiveOrder() {
		$this->load->model('checkout/order');
		$order_id = $this->session->data['order_id'];
		return $this->model_checkout_order->getOrder($order_id);
	}
	
	/// Utilities ///
	
	private function createSendHash($date, $order_info) {
		$total = $this->resolveTotalAmount($order_info);
		$storename = $this->getConfigValue('store_id');
		$sharedSecret = $this->getConfigValue('secret');
		$currency_code = $this->resolveCurrency($order_info['currency_code']);
		$stringToHash = $storename . $date . $total . $currency_code . $sharedSecret;
		$ascii = bin2hex($stringToHash);
		return hash('sha256',$ascii);
	}
	private function createReceivedHash($date, $order_info, $approval_code) {
		$total = $this->resolveTotalAmount($order_info);
		$total = str_replace('.', ',', $total); // bank is using comma instead of dot for it's response hash generation
		$storename = $this->getConfigValue('store_id');
		$sharedSecret = $this->getConfigValue('secret');
		$currency_code = $this->resolveCurrency($order_info['currency_code']);
		$stringToHash = $sharedSecret . $approval_code . $total . $currency_code . $date . $storename;
		$ascii = bin2hex($stringToHash);
		return hash('sha256',$ascii);
	}
	private function getConfigValue($key) {
		return  html_entity_decode($this->config->get('attiki_bank_'.$key));
	}
    private function resolveCurrency($currency) {
    	switch($currency)
    	{
    		case 'EUR': return 978;
    		case 'USD': return 840;
    		default: throw new Exception('unsupported currency');
    	}
    }
    private function resolveTotalAmount($order){
    	return $this->currency->format($order['total'], $order['currency_code'], $order['currency_value'], false);
    }
    
	/// Response Handlers ///
	
	private function processBankResponse($approval_code, $fail_reason, $order)
	{
		$result_code = substr($approval_code, 0, 1);
		if($result_code == "N")
		{
			$this->model_payment_attiki_bank->updateOrder($order['order_id'], AttikiBankOrderStatus::Failed);
			$this->generateErrorResponse('failed');
			return;
		}
		else if($result_code == "?")
		{
			$this->model_payment_attiki_bank->updateOrder($order['order_id'], AttikiBankOrderStatus::Waiting);
			$this->generateErrorResponse('waiting');
			return;
		}
		else if($result_code != "Y")
		{
			$this->model_payment_attiki_bank->updateOrder($order['order_id'], AttikiBankOrderStatus::UnknownResponse);
			$this->generateErrorResponse('unknown');
			return;
		}
		
		$this->model_checkout_order->confirm($order["order_id"], $this->getConfigValue('order_status_succeeded'));
		$this->model_payment_attiki_bank->updateOrder($order['order_id'], AttikiBankOrderStatus::Succeeded);
		$this->redirect($this->url->link('checkout/success'));
	}
    private function generateErrorResponse($error_message_key) {
		
		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/attiki_bank_failure.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/payment/attiki_bank_failure.tpl';
		} else {
			$this->template = 'default/template/payment/attiki_bank_failure.tpl';
		}
		
		$this->document->setTitle($this->language->get('text_title'));
		$this->data['heading_title'] = $this->language->get('text_title');
		$this->data['message'] = $this->language->get('error_'.$error_message_key);
		
		$this->children = array(
				'common/column_left',
				'common/column_right',
				'common/content_top',
				'common/content_bottom',
				'common/footer',
				'common/header'
		);
		
		$this->response->setOutput($this->render());
	}
}
?>