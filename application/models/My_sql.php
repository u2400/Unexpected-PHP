<?php
class My_sql extends CI_Model
{
    function __construct()
    {
        parent::__construct();
    	$this->load->database();
    }

 	public function free_sql($sql,$data = array())
    {
        $query = $this->db->query($sql,$data);
        return $query;
    }
}