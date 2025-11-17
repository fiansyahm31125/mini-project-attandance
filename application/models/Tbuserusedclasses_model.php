<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Tbuserusedclasses_model extends CI_Model
{
    protected $table = 'tbuserusedclasses';

    public function get_by_appid_and_userid($appid, $user_id)
    {
        $this->db->select('appid, user_id, schclass_id');
        $this->db->from($this->table);
        $this->db->where('appid', $appid);
        $this->db->where('user_id', $user_id);
        $query = $this->db->get();
        return $query->row_array(); // satu baris hasil
    }

    public function get_with_schclass($appid, $user_id)
    {
        $this->db->select('u.id as userused_id, u.appid, u.user_id, u.schclass_id, s.name, 
        s.start_time, s.end_time,
        s.start_checkin_time,s.end_checkin_time,s.start_checkout_time,s.end_checkout_time,
        s.late_minutes,s.early_minutes,
        s.overtime_start,s.overtime_end');
        $this->db->from('tbuserusedclasses u');
        $this->db->join('tbschclass s', 's.id = u.schclass_id', 'left');
        $this->db->where('u.appid', $appid);
        $this->db->where('u.user_id', $user_id);
        $this->db->order_by('s.start_time', 'ASC');
        $this->db->limit(1);

        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            return $query->row_array();
        }

        return false;
    }
}
