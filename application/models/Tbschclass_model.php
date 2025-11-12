<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Tbschclass_model extends CI_Model
{
    protected $table = 'tbschclass';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Ambil data schclass berdasarkan ID dan tanggal (format Y-m-d)
     */
    public function get_name_by_id($schclass_id, $date = null)
    {
        $this->db->select('id, name,start_time,end_time');
        $this->db->from($this->table);
        $this->db->where('id', $schclass_id);

        // Jika ada parameter tanggal, cocokkan berdasarkan tahun

        $query = $this->db->get();
        return $query->row(); // satu baris saja
    }
}
