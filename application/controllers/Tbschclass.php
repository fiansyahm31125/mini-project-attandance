<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Tbschclass extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Tbschclass_model');
        header('Content-Type: application/json');
    }

    /**
     * Endpoint: /index.php/tbschclass/get_name_by_id/{id}/{date}
     */
    public function get_name_by_id($schclass_id, $date = null)
    {
        $result = $this->Tbschclass_model->get_name_by_id($schclass_id, $date);

        if ($result) {
            echo json_encode([
                'status' => true,
                'data' => $result
            ]);
        } else {
            echo json_encode([
                'status' => false,
                'message' => 'Data tidak ditemukan'
            ]);
        }
    }
}
