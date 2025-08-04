<?php
class ControllerExtensionModuleOnlineDeposit extends Controller {
	private $error = array();

	public function index() {
      
		$this->load->language('extension/module/online_deposit');

		$this->load->model('catalog/manufacturer');

		$this->load->model('catalog/category');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/module');

		$this->load->model('tool/image');
		



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
	

		$data['action'] = $this->url->link('extension/module/online_deposit', 'user_token=' . $this->session->data['user_token'], true);
	

		$data['user_token']  = $this->session->data['user_token'];

		$data['save_section'] = $this->load->controller('extension/component/save_section', [
			'form_id' 		=> "form-module",
			'save_new' 		=> false,
			'save_edit' 	=> false,
			'save' 			=> false,
			'cancel' 		=> $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true),
		]);

		$this->load->model('localisation/language');

		$data['languages'] = $this->model_localisation_language->getLanguages();
		
		$data['default_language_id'] = $this->config->get("config_language_id");

		$data['user_token'] =$this->session->data['user_token'];
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/online_deposit', $data));
	}

	public function getExtensions() {
	    $this->load->model('setting/extension');
	    $this->load->model('setting/module');
	    $extension = 'module';
	    $extensions =  $this->categoryExtensions();
        // $extensions = ['extra_slider', 'display_brand'];
        $module_list = [];
        
        foreach ($extensions as $code => $data) {
            $module_list[$code] = $data;
            $this->load->language('extension/'. $extension .'/' . $code, 'extension');

            $modules = $this->model_setting_module->getModulesByCode($code);

            foreach ($modules as $module) {
                $module_list[$code]['modules'][] = array(
                    'name'   => strip_tags($module['name']),
                    'code'   => $code . '.' .  $module['module_id'],
                );
            }
        }
        
        
        return $module_list;
	}
	
	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/online_deposit')) {
			$this->error['warning'] = $this->language->get('error_permission');
		} 
		return !$this->error;
	}
	
}