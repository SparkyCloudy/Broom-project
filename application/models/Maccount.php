<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @property BRoom_Verify $account_verify
 * @property CI_Loader $load
 * @property CI_Input $input
 * @property CI_Session $session
 * @property CI_DB $db
 * @property CI_Lang $lang
 */
class Maccount extends CI_Model
{
	public function __construct()
	{
		parent::__construct();
		$this->load->library('accounts/BRoom_Verify', null, 'account_verify');
		// TODO must add language mechanism
		$this->load->language('BRoomAuth', 'indonesia');
	}
	
	/**
	 * For authenticating login sent by user.
	 *
	 * @return void
	 */
	public function login(): void
	{
		// Retrieving data from user input post
		$email = $this->input->post('email');
		$password = $this->input->post('password');
		
		// Fetching from database
		$data = $this->db->select()->from('Account')
				->where('email', $email)
				->where('password', $password)->get();
		
		/*
		 * Checking whether the data exists or not.
		 * If exist     = identifies role -> join table account with related
		 *                role -> set session
		 * If not       = login authentication failed
		 */
		if ($data->num_rows() > 0) {
			
			$accountData = $data->first_row();
			
			if (!$accountData->is_verif) {
				$this->session->set_flashdata(
						'loginerror',
						$this->lang->line('email_not_verified')
				);
				redirect(site_url());
			}
			
			// Join table account with related role
			$roleData = $this->db->select()->from($accountData->role)->join(
					'Account',
					"Account.account_id = " . $accountData->role .
					".account_id",
					'inner'
			)->where('Account.account_id', $accountData->account_id)->get()
					->first_row();
			
			// Setting session with role & id
			$sessionData = array(
					'id' => $roleData->id,
					'role' => $roleData->role
			);
			
			$this->session->set_userdata($sessionData);
		} else {
			$this->session->set_flashdata(
					'loginerror',
					$this->lang->line('login_failed')
			);
			redirect(site_url());
		}
	}
	
	/**
	 * For registration user with input data.
	 *
	 * @return void
	 */
	public function register(): void
	{
		// Retrieving data from user input post
		$email = $this->input->post("email");
		$password = $this->input->post("password");
		$id = $this->input->post('id');
		$name = $this->input->post("name");
		$phone = $this->input->post("phone");
		$token = $this->account_verify->create_random(Verification::REGISTER);
		
		// Check duplicate id
		$duplicate_entry = $this->db->select()->from('Peminjam')
				->where('id', $id)->get()->num_rows();
		if (!empty($duplicate_entry)) {
			$this->session
					->set_flashdata('register_error',
							$this->lang->line('register_duplicate_id'));
			redirect('register');
		}
		
		// Insert data email, password, and generated token to table account
		$data = array(
				"email" => $email,
				"password" => $password,
				"token" => $token,
				"role" => AccountRole::PEMINJAM
		);
		$this->db->insert('Account', $data);
		
		// Get account_id FROM table account
		$fkdata = $this->db->select()->from('Account')
				->where('email', $email)->where('password', $password)
				->get()->first_row();
		$fkid = $fkdata->account_id;
		
		// Insert data id, name, phone, & (id FROM Account) to table peminjam
		$data = array(
				"id" => $id,
				"name" => $name,
				"phone" => $phone,
				"role" => PeminjamRole::MAHASISWA,
				"account_id" => $fkid
		);
		$this->db->insert('Peminjam', $data);
		
		$this->account_verify->send_email($email, $token);
		$this->session->set_flashdata('email_verify',
				$this->lang->line('register_success'));
		
		redirect(site_url());
	}
	
	/**
	 * Determines email to use for the OTP code
	 * Creates session based on the email and the otp code
	 *
	 * @return void
	 */
	public function create_verification(): void
	{
		// Retrieving data from user input post
		$email = $this->input->post('email');
		$otp = '';
		
		// Make sure the email has been verified before perform change password
		$query = $this->db->select()->from('Account')
				->where('email', $email)->where('is_verif', 1)
				->get();
		
		if ($query->num_rows() > 0) {
			$this->account_verify->send_email($email, $otp, true);
			$array = array(
					'email' => $email,
					'token' => $otp,
					'has_verification' => true
			);
			
			$this->session->set_tempdata($array, null, 3600);
		} else {
			$this->session->set_flashdata(
					'loginerror',
					$this->lang->line('forgot_pass_failed')
			);
			redirect('login/forgot');
		}
	}
	
	/**
	 * For resetting password
	 */
	public function newpass($password): void
	{
		// gets the session based on the email input
		$email = $this->session->userdata('email');
		
		// Fetching from database
		$query = $this->db->select()->from('Account')
				->where('email', $email)->get();
		
		/**
		 * Checking whether the data exist  or not.
		 * If exist        = Updates the password field -> redirect to first view -> destroy session
		 * If not        = failed -> go back to reset password page
		 */
		if ($query->num_rows() > 0) {
			$this->db->set('password', $password)->where('email', $email)
					->update('Account');
			
			$this->session->sess_destroy();
			
			redirect('login');
		} else {
			echo "failed";
			redirect('login/forgot');
		}
		
	}
	
	/**
	 * Get current user data's of session
	 *
	 * @return object
	 */
	public function get_current_account_data(): object
	{
		$currentSession = $this->session->get_userdata();
		
		return $this->db->select()->from($currentSession['role'])->join(
				'Account',
				"Account.account_id = " . $currentSession['role'] . ".account_id",
				'inner'
		)->where('id', $currentSession['id'])->get()->first_row();
	}
	
	/**
	 * Update current user data's of session
	 * - name
	 * - phone
	 *
	 * @param array $data
	 * @return void
	 */
	public function edit(array $data): void
	{
		// TODO need refactor this logic
		$currentSessionData = $this->session->userdata;
		
		$this->db->set('name', $data['account_name'])
				->set('phone', $data['account_phone'])
				->where('id', $currentSessionData['id'])
				->update($currentSessionData['role']);
	}
	
	function verify_email(string $token): void
	{
		$this->db->set('is_verif', 1)->where('token', $token)
				->update('Account');
	}
	
	public function logout(): void
	{
		$this->session->sess_destroy();
	}
}

