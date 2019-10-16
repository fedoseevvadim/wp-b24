<?php

namespace B24;

interface B24 {

    public function __construct($crmUrl, $crmLogin, $crmPassword);

    public function autorise();

}