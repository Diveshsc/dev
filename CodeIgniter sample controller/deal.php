<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Deal Method 
 * Methods for add, delete & manage deals
 * 
 * */
class deal extends MX_Controller {

	function __construct() {
		parent::__construct();
		$this->load->library(array('admin_template','head'));
		$this->load->library(array('login_template','template','head'));
		$this->load->library('form_validation');
		$this->lang->load(array('admin','common','resources','deal_activities'));
		$this->load->model(array('deals_model'));
		$this->session_check->checkSession();
		if(!is_admin_login()) {	// check user is already login or not
			redirect('users');
		}
	}
	

/*****************************Deal Section Start*****************************/
	
	/*************
	 * Method :index
	 * Task : Show deal listing for getting all active users
	 *****************/
	public function index(){
		set_page_title('Deals');
		$data['getDeals'] = $this->deals_model->getDataById(TBL_DEALS,array('is_deleted'=>0),'id');
		$this->admin_template->load('admin_template','deals',$data);
	}
	/*************
	 * Method :add deal
	 * Task : Show deal form
	 *****************/
	public function add_deal() {
		set_page_title('Add Deal');
		$role_id=LoginUserDetails('roleId');
		$accessPermissionId=LoginUserDetails('accessPermissionId');
		if($accessPermissionId==1) { //For Super Admin Dashboard
			$this->admin_template->load('admin_template','add_deal');
		}else{
			redirect('login/logout');
		}
	}
	/*************
	 * Method :Save Deal
	 * Task : Save deal to database
	 *****************/
	public function saveDeal(){
		$user_id=$this->session->userdata('userId');
	    $contact_person_name=$this->input->post('contact_person_name');
		$organization_name=$this->input->post('organization_name');
		$deal_title=$this->input->post('deal_title');
		$deal_value=$this->input->post('deal_value');
		$currency_type=$this->input->post('currency_type');
		$pipeline_stage=$this->input->post('pipeline_stage');
		$expected_close_date=$this->input->post('expected_close_date');
		
		//form validation
		$this->form_validation->set_rules('contact_person_name','Contact Person Name','xss_clean|required');
		$this->form_validation->set_rules('organization_name','Organization Name','xss_clean|required');
		$this->form_validation->set_rules('deal_title','Deal Title','xss_clean|required');
		if($this->form_validation->run()==FALSE){
			redirect('deal');
		}else{

			//update setting table
			$data=array('user_id'=>$user_id,
						'contact_person_name'=>$contact_person_name,
						'organization_name'=>$organization_name,
						'deal_title'=>$deal_title,
						'deal_value'=>$deal_value,
						'currency_type'=>$currency_type,
						'pipeline_stage'=>$pipeline_stage,
						'expected_close_date'=>$expected_close_date,
						'status'=>1,
				        );
			$this->deals_model->add_deal($data);
			set_global_messages(lang('setting_update_msg'),'success');
			redirect('deal'); 

		}
	}
	
	/*************
	 * Method :edit deal
	 * Task : Show edit deal form
	 *****************/
	public function edit_deal($deal_id=0) {
		if($deal_id==0){
			set_global_messages(lang('something_wrong'),'error');
			redirect('deal');
		}
		set_page_title('Edit Deal');
		$role_id=LoginUserDetails('roleId');
		$accessPermissionId=LoginUserDetails('accessPermissionId');
		$deal = $this->deals_model->getDataById(TBL_DEALS,array('is_deleted'=>0,'id'=>$deal_id));
		
		if(!$deal){
			set_global_messages(lang('something_wrong'),'error');
			redirect('deal');
		}
		
		$data['deal'] = $deal[0];		
		if($accessPermissionId==1) { //For Super Admin Dashboard
			$this->admin_template->load('admin_template','edit_deal',$data);
		}else{
			redirect('login/logout');
		}
	}
	/*************
	 * Method :Update Deal
	 * Task : Save deal to database
	 *****************/
	public function updateDeal(){
		$deal_id=$this->input->post('deal_edit_id');
		
		if(!$deal_id){
			set_global_messages(lang('something_wrong'),'error');
			redirect('deal');
		}
		
		$user_id=$this->session->userdata('userId');
	    $contact_person_name=$this->input->post('contact_person_name');
		$organization_name=$this->input->post('organization_name');
		$deal_title=$this->input->post('deal_title');
		$deal_value=$this->input->post('deal_value');
		$currency_type=$this->input->post('currency_type');
		$pipeline_stage=$this->input->post('pipeline_stage');
		$expected_close_date=$this->input->post('expected_close_date');
		
		//form validation
		$this->form_validation->set_rules('contact_person_name','Contact Person Name','xss_clean|required');
		$this->form_validation->set_rules('organization_name','Organization Name','xss_clean|required');
		$this->form_validation->set_rules('deal_title','Deal Title','xss_clean|required');
		if($this->form_validation->run()==FALSE){
			redirect('deal');
		}else{

			//update setting table
			$data=array('user_id'=>$user_id,
						'contact_person_name'=>$contact_person_name,
						'organization_name'=>$organization_name,
						'deal_title'=>$deal_title,
						'deal_value'=>$deal_value,
						'currency_type'=>$currency_type,
						'pipeline_stage'=>$pipeline_stage,
						'expected_close_date'=>$expected_close_date,
						'status'=>1,
				        );
			$this->deals_model->update_deal($deal_id,$data);
			set_global_messages(lang('deal_updated'),'success');
			redirect('deal'); 

		}
	}
	
	/*
	* Name : change_status()
	* Request Parameter :  id, status
	* Use : Change deal status
	* */
	public function change_status($id='',$status='') {
		$data['status'] = $status;
		$this->deals_model->update_deal($id, $data);
		set_global_messages(lang('updated_successfully'),'success');
		redirect('deal');
	}
	/*
	* Name : users_delete()
	* Request Parameter :  id, status,accessPermissionId
	* Use : for deleting users
	* */
	public function delete_item($id='',$status='') {
		$data['is_deleted'] = '1';
		$this->deals_model->update_deal($id, $data);
		set_global_messages(lang('admin_msg_delete'),'success');
		redirect('deal');
	}

	/**
	 * Method :activate_deactivate_user
	 * Task : Loading for activating and deactivating multiple users
	 */
	public function activate_deactivate_deal(){

		if(!empty($_POST)){

		 	if($_POST['status']==1){
		 		$user_id=$_POST['id_array'];
				if(count($user_id)>0){
					for($i=0;$i<count($user_id);$i++){
						$update_data=$this->deals_model->update_data(TBL_DEALS,array('status'=>1),array('id'=>$user_id[$i]));
					} 
					 set_global_messages(lang('item_activate_success'),'success');
				}else{
					return FALSE;
				}
		 	}else{
		 		   $user_id=$_POST['id_array'];
				   if(count($user_id)>0){
					  for($i=0;$i<count($user_id);$i++){
						$update_data=$this->deals_model->update_data(TBL_DEALS,array('status'=>0),array('id'=>$user_id[$i]));
						}
					 set_global_messages(lang('item_deactivate_success'),'success');
					}else{
					  return FALSE;
				    }
		 	    }
		}else{
			return FALSE;
		}
	}
	
	/**
	* Method :Delete All
	* Task : Delete add items
	*/
	public function deleteAll(){
		 if(!empty($_POST)){
			$user_id=$_POST['id_array'];
			if(count($user_id)>0){
				for($i=0;$i<count($user_id);$i++){
					$update_data=$this->deals_model->update_data(TBL_DEALS,array('is_deleted'=>1),array('id'=>$user_id[$i]));
				}
				set_global_messages(lang('item_delete_success'),'success');
			}else{
				return FALSE;
			}
		}else{
			return FALSE;
		}
	}
	
}//main class end

/* End of file login.php */
/* Location: ./application/modules/deal/controllers/deal.php */
