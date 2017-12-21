<?php
if(!function_exists("escribirLog")) {
	require_once('apiRedsys/redsysLibrary.php');
}
if(!class_exists("RedsysAPI")) {
	require_once('apiRedsys/apiRedsysFinal.php');
}

class ControllerExtensionPaymentRedsys extends Controller {
	public function index() {
		$this->session->data["idLog"] = generateIdLog();
		$this->load->language('extension/payment/redsys'); 
		$data['button_confirm'] = $this->language->get('button_confirm');

		if ($this->config->get('payment_redsys_entorno') == 'Real') {
			$data['action'] = 'https://sis.redsys.es/sis/realizarPago/utf-8';
		} else if ($this->config->get('payment_redsys_entorno') == 'Sis-d') {
			$data['action'] = 'http://sis-d.redsys.es/sis/realizarPago/utf-8';		
		} else if ($this->config->get('payment_redsys_entorno') == 'Sis-i') {
			$data['action'] = 'https://sis-i.redsys.es:25443/sis/realizarPago/utf-8';
		} else if ($this->config->get('payment_redsys_entorno') == 'Sis-t') {
			$data['action'] = 'https://sis-t.redsys.es:25443/sis/realizarPago/utf-8';
		}	
  
		$this->load->model('checkout/order');
		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
		
		///////////////////////////////////////////////////////////////////////////////////
		//Obtengo los datos de configuración
		$data['Nombre']=$this->config->get('payment_redsys_nombre');
		$data['Fuc']=$this->config->get('payment_redsys_fuc');
		$data['Tipopago']=$this->config->get('payment_redsys_tipopago');
		$data['Clave256']=$this->config->get('payment_redsys_clave256');
		$data['Terminal']=$this->config->get('payment_redsys_term');
		$data['Moneda']=$this->config->get('payment_redsys_moneda');
		$data['Trans']=$this->config->get('payment_redsys_trans');
		$data['LogActivo']=$this->config->get('payment_redsys_log');
		$data['MantenerPedidoAnteError']=$this->config->get('payment_redsys_error_pedido');
		$data['Notif']=$this->config->get('payment_redsys_notif');
		$data['Ssl']=$this->config->get('payment_redsys_ssl');
		$data['Error']=$this->config->get('payment_redsys_error');
		$data['Idiomas']=$this->config->get('payment_redsys_idiomas');
			if($data['Idiomas']=="No"){
			$data['Idiomas']="0";
			}
			else {
				$idioma_web = substr($_SERVER["HTTP_ACCEPT_LANGUAGE"],0,2); 
				switch ($idioma_web) {
					case 'es':
					$idiomaFinal='001';
					break;
					case 'en':
					$idiomaFinal='002';
					break;
					case 'ca':
					$idiomaFinal='003';
					break;
					case 'fr':
					$idiomaFinal='004';
					break;
					case 'de':
					$idiomaFinal='005';
					break;
					case 'nl':
					$idiomaFinal='006';
					break;
					case 'it':
					$idiomaFinal='007';
					break;
					case 'sv':
					$idiomaFinal='008';
					break;
					case 'pt':
					$idiomaFinal='009';
					break;
					case 'pl':
					$idiomaFinal='011';
					break;
					case 'gl':
					$idiomaFinal='012';
					break;
					case 'eu':
					$idiomaFinal='013';
					break;
					default:
					$idiomaFinal='002';
				}		
			$data['Idiomas']=$idiomaFinal;
			}
			//Callback
			if($data['Ssl']=="No"){
				$data['Notify_url'] = $this->url->link('extension/payment/redsys/callback', '', 'SSL');
			} else {
				$data['Notify_url'] = $this->url->link('extension/payment/redsys/callback', '', 'SSL');
			}
		
		///////////////////////////////////////////////////////////////////////////////////	
		//Obtengo los datos del cliente
		$data['CustomerName'] = html_entity_decode($order_info['payment_firstname'] . ' ' . $order_info['payment_lastname'], ENT_QUOTES, 'UTF-8');
		$data['CustomerEMail'] = $order_info['email'];
		$data['BillingFirstnames'] = $order_info['payment_firstname'];
		$data['BillingSurname'] = $order_info['payment_lastname'];
		$data['BillingAddress1'] = $order_info['payment_address_1'];
		$data['BillingAddress2'] = $order_info['payment_address_2'];
		$data['BillingCity'] = $order_info['payment_city'];
		$data['BillingPostCode'] = $order_info['payment_postcode'];
		$data['BillingCountry'] = $order_info['payment_iso_code_2'];
		$data['BillingPhone'] = $order_info['telephone'];

		if ($this->cart->hasShipping()) {
			$data['DeliveryFirstnames'] = $order_info['shipping_firstname'];
			$data['DeliverySurname'] = $order_info['shipping_lastname'];
			$data['DeliveryAddress1'] = $order_info['shipping_address_1'];
			$data['DeliveryAddress2'] = $order_info['shipping_address_2'];
			$data['DeliveryCity'] = $order_info['shipping_city'];
			$data['DeliveryPostCode'] = $order_info['shipping_postcode'];
			$data['DeliveryCountry'] = $order_info['shipping_iso_code_2'];
			$data['DeliveryState'] = $order_info['shipping_zone_code'];
			$data['DeliveryPhone'] = $order_info['telephone'];
		} else {
			$data['DeliveryFirstnames'] = $order_info['payment_firstname'];
			$data['DeliverySurname'] = $order_info['payment_lastname'];
			$data['DeliveryAddress1'] = $order_info['payment_address_1'];
			$data['DeliveryAddress2'] = $order_info['payment_address_2'];
			$data['DeliveryCity'] = $order_info['payment_city'];
			$data['DeliveryPostCode'] = $order_info['payment_postcode'];
			$data['DeliveryCountry'] = $order_info['payment_iso_code_2'];
			$data['DeliveryState'] = $order_info['payment_zone_code'];
			$data['DeliveryPhone'] = $order_info['telephone'];			
		}
		//Resumen de un cliente
		$data['Titular'] = $data['DeliveryFirstnames']." ".$data['DeliverySurname']."/".$data['DeliveryAddress1']."/Telef:".$data['DeliveryPhone'];
		
		
		///////////////////////////////////////////////////////////////////////////////////
		//Obtengo los datos del pedido

		//Order_ID
		$data['Id']=str_pad($this->session->data['order_id'], 12, "0", STR_PAD_LEFT);

		//Desc. del pedido
		$data['Productos']="";
		foreach ($this->cart->getProducts() as $product) {
			$data['Productos'].=$product['name']."-".$product['model']."-".$product['quantity']."/";
		}		

		$totalcompra = $this->currency->format($order_info['total'], $order_info['currency_code'], false, false);
		
		//Precio del pedido
		$total = $this->currency->format($order_info['total'], $order_info['currency_code'], false, false);
		$transaction_amount = number_format( (float) $total, 2, '.', '' );
		$transaction_amount = str_replace('.','',$transaction_amount);
		$transaction_amount = floatval($transaction_amount);
		$data['Amount'] = $transaction_amount;
		
		
		///////////////////////////////////////////////////////////////////////////////////
		// Generamos la firma	
		
		if($data['Moneda']=="EURO"){
			$moneda="978";
		} else {
			$moneda="840";
		}
		$data['Moneda']=$moneda;
		$descripcion = $data['Titular'];
		$clave256  = $data['Clave256'];
		$cantidad = $data['Amount'];
		$pedido = $data['Id'];
		$codigo = $data['Fuc'];
		$moneda = $data['Moneda'];
		$trans	= $data['Trans'];
		$terminal = $data['Terminal'];
		$urltienda = $data['Notify_url'];
		//$urlok = $this->url->link('checkout/success', '', 'SSL');
		//$urlko = $this->url->link('checkout/failure', '', 'SSL');
		$idioma_tpv = $data['Idiomas'];
		$productos = $data['Productos'];
		$nombre = $data['Nombre'];
		$tipopago = $data['Tipopago'];
		
		$miObj = new RedsysAPI;
		$miObj->setParameter("DS_MERCHANT_AMOUNT",$cantidad);
		$miObj->setParameter("DS_MERCHANT_ORDER",strval($pedido));
		$miObj->setParameter("DS_MERCHANT_MERCHANTCODE",$codigo);
		$miObj->setParameter("DS_MERCHANT_CURRENCY",$moneda);
		$miObj->setParameter("DS_MERCHANT_TRANSACTIONTYPE",$trans);
		$miObj->setParameter("DS_MERCHANT_TERMINAL",$terminal);
		$miObj->setParameter("DS_MERCHANT_MERCHANTURL",$urltienda);
		$miObj->setParameter("DS_MERCHANT_URLOK",$urltienda);
		$miObj->setParameter("DS_MERCHANT_URLKO",$urltienda);
		//$miObj->setParameter("DS_MERCHANT_URLOK",$urlok);
		//$miObj->setParameter("DS_MERCHANT_URLKO",$urlko);
		$miObj->setParameter("Ds_Merchant_ConsumerLanguage",$idioma_tpv);
		$miObj->setParameter("Ds_Merchant_ProductDescription",$productos);
		$miObj->setParameter("Ds_Merchant_Titular",$nombre);
		$miObj->setParameter("Ds_Merchant_MerchantData",sha1($urltienda));
		$miObj->setParameter("Ds_Merchant_MerchantName",$nombre);
		$miObj->setParameter("Ds_Merchant_PayMethods",$tipopago);
		$miObj->setParameter("Ds_Merchant_Module","opencart_redsys_2.8.3");

		
		//Datos de configuración
		$version = "HMAC_SHA256_V1";
		
		//Clave del comercio que se extrae de la configuración del comercio
		// Se generan los parámetros de la petición
		$request = "";
		$paramsBase64 = $miObj->createMerchantParameters();
		$signatureMac = $miObj->createMerchantSignature($clave256);
		
		$data['version'] = $version;
		$data['paramsBase64'] = $paramsBase64;
		$data['signatureMac'] = $signatureMac;

		/////////////FIN CALCULO DE FIRMA
		return $this->load->view('extension/payment/redsys', $data);
	
	}

	
	public function callback() {
		
		//Estados de un pedido:
		//1 pending,2 processing,3 shipped,4 "",5 complete,6 "",7 canceled,8 denied,9 Canceled Reversal,10 Failed,11 Refunded,12 Reversed,13 Chargeback,14 Expired,15 Processed,16 Voided,17 "",
		$this->load->model('checkout/order');

		$logActivo	= $this->config->get('payment_redsys_log');
		$mantenerPedidoAnteError	= $this->config->get('payment_redsys_error_pedido');

		if (isset($this->request->get['Ds_MerchantParameters'])) {								
			// Recogemos la clave del comercio para autenticar
			$clave256     = $this->config->get('payment_redsys_clave256');	
			// Recogemos datos de respuesta
			$version      = $_GET["Ds_SignatureVersion"];
			$datos		  = $_GET["Ds_MerchantParameters"];
			$firma_remota = $_GET["Ds_Signature"];

			// Se crea Objeto
			$miObj = new RedsysAPI;
			
			/** Se decodifican los datos enviados y se carga el array de datos **/
			$decodec = $miObj->decodeMerchantParameters($datos);

			/** Clave **/
			$kc = $clave256;
			
			/** Se calcula la firma **/
			$firma_local = $miObj->createMerchantSignatureNotif($kc,$datos);
			
			/** Extraer datos de la notificación **/
			$total     = $miObj->getParameter('Ds_Amount');
			$pedido    = $miObj->getParameter('Ds_Order');
			$codigo    = $miObj->getParameter('Ds_MerchantCode');
			$terminal  = $miObj->getParameter('Ds_Terminal');
			$moneda    = $miObj->getParameter('Ds_Currency');
			$respuesta = $miObj->getParameter('Ds_Response');
			$fecha	   = $miObj->getParameter('Ds_Date');
			$hora	   = $miObj->getParameter('Ds_Hour');
			$id_trans  = $miObj->getParameter('Ds_AuthorisationCode');
			$tipoTrans = $miObj->getParameter('Ds_TransactionType');

			// Inicializamos el valor del status del pedido
			$status="";

			//Recuperamos Id_pedido
			$idPedido=$pedido;
			$idPedido=ltrim($idPedido,"0");
			
			// Validacion de firma y parámetros
			if ($firma_local === $firma_remota
					&& checkImporte($total)					
					&& checkPedidoNum($pedido)
					&& checkFuc($codigo)
					&& checkMoneda($moneda)
					&& checkRespuesta($respuesta)
			) {
				// Formatear variables
				$respuesta = intval($respuesta);

				if ($respuesta < 101){
						$this->model_checkout_order->addOrderHistory($idPedido, 5);
						$this->escribirLog("Pago aceptado. Para el pedido ".$idPedido,$logActivo);
						$this->response->redirect($this->url->link('checkout/success'));
				} else {	
						$this->model_checkout_order->addOrderHistory($idPedido, 7);
						$this->escribirLog("Pago rechazado. Para el pedido ".$idPedido,$logActivo);
						if($mantenerPedidoAnteError != 'Si') {
							if ($this->cart->hasProducts()) {
								$this->escribirLog("Se vacía el carrito.",$logActivo);
								$this->cart->clear();
							}
						}
						$this->response->redirect($this->url->link('checkout/failure'));
				}
			} else {
				$this->model_checkout_order->addOrderHistory($idPedido, 7);
				$this->escribirLog("Parámetros incorrectos.",$logActivo);
				if($firma_local !== $firma_remota) {
					$this->escribirLog("La firma no coincide.",$logActivo);
				}
				if(!checkImporte($total)) {
					$this->escribirLog("Formato de importe incorrecto.",$logActivo);
				}
				if(!checkPedidoNum($pedido)) {
					$this->escribirLog("Formato de número de pedido incorrecto.",$logActivo);
				}
				if(!checkFuc($codigo)) {
					$this->escribirLog("Formato de FUC incorrecto.",$logActivo);
				}
				if(!checkMoneda($moneda)) {
					$this->escribirLog("Formato de moneda incorrecto.",$logActivo);
				}
				if(!checkRespuesta($respuesta)) {
					$this->escribirLog("Formato de respuesta incorrecto.",$logActivo);
				}
				if(!checkFirma($firma_remota)) {
					$this->escribirLog("Formato de firma incorrecto.",$logActivo);
				}
				if ($this->cart->hasProducts()) {
					$this->escribirLog("Se vacía el carrito por seguridad.",$logActivo);
					$this->cart->clear();
				}
				$this->response->redirect($this->url->link('checkout/failure'));
			}
		} elseif (isset($this->request->post['Ds_MerchantParameters'])){
				
			$clave256     = $this->config->get('payment_redsys_clave256');	
			/** Recoger datos de respuesta **/
			$version     = $_POST["Ds_SignatureVersion"];
			$datos    = $_POST["Ds_MerchantParameters"];
			$firma_remota    = $_POST["Ds_Signature"];

			// Se crea Objeto
			$miObj = new RedsysAPI;
			
			/** Se decodifican los datos enviados y se carga el array de datos **/
			$decodec = $miObj->decodeMerchantParameters($datos);

			/** Clave **/
			$kc = $clave256;
			
			/** Se calcula la firma **/
			$firma_local = $miObj->createMerchantSignatureNotif($kc,$datos);
			
			/** Extraer datos de la notificación **/
			$total     = $miObj->getParameter('Ds_Amount');
			$pedido    = $miObj->getParameter('Ds_Order');
			$codigo    = $miObj->getParameter('Ds_MerchantCode');
			$terminal  = $miObj->getParameter('Ds_Terminal');
			$moneda    = $miObj->getParameter('Ds_Currency');
			$respuesta = $miObj->getParameter('Ds_Response');
			$fecha	   = $miObj->getParameter('Ds_Date');
			$hora	   = $miObj->getParameter('Ds_Hour');
			$id_trans  = $miObj->getParameter('Ds_AuthorisationCode');
			$tipoTrans = $miObj->getParameter('Ds_TransactionType');

			// Inicializamos el valor del status del pedido
			$status="";

			// Validacion de firma y parámetros
			if ($firma_local === $firma_remota
					&& checkImporte($total)					
					&& checkPedidoNum($pedido)
					&& checkFuc($codigo)
					&& checkMoneda($moneda)
					&& checkRespuesta($respuesta)
			) {
				// Formatear variables
				$respuesta = intval($respuesta);

				//Recuperamos Id_pedido
				$idPedido=$pedido;
				$idPedido=ltrim($idPedido,"0");
				$order = $this->model_checkout_order->getOrder($idPedido);

					
				if ($respuesta < 101) {
					$this->escribirLog("Pago POST aceptado. Para el pedido ".$idPedido,$logActivo);
					$this->model_checkout_order->addOrderHistory($idPedido, 5);
					//$this->response->redirect($this->url->link('checkout/success'));
				} else {
					$this->escribirLog("Pago POST rechazado. Para el pedido ".$idPedido,$logActivo);
					$this->model_checkout_order->addOrderHistory($idPedido, 7);
					if($mantenerPedidoAnteError != 'Si') {
						if ($this->cart->hasProducts()) {
							$this->escribirLog("Se vacía el carrito.",$logActivo);
							$this->cart->clear();
						}
					}
					//$this->response->redirect($this->url->link('checkout/failure'));
				}
			} else { 
				$this->escribirLog("Parámetros POST incorrectos.",$logActivo);
				if($firma_local !== $firma_remota) {
					$this->escribirLog("La firma no coincide.",$logActivo);
				}
				if(!checkImporte($total)) {
					$this->escribirLog("Formato de importe incorrecto.",$logActivo);
				}
				if(!checkPedidoNum($pedido)) {
					$this->escribirLog("Formato de número de pedido incorrecto.",$logActivo);
				}
				if(!checkFuc($codigo)) {
					$this->escribirLog("Formato de FUC incorrecto.",$logActivo);
				}
				if(!checkMoneda($moneda)) {
					$this->escribirLog("Formato de moneda incorrecto.",$logActivo);
				}
				if(!checkRespuesta($respuesta)) {
					$this->escribirLog("Formato de respuesta incorrecto.",$logActivo);
				}
				if(!checkFirma($firma_remota)) {
					$this->escribirLog("Formato de firma incorrecto.",$logActivo);
				}
				if ($this->cart->hasProducts()) {
					$this->escribirLog("Se vacía el carrito por seguridad.",$logActivo);
					$this->cart->clear();
				}
				$this->response->redirect($this->url->link('checkout/failure'));
			} // if (firma_local=firma_remota)		
		} else {
			$this->escribirLog("No hay respuesta del TPV.",$logActivo);
			echo ("No hay respuesta del TPV");
		}
	}

	public function escribirLog($texto,$activo) {
		if($activo=='Si'){
			if(!isset($this->session->data["idLog"])) {
				$this->session->data["idLog"] = generateIdLog();
			}
			$this->log->write("Redsys: ".$this->session->data["idLog"]." - ".$texto);
		}
	}
	
}
?>