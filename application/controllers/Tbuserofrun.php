<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Tbuserofrun extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Tbuserofrun_model', 'tbuserofrun');
    }

    /**
     * 🔹 Ambil semua data
     * endpoint: /index.php/tbuserofrun
     */
    public function index()
    {
        $data = $this->tbuserofrun->get_all();
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($data));
    }

    /**
     * 🔹 Ambil data berdasarkan AppID
     * endpoint: /index.php/tbuserofrun/get_by_appid/{appid}
     */
    public function get_by_appid($appid = null)
    {
        if (empty($appid)) {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(400)
                ->set_output(json_encode(['status' => false, 'message' => 'AppID wajib diisi']));
        }

        $data = $this->tbuserofrun->get_by_appid($appid);

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($data));
    }

    /**
     * 🔹 Ambil data berdasarkan AppID dan EmpID (user_id di tabel)
     * endpoint: /index.php/tbuserofrun/get_by_appid_and_empid/{appid}/{empid}
     */
    public function get_by_appid_and_empid($appid = null, $empid = null)
    {
        if (empty($appid) || empty($empid)) {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(400)
                ->set_output(json_encode(['status' => false, 'message' => 'AppID dan EmpID wajib diisi']));
        }

        $data = $this->tbuserofrun->get_by_appid_and_empid($appid, $empid);

        if ($data) {
            $response = ['status' => true, 'data' => $data];
        } else {
            $response = ['status' => false, 'message' => 'Data tidak ditemukan'];
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }
}
