<?php 
defined('BASEPATH') or exit('No direct script access allowed');

class Cost_m extends CI_Model
{
    function getDistrict($districtCode)
    {
        return $this->db->get_where("tb_district", ["district_code" => $districtCode])->limit(1);
    }

    function getContract($customerCode)
    {
        $this->db->select("a.shipment_cost_id, a.price_flag, a.shtpc_discount, a.shtdc_discount, a.drgreg_discount, a.udrreg_discount, a.udrons_discount, a.udrsds_discount");
        $this->db->from("tb_contract AS a");
        $this->db->where("a.customer_code", $customerCode);
        $this->db->where("a.rowstate", "ACTIVE");
        $this->db->limit(1);
        return $this->db->get()->row();
    }

    function getPublishNew($shipmentCostId, $destination, $origin, $whereIN = [], $whereNOTIN = [])
    {
        $this->db->select("a.district_code AS origin_district_code");
        $this->db->select("c.shipment_cost_detail_id, c.origin_branch_code, c.cost, c.zone_code, c.service_type_code, c.destination_district_code, c.lead_time, c.shipment_cost_id");
        $this->db->select("(SELECT coverage_cod FROM tb_district WHERE district_code='" . $destination . "') AS coverageCOD");
        $this->db->select("c.coverage_area");
        $this->db->from("tb_district AS a");
        $this->db->join("tb_city AS b", "a.city_code=b.city_code");
        $this->db->join("tb_shipment_cost_detail_publish AS c", "c.origin_city_code=b.city_code_area");
        $this->db->where("c.shipment_cost_id", $shipmentCostId);
        $this->db->where("c.rowstate", 1);
        $this->db->where("a.district_code", $origin);
        $this->db->where("c.destination_district_code", $destination);

        if ($whereIN) {
            $this->db->where_in("c.service_type_code", $whereIN);
        }

        if ($whereNOTIN) {
            $this->db->where_not_in("c.service_type_code", $whereNOTIN);
        }

        return $this->db->get()->result();
    }

    function getPublishOld($shipmentCostId, $destination, $origin, $whereIN = [], $whereNOTIN = [])
    {
        $this->db->select("a.district_code AS origin_district_code");
        $this->db->select("c.shipment_cost_detail_id, c.origin_branch_code, c.cost, c.zone_code, c.service_type_code, c.destination_district_code, c.lead_time, c.shipment_cost_id");
        $this->db->select("(SELECT coverage_cod FROM tb_district WHERE district_code='" . $destination . "') AS coverageCOD");
        $this->db->select("c.coverage_area");
        $this->db->from("tb_district AS a");
        $this->db->join("tb_city AS b", "a.city_code=b.city_code");
        $this->db->join("tb_branch AS e", "b.branch_code=e.branch_code");
        $this->db->join("tb_shipment_cost_detail_publish AS c", "c.origin_branch_code=e.price_area_code");
        $this->db->where("c.shipment_cost_id", $shipmentCostId);
        $this->db->where("c.rowstate", 1);
        $this->db->where("a.district_code", $origin);
        $this->db->where("c.destination_district_code", $destination);

        if ($whereIN) {
            $this->db->where_in("c.service_type_code", $whereIN);
        }

        if ($whereNOTIN) {
            $this->db->where_not_in("c.service_type_code", $whereNOTIN);
        }

        return $this->db->get()->result();
    }

    function getProvinsiByDistrict($districtCode)
    {
        $this->db->select("b.provinsi_code");
        $this->db->from("tb_district AS a");
        $this->db->join("tb_city AS b", "b.city_code=a.city_code");
        $this->db->where("a.district_code", $districtCode);
        $this->db->limit(1);
        return $this->db->get()->row();
    }

    function getServiceTypeName($serviceTypeCode)
    {
        $service = $this->db->select("a.descriptions")->from("tb_service_type AS a")->where("a.service_type_code", $serviceTypeCode)->limit(1)->get()->row();
        if ($service) {
            return $service->descriptions;
        } else {
            return NULL;
        }
    }

    function getCustomer($customerCode)
    {
        return $this->db->get_where("tb_customer", ["customer_code" => $customerCode]);
    }

    function getPacking($packingTypeCode)
    {
        return $this->db->get_where("tb_packing_type", ["packing_type_code" => $packingTypeCode]);
    }

    function getInsurance($insuranceTypeCode)
    {
        return $this->db->get_where("tb_insurance_type", ["insurance_type_code" => $insuranceTypeCode]);
    }

    function getInsurancePercentage($customerCode)
    {
        return $this->db->get_where("tb_contract", ["customer_code" => $customerCode, "rowstate" => "ACTIVE"]);
    }

    function getContractPublish($customerCode, $flag)
    {
        return $this->db->get_where("tb_contract", ["customer_code" => $customerCode, "rowstate" => "ACTIVE", "price_flag" => "$flag"])->row();
    }

    function getPublish($origin, $destination, $shipmentCostId)
    {
        $this->db->select("c.shipment_cost_detail_id, c.origini_branch_code, a.district_code AS origin_district_code, c.cost, c.zone_code, c.min_kilo");
        $this->db->select("d.district_code AS destination_district_code, d.district_name AS destination_district_name, c.service_type_code, c.lead_time");
        $this->db->select("(SELECT coverage_code FROM tb_district WHERE district_code = '" . $destination . "') AS coverageCOD");
        $this->db->from("tb_district AS a");
        $this->db->join("tb_city AS b", "a.city_code=b.city_code");
        $this->db->join("tb_branch AS e", "b.branch_code=e.branch_code");
        $this->db->join("tb_shipment_cost_detail_publish AS c", "c.origin_branch_code=e.price_area_code");
        $this->db->join("tb_district AS d", "d.zone_code=c.zone_code");
        $this->db->where("a.district_code", $origin);
        $this->db->where("c.shipment_cost_id", $shipmentCostId);

        return $this->db->get();
    }

    function getSpecial($origin, $destination, $shipmentCostId)
    {
        $this->db->select("a.shipment_cost_detail_id, a.cost, a.origin_branch_code, a.service_type_code, a.lead_time");
        $this->db->select("(SELECT coverage_code FROM tb_district WHERE district_code = '" . $destination . "') AS coverageCOD");
        $this->db->select("a.zone_code AS destination_district_code");
        $this->db->from("tb_shipment_cost_detail AS a");
        $this->db->where("a.zone_code", $destination);
        $this->db->where("a.origin_branch_code", $origin);
        $this->db->where("a.shipment_cost_id", $shipmentCostId);
        
        return $this->db->get();
    }

    function getServiceDetail($serviceTypeCode)
    {
        return $this->db
            ->select("a.*, b.kilo_driver")
            ->from("tb_service_type AS a")
            ->join("tb_product AS b", ".product_code=b.product_code")
            ->where("a.rowstate", 1)
            ->where("a.service_type_code", $serviceTypeCode)
            ->get();
    }

    function minimumKilo($originProvinceCode, $destinationProvinceCode, $serviceTypeCode)
    {
        $this->db->select("a.origin_province_code, a.destination_province_code, a.min_kilo");
        $this->db->from("tb_shipment_cost_minimum_kilo AS a");
        $this->db->where("a.origin_province_code", $originProvinceCode);
        $this->db->where("a.destination_province_code", $destinationProvinceCode);
        $this->db->where("a.service_type_code", $serviceTypeCode);
        $this->db->where("a.rowstate", "1");
        $this->db->limit(1);
        return $this->db->get()->row();
    }

    function publishActive()
    {
        return $this->db
        ->select("shipment_cost_id, shipment_cost_name")
        ->from("tb_shipment_cost")
        ->where("shipment_cost_type", "1")
        ->where("price_flag", "1")
        ->order_by("shipment_cost_id", "1")
        ->limit(1)
        ->get();
    }
}
 