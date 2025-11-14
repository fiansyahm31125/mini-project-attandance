<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Tbdepartements extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Tbdepartements_model');
    }

    /**
     * GET: /index.php/tbdepartements/get_names/{appid}
     * Contoh: http://localhost:8080/index.php/tbdepartements/get_names/IA01M168064F20250505533
     */
    public function get_names($appid = null)
    {
        // Pastikan method GET
        if ($this->input->method() !== 'get') {
            $this->output
                ->set_status_header(405)
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'status'  => 'error',
                    'message' => 'Method Not Allowed'
                ]));
            return;
        }

        // Validasi AppID wajib ada
        if (!$appid || empty(trim($appid))) {
            $this->output
                ->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'status'  => 'error',
                    'message' => 'AppID is required'
                ]));
            return;
        }

        // Ambil data dari model (id + name)
        $departments = $this->Tbdepartements_model->get_departments_by_appid($appid);

        // Jika tidak ada data
        if (empty($departments)) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'status' => 'success',
                    'data'   => []  // array kosong, bukan null
                ]));
            return;
        }

        // Berhasil → kirim array objek {id: "10", name: "Marketing"}
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => 'success',
                'data'   => $departments   // sudah berupa array of objects/associative
            ]));
    }
}
