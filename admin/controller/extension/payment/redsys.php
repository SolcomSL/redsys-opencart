<?php 
class ControllerExtensionPaymentRedsys extends Controller {
	private $error = array(); 
 
	public function index() {
		$this->load->language('extension/payment/redsys');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate() ) {
			$this->model_setting_setting->editSetting('payment_redsys', $this->request->post);				

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', 'SSL'));
		}

		// RECOGIDA DE ERRORES
		
		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['nombre'])) {
			$data['error_nombre'] = $this->error['nombre'];
		} else {
			$data['error_nombre'] = '';
		}
		
		if (isset($this->error['fuc'])) {
			$data['error_fuc'] = $this->error['fuc'];
		} else {
			$data['error_fuc'] = '';
		}
		
		if (isset($this->error['clave256'])) {
			$data['error_clave256'] = $this->error['clave256'];
		} else {
			$data['error_clave256'] = '';
		}
		
		if (isset($this->error['term'])) {
			$data['error_term'] = $this->error['term'];
		} else {
			$data['error_term'] = '';
		}
		
		if (isset($this->error['trans'])) {
			$data['error_trans'] = $this->error['trans'];
		} else {
			$data['error_trans'] = '';
		}
		
		// FIN DE ERRORES
		
		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'user_token=' . $this->session->data['user_token'], 'SSL')
		);

		$data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_extension'),
			'href'      => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', 'SSL')      		
		);

		$data['breadcrumbs'][] = array(
			'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('extension/payment/redsys', 'user_token=' . $this->session->data['user_token'], 'SSL')
		);

		$data['action'] = $this->url->link('extension/payment/redsys', 'user_token=' . $this->session->data['user_token'], 'SSL');

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', 'SSL');

		
		
		//RECOGIDA DE PARAM.
		
		if (isset($this->request->post['payment_redsys_entorno'])) {
			$data['payment_redsys_entorno'] = $this->request->post['payment_redsys_entorno'];
		} else {
			$data['payment_redsys_entorno'] = $this->config->get('payment_redsys_entorno');
		}

		if (isset($this->request->post['payment_redsys_nombre'])) {
			$data['payment_redsys_nombre'] = $this->request->post['payment_redsys_nombre'];
		} else {
			$data['payment_redsys_nombre'] = $this->config->get('payment_redsys_nombre');
		}

		if (isset($this->request->post['payment_redsys_fuc'])) {
			$data['payment_redsys_fuc'] = $this->request->post['payment_redsys_fuc'];
		} else {
			$data['payment_redsys_fuc'] = $this->config->get('payment_redsys_fuc');
		}
		
		if (isset($this->request->post['payment_redsys_tipopago'])) {
			$data['payment_redsys_tipopago'] = $this->request->post['payment_redsys_tipopago'];
		} else {
			$data['payment_redsys_tipopago'] = $this->config->get('payment_redsys_tipopago');
		}

		if (isset($this->request->post['payment_redsys_clave256'])) {
			$data['payment_redsys_clave256'] = $this->request->post['payment_redsys_clave256'];
		} else {
			$data['payment_redsys_clave256'] = $this->config->get('payment_redsys_clave256');
		}
		
		if (isset($this->request->post['payment_redsys_term'])) {
			$data['payment_redsys_term'] = $this->request->post['payment_redsys_term'];
		} else {
			$data['payment_redsys_term'] = $this->config->get('payment_redsys_term');
		}

		if (isset($this->request->post['payment_redsys_moneda'])) {
			$data['payment_redsys_moneda'] = $this->request->post['payment_redsys_moneda'];
		} else {
			$data['payment_redsys_moneda'] = $this->config->get('payment_redsys_moneda');
		}
		
		if (isset($this->request->post['payment_redsys_trans'])) {
			$data['payment_redsys_trans'] = $this->request->post['payment_redsys_trans'];
		} else {
			$data['payment_redsys_trans'] = $this->config->get('payment_redsys_trans');
		}
	
		if (isset($this->request->post['payment_redsys_log'])) {
			$data['payment_redsys_log'] = $this->request->post['payment_redsys_log'];
		} else {
			$data['payment_redsys_log'] = $this->config->get('payment_redsys_log');
		}
		
		if (isset($this->request->post['payment_redsys_error_pedido'])) {
			$data['payment_redsys_error_pedido'] = $this->request->post['payment_redsys_error_pedido'];
		} else {
			$data['payment_redsys_error_pedido'] = $this->config->get('payment_redsys_error_pedido');
		}
		
		if (isset($this->request->post['payment_redsys_notif'])) {
			$data['payment_redsys_notif'] = $this->request->post['payment_redsys_notif'];
		} else {
			$data['payment_redsys_notif'] = $this->config->get('payment_redsys_notif');
		}
		
		if (isset($this->request->post['payment_redsys_ssl'])) {
			$data['payment_redsys_ssl'] = $this->request->post['payment_redsys_ssl'];
		} else {
			$data['payment_redsys_ssl'] = $this->config->get('payment_redsys_ssl');
		}
		
		if (isset($this->request->post['payment_redsys_error'])) {
			$data['payment_redsys_error'] = $this->request->post['payment_redsys_error'];
		} else {
			$data['payment_redsys_error'] = $this->config->get('payment_redsys_error');
		}
		
		if (isset($this->request->post['payment_redsys_idiomas'])) {
			$data['payment_redsys_idiomas'] = $this->request->post['payment_redsys_idiomas'];
		} else {
			$data['payment_redsys_idiomas'] = $this->config->get('payment_redsys_idiomas');
		}
		
		if (isset($this->request->post['payment_redsys_status'])) {
			$data['payment_redsys_status'] = $this->request->post['payment_redsys_status'];
		} else {
			$data['payment_redsys_status'] = $this->config->get('payment_redsys_status');
		}

		if (isset($this->request->post['payment_redsys_order_status_id'])) {
			$data['payment_redsys_order_status_id'] = $this->request->post['payment_redsys_order_status_id'];
		} else {
			$data['payment_redsys_order_status_id'] = $this->config->get('payment_redsys_order_status_id'); 
		} 

		$this->load->model('localisation/order_status');

		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
		
		
		if (isset($this->request->post['payment_redsys_sort_order'])) {
			$data['payment_redsys_sort_order'] = $this->request->post['payment_redsys_sort_order'];
		} else {
			$data['payment_redsys_sort_order'] = $this->config->get('payment_redsys_sort_order');
		}
		
		if (isset($this->request->post['payment_redsys_total'])) {
			$data['payment_redsys_total'] = $this->request->post['payment_redsys_total'];
		} else {
			$data['payment_redsys_total'] = $this->config->get('payment_redsys_total'); 
		} 

		if (isset($this->request->post['payment_redsys_geo_zone_id'])) {
			$data['payment_redsys_geo_zone_id'] = $this->request->post['payment_redsys_geo_zone_id'];
		} else {
			$data['payment_redsys_geo_zone_id'] = $this->config->get('payment_redsys_geo_zone_id');
		}
		
		$this->load->model('localisation/geo_zone');

		$data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();
		
		
		//FIN DE RECOGIDA DE PARAMS.

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/payment/redsys', $data));

 
	}
	private function validate() {
		
		if (!$this->user->hasPermission('modify', 'extension/payment/redsys')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!$this->request->post['payment_redsys_nombre']) {
			$this->error['nombre'] = $this->language->get('error_nombre');
		}

		if (!$this->request->post['payment_redsys_fuc']) {
			$this->error['fuc'] = $this->language->get('error_fuc');
		}

		if (!$this->request->post['payment_redsys_clave256']) {
			$this->error['clave256'] = $this->language->get('error_clave256');
		}

		if (!$this->request->post['payment_redsys_term']) {
			$this->error['terminal'] = $this->language->get('error_terminal');
		}
		
		if ($this->request->post['payment_redsys_trans']!="0") {
			$this->error['trans'] = $this->language->get('error_trans');
		}

		
		if (!$this->error) {
			return true;
		} else {
			return false;
		}	
	
	
	
	}
}
?>