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
        $this->db->select('employee_id, employee_full_name,departement_id');
        $this->db->from('tbemployee');
        $this->db->where('appid', $appid);
        $query = $this->db->get();
        return $query->result_array();
    }

    public function get_employee($appid, $employee_id)
    {
        if (empty($appid) || empty($employee_id)) {
            return false;
        }

        $this->db->select('d.name,e.departement_id, e.employee_full_name');
        $this->db->from('tbemployee e');
        $this->db->join('tbdepartements d', 'e.departement_id=d.id', 'left');
        $this->db->where('e.appid', $appid);
        $this->db->where('e.employee_id', $employee_id);
        $this->db->limit(1);

        $query = $this->db->get();
        return $query->num_rows() > 0 ? $query->row_array() : false;
    }

    public function get_by_department($appid, $department_id)
    {
        $this->db->select('employee_id, employee_full_name,departement_id');
        $this->db->from('tbemployee');
        $this->db->where('appid', $appid);
        $this->db->where('departement_id', $department_id);
        $this->db->order_by('employee_full_name', 'ASC');

        return $this->db->get()->result_array();
    }
}
