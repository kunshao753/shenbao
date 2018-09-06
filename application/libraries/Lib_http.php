<?php

class Lib_http{
    /**
     * Contains the last HTTP status code returned.
     */
    public $http_code;
    /**
     * Contains the last API call.
     */
    public $url;
    /**
     * Set up the API root URL.
     */
    public $host;
    /**
     * Set timeout default.
     */
    public $timeout = 30;
    /**
     * Set connect timeout.
     */
    public $connecttimeout = 30;
    /**
     * Respons format.
     */
    public $format = 'json';
    /**
     * Decode returned json data.
     */
    public $decode_json = TRUE;
    /**
     * Contains the last HTTP headers returned.
     */
    public $http_info;
    /**
     * print the debug info
     */
    public $debug = FALSE;
    /**
     * Verify SSL Cert.
     */
    public $ssl_verifypeer = FALSE;
    /**
     * Set the useragnet.
     */
    public $useragent = 'weibo';

    /**
     * 模拟Referer
     * @var url
     */
    public $referer;

    public $follow;

    /**
     * boundary of multipart
     * @ignore
     */
    public static $boundary = '';

    public function __construct($config=''){
        $this->host = isset($config['host']) ? $config['host'] : '';
    }

    public function set_timeout($time){
        $this->timeout = $time;
    }

    public function set_connecttimeout($time){
        $this->connecttimeout = $time;
    }

    function get($url, $parameters = array(), $headers = array()){
        $response = $this->request($url, 'GET', $parameters, NULL, $headers);
        if($this->format === 'json' && $this->decode_json){
            return json_decode($response, true);
        }
        return $response;
    }

    function post($url, $parameters = array(), $multi = false, $headers = array()){
        $response = $this->request($url, 'POST', $parameters, $multi, $headers );
        if($this->format === 'json' && $this->decode_json){
            return json_decode($response, true);
        }
        return $response;
    }

    function delete($url, $parameters = array()){
        $response = $this->request($url, 'DELETE', $parameters);
        if($this->format === 'json' && $this->decode_json){
            return json_decode($response, true);
        }
        return $response;
    }

    function request($url, $method, $parameters, $multi = false, $headers = array()){

        if(isset($_GET['debug'])){
            echo $url;
            echo "<hr>";
            print_r($parameters);
            $this->debug = true;
        }
        if(strrpos($url, 'http://') !== 0 && strrpos($url, 'https://') !== 0){
            $url = "{$this->host}{$url}";
        }

        switch ($method){
            case 'GET':
                $url .= strpos($url, '?') === false ? '?' : '';
                $url .= http_build_query($parameters);
                return $this->http($url, 'GET',null,$headers);
            default:
                $body = $parameters;
                if($multi){
                    $body = self::build_http_query_multi($parameters);
                    $headers[] = "Content-Type: multipart/form-data; boundary=" . self::$boundary;
                }elseif(is_array($parameters) || is_object($parameters)){
                    $body = http_build_query($parameters);
                }

                return $this->http($url, $method, $body, $headers);
        }
    }

    function http($url, $method, $postfields = NULL, $headers = array()){
        if(isset($_GET['debug'])) echo $url,"\r\n";
        $this->http_info = array();
        $ci = curl_init();

        /* Curl settings */
        curl_setopt($ci, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        curl_setopt($ci, CURLOPT_USERAGENT, $this->useragent);
        curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, $this->connecttimeout);
        curl_setopt($ci, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ci, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ci, CURLOPT_ENCODING, "");
        curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, $this->ssl_verifypeer);
        curl_setopt($ci, CURLOPT_HEADERFUNCTION, array($this, 'getHeader'));
        curl_setopt($ci, CURLOPT_HEADER, FALSE);

        switch ($method){
            case 'POST':
                curl_setopt($ci, CURLOPT_POST, TRUE);
                if(!empty($postfields)){
                    curl_setopt($ci, CURLOPT_POSTFIELDS, $postfields);
                    $this->postdata = $postfields;
                }
                break;
            case 'GET':
                curl_setopt($ci, CURLOPT_FOLLOWLOCATION, 1);
                break;
            case 'DELETE':
                curl_setopt($ci, CURLOPT_CUSTOMREQUEST, 'DELETE');
                if(!empty($postfields)){
                    $url = "{$url}?{$postfields}";
                }
        }

        $headers[] = "API-RemoteIP: " . $_SERVER['REMOTE_ADDR'];
        curl_setopt($ci, CURLOPT_URL, $url );
        curl_setopt($ci, CURLOPT_HTTPHEADER, $headers );
        curl_setopt($ci, CURLINFO_HEADER_OUT, TRUE );
        if($this->referer){
            curl_setopt($ci, CURLOPT_REFERER, $this->referer);
        }
        if($this->follow){
            curl_setopt($ci, CURLOPT_FOLLOWLOCATION, 1);
        }

        $response = curl_exec($ci);
        $this->http_code = curl_getinfo($ci, CURLINFO_HTTP_CODE);
        $this->http_info = array_merge($this->http_info, curl_getinfo($ci));
        $this->url = $url;

        if($this->debug){
            echo "=====post data======\r\n";
            var_dump($postfields);

            echo '=====info====='."\r\n";
            print_r( curl_getinfo($ci) );

            echo '=====$response====='."\r\n";
            print_r( $response );
        }
        curl_close ($ci);
        return $response;
    }

    function getHeader($header){
        $i = strpos($header, ':');
        if(!empty($i)){
            $key = str_replace('-', '_', strtolower(substr($header, 0, $i)));
            $value = trim(substr($header, $i + 2));
            $this->http_header[$key] = $value;
        }
        return strlen($header);
    }

    public static function build_http_query_multi($params){
        if(!$params) return '';

        uksort($params, 'strcmp');

        $pairs = array();

        self::$boundary = $boundary = uniqid('------------------');
        $MPboundary = '--'.$boundary;
        $endMPboundary = $MPboundary. '--';
        $multipartbody = '';

        foreach ($params as $parameter => $value){

            if( in_array($parameter, array('pic', 'image','Filedata')) && $value{0} == '@' ){
                $url = ltrim( $value, '@' );
                $content = file_get_contents( $url );
                $array = explode( '?', basename( $url ) );
                $filename = $array[0];

                $multipartbody .= $MPboundary . "\r\n";
                $multipartbody .= 'Content-Disposition: form-data; name="' . $parameter . '"; filename="' . $filename . '"'. "\r\n";
                $multipartbody .= "Content-Type: image/unknown\r\n\r\n";
                $multipartbody .= $content. "\r\n";
            } else {
                $multipartbody .= $MPboundary . "\r\n";
                $multipartbody .= 'content-disposition: form-data; name="' . $parameter . "\"\r\n\r\n";
                $multipartbody .= $value."\r\n";
            }

        }

        $multipartbody .= $endMPboundary;
        return $multipartbody;
    }
}