<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_cont extends CI_Controller
{

	public function index()
	{
		$this->load->view('user/index');
	}

	public function user($username = '', $image = '')
	{
		// load thư viện và model CI
		$this->load->library('form_validation');
		$this->load->helper('form');
		$this->load->model('user_model');
		//kiểm tra username
		if ($username != '')
		{
			//đăng nhập basic Auth
			//header('HTTP/1.0 401 Unauthorized');
			if (!isset($_SERVER['PHP_AUTH_USER']))
			{
			   header('WWW-Authenticate: Basic realm="Vui lòng nhập thông tin username và password hệ thống"');
			   header('HTTP/1.1 401 Unauthorized');
			}
			else
			{
			   $username_sys = $_SERVER['PHP_AUTH_USER'];
			   $password_sys = $_SERVER['PHP_AUTH_PW'];
			   if($username_sys != 'wsgroup' || $password_sys != 'proudtobehere')
			   {
			      	header("HTTP/1.1 401 Unauthorized");
			   }
			}
			//kiểm tra username nếu chưa đăng nhập trả về trang user chính
			if ($username != $this->session->userdata('user_s'))
			{
				header('Location: '.base_url().'user_cont/');
			}
			// kiểm tra nếu có yêu cầu đổi hình ảnh
			if ($image)
			{
				if ($image == 'image')
				{
					$user = $username;
					$where['username'] = $username;
					$id = $this->user_model->getuserid($where);
					$info = $this->user_model->getuserinfo($id);
					$data['info'] = $info;
					if ($this->input->post('submit'))
					{
						$filename = $_FILES['avata']['name'];
						$filedata = $_FILES['avata']['tmp_name'];
						$filetype = $_FILES['avata']['type'];
						$filesize = $_FILES['avata']['size'];
						if ($filedata)
						{
							$headers = array('Content-Type:multipart/form-data');
				        	$avata = array("filedata" => $filedata, "filename" => $filename, 'id' => $id);
				        	$url = base_url().'user_cont/upload';
				        	$ch = curl_init($url);
				        	//curl_setopt($ch, CURLOPT_HEADER, true);
					        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
					        curl_setopt($ch, CURLOPT_POST, true);
					        curl_setopt($ch, CURLOPT_POSTFIELDS, $avata);
					        curl_setopt($ch, CURLOPT_INFILESIZE, $filesize);
					        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					        if ($result = curl_exec($ch))
					        {
					        	$result_decode = json_decode($result);
					        	if ($result_decode->status == 'success') {
					        		header('Location: '.base_url().'user_cont/user/'.$username);
					        	}
					        }
					        curl_close($ch);
				    	}
					}
					$this->load->view('user/uploadavata', $data);
				}
				else
				{
					header('Location: '.base_url().'user_cont/user/'.$username);
				}

			}
			// có username nhưng không yêu cầu đổi hình ảnh
			else
			{
				if ($_SERVER['REQUEST_METHOD'] == 'GET') {
					$user = $username;
					$url = base_url().'user_cont/getinfo/'.$username;
					$ch = curl_init($url);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
					if ($result = curl_exec($ch)) {
						$result_decode = json_decode($result);
						$data['info'] = $result_decode;
					}
					$this->load->view('user/info.php', $data);
				}
				if ($_SERVER['REQUEST_METHOD'] == 'POST')
				{
					$user = $username;
					$where['username'] = $user;
					$id = $this->user_model->getuserid($where);
					$info = $this->user_model->getuserinfo($id);
					$data['info'] = $info;
					$this->form_validation->set_rules('name', 'Name', 'required');
					if ($this->input->post('email') != $info->email)
					{
						$this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email|callback_check_email');
					}
					if ($this->form_validation->run())
					{
						$update['id'] = $id;
						$update['email'] = $this->input->post('email');
						$update['name'] = $this->input->post('name');
						$update = (is_array($update)) ? http_build_query($update) : $update;
						$url = base_url().'user_cont/user/'.$username;
						$ch = curl_init($url);
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
						curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
						curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Length: ' . strlen($update)));
						curl_setopt($ch, CURLOPT_POSTFIELDS, $update);
						if($result = curl_exec($ch))
						{
							$result_decode = json_decode($result);
							if ($result_decode->status == 'success')
							{
								header('Location: '.base_url().'user_cont/user/'.$user);
							}
						}
						else
						{
							echo 'fail';
						}
					}
					$this->load->view('user/info.php', $data);
				}
				if ($_SERVER['REQUEST_METHOD'] == 'PUT')
				{
					parse_str(file_get_contents('php://input'), $requestData);
					foreach ($requestData as $key => $value) {
						$data[$key] = $value;
					}
					if ($this->user_model->updateinfo($data, $data['id'])) {
						$result['status'] = 'success';
						$result['mess'] = 'Update success';
						echo json_encode($result);
					}
					else
					{
						$result['status'] = 'fail';
						$result['mess'] = 'Update fail';
						echo json_encode($result);
					}
				}
			}
		}
		//không có username. đến trang đăng ký
		else
		{
			$this->form_validation->set_rules('user', 'Username', 'trim|required|callback_check_username');
			$this->form_validation->set_rules('pass', 'Password', 'trim|required');
			$this->form_validation->set_rules('pass_r', 'Password', 'trim|required|matches[pass]');
			$this->form_validation->set_rules('name', 'Name', 'required');
			$this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email|callback_check_email');
			if ($this->form_validation->run())
			{
				if ($this->input->post('submit'))
				{
					$regist = array(
					'user' => $this->input->post('user'),
					'pass' => $this->input->post('pass'),
					'pass_r' => $this->input->post('pass_r'),
					'name' => $this->input->post('name'),
					'email' => $this->input->post('email')
					);
					$url = base_url().'user_cont/regist';
					$ch = curl_init($url);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
					curl_setopt($ch, CURLOPT_POST, TRUE);
					curl_setopt($ch, CURLOPT_POSTFIELDS, $regist);
					if ($result = curl_exec($ch)) {
						$result_decode = json_decode($result);
						if ($result_decode->status == 'success')
						{
							$this->session->set_userdata('user_s', $result_decode->user);
							header('Location: '.base_url().'user_cont/user/'.$result_decode->user);
						}
					}
				}
			}
			$this->load->view('user/regist');
		}
	}
	//lấy thông tin user
	public function getinfo($user)
	{
		$this->load->model('user_model');
		$where['username'] = $user;
		$id = $this->user_model->getuserid($where);
		$info = $this->user_model->getuserinfo($id);
		echo json_encode($info);
	}
	//xử đăng ký
	public function regist()
	{
		if ($_SERVER['REQUEST_METHOD'] != 'POST') {
			die();
		}
		else
		{
			$this->load->model('user_model');
			$result = array();
			$user = $this->input->post('user');
			$pass = $this->input->post('pass');
			$email = $this->input->post('email');
			$name = $this->input->post('name');
			if ($user && $pass && $email && $name) {
				$value = array('username' => $user, 'pass' => md5($pass), 'name' => $name, 'email' => $email, 'avata' => 'avata.png');
				if ($this->user_model->adduser($value))
				{
					$result['status'] = 'success';
					$result['mess'] = 'Regist success';
					$result['user'] = $user;
					echo json_encode($result);
				}
				else
				{
					$result['status'] = 'fail';
					$result['mess'] = 'Regist fail';
					echo json_encode($result);
				}
			}
			else
			{
				$result['status'] = 'fail';
				$result['mess'] = 'Value missing';
				echo json_encode($result);
			}
		}
	}
	//xử lý upload
	public function upload()
	{
		$this->load->model('user_model');
		if ($_SERVER['REQUEST_METHOD'] != 'POST') {
			die();
		}
		else
		{
			$uploadpath = "upload/";
			$filedata = $this->input->post('filedata');
			$filename = $this->input->post('filename');
			$id = $this->input->post('id');
			$des =  md5(rand()).$filename;
			if ($filedata && $filename) {
				if (copy($filedata, $uploadpath.$des))
				{
			    	$data['avata'] = $des;
			    	if ($this->user_model->updateinfo($data, $id))
			    	{
			    		$result['status'] = 'success';
			    		$result['mess'] = 'Insert success';
			    		echo json_encode($result);
			    	}
			    	else
			    	{
			    		$result['status'] = 'fail';
			    		$result['mess'] = 'Insert fail';
			    		echo json_encode($result);
			    	}
			    }
			    else
			    {
			    	$result['status'] = 'fail';
			    	$result['mess'] = 'Can not upload';
			    	echo json_encode($result);
			    }
			}
		}
	}
	//kiểm tra tồn tại username
	public function check_username($user = '')
	{
		$this->load->library('form_validation');
		$this->load->model('user_model');
		if (!$this->user_model->checkusername($user)) {
			return true;
		}
		else
		{
			$this->form_validation->set_message('check_username', 'Username đã tồn tại');
			return false;
		}
	}
	//kiểm tra tồn tại email
	public function check_email($email = '')
	{
		$this->load->library('form_validation');
		$this->load->model('user_model');
		if (!$this->user_model->checkemail($email)) {
			return true;
		}
		else
		{
			$this->form_validation->set_message('check_email', 'Email đã tồn tại');
			return false;
		}
	}
	//tạo lệnh login giả phục vụ việc kiểm tra
	public function fakelogin($username = '')
	{
		$this->session->set_userdata('user_s', $username);
		header('Location: '.base_url().'user_cont/');
	}
	//logout
	public function logout()
	{
		$this->session->unset_userdata('user_s');
		header('Location: '.base_url().'user_cont/');
	}
}

/* End of file user.php */
/* Location: ./application/controllers/user.php */