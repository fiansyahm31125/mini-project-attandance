<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Tbcheckinout_mobile_model extends CI_Model
{
    protected $table = 'tbcheckinout_mobile';

    /**
     * Ambil waktu CheckIn paling awal dan CheckOut paling akhir
     * berdasarkan employee_id dan tanggal (YYYY-MM-DD)
     */
    public function get_checkin_checkout($employee_id, $date)
    {
        if (empty($employee_id) || empty($date)) {
            return null;
        }

        // Pastikan hanya tanggalnya yang dibandingkan
        $this->db->select("
            MIN(CASE WHEN checklog_event = 'CheckIn' THEN checklog_date END) AS first_checkin,
            MAX(CASE WHEN checklog_event = 'CheckOut' THEN checklog_date END) AS last_checkout
        ", false);

        $this->db->from($this->table);
        $this->db->where('employee_id', $employee_id);
        $this->db->where("DATE(checklog_date)", $date); // hanya tanggalnya yang dibandingkan

        $query = $this->db->get();
        return $query->row(); // kembalikan satu object {first_checkin, last_checkout}
    }
}
