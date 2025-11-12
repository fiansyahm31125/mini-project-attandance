<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Tbuserusedclasses extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Tbuserusedclasses_model');
        header('Content-Type: application/json');
    }

    public function get_by_appid_and_empid($appid = null, $empid = null)
    {
        if (!$appid || !$empid) {
            echo json_encode([
                'status' => false,
                'message' => 'Parameter appid dan empid harus diisi.'
            ]);
            return;
        }

        // empid dari JS berarti user_id di database
        $data = $this->Tbuserusedclasses_model->get_by_appid_and_userid($appid, $empid);

        if ($data) {
            echo json_encode([
                'status' => true,
                'data' => $data
            ]);
        } else {
            echo json_encode([
                'status' => false,
                'message' => 'Data tidak ditemukan.'
            ]);
        }
    }
}
