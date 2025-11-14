<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Tbdepartements_model extends CI_Model
{

    private $table = 'tbdepartements';

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Ambil department berdasarkan AppID
     * Return: array of associative [{ 'id' => 10, 'name' => 'Marketing' }, ...]
     */
    public function get_departments_by_appid($appid)
    {
        $this->db->select('id, name');                    // ambil id dan name
        $this->db->from($this->table);
        $this->db->where('appid', $appid);                // filter berdasarkan appid
        $this->db->distinct();                            // jika ada duplikat
        $this->db->order_by('name', 'ASC');

        $query = $this->db->get();

        return $query->result_array(); // hasilnya: array of associative array
        // Contoh: [ ['id'=>10, 'name'=>'Marketing'], ['id'=>15, 'name'=>'IT'] ]
    }
}
