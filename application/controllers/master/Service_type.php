<?php 
use chriskacerguis\RestServer\RestController;

defined('BASEPATH') or exit('No direct script access allowed');

class Service_type extends RestController
{

    function __construct()
    {
        parent::__construct();
        $this->load->model("service_m");
    }

    public function get_get()
    {
        try {
            $services = $this->service_m->getAll()->result();

            foreach ($services as $service) {
                $service_type_code = preg_replace('/\r\n/', '', $service->service_type_code);

                $service->service_type_code = $service_type_code;
            }

            if ($services) {
                return $this->response($services, RestController::HTTP_OK);
            } else {
                return $this->response([
                    "status" => FALSE,
                    "message" => "No Service Found."
                ], RestController::HTTP_NOT_FOUND);
            }
            
        } catch (\Throwable $th) {
            return $this->response([
                "status" => FALSE,
                "message" => strval($th)
            ]);
        }
    }

}


?>