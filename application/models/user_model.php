<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends CI_Model {

	var $table = 'user';
	public function getuserid($data = array())
	{
		$this->db->select('id');
		$this->db->where($data);
		if ($query = $this->db->get($this->table))
		{
			if ($query->result())
			{
				$result = $query->row();
				return $result->id;
			}
			else
			{
				return false;
			}
		}
		else
		{
			return false;
		}

	}
	public function getuserinfo($id)
	{
		$this->db->where('id', $id);
		if ($query = $this->db->get($this->table)) {
			return $query->row();
		}
		else
		{
			return false;
		}
	}
	public function checkusername($username)
	{
		$this->db->where('username', $username);
		$query = $this->db->get($this->table);
		if ($query->num_rows() > 0)
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	public function checkemail($email)
	{
		$this->db->where('email', $email);
		$query = $this->db->get($this->table);
		if ($query->num_rows() > 0)
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	public function adduser($data = array())
	{
		if ($this->db->insert($this->table, $data)) {
			return true;
		}
		else
		{
			return false;
		}
	}
	public function updateinfo($data = array(), $id)
	{
		if ($id) {
			$this->db->where('id', $id);
			if ($this->db->update($this->table, $data)) {
				return true;
			}
			else
			{
				return false;
			}
		}
		else
		{
			return false;
		}
	}


}

/* End of file user_model.php */
/* Location: ./application/models/user_model.php */