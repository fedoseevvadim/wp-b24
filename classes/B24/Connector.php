<?php

namespace B24;


class Connector implements B24 {

    public $accessToken;
    public $accessObj;

    public $crmUrl;
    public $crmLogin;
    public $crmPassword;
    public $clientId;
    public $clientSecret;

    public $error;


    public  $curl;

    public function __construct( $crmUrl, $crmLogin, $crmPassword, $client_id, $clientSecret ) {

        if ( empty ( $crmUrl ) ) {
            throw new \InvalidArgumentException('No url crm provided');
        }

        if ( empty ( $crmLogin ) ) {
            throw new \InvalidArgumentException('No crm login provided');
        }

        if ( empty ( $crmPassword ) ) {
            throw new \InvalidArgumentException('No crm password provided');
        }

        if ( empty ( $client_id ) ) {
            throw new \InvalidArgumentException('Client id is not provided');
        }

        if ( empty ( $clientSecret ) ) {
            throw new \InvalidArgumentException('Client Secret is not provided');
        }

        $this->crmUrl       = $crmUrl;
        $this->crmLogin     = $crmLogin;
        $this->crmPassword  = $crmPassword;
        $this->clientId     = $client_id;
        $this->clientSecret = $clientSecret;

        $this->autorise();

    }

    /**
    * addLead - check connection to the host
    * @param   $params array for query
    * @return  bolean
    */
    public function checkConnection ( $hostName ) {

        $fp = fsockopen("tcp://$hostName", 443, $errno, $errstr);

        if ( !$fp ) {

            $this->error = "ERROR: $errno - $errstr<br />\n";

            return false;
        }

        return true;

    }

    public function autorise() {

        $this->initCurl();

    }

    public function initCurl () {

        $_url   = 'https://'.$this->crmUrl;

        $ch     = curl_init();

        curl_setopt( $ch, CURLOPT_URL, $_url );
        curl_setopt( $ch, CURLOPT_HEADER, true );
        curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, false );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, TRUE );

        $res = curl_exec($ch);
        $l = '';

        if(preg_match('#Location: (.*)#', $res, $r)) {
            $l = trim($r[1]);
        }

        //echo $l.PHP_EOL;
        curl_setopt( $ch, CURLOPT_URL, $l );
        $res = curl_exec( $ch );
        preg_match ('#name="backurl" value="(.*)"#', $res, $math );

        $post = http_build_query( [
            'AUTH_FORM' => 'Y',
            'TYPE' => 'AUTH',
            'backurl' => $math[1],
            'USER_LOGIN' => $this->crmLogin,
            'USER_PASSWORD' => $this->crmPassword,
            'USER_REMEMBER' => 'Y'
        ] );

        curl_setopt( $ch, CURLOPT_URL, 'https://www.bitrix24.net/auth/' );
        curl_setopt( $ch, CURLOPT_POST, true );
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $post );

        $res    = curl_exec( $ch );
        $l      = '';

        if ( preg_match('#Location: (.*)#', $res, $r ) ) {
            $l = trim( $r[1] );
        }

        //echo $l.PHP_EOL;
        curl_setopt( $ch, CURLOPT_URL, $l );
        $res    = curl_exec( $ch );
        $l      = '';

        if ( preg_match( '#Location: (.*)#', $res, $r ) ) {
            $l = trim( $r[1] );
        }

        //echo $l.PHP_EOL;
        curl_setopt($ch, CURLOPT_URL, $l);
        $res = curl_exec($ch);

        //end autorize
        curl_setopt(
            $ch,
            CURLOPT_URL,
            'https://'.$this->crmUrl.'/oauth/authorize/?response_type=code&client_id='.$this->clientId
        );

        $res    = curl_exec($ch);
        $l      = '';

        if(preg_match('#Location: (.*)#', $res, $r)) {
            $l = trim($r[1]);
        }

        preg_match('/code=(.*)&do/', $l, $code);

        $code1      = $code[1];
        $pos        = strpos( $code1, "&" );
        $code_final = substr( $code1, 0, $pos );

        curl_setopt(
            $ch,
            CURLOPT_URL,
            'https://'.$this->crmUrl.'/oauth/token/?grant_type=authorization_code&client_id='.$this->clientId.'&client_secret='.$this->clientSecret.'&code='.$code_final.'&scope=crm'
        );

        curl_setopt( $ch, CURLOPT_HEADER, false );
        $res = curl_exec( $ch );
        curl_close( $ch );
        $obj = json_decode($res);

        //$access_token = $res['access_token'];
        $this->accessObj   = $obj;
        $this->accessToken = $obj->access_token;


    }

    /**
    * addLead - build curl
    * @param   $params array for query
    * @param   $restQuery
    * @return  $response
    */
    public function buildQuery ( array $params, $restQuery ): array {

        $c = curl_init('https://'.$this->crmUrl.'/rest/' . $restQuery );

        try {

            curl_setopt($c,CURLOPT_RETURNTRANSFER,true);
            curl_setopt($c,CURLOPT_POST,true);
            curl_setopt($c,CURLOPT_POSTFIELDS, http_build_query ( $params ));

            $response = curl_exec( $c );
            $response = json_decode( $response, true );

        } catch ( \Exception $e ) {

            echo 'Caught exeption: ' . $e->getMessage();
        }

        return $response;

    }

}
