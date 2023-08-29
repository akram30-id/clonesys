<?php

use chriskacerguis\RestServer\RestController;

defined("BASEPATH") or exit("NO DIRECT SCRIPT IS ALLOWED");

class Pickup extends RestController
{
    function __construct()
    {
        parent::__construct();
        $this->load->model("pickup_m");
        $this->load->library("form_validation");
    }

    private function _inputValidation()
    {
        $this->form_validation
            ->set_rules("customer_code", "customer_code", "required", [
                "required" => "customer_code TIDAK BOLEH KOSONG"
            ])
            ->set_rules("reference_no", "reference_no", "required", [
                "required" => "reference_no TIDAK BOLEH KOSONG"
            ])
            ->set_rules("pickup_name", "pickup_name", "required", [
                "required" => "pickup_name TIDAK BOLEH KOSONG"
            ])
            ->set_rules("pickup_address", "pickup_address", "required", [
                "required" => "pickup_address TIDAK BOLEH KOSONG"
            ])
            ->set_rules("pickup_phone", "pickup_phone", "required", [
                "required" => "pickup_phone TIDAK BOLEH KOSONG"
            ])
            ->set_rules("pickup_district_code", "pickup_district_code", "required", [
                "required" => "pickup_district_code TIDAK BOLEH KOSONG"
            ])
            ->set_rules("service_type_code", "service_type_code", "required", [
                "required" => "service_type_code TIDAK BOLEH KOSONG"
            ])
            ->set_rules("quantity", "quantity", "required", [
                "required" => "quantity TIDAK BOLEH KOSONG"
            ])
            ->set_rules("weight", "weight", "required", [
                "required" => "weight TIDAK BOLEH KOSONG"
            ])
            ->set_rules("volumetric", "volumetric", "required", [
                "required" => "volumetric TIDAK BOLEH KOSONG"
            ])
            ->set_rules("shipment_type_code", "shipment_type_code", "required", [
                "required" => "shipment_type_code TIDAK BOLEH KOSONG"
            ])
            ->set_rules("insurance_flag", "insurance_flag", "required", [
                "required" => "insurance_flag TIDAK BOLEH KOSONG"
            ])
            ->set_rules("cod_flag", "cod_flag", "required", [
                "required" => "cod_flag TIDAK BOLEH KOSONG"
            ])
            ->set_rules("shipper_name", "shipper_name", "required", [
                "required" => "shipper_name TIDAK BOLEH KOSONG"
            ])
            ->set_rules("shipper_address", "shipper_address", "required", [
                "required" => "shipper_address TIDAK BOLEH KOSONG"
            ])
            ->set_rules("shipper_phone", "shipper_phone", "required", [
                "required" => "shipper_phone TIDAK BOLEH KOSONG"
            ])
            ->set_rules("destination_district_code", "destination_district_code", "required", [
                "required" => "destination_district_code TIDAK BOLEH KOSONG"
            ])
            ->set_rules("receiver_name", "receiver_name", "required", [
                "required" => "receiver_name TIDAK BOLEH KOSONG"
            ])
            ->set_rules("receiver_address", "receiver_address", "required", [
                "required" => "receiver_address TIDAK BOLEH KOSONG"
            ])
            ->set_rules("receiver_phone", "receiver_phone", "required", [
                "required" => "receiver_phone TIDAK BOLEH KOSONG"
            ])
            ->set_rules("pickup_place", "pickup_place", "required", [
                "required" => "pickup_place TIDAK BOLEH KOSONG"
            ]);

        if ($this->form_validation->run() == FALSE) {
            return FALSE;
        }

        return TRUE;
    }

    private function _isAWBNoExist()
    {
        if ($this->pickup_m->checkReferenceNo($this->post("reference_no"))) {
            return $this->response([
                "status" => "fail",
                "message" => "RERENCE NO SUDAH ADA. MOHON GANTI DENGAN YANG LAIN."
            ], RestController::HTTP_BAD_REQUEST);
        }

        if ($this->_apiuser->is_reference_move_to_awb == 1) {
            if ($this->pickup_m->checkAWBNo($this->post("reference_no"))) {
                return $this->response([
                    "status" => "fails",
                    "message" => "REF_WB NO SUDAH ADA. MOHON GANTI DENGAN YANG LAIN."
                ], RestController::HTTP_BAD_REQUEST);
            }
        }

        if ($this->post("awb_no")) {
            if ($this->pickup_m->checkAWBNo($this->post("awb_no"))) {
                return $this->response([
                    "status" => "fails",
                    "message" => "AWB NO SUDAH ADA. MOHON GANTI DENGAN YANG LAIN."
                ]);
            }
        }
    }

    private function _isProductCodeFound()
    {
        if (!$this->pickup_m->checkProductCode($this->post("service_type_code"))) {
            return FALSE;
        }

        return TRUE;
    }

    private function _isShipmentTypeCodeValid()
    {
        if (!in_array($this->post("shipment_type_code"), ["SHTPC", "SHTDC"])) {
            return FALSE;
        }

        return TRUE;
    }

    private function _validateParamReturnDistrictName()
    {
        if (!empty($this->post("return_district_name")) && !empty($this->post("return_city_name")) && !empty($this->post("return_province_name"))) {
            return TRUE;
        }

        return FALSE;
    }

    private function _validateParamReturnAddress()
    {
        if (trim($this->post("return_address")) == NULL || trim($this->post("return_address")) == "") {
            return $this->response([
                "status" => "fail",
                "message" => "PARAMETER return_address TIDAK BOLEH KOSONG"
            ], RestController::HTTP_NOT_ACCEPTABLE);
        } else if (trim($this->post("return_phone")) == NULL || trim($this->post("return_phone")) == "") {
            return $this->response([
                "status" => "fail",
                "message" => "PARAMETER return_phone TIDAK BOLEH KOSONG"
            ], RestController::HTTP_NOT_ACCEPTABLE);
        } else if (trim($this->post("return_contract")) == NULL || trim($this->post("return_contract")) == "") {
            return $this->response([
                "status" => "fail",
                "message" => "PARAMETER return_contract TIDAK BOLEH KOSONG"
            ], RestController::HTTP_NOT_ACCEPTABLE);
        }
    }

    private function _defaultPackingNULL($apiKey)
    {
        $listData = ["Ac0mm3rc3_2022"];
        if (in_array($apiKey, $listData)) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    private function _roundKG($n)
    {
        if ($n < 1) {
            return 1; // 1kg
        }

        $fraction   = fmod($n, 1); // 1.25 -> 0.25 (get whole decimal)
        $round      = 0.3;
        $precision  = 5; // handling to comparing two float number

        // 0.3 >= 0.3
        if (round($fraction, $precision) >= round($round, $precision)) {
            $rounded_kg = ceil($n); // round up
        } else {
            $rounded_kg = floor($n); // round down
        }

        return $rounded_kg;
    }

    public function single_push_post()
    {
        try {
            if ($this->_inputValidation() == false) {
                return $this->response([
                    "status" => "fail",
                    "message" => validation_errors()
                ], RestController::HTTP_NOT_ACCEPTABLE);
            }

            $this->_isAWBNoExist();

            if ($this->_isProductCodeFound() == FALSE) {
                return $this->response([
                    "status" => "fail",
                    "message" => "SERVICE TYPE CODE TIDAK DITEMUKAN"
                ], RestController::HTTP_NOT_FOUND);
            }

            if ($this->_isShipmentTypeCodeValid() == FALSE) {
                return $this->response([
                    "status" => "fail",
                    "message" => "SHIPMENT TYPE CODE TIDAK DITEMUKAN"
                ], RestController::HTTP_NOT_FOUND);
            }

            $pickupDistrictCode = empty($this->_apiuser->pickup_district_code_default) ? $this->post("pickup_district_code") : $this->_apiuser->pickup_district_code_default;
            $destinationDistrictCode = $this->post("destination_district_code");
            $returnDistrictCode = $this->post("return_district_code");

            if ($this->_apiuser->mapping_destination_district_type == 1) { // mapping using client district name
                $mappingPickup = $this->pickup_m->getDistrictMappingByName($this->post("pickup_district_name"), $this->post("pickup_city_name"), $this->post("pickup_province_name"), $this->_apiuser->district_mapping_code);
                if ($mappingPickup) {
                    //  mapping_api_type 1 = pickup
                    if ($mappingPickup->mapping_api_type == 1 || $mappingPickup->mpping_api_type == NULL) {
                        $pickupDistrictCode = $mappingPickup->district_code;
                    } else {
                        $pickupDistrictCode = NULL;
                    }
                } else {
                    $pickupDistrictCode = NULL;
                }
            } else if ($this->_apiuser->mapping_destination_district_type == 2) { // mapping using client district code
                $mappingPickup = $this->pickup_m->getDistrictMappingByCode($pickupDistrictCode, $this->_apiuser->district_mapping_code);

                if ($mappingPickup) {
                    if ($mappingPickup->mapping_api_type == 1 || $mappingPickup->mapping_api_type == NULL) {
                        $pickupDistrictCode = $mappingPickup->district_code;
                    } else {
                        $pickupDistrictCode = NULL;
                    }
                } else {
                    $pickupDistrictCode = NULL;
                }
            }

            if ($this->_apiuser->mapping_destination_district_type == 1) { // mapping using client district name
                $mappingPickup = $this->pickup_m->getDistrictMappingByName($this->post("pickup_district_name"), $this->post("pickup_city_name"), $this->post("pickup_province_name"), $this->_apiuser->district_mapping_code);
                if ($mappingPickup) {
                    //  mapping_api_type 1 = pickup
                    if ($mappingPickup->mapping_api_type == 1 || $mappingPickup->mpping_api_type == NULL) {
                        $destinationDistrictCode = $mappingPickup->district_code;
                    } else {
                        $destinationDistrictCode = NULL;
                    }
                } else {
                    $destinationDistrictCode = NULL;
                }
            } else if ($this->_apiuser->mapping_destination_district_type == 2) { // mapping using client district code
                $mappingPickup = $this->pickup_m->getDistrictMappingByCode($destinationDistrictCode, $this->_apiuser->district_mapping_code);

                if ($mappingPickup) {
                    if ($mappingPickup->mapping_api_type == 1 || $mappingPickup->mapping_api_type == NULL) {
                        $destinationDistrictCode = $mappingPickup->district_code;
                    } else {
                        $destinationDistrictCode = NULL;
                    }
                } else {
                    $destinationDistrictCode = NULL;
                }
            }

            $returnDistrict = NULL;
            if ($returnDistrict || $this->_validateParamReturnDistrictName()) {
                if ($this->_apiuser->mapping_return_district_type == 1) {
                    $mappingDestination = $this->pickup_m->getDistrictMappingByName($this->post("return_district_name"), $this->post("return_city_name"), $this->post("return_province_name"), $this->_apiuser->district_mapping_code);

                    if ($mappingDestination) {
                        $returnDistrictCode = $mappingDestination->district_code;
                    } else {
                        $returnDistrictCode = NULL;
                    }
                } else if ($this->_apiuser->mapping_return_district_type == 2) {
                    $mappingDestination = $this->pickup_m->getDistrictMappingByCode($this->post("return_district_code"), $this->_apiuser->district_mapping_code);

                    if ($mappingDestination) {
                        $returnDistrictCode = $mappingDestination->district_code;
                    } else {
                        $returnDistrictCode = NULL;
                    }
                }

                $returnDistrict = $this->pickup_m->checkDistrict(trim($returnDistrictCode));
                if (!$returnDistrict) {
                    return $this->response([
                        "status" => "fail",
                        "message" => "KODE KECAMATAN RETURN (@return_district_code) TIDAK DITEMUKAN"
                    ], RestController::HTTP_NOT_FOUND);
                }

                if ($returnDistrict) {
                    $this->_validateParamReturnAddress();
                }
            }

            $pickupDistrict = $this->pickup_m->checkDistrict(trim($pickupDistrictCode));
            $destinationDistrict = $this->pickup_m->checkDistrict(trim($destinationDistrictCode));

            if (!$pickupDistrict) {
                return $this->response([
                    "status" => "fail",
                    "message" => "KODE KECAMATAN PICKUP/ASAL TIDAK DITEMUKAN: " . $pickupDistrictCode
                ], RestController::HTTP_NOT_FOUND);
            } else if (!$destinationDistrict) {
                return $this->response([
                    "status" => "fail",
                    "message" => "KODE KECAMATAN PICKUP/ASAL TIDAK DITEMUKAN: " . $destinationDistrictCode
                ], RestController::HTTP_NOT_FOUND);
            } else {
                /**
                 * @SET VARIABLE FOR AKUN
                 *
                 *   $awbPrefixss
                 *   $isPickup
                 *   $customerCode
                 *   $customerCodeName
                 *   $flagTrackingPickup
                 *   $pickupPlace
                 *   $isReferenceMoveToAwb
                 */
                $awbPrefix = $this->_apiuser->awb_prefix;
                $isPickup = $this->_apiuser->pickup_rowstate;
                $flagNewAWB = NULL;

                // tidak menggunakan @parameter customer_code yang dikirim dari API
                // maka pakai @parameter customer_code_cod | customer_code_non_cod dari DB
                if ($this->_apiuser->is_used_customer_code_api == 0) { // menggunakan customer code dari DB
                    if ($this->post("cod_flag") == 1) { // non COD
                        $customerCode = $this->_apiuser->customer_code_non_cod;
                        $getCustomer = $this->pickup_m->getCustomer($customerCode);
                        if (!$getCustomer) {
                            return $this->response([
                                "status" => "fail",
                                "message" => "Customer " . $customerCode . " Not Found"
                            ], RestController::HTTP_NOT_FOUND);
                        }
                    } else if ($this->post("cod_flag") == 2) { // COD
                        $customerCode = $this->_apiuser->customer_code_cod;
                        $getCustomer = $this->pickup_m->getCustomer($customerCode);
                        if (!$getCustomer) {
                            return $this->response([
                                "status" => "fail",
                                "message" => "Customer " . $customerCode . " Not Found"
                            ], RestController::HTTP_NOT_FOUND);
                        }
                    }
                } else { // menggunakan customer_code dari API
                    $customerCode = $this->post("customer_code");
                    $getCustomer = $this->pickup_m->getCustomer($customerCode);
                    if (!$getCustomer) {
                        return $this->response([
                            "status" => 'fail',
                            "message" => "Customer " . $customerCode . " Not Found"
                        ], RestController::HTTP_NOT_FOUND);
                    }

                    if ($getCustomer->flag_api == 0) {
                        return $this->response([
                            "status" => "fail",
                            "message" => "Customer " . $customerCode . " Not Ready Yet"
                        ], RestController::HTTP_NOT_FOUND);
                    }

                    if ($getCustomer->cod_flag != $this->post("cod_flag")) {
                        return $this->response([
                            "status" => "fail",
                            "message" => "Customer Cod Flag and @param Cod Flag not matched"
                        ], RestController::HTTP_NOT_FOUND);
                    }
                }

                if ($getCustomer->rowstate == 3) {
                    return $this->response([
                        "message" => "Customer Suspended",
                        "status" => "fail"
                    ], RestController::HTTP_NOT_FOUND);
                }

                if ($this->post("cod_flag") == 2 && $destinationDistrict->coverage_cod != 1) {
                    return $this->response([
                        "status" => "fail",
                        "message" => "Destination District Code not covered for COD"
                    ], RestController::HTTP_NOT_FOUND);
                }

                $customerCodeName = $customerCode . " - " . $getCustomer->customer_name;
                $flagNewAWB = !empty($getCustomer->flag_new_awb) ? $getCustomer->flag_new_awb : NULL;
                $flagPhoto = ($getCustomer->flag_photo == 1) ? 1 : NULL;
                $flagSignature = ($getCustomer->flag_signature == 1) ? 1 : NULL;
                $flagSMS = $getCustomer->flag_sms;
                $flagSMSIncoming = $getCustomer->flag_sms_incoming;
                $flagSMSPOD = $getCustomer->flag_sms_pod;

                $pickupBranchCode = $pickupDistrict->branch_code;
                $pickupDistrictCode = $pickupDistrict->district_code;

                $flagTrackingPickup = $this->_apiuser->flag_tracking_pickup;
                $pickupPlace = $this->_apiuser->pickup_place;
                $isReferenceMoveToAwb = $this->_apiuser->is_reference_move_to_awb;

                // mapping district_code seller yang salah
                if ($this->_apiuser->is_fix_seller_district_code == 1) {
                    $fixSellerDistrict = $this->pickup_m->fixSellerDistrict($this->_apiuser->api_code);
                    if ($fixSellerDistrict) {
                        $sellerDistrict = $this->pickup_m->checkDistrict($fixSellerDistrict->fix_seller_district_code);
                        $pickupDistrictCode = $sellerDistrict->district_code;
                        $pickupBranchCode = $sellerDistrict->branch_code;
                    }
                }

                $this->db->trans_start();

                if ($isPickup == 1) {
                    $awbRowstate = 1;
                    $pickupRowstate = 1;
                    $directToVerification = 1; // langsung verifikasi
                } else {
                    if ($flagTrackingPickup == 0) {
                        $awbRowstate = NULL;
                        $pickupRowstate = 1; // entri
                    } else {
                        $awbRowstate = 1; // pickup
                        $pickupRowstate = 1; // entri
                    }

                    $directToVerification = 0;
                }

                $koli = 1;
                if (!empty($this->_apiuser->default)) { // ad default koli
                    $koli = 1;
                } else {
                    $koli = $this->post("quantity");
                }

                $packingTypeCode = $this->post("packing_type_code");
                if ($this->_defaultPackingNULL($this->_apiuser->api_key)) {
                    $packingTypeCode = NULL;
                }

                // roud up or round down kilo
                $kilo = $this->_roundKG($this->post("weight"));

                $dataPickup = [
                    "customer_code" => $customerCode,
                    "pickup_date" => date("Y-m-d H:i:s"),
                    "kilo" => $kilo,
                    "koli" => $koli,
                    "total_item" => $this->post("total_item"),
                    "volumetric" => $this->post("volumetric"),
                    "pickup_branch_code" => $pickupBranchCode,
                    "pickup_district_code" => $pickupDistrictCode,
                    "pickup_name" => strtoupper($this->post("pickup_name")),
                    "pickup_address" => strtoupper($this->post("pickup_address")),
                    "pickup_phone" => $this->post("pickup_phone"),
                    "pickup_email" => $this->post("pickup_email"),
                    "pickup_postal_code" => $this->post("pickup_postal_code"),
                    "pickup_contact" => strtoupper($this->post("pickup_contact")),
                    "pickup_latitude" => $this->post("pickup_latitude"),
                    "pickup_longitude" => $this->post("pickup_longitude"),
                    "shipment_label_flag" => $this->post("shipment_label_flag"),
                    "create_date" => date("Y-m-d H:i:s"),
                    "user_inp" => $awbPrefix,
                    "pickup_place" => $pickupPlace,
                    "direct_to_verification" => $pickupRowstate
                ];

                $this->pickup_m->insertPickup($dataPickup);
                $lastPickupID = $this->db->insert_id();
                $pickupNo = "PUP" . (empty($pickupDistrict) ? "CGK" : $pickupDistrict->branch_code) . str_pad($lastPickupID, 10, "0", STR_PAD_LEFT);

                $this->pickup_m->updatePickup($lastPickupID, $pickupNo);

                $dataAWB = [
                    "awb_no" => $this->post("awb_no"),
                    "awb_parent_no" => $this->post("awb_parent_no"),
                    "pickup_no" => $this->post($pickupNo),
                    "origin_branch_code" => $pickupBranchCode,
                    "origin_district_code" => $pickupDistrictCode,
                    "origin_counter_code" => $pickupBranchCode . "000",
                    "destination_branch_code" => $destinationDistrict->branch_code,
                    "destination_district_code" => $destinationDistrict->district_code,
                    "destination_district_code_name" => $destinationDistrict->district_name,
                    "customer_code" => $customerCode,
                    "customer_code_name" => $customerCodeName,
                    "service_type_code" => $this->pickup_m->checkProductCode($this->post("service_type_code"))->service_type_code,
                    "product_code" => $this->pickup_m->checkProductCode($this->post("service_type_code"))->product_code,
                    "kilo" => $this->post("weight"),
                    "koli" => $koli,
                    "volumetric" => $this->post("volumetric"),
                    "volumetric_customer" => $this->post("volumetric"),
                    "description_item" => strtoupper($this->post("description_item") ?? ""),
                    "shipment_type_code" => $this->post("shipment_type_code"),
                    "shipment_content_code" => $this->post("shipment_type_code"),
                    "packing_type_code" => $this->post("packing_type_code"),
                    "insurance_flag" => $this->post("insurance_flag"),
                    "insurance_cost" => $this->post("insurance_value"),
                    "insurance_type_code" => $this->post("insurance_type_code"),
                    "transaction_type_code" => "TRTCR",
                    "cod_flag" => $this->post("cod_flag"),
                    "price_cod" => $this->post("cod_value"),
                    "price_cod_customer" => $this->post("cod_value"),
                    "item_value" => $this->post("item_value"),
                    "shipper_name" => $this->post("shipper_name"),
                    "shipper_address_1" => strtoupper($this->post("shipper_address")),
                    "shipper_phone_1" => $this->post("shipper_phone"),
                    "shipper_email" => $this->post("shipper_email"),
                    "shipper_postal_code" => $this->post("shipper_postal_code"),
                    "shipper_contact" => $this->post("shipper_contact"),
                    "receiver_name" => $this->post("receiver_name"),
                    "receiver_address_1" => $this->post("receiver_address"),
                    "receiver_phone_1" => $this->post("receiver_phone"),
                    "receiver_email" => $this->post("receiver_email"),
                    "receiver_postal_code" => $this->post("receiver_postal_code"),
                    "receiver_contact" => strtoupper($this->post("receiver_contact") ?? ""),
                    "pickup_date" => date("Y-m-d"),
                    "date" => date("d"),
                    "month" => date("m"),
                    "year" => date("Y"),
                    "pickup_time" => date("H:i:s"),
                    "rowstate" => 1,
                    "flag_new_awb" => $flagNewAWB,
                    "flag_photo" => $flagPhoto,
                    "flag_signature" => $flagSignature,
                    "flag_sms" => $flagSMS,
                    "flag_sms_incoming" => $flagSMSIncoming,
                    "flag_sms_pod" => $flagSMSPOD,

                    "return_origin_district_code" => !empty($returnDistrict) ? $returnDistrict->district_code : NULL,
                    "return_address" => $this->post("return_address"),
                    "return_address" => $this->post("return_address"),
                    "return_phone" => $this->post("return_phone"),
                    "return_contact" => $this->post("return_contact"),
                ];

                // client ngirim AWB sendiri
                if (trim($this->post("awb_no") ?? "") != "") {
                    $dataAWB["awb_no"] = $awbNo = str_replace(" ", "", $this->post("awb_no"));
                    $dataAWB["parent_no"] = $dataAWB["awb_no"];
                }
                $this->pickup_m->insertAWB($dataAWB);
                $lastInsertAWBID = $this->db->insert_id();

                // client ga ngirim AWB, maka nomor AWB digenerate oleh sistem
                if ($this->post("awb_no") == NULL) {
                    if ($isReferenceMoveToAwb == 0) {
                        $awbNo = $awbPrefix . str_pad($lastInsertAWBID, 10, 0, STR_PAD_LEFT);
                    } else {
                        $awbNo = str_replace(" ", "", $this->post("reference_no"));
                    }
                    $awbParentNo = $awbNo;
                    $this->pickup_m->updateAWBAutoGenerate($lastInsertAWBID, $awbNo, $awbParentNo);
                }

                $this->pickup_m->updatePickupWithNewAWB($lastPickupID, $pickupNo, $awbNo);

                $dataPickupAWB = [
                    "pickup_no" => $pickupNo,
                    "awb_no" => $awbNo,
                    "create_date" => date("Y-m-d H:i:s")
                ];

                $this->pickup_m->insertPickupAWB($dataPickupAWB);

                $this->db->trans_complete();

                if ($this->db->trans_status() === FALSE) {
                    return $this->response("trans_status === FALSE", RestController::HTTP_BAD_REQUEST);
                } else {
                    $msg = [
                        "awb_no" => $awbNo,
                        "reference_no" => $this->post("reference_no"),
                        "origin_branch_code" => $pickupBranchCode,
                        "destination_branch_code" => $destinationDistrict->branch_code,
                    ];

                    if ($this->_apiuser->flag_label == 1) {
                        $msg["label"] = $this->_apiuser->url_label . $this->post("awb_no") . "&api_key=" . $this->_apiuser->api_key;
                    }

                    return $this->response([
                        "status" => "success",
                        "data" => $msg,
                        "msg" => "Pickup transfer success",
                        "response" => RestController::HTTP_CREATED
                    ]);
                }
            }
        } catch (\Throwable $th) {
            return $this->response([
                "status" => "fail",
                "message" => strval($th)
            ]);
        }
    }

    private function _validateCancelPickup($awbNo, $desc)
    {
        if (empty($awbNo)) {
            return $this->response([
                "status" => "fails",
                "message" => "AWB NO. TIDAK BOLEH KOSONG."
            ], RestController::HTTP_NOT_ACCEPTABLE);
        }

        if (empty($desc)) {
            return $this->response([
                "status" => "fails",
                "message" => "DESC TIDAK BOLEH KOSONG."
            ], RestController::HTTP_NOT_ACCEPTABLE);
        }
    }

    public function cancel_post()
    {
        try {
            $awbNo = $this->post("awb_no");
            $desc = $this->post("desc");
            $dateNow = date("Y-m-d H:i:s");
            $username = $this->_apiuser->api_code;

            $this->_validateCancelPickup($awbNo, $desc);

            $r = $this->pickup_m->checkAWBPickupNo($awbNo);

            if (!$r || empty($r)) {
                return $this->response([
                    "status" => "fails",
                    "message" => "AWB NO (PICKUP) " . $awbNo . " TIDAK DITEMUKAN"
                ], RestController::HTTP_NOT_FOUND);
            }
            
            $r2 = $this->pickup_m->getAWBDetail($awbNo);
            if (!$r2 || $r2 == "") {
                return $this->response([
                    "status" => "fails",
                    "message" => "AWB NO (AWB) " . $awbNo . " TIDAK DITEMUKAN"
                ], RestController::HTTP_NOT_FOUND);
            }

            if ($r2->rowstate > 8 && $r2->rowstate < 20) {
                return $this->response([
                    "status" => "fails",
                    "message" => "AWB NO " . $awbNo . " SUDAH PERNAH POD",
                    "data" => $r2
                ], RestController::HTTP_FORBIDDEN);
            }

            $this->db->trans_start();
            // tambah validasi , 
            //  jika status entry verified atau kurang dari entry verified , mengupdate void pickup
            $rowstate = "20";
            if ($r2->rowstate <= 3) {
                $rowstate = "25";

                // update tb_pickup
                $dataPickup = [
                    "pickup_status" => "GAGAL",
                    "pickup_reason_code" => "GL002",
                    "pickup_master_no" => NULL,
                    "void_type_code" => "VOID_CANCEL_ORDER",
                    "pickup_status_desc" => $desc,
                    "pickup_status_date" => $dateNow,
                    "pickup_from_name" => "-"
                ];
                $this->pickup_m->updatePickupToVoid($dataPickup, $r->pickup_no);

                // insert tb_pickup_detail
                $dataPickupDetail = [
                    "awb_no" => $awbNo,
                    "create_date" => $dateNow,
                    "pickup_status" => "GAGAL",
                    "pickup_desc" => $desc,
                    "user_inp" => $username
                ];
                $this->pickup_m->insertPickupDetail($dataPickupDetail);
            }

            // update tb_awb
            $dataAWB = [
                "rowstate" => $rowstate,
                "user_update" => $username,
                "last_update" => $dateNow
            ];
            $this->pickup_m->updateAWBToVoid($awbNo, $dataAWB);

            // insert tb_tracking
            $dataTracking = [
                "tracking_process" => $rowstate,
                "awb_no" => $awbNo,
                "tracking_doc_no" => $awbNo,
                "branch_code" => $r2->origin_branch_code,
                "counter_code" => $r2->origin_counter_code,
                "reference_no" => $awbNo,
                "description" => "[VOID BY: " . $username . "] [KET: " . $desc . "]",
                "create_date" => $dateNow,
                "user_inp" => $username
            ];
            $this->pickup_m->insertTracking($dataTracking);

            $this->db->trans_complete();

            if ($this->db->trans_status() === FALSE) {
                return $this->response([
                    "status" => "error",
                    "message" => "Transaction failed, pleaes try again"
                ], RestController::HTTP_INTERNAL_ERROR);
            } else {
                return $this->response([
                    "status" => "success",
                    "data" => [
                        "awb_no" => $awbNo,
                        "cancel_date" => $dateNow,
                    ],
                    "message" => "Cancel Order Success"
                ], RestController::HTTP_OK);
            }

        } catch (\Throwable $th) {
            return $this->response([
                "status" => "fails",
                "message" => strval($th)
            ]);
        }
    }
}
