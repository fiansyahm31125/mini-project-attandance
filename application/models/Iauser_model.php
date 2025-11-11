<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Iauser_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
    }

    // Ambil semua data dari tabel iauser
    public function get_all_users()
    {
        $query = $this->db->get('iauser');
        return $query->result_array(); // hasil berupa array asosiatif
    }

    // Contoh ambil user by id
    public function get_user_by_id($id)
    {
        $this->db->where('id', $id);
        $query = $this->db->get('iauser');
        return $query->row_array();
    }

    public function get_all_appid()
    {
        $this->db->select('appid');
        $this->db->from('iauser');
        $query = $this->db->get();
        return $query->result_array(); // hasil: array of ['appid' => value]
    }
}
