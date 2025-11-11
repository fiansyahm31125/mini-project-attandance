<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Tbemployee_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function get_by_appid($appid)
    {
        $this->db->select('employee_id, employee_full_name');
        $this->db->from('tbemployee');
        $this->db->where('appid', $appid);
        $query = $this->db->get();
        return $query->result_array();
    }
}
