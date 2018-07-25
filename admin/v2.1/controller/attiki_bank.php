<?php
class ControllerPaymentAttikiBank extends Controller {
	private $errors = array();

	public function index() {
		$this->load->model('localisation/geo_zone');
		$this->load->model('localisation/order_status');
		$this->load->model('setting/setting');
		$this->language->load('payment/attiki_bank');

		$isPost =  $this->request->server['REQUEST_METHOD'] == 'POST';
		
		if ($isPost && $this->validate()) {
			
			$data = array();
			foreach($this->request->post as $key => $value) $data[$this->resolveDataKey($key)] = $value;
			
			$this->model_setting_setting->editSetting('attiki_bank', $data);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'));
		}
		
		$edit_fields = array_merge(array('secret', 'store_id', 'order_status' => $this->order_statuses), $this->defaultEditDataKeys);
		$this->populateEditData($edit_fields, $isPost);

		$this->document->setTitle($this->language->get('heading_title'));

		$this->applyLanguageToData();
		
		$this->applyBreadcrumToData();
		
	    $this->applyMiscToData();

		$this->children = array(
				'common/header',
				'common/footer'
		);

		$this->template = 'payment/attiki_bank.tpl';

		$this->response->setOutput($this->render());
	}

	public function install() {
		$this->load->model('payment/attiki_bank');
		$this->load->model('setting/setting');
		$this->model_payment_attiki_bank->install();
		//$this->model_setting_setting->editSetting('amazon_checkout', $this->settings);
	}
	
	public function uninstall() {
		$this->load->model('payment/attiki_bank');
		
		$hung_status = $this->config->get($this->resolveDataKey($this->resolveOrderStatusKey('pending')));
		if(!empty($hung_status) && $this->model_payment_attiki_bank->hasHungOrders($hung_status))
		{
			$this->language->load('payment/attiki_bank');
			$this->session->data['error'] = $this->language->get('error_hung_orders');
			
			// revert uninstall from caller class
			// TODO: can not revert settings deletion
			$this->load->model('setting/extension');
			$this->model_setting_extension->install('payment', $this->request->get['extension']);
			
			$this->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'));
		}
		
		$this->model_payment_attiki_bank->uninstall();
	}
	
	##### Data Apply ######
	
    private function applyLanguageToData() {

    	$this->data['heading_title'] = $this->language->get('heading_title');
    	
    	$this->data['text_enabled'] = $this->language->get('text_enabled');
    	$this->data['text_disabled'] = $this->language->get('text_disabled');
    	$this->data['text_all_zones'] = $this->language->get('text_all_zones');
    	
    	$order_statuses_lang = array();
    	foreach($this->order_statuses as $status)
    		$order_statuses_lang[$status] = $this->language->get($this->resolveOrderStatusKey($status));
    	$this->data['order_statuses_lang'] = $order_statuses_lang;	
    	
    	$this->data['entry_secret'] = $this->language->get('entry_secret');
    	$this->data['entry_store_id'] = $this->language->get('entry_store_id');
    	$this->data['entry_total'] = $this->language->get('entry_total');
    	$this->data['entry_order_status'] = $this->language->get('entry_order_status');
    	$this->data['entry_geo_zone'] = $this->language->get('entry_geo_zone');
    	$this->data['entry_status'] = $this->language->get('entry_status');
    	$this->data['entry_sort_order'] = $this->language->get('entry_sort_order');
    	
    	$this->data['button_save'] = $this->language->get('button_save');
    	$this->data['button_cancel'] = $this->language->get('button_cancel');
    	 
    }
    private function applyBreadcrumToData() {
    	$this->data['breadcrumbs'] = array();
    	
    	$this->data['breadcrumbs'][] = array(
    			'text' => $this->language->get('text_home'),
    			'href' => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
    			'separator' => false
    	);
    	
    	$this->data['breadcrumbs'][] = array(
    			'text' => $this->language->get('text_payment'),
    			'href' => $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'),
    			'separator' => ' :: '
    	);
    	
    	$this->data['breadcrumbs'][] = array(
    			'text' => $this->language->get('heading_title'),
    			'separator' => ' :: '
    	);
    	 
    }
    private function applyMiscToData(){
    	$this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();
    	$this->data['order_statuses_suggestions'] = $this->model_localisation_order_status->getOrderStatuses();
    	
    	$this->data['errors'] = $this->errors;
    	$this->data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token']);
    }

    ##### Data Populate ######
    
    private function populateEditData(array $dataKeys, $usePostData)
    {
    	foreach($dataKeys as $key => $value)
    	{
    		if(is_array($value))
    			$this->data[$key] = $this->getArrayEditValue($value, $usePostData);
    		else 
    			$this->data[$value] = $this->getSingleEditValue($value, $usePostData);
    	}
    }
    
    private function getArrayEditValue(array $array, $usePostData)
    {
    	$result = array();
    	foreach($array as $key) $result[$key] = $this->getSingleEditValue($key, $usePostData);
    	return $result;
    }
    private function getSingleEditValue($key, $usePostData)
    {
    	$keyResolved = $this->resolveDataKey($key);
    	if($usePostData && isset($this->request->post[$key]))
    		return $this->request->post[$key];
    	else if($this->config->has($keyResolved))
    		return $this->config->get($keyResolved);
    	else if(isset ($this->defaultEditDataValues[$key]))
    		return $this->defaultEditDataValues[$key];
    	
    	return '';
    }
    
    private function resolveDataKey($key)
    {
    	if(in_array($key, $this->order_statuses)) 
    		return $this->resolveDataKey($this->resolveOrderStatusKey($key));
    		
    	return $this->dataPrefix.'_'.$key;
    }
    private function resolveOrderStatusKey($key)
    {
    	return 'order_status_'.$key;
    }
    
    ##### Validation ######
    
    protected function validate() {
    	
    	$this->errors = array();
    	
    	$this->load->model('localisation/currency');
    
    	if (!$this->user->hasPermission('modify', 'payment/attiki_bank')) {
    		$this->errors[] = $this->language->get('error_permission');
    	}
    
    	$currency_code = 'EUR';
    	$enabled_currency_status = '1';
    
    	$currency = $this->model_localisation_currency->getCurrency($this->currency->getId($currency_code));
    
    	if (empty($currency) || $currency['status'] != $enabled_currency_status) {
    		$this->errors[] = sprintf($this->language->get('error_curreny'), $currency_code);
    	}
    	
    	$this->validateEditData();
    	
    	$edit_fields = array_merge(array('secret', 'store_id'), $this->order_statuses, $this->defaultEditDataKeys);
    	$malicious_data = array_diff(array_keys($this->request->post), $edit_fields);	
    	if(count($malicious_data) > 0)
    		$this->errors[] = sprintf($this->language->get('error_malicious_data'));
    	
    	return empty($this->errors);
    }
    private function validateEditData()
    {
    	$required_total = $this->request->post['required_total'];
    	if(!empty($required_total) && (!is_numeric($required_total) || floatval($required_total) <= 0))
    		$this->errors[] = sprintf($this->language->get('error_required_total'));
    	
    	$sort_order = $this->request->post['sort_order'];
    	if(!empty($sort_order) && (!is_numeric($sort_order) || !ctype_digit($sort_order) || intval($sort_order) < 0))
    		$this->errors[] = sprintf($this->language->get('error_sort_order'));
    	
    	$geo_zone = $this->request->post['geo_zone'];
    	if($geo_zone != 0 && !$this->optionValueExists($geo_zone, $this->model_localisation_geo_zone->getGeoZones(), 'geo_zone_id'))
    		$this->errors[] = sprintf($this->language->get('error_geo_zone'));
    	
    	$secret = $this->request->post['secret'];
    	if(empty($secret))
    		$this->errors[] = sprintf($this->language->get('error_secret'));
    	
    	$store_id = $this->request->post['store_id'];
    	if(empty($store_id))
    		$this->errors[] = sprintf($this->language->get('error_store_id'));
    		
    	$status = $this->request->post['status'];
    	if($status != '0' && $status != '1')
    		$this->errors[] = sprintf($this->language->get('error_status'));
    	
    	$this->validateOrderStatusesEditData();
    }
    private function validateOrderStatusesEditData()
    {
    	$statuses = array();
    	foreach($this->order_statuses as $status)
    	{
    		$order_status = $this->request->post[$status];
    		$statuses[$status] = $order_status;
    		if(!$this->optionValueExists($order_status, $this->model_localisation_order_status->getOrderStatuses(), 'order_status_id'))
    			$this->errors[] = sprintf($this->language->get('error_order_status'));
    	}
    	
    	if(count(array_count_values($statuses)) < 4)
    		$this->errors[] = sprintf($this->language->get('error_order_status_duplicate'));
    }
    private function optionValueExists($value, $possible_values, $value_key)
    {
    	foreach($possible_values as $item)
    		if($item[$value_key] == $value)
    			return true;
    		
    	return false;
    }
    
    ##### Defaults ######
    
    private $defaultEditDataKeys = array('geo_zone', 'status', 'sort_order', 'required_total');
    
    // processing, all zones
    private $defaultEditDataValues = array('order_status' => '2', 'geo_zone' => '0');
    
    private $dataPrefix = 'attiki_bank';
    
    private $order_statuses = array('created', 'pending', 'succeeded', 'failed');
}
?>