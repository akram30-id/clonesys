<?php 
use chriskacerguis\RestServer\RestController;
use PhpParser\Node\Stmt\Return_;


defined('BASEPATH') or exit('No direct script access allowed');

class District extends RestController
{
    function __construct()
    {
        parent::__construct();
        $this->load->model("district_m");
    }

    private function getAllDistrict($page)
    {
        $districts = $this->district_m->getAll($page);

        if (!empty($districts->result())) {
            return $districts->result();
        } else {
            return false;
        }
    }

    private function getSpecificDistrict($keyword)
    {
        $districts = $this->district_m->getSpecific($keyword);

        if (!empty($districts->result())) {
            return $districts->result();
        } else {
            return false;
        }
    }

    public function get_get()
    {
        try {
            if ($this->get("district_name") === NULL) {
                $page = 1;

                if (!empty($this->get("page"))) {
                    $page = $this->get("page");
                }

                if ($this->getAllDistrict($page) === FALSE) {
                    return $this->response([
                        "status" => FALSE,
                        "error" => "No District Data Found"
                    ], RestController::HTTP_NOT_FOUND);
                } else {
                    return $this->response([
                        "page" => $page,
                        "count" => count($this->getAllDistrict($page)),
                        "data" => $this->getAllDistrict($page)
                    ], RestController::HTTP_OK);
                }
            } else {
                if ($this->getSpecificDistrict($this->get("district_name")) === FALSE) {
                    return $this->response([
                        "status" => FALSE,
                        "error" => "No District Data Found"
                    ], RestController::HTTP_NOT_FOUND);
                } else {
                    return $this->response($this->getSpecificDistrict($this->get("district_name")), RestController::HTTP_OK);
                }
            }
            
        } catch (\Throwable $th) {
            return $this->response([
                "status" => FALSE,
                "error" => strval($th)
            ]);
        }
    }
}
 