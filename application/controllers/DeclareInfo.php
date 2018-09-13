<?php

/**
 * 评审
 * Class DeclareInfo
 */
class DeclareInfo extends BASE_Controller{
    public function __construct(){
        parent::__construct();
        $this->load->model('Corp_model','corp_model');
        $this->load->model('Project_model','project_model');
        $this->load->model('Projectphoto_model','projectphoto_model');
        $this->load->model('Projectteam_model','projectteam_model');
        $this->load->model('Result_model','result_model');
        $this->load->model('Distribute_model','distribute_model');
    }

    public function getList(){

        $review_status = $this->input->post('review_status');//评审状态
        $project_name = $this->input->post('project_name');
        $page = $this->input->post('page');
        $page_size = $this->input->post('$page_size');
        $offset = ($page - 1) * $page_size;

        $condition = array();
        if(!empty($review_status)){
            $condition[] = "review_status={$review_status}";
        }
        if(!empty($project_name)){
            $condition[] = "project_name like '%{$project_name}%'";
        }
        $condition[] = "audit_status in (1,3,4)";
        $where = implode(' and ',$condition);

        $data = $this->corp_model->get_list($where,'','',$offset,$page_size);
        var_dump($data);
    }

    public function getInfo(){
        $user_id = $this->input->get('user_id');
        $where = array('user_id' => $user_id);
        $corp_info = $this->corp_model->fetch_row($where);
        if(empty($corp_info)){
            return false;
        }
        if(!in_array($corp_info['audit_status'],array(1,3,4))){
            return false;
        }
        $config= $this->getCorpInfoConfig();
        $product_form = $config['productForm'];
        $product_type = $config['productType'];
        $help = $config['help'];
        if($corp_info){
            $help_array = explode(',', $corp_info['accept_help']);
            foreach ($help as $key=>$value){
                if(array_key_exists($key, array_flip($help_array))){
                    $help[$key]['show'] = 1;
                }
            }
        }
        $team_info = $this->projectteam_model->fetch_row($where);
        $project_info = $this->project_model->fetch_row($where);
        if ($project_info) {
            $product_formArray = json_decode($project_info['product_form_val'], true);
            foreach ($product_form as $key => $value) {
                if (array_key_exists($key, $product_formArray)) {
                    $product_form[$key]['text'] = $product_formArray[$key];
                    $product_form[$key]['show'] = 1;
                }
            }
        }
        $project_photo = $this->projectphoto_model->fetch_row($where);
        $assign = array(
            'corpInfo'=>$corp_info,
            'help'=>$help,
            'signupResouce' => $config['signupResouce'],
            'team_info'=>$team_info,
            'productType'=>$product_type,
            'productForm'=>$product_form,
            'projectInfo'=>!empty($project_info) ? $project_info : '',
            'projectPhoto'=>!empty($project_photo) ? $project_photo : '',
        );
        var_dump($assign);

    }

    //保存评审信息
    public function doDeclare(){
        $expert_id = $this->expert_info['id'];
        $expert_group_id = $this->expert_info['group_id'];
        $expert_name = $this->expert_info['name'];
        $user_id = $this->input->post('user_id');
        $user_name = $this->input->post('user_name');
        $project_id = $this->input->post('project_id');
        $project_name = $this->input->post('project_name');
        $product_type_score = $this->input->post('product_type_score');
        $product_type_reason = $this->input->post('product_type_reason');
        $product_form_score = $this->input->post('product_form_score');
        $product_form_reason = $this->input->post('product_form_reason');
        $registered_capital_score = $this->input->post('registered_capital_score');
        $registered_capital_reason = $this->input->post('registered_capital_reason');
        $product_user_score = $this->input->post('product_user_score');
        $product_user_reason = $this->input->post('product_user_reason');
        //查询结果表是否存在信息
        $where = array('user_id'=>$user_id,'expert_id'=>$expert_id);
        $res = $this->result_model->fetch_row($where);
        if($res){
            //更新
            $update_data = array(
                'product_type_score' => $product_type_score,
                'product_type_reason' => $product_type_reason,
                'product_form_score' => $product_form_score,
                'product_form_reason' => $product_form_reason,
                'registered_capital_score' => $registered_capital_score,
                'registered_capital_reason' => $registered_capital_reason,
                'product_user_score' => $product_user_score,
                'product_user_reason' => $product_user_reason,
            );
            $up_res = $this->result_model->update($update_data,$where);
            if($up_res){
                $this->ajax_return(array(),'保存成功');
            }
            $this->ajax_return(array(),'保存失败',400001);
        }
        $insert_data = array(
            'user_id' => $user_id,
            'user_name' => $user_name,
            'expert_id' => $expert_id,
            'expert_name' => $expert_name,
            'project_id' => $project_id,
            'project_name' => $project_name,
            'product_type_score' => $product_type_score,
            'product_type_reason' => $product_type_reason,
            'product_form_score' => $product_form_score,
            'product_form_reason' => $product_form_reason,
            'registered_capital_score' => $registered_capital_score,
            'registered_capital_reason' => $registered_capital_reason,
            'product_user_score' => $product_user_score,
            'product_user_reason' => $product_user_reason,
            'create_at' => date('Y-m-d H:i:s')
        );
        $insert_res = $this->result_model->insert($insert_data);
        if($insert_res){
            //更新评审状态
            $up_data = array('review_status' => 2);
            $up_where = array('user_id' => $user_id, 'group_id' => $expert_group_id);
            $up_dis_res = $this->distribute_model->update($up_data,$up_where);
            if($up_dis_res !== false){
                $this->ajax_return(array(),'保存成功');
            }
        }
        $this->ajax_return(array(),'保存失败',400002);
    }

    //分配专家组
    public function distribute(){
        $user_id = $this->input->post('user_id');
        $group_id = $this->input->post('group_id');

        if(empty($user_id) || empty($group_id)){
            $this->ajax_return(array(),'参数错误',300001);
        }

        $insert_data = array(
            'user_id' => $user_id,
            'group_id' => $group_id,
            'review_status' => 1,
            'create_at' => date('Y-m-d H:i:s')
        );
        $res = $this->distribute_model->insert($insert_data);
        if($res){
            $this->ajax_return($res,'分配成功!');
        }else{
            $this->ajax_return($res,'分配失败!',300002);
        }
    }
}