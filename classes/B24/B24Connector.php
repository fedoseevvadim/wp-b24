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

    public $error;

    const leadTitle = "Новый заказ с сайта";
    const dealTitle = "Новый заказ с сайта";
    const statusId = "NEW";

    const prefix = "UF_CRM_";

    public  $curl;

    // map fileds
    // First Elem - WP
    // Second - B24 fileds
    private static $arrContactFields = [

        ["first_name",       "NAME"],
        ["last_name",        "LAST_NAME"],
        ["billing_email",    "EMAIL"] ,
        ["billing_phone",    "WORK_PHONE"]

    ];

    private static $arrDealFields = [
        ["CONTACT_ID",  "CONTACT_ID"],
        ["SOURCE_ID",   "SOURCE_ID"],
        ["CATEGORY_ID", "CATEGORY_ID"],
    ];

    public function __construct( $crmUrl, $crmLogin, $crmPassword, $client_id, $clientSecret ) {

        if ( empty ( $crmUrl ) ) {
            throw new \RuntimeException('No url crm provided');
        }

        if ( empty ( $crmLogin ) ) {
            throw new \RuntimeException('No crm login provided');
        }

        if ( empty ( $crmPassword ) ) {
            throw new \RuntimeException('No crm password provided');
        }

        if ( empty ( $client_id ) ) {
            throw new \RuntimeException('Client id is not provided');
        }

        if ( empty ( $clientSecret ) ) {
            throw new \RuntimeException('Client Secret is not provided');
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
    private function buildQuery ( array $params, $restQuery ): array {

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

    private function prepareFields () {

    }

    /**
    * addLead - добавляет лид в базу
    * @param   array $data - array of data
    * @return $response
    */
    public function addLead ( array $data ) {

        $restQuery = "crm.lead.add.json";

        $params["auth"]                 = $this->accessToken;
        $params["fields"]['TITLE']      = self::leadTitle . $data['client_name'];
        $params["fields"]['NAME']       = $data['name'];
        $params["fields"]['STATUS_ID']  = "NEW";

        $response = $this->buildQuery( $params, $restQuery );

        return $response['result'];

    }

    /**
    * addLead - добавляет лид в базу
    * @param   array $data - array of data
    * @return $response
    */
    public function addDeal ( array $data ) {

        $restQuery = "crm.deal.add.json";

        $params["auth"]                 = $this->accessToken;
        $params["fields"]['TITLE']      = self::dealTitle;// . $data['client_name'];

        $params = $this->mapFieldsB24( $data, $params );

        $response = $this->buildQuery( $params, $restQuery );
        return $response['result'];
    }

    /**
    * mapFieldsB24 - map fields to B24
    * @param   array $data      - array of data
    * @param   array $params    - array of params for B24
    * @return array $params
    */
    public function mapFieldsB24 ( array $data, array $params ): array {

        $arrUserFields = $this->getDealUserFileds();

        foreach ( $data as $key => $item ) {

            $type = gettype($item);

            if ( strpos( $key, self::prefix ) === false ) {
                $params["fields"][$key] = $item;
                continue;

            }

            // search element in B24
            $bFieldFound = false;

            foreach ( $arrUserFields as $field ) {

                if ( $field["FIELD_NAME"] === $key ) {

                    $bFieldFound = true;

                    switch ( $field["USER_TYPE_ID"] ) {

                            // map date
                        case "date":
                            $params["fields"][$key] = $data[$key];
                            continue;
                            break;

                            // find id in B24 values
                        case "enumeration":

                            $listID = array_search( $item, array_column( $field["LIST"], 'VALUE') );

                            if ( $listID >= 0 ) {
                                $ID = (int) $field["LIST"][$listID]["ID"];
                                $params["fields"][$key] = $ID;
                                continue;
                            }

                        case "string" or "integer" or "boolean":
                            $params["fields"][$key] = $item;
                            break;

                    }
                }
            }

            if ( $bFieldFound === false ) {
                $params["fields"][$key] = $item;
            }

        }

        return $params;
    }

    /**
    * addLead - добавляет контакт в базу
    * @param   array $data - array of data
    * @return $response
    */
    public function addContact( array $data ): int {

        if ( empty ( $data ) ) {
            throw new \RuntimeException('Data is empty');
        }

        $result = $this->getContactByEmail( $data['billing_email'][0] );

        if ( is_array( $result) AND count($result) > 0 ) {

            $userId = (int) $result[0]["ID"];

            return $userId;
        }

        // Or add contact

        foreach (self::$arrContactFields as $key => $item ) {

            $params["fields"][$item[1]] = $data[$item[0]][0];

        }

        $params["fields"]['STATUS_ID']              = self::statusId;
        $params["fields"]['PHONE']                  = [[
            "VALUE" => $data['billing_phone'][0],
            "VALUE_TYPE" => "WORK"
        ]];

        $params["fields"]['EMAIL']                  = [[
            "VALUE" => $data['billing_email'][0],
            "VALUE_TYPE" => "WORK"
        ]];

        $restQuery = "crm.contact.add.json";
        $params["auth"]  = $this->accessToken;

        $response = $this->buildQuery( $params, $restQuery );

        return $response['result']; //return contact id

    }

    /**
     * getContactByEmail - возвращает id контакта по email
     * @param   text $email
     * @return result
     */
    public function getContactByEmail( $email ) {

        if ( empty ( $email ) ) {
            throw new \RuntimeException('Email is empty');
        }

        $restQuery = "crm.contact.list.json";

        $params["auth"]     = $this->accessToken;
        $params["filter"] = ["EMAIL" => $email];

        $response = $this->buildQuery( $params, $restQuery );

        return $response['result'];
    }

    /**
     * getDealUserFileds - get user fields
     * @param   no params
     * @return result
     */
    public function getDealUserFileds () {

        $restQuery      = "crm.deal.userfield.list";
        $params["auth"] = $this->accessToken;
        $response       = $this->buildQuery( $params, $restQuery );

        return $response['result'];


    }



}
