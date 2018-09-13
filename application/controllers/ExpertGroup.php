<?php
class ExpertGroup extends BASE_Controller{

    public function __construct(){
        parent::__construct();
        $this->load->model('Expertgroup_model','expertgroup_model');
        $this->load->model('Scorestandard_model','scorestandard_model');
    }

    public function add(){
        //查询评分名称
        $standard_data = array();
        $standard_res = $this->scorestandard_model->get_list();
        foreach($standard_res as $value){
            $standard_data[$value['id']] = $value['name'];
        }
        $this->assign('standard_data',$standard_data);
        $this->display('group/add.html');
    }

    public function edit(){
        $id = $this->input->get('id');
        //查询专家组信息
        $expert_group_info = $this->expertgroup_model->fetch_row(array('id'=>$id));
        if(empty($expert_group_info)){
            return false;
        }
        //查询评分名称
        $standard_data = array();
        $standard_res = $this->scorestandard_model->get_data();
        foreach($standard_res as $value){
            $standard_data[$value['id']] = $value['name'];
        }
        $this->assign('standard_data',$standard_data);
        $this->assign('expert_group_info',$expert_group_info);
        $this->display('group/edit.html');
    }
    //获取专家组列表
    public function getList(){

        $group_name = !empty($this->input->post('group_name'));

        $page = $this->input->post('page');
        $page_size = $this->input->post('page_size');

        $page = !empty($page) ? $page : 1;
        $page_size = !empty($page_size) ? $page_size : 20;
        if(!empty($group_name)){
            $condition[] = "group_name like %{$group_name}%";
        }
        $condition[] = "is_delete=0";
        $where = implode(" and ",$condition);
        $offset = ($page - 1) * $page_size;
        $res = $this->expertgroup_model->get_list($where,'','',$offset,$page_size);
        $count = $this->expertgroup_model->fetch_count($where);

        $this->assign('group_name',$group_name);
        $this->assign('page',$page);
        $this->assign('count',$count);
        $this->assign('data',$res);

        $this->display('group/list.html');
    }

    //获取单个专家组信息
    public function getExpertGroupInfo(){

        $id = !empty($this->input->post('id'));
        if(empty($id)){
            $this->ajax_return(array(),'参数错误',200001);
        }
        $res = $this->expertgroup_model->fetch_row(array('id'=>$id,'is_delete'=>0));
        if($res){
            $this->ajax_return($res,'返回成功');
        }else{
            $this->ajax_return(array(),'查询数据为空',200000);
        }
    }

    //添加专家组
    public function addExpertGroup(){
        $group_name = trim($this->input->post('group_name'));
        $score_standard_id = $this->input->post('score_standard_id');

        if(empty($group_name)){
            $this->ajax_return(array(),'专家组名称不能为空',2000008);
        }

        $info = $this->expertgroup_model->fetch_row(array('group_name'=>$group_name));
        if($info){
            $this->ajax_return(array(),'专家组名称重复',2000009);
        }

        $insert_data = array(
            'group_name' => $group_name,
            'score_standard_id' => !empty($score_standard_id) ? $score_standard_id : 0,
            'create_at' => date('Y-m-d H:i:s')
        );
        $res = $this->expertgroup_model->insert($insert_data);
        if($res){
            $this->ajax_return($res,'添加成功');
        }
    }

    //编辑专家组信息
    public function editExpertGroup()
    {
        $id = $this->input->post('id');
        $group_name = $this->input->post('group_name');
        $score_standard_id = $this->input->post('score_standard_id');

        if (empty($id)) {
            $this->ajax_return(array(), '参数错误', 2000008);
        }
        if (empty($group_name)) {
            $this->ajax_return(array(), '专家组名称不能为空', 2000008);
        }

        $info = $this->expertgroup_model->fetch_row(array('group_name' => $group_name,'is_delete'=>0));
        if ($info) {
            $this->ajax_return(array(), '专家组名称重复', 2000009);
        }

        $where = array(
            'id' => $id,
        );
        $update_data = array(
            'group_name' => $group_name,
            'score_standard_id' => !empty($score_standard_id) ? $score_standard_id : 0,
        );
        $res = $this->expertgroup_model->update($update_data,$where);
        if ($res) {
            $this->ajax_return($res, '修改成功');
        }else{
            $this->ajax_return(array(), '修改失败',2000012);
        }
    }

    //删除专家组
    public function delExpertGroup(){
        $id = $this->input->post('id');
        if (empty($id)) {
            $this->ajax_return(array(), '参数错误', 2000008);
        }
        $where = array(
            'group_id' => $id,
        );
        $list = $this->expert_model->get_list($where);
        if($list){
            $this->ajax_return(array(), '专家组下有专家人员,不能删除', 2000010);
        }

        $res = $this->expertgroup_model->update(array('is_delete'=>1),array('id'=>$id));
        if($res){
            $this->ajax_return($res, '添加成功');
        }else{
            $this->ajax_return(array(), '删除失败', 2000013);
        }
    }
}