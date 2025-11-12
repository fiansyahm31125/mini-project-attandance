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
}
