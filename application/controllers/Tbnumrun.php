<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Tbnumrun extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Tbnumrun_model');
    }

    /**
     * Endpoint: /index.php/tbnumrun/get_name_by_id/{num_of_run_id}
     */
    public function get_name_by_id($num_of_run_id = null)
    {
        if ($num_of_run_id === null) {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'status' => false,
                    'message' => 'Parameter num_of_run_id wajib diisi.'
                ]));
        }

        $data = $this->Tbnumrun_model->get_by_id($num_of_run_id);

        if ($data) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'status' => true,
                    'data' => [
                        'id' => $data->id,
                        'name' => $data->name
                    ]
                ]));
        } else {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'status' => false,
                    'message' => 'Data tidak ditemukan untuk ID: ' . $num_of_run_id
                ]));
        }
    }
}
