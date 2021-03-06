<?php
class ExpertGroup extends BASE_Controller{

    public function __construct(){
        parent::__construct();
        if(!$this->is_admin){
            header('location:/Index/getList');
            exit;
        }
        $this->load->model('Expertgroup_model','expertgroup_model');
        $this->load->model('Scorestandard_model','scorestandard_model');
        $this->load->model('Distribute_model','distribute_model');
    }

    public function add(){
        //查询评分名称
        $standard_data = array();
        $standard_res = $this->scorestandard_model->fetch_all(array('is_delete'=>0));
        foreach($standard_res as $value){
            $standard_data[$value['id']] = $value['name'];
        }
        $this->assign('standard_data',$standard_data);
        $this->assign('p','group');

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
        $standard_res = $this->scorestandard_model->fetch_all(array('is_delete'=>0));
        foreach($standard_res as $value){
            $standard_data[$value['id']] = $value['name'];
        }
        $this->assign('standard_data',$standard_data);
        $this->assign('expert_group_info',$expert_group_info);
        $this->assign('p','group');

        $this->display('group/edit.html');
    }
    //获取专家组列表
    public function getList(){

        $condition = array();
        $group_name = $this->input->get('group_name');
        $page = !empty($this->input->get('page')) ? $this->input->get('page') : 1;
        $page_size = !empty($this->input->get('page_size')) ? $this->input->get('page_size') : 10;

        if(!empty($group_name)){
            $condition[] = "group_name like '%{$group_name}%'";
        }
        $condition[] = "is_delete=0";

        $where = implode(" and ",$condition);
        $offset = ($page - 1) * $page_size;
        $expert_group_info = $this->expertgroup_model->fetch_all($where,'','id desc','',$offset,$page_size);
        if(!empty($expert_group_info)){
            foreach($expert_group_info as $key => $value){
                //获取评分信息
                $score_standard_id = $value['score_standard_id'];
                $ss_where = array(
                    'id' => $score_standard_id,
                    'is_delete' => 0
                );
                $expert_group_info[$key]['standard_info'] = '';
                if($score_standard_id != 0){
                    $standard_info = $this->scorestandard_model->fetch_row($ss_where);
                    $expert_group_info[$key]['standard_info'] = $standard_info['name'];
                }
                //获取专家信息
                $group_id = $value['id'];
                $ex_where = array(
                    'group_id' => $group_id,
                    'is_delete' => 0
                );
                $expert_group_info[$key]['expert_info']  = '';
                $ex_info = $this->expert_model->fetch_all($ex_where);
                $expert_name = array();
                if(!empty($ex_info)){
                    foreach($ex_info as $value1){
                        $expert_name[] = $value1['name'];
                    }
                    $expert_group_info[$key]['expert_info'] = implode('、',$expert_name);
                }

            }
        }
        $count = $this->expertgroup_model->fetch_count($where);

        //var_dump($count);
        //var_dump($expert_group_info);

        $pages_list = $this->dividePage('/expertGroup/getList?group_name='.$group_name,$page_size,$count);
        $this->assign('offset',$offset);
        $this->assign('data',$expert_group_info);
        $this->assign('pages_list',$pages_list);
        $this->assign('group_name',$group_name);
        $this->assign('p','group');

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
        if(mb_strlen($group_name) > 20){
            $this->ajax_return(array(),'专家组名称字符超长',2000018);
        }

        $info = $this->expertgroup_model->fetch_row(array('group_name'=>$group_name,'is_delete'=>0));
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
        if(mb_strlen($group_name) > 20){
            $this->ajax_return(array(),'专家组名称不能为空',2000018);
        }

        $where_check = "id != $id and group_name='$group_name' and is_delete=0";
        $info = $this->expertgroup_model->fetch_row($where_check);
        if ($info) {
            $this->ajax_return(array(), '专家组名称重复', 2000009);
        }

        $check_res = $this->distribute_model->fetch_row(array('group_id'=>$id));
        if($check_res){
            $this->ajax_return(array(), '专家组已关联项目,不能修改', 2000009);
        }

        $where = array(
            'id' => $id,
        );
        $update_data = array(
            'group_name' => $group_name,
            'score_standard_id' => !empty($score_standard_id) ? $score_standard_id : 0,
        );
        $res = $this->expertgroup_model->update($update_data,$where);
        if ($res !== false) {
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
            'is_delete' => 0,
        );
        $list = $this->expert_model->fetch_all($where);
        if(!empty($list)){
            $this->ajax_return(array(), '专家组下有专家人员,不能删除', 2000010);
        }

        $dis_res = $this->distribute_model->fetch_row(array('group_id' => $id));
        if(!empty($dis_res)){
            $this->ajax_return(array(), '专家组已被分配到项目,不能删除', 2000010);
        }

        $res = $this->expertgroup_model->update(array('is_delete'=>1),array('id'=>$id));
        if($res){
            $this->ajax_return($res, '删除成功');
        }else{
            $this->ajax_return(array(), '删除失败', 2000013);
        }
    }
}