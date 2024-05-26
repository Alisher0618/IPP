<?php

class ParseXML{
    /** @var DOMElement */
    public $root;

    public int $actual_order = 0;
    function __construct(DOMDocument $source_xml){
        $this->root = $source_xml->documentElement;
    }

    /**
    * Method for checking order of opcodes
    */
    public function check_order(String $new_order): int{
        if($this->actual_order < (int)$new_order){
            $this->actual_order = (int)$new_order;
            return 0;
        }else{
            throw new Interexception("Invalid order", 32);
        }
    }
}