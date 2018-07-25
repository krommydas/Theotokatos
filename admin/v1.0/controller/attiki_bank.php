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
		
		$this->populateEditData($this->defaultEditDataKeys, $isPost);

		$this->document->setTitle($this->language->get('heading_title'));

		$this->applyLanguagToData();
		
		$this->applyBreadcrumToData();
		
	    $this->applyMiscToData();

		$this->children = array(
				'common/header',
				'common/footer'
		);

		$this->template = 'payment/attiki_bank.tpl';

		$this->response->setOutput($this->render());
	}

	##### Data Apply ######
	
    private function applyLanguagToData() {

    	$this->data['heading_title'] = $this->language->get('heading_title');
    	
    	$this->data['text_enabled'] = $this->language->get('text_enabled');
    	$this->data['text_disabled'] = $this->language->get('text_disabled');
    	$this->data['text_all_zones'] = $this->language->get('text_all_zones');
    	
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
    	$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
    	
    	$this->data['errors'] = $this->errors;
    	$this->data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token']);
    }

    ##### Data Populate ######
    
    private function populateEditData(array $dataKeys, $usePostData)
    {
    	$this->data['dataKeyPrefix'] = $this->dataPrefix;
    	foreach($dataKeys as $key)
    	{
    		$value = '';
    		$keyResolved = $this->resolveDataKey($key);
    		if($usePostData && isset($this->request->post[$key]))
    			$value = $this->request->post[$key];
    		else if($this->config->has($keyResolved))
    			$value = $this->config->get($keyResolved);
    		else if(isset ($this->defaultEditDataValues[$key]))
    			$value = $this->defaultEditDataValues[$key];
    			
    		$this->data[$key] = $value;
    	}	
    }
    
    private function resolveDataKey($key)
    {
    	return $this->dataPrefix.'_'.$key;
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
    	
    	$malicious_data = array_diff(array_keys($this->request->post), $this->defaultEditDataKeys);	
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
    	if(!empty($sort_order) && (!is_numeric($sort_order) || !is_int($sort_order) || intval($sort_order) < 0))
    		$this->errors[] = sprintf($this->language->get('error_sort_order'));
    	
    	$geo_zone = $this->request->post['geo_zone'];
    	if($geo_zone != 0 && !$this->optionValueExists($geo_zone, $this->model_localisation_geo_zone->getGeoZones(), 'geo_zone_id'))
    		$this->errors[] = sprintf($this->language->get('error_geo_zone'));
    	
    	$order_status = $this->request->post['order_status'];
    	if(!$this->optionValueExists($order_status, $this->model_localisation_order_status->getOrderStatuses(), 'order_status_id'))
    		$this->errors[] = sprintf($this->language->get('error_order_status'));
    	
    	$status = $this->request->post['status'];
    	if($status != '0' && $status != '1')
    		$this->errors[] = sprintf($this->language->get('error_status'));
    }
    private function optionValueExists($value, $possible_values, $value_key)
    {
    	foreach($possible_values as $item)
    		if($item[$value_key] == $value)
    			return true;
    		
    	return false;
    }
    
    ##### Defaults ######
    
    private $defaultEditDataKeys = array('geo_zone', 'status', 'sort_order', 'order_status', 'required_total');
    
    // processing, all zones
    private $defaultEditDataValues = array('order_status' => '2', 'geo_zone' => '0');
    
    private $dataPrefix = 'attiki_bank';
}
?>