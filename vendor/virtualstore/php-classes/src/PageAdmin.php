<?php

namespace virtualstore;

use Rain\Tpl;

class PageAdmin extends Page{

    public function __construct($opts = array(), $tpl_dir = "/template/admin/"){

        parent::__construct($opts, $tpl_dir);

    }
    
}