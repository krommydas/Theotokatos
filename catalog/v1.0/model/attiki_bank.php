<?php
abstract class AttikiBankOrderStatus
{
	const Pending = 0;
	const Succeeded = 1;
	const Failed = 2;
	const NotFound = 3;
	const HashMismatch = 4;
	const Waiting = 5;
	const UnknownResponse = 6;
}
class ModelPaymentAttikiBank extends Model {
	
	public function __construct($registry) {
		$this->orders_table = DB_PREFIX."attiki_bank_order";
		parent::__construct($registry);
	}
	
	public function getMethod($address, $total) {
		$this->language->load('payment/attiki_bank');

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE 
				geo_zone_id = '" . (int)$this->getConfig('geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' 
				AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

		if ($this->getConfig('required_total') > 0 && $this->getConfig('required_total') > $total) {
			$status = false;
		} elseif (!$this->getConfig('geo_zone')) {
			$status = true;
		} elseif ($query->num_rows) {
			$status = true;
		} else {
			$status = false;
		}

		$status = $status && $this->isConfigured() && $this->isInstalled();
		
		$method_data = array();

		if ($status) {
			$method_data = array(
					'code'       => 'attiki_bank',
					'title'      => $this->language->get('text_title'),
					'sort_order' => $this->getConfig('sort_order')
			);
		}

		return $method_data;
	}
	
	public function insertOrder($id, $date) {
		$this->db->query("INSERT INTO $this->orders_table (order_id, datetime, status, last_update_time, creation_time) VALUES 
				(" . (int)$id . ", '" . $date . "', ". AttikiBankOrderStatus::Pending . ", NOW(), NOW())");
	}
	
	public function updateOrder($id, $status, $approval_code = null) {
		$this->db->query("update $this->orders_table set status = $status,". 
				($approval_code == null ? "" : "approval_code = $approval_code,").
				"last_update_time = NOW() where order_id = ".(int)$id);
	}
	
	public function deleteOrder($order_id) {
		$this->db->query("delete from $this->orders_table where order_id = ".(int)$order_id);
	}
	
	public  function getOrderDate($id) {
		$result = $this->db->query("SELECT datetime FROM $this->orders_table WHERE order_id = $id");
		
		if (!$result->num_rows)
			return false;
		
		return $result->row['datetime'];
	}
	
	private function isInstalled(){
		$result = $this->db->query("show tables like '$this->orders_table'");
		return $result->num_rows == 1;
	}
	
	private function isConfigured(){
		return $this->getConfig('store_id') != null && $this->getConfig('secret') != null &&
		$this->getConfig('order_status_succeeded') != null && in_array($this->currency->getCode(), $this->supported_currencies);
	}
	
	private function getConfig($key) {
		return $this->config->get('attiki_bank_'.$key);
	}
	
	private $orders_table;
	
	private $supported_currencies = array('EUR', 'USD');
}
?>