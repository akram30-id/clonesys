<?php 
defined('BASEPATH') or exit('No direct script access allowed');

class District_m extends CI_Model
{

    function getAll(int $page)
    {
        $limit = 50;
        $this->db->select("td.city_code, td.district_code, td.district_name, td.zone_code, tp.provinsi_code, tc.city_name, tc.tlc_branch_code, tp.provinsi_name");
        $this->db->from("tb_district AS td");
        $this->db->join("tb_city AS tc", "td.city_code=tc.city_code");
        $this->db->join("tb_provinsi AS tp", "tp.provinsi_code=tc.provinsi_code", "inner");
        $this->db->limit($limit, $limit*$page);
        $query = $this->db->get();

        return $query;
    }

    function getSpecific($keyword)
    {
        $this->db->select("td.city_code, td.district_code, td.district_name, td.zone_code, tc.provinsi_code, tc.city_name, tc.tlc_branch_code, tp.provinsi_name");
        $this->db->from("tb_district AS td");
        $this->db->join("tb_city AS tc", "td.city_code=tc.city_code");
        $this->db->join("tb_provinsi AS tp", "tp.provinsi_code=tc.provinsi_code", "inner");
        $this->db->like("td.district_name", $keyword);
        $query = $this->db->get();

        return $query;
    }

}


?>