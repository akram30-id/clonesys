<?php 
defined('BASEPATH') or exit('No direct script access allowed');

class Shipment_m extends CI_Model
{
    function getAll()
    {
        $this->db->select('a.shipment_type_code, a.shipment_content_code, a.shipment_content_name');
        $this->db->from("tb_shipment_content AS a");
        return $this->db->get();
    }

    function getShipmentContentByTypeCode($typeCode)
    {
        $this->db->select('a.shipment_type_code, a.shipment_content_code, a.shipment_content_name');
        $this->db->from("tb_shipment_content AS a");
        $this->db->where("a.shipment_type_code", $typeCode);
        return $this->db->get();
    }
}


?>