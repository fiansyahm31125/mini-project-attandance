<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Tbusertempsch extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Tbusertempsch_model');
        header('Content-Type: application/json');
    }

    /**
     * Endpoint: /index.php/tbusertempsch/get_by_appid_and_empid/{appid}/{empid}
     */
    public function get_by_appid_and_empid($appid, $empid, $date = null)
    {
        $result = $this->Tbusertempsch_model->get_by_appid_and_empid($appid, $empid, $date);

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
