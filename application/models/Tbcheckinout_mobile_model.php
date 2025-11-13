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

    public function get_checkin($employee_id, $start, $end)
    {
        // Validasi parameter
        if (empty($employee_id) || empty($start) || empty($end)) {
            return null;
        }

        // Query: ambil checkout terakhir dalam rentang waktu
        $this->db->select("MIN(checklog_date) AS first_checkin", false);
        $this->db->from($this->table);
        $this->db->where('employee_id', $employee_id);
        $this->db->where('checklog_date >=', $start);
        $this->db->where('checklog_date <=', $end);
        $this->db->where('checklog_event', 'CheckIn');

        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            return $query->row(); // hasil: object { last_checkout }
        }

        return null;
    }

    public function get_checkout($employee_id, $start, $end)
    {
        // Validasi parameter
        if (empty($employee_id) || empty($start) || empty($end)) {
            return null;
        }

        // Query: ambil checkout terakhir dalam rentang waktu
        $this->db->select("MAX(checklog_date) AS last_checkout", false);
        $this->db->from($this->table);
        $this->db->where('employee_id', $employee_id);
        $this->db->where('checklog_date >=', $start);
        $this->db->where('checklog_date <=', $end);
        $this->db->where('checklog_event', 'CheckOut');

        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            return $query->row(); // hasil: object { last_checkout }
        }

        return null;
    }
}
