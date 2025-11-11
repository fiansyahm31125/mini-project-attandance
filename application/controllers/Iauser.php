<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Iauser extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Iauser_model');
    }

    // Tampilkan semua user dalam format JSON
    public function index()
    {
        $data = $this->Iauser_model->get_all_users();

        // Set header dan kirim JSON
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($data));
    }

    // Contoh endpoint untuk ambil user by id
    public function detail($id = null)
    {
        if ($id === null) {
            show_404();
        }

        $data = $this->Iauser_model->get_user_by_id($id);

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($data));
    }

    public function select_appid()
    {
        $data['iausers'] = $this->Iauser_model->get_all_appid();
        $this->load->view('iauser_select_view', $data);
    }
}
