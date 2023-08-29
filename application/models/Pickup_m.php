<?php

use PHPUnit\Framework\MockObject\Stub\ReturnReference;

defined("BASEPATH") or exit("NO DIRECT SCRIPT IS ALLOWED");

class Pickup_m extends CI_Model
{
    function checkReferenceNo($refNO)
    {
        return $this->db->select("reference_no")->from("tb_awb")->where("reference_no", str_replace(" ", "", $refNO))->get()->row();
    }

    function checkAWBNo($no)
    {
        return $this->db->select("awb_no")->from("tb_awb")->where("awb_no", str_replace(" ", "", $no))->get()->row();
    }

    function checkProductCode($serviceTypeCode)
    {
        return $this->db->select("product_code, service_type_code")->from("tb_service_type")->where("service_type_code", $serviceTypeCode)->get()->row();
    }

    function getDistrictMappingByName($districtName, $cityName, $provinceName, $districtMappingCode)
    {
        return $this->db->select("district_code, mapping_api_type")
                        ->from("tb_district_mapping_api")
                        ->where("district_mapping_code", $districtMappingCode)
                        ->where("customer_district_name", $districtName)
                        ->where("customer_city_name", $cityName)
                        ->where("customer_provinsi_name", $provinceName)
                        ->get()->row();
    }

    function getDistrictMappingByCode($districtCode, $districtMappingCode)
    {
        return $this->db->select("district_code, mapping_api_type")
                        ->from("tb_district_mapping_api")
                        ->where("customer_district_code", $districtCode)
                        ->where("district_mapping_code", $districtMappingCode)
                        ->get()->row();
    }

    function checkDistrict($districtCode)
    {
        return $this->db->select("*")
                        ->from("tb_district AS a, tb_city AS b")
                        ->where("a.city_code=b.city_code")
                        ->where("a.district_code", $districtCode)
                        ->get()->row();
    }

    function getCustomer($customerCode)
    {
        return $this->db->select("customer_name, flag_new_awb, flag_photo, flag_signature, flag_sms, flag_sms_incoming, flag_sms_pod, flag_api, cod_flag, rowstate, flag_pickup_forward_origin")
                        ->from("tb_customer")
                        ->where("customer_code", $customerCode)
                        ->limit(1)->get()->row();
    }

    function fixSellerDistrict($apiCode, $pickupName)
    {
        return $this->db->select("fix_seller_district_code")
                        ->from("tb_api_fix_seller_district_code")
                        ->where("api_code", $apiCode)
                        ->where("pickup_name", $pickupName)
                        ->where("is_active", 1)
                        ->limit(1)->get()->row();
    }

    function insertPickup($data)
    {
        $this->db->insert("tb_pickup", $data);
    }

    function insertAWB($data)
    {
        $this->db->insert("tb_awb", $data);
    }

    function insertPickupAWB($data)
    {
        $this->db->insert("tb_pickup_awb", $data);
    }

    function updatePickup($pickupId, $pickupNo)
    {
        $this->db->where("pickup_id", $pickupId);
        $this->db->update("tb_pickup", ["pickup_no" => $pickupNo]);
    }

    function updatePickupWithNewAWB($pickupId, $pickupNo, $awbNo)
    {
        $this->db->where("pickup_id", $pickupId);
        $this->db->update("tb_pickup", ["pickup_no" => $pickupNo, "awb_no" => $awbNo]);
    }

    function updateAWB($awbNo, $referenceNo, $data)
    {
        $this->db->where("awb_booking_no", $awbNo);
        $this->db->where("reference_no", $referenceNo);
        $this->db->update("tb_awb", $data);
    }

    function updateAWBAutoGenerate($id, $awbNo, $awbParentNo)
    {
        $this->db->where("awb_id", $id);
        $this->db->update("tb_awb", ["awb_no" => $awbNo, "awb_parent_no" => $awbParentNo]);
    }

    function checkAWBPickupNo($awbNo)
    {
        $this->db->select("a.pickup_no");
        $this->db->select("b.awb_no");
        $this->db->from("tb_pickup AS a");
        $this->db->join("tb_awb AS b", "b.pickup_no=a.pickup_no");
        $this->db->where("b.awb_no", $awbNo);
        return $this->db->get()->row();
    }

    function getAWBDetail($awbNo)
    {
        $this->db->select("awb_no, pod_no, rowstate, origin_branch_code, origin_counter_code");
        $this->db->from("tb_awb");
        $this->db->where("awb_no", $awbNo);
        return $this->db->get()->row();
    }

    function updatePickupToVoid($data, $pickupNo)
    {
        $this->db->where("pickup_no", $pickupNo);
        $this->db->update("tb_pickup", $data);
    }

    function insertPickupDetail($data)
    {
        $this->db->insert("tb_pickup_detail", $data);
    }

    function updateAWBToVoid($awbNo, $data)
    {
        $this->db->where("awb_no", $awbNo);
        $this->db->update("tb_awb", $data);
    }

    function insertTracking($data)
    {
        $this->db->insert("tb_tracking", $data);
    }
}

?>