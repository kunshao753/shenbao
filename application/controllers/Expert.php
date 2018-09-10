<?php
class Expert extends BASE_Controller{

    public function __construct(){
        parent::__construct();
    }

    public function getList(){
        $where = '';
        $name = !empty($this->input->post('name'));
        $page = $this->input->post('page');
        $page_size = $this->input->post('page_size');

        $page = !empty($page) ? $page : 1;
        $page_size = !empty($page_size) ? $page_size : 20;
        if(!empty($name)){
            $where .= "name like %{$name}%";
        }
        $offset = ($page - 1) * $page_size;
        $res = $this->expert_model->get_list($where,'','',$offset,$page_size);
        $count = $this->expert_model->fetch_count($where);

        $this->assign('name',$name);
        $this->assign('page',$page);
        $this->assign('count',$count);
        $this->assign('data',$res);

        $this->display('expert/list.html');
    }

    public function getExpertInfo(){

        $id = !empty($this->input->post('id'));
        if(empty($id)){
            $this->ajax_return(array(),'参数错误',200001);
        }
        $res = $this->expert_model->fetch_row(array('id'=>$id));
        if($res){
            $this->ajax_return($res,'返回成功',200);
        }else{
            $this->ajax_return(array(),'查询数据为空',200000);
        }
    }

    public function addExpert(){
        $name = $this->input->post('name');
        $account = $this->input->post('account');
        $password = md5($this->input->post('password'));
        $repassword = md5($this->input->post('password'));
        $group_id = md5($this->input->post('group_id'));

        if(empty($name)){
            $this->ajax_return('姓名不能为空');
        }
        if(empty($account)){
            $this->ajax_return('账号不能为空');
        }
        if(empty($password) || empty($repassword)){
            $this->ajax_return('密码不能为空');
        }
        if($password !== $repassword){
            $this->ajax_return('两次输入的密码不同');
        }

        $insert_data = array(
            'name' => $name,
            'password' => $password,
            'group_id' => $group_id,
            'time' => date('Y-m-d H:i:s')
        );
        $res = $this->expert_model->insert($insert_data);
        if($res){
            $this->ajax_return('添加成功');
        }
    }

    public function editExpert(){
        $id = $this->input->post('id');
        $name = $this->input->post('name');
        $account = $this->input->post('account');
        $password = md5($this->input->post('password'));
        $repassword = md5($this->input->post('password'));
        $group_id = md5($this->input->post('group_id'));

        if(empty($id)){
            $this->ajax_return(array(),'参数错误',200001);
        }
        if(empty($name)){
            $this->ajax_return(array(),'姓名不能为空',200002);
        }
        if(empty($account)){
            $this->ajax_return(array(),'账号不能为空',200003);
        }
        if(empty($password) || empty($repassword)){
            $this->ajax_return(array(),'密码不能为空',200004);
        }
        if($password !== $repassword){
            $this->ajax_return(array(),'两次输入的密码不同',200005);
        }
        //if(empty($group_id)){
        //    $this->ajax_return(array(),'请选择专家组',200006);
        //}

        $where = array(
            'id' => $id,
        );
        $update_data = array(
            'name' => $name,
            'password' => $password,
            'group_id' => $group_id,
        );
        $res = $this->expert_model->update($update_data,$where);
        if($res){
            $this->ajax_return($res,'修改成功',200);
        }
    }
}