<?php
class ExpertGroup extends BASE_Controller{

    public function __construct(){
        parent::__construct();
        $this->load->model('Expertgroup_model','expertgroup_model');
    }

    public function getList(){

        $where = '';
        $group_name = !empty($this->input->post('group_name'));

        $page = $this->input->post('page');
        $page_size = $this->input->post('page_size');

        $page = !empty($page) ? $page : 1;
        $page_size = !empty($page_size) ? $page_size : 20;
        if(!empty($group_name)){
            $where .= "group_name like %{$group_name}%";
        }
        $offset = ($page - 1) * $page_size;
        $res = $this->expertgroup_model->get_list($where,'','',$offset,$page_size);
        $count = $this->expertgroup_model->fetch_count($where);

        $this->assign('group_name',$group_name);
        $this->assign('page',$page);
        $this->assign('count',$count);
        $this->assign('data',$res);

        $this->display('group/list.html');
    }

    public function getExpertGroupInfo(){

        $id = !empty($this->input->post('id'));
        if(empty($id)){
            $this->ajax_return(array(),'参数错误',200001);
        }
        $res = $this->expertgroup_model->fetch_row(array('id'=>$id));
        if($res){
            $this->ajax_return($res,'返回成功',200);
        }else{
            $this->ajax_return(array(),'查询数据为空',200000);
        }
    }

    public function addExpertGroup(){
        $group_name = $this->input->post('group_name');

        if(empty($group_name)){
            $this->ajax_return(array(),'专家组名称不能为空',2000008);
        }

        $info = $this->expertgroup_model->fetch_row(array('group_name'=>$group_name));
        if($info){
            $this->ajax_return(array(),'专家组名称重复',2000009);
        }

        $insert_data = array(
            'group_name' => $group_name,
            'time' => date('Y-m-d H:i:s')
        );
        $res = $this->expertgroup_model->insert($insert_data);
        if($res){
            $this->ajax_return($res,'添加成功');
        }
    }

    public function editExpertGroup()
    {
        $id = $this->input->post('id');
        $group_name = $this->input->post('group_name');

        if (empty($id)) {
            $this->ajax_return(array(), '参数错误', 2000008);
        }
        if (empty($group_name)) {
            $this->ajax_return(array(), '专家组名称不能为空', 2000008);
        }

        $info = $this->expertgroup_model->fetch_row(array('group_name' => $group_name));
        if ($info) {
            $this->ajax_return(array(), '专家组名称重复', 2000009);
        }

        $where = array(
            'id' => $id,
        );
        $update_data = array(
            'group_name' => $group_name,
            'time' => date('Y-m-d H:i:s')
        );
        $res = $this->expertgroup_model->update($update_data,$where);
        if ($res) {
            $this->ajax_return($res, '添加成功');
        }else{
            $this->ajax_return(array(), '添加失败',2000012);
        }
    }

    public function delExpertGroup(){
        $id = $this->input->post('id');
        if (empty($id)) {
            $this->ajax_return(array(), '参数错误', 2000008);
        }
        $where = array(
            'expert_group.id' => $id,
        );
        $list = $this->expertgroup_model->get_list($where);
        if($list){
            $this->ajax_return(array(), '专家组下有专家人员,不能删除', 2000010);
        }

        $res = $this->expertgroup_model->delete(array('id'=>$id));
        if($res){
            $this->ajax_return($res, '添加成功');
        }else{
            $this->ajax_return(array(), '删除失败', 2000013);
        }
    }
}