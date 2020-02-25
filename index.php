<?php
    $CampaignLink="http://trkbinom.info/click.php?key=fbgt6dpxy1xtgp97d6zz";
    $ApiKey='26000001cf8ff3243a8a025786b57f8ba31ff940';
    $getClick=new getClick($CampaignLink, $ApiKey);

    if ($getClick->getLandingUrl() == 'Direct') {
        header('Location: '.$getClick->getOfferUrl()); }
    else { 
        $domain = $_SERVER['SERVER_NAME'];
        $landingUrl = str_replace("%domain%", $domain, $getClick->getLandingUrl());
        $getClick->getLanding($landingUrl);
    }

    class getClick{
        function __construct($CampaignLink, $ApiKey){
            if(strpos($CampaignLink, '?')!==false){
                $this->ClickURL=$CampaignLink.'&lp_type=click_info&api_key='.$ApiKey;
            }else{
                $this->ClickURL=$CampaignLink.'?lp_type=click_info&api_key='.$ApiKey;
            }
            if(isset($_GET)){
                foreach($_GET AS $key=>$val){
                    $this->ClickURL=$this->ClickURL.'&'.$key.'='.$val;
                }
            }
            $this->DataClick=$this->getClickData($this->ClickURL);
        }
        function setLPClick(){
            $URL=$this->getLPClickURL();
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $URL);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 60);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            $result = curl_exec( $ch );
            curl_close( $ch );
            return true;
        }
        function getLPClickURL($emulation=1){
            if(isset($this->ClickURL) && isset($this->DataClick['uclick'])){
                $tempArr=explode('?',$this->ClickURL);
                if($emulation==1){
                    $LPClickURL=$tempArr[0].'?lp=1&emulation_mode=1&uclick='.$this->DataClick['uclick'];
                }else{
                    $LPClickURL=$tempArr[0].'?lp=1&uclick='.$this->DataClick['uclick'];
                }
                return $LPClickURL;
            }
            return false;
        }
        function getLanding($landingUrl){
            echo $this->loadLanding($landingUrl);
            
            /*if(isset($this->DataClick['landing']['type'])){
                if($this->DataClick['landing']['id']==0 || $this->DataClick['landing']['name']=='DIRECT'){
                    echo 'Direct';
                }else{
            if($this->DataClick['landing']['type']==2){
                        $this->includeLanding();
                    }else{
                        echo $this->loadLanding();
                    }
                }
            }*/
        }
        function includeLanding($landingUrl){
            ob_start();
            include($landingUrl);
            //include($this->getLandingUrl());
            return $this->replaceLandingLink(ob_get_clean());
        }
        function loadLanding($landingUrl){
            return $this->replaceLandingLink(file_get_contents('./index.html'));
            //return $this->replaceLandingLink(file_get_contents($this->getLandingUrl()));
        }
        function replaceLandingLink($html){
            if(isset($this->DataClick['uclick'])){
                $html=str_replace('?lp=1','?lp=1&uclick='.$this->DataClick['uclick'],$html);
            }
            return $html;
        }
        function getOfferUrl(){
            $OfferUrl='Unknown';
            if(isset($this->DataClick['offer']['url'])){
                $OfferUrl=$this->DataClick['offer']['url'];
            }
            return $OfferUrl;
        }
        function getLandingUrl(){
            $LandingUrl='Unknown';
            if(isset($this->DataClick['landing']['url'])){
                if($this->DataClick['landing']['id']=='0'){
                    $LandingUrl='Direct';
                }else{
                    $LandingUrl=$this->DataClick['landing']['url'];
                }
            }
            return $LandingUrl;
        }
        function getClickData($ClickURL){
            $ClickURL=$ClickURL.$this->getClickGet();
            $ClickOptions=$this->getClickOptions();
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $ClickURL);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 60);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            if(!empty($ClickOptions)){
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $ClickOptions);
            }
            $result = curl_exec( $ch );
            curl_close( $ch );
            if(!$result=json_decode($result,true)){
                $result['status']='error';
                $result['error']='Incorrect Campaign link';
            }
            return $result;
        }
        function getClickGet(){
            $gets='';
            foreach($_GET AS $key=>$val){
                $gets=$gets.'&'.$key.'='.$val;
            }
            return $gets;
        }
        function getClickOptions(){
            $posts=array();
            foreach($_POST AS $key=>$val){
                $posts[]=$key.'='.$val;
            }
            $Headers=array();
            $TrueHeaders=array(
                'REMOTE_ADDR','HTTP_USER_AGENT','HTTP_COOKIE','HTTP_X_PURPOSE',
                'HTTP_REFERER','HTTP_ACCEPT_LANGUAGE','HTTP_X_FORWARDED_FOR'
            );
            foreach($_SERVER AS $key=>$val){
                if(in_array($key,$TrueHeaders)){
                    $Headers[$key]=$val;
                }
            }
            if(!isset($Headers['HTTP_X_FORWARDED_FOR']) && isset($Headers['REMOTE_ADDR'])){
                $Headers['HTTP_X_FORWARDED_FOR']=$Headers['REMOTE_ADDR'];
            }
            $posts[]='ClickDataHeaders='.json_encode($Headers);
            return implode('&',$posts);
        }
    }
?>
