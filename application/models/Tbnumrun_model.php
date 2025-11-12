<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Tbnumrun_model extends CI_Model
{
    protected $table = 'tbnumrun';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Ambil data berdasarkan num_of_run_id
     * @param int|string $num_of_run_id
     * @return object|null
     */
    public function get_by_id($num_of_run_id)
    {
        return $this->db
            ->select('id, name')
            ->from($this->table)
            ->where('id', $num_of_run_id)
            ->get()
            ->row(); // Ambil satu baris (bukan array of rows)
    }
}
