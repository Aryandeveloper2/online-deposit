<?php
class ControllerExtensionModuleOnlineDeposit extends Controller {
	private $error = array();

	public function index() {
	    
	    
	    
      
		$this->load->language('extension/module/online_deposit');

		$this->load->model('catalog/manufacturer');

		$this->load->model('catalog/category');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');
		$this->load->model('setting/module');
		$this->load->model('extension/module/online_deposit');

		$this->load->model('tool/image');
		

        
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
    
            $action = isset($this->request->post["action"]) ? $this->request->post["action"] : "";

            //  var_dump($this->request->post); exit;
            $this->model_setting_setting->editSetting('online_deposit',['online_deposit' => $this->request->post ]);

			$this->session->data['success'] = $this->language->get('text_success');
			
            switch($action){
    			case "save":	
    				$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token']. '&type=module', 'SSL'));
    			break;
    			case "save_edit":
    				$this->response->redirect($this->url->link('extension/module/online_deposit', '&user_token=' . $this->session->data['user_token'], 'SSL'));
    			break;
    		}
            
        }
        
        

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['name'])) {
			$data['error_name'] = $this->error['name'];
		} else {
			$data['error_name'] = '';
		}
		
		
		if (isset($this->error['price_list'])) {
			$data['error_price_list'] = $this->error['price_list'];
		} else {
			$data['error_price_list'] = '';
		}


		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/module/online_deposit', 'user_token=' . $this->session->data['user_token'] , true)
		);
	

		
	

		$data['user_token']  = $this->session->data['user_token'];

		$data['save_section'] = $this->load->controller('extension/component/save_section', [
			'form_id' 		=> "form-module",
			'save_new' 		=> false,
			'save_edit' 	=> true,
			'save' 			=> true,
			'cancel' 		=> $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true),
		]);

		$this->load->model('localisation/language');

		$data['languages'] = $this->model_localisation_language->getLanguages();
		
		$data['default_language_id'] = $this->config->get("config_language_id");
        
        
         
        if($this->config->get('online_deposit')) {
            $module_info = $this->config->get('online_deposit');
           $data = array_merge($data, $module_info);
        } elseif(isset($this->request->post)) {
             $module_info = $this->request->post;
           $data = array_merge($data, $module_info);
        }
        
        
        if(!isset($data['selected_payment_methods'])) {
            $data['selected_payment_methods'] = [];
        }
        
        
        $this->load->model('extension/module/online_deposit');

        $data['online_deposits'] = [];
        $online_deposits  = $this->model_extension_module_online_deposit->getOnlineDeposits([
            'order' => "DESC"
        ]);
        
        foreach($online_deposits as $key=> $deposit) {
           $data['online_deposits'][$key] = $deposit;
           
           $timestamp = strtotime($deposit['date_added']);
           $data['online_deposits'][$key]['date_added'] = jdate('Y/m/d H:i:s', $timestamp);   
           
           
             $data['online_deposits'][$key]['price'] = $this->currency->format($deposit['price'], $this->session->data['currency']);
           
           

        }
        
        $data['checkout_pro_gateway'] = $this->config->get('checkout_pro_gateway');

        $this->load->model('extension/module/checkout_pro');
        $data['payment_methods'] = [];
        $payment_methods = $this->model_extension_module_checkout_pro->getPaymentMethods();
        foreach($payment_methods as $key => $method) {
            // if(in_array($method['code'] , $data['checkout_pro_gateway'])) {
                $data['payment_methods'][$key] = $method;
                if(in_array($method['code'], $data['selected_payment_methods'])) {
                    $data['payment_methods'][$key]['selected'] = true;
                }
            
            // }
        }
   
        
        
		$this->load->model('localisation/tax_class');

		$data['tax_classes'] = $this->model_localisation_tax_class->getTaxClasses();



       
        $data['action'] = $this->url->link('extension/module/online_deposit', 'user_token=' . $this->session->data['user_token'], true);
    
    
    
        $data['form_display_name'] = false;
        $data['form_display_title'] = false;
        $data['form_display_class_suffix'] = false;
        $data['form_display_image'] = false;
        $data['form_display_module_image_width'] = false;
        $data['form_display_module_image_height'] = false;
    
  
		$data['main_sub_module_form'] = $this->load->controller('extension/component/main_sub_module_form', $data);

		$data['user_token'] =$this->session->data['user_token'];
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/online_deposit', $data));
	}


	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/online_deposit')) {
			$this->error['warning'] = $this->language->get('error_permission');
		} 
		
		  foreach($this->request->post['price_list'] as $key=> $price_list) {
		      if(!isset($price_list['price']) || empty($price_list['price']) ) {
		          $this->error['price_list'][$key] =  'قیمت نامعتبر است';
		      }
		  }
		
		return !$this->error;
	}
	
	public function generateLink() {
	    
	    if(isset($this->request->get['price'])) {
	        $price = (float)$this->request->get['price'];
	         
    	    $sig = hash_hmac('sha256', $price, "Test");
    	    
    	    
    	    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' 
             || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";

            $host = $_SERVER['HTTP_HOST'];
            
            $base_url = $protocol . $host;
            
            $json['sig'] = $base_url. '/account/online_deposit/?s=' . $sig . '&p=' .$price;
	    }
	   
    
	    $this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
}