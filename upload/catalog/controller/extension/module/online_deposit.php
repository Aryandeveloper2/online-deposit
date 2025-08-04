<?php
class ControllerExtensionModuleOnlineDeposit extends Controller {

	public function index() { 
        // loads
		$this->load->model('tool/image');
		$this->load->model('catalog/manufacturer');
		$this->load->model('catalog/category');
        
        $data = [];

       

    

        try {
            return $this->load->view('extension/module/online_deposit', $data);	
            // return $this->load->view('extension/module/mega_menu/'.$setting['template'], $data);
        } catch (\Throwable $th) {
            echo $th;
        } 
        
	}


    



}