<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Tbcheckinout_mobile extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Tbcheckinout_mobile_model', 'checkinout');
        $this->output->set_content_type('application/json');
    }

    /**
     * Endpoint: /index.php/tbcheckinout_mobile/get_by_empid_and_date/{employee_id}/{date}
     * Contoh: /index.php/tbcheckinout_mobile/get_by_empid_and_date/15873/2025-11-12
     */
    public function get_by_empid_and_date($employee_id = null, $date = null)
    {
        if (empty($employee_id) || empty($date)) {
            echo json_encode([
                'status' => false,
                'message' => 'Parameter employee_id dan date wajib diisi'
            ]);
            return;
        }

        $data = $this->checkinout->get_checkin_checkout($employee_id, $date);

        if ($data && ($data->first_checkin || $data->last_checkout)) {
            echo json_encode([
                'status' => true,
                'data' => [
                    'first_checkin' => $data->first_checkin,
                    'last_checkout' => $data->last_checkout
                ]
            ]);
        } else {
            echo json_encode([
                'status' => false,
                'message' => 'Data checkin/checkout tidak ditemukan'
            ]);
        }
    }
}
