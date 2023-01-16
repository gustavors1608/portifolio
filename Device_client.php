<?php
//arquivo principalmente usado para identificar o dispositivo em logs, e assim saber que em x celular ou pc deu problema

namespace tools;


class Device_client{

    public function Get_browser(){
        $dados =  $_SERVER['HTTP_USER_AGENT'];
        $return = array();
        $browser = array("Navigator"            => "/Navigator(.*)/i",
                        "Firefox"              => "/Firefox(.*)/i",
                        "Internet Explorer"    => "/MSIE(.*)/i",
                        "Google Chrome"        => "/chrome(.*)/i",
                        "MAXTHON"              => "/MAXTHON(.*)/i",
                        "Opera"                => "/Opera(.*)/i",
                        );
        foreach($browser as $key => $value){
            if(preg_match($value,$dados)){
                $return[1] = $this->getVersion($key, $value, $dados );
                $return[0] = $key;
                return $return;
            }
        }
    }

    //util nos logs para debugar e ver por que do erro
    public function Get_os(){
        $OS = array("Windows"   =>   "/Windows/i",
                    "Linux"     =>   "/Linux/i",
                    "Unix"      =>   "/Unix/i",
                    "Mac"       =>   "/Mac/i",
                    'iphone'    =>   '/iphone/i',           
                    'ipod'      =>   '/ipod/i',              
                    'ipad'      =>   '/ipad/i',             
                    'blackberry'=>   '/blackberry/i',       
                    'webos'     =>   '/webos/i'          
                    );

        foreach($OS as $key => $value){
            if(stripos($_SERVER['HTTP_USER_AGENT'],'android') !== false) { 
                return 'Android';
            }
            if(preg_match($value, $_SERVER['HTTP_USER_AGENT'])){
                return $key;
            }
        }
    }

    //versao do navegador
    public function getVersion($browser, $search, $string){
        $version = "";
        $browser = strtolower($browser);
        preg_match_all($search,$string,$match);
        switch($browser){
            case "firefox": $version = str_replace("/","",$match[1][0]);
            break;

            case "opera": $version = str_replace("/","",substr($match[1][0],0,5));
            break;

            case "navigator": $version = substr($match[1][0],1,7);
            break;

            case "maxthon": $version = str_replace(")","",$match[1][0]);
            break;

            case "google chrome": $version = substr($match[1][0],1,10);
        }
        return $version;
    }

    //verifica se a pessoa ta no celular, usado pra link de app em vez de site, ex: no celular exibir um link para ir para o app do insta e no pc um link para abrir o site do insta
    public function is_mobile() {
        return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);
    }
}



?>