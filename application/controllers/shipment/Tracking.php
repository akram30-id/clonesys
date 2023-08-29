<?php

use chriskacerguis\RestServer\RestController;

defined("BASEPATH") or exit("NO DIRECT SCRIPT IS ALLOWED");

class Tracking extends RestController
{
    function __construct()
    {
        parent::__construct();
        $this->load->model("tracking_m");
    }

    private function _isReferenceNo($apiKey)
    {
        $listApiKey = ["Dir4ct@Trac1n9"];
        if (in_array($apiKey, $listApiKey)) {
            return TRUE;
        }
        return FALSE;
    }

    private function _isAWBTracable($awbNo)
    {
        $canTracking = $this->tracking_m->getAWBTracable($awbNo);
        if ($canTracking) {
            if ($canTracking->is_tracking == 0) {
                return $this->response([
                    "status" => "fails",
                    "message" => "UNTRACABLE AWB NO."
                ], RestController::HTTP_BAD_REQUEST);
            }
        }
    }

    private function _listRowstate()
    {
        return [
            // 1,  // BELUM MANIFEST PICKUP
            2,  // ENTRI
            2.10, //ENTRI (SEDANG DI PICKUP)
            2.30, //ENTRI (PENDING PICKUP)
            3,  // ENTRI VERIFIED
            4,  // MANIFEST OUTGOING
            5,  // OUTGOING SMU
            // 6,  // TRANSIT
            7,  // INCOMING
            8,  // DELIVERY
            9,  // POD
            // 9.80,  // CUSTOMER CONFIRMATION
            10, // SHIPMENT LOST
            11, // SHIPMENT DAMAGE
            12, // OUTGOING RETURN
            13, // INCOMING RETURN
            14, // DELIVERY RETURN
            15, // SHIPMENT RETURN TO CLIENT
            20, // VOID
            21, // ENTRI (SEDANG DI PICKUP)
            22, // PICKED UP
            23, // ENTRI (PENDING PICKUP)
            24, // ENTRI (SEDANG PICKUP ULANG)
            25  // VOID PICKUP
        ];
    }

    private function _isCheckpointConfirmation()
    {
        $listKey = ["DEVELOPMENT#@"];
        $apiKey = getallheaders()["X-API-KEY"];

        if (in_array($apiKey, $listKey)) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    public function awb_get()
    {
        try {
            $awbNo = $this->get("awb_no");

            $apiKey = getallheaders()["X-API-KEY"];
            $cekApiKey = $this->tracking_m->cekApiKey($apiKey);
            $version = $cekApiKey->version;

            if ($awbNo == "") {
                return $this->response([
                    "status" => "fails",
                    "message" => "AWB NO. TIDAK BOLEH KOSONG"
                ], RestController::HTTP_NOT_ACCEPTABLE);
            }

            if ($this->_isReferenceNo($apiKey)) {
                $getAWBByRef = $this->tracking_m->getAWBByRef($awbNo);
                $awbNo = $getAWBByRef->awb_no;
            }

            $this->_isAWBTracable($awbNo);

            $result = $this->tracking_m->showDataTracking($awbNo, $this->_isCheckpointConfirmation(), $this->_listRowstate());

            if ($version == 2) {
                if (!$result) {
                    $result = $this->tracking_m->getAWBDetail($awbNo);
                }

                $referenceNo = NULL;
                $awbNo = NULL;
            } else {
                $referenceNo = NULL;
                $awbNo = NULL;
            }

            $hiddenCheckpoint = FALSE;
            foreach ($result as $key => $value) {
                // pod_type=4, delete checkpoint
                if ($hiddenCheckpoint) {
                    unset($result[$key]);
                }

                // get client reference_no and awb_no
                // kalo misalnya sudah manifest pickup, entri verified, atau entri
                //  sedang dipickup ulang
                if ($value->rowstate == '1' || $value->rowstate == '3' || $value->rowstate == '21') {
                    if ($referenceNo == NULL) {
                        $referenceNo = $value->tb_awb_reference_no;
                    }

                    if ($awbNo == NULL) {
                        $awbNo = $value->tb_awb_awb_no;
                    }
                }

                // kalo entri picked up
                if ($value->rowstate == '22') {
                    $value->photo_pickup_multi = "https://client.coresyssap.com/getimage/get_image_pickup?key=0&proof_no=PRF0001907758&type=1";
                }

                //** 19 Januari 2023 Update untuk buat parameter nama kurir */
                // kalo entri delivery dan POD
                if ($value->rowstate == '8' || $value->rowstate == '9') {
                    // cari nama courier
                    $courierName = $this->tracking_m->getCourier($value->tracking_doc_no);
                }

                // update client reference no and awb_no
                if ($referenceNo != NULL || $awbNo != NULL) {
                    $value->reference_no = $referenceNo;
                    $value->awb_no = $awbNo;
                }

                if ($value->rowstate == "8" /* DRS */) {
                    $drsDateTime = $value->create_date;
                    $value->courier_name = $courierName->courier_name . " [PT SAP] ";
                }

                if ($value->rowstate == "9") {
                    /* PARAMETER NAMA KURIR */
                    $value->courier_name = $courierName->courier_name . " [PT SAP] ";

                    // (pod bom) if pod_type=4, then hide next checkpoint (hidden after pod bom, e.g.:drs,pod)
                    if ($value->pod_type == 4 /* claim khusus */) {
                        $hiddenCheckpoint = TRUE;
                    }

                    if ($value->pod_status_delivered == "UNDELIVERED") {
                        $value->rowstate_name = "POD - UNDELIVERED";

                        $value->pod_photo_undelivered = 'https://client.coresyssap.com/photo/pod/JXFD01808891862-1-1-9';
                        $value->pod_signature_undelivered = 'https://client.coresyssap.com/photo/pod_signature/220113000003748';
                        $value->pod_photo_undelivered_multi = ['https://client.coresyssap.com/getimage/get_all_images?key=0&pod_no=POD60139965823.jpg&type=1'];
                    } else {
                        /**
                         * POD Delivered
                         */
                        $value->rowstate_name = "POD - " . $value->pod_status_delivered;

                        /**
                         * PHOTO BASE64
                         */
                        $value->pod_photo = 'https://client.coresyssap.com/photo/pod/JXFD01808891862-1-1-9';
                        $value->pod_signature = 'https://client.coresyssap.com/photo/pod_signature/220113000003748';
                        $value->pod_camera = 'https://client.coresyssap.com/photo/pod/JXFD01808891862-1-1-9'; // added 14 may 2019
                        $value->pod_photo_multi = ['https://client.coresyssap.com/getimage/get_all_images?key=0&pod_no=POD60139965823.jpg&type=1'];

                        if ($value->pod_status_delivered == "DELIVERED" && !empty($value->pod_photo_tmp)) {
                            // $value->pod_photo = $this->url_pod_photo.$value->awb_no;
                            $value->pod_photo = 'https://client.coresyssap.com/photo/pod/JXFD01808891862-1-1-9';
                            // $value->pod_camera = $this->url_pod_camera . $value->awb_no;
                            $value->pod_camera = 'https://client.coresyssap.com/photo/pod/JXFD01808891862-1-1-9';
                            // $value->pod_signature = $this->url_pod_signature . $value->awb_no;
                            $value->pod_signature = 'https://client.coresyssap.com/photo/pod_signature/220113000003748';
                        }
                    }
                }
                unset($value->pod_status_delivered);
                unset($value->tb_awb_reference_no);
                unset($value->tb_awb_awb_no);
                unset($value->pod_photo_tmp);
            }

            return $this->response($result, RestController::HTTP_OK);

        } catch (\Throwable $th) {
            return $this->response([
                "status" => "fails",
                "message" => strval($th)
            ]);
        }
    }
}
