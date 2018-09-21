<?php

/**
 * 评审
 * Class DeclareInfo
 */
class Index extends BASE_Controller{
    public function __construct(){
        parent::__construct();

        $this->load->model('Corp_model','corp_model');
        $this->load->model('Project_model','project_model');
        $this->load->model('Projectphoto_model','projectphoto_model');
        $this->load->model('Projectteam_model','projectteam_model');
        $this->load->model('Distributeresult_model','distribute_result_model');
        $this->load->model('Distribute_model','distribute_model');
    }

    //首页
    public function getList(){

        $review_status = $this->input->get('review_status');//评审状态
        $project_name = $this->input->get('project_name');
        $page = !empty($this->input->get('page')) ? $this->input->get('page') : 1;
        $page_size = !empty($this->input->get('page_size')) ? $this->input->get('page_size') : 10;
        $offset = ($page - 1) * $page_size;

        if($this->is_admin){//管理员
            $data_res = $this->corp_model->get_list($project_name,$review_status,$this->is_admin,$this->expert_info,$offset,$page_size);
            $data = $this->parse_data($data_res['data']);
            $count = $data_res['count'];
        }else{
            $data_res = $this->corp_model->get_list($project_name,$review_status,0,$this->expert_info,$offset,$page_size);
            $data = $this->parse_data($data_res['data']);
            $count = $data_res['count'];
        }

        //var_dump($count);
        //var_dump($data);die;
        $pages_list = $this->dividePage('/Index/getList?project_name='.$project_name.'&review_status='.$review_status,$page_size,$count);
        $this->assign('review_status',$review_status);
        $this->assign('project_name',$project_name);
        $this->assign('offset',$offset);
        $this->assign('data',$data);
        $this->assign('pages_list',$pages_list);
        $this->assign('p','index');

        if($this->is_admin == 1){
            $this->display('index/admin_index.html');
        }else{
            $this->display('index/expert_index.html');
        }
    }

    //选择专家组页面
    public function choose(){
        if(!$this->is_admin){
            header('location:/Index/getList');
            exit;
        }
        $user_id = $this->input->get('user_id');
        $where = array('user_id' => $user_id);
        $corp_info = $this->corp_model->fetch_row($where);
        if(empty($corp_info)){
            return false;
        }
        if(!in_array($corp_info['audit_status'],array(1,3,4))){
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
        //var_dump($data);die;
        $this->assign('data',$data);
        $this->assign('expert_group_data',$expert_group_data);
        $this->assign('p','index');


        $this->display('index/choose_group.html');
    }

    //分配专家组
    public function distribute(){
        if(!$this->is_admin){
            $this->ajax_return(array(),'权限错误',300001);
        }
        $user_id = $this->input->post('user_id');
        $user_name = $this->input->post('user_name');
        $project_id = $this->input->post('project_id');
        $project_name = $this->input->post('project_name');
        $group_id = $this->input->post('group_id');

        if(empty($user_id) || empty($group_id)){
            $this->ajax_return(array(),'参数错误',300001);
        }

        $insert_data = array(
            'user_id' => $user_id,
            'group_id' => $group_id,
            'review_status' => 2,
            'create_at' => date('Y-m-d H:i:s')
        );
        $res = $this->distribute_model->insert($insert_data);
        if($res){

            //查询当前专家组内的所有专家
            $expert_res = $this->expert_model->fetch_all(array('group_id'=>$group_id,'is_delete'=>0),'id,name');
            //var_dump($expert_res);die;
            if(!empty($expert_res)){
                foreach($expert_res as $value){
                    $expert_id = $value['id'];
                    $expert_name = $value['name'];
                    $insert_dr_data = array(
                        'user_id' => $user_id,
                        'user_name' => $user_name,
                        'group_id' => $group_id,
                        'expert_id' => $expert_id,
                        'expert_name' => $expert_name,
                        'project_id' => $project_id,
                        'project_name' => $project_name,
                        'create_at' => date('Y-m-d H:i:s')
                    );
                    $this->distribute_result_model->insert($insert_dr_data);
                }
            }
            $this->ajax_return($res,'分配成功!');
        }else{
            $this->ajax_return($res,'分配失败!',300002);
        }
    }

    //导出管理员维度列表
    public function export_admin(){
        if(!$this->is_admin){
            return false;
        }
        $this->load->library('lib_excel');
        $review_status = $this->input->get('review_status');//评审状态
        $project_name = $this->input->get('project_name');

        $file_name = 'project_info_'.date('Y-m-d');
        $titles = array('项目名称','报名人姓名','手机号','报名来源','参赛身份','企业名称','产品类型','产品形态','评审状态','评审情况');
        $this->lib_excel->createRow($titles);
        $data_res = $this->corp_model->get_list($project_name,$review_status,$this->is_admin,$this->expert_info);
        $data = $this->parse_data($data_res['data']);
        if(!empty($data)){
            foreach($data as $key => $value){
                unset($data[$key]['id']);
                unset($data[$key]['user_id']);
                unset($data[$key]['audit_status']);
                unset($data[$key]['status']);
            }
        }
        //var_dump($data);die;
        foreach($data as $row) {
            $this->lib_excel->createRow($row);
        }
        $this->lib_excel->download($file_name);
    }

    //导出专家维度列表
    public function export_expert(){
        if(!$this->is_admin){
            return false;
        }
        $this->load->library('lib_excel');

        $file_name = 'result_info_'.date('Y-m-d');
        $titles = array('专家名称','项目名称','评审情况','总分');
        $this->lib_excel->createRow($titles);
        $data_res = $this->distribute_result_model->fetch_all('','expert_name,project_name,result','result desc,expert_id');

        if(!empty($data_res)){
            foreach($data_res as $key => $value){
                $result = json_decode($value['result'],true);
                $result_arr = array();
                $data_res[$key]['score'] = 0;
                if(!empty($result)){
                    $data_res[$key]['score'] = array_sum($result);
                    foreach($result as $k => $v){
                        $result_arr[] = $k .": ". $v;
                    }
                }
                $data_res[$key]['result'] = implode(' 、',$result_arr);
            }

            foreach($data_res as $row) {
                $this->lib_excel->createRow($row);
            }
            $this->lib_excel->download($file_name);
        }

    }
    //设置
    public function settings(){
        if(!$this->is_admin){
            $this->ajax_return(array(),'权限错误',300001);
        }
        $settings = $this->input->post('settings');
        if(in_array($settings,array(0,1))){
            $res = $this->settings_model->update(array('value'=>$settings),array('type'=>'permit_show'));
            if($res !== false){
                $this->ajax_return(array(),'设置成功');
            }
        }
        $this->ajax_return(array(),'设置失败',3000001);
    }

    //解析首页数据
    private function parse_data($data=array()){
        $infoConfig = $this->getCorpInfoConfig();
        if(!empty($data)){
            foreach($data as $key => $value){
                $signup_resouce = $value['signup_resouce'];
                $contestant_identity = $value['contestant_identity'];
                $product_type = $value['product_type'];
                $review_status = !empty($value['review_status']) ? $value['review_status'] : 1;
                $status = !empty($value['status']) ? $value['status'] : 1;
                $product_form_val = json_decode($value['product_form_val'],true);

                $data[$key]['signup_resouce'] = $infoConfig['signupResouce'][$signup_resouce];
                $data[$key]['contestant_identity'] = $infoConfig['contestantIdentity'][$contestant_identity];
                $data[$key]['product_type'] = $infoConfig['productType'][$product_type];
                $data[$key]['review_status'] = $infoConfig['reviewStatus'][$review_status];
                $data[$key]['status'] = $infoConfig['status'][$status];

                $product_form_arr = array();
                foreach($product_form_val as $k1 => $v1 ){
                    $product_form_arr[] = $infoConfig['productForm'][$k1];
                }
                $data[$key]['product_form_val'] = implode('、',$product_form_arr);
            }
        }

        return $data;
    }
}