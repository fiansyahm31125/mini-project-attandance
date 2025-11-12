<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Tbnumrundeil_model extends CI_Model
{

    public function get_by_num_run_id($num_run_id)
    {
        return $this->db
            ->select('start_time, end_time')
            ->from('tbnumrundeil')
            ->where('num_run_id', $num_run_id)
            ->get()
            ->row_array();
    }
}
