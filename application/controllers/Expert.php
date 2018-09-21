<?php
class Expert extends BASE_Controller{

    public function __construct(){
        parent::__construct();
        if(!$this->is_admin){
            header('location:/Index/getList');
            exit;
        }
        $this->load->helper(array('form', 'url'));
    }

    //专家列表
    public function getList(){
        $condition = array();
        $name = $this->input->get('name');
        $page = !empty($this->input->get('page')) ? $this->input->get('page') : 1;
        $page_size = !empty($this->input->get('page_size')) ? $this->input->get('page_size') : 10;

        if(!empty($name)){
            $condition[] = "name like '%{$name}%'";
        }
        $condition[] = "is_delete=0";
        $condition[] = "permission != 1";

        $where = implode(" and ",$condition);
        $offset = ($page - 1) * $page_size;
        $expert_info = $this->expert_model->fetch_all($where,'','id desc','',$offset,$page_size);
        $count = $this->expert_model->fetch_count($where);

        if(!empty($expert_info)){
            foreach($expert_info as $key => $value){
                $group_id = $value['group_id'];
                $g_where = array(
                    'id' => $group_id,
                    'is_delete' => 0
                );
                $expert_info[$key]['expert_group_name'] = '';
                $group_info = $this->expertgroup_model->fetch_row($g_where);
                if(!empty($group_info)){
                    $expert_info[$key]['expert_group_name'] = $group_info['group_name'];
                }
            }
        }
        //var_dump($expert_info);
        //var_dump($count);
        $pages_list = $this->dividePage('/expert/getList?name='.$name,$page_size,$count);
        $this->assign('offset',$offset);
        $this->assign('data',$expert_info);
        $this->assign('pages_list',$pages_list);

        $this->assign('name',$name);
        $this->assign('p','expert');

        $this->display('expert/list.html');
    }

    public function add(){
        $expert_group_data = array();
        $expert_group_res = $this->expertgroup_model->fetch_all(array('is_delete'=>0));
        if(!empty($expert_group_res)){
            foreach($expert_group_res as $value){
                $expert_group_data[$value['id']] = $value['group_name'];
            }
        }
        $this->assign('expert_group_data',$expert_group_data);
        $this->assign('p','expert');

        $this->display('expert/add.html');
    }

    public function edit(){
        $id = $this->input->get('id');
        $expert_info = $this->expert_model->fetch_row(array('id'=>$id,'is_delete'=>0));
        if(empty($expert_info)){
            return false;
        }
        $expert_group_data = array();
        $expert_group_res = $this->expertgroup_model->fetch_all(array('is_delete'=>0));
        if(!empty($expert_group_res)){
            foreach($expert_group_res as $value){
                $expert_group_data[$value['id']] = $value['group_name'];
            }
        }
        $this->assign('expert_group_data',$expert_group_data);
        $this->assign('expert_info',$expert_info);
        $this->assign('p','expert');

        $this->display('expert/edit.html');
    }

    public function getExpertInfo(){

        $id = !empty($this->input->post('id'));
        if(empty($id)){
            $this->ajax_return(array(),'参数错误',200001);
        }
        $res = $this->expert_model->fetch_row(array('id'=>$id));
        if($res){
            $this->ajax_return($res,'返回成功');
        }else{
            $this->ajax_return(array(),'查询数据为空',200000);
        }
    }

    //新增
    public function addExpert(){
        $name = trim($this->input->post('name'));
        $account = trim($this->input->post('account'));
        $password = trim($this->input->post('password'));
        $group_id = $this->input->post('group_id');
        $sign_pic = $this->input->post('sign_pic');

        if(empty($name)){
            $this->ajax_return('姓名不能为空',2000011);
        }
        if(empty($account)){
            $this->ajax_return('账号不能为空',2000012);
        }
        if(empty($password)){
            $this->ajax_return('密码不能为空',2000013);
        }
        if(mb_strlen($name) > 10){
            $this->ajax_return('姓名长度不能超过10个字符',2000111);
        }
        if(strlen($account) > 25){
            $this->ajax_return('账号长度不能超过25个字符',2000112);
        }
        if(strlen($password) > 25){
            $this->ajax_return('密码长度不能超过25个字符',2000113);
        }
        $insert_data = array(
            'name' => $name,
            'account' => $account,
            'password' => $password,
            'md5_password' => md5($password),
            'group_id' => $group_id,
            'sign_pic' => !empty($sign_pic) ? $sign_pic : '',
            'create_at' => date('Y-m-d H:i:s')
        );
        $res = $this->expert_model->insert($insert_data);
        if($res){
            $this->ajax_return('添加成功');
        }
    }

    //修改
    public function editExpert(){
        $id = $this->input->post('id');
        $name = trim($this->input->post('name'));
        $account = trim($this->input->post('account'));
        $password = trim($this->input->post('password'));
        $group_id = $this->input->post('group_id');
        $sign_pic = $this->input->post('sign_pic');

        if(empty($id)){
            $this->ajax_return(array(),'参数错误',200001);
        }
        if(empty($name)){
            $this->ajax_return(array(),'姓名不能为空',200002);
        }
        if(empty($account)){
            $this->ajax_return(array(),'账号不能为空',200003);
        }
        if(empty($password)){
            $this->ajax_return(array(),'密码不能为空',200004);
        }
        if(mb_strlen($name) > 10){
            $this->ajax_return('姓名长度不能超过10个字符',2000111);
        }
        if(strlen($account) > 25){
            $this->ajax_return('账号长度不能超过25个字符',2000112);
        }
        if(strlen($password) > 25){
            $this->ajax_return('密码长度不能超过25个字符',2000113);
        }

        //if(empty($group_id)){
        //    $this->ajax_return(array(),'请选择专家组',200006);
        //}

        $where = array(
            'id' => $id,
        );
        $update_data = array(
            'name' => $name,
            'account' => $account,
            'password' => $password,
            'md5_password' => md5($password),
            'group_id' => $group_id,
            'sign_pic' => !empty($sign_pic) ? $sign_pic : '',
        );
        $res = $this->expert_model->update($update_data,$where);
        if($res !== false){
            $this->ajax_return($res,'修改成功');
        }
    }

    public function do_upload(){
        $config['upload_path']      = './resources/uploads/';
        $config['allowed_types']    = 'gif|jpg|jpeg|png|pdf|bmp';
        $config['max_size']         = 5 * 1024 * 1024;
        $config['encrypt_name'] = TRUE;
        //$config['max_width']        = 1024;
        //$config['max_height']       = 768;

        $this->load->library('upload', $config);

        if ( ! $this->upload->do_upload('file')) {
            $error = array('error' => $this->upload->display_errors());
            $this->ajax_return(array(),$error,3000011);
        } else {
            $data = array('upload_data' => $this->upload->data());
            $this->ajax_return($data['upload_data']['file_name'],'上传成功');
        }
    }
}