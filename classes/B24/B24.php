<?php

namespace B24;

interface B24 {

    public function __construct($crmUrl, $crmLogin, $crmPassword, $client_id, $clientSecret);

    public function autorise();

    public function initCurl();


}