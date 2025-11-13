<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Tbusertempsch_model extends CI_Model
{
    protected $table = 'tbusertempsch';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Ambil data tbusertempsch berdasarkan appid dan empid (user_id)
     */
    public function get_by_appid_and_empid($appid, $empid, $date = null)
    {
        $this->db->select('id, appid, user_id, schclass_id');
        $this->db->from($this->table);
        $this->db->where('appid', $appid);
        $this->db->where('user_id', $empid);

        if (!empty($date)) {
            // Pastikan hanya data dengan periode aktif di tanggal tersebut yang diambil
            $this->db->where('DATE(start_date) <=', $date);
            $this->db->where('DATE(end_date) >=', $date);
        }


        $query = $this->db->get();
        return $query->row(); // ambil satu baris data
    }

    public function get_with_schclass($appid, $user_id, $date)
    {
        if (empty($appid) || empty($user_id) || empty($date)) {
            return false;
        }

        $this->db->select('t.id as tempsch_id, t.appid, t.user_id, t.start_date, t.end_date,
                           t.schclass_id, s.name, s.start_time, s.end_time,s.start_checkin_time,s.end_checkin_time,s.start_checkout_time,s.end_checkout_time,s.late_minutes,s.early_minutes');
        $this->db->from('tbusertempsch t');
        $this->db->join('tbschclass s', 's.id = t.schclass_id', 'left');
        $this->db->where('t.appid', $appid);
        $this->db->where('t.user_id', $user_id);
        $this->db->where('t.start_date <=', $date);
        $this->db->where('t.end_date >=', $date);
        $this->db->order_by('t.start_date', 'DESC');
        $this->db->limit(1); // ambil 1 record aktif

        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            return $query->row_array();
        }

        return false;
    }
}
