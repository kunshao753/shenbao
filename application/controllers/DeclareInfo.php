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
        $this->load->model('Distributeresult_model','distributeresult_model');
        $this->load->model('Distribute_model','distribute_model');
        $this->load->model('Scorestandard_model','scorestandard_model');
    }

    //评审详情页面
    public function getInfo(){
        $user_id = $this->input->get('user_id');
        $where = array('user_id' => $user_id);
        $corp_info = $this->corp_model->fetch_row($where);
        if(empty($corp_info)){
            return false;
        }
        if(!in_array($corp_info['audit_status'],array(1,3,4,5))){
            return false;
        }

        //专家组信息
        $expert_group_data = array();
        $expert_group_res = $this->expertgroup_model->fetch_all(array('is_delete'=>0));
        if(!empty($expert_group_res)){
            foreach($expert_group_res as $value){
                $expert_group_data[$value['id']] = $value['group_name'];
            }
        }

        $config= $this->getCorpInfoConfig();
        $product_form = $config['productForm'];
        $product_type = $config['productType'];
        $product_user = $config['productUser'];
        $help = $config['help'];

        //项目信息
        $corp_info['signup_resouce'] = $config['signupResouce'][$corp_info['signup_resouce']];
        $corp_info['contestant_identity'] = $config['contestantIdentity'][$corp_info['contestant_identity']];
        $accept_help_arr = explode(',',$corp_info['accept_help']);
        if(!empty($accept_help_arr)){
            foreach($accept_help_arr as $value){
                $corp_info_accect_help[] = $help[$value];
            }
            $corp_info['accect_help'] = implode('、',$corp_info_accect_help);
        }

        //团队信息
        $team_info = $this->projectteam_model->fetch_row($where);

        //项目信息
        $project_info = $this->project_model->fetch_row($where);
        $product_form_arr = json_decode($project_info['product_form_val'], true);

        if(!empty($product_form_arr)) {
            foreach ($product_form_arr as $key => $value) {
                $project_info_product_form_val[]= $product_form[$key];
            }
            $project_info['product_form_val'] = implode('、',$project_info_product_form_val);
        }
        $project_info['product_type'] = $product_type[$project_info['product_type']];
        $project_info['product_user'] = $product_user[$project_info['product_user']];

        //项目照片
        $project_photo = $this->projectphoto_model->fetch_row($where);
        $data = array(
            'corp_info'=>$corp_info,
            'team_info'=>$team_info,
            'project_info'=>!empty($project_info) ? $project_info : '',
            'project_photo'=>!empty($project_photo) ? $project_photo : '',
        );

        //评分标准信息
        $distribute_info = $this->distribute_model->fetch_row($where);
        $group_id = $distribute_info['group_id'];
        $group_info = $this->expertgroup_model->fetch_row(array('id'=>$group_id));
        $score_standard_id = $group_info['score_standard_id'];
        $score_standard_info = $this->scorestandard_model->fetch_row(array('id'=>$score_standard_id));
        $standard_info = json_decode($score_standard_info['type'],true);

        //评分结果信息
        $result_info = $this->distributeresult_model->fetch_row(array('user_id'=>$user_id,'expert_id'=>$this->expert_info['id']));
        $result_info = json_decode($result_info['result'],true);
        //var_dump($result_info);die;
        //var_dump($standard_info);die;
        //var_dump($data);die;
        $this->assign('user_id',$user_id);
        $this->assign('standard_info',$standard_info);
        $this->assign('result_info',$result_info);
        $this->assign('data',$data);
        $this->assign('expert_group_data',$expert_group_data);

        $this->display('declare/index.html');

    }

    //保存评审信息
    public function doDeclare(){
        $expert_id = $this->expert_info['id'];
        $user_id = $this->input->post('user_id');
        $standard = $this->input->post('standard');
        $score = $this->input->post('score');
        if(count($standard) != count($score)){
            $this->ajax_return(array(),'参数错误',1000009);
        }
        //查询结果表是否存在信息
        $where = array('user_id'=>$user_id,'expert_id'=>$expert_id);
        //更新
        $update_data = array(
            'result' => json_encode(array_combine($standard,$score)),
            'status' => 2
        );
        $up_res = $this->distributeresult_model->update($update_data,$where);
        if($up_res !== false){
            $this->ajax_return(array(),'保存成功');
        }
        $this->ajax_return(array(),'保存失败',400001);
    }

    //一键提交所有评分
    public function submitDeclare(){
        $expert_id = $this->expert_info['id'];
        $group_id = $this->expert_info['group_id'];
        $where = array(
            'expert_id' => $expert_id,
            'status' => 2
        );
        $update_data = array(
            'status' => 3
        );
        $res = $this->distributeresult_model->update($update_data,$where);
        if($res !== false){
            //查询当前专家已提交的项目
            $cur_sub_res = $this->distributeresult_model->fetch_all(array('expert_id'=>$expert_id,'status'=>3));
            if(!empty($cur_sub_res)){
                $flag = true;
                foreach($cur_sub_res as $value){
                    $user_id = $value['user_id'];
                    $all_res = $this->distributeresult_model->fetch_all(array('user_id'=>$user_id));
                    //查询当前项目对应的所有专家是否都是已提交状态
                    foreach($all_res as $v1){
                        if($v1['status'] != 3){
                            $flag = false;
                            break;
                        }
                    }
                    if($flag){
                        $this->distribute_model->update(array('review_status'=>3),array('user_id'=>$user_id));
                    }
                }
            }
            $this->ajax_return(array(),'提交成功');
        }

        $this->ajax_return(array(),'提交失败',5000001);
    }

    //评审结果页
    public function result(){
        $page = !empty($this->input->get('page')) ? $this->input->get('page') : 1;
        $page_size = !empty($this->input->get('page_size')) ? $this->input->get('page_size') : 10;
        $offset = ($page - 1) * $page_size;

        $expert_id = $this->expert_info['id'];
        $result_info = $this->distributeresult_model->fetch_all(array('expert_id'=>$expert_id,'status'=>3),'','','',$offset,$page_size);
        if(!empty($result_info)){
            //var_dump($result_info);
            foreach($result_info as $key => $value){
                $result_arr = json_decode($value['result'],true);
                $result_str = array();
                foreach($result_arr as $k => $v){
                    $result_str[] = $k . ':' .$v;
                }
                $result_info[$key]['result'] = implode('<br>',$result_str);

                if($this->settings){
                    $user_id = $value['user_id'];
                    $all_res = $this->distributeresult_model->fetch_all(array('user_id'=>$user_id));
                    //查询当前项目对应的所有专家是否都是已提交状态
                    //var_dump($all_res);die;
                    $result_other = array();
                    foreach($all_res as $v1){
                        $result_data = !empty(json_decode($v1['result'],true)) ? array_sum(json_decode($v1['result'],true)) : '' ;
                        $result_other[] = $v1['expert_name'].':'.$result_data;
                    }
                    $result_info[$key]['result_other'] = implode('<br/>',$result_other);
                }else{
                    $result_info[$key]['result_other'] = array_sum($result_arr);
                }

            }
        }
        $count = $this->distributeresult_model->fetch_count(array('expert_id'=>$expert_id,'status'=>3));
        $pages_list = $this->dividePage("/DeclareInfo/result",$page_size,$count);

        $this->assign('offset',$offset);
        $this->assign('pages_list',$pages_list);
        $this->assign('result',$result_info);
        $this->assign('p','result');
        $this->display('declare/result.html');

    }

    //导出评审结果
    public function exportResult(){
        $this->load->library('lib_excel');

        $expert_id = $this->expert_info['id'];
        $result_info = $this->distributeresult_model->fetch_all(array('expert_id'=>$expert_id,'status'=>3),'user_id,project_name,result');
        if(!empty($result_info)){
            foreach($result_info as $key => $value){
                $result_arr = json_decode($value['result'],true);
                $result_str = array();
                foreach($result_arr as $k => $v){
                    $result_str[] = $k . ':' .$v;
                }
                $result_info[$key]['result'] = implode('、',$result_str);

                if($this->settings) {
                    $user_id = $value['user_id'];
                    $all_res = $this->distributeresult_model->fetch_all(array('user_id' => $user_id));
                    //查询当前项目对应的所有专家是否都是已提交状态
                    //var_dump($all_res);die;
                    $result_other = array();
                    foreach ($all_res as $v1) {
                        $result_data = !empty(json_decode($v1['result'], true)) ? array_sum(json_decode($v1['result'],
                            true)) : '';
                        $result_other[] = $v1['expert_name'] . ': ' . $result_data;
                    }
                    $result_info[$key]['result_other'] = implode('、', $result_other);
                }else{
                    $result_info[$key]['result_other'] = array_sum($result_arr);
                }
            }

            $file_name = 'result_'.date('Y-m-d');
            if($this->settings == 1){
                $titles = array('项目名称','评分详情','其他专家评分');
            }else{
                $titles = array('项目名称','评分详情','总分');
            }
            $this->lib_excel->createRow($titles);

            //var_dump($result_info);die;
            foreach($result_info as $key => $row) {
                unset($row['user_id']);
                $this->lib_excel->createRow($row);
            }
            $this->lib_excel->download($file_name);
        }
    }
}