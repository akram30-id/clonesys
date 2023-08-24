<?php 
defined('BASEPATH') or exit('No direct script access allowed');

class Service_m extends CI_Model
{

    function getAll()
    {
        $this->db->select("service_type_code, service_type_name, descriptions AS service_desc");
        $this->db->from("tb_service_type");
        return $this->db->get();
    }

}


?>