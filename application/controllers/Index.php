<?php
class Index extends BASE_Controller{

    public function __construct(){
        parent::__construct();
    }

    public function index(){

        $res = $this->expert_model->get_list();
        $this->ajax_return($res);
    }

    public function getRuleInfo(){

        $res = $this->expert_model->get_info();
        $this->ajax_return($res);
    }

    public function addRule(){

    }
}