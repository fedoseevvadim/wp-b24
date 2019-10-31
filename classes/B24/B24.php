<?php

namespace B24;

interface B24 {

    public function __construct(
                                    string $crmUrl,
                                    string $crmLogin,
                                    string $crmPassword,
                                    string $client_id,
                                    string $clientSecret
                                );

    public function autorise();

    public function initCurl();


}