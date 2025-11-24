<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Tbuserofrun_model extends CI_Model
{
    protected $table = 'tbuserofrun';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 🔹 Ambil semua data
     */
    public function get_all()
    {
        return $this->db->get($this->table)->result();
    }

    /**
     * 🔹 Ambil data berdasarkan AppID saja
     */
    public function get_by_appid($appid)
    {
        return $this->db
            ->where('appid', $appid)
            ->get($this->table)
            ->result();
    }

    /**
     * 🔹 Ambil data berdasarkan AppID dan EmpID (kolom user_id di tabel)
     */
    public function get_by_appid_and_empid($appid, $empid)
    {
        return $this->db
            ->where('appid', $appid)
            ->where('user_id', $empid)
            ->get($this->table)
            ->row(); // 1 baris hasil (jika kombinasi unik)
    }

    public function get_with_numrun($appid, $user_id, $date)
    {
        if (empty($appid) || empty($user_id) || empty($date)) {
            return false;
        }

        // ============================================================
        // Hitung sdays_week dan sdays_month
        // ============================================================
        $timestamp = strtotime($date);
        $sdays_week  = (int) date('N', $timestamp);   // Senin = 1 ... Minggu = 7
        $sdays_month = (int) date('j', $timestamp);   // 1 - 31

        $this->db->select('
        u.appid,
        u.user_id,
        u.num_of_run_id,
        r.name AS run_name,
        r.start_date,
        r.end_date,
        r.unit,
        d.start_time,
        d.end_time,
        d.sdays,
        s.start_checkin_time,
        s.end_checkin_time,
        s.start_checkout_time,
        s.end_checkout_time,
        s.late_minutes,
        s.early_minutes,
        s.overtime_start,
        s.overtime_end,
        s.break_type,s.break_in,s.break_out,s.break_duration
    ');
        $this->db->from('tbuserofrun u');
        $this->db->join('tbnumrun r', 'r.id = u.num_of_run_id', 'left');
        $this->db->join('tbnumrundeil d', 'd.num_run_id = u.num_of_run_id', 'left');
        $this->db->join('tbschclass s', 's.id = d.schclass_id', 'left');
        $this->db->where('u.appid', $appid);
        $this->db->where('u.user_id', $user_id);
        $this->db->where('r.start_date <=', $date);

        // ============================================================
        // end_date bisa NULL → dianggap berlaku
        // ============================================================
        $this->db->group_start();
        $this->db->where('r.end_date >=', $date);
        $this->db->or_where('r.end_date IS NULL', null, false);
        $this->db->group_end();

        // ============================================================
        // Filter hari (match weekly OR monthly)
        // ============================================================
        $this->db->group_start();
        $this->db->where('d.sdays', $sdays_week);
        $this->db->or_where('d.sdays', $sdays_month);
        $this->db->group_end();

        // Ambil semua shift (jangan LIMIT)
        $this->db->order_by('d.start_time', 'ASC');

        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            return $query->result_array(); // 🔥 return multi row
        }

        return false;
    }
}
