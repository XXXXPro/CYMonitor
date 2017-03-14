<?php

class Requests {
   private $ch;

   function __construct() {
     $this->ch = curl_init();
     curl_setopt($this->ch,CURLOPT_RETURNTRANSFER,1);
     curl_setopt($this->ch,CURLOPT_TIMEOUT,2);
     curl_setopt($this->ch,CURLOPT_SAFE_UPLOAD,1);
     curl_setopt($this->ch,CURLOPT_USERAGENT,"Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/56.0.2924.87 Safari/537.36 OPR/43.0.2442.1144");
     if (defined('CY_cookie')) curl_setopt($this->ch,CURLOPT_COOKIE,CY_cookie);
     curl_setopt($this->ch,CURLOPT_COOKIEJAR,'cookies.txt');
   }

   function __destruct() {
     curl_close($this->ch);
   }

   function get_last_error() {
     curl_error($this->ch);
   }

   function build_string($parameters) {
     $buffer='';
     foreach ($parameters as $key=>$value) $buffer.=urlencode($key).'='.urlencode($value).'&';
     if ($buffer) $buffer=substr($buffer,0,-1);
     return $buffer;
   }

   function get($url,$params='') {
     if (is_array($params)) $params =  http_build_query($params);
     if ($params) $params='?'.$params;

     curl_setopt($this->ch,CURLOPT_HTTPGET,true);
     curl_setopt($this->ch,CURLOPT_URL,$url.$params);
     $result = curl_exec($this->ch);
//     print_r(curl_getinfo($this->ch));
     return $result;
   }

   function post($url,$params) {
     if (is_array($params)) $params =  http_build_query($params);

     curl_setopt($this->ch,CURLOPT_POST,true);
     curl_setopt($this->ch,CURLOPT_URL, $url);
     curl_setopt($this->ch,CURLOPT_POSTFIELDS,$params);
     $result = curl_exec($this->ch);
     return $result;
   }
}
