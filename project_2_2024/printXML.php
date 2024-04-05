<?php

class ParseXML{
    /** @var DOMElement */
    public $root;

    public int $actual_order = 0;
    function __construct(DOMDocument $source_xml){
        $this->root = $source_xml->documentElement;
    }

    /*public function parse_xml(){
        if($this->root->tagName != "program" || $this->root->getAttribute('language') != "IPPcode24"){
            echo "invalid xml doc\n";
            return 32;
        }else{
            echo "all good\n";
        }
    }*/

    public function check_order(String $new_order): int{
        if($this->actual_order < (int)$new_order){
            $this->actual_order = (int)$new_order;
            return 0;
        }else{
            echo "invalid order\n";
            exit(32);
        }
    }
}