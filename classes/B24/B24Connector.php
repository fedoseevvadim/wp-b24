<?php

namespace B24;


class B24Connector implements B24 {

    public $accessToken;
    public $accessObj;

    public $crmUrl;
    public $crmLogin;
    public $crmPassword;
    public $clientId;
    public $clientSecret;

    public  $curl;

    public function __construct( $crmUrl, $crmLogin, $crmPassword ) {

        if ( empty ( $crmUrl ) ) {
            throw new \RuntimeException('No url crm provided');
        }

        if ( empty ( $crmLogin ) ) {
            throw new \RuntimeException('No crm login provided');
        }

        if ( empty ( $crmPassword ) ) {
            throw new \RuntimeException('No crm password provided');
        }

        $this->crmUrl   = $crmUrl;
        $this->crmLogin = $crmLogin;
        $this->crmPassword = $crmPassword;

        $this->autorise();

    }

    public function autorise() {

        $this->initCurl();

    }

    public function initCurl () {

        $ch = curl_init();

        curl_setopt( $ch, CURLOPT_URL,   $this->crmUrl );
        curl_setopt( $ch, CURLOPT_HEADER, true );
        curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, false );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );

        $res    = curl_exec( $ch );
        $l      = '';

        if ( preg_match(  '#Location: (.*)#', $res, $r ) ) {
            $l = trim($r[1]);
        }

        curl_setopt( $ch, CURLOPT_URL, $l );
        $res = curl_exec( $ch );

        preg_match(  '#name=backurl" value="(.*)"#', $res, $math );

        $post = http_build_query(
            [
                'AUTH_FORM' => 'Y',
                'TYPE'  => 'AUTH',
                'backurl' => $math[1],
                'USER_LOGIN' => $this->crmLogin,
                'USER_PASSWORD' => $this->crmPassword,
                'USER_REMEMBER' => 'Y'
            ]
        );

        curl_setopt( $ch, CURLOPT_URL, 'https://www.bitrix24.net/auth/');
        curl_setopt( $ch, CURLOPT_PORT, true);
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $post);

        $res    = curl_exec( $ch );
        $l      = '';

        if ( preg_match(  '#Location: (.*)#', $res, $r ) ) {
            $l = trim($r[1]);
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
            'https://'.$this->crmURL.'/oauth/authorize/?response_type=code&client_id='.$this->client_id
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
            'https://'.$this->crmURL.'/oauth/token/?grant_type=authorization_code&client_id='.$this->client_id.'&client_secret='.$this->client_secret.'&code='.$code_final.'&scope=crm'
        );

        curl_setopt( $ch, CURLOPT_HEADER, false );
        $res = curl_exec( $ch );
        curl_close( $ch );
        $obj = json_decode($res);

        //$access_token = $res['access_token'];
        $this->access_obj   = $obj;
        $this->access_token = $obj->access_token;

    }


    /**
    * addLead - добавляет лид в базу
    * @param   array $data - array of data
    * @return nothing
    */
    public function addLead ( $data ) {

        $c                              = curl_init('https://'.$this->crmUrl.'/rest/crm.lead.add.json');
        $params["auth"]                 = $this->accessToken;
        $params["fields"]['TITLE']      = "Новый заказ с сайта ".$data['client_name'];
        $params["fields"]['NAME']       = $data['name'];
        $params["fields"]['STATUS_ID']  = "NEW";

        curl_setopt($c,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($c,CURLOPT_POST,true);
        curl_setopt($c,CURLOPT_POSTFIELDS,http_build_query($params));

        $response = curl_exec($c);
        $response = json_decode($response,true);

        return $response['result'];

    }

}
