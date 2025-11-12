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
}
