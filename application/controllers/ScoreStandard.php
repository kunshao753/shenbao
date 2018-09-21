<?php
class ScoreStandard extends BASE_Controller{
    public function __construct(){
        parent::__construct();
        if(!$this->is_admin){
            header('location:/Index/getList');
            exit;
        }
        $this->load->model('Scorestandard_model','scorestandard_model');
    }

    public function getList(){
        $page = !empty($this->input->get('page')) ? $this->input->get('page') : 1;
        $page_size = !empty($this->input->get('page_size')) ? $this->input->get('page_size') : 10;
        $where = 'is_delete=0';
        $offset = ($page - 1) * $page_size;
        $standard_info = $this->scorestandard_model->fetch_all($where,'','id desc','',$offset,$page_size);
        $count = $this->scorestandard_model->fetch_count($where);

        if(!empty($standard_info)){
            foreach($standard_info as $key => $value){
                //处理评分标准信息
                $standard_info[$key]['type'] = json_decode($value['type'],true);
                $standard_array = array();
                if(!empty($standard_info[$key]['type'])){
                    foreach($standard_info[$key]['type'] as $type_info){
                        $standard_array[] = $type_info['standard'];
                    }
                }
                $standard_info[$key]['standard'] = implode('、',$standard_array);

                //查询专家组信息
                $standard_id = $value['id'];
                $eg_where = array(
                    'score_standard_id' => $standard_id,
                    'is_delete' => 0
                );
                $standard_info[$key]['group_name'] = '';
                $eg_info = $this->expertgroup_model->fetch_all($eg_where);
                $group_names = array();
                if(!empty($eg_info)){
                    foreach($eg_info as $value1){
                        $group_names[] = $value1['group_name'];
                    }
                }
                $standard_info[$key]['group_name'] = implode('、',$group_names);

            }
        }

        $pages_list = $this->dividePage('/ScoreStandard/getList',$page_size,$count);
        $this->assign('offset',$offset);
        $this->assign('data',$standard_info);
        $this->assign('pages_list',$pages_list);
        $this->assign('p','score');

        $this->display('standard/list.html');
    }

    public function add(){
        $this->assign('p','score');
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
        $this->assign('p','score');

        $this->display('standard/edit.html');
    }

    public function addScoreStandard(){
        $name = $this->input->post('name');
        $standard = $this->input->post('standard');
        $reason = $this->input->post('reason');
        $max_score = $this->input->post('max_score');

        if(empty($name)){
            $this->ajax_return(array(),'评分名称不能为空',5000001);
        }

        if(empty($standard)){
            $this->ajax_return(array(),'评分项不能为空',5000002);
        }

        if(empty($reason)){
            $this->ajax_return(array(),'评分依据不能为空',5000012);
        }

        if(empty($max_score)){
            $this->ajax_return(array(),'最高分不能为空',5000022);
        }
        if(count($standard) != count($reason) && count($reason) != count($max_score)){
            $this->ajax_return(array(),'提交参数有误',5000032);
        }
        $standard_reason_maxscore = array();
        foreach($standard as $key => $value){
            if(mb_strlen($value) > 10){
                $this->ajax_return('评分项不能超过10个字',5000130);
            }
            if(mb_strlen($reason[$key]) > 100){
                $this->ajax_return('评分依据不能超过100个字',5000131);
            }
            if(intval($max_score[$key]) > 100){
                $this->ajax_return('最高分值不能超过100',5000132);
            }
            $standard_reason_maxscore[$key]['standard'] = $standard[$key];
            $standard_reason_maxscore[$key]['reason'] = $reason[$key];
            $standard_reason_maxscore[$key]['maxscore'] = $max_score[$key];
        }

        $insert_data = array(
            'name' => $name,
            'type' => json_encode($standard_reason_maxscore),
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
        $max_score = $this->input->post('max_score');

        if(empty($id)){
            $this->ajax_return(array(),'id不能为空',5000004);
        }

        if(empty($name)){
            $this->ajax_return(array(),'评分名称不能为空',5000001);
        }

        if(empty($standard)){
            $this->ajax_return(array(),'评分项不能为空',5000002);
        }

        if(empty($reason)){
            $this->ajax_return(array(),'评分依据不能为空',5000012);
        }

        if(empty($max_score)){
            $this->ajax_return(array(),'最高分不能为空',5000022);
        }
        if(count($standard) != count($reason) && count($reason) != count($max_score)){
            $this->ajax_return(array(),'提交参数有误',5000032);
        }

        $standard_reason_maxscore = array();
        foreach($standard as $key => $value){
            if(mb_strlen($value) > 10){
                $this->ajax_return('评分项不能超过10个字',5000130);
            }
            if(mb_strlen($reason[$key]) > 100){
                $this->ajax_return('评分依据不能超过100个字',5000131);
            }
            if(intval($max_score[$key]) > 100){
                $this->ajax_return('最高分值不能超过100',5000132);
            }
            $standard_reason_maxscore[$key]['standard'] = $standard[$key];
            $standard_reason_maxscore[$key]['reason'] = $reason[$key];
            $standard_reason_maxscore[$key]['maxscore'] = $max_score[$key];
        }

        $update_data = array(
            'name' => $name,
            'type' => json_encode($standard_reason_maxscore),
        );

        $res = $this->scorestandard_model->update($update_data,array('id'=>$id));

        if($res !== false){
            $this->ajax_return($res,'修改成功');
        }else{
            $this->ajax_return(array(),'修改失败',5000007);
        }

    }

    //删除评分项
    public function delStandard(){
        $id = $this->input->post('id');
        if (empty($id)) {
            $this->ajax_return(array(), '参数错误', 5000008);
        }
        $where = array(
            'score_standard_id' => $id,
        );
        $list = $this->expertgroup_model->fetch_all($where);
        if($list){
            $this->ajax_return(array(), '已分配专家组,不能删除', 5000010);
        }

        $res = $this->scorestandard_model->update(array('is_delete'=>1),array('id'=>$id));
        if($res){
            $this->ajax_return($res, '删除成功');
        }else{
            $this->ajax_return(array(), '删除失败', 5000013);
        }

    }
}