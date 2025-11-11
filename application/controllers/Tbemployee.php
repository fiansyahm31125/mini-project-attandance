<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Iauser extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Iauser_model');
    }

    public function index()
    {
        $data['iausers'] = $this->Iauser_model->get_all_appid();
        $this->load->view('iauser_select_view', $data);
    }
}
