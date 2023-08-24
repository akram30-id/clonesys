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

    private function checkPackingType($packingTypeCode)
    {
        $packingType = $this->cost_m->getPacking($packingTypeCode)->row();
        if (!$packingType) {
            return false;
        }

        return $packingType;
    }

    // private function checkInsurance($insuranceAdminCost = 0, $insuranceCost = 0, $post)
    // {
    //     $insuranceType = $this->cost_m->getInsurance($post["insurance_type_code"]);
    //     if (!$insuranceType) {
    //         return false;
    //     }

    //     return $insuranceType;
    // }

    private function insurancePercentage($post)
    {
        $insurancePercentage = $this->cost_m->getInsurancePercentage($post["customer_code"])->row();
        $percentage = 0.3;

        if (isset($insurancePercentage->insurance_percentage) && ($insurancePercentage->insurance_percentage >= 0 || $insurancePercentage->insurance_percentage !== null)) {
            $percentage = $insurancePercentage->insurance_percentage;

            return $percentage;
        }

        return false;
    }

    // private function getContractShipment()
    // {
    //     $flag_shipment_cost = $this->_apiuser->flag_shipment_cost;
    //     $shipment_cost = $this->cost_m->publishActive()->row();

    //     $is_discount = false;
    //     $is_special = false;
    //     $contract = [];
    //     $customerCode = $this->checkCustomerCode($this->postIn());

    //     if ($flag_shipment_cost === 1) {
    //         $shipment_cost_id = $shipment_cost->shipment_cost_id;
    //     } else if ($flag_shipment_cost === 2) {
    //         // $contract = $this->cost_m->getContractPublish($customerCode, 1);
    //         $contract = $this->db->get_where("tb_contract", ["customer_code" => $customerCode, "rowstate" => "ACTIVE", "price_flag" => "1"])->row();
    //         if (!$contract) {
    //             return false;
    //         }

    //         $shipment_cost_id = $contract->shipment_cost_id;
    //     } else if ($flag_shipment_cost === 3) {
    //         // $contract = $this->cost_m->getContractPublish($customerCode, 1);
    //         $contract = $this->db->get_where("tb_contract", ["customer_code" => $customerCode, "rowstate" => "ACTIVE", "price_flag" => "1"])->row();
    //         if (!$contract) {
    //             return false;
    //         }

    //         $is_discount = true;
    //         $shipment_cost_id = $shipment_cost->shipment_cost_id;
    //     } else {
    //         $is_special = true;
    //         // $contract = $this->cost_m->getContractPublish($customerCode, 2);
    //         $contract = $this->db->get_where("tb_contract", ["customer_code" => $customerCode, "rowstate" => "ACTIVE", "price_flag" => "2"])->row();
    //         if (!$contract) {
    //             return false;
    //         }

    //         $shipment_cost_id = $contract->shipment_cost_id;
    //     }

    //     if ($is_special === false) {
    //         // get origin district code
    //         $orgDistrictCode = ($this->checkOriginDistrict($this->postIn())) ? $this->checkOriginDistrict($this->postIn()) : false;

    //         // get destination district code
    //         $destDistrictCode = ($this->checkDestinationDistrict($this->postIn())) ? $this->checkDestinationDistrict($this->postIn()) : false;

    //         if ($orgDistrictCode === false || $destDistrictCode === false) {
    //             return false;
    //         }

    //         $getShipment = $this->cost_m->getPublish($orgDistrictCode->district_code, $destDistrictCode->district_code, $shipment_cost_id)->result();
    //     } else {
    //         $getShipment = $this->cost_m->getSpecial($orgDistrictCode->district_code, $destDistrictCode->district_code, $shipment_cost_id)->result();
    //     }

    //     if (count($getShipment) < 1) {
    //         return false;
    //     }

    //     return ["shipmentCost" => $getShipment, "contract" => $contract, "is_discount" => $is_discount];
    // }

    private function getDiscount($contract, $service_type_code)
    {
        // foreach ($contract as $key => $value) {
        if ($service_type_code == 'UDRREG') {
            $discount =  $contract->udrreg_discount;
        } else if ($service_type_code == 'UDRONS') {
            $discount =  $contract->udrons_discount;
        } else if ($service_type_code == 'UDRSDS') {
            $discount =  $contract->udrsds_discount;
        } else { //jika DRGREG CARGO
            $discount = $contract->drgreg_discount;
        }

        return $discount;
    }

    public function test_post()
    {
        $data = [];
        $data["name"] = "Akram";
        return $this->response([
            "data" => $data
        ]);
    }

    // public function index_post()
    // {
    //     try {
    //         if ($this->inputValidation($this->postIn())) {
    //             if ($this->getContractShipment() !== false) {
    //                 $data = [];
    //                 $weight = round_kg($this->postIn()["weight"]);
    //                 foreach ($this->getContractShipment()->shipmentCost as $value) {
    //                     $services = $this->cost_m->getServiceDetail($value->service_type_code);

    //                     $data["origin"] = $this->postIn()["origin"];
    //                     $data["destination"] = $this->postIn()["destination"];
    //                     $data["coverage_cod"] = $value->coverage_cod == 1 ? true : false;

    //                     $finalKG = $weight;
    //                     $volumetricKG = 0;

    //                     if (isset($this->postIn()["volumetric"]) && $this->postIn()["volumetric"] !== null) {
    //                         $volumetricKG = volumetrict_kg($this->postIn()["volumetric"], $services->kilo_driver);
    //                         if ($volumetricKG > $weight) {
    //                             $finalKG = $volumetricKG;
    //                         }
    //                     }

    //                     $getMinKilo = $this->cost_m->minimumKilo($value->origin_branch_code, $value->destination_branch_code, $value->service_type_code)->row();
    //                     $minimumKilo = 1;
    //                     if ($getMinKilo) {
    //                         $minimumKilo = $getMinKilo->min_kilo;
    //                     }

    //                     //jika DRGREG/Cargo final kilogram nya harus minimal minimum kilo
    //                     if ($value->service_type_code === "DRGREG") {
    //                         if ($finalKG < $minimumKilo) {
    //                             $finalKG = $minimumKilo;
    //                         }
    //                     }

    //                     $packingCost = 0;

    //                     $contract = $this->getContractShipment()->contract;
    //                     $packingType = $this->checkPackingType($this->postIn()["packing_type_code"]);

    //                     $contractId = (isset($contract->contract_id) || $this->getContractShipment()->contract->contract_id != false) ? $this->getContractShipment()->contract->contract_id : null;

    //                     if (isset($packingType)) {
    //                         $packingCost = packing_cost($packingType->packing_type_code, $this->postIn()["volumetric"], $finalKG, $contractId);
    //                     }

    //                     $discount = ($this->getContractShipment()->is_discount === true) ? $this->getDiscount($contract, $value->service_type_code) : 0;

    //                     $insuranceAdminCost = 0;
    //                     $insuranceCost = 0;
    //                     if (isset($this->postIn()["insurance_type_code"])) {
    //                         if ($this->insurancePercentage($this->postIn()) === FALSE) {
    //                             return $this->response([
    //                                 "status" => FALSE,
    //                                 "message" => "Tipe Asuransi Tidak Ditemukan."
    //                             ], RestController::HTTP_NOT_FOUND);
    //                         }

    //                         $insuranceAdminCost = 2000;
    //                         $insuranceCost = ($this->insurancePercentage($this->postIn())/100) * $this->postIn()["item_value"];
    //                     }

    //                     $totalCost = (($value->cost - ($discount / 100)) * $finalKG) + $packingCost + $insuranceCost + $insuranceAdminCost;

    //                     $data["services"][] = [
    //                         "minimum_kilo" => $minimumKilo,
    //                         "insurance_kilo" => $insuranceCost,
    //                         "insurance_admin_cost" => $insuranceAdminCost,
    //                         "volumetric_kg" => $volumetricKG,
    //                         "packing_cost" => $packingCost,
    //                         "weight" => $weight,
    //                         "final_weight" => $finalKG,
    //                         "kilo_driver" => $services->kilo_driver,
    //                         "cost" => $value->cost,
    //                         "discount" => $discount . "%",
    //                         "total_cost" => $totalCost,
    //                         "service_type_code" => $value->service_type_code,
    //                         "service_type_name" => $services->descriptions,
    //                         "sla" => str_replace("-", " - ", str_replace(" ", "", $value->lead_time)) . " Hari"
    //                     ];
    //                 }

    //                 return $this->response([
    //                     "status" => TRUE,
    //                     "data" => $data,
    //                     "message" => "Showing Result"
    //                 ], RestController::HTTP_OK);
    //             } else {
    //                 return $this->response([
    //                     "status" => false,
    //                     "message" => "Harga Tidak Dapat Ditemukan"
    //                 ], RestController::HTTP_INTERNAL_ERROR);
    //             }
    //         } else {
    //             return $this->response([
    //                 "status" => false,
    //                 "message" => $this->inputValidation($this->postIn())
    //             ], RestController::HTTP_NOT_ACCEPTABLE);
    //         }
    //     } catch (\Throwable $th) {
    //         return $this->response([
    //             "status" => false,
    //             "message" => strval($th)
    //         ], RestController::HTTP_INTERNAL_ERROR);
    //     }
    // }

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
            $maxSLA = trim($splitSLA[0]);
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
        // return $this->response($this->_getPublish('', '', $customerCode, $costType), RestController::HTTP_OK);
    }
}
 