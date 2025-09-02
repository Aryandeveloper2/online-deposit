<?php
class ControllerAccountOnlineDeposit extends Controller {
	private $error = array();

	public function index() {
		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/edit', '', true);

			$this->response->redirect($this->url->link('account/login', '', true));
		}

		$this->load->language('extension/module/online_deposit');
		$this->document->setTitle($this->language->get('heading_title'));


		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $sd_title,
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_account'),
			'href' => $this->url->link('account/account', '', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('account/online_deposit', '', true)
		);
		
		$this->load->model('setting/extension');
        
        
        
                
        $online_deposit_setting = $this->config->get('online_deposit');  
        $data['tax_class_id'] = (int)$online_deposit_setting['tax_class_id'];
        
        if($online_deposit_setting['display_module_description'] == 1) {
            $language_id = (int)$this->config->get('config_language_id');
            $data['descirption'] = $online_deposit_setting['description'][$language_id];
        }
        
        $data['selected_payment_methods'] = $online_deposit_setting['selected_payment_methods'];
        
        
        $data['price_list'] = [];
        foreach($online_deposit_setting['price_list'] as $key=> $price) {
            
            $data['price_list'][$key] = $price;
            $data['price_list'][$key]['text_price'] = $this->currency->format(
    					$this->tax->calculate($price['price'],
    					(int)$online_deposit_setting['tax_class_id'],
    					$this->config->get('config_tax'))
    					 , $this->session->data['currency']);
        }
        
        $data['custom_price'] = $online_deposit_setting['custom_price'];

        
        $config_payments = $this->config->get("checkout_pro_gateway");
        $results = $this->model_setting_extension->getExtensions('payment');
        $data['payment_methods'] =[];
        foreach ($results as $key => $result) {
            // if ($this->config->get('payment_' . $result['code'] . '_status') && in_array($result['code'] , $config_payments )) {
            if ($this->config->get('payment_' . $result['code'] . '_status') && in_array($result['code'] , $data['selected_payment_methods'] )) {
                $this->load->language('extension/payment/' . $result['code']);
                $data['payment_methods'][$key] = $result;
                $data['payment_methods'][$key]['name'] = $this->language->get('text_title');
            }
        }
        
		$this->load->language('account/online_deposit');
        $this->load->model('account/online_deposit');

        $data['online_deposits'] = [];
        $online_deposits  = $this->model_account_online_deposit->getOnlineDeposits([
            'order' => "DESC"
            ]);
        
        foreach($online_deposits as $key=> $deposit) {
            $data['online_deposits'][$key] = $deposit;
            $timestamp = strtotime($deposit['date_added']);
            $data['online_deposits'][$key]['date_added'] = jdate('Y/m/d H:i:s', $timestamp);   
            $data['online_deposits'][$key]['price'] = $this->currency->format($deposit['price'], $this->session->data['currency']);
        }
        
        
        
		$data['back'] = $this->url->link('account/account', '', true);
		
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');


        /*--  End - SO Dashboard --*/
        $this->load->language('extension/module/so_dashboard');
        $this->load->model('account/customer');
        $data['customer_info'] = $this->model_account_customer->getCustomer($this->customer->getId());
        if ($data['customer_info']['custom_field'] && !empty($data['customer_info']) && !empty(json_decode($data['customer_info']['custom_field'], true))) {
        $data['field_addimage'] = json_decode($data['customer_info']['custom_field'], true);
        $data['thumbSrc'] = $data['field_addimage']['profileimage'];
        $data['thumbUrl'] = $this->model_tool_image->resize($data['field_addimage']['profileimage'], 200, 200);
        } else {
        $data['thumbUrl'] = 'image/placeholder.png';
        }
        /*--  End - SO Dashboard --*/

        $data['logout'] = $this->url->link('account/logout', '', true);

		$this->response->setOutput($this->load->view('extension/module/online_deposit', $data));
	}

    public function filter() {
        $this->load->model('setting/module');
        $this->load->model('setting/setting');
        $this->load->model('setting/extension');
        $this->load->model('tool/image');

        $this->load->model('account/online_deposit');

        $data = [];

        if(isset($this->request->get['status'])) {
           
               $this->load->model('account/online_deposit');

                $data['online_deposits'] = [];
                $online_deposits  = $this->model_account_online_deposit->getOnlineDeposits([
                    'order' => "DESC",
                    'filter_status' => $this->request->get['status'],
                    ]);
                
                foreach($online_deposits as $key=> $deposit) {
                   $data['online_deposits'][$key] = $deposit;
                   
                   $timestamp = strtotime($deposit['date_added']);
                   $data['online_deposits'][$key]['date_added'] = jdate('Y/m/d H:i:s', $timestamp);   
                   
                   
                   $data['online_deposits'][$key]['price'] = $this->currency->format($deposit['price'], $this->session->data['currency']);
                   
                   
        
                }

            
        }
          
        $this->response->addHeader('Content-Type: application/json');

    

        $this->response->setOutput(json_encode($data));

    }
    


    public function update_tax() {
        

        $this->load->model('setting/module');
        $this->load->model('setting/setting');
        $this->load->model('setting/extension');
        $this->load->model('tool/image');

        $this->load->model('account/online_deposit');

        $data = [];

        if(isset($this->request->post['deposit_price'])) {
            
            
            
            
            $online_deposit_setting = $this->config->get('online_deposit');  


	
	
            
            $total = $this->tax->calculate(
                $this->request->post['deposit_price']
            , 	(int)$online_deposit_setting['tax_class_id'], $this->config->get('config_tax'));
            
            
            
            $text_tax  =  $this->currency->format(
               ( $total-$this->request->post['deposit_price'])
                ,$this->session->data['currency']);
                
            $text_total =  $this->currency->format(
               ( $total)
                ,$this->session->data['currency']);
                
            $text_price =  $this->currency->format(
               ( $this->request->post['deposit_price'])
                ,$this->session->data['currency']);
            
           $data = [
               
             'price'   => $this->request->post['deposit_price'],
             'tax_class_id'   => $online_deposit_setting['tax_class_id'],
             'total'   => $total,
             'text_price'   => $text_price,
             'text_total'   => $text_total,
             'text_tax'   => $text_tax,
             'tax'   => $total-$this->request->post['deposit_price'],
           ];
        }
          
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($data));

    }
    
    
      public function payment() {
        

        $this->load->model('setting/module');
        $this->load->model('setting/setting');
        $this->load->model('setting/extension');
        $this->load->model('tool/image');

        $this->load->model('account/online_deposit');

        $data = [];

        if(isset($this->request->post['deposit_price'])) {
            
            
            
            
            $online_deposit_setting = $this->config->get('online_deposit');  


	
	
            
            $this->session->data['deposit_price'] = $this->tax->calculate($this->request->post['deposit_price']
            , 	(int)$online_deposit_setting['tax_class_id'], $this->config->get('config_tax'));
            
            
            // add to database
                  $data['test'] = $this->request->post;
            
            switch($this->request->post['payment_method']) {
                case 'saman': 
                      $data = $this->sendSamanRequest();
                
                    
                    break;
            }
        }
          
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($data));

    }


    public function sendSamanRequest() {
            $this->load->model('account/online_deposit');
            
            $this->session->data['customer_online_deposit_id']  = $this->model_account_online_deposit->addOnlineDeposit([
                    
                'customer_id' =>$this->customer->getId(),
                'status' =>0,
                'payment_method' => 'saman',
                'price' => $this->session->data['deposit_price'],
            ]);
                





            $total = $this->session->data['deposit_price'];
            $total = $total * 10;

			$red = $this->url->link('account/online_deposit/callback_online_deposit', 'shf_key='.$this->session->getId(), true);
			$SegAmount1 = $SegAmount2 = $SegAmount3 = $SegAmount4 = $SegAmount5 = $SegAmount6 = $AdditionalData1 = $AdditionalData2 = $Wage = '';
			ini_set("soap.wsdl_cache_enabled", "0");
			$client = new SoapClient('https://sep.shaparak.ir/payments/referencepayment.asmx?WSDL');
			$result = $client->RequestToken($this->config->get('payment_saman_mid'), time(), $total, $SegAmount1, $SegAmount2, $SegAmount3, $SegAmount4, $SegAmount5, $SegAmount6, $AdditionalData1, $AdditionalData2, $Wage, $red);
			if (is_numeric($result) && 0 > $result) {
				return 'SEP-Error! (cdoe : '.$result.')';
			}
			
			
		
			return [
                'token' => $result,
                'redirect_url' => $red
            ];
        
		
    }
    
	public function callback_online_deposit()
	{
	
	    $this->load->model('account/online_deposit');
		if ($this->request->get['shf_key']) {
			$this->session->start($this->request->get['shf_key']);
			setcookie($this->config->get('session_name'), $this->session->getId(),
			ini_get('session.cookie_lifetime'), ini_get('session.cookie_path'), ini_get('session.cookie_domain'));
		}
	
	     $customer_online_deposit_id = $this->session->data['customer_online_deposit_id'];

		$online_deposit_info = $this->model_account_online_deposit->getOnlineDeposit($customer_online_deposit_id);
		if ($online_deposit_info)
		{
			$ok      = false;
			$MID     = isset($_POST['MID'])     ? $_POST['MID']     : 0;
			$CID     = isset($_POST['CID'])     ? $_POST['CID']     : 0;
			$RRN     = isset($_POST['RRN'])     ? $_POST['RRN']     : 0;
			$State   = isset($_POST['State'])   ? $_POST['State']   : '';
			$RefNum  = isset($_POST['RefNum'])  ? $_POST['RefNum']  : 0;
			$ResNum  = isset($_POST['ResNum'])  ? $_POST['ResNum']  : 0;
			$TraceNo = isset($_POST['TRACENO']) ? $_POST['TRACENO'] : 0;
			$ScrPan  = isset($_POST['SecurePan']) ? $_POST['SecurePan'] : 0;
			$msg = "State : $State , ResNum : $ResNum";
			if ($RefNum)  $msg .= " , RefNum : $RefNum";
			if ($TraceNo) $msg .= " , TraceNo : $TraceNo";
			if ($MID)     $msg .= " , MID : $MID";
			if ($CID)     $msg .= " , CID : $CID";
			if ($RRN)     $msg .= " , RRN : $RRN";
			if ($ScrPan)  $msg .= " , ScrPan : $ScrPan";
			if ($this->config->get('payment_saman_debug'))
			{
				$this->log->write('Saman :: customer_online_deposit_id='.$customer_online_deposit_id.' :: POST='.implode($this->request->post).' :: GET='.implode($this->request->get));
			}
			if (strtolower($State) != 'ok')
			{
				if ($this->config->get('payment_saman_debug'))
				{
					$this->log->write(' Saman :: customer_online_deposit_id= '.$customer_online_deposit_id.' :: pay cancelled :: State='.$State);
				}
			}
			else
			{
				try
				{
					$total = $online_deposit_info['price'];
					ini_set("soap.wsdl_cache_enabled", "0");
					$client = new SoapClient('https://sep.shaparak.ir/payments/referencepayment.asmx?WSDL');
					$result = $client->VerifyTransaction($RefNum, $this->config->get('payment_saman_mid'));
					if ($result > 0 && intval($result) == intval($total))
					{
						$ok = true;
					}
					else
					{
						if ($this->config->get('payment_saman_debug'))
						{
							$this->log->write('Saman :: customer_online_deposit_id='.$customer_online_deposit_id.' :: error in verify='.$result);
						}
					}
				}
				catch (SoapFault $ex)
				{
				    $this->model_account_online_deposit->editStatusOnlineDeposit($customer_online_deposit_id , 1 );
					die('Error in get data from bank. ' . $ex->getMessage());
				}
			}


			if ($ok == true) {
			    $this->model_account_online_deposit->editStatusOnlineDeposit($customer_online_deposit_id , 2 );
				header('location: '.$this->url->link('account/online_deposit'));
			} else {
			    $this->model_account_online_deposit->editStatusOnlineDeposit($customer_online_deposit_id , 1 );
				header('location: '.$this->url->link('account/online_deposit', '', true));
			}
			exit;
		}
	}

}