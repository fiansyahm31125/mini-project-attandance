<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Tbcheckinout_mobile_model extends CI_Model
{
    protected $table = 'tbcheckinout_mobile';
    protected $table2 = 'tbcheckinout';

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

        // --------- CHECKIN dari TABLE 1 ---------
        $this->db->select("MIN(checklog_date) AS first_checkin", false);
        $this->db->from($this->table);
        $this->db->where('employee_id', $employee_id);
        $this->db->where('checklog_date >=', $start);
        $this->db->where('checklog_date <=', $end);
        $this->db->where('checklog_event', 'CheckIn');

        $query = $this->db->get();

        $a = null;
        if ($query->num_rows() > 0) {
            $a = $query->row(); // object { first_checkin }
        }

        // --------- CHECKIN dari TABLE 2 ---------
        $this->db->select("MIN(checkinout_datetime) AS first_checkin", false);
        $this->db->from($this->table2);
        $this->db->where('checkinout_employee_id', $employee_id);
        $this->db->where('checkinout_datetime >=', $start);
        $this->db->where('checkinout_datetime <=', $end);

        $query = $this->db->get();

        $b = null;
        if ($query->num_rows() > 0) {
            $b = $query->row(); // object { first_checkin }
        }

        // --------- Jika keduanya NULL -> return NULL ---------
        if (!$a && !$b) {
            return null;
        }

        // Ambil nilai datetime dari masing-masing object
        $a_val = $a ? $a->first_checkin : null;
        $b_val = $b ? $b->first_checkin : null;

        // --------- Jika salah satu kosong -> return yang ada ---------
        if ($a_val && !$b_val) return $a;
        if (!$a_val && $b_val) return $b;

        // --------- Jika dua-duanya ada, ambil yang paling awal ---------
        if ($a_val <= $b_val) {
            return $a;
        } else {
            return $b;
        }
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

        $a = null;
        if ($query->num_rows() > 0) {
            $a = $query->row(); // hasil: object { last_checkout }
        }

        // --------- CHECKOUT dari TABLE 2 ---------
        $this->db->select("MAX(checkinout_datetime) AS last_checkout");
        $this->db->from($this->table2);
        $this->db->where('checkinout_employee_id', $employee_id);
        $this->db->where('checkinout_datetime >=', $start);
        $this->db->where('checkinout_datetime <=', $end);

        $query = $this->db->get();

        $b = null;
        if ($query->num_rows() > 0) {
            $b = $query->row(); // hasil: object { last_checkout }
        }

        // --------- Jika keduanya NULL -> return NULL ---------
        if (!$a && !$b) {
            return null;
        }

        // Ambil nilai datetime dari masing-masing object
        $a_val = $a ? $a->last_checkout : null;
        $b_val = $b ? $b->last_checkout : null;

        // --------- Jika salah satu kosong -> return yang ada ---------
        if ($a_val && !$b_val) return $a;
        if (!$a_val && $b_val) return $b;

        // --------- Jika dua-duanya ada, ambil yang paling akhir ---------
        if ($a_val >= $b_val) {
            return $a;
        } else {
            return $b;
        }
    }
}
