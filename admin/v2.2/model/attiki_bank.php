<?php
class ModelPaymentAttikiBank extends Model {
	public function install() {
		$this->db->query("
			CREATE TABLE `".$this->orders_table."` (
				`order_id` int(11) NOT NULL,
				`datetime` datetime NOT NULL,
				PRIMARY KEY(`order_id`)
			) ENGINE=MyISAM DEFAULT COLLATE=utf8_general_ci");
	}

	public function uninstall() {
		$this->db->query("DROP TABLE IF EXISTS $this->orders_table");
	}

	public function hasHungOrders($hung_status) {
		
		$result = $this->db->query("SELECT * FROM $this->orders_table WHERE order_id in
				(select order_id from ".DB_PREFIX."order where order_status_id = $hung_status)");

		if ($result->num_rows) 
			return true;

		return false;
	}
	
	private $orders_table = DB_PREFIX . 'attiki_bank_order';
}
?>