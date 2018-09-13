<?php
class Login extends BASE_Controller{

    public function __construct(){
        parent::__construct();
        $this->load->library('lib_verifycode');
    }

    public function index(){
        $this->display('login.html');
    }

    //验证码
    public function verifycode() {
        $this->lib_verifycode->get_code();
    }

    //登陆方法
    public function doLogin(){
        if ($this->user_info) {
            redirect('/index/index');
        }

        if ($this->input->post()) {
            //验证验证码
            $code = $this->input->post('code');
            if ($code == '') {
                $this->ajax_return(array(),'验证码不能为空！', 100001);
            }

            $check_res = $this->lib_verifycode->check_code($code);
            if (!$check_res) {
                $this->ajax_return(array(),'验证码错误，请正确输入验证码！', 100002);
            }

            $data = array(
                'account' => trim($this->input->post('account')),
                'password' => trim($this->input->post('password')),
            );

            if (empty($data['account'])) {
                $this->ajax_return(array(),'登录失败，账号不能为空！', 100003);
            }
            if (empty($data['password'])) {
                $this->ajax_return(array(),'登录失败，密码不能为空！', 100004);
            }

            $res = $this->login_model->login($data);

            if ($res['result'] == $this->_succ) {
                header('Location:index/index');
            } else {
                if ($res['reason']) {
                    $this->ajax_return(array(),$res['reason'], 100005);
                }

                $this->ajax_return(array(),'登录失败，用户名或密码错误！', 100006);
            }
        }
    }

    //验证验证码
    public function check_verifycode() {
        var_dump($this->lib_verifycode->check_code('4uxe6'));
    }
}