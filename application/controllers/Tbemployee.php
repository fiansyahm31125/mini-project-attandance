<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Tbemployee extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        // Load model
        $this->load->model('Tbemployee_model');
        // (Opsional) Load helper untuk response JSON
        $this->load->helper('url');
    }

    // Contoh: http://localhost:8000/index.php/tbemployee/get_by_appid/123
    public function get_by_appid($appid = null)
    {
        if ($appid === null) {
            // Kalau tidak ada appid dikirim, kasih respon error
            $response = [
                'status' => false,
                'message' => 'Parameter appid wajib diisi.'
            ];
        } else {
            $data = $this->Tbemployee_model->get_by_appid($appid);

            if (!empty($data)) {
                $response = [
                    'status' => true,
                    'data' => $data
                ];
            } else {
                $response = [
                    'status' => false,
                    'message' => 'Data tidak ditemukan.'
                ];
            }
        }

        // Output dalam format JSON
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }

    public function get_by_department()
    {
        if ($this->input->method() !== 'get') {
            show_error('Method Not Allowed', 405);
            return;
        }

        $appid          = $this->input->get('appid', TRUE);
        $department_id  = $this->input->get('department_id', TRUE);

        if (empty($appid) || empty($department_id)) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'status' => 'error',
                    'message' => 'appid dan department_id wajib dikirim'
                ]));
            return;
        }

        $employees = $this->Tbemployee_model->get_by_department($appid, $department_id);

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => 'success',
                'data'   => $employees
            ]));
    }
}
