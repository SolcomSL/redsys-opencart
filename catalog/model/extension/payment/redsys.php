<?php
class ModelExtensionPaymentRedsys extends Model {
	public function getMethod($address, $total) {
		$this->load->language('extension/payment/redsys');

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('redsys_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

		if ($this->config->get('payment_redsys_total') > 0 && $this->config->get('payment_redsys_total') > $total) {
			$status = false;
		} elseif (!$this->config->get('payment_redsys_geo_zone_id')) {
			$status = true;
		} elseif ($query->num_rows) {
			$status = true;
		} else {
			$status = false;
		}

		$method_data = array();

		if ($status) {  
			$method_data = array(
				'code'       => 'redsys',
				'title'      => $this->language->get('text_title'),
				'terms' => '',
				'sort_order' => $this->config->get('payment_redsys_sort_order')
			);
		}

		return $method_data;
	}
}
?>