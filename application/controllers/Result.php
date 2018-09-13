<?php
class Result extends BASE_Controller{
    public function __construct(){
        parent::__construct();
    }

    public function getList(){
        $condition = array();
        if(!$this->is_admin){
            $group_id = $this->expert_info['group_id'];
            //根据专家组id获取当前专家组的所有专家
            $expert_ids = $this->expert_model->fetch_all(array('group_id'=>$group_id),'id');
            $condition[] = "expert_id in ('" . implode(',',$expert_ids) . "')'";
        }
        $project_name = $this->input->get('project_name');
        $expert_name = $this->input->get('expert_name');
        $page = !empty($this->input->post('page')) ? $this->input->post('page') : 1;
        $page_size = !empty($this->input->post('page_size')) ? $this->input->post('page_size') : 20;
        $offset = ($page - 1) * $page_size;

        if(!empty($project_name)){
            $condition[] = "expert_name like '%{$expert_name}%'";
        }
        if(!empty($project_name)){
            $condition[] = "project_name like '%{$project_name}%'";
        }
        $order = "project_id asc";
        $where = implode(' and ',$condition);
        $res = $this->result_model->fetch_all($where,'',$order,$offset,$page_size);

    }
}