<?php
class Lib_verifycode{
    private $width ;
    private $height;
    private $counts;
    private $distrubcode;
    private $fonturl;
    private $session;
    public $CI;
    function __construct($width = 160,$height = 50,$counts = 5,$distrubcode="1235467890qwertyuipkjhgfdaszxcvbnm",$fonturl=""){
        $this->CI = & get_instance();
        $this->CI->load->library('session');
        $this->width=$width;
        $this->height=$height;
        $this->counts=$counts;
        $this->distrubcode=$distrubcode;
        $this->fonturl=APPPATH."helpers/TektonPro-BoldCond.otf";
    }

    public function get_code(){
        $this->session=$this->sessioncode();
        $captcha=$this->session;
        $this->CI->session->set_userdata('captcha', $captcha);   //保存验证码值
        $this->imageOut();//生成验证码图片
    }

    public function check_code($code){
        $verify_code = $this->CI->session->userdata('captcha');

        if(isset($verify_code) && $verify_code == $code) {
            return true;
        } else {
            //删除验证码
            $this->CI->session->unset_userdata('captcha');
        }

        return false;
    }

    function imageOut(){
        Header("Content-type: image/GIF");
        $im=$this->createimagesource();
        $this->setbackgroundcolor($im);
        $this->set_code($im);
        $this->setdistrubecode($im);
        ImageGIF($im);
        ImageDestroy($im);
    }

    private function createimagesource(){
        return imagecreate($this->width,$this->height);
    }
    private function setbackgroundcolor($im){
        $bgcolor = ImageColorAllocate($im, rand(200,255),rand(200,255),rand(200,255));
        imagefill($im,0,0,$bgcolor);
    }
    private function setdistrubecode($im){
        $count_h=$this->height;
        $cou=floor($count_h/5);
        for($i=0;$i<$cou;$i++){
            $x=rand(0,$this->width);
            $y=rand(0,$this->height);
            $jiaodu=rand(0,360);
            $fontsize=rand(8,15);
            $fonturl=$this->fonturl;
            $originalcode = $this->distrubcode;
            $countdistrub = strlen($originalcode);
            $dscode = $originalcode[rand(0,$countdistrub-1)];
            $color = ImageColorAllocate($im, rand(40,140),rand(40,140),rand(40,140));
            imagettftext($im,$fontsize,$jiaodu,$x,$y,$color,$fonturl,$dscode);

        }
    }
    private function set_code($im){
        $width=$this->width;
        $counts=$this->counts;
        $height=$this->height;
        $scode=$this->session;
        $y=floor($height/2)+floor($height/4);
        $fontsize=rand(30,35);
        $fonturl=APPPATH."helpers/AdobeGothicStd-Bold.otf";

        for($i=0;$i<$counts;$i++){
            $char=$scode[$i];
            $x=floor($width/$counts)*$i+8;
            $jiaodu=rand(-20,30);
            $color = ImageColorAllocate($im,rand(0,50),rand(50,100),rand(100,140));
            imagettftext($im,$fontsize,$jiaodu,$x,$y,$color,$fonturl,$char);
        }



    }
    private function sessioncode(){
        $originalcode = $this->distrubcode;
        $countdistrub = strlen($originalcode);
        $_dscode = "";
        $counts=$this->counts;
        for($j=0;$j<$counts;$j++){
            $dscode = $originalcode[rand(0,$countdistrub-1)];
            $_dscode.=$dscode;
        }
        return $_dscode;

    }
}

