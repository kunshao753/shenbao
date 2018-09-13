<?php
class ScoreStandard extends BASE_Controller{
    public function __construct(){
        parent::__construct();
        $this->load->model('Scorestandard_model','scorestandard_model');
    }

    public function getList(){
        $list = $this->scorestandard_model->get_data();
        var_dump($list);
    }

    public function add(){
        $this->display('standard/add.html');
    }

    public function edit(){
        $id = $this->input->get('id');
        //查询评分标准信息
        $score_standard_info = $this->scorestandard_model->fetch_row(array('id'=>$id));

        if(empty($score_standard_info)){
            return false;
        }
        $score_standard_info['type'] = json_decode($score_standard_info['type'],true);

        $this->assign('score_standard_info',$score_standard_info);
        $this->display('standard/edit.html');
    }

    public function addScoreStandard(){
        $name = $this->input->post('name');
        $standard = $this->input->post('standard');
        $reason = $this->input->post('reason');


        if(empty($name)){
            $this->ajax_return(array(),'评分名称不能为空',5000001);
        }

        if(empty($standard)){
            $this->ajax_return(array(),'评分项不能为空',5000002);
        }

        if(empty($reason)){
            $this->ajax_return(array(),'评分依据不能为空',5000002);
        }
        $standard_reason = array_combine($standard,$reason);

        $insert_data = array(
            'name' => $name,
            'type' => json_encode($standard_reason),
            'create_at' => date('Y-m-d H:i:s')
        );

        $res = $this->scorestandard_model->insert($insert_data);
        if($res){
            $this->ajax_return($res,'添加成功');
        }else{
            $this->ajax_return(array(),'添加失败',5000003);
        }

    }

    public function editScoreStandard(){
        $id = $this->input->post('id');
        $name = $this->input->post('name');
        $standard = $this->input->post('standard');
        $reason = $this->input->post('reason');

        if(empty($id)){
            $this->ajax_return(array(),'id不能为空',5000004);
        }

        if(empty($name)){
            $this->ajax_return(array(),'评分名称不能为空',5000005);
        }

        if(empty($standard)){
            $this->ajax_return(array(),'评分项不能为空',5000006);
        }

        if(empty($reason)){
            $this->ajax_return(array(),'评分项依据不能为空',5000006);
        }
        $standard_reason = array_combine($standard,$reason);
        $update_data = array(
            'name' => $name,
            'type' => json_encode($standard_reason),
        );

        $res = $this->scorestandard_model->update(array('id'=>$id),$update_data);
        if($res){
            $this->ajax_return($res,'修改成功');
        }else{
            $this->ajax_return(array(),'修改失败',5000007);
        }

    }

    public function delScoreStandard(){
        $id = $this->input->post('id');
        if(empty($id)){
            $this->ajax_return(array(),'id不能为空',5000008);
        }

        $update_data = array(
            'is_delete' => 1,
        );

        $res = $this->scorestandard_model->update(array('id'=>$id),$update_data);
        if($res){
            $this->ajax_return($res,'删除成功');
        }else{
            $this->ajax_return(array(),'删除失败',5000009);
        }

    }
}