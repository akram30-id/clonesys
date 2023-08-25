<?php 
use chriskacerguis\RestServer\RestController;

defined('BASEPATH') or exit('No direct script access allowed');

class Shipment_cost extends RestController
{
    function __construct()
    {
        parent::__construct();
        $this->load->model("cost_m");
        $this->load->helper("format_helper");
        $this->load->library("form_validation");
    }

    private function postIn()
    {
        return $this->post(null, true);
    }

    private function inputValidation($post)
    {
        $this->form_validation
            // ->set_data($post)
            ->set_rules('origin', 'origin district code', 'required|trim')
            ->set_rules('destination', 'destination district code', 'required|trim')
            ->set_rules('customer_code', 'customer code', 'required|trim')
            ->set_rules('weight', 'weight', 'required|trim|is_numeric');

        if (isset($post["packing_type_code"])) {
            $this->form_validation->set_rules("packing_type_code", "packing_type_code", "required|trim");

            $this->form_validation->set_rules("volumetric", "volumetric", "required|trim");
        }

        if (isset($post["insurance_type_code"])) {
            $this->form_validation->set_rules("insurance_type_code", "insurance_type_code", "required|trim|integer");
        }

        $this->form_validation->set_error_delimiters("", "");

        if ($this->form_validation->run() === false) {
            return validation_errors();
        }

        return true;
    }

    private function checkOriginDistrict($post)
    {
        $origin = $this->cost_m->getDistrict($post["origin"])->row();
        if (!$origin) {
            return false;
        }

        return $origin;
    }

    private function checkDestinationDistrict($post)
    {
        $destination = $this->cost_m->getDistrict($post["destination"])->row();
        if (!$destination) {
            return false;
        }

        return $destination;
    }

    private function checkCustomerCode($post)
    {
        $customer = $this->cost_m->getCustomer($post["customer_code"])->row();
        if (!$customer) {
            return false;
        }

        return $customer;
    }

    public function test_post()
    {
        $data = [];
        $data["name"] = "Akram";
        return $this->response([
            "data" => $data
        ]);
    }

    private function _getContract($customerCode = NULL)
    {
        $contract = $this->cost_m->getContract($customerCode);

        return $contract;
    }

    private function _getPublish($customerCode, $destination, $origin, $whereIN = NULL,  $whereNOTIN = NULL, $costType = "OLD")
    {
        $contract = NULL;
        if (!empty($customerCode)) {
            $contract = $this->_getContract($customerCode);
        }

        if ($contract) {
            if ($contract->shipment_cost_id >= 10) {
                $shipment = $this->cost_m->getPublishNew($contract->shipment_cost_id, $destination, $origin, $whereIN, $whereNOTIN);
                return $shipment;
            } else {
                $shipment = $this->cost_m->getPublishOld($contract->shipment_cost_id, $destination, $origin, $whereIN, $whereNOTIN);
                return $shipment;
            }
        } else {
            if ($costType === "NEW") {
                $shipment = $this->cost_m->getPublishNew(13, $destination, $origin, $whereIN, $whereNOTIN);
                return $shipment;
            } else {
                $shipment = $this->cost_m->getPublishOld(9, $destination, $origin, $whereIN, $whereNOTIN);
                return $shipment;
            }
        }
    }

    private function _getSLAMin($sla)
    {
        $minSLA = "";
        $splitSLA = explode("-", $sla);
        if (isset($splitSLA) && isset($splitSLA[0])) {
            $minSLA = trim($splitSLA[0]);
        } else {
            $minSLA = "";
        }

        return $minSLA;
    }

    private function _getSLAMax($sla)
    {
        $maxSLA = "";
        $splitSLA = explode("-", $sla);
        if (isset($splitSLA) && isset($splitSLA[0])) {
            $maxSLA = trim($splitSLA[1]);
        } else {
            $maxSLA = "";
        }

        return $maxSLA;
    }

    private function _setVolumetric($theKG)
    {
        $checkPLT = preg_match('/\d{1,}x\d{1,}x\d{1,}/', $this->postIn()["volumetric"]); // check is volumetric input is PxLxT
        
        if (!$checkPLT) {
            return $this->response([
                "status" => "fail",
                "message" => "Parameter @volumetric harus PxLxT"
            ], RestController::HTTP_BAD_REQUEST);
        }

        $explodeVolumetric = explode("x", $this->postIn()["volumetric"]);
        $p = $explodeVolumetric[0];
        $l = $explodeVolumetric[1];
        $t = $explodeVolumetric[2];

        $volumetricKG = round(($p * $l * $t) / 6000);

        if ($volumetricKG > $theKG) {
            return $volumetricKG;
        } else {
            return $theKG;
        }
        
    }

    public function index_post()
    {
        if ($this->inputValidation($this->postIn()) !== TRUE) {
            return $this->response([
                "status" => "fail",
                "message" => $this->inputValidation($this->postIn())
            ]);
        }

        if ($this->checkCustomerCode($this->postIn()) === false) {
            return $this->response([
                "status" => "fail",
                "message" => "Customer Code tidak ditemukan"
            ], RestController::HTTP_NOT_FOUND);
        }

        $theKG = ($this->postIn()["weight"] < 1) ? 1 : $this->postIn()["weight"];
        $finalKG = $theKG;

        if ($this->postIn()["volumetric"]) {
            $finalKG = $this->_setVolumetric($theKG);
        }

        $costType = in_array($this->postIn()["cost_type"], ["NEW", "OLD"]) ? $this->postIn()["cost_type"] : "OLD";
        $customerCode = $this->postIn()["customer_code"];

        $customResult = [];
        $priceList = [];
        $priceListDetail = [];
        $priceListArray = [];

        $result = $this->_getPublish($customerCode, $this->postIn()["destination"], $this->postIn()["origin"], NULL, NULL, $costType);
        foreach ($result as $value) {
            if ($value->shipment_cost_id >= 10) {
                if ($value->coverage_area != 1) {
                    continue;
                }
            }

            $customResult["origin"] = $value->origin_district_code;
            $customResult["destination"] = $value->destination_district_code;
            $customResult["weight"] = $finalKG;
            $customResult["coverage_cod"] = $value->coverageCOD == 1 ? TRUE : FALSE;
            
            if (!empty($value->cost)) {

                $minimumKG = NULL;
                if ($value->service_type_code == "DRGREG") {
                    // check minimum KG
                    $getOriginProvinsi = $this->cost_m->getProvinsiByDistrict($value->origin_district_code);
                    $getDestinationProvinsi = $this->cost_m->getProvinsiByDistrict($value->destination_district_code);

                    $minimumKG = $this->cost_m->minimumKilo($getOriginProvinsi->provinsi_code, $getDestinationProvinsi->provinsi_code, $value->service_type_code);
                }

                if ($minimumKG) {
                    if ($minimumKG->min_kilo > $finalKG) {
                        $inputHeight = $minimumKG->min_kilo;
                    } else {
                        $inputHeight = $finalKG;
                    }
                    
                } else {
                    $inputHeight = $finalKG;
                }

                $priceList[$value->service_type_code] = ($value->cost * round($inputHeight));

                $detailOfService = [];
                $detailOfService["service_type_code"] = $value->service_type_code;
                $detailOfService["service_type_name"] = $this->cost_m->getServiceTypeName($value->service_type_code);
                $detailOfService["unit_price"] = $value->cost;
                $detailOfService["minimum_kilo"] = round($inputHeight);
                $detailOfService["price"] = ($value->cost * round($inputHeight));

                $sla = $value->lead_time;
                $detailOfService["sla"] = $sla . " Hari";
                $detailOfService["sla_min"] = $this->_getSLAMin($sla);
                $detailOfService["sla_max"] = $this->_getSLAMax($sla);

                $detailOfService["id"] = $value->shipment_cost_detail_id;

                $priceListDetail[$value->service_type_code] = $detailOfService;
                $priceListArray[] = $detailOfService;
            }
        }

        $customResult["price"] = $priceList;
        $customResult["price_detail"] = $priceListDetail;
        $customResult["price_array"] = $priceListArray;

        return $this->response($customResult, RestController::HTTP_OK);
    }
}
 