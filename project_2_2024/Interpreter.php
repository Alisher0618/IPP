<?php

namespace IPP\Student;

use DOMDocument;
use IPP\Core\AbstractInterpreter;
use IPP\Core\Exception\NotImplementedException;
use IPP\Core\FileSourceReader;
use IPP\Core\Settings;
use IPP\Core\Engine;
use IPP\Core\ReturnCode;
use ParseXML;
use DOMElement;
use Instructions;
use Interexception;
use IPP\Core\StreamWriter;

include 'printXML.php';
include 'Instructions.php';

/**
* Main class
*/
class Interpreter extends AbstractInterpreter
{
    public function execute(): int
    {
        $settings = new Settings();
        $sourceReader = $settings->getSourceReader();
        $inputFile = $settings->getInputReader();
        $dom = $sourceReader->getDOMDocument();
        $output_stdout = new StreamWriter(STDOUT);
        $output_stderr = new StreamWriter(STDERR);
        $instructions = [];
        $labels = [];

        $test = new ParseXML($dom);

        
        foreach ($dom->documentElement->childNodes as $node){
            if ($node->nodeType === XML_ELEMENT_NODE && $node instanceof DOMElement){
                if($node->tagName == "instruction"){
                    if($node->getAttribute('opcode') === '' || $node->getAttribute('order') === ''){
                        $this->stderr->writeString("INVALID XML STRUCTURE\n");
                        exit(ReturnCode::INVALID_SOURCE_STRUCTURE);
                    }
                    array_push($instructions, $node);
                }else{
                    throw new Interexception("ERROR: Unexpected tag name", 32);
                }
            }
        }

        $number_of_label = 0;
        for ($i=0; $i < sizeof($instructions); $i++){ 
            if($test->check_order($instructions[$i]->getAttribute('order'))){
                $this->stderr->writeString("INVALID XML STRUCTURE\n");
                exit(ReturnCode::INVALID_SOURCE_STRUCTURE);
            }
            
            if(strtoupper($instructions[$i]->getAttribute('opcode')) == "LABEL"){
                $args = $instructions[$i]->getElementsByTagName('*');
                foreach ($args as $arg) {
                    $arg->nodeValue = trim($arg->nodeValue);
                    if(array_search($arg->nodeValue, array_keys($labels)) !== false){
                        $this->stderr->writeString("REDEFINITION OF LABELS\n");
                        exit(ReturnCode::SEMANTIC_ERROR);
                    }else{
                        $labels[$arg->nodeValue] = $number_of_label;
                    }
                }
                
            }
            $number_of_label++;
        } 

        $start = new Instructions($labels, $inputFile, $output_stdout, $output_stderr);
        $step = 0;
        for ($i=0; $i < sizeof($instructions); $i++) { 
            $step = $start->start_interpreter($instructions[$i], $i);
            $i = $step;
        }
            

        return ReturnCode::OK;
    }
    
}
