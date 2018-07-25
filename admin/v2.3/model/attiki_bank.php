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
	
	public function install() {
		$this->db->query("
			CREATE TABLE `".$this->orders_table."` (
				`order_id` int(11) NOT NULL,
				`datetime` varchar(20) NOT NULL,
				`status` tinyint NOT NULL,
				`approval_code` varchar(100) NULL,
				`last_update_time` datetime NOT NULL,
				`creation_time` datetime NOT NULL,
				PRIMARY KEY(`order_id`)
			) ENGINE=MyISAM DEFAULT COLLATE=utf8_general_ci");
	}

	public function uninstall() {
		$this->db->query("DROP TABLE IF EXISTS $this->orders_table");
	}

	public function hasHungOrders($hung_status) {
		
		$result = $this->db->query("SELECT * FROM $this->orders_table WHERE status = ".AttikiBankOrderStatus::Pending);

		if ($result->num_rows) 
			return true;
			
		return false;
	}
	
	private $orders_table;
}
?>