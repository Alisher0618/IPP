<?php
/*
*Name: Alisher Mazhirinov
*Login: xmazhi00
*/
ini_set('display_errors', 'stderr');
$xml = new DOMDocument('1.0', 'UTF-8');
$xml->formatOutput=true;

$header = false;
$order = 0;

const no_errors = 0;
const error_params = 10;
const error_header = 21;
const error_opcode = 22;
const error_lexical_syntatic = 23;

$instructions = array(
    "MOVE", "CREATEFRAME", "PUSHFRAME", "POPFRAME", "DEFVAR", "CALL", "RETURN", 
    "PUSHS", "POPS", 
    "ADD", "SUB", "MUL", "IDIV", "LT", "GT", "EQ", "AND", "OR", "NOT", "INT2CHAR", "STRI2INT", 
    "READ", "WRITE", 
    "CONCAT", "STRLEN", "GETCHAR", "SETCHAR", 
    "TYPE", 
    "LABEL", "JUMP", "JUMPIFEQ", "JUMPIFNEQ", "EXIT",
    "DPRINT", "BREAK",
    ".IPPCODE23", ".IPPcode23");


$threeArgs = array("ADD", "SUB", "MUL", "IDIV", "LT", "GT", "EQ", "AND", "OR", "STRI2INT", 
    "CONCAT", "GETCHAR", "SETCHAR", "JUMPIFEQ", "JUMPIFNEQ");

$twoArgs = array("MOVE", "INT2CHAR", "READ", "STRLEN", "TYPE", "NOT");

$oneArgs = array("LABEL", "JUMP", "EXIT", "WRITE", "PUSHS", "POPS", "DEFVAR", "CALL", "DPRINT");

$zeroArgs = array("CREATEFRAME", "PUSHFRAME", "POPFRAME", "RETURN", "BREAK");

$regType   = "/^(int|string|bool)$/";
$regVar    = "/^(GF|LF|TF)@([a-zA-Z]|[_\-$%&*?!])(\w|[_\-$%&*?!])*$/";
$regLabel  = "/^(\w|[_\-$%&*?!])*$/";
$regInt    = "/^int@(([-\+]?[0-9]+$)|(0[xX][0-9a-fA-F]+$)|(0[oO][0-7]+$))/";
$regString = "/^string@(([^\s\#\\\\]|\\\\[0-9]{3})*$)/";
$regNil    = "/^nil@nil$/";
$regBool   = "/^bool@(true|false)$/";

$firstIsVar = array("MOVE", "DEFVAR", "POPS", "ADD", "SUB", "MUL", "IDIV", "LT", "GT", "EQ", "AND", "OR", "NOT", 
"INT2CHAR", "STRI2INT", "READ", "CONCAT", "STRLEN", "GETCHAR", "SETCHAR", "TYPE" );

$firstIsLabel = array("JUMPIFEQ", "JUMPIFNEQ", "CALL", "LABEL", "JUMP");

$firstIsSymb = array("PUSHS", "WRITE", "EXIT", "DPRINT");

$sndArgIsSymb = array("MOVE", "ADD", "SUB", "MUL", "IDIV", "LT", "GT", "EQ", "AND", "OR", "NOT", 
"INT2CHAR", "STRI2INT", "CONCAT", "STRLEN", "GETCHAR", "SETCHAR", "TYPE", "JUMPIFEQ", "JUMPIFNEQ");


function getHelp($argv) {
    if (array_search("--help", $argv, true)) {
        if (sizeof($argv) !== 2){
            fwrite(STDERR, "wrong paramaters");
            exit(error_params);
        } 
        else{
            echo "Script parse.php reads source code in IPPcode23 from standard input,\n".
            "checks correctness of the code and writes it to standard output XML representation of the program.\n".
             "Usage: php parse.php [--help]\n".
            "\t--help prints help to standard output.\n";
            exit(no_errors);
        }     
    }
}

function checkArgumentOne($instrName, $argName){
    global $regVar;
    global $regInt;
    global $regString;
    global $regBool;
    global $regNil;
    global $regLabel;

    global $firstIsVar;
    global $firstIsLabel;
    global $firstIsSymb;

    if(in_array($instrName, $firstIsVar) && preg_match($regVar, $argName) == 1){
        $var = explode('@', $argName);
        return $var;
    }else if(in_array($instrName, $firstIsLabel) && preg_match($regLabel, $argName) == 1){
        $argName = "label";
        return $argName;
    }else if(in_array($instrName, $firstIsSymb) && preg_match($regVar, $argName) == 1){
        $var = explode('@', $argName);
        return $var;
    }else if(in_array($instrName, $firstIsSymb) && preg_match($regInt, $argName) == 1){
        $var = explode('@', $argName);
        return $var;
    }else if(in_array($instrName, $firstIsSymb) && preg_match($regString, $argName) == 1){
        $var = explode('@', $argName);
        return $var;
    }else if(in_array($instrName, $firstIsSymb) && preg_match($regBool, $argName) == 1){
        $var = explode('@', $argName);
        return $var;
    }else if(in_array($instrName, $firstIsSymb) && preg_match($regNil, $argName) == 1){
        $var = explode('@', $argName);
        return $var;
    }else{
        exit(error_lexical_syntatic);
    }

}

function checkArgumentTwo($instrName, $argName){
    global $regVar;
    global $regInt;
    global $regString;
    global $regBool;
    global $regNil;
    global $regType;

    global $sndArgIsSymb;

    if(in_array($instrName, $sndArgIsSymb) && preg_match($regVar, $argName) == 1){
        $var = explode('@', $argName);
        return $var;
    }else if(in_array($instrName, $sndArgIsSymb) && preg_match($regInt, $argName) == 1){
        $var = explode('@', $argName);
        return $var;
    }else if(in_array($instrName, $sndArgIsSymb) && preg_match($regString, $argName) == 1){
        $var = explode('@', $argName);
        return $var;
    }else if(in_array($instrName, $sndArgIsSymb) && preg_match($regBool, $argName) == 1){
        $var = explode('@', $argName);
        return $var;
    }else if(in_array($instrName, $sndArgIsSymb) && preg_match($regNil, $argName) == 1){
        $var = explode('@', $argName);
        return $var;
    }else if(strcmp($instrName, "READ") == 0 && preg_match($regType, $argName) == 1){
        $argName = "type";
        return $argName;
    }else{
        exit(error_lexical_syntatic);
    }
}

function checkArgumentThree($argName){
    global $regVar;
    global $regInt;
    global $regString;
    global $regBool;
    global $regNil;
    
    if(preg_match($regInt, $argName) == 1){
        $var = explode('@', $argName);
        return $var;
    }else if(preg_match($regString, $argName) == 1){
        $var = explode('@', $argName);
        return $var;
    }else if(preg_match($regVar, $argName) == 1){
        $var = explode('@', $argName);
        return $var;
    }else if(preg_match($regBool, $argName) == 1){
        $var = explode('@', $argName);
        return $var;
    }else if(preg_match($regNil, $argName) == 1){
        $var = explode('@', $argName);
        return $var;
    }else{
        exit(error_lexical_syntatic);
    }
}

function specSymb($argName){
    $argName = str_replace("&", "&amp;", $argName);
    $argName = str_replace("<", "&lt;", $argName);
    $argName = str_replace(">", "&gt;", $argName);
    return $argName;
}

function createXML_for3($instrName, $order, $num){
    global $xml;
    global $prog;

    $instr = $xml->createElement("instruction");
    $prog->appendChild($instr);
    $instr->setAttribute("order", $order);
    $instr->setAttribute("opcode", $instrName[0]);

    $argument1 = checkArgumentOne($instrName[0], $instrName[1]);
    $instrName[1] = specSymb($instrName[1]);
    
    $arg1 = $xml->createElement("arg1", $instrName[1]);
    $instr->appendChild($arg1);

    
    if($num == 1){
        $argument1[0] = "var"; 
        $arg1->setAttribute("type", $argument1[0]);
    }else if($num == 0){
        $arg1->setAttribute("type", $argument1);
    }
    
    
    $argument2 = checkArgumentTwo($instrName[0], $instrName[2]);
    $argument2 = specSymb($argument2);
    if(strcmp($argument2[0], "GF") == 0 || strcmp($argument2[0], "LF") == 0 || strcmp($argument2[0], "TF") == 0){
        $newArg2 = implode('@', $argument2);
        $argument2[0] = "var";
        $arg2 = $xml->createElement("arg2", $newArg2);
    }else{
        if(strcmp($argument2[0], "string") == 0){
            $merge = "";
            for($i = 1; $i < count($argument2); $i++){
                if(strcmp($merge, "") == 0){
                    $merge = $merge . $argument2[$i]; 
                }else{
                    $merge = $merge . '@' . $argument2[$i];

                }
            }
            $arg2 = $xml->createElement("arg2", $merge);
        }else{
            $arg2 = $xml->createElement("arg2", $argument2[1]);
        }
    }
    $instr->appendChild($arg2);
    $arg2->setAttribute("type", $argument2[0]);


    $argument3 = checkArgumentThree($instrName[3]);
    $argument3 = specSymb($argument3);
    if(strcmp($argument3[0], "GF") == 0 || strcmp($argument3[0], "LF") == 0 || strcmp($argument3[0], "TF") == 0){
        $newArg3 = implode('@', $argument3);
        $argument3[0] = "var";
        $arg3 = $xml->createElement("arg3", $newArg3);
        
    }else{
        if(strcmp($argument3[0], "string") == 0){
            $merge = "";
            for($i = 1; $i < count($argument3); $i++){
                if(strcmp($merge, "") == 0){
                    $merge = $merge . $argument3[$i]; 
                }else{
                    $merge = $merge . '@' . $argument3[$i];

                }
                 
            }
            $arg3 = $xml->createElement("arg3", $merge);
        }else{
            $arg3 = $xml->createElement("arg3", $argument3[1]);
        }
    }
    $instr->appendChild($arg3);
    $arg3->setAttribute("type", $argument3[0]);
}

function createXML_for2($instrName, $order, $num){
    global $xml;
    global $prog;

    $instr = $xml->createElement("instruction");
    $prog->appendChild($instr);
    $instr->setAttribute("order", $order);
    $instr->setAttribute("opcode", $instrName[0]);

    $argument1 = checkArgumentOne($instrName[0], $instrName[1]);
    $instrName[1] = specSymb($instrName[1]);
   
    $arg1 = $xml->createElement("arg1", $instrName[1]);
    $instr->appendChild($arg1);

    $argument1[0] = "var";
    $arg1->setAttribute("type", $argument1[0]);


    $argument2 = checkArgumentTwo($instrName[0], $instrName[2]);
    $argument2 = specSymb($argument2);
    if($num == 1){
        if(strcmp($argument2[0], "GF") == 0 || strcmp($argument2[0], "LF") == 0 || strcmp($argument2[0], "TF") == 0){
            $newArg2 = implode('@', $argument2);
            $argument2[0] = "var";

            $arg2 = $xml->createElement("arg2", $newArg2);
            $instr->appendChild($arg2);
            $arg2->setAttribute("type", $argument2[0]);

        }else if(strcmp($argument2[0], "int") == 0 || strcmp($argument2[0], "string") == 0 || strcmp($argument2[0], "bool") == 0){
            if(strcmp($argument2[0], "string") == 0){
                $merge = "";
                for($i = 1; $i < count($argument2); $i++){
                    if(strcmp($merge, "") == 0){
                        $merge = $merge . $argument2[$i]; 
                    }else{
                        $merge = $merge . '@' . $argument2[$i];
                    }
                }
                $arg2 = $xml->createElement("arg2", $merge);
            }else{
                $arg2 = $xml->createElement("arg2", $argument2[1]);
            }
            $instr->appendChild($arg2);
            $arg2->setAttribute("type", $argument2[0]);
        }else if(strcmp($argument2[0], "nil") == 0){
            $arg2 = $xml->createElement("arg2", $argument2[1]); 
            $instr->appendChild($arg2);
            $arg2->setAttribute("type", $argument2[0]);
        }
        else{
            $arg2 = $xml->createElement("arg2", $instrName[2]); 
            $instr->appendChild($arg2);
            $arg2->setAttribute("type", $argument2);
        }
    }
}

function createXML_for1($instrName, $order, $num){
    global $xml;
    global $prog;

    $instr = $xml->createElement("instruction");
    $prog->appendChild($instr);
    $instr->setAttribute("order", $order);
    $instr->setAttribute("opcode", $instrName[0]);

    $argument1 = checkArgumentOne($instrName[0], $instrName[1]);
    $argument1 = specSymb($argument1);
    if($num == 1){
        if(strcmp($argument1[0], "GF") == 0 || strcmp($argument1[0], "LF") == 0 || strcmp($argument1[0], "TF") == 0){
            $newArg1 = implode('@', $argument1);
            $argument1[0] = "var";
            
            $arg1 = $xml->createElement("arg1", $newArg1);
        }else{
            if(strcmp($argument1[0], "string") == 0){
                $merge = "";
                for($i = 1; $i < count($argument1); $i++){
                    if(strcmp($merge, "") == 0){
                        $merge = $merge . $argument1[$i]; 
                    }else{
                        $merge = $merge . '@' . $argument1[$i];
                    }
                }
                $arg1 = $xml->createElement("arg1", $merge);
            }else{
                $arg1 = $xml->createElement("arg1", $argument1[1]);
            }
        }
        $instr->appendChild($arg1);
        $arg1->setAttribute("type", $argument1[0]);
    }else{
        $arg1 = $xml->createElement("arg1", $instrName[1]);
        $instr->appendChild($arg1);
        $arg1->setAttribute("type", $argument1);
    }
}

function createXML_for0($instrName, $order){
    global $xml;
    global $prog;

    $instr = $xml->createElement("instruction");
    $prog->appendChild($instr);
    $instr->setAttribute("order", $order);
    $instr->setAttribute("opcode", $instrName[0]);
}


function checkLine($line){
    global $threeArgs;
    global $twoArgs;
    global $oneArgs;
    global $zeroArgs;
    global $instructions;
    $isnotvar = 0;
    $isvar = 1;
    global $order;
    $args = count($line) - 1;

    if(in_array($line[0], $threeArgs) && $args == 3){
        if(strcmp($line[0], "JUMPIFEQ") == 0 || strcmp($line[0], "JUMPIFNEQ") == 0){    //label symb symb
            $order++;
            createXML_for3($line, $order, $isnotvar);
        }else{      //var symb symb
            $order++;
            createXML_for3($line, $order, $isvar);
        }
    }
    else if(in_array($line[0], $twoArgs) && $args == 2){
            $order++;
            createXML_for2($line, $order, $isvar);
    }
    else if(in_array($line[0], $oneArgs) && $args == 1){
        if(strcmp($line[0], "CALL") == 0 || strcmp($line[0], "LABEL") == 0 || strcmp($line[0], "JUMP") == 0){ //label
            $order++;
            createXML_for1($line, $order, $isnotvar);
        }else{      //var or symb 
            $order++;
            createXML_for1($line, $order, $isvar);
        }
    }else if(in_array($line[0], $zeroArgs) && $args == 0){
        $order++;
        createXML_for0($line, $order);
    }
    else if(!in_array($line[0], $instructions)){
        exit(error_opcode);
    }
    else{
        exit(error_lexical_syntatic);
    }
    
}


function scanner(){
    global $xml;
    global $prog;
    $counterHeader = 0;    

    while($line = fgets(STDIN)){
        if (preg_match("~#[^\r\n]*~", $line)) {
            $line = substr($line, 0, strpos($line, "#"));
        }
        
        $line = rtrim($line);
        $line = trim($line);
        $line = preg_replace('/\s+/', ' ', $line);

        if ($line == "")
            continue;

        $line_arr = explode(" ", $line);
        $line_arr[0] = strtoupper($line_arr[0]);

        if (preg_match('/^.IPPCODE23$/', $line_arr[0])){
            $prog = $xml->createElement("program");
            $xml->appendChild($prog);   
            $prog->setAttribute("language", 'IPPcode23');
        }

        if(strcmp($line_arr[0], ".IPPCODE23") == 0){
            $counterHeader++;
        }
        if($counterHeader > 1){
            exit(22);
        }
        if($counterHeader != 1){
            exit(21);
        }
    
        if(strcmp($line_arr[0], ".IPPCODE23") == 0){
            continue;
        }

        checkLine($line_arr);
    }
    
    echo $xml -> saveXML();
    exit(no_errors);
}


getHelp($argv);
scanner();

?>