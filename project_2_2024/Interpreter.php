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

include 'printXML.php';
include 'Instructions.php';

class Interpreter extends AbstractInterpreter
{
    public function execute(): int
    {
        // TODO: Start your code here
        // Check \IPP\Core\AbstractInterpreter for predefined I/O objects:
        // $dom = $this->source->getDOMDocument();
        // $val = $this->input->readString();
        //$this->stdout->writeString("stdout");
        // $this->stderr->writeString("stderr");
        #throw new NotImplementedException;

        $settings = new Settings();
        $sourceReader = $settings->getSourceReader();
        $inputFile = $settings->getInputReader();
        $dom = $sourceReader->getDOMDocument();
        $output_stdout = $this->stdout;
        $file = $inputFile->readInt();
        
        //echo "value from inputfile: " . $file . "\n";

        if ($dom instanceof DOMDocument) {
            $instructions = [];
            $labels = [];

            $test = new ParseXML($dom);

            foreach ($dom->documentElement->childNodes as $node){
                if ($node->nodeType === XML_ELEMENT_NODE && $node instanceof DOMElement){
                    if($node->tagName == "instruction"){
                        if($node->getAttribute('opcode') === '' || $node->getAttribute('order') === ''){
                            $this->stderr->writeString("INVALID XML STRUCTURE\n");
                            echo "aarrrrn\n";
                            exit(ReturnCode::INVALID_SOURCE_STRUCTURE);
                        }
                        array_push($instructions, $node);
                    }else{
                        exit(32);
                    }
                }
            }

            /*for ($i=0; $i < sizeof($instructions); $i++) { 
                echo $instructions[$i]->getAttribute('opcode') . " : " . $instructions[$i]->getAttribute('order') . "\n";
            }*/

            $number_of_label = 0;
            for ($i=0; $i < sizeof($instructions); $i++){ 
                if($test->check_order($instructions[$i]->getAttribute('order'))){
                    $this->stderr->writeString("INVALID XML STRUCTURE\n");
                    echo "aarrrrn\n";
                    exit(ReturnCode::INVALID_SOURCE_STRUCTURE);
                }
                
                if(strtoupper($instructions[$i]->getAttribute('opcode')) == "LABEL"){
                    $args = $instructions[$i]->getElementsByTagName('*');
                    foreach ($args as $arg) {
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

            //run            

            //echo gettype($instructions) . " " . gettype($inputFile) ."\n";

            /*foreach ($labels as $x => $y) {
                echo "$x: $y \n";
            }*/

            $start = new Instructions($labels, $inputFile, $output_stdout);
            $step = 0;
            for ($i=0; $i < sizeof($instructions); $i++) { 
                $step = $start->start_interpreter($instructions[$i], $i);
                $i = $step;
            }
            



        }
        else {
            echo "Failed to load the DOMDocument.";
        }     

        return ReturnCode::OK;
    }
    
}
