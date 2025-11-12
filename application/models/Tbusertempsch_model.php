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
    public function get_by_appid_and_empid($appid, $empid)
    {
        $this->db->select('id, appid, user_id, schclass_id');
        $this->db->from($this->table);
        $this->db->where('appid', $appid);
        $this->db->where('user_id', $empid);

        $query = $this->db->get();
        return $query->row(); // ambil satu baris data
    }
}
