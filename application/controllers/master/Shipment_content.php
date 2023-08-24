<?php 
use chriskacerguis\RestServer\RestController;
use PHPUnit\Framework\MockObject\ReturnValueNotConfiguredException;

defined('BASEPATH') or exit('No direct script access allowed');

class Shipment_content extends RestController
{
    function __construct()
    {
        parent::__construct();
        $this->load->model("shipment_m");
    }

    private function getAllShipmentContent()
    {
        $shipments = $this->shipment_m->getAll()->result();

        if (!empty($shipments)) {
            return $shipments;
        } else {
            return false;
        }
    }

    private function getShipmentContentByType($typeCode)
    {
        $shipment = $this->shipment_m->getShipmentContentByTypeCode($typeCode)->result();

        if (!empty($shipment)) {
            return $shipment;
        } else {
            return false;
        }
    }

    private function jsonResponseNotFound()
    {
        return $this->response([
            "status" => FALSE,
            "message" => "No Shipment Content Found."
        ], RestController::HTTP_NOT_FOUND);
    }

    public function get_get()
    {
        try {
            $typeCode = $this->get("shipment_type_code");
            if (!empty($typeCode)) {
                if ($this->getShipmentContentByType($typeCode) !== false) {
                    $data = $this->getShipmentContentByType($typeCode);
                    return $this->response([
                        "status" => true,
                        "data" => $data
                    ], RestController::HTTP_OK);
                } else {
                    return $this->jsonResponseNotFound();
                }
            } else {
                if ($this->getAllShipmentContent() !== FALSE) {
                    $data = $this->getAllShipmentContent();

                    return $this->response([
                        "status" => TRUE,
                        "data" => $data
                    ]);
                } else {
                    return $this->jsonResponseNotFound();
                }
                
            }
        } catch (\Throwable $th) {
            return $this->response([
                "status" => FALSE,
                "message" => strval($th)
            ]);
        }
    }
}
 