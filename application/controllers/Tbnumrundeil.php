<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Tbnumrundeil extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Tbnumrundeil_model');
    }

    // GET /index.php/tbnumrundeil/get_by_num_run_id/{num_run_id}
    public function get_by_num_run_id($num_run_id)
    {
        $data = $this->Tbnumrundeil_model->get_by_num_run_id($num_run_id);

        if ($data) {
            echo json_encode([
                'status' => true,
                'data' => $data
            ]);
        } else {
            echo json_encode([
                'status' => false,
                'message' => 'Data tidak ditemukan'
            ]);
        }
    }
}
