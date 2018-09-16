<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class BASE_Controller extends CI_Controller {
    public $expert_info;
    public $is_admin = 0;
    protected $_succ = 'succ';
    protected $_fail = 'fail';
    public function __construct() {
        parent::__construct();
        $this->load->model('Expert_model','expert_model');
        $this->load->model('Expertgroup_model','expertgroup_model');
        $this->load->model('Login_model','login_model');

        self::_init();

    }

    protected function _init()
    {
        $path_set = array(
            'login/index',
            'login/verifycode',
            'login/dologin',
        );
        $admin_uid = $this->login_model->get_admin_id();
        $path = $this->router->class . "/" . $this->router->method;
        if(in_array($path,$path_set) && !$admin_uid) {
            return;
        }
        if (!$admin_uid) {
            header('Location:/login/index');
            exit;
        }

        //用户信息
        $this->expert_info = $this->expert_model->fetch_row(array('id'=>$admin_uid));

        $this->assign('expert_info', $this->expert_info);
        if($this->expert_info['permission'] == 1){
            $this->is_admin = 1;
            $this->assign('is_admin',1);
        }
        $this->assign('photo_pre_url', 'http://shenbaoreg.kepuchina.cn/public');
    }

    public function assign($key,$val) {
        $this->ci_smarty->assign($key,$val);
    }

    public function display($html) {
        $this->ci_smarty->display($html);
    }

    public function getCorpInfoConfig()
    {
        return [
            'productForm'=>array(
                "1"=>array(
                    'name'=>'App Ios',
                    'tips'=>'请填写APP名称',
                    'show' => 0,
                    'text' => '',
                    'key' => 'product_form_k[1]',
                    'val' => 'product_form_v[1]'
                ),
                "2"=>array(
                    'name'=>'App Android',
                    'tips'=>'请填写APP名称',
                    'show' => 0,
                    'text' => '',
                    'key' => 'product_form_k[2]',
                    'val' => 'product_form_v[2]'
                ),
                "3"=>array(
                    'name'=>'小程序',
                    'tips'=>'请填写小程序名称',
                    'show' => 0,
                    'text' => '',
                    'key' => 'product_form_k[3]',
                    'val' => 'product_form_v[3]'
                ),
                "4"=>array(
                    'name'=>'网址',
                    'tips'=>'请填写网址',
                    'show' => 0,
                    'text' => '',
                    'key' => 'product_form_k[4]',
                    'val' => 'product_form_v[4]'
                ),
                "5"=>array(
                    'name'=>'H5',
                    'tips'=>'请填写网址',
                    'show' => 0,
                    'text' => '',
                    'key' => 'product_form_k[5]',
                    'val' => 'product_form_v[5]'
                ),
                "6"=>array(
                    'name'=>'微信公众号',
                    'tips'=>'请填写微信公众号名称',
                    'show' => 0,
                    'text' => '',
                    'key' => 'product_form_k[6]',
                    'val' => 'product_form_v[6]'
                ),
            ),
            'productType' => array(
                "1"=>array( 'name' => 'product_type', 'text' => '科普内容聚合平台（知识、游戏、教育、工具书类）', 'show' => 0),
                "2"=>array( 'name' => 'product_type', 'text' => '科普技术', 'show' => 0),
                "3"=>array( 'name' => 'product_type', 'text' => '科普交互服务', 'show' => 0),
                "4"=>array( 'name' => 'product_type', 'text' => '科普体验', 'show' => 0),
            ),
            'help'=>array(
                "1"=>array( 'name' => 'accept_help[1]', 'text' => '投资', 'show' => 0),
                "2"=>array( 'name' => 'accept_help[2]', 'text' => '资源对接（产品、技术合作）', 'show' => 0 ),
                "3"=>array( 'name' => 'accept_help[3]', 'text' => '创业指导（知名投资人、企业家指导）', 'show' => 0 ),
                "4"=>array( 'name' => 'accept_help[4]', 'text' => '项目推广（品牌宣传）', 'show' => 0 ),
                "5"=>array( 'name' => 'accept_help[5]', 'text' => '创业训练营', 'show' => 0 ),
                "6"=>array( 'name' => 'accept_help[6]', 'text' => '其他', 'show' => 0 )
            ),
            'signupResouce' =>array(
                "1"=>array(
                    'name'=>'地方推荐',
                    'tips'=>'请填写推荐的组织或学会',
                    'show' => 0,
                    'text' => ''
                ),
                "2"=>array(
                    'name'=>'投资人推荐',
                    'tips'=>'填写投资人',
                    'show' => 0,
                    'text' => ''
                ),
                "3"=>array(
                    'name'=>'高校推荐',
                    'tips'=>'请填写学校',
                    'show' => 0,
                    'text' => ''
                ),
                "4"=>array(
                    'name'=>'自主报名',
                    'tips'=>'',
                    'show' => 0,
                    'text' => ''
                )
            )];
    }


    public function ajax_return($data=array(), $msg='',$code=0,$type='json'){
        $result = array();
        $result['data'] = $data;
        $result['msg'] = $msg;
        $result['code'] = $code;

        $callback = $this->input->get('callback');

        //返回数据类型
        if(strtoupper($type)=='JSON') {
                // 返回JSON数据格式到客户端 包含状态信息
                header('Content-Type:application/json; charset=utf-8');
                $return   = empty($callback) ? json_encode($result) : ' ' . $callback . "(" . json_encode($result) . ");";
                exit($return);
            }elseif(strtoupper($type)=='XML'){
                // 返回xml格式数据
                header('Content-Type:text/xml; charset=utf-8');
                $return   = empty($callback) ? xml_encode($result) : ' ' . $callback . "(" . xml_encode($result) . ");";
                exit($return);
            }elseif(strtoupper($type)=='EVAL'){
                // 返回可执行的js脚本
                header('Content-Type:text/html; charset=utf-8');
                exit($result);
            }
    }

    public function dividePage($site_url,$page_size,$rows){
        //装载类文件
        $this->load->library('pagination');

        $config['base_url']=$site_url;

        $config['full_tag_open'] = '<ul class="page-box">';
        $config['full_tag_close'] = '</ul>';
        $config['first_tag_open'] = '<li class="page-btn p-first">';
        $config['first_tag_close'] = '</li>';
        $config['prev_tag_open'] = '<li class="page-btn p-pre">';
        $config['prev_tag_close'] = '</li>';
        $config['next_tag_open'] = '<li class="page-btn p-next">';
        $config['next_tag_close'] = '</li>';
        $config['cur_tag_open'] = '<li class="cur">';
        $config['cur_tag_close'] = '</li>';
        $config['last_tag_open'] = '<li class="page-btn p-last">';
        $config['last_tag_close'] = '</li>';
        $config['num_tag_open'] = '<li>';
        $config['num_tag_close'] = '</li>';

        $config['enable_query_strings'] = true;
        $config['page_query_string'] = true;
        $config['query_string_segment'] = 'page';
        $config['use_page_numbers'] = TRUE;
        $config['num_links'] = 3;
        $config['per_page']=$page_size;

        $config['first_link']= '首页';
        $config['next_link']= '下一页';
        $config['prev_link']= '上一页';
        $config['last_link']= '末页';


        $config['total_rows']=$rows;

        //初始化 ，传入配置
        $this->pagination->initialize($config);
        return $this->pagination->create_links();
    }

}
