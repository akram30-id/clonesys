<?php

use PhpParser\Lexer\TokenEmulator\FlexibleDocStringEmulator;

defined("BASEPATH") or exit("NO DIRECT SCRIPT IS ALLOWED");

class Tracking_m extends CI_Model
{
    function cekApiKey($apiKey)
    {
        $this->db->select("api_key, api_code, version");
        $this->db->from("tb_api_keys");
        $this->db->where("api_key", $apiKey);
        return $this->db->get()->row();
    }

    function getAWBByRef($refNo)
    {
        $this->db->select("awb_no, reference_no");
        $this->db->from("tb_awb");
        $this->db->where("reference_no", $refNo);
        $this->db->limit(1);
        return $this->db->get()->row();
    }

    function getAWBTracable($awbNo)
    {
        return $this->db->select("b.is_tracking")->from("tb_awb AS a")->join("tb_customer AS b", "b.customer_code=a.customer_code")->where("awb_no", $awbNo)->get()->row();
    }

    function showDataTracking($awbNo, $isCheckPointConfirmation, $listRowstate = [])
    {
        $this->db->select(
            "a.id AS tracking_id,
            c.rowstate_name,
            e.pod_status_delivered,
            f.pod_status_name,
            e.pod_status_code,
            a.tracking_doc_no,
            a.reference_no,
            a.create_date,
            d.counter_name,
            a.user_inp,
            a.description,
            h.district_name AS origin,
            i.district_name AS destination,
            b.receiver_name,
            b.shipper_name,
            b.koli,
            b.kilo,
            (CASE WHEN b.packing_cost IS NOT NULL THEN b.packing_cost ELSE 0 END) AS packing_cost,
            (CASE WHEN b.charge_type_cost IS NOT NULL THEN b.charge_type_cost ELSE 0 END) AS insurance_cost,
            c.rowstate,
            b.reference_no AS tb_awb_reference_no,
            b.awb_no AS tb_awb_awb_no,
            (CASE WHEN c.rowstate=9 THEN e.receiver_name ELSE '' END) AS pod_receiver_name,
            (CASE WHEN c.rowstate=9 THEN g.relationship_receiver_name ELSE '' END) AS pod_relation_name,
            e.latitude,
            e.longitude,
            e.photo AS pod_photo_tmp,
            e.pod_type,
            j.branch_name AS current_branch_name
            ");
            $this->db->order_by("a.id", "ASC");
            $this->db->from("tb_tracking AS a");
            $this->db->join("tb_awb AS b", "b.awb_no=a.awb_no");
            $this->db->join("tb_rowstate AS c", "a.tracking_process=c.rowstate");
            $this->db->join("tb_counter AS d", "a.counter_code=d.counter_code", "left");
            $this->db->join("tb_pod AS e", "a.reference_no=e.pod_no", "left");
            $this->db->join("tb_pod_status AS f", "f.pod_status_code=e.pod_status_code", "left");
            $this->db->join("tb_relationship_receiver AS g", "g.relationship_receiver_code=e.relationship_receiver_code", "left");
            $this->db->join("tb_district AS h", "h.district_code=b.origin_district_code", "left");
            $this->db->join("tb_district AS i", "i.district_code=b.destination_district_code", "left");
            $this->db->join("tb_branch AS j", "j.branch_code=a.branch_code");
            $this->db->where("a.awb_no", $awbNo);

            if ($isCheckPointConfirmation) {
                $listRowstate[] = 9.80;
            }

            $this->db->where_in("c.rowstate", $listRowstate);
            $this->db->where("b.rowstate != '50'", NULL, FALSE);
            
            return $this->db->get()->result();
    }

    function getAWBDetail($awbNo)
    {
        $this->db->select(
            "b.tracking_id,
            c.rowstate_name,
            e.pod_status_delivered,
            (CASE WHEN b.tracking_doc_no is null THEN a.awb_no ELSE b.tracking_doc_no END) AS tracking_doc_no,
            a.reference_no,
            b.create_date,
            d.counter_name,
            b.user_inp,
            h.district_name AS origin,
            i.district_name AS destination,
            (CASE WHEN c.rowstate=9 THEN e.receiver_name ELSE '' END) AS pod_receiver_name,
            (CASE WHEN c.rowstate=9 THEN g.relationship_receiver_name ELSE '' END) AS pod_relation_name,
            a.receiver_name,
            a.awb_no
            ");

        $this->db->from("tb_awb AS a");
        $this->db->join("tb_tracking AS b", "a.awb_no=b.awb_no", "left");
        $this->db->join("tb_rowstate AS c", "a.rowstate=c.rowstate");
        $this->db->join("tb_counter AS d", "b.counter_code=d.counter_code", "left");
        $this->db->join("tb_pod AS e", "b.reference_no=e.pod_no", "left");
        $this->db->join("tb_pod_status AS f", "f.pod_status_code=e.pod_status_code", "left");
        $this->db->join("tb_relationship_receiver AS g", "g.relationship_receiver_code=e.relationship_receiver_code", "left");
        $this->db->join("tb_district AS h", "h.district_code=a.origin_district_code", "left");
        $this->db->join("tb_district AS i", "i.district_code=a.destination_district_code", "left");
        $this->db->where("a.awb_no", $awbNo);
        $this->db->where("(c.rowstate < 10 OR c.rowstate=21)", NULL, FALSE);
        $this->db->where("(a.rowstate != 20 AND a.rowstate = 21)", NULL, FALSE);
        $this->db->where("a.rowstate != 20 AND a.rowstate != 50", NULL, FALSE);
        $this->db->order_by("b.id", "ASC");
        return $this->db->get()->result();
    }

    function getCourier($deliveryNo)
    {
        $this->db->select("courier_name");
        $this->db->from("tb_delivery AS b");
        $this->db->join("tb_courier AS a", "b.courier_code=a.courier_code");
        $this->db->where("b.delivery_no", $deliveryNo);
        return $this->db->get()->row();
    }
}


?>