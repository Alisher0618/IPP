<?php

include 'Interexceptions.php';

class Execute{
    public string $opcode;
    public mixed $instr;
    public mixed $class_instr;
    public function __construct(string $opcode, mixed $instr, mixed $class_instr){
        $this->opcode = $opcode;
        $this->instr = $instr;
        $this->class_instr = $class_instr;
    }

    public function check_type(mixed $symbol) : mixed {
        if($symbol->getAttribute('type') == "var"){
            $parts = explode("@", $symbol->nodeValue);
            $value = $this->class_instr->stack->get_symbol($parts);
            //echo $value . "\n";
            if($value == null){
                if($this->opcode != "TYPE"){
                    throw new Interexception("ERROR: No value", 56);
                }else{
                    $return_value = null;
                    $return_type = "var";
                    return array($return_type, $return_value);
                }
            }
            $type = $this->class_instr->stack->get_type($parts[1]);
            $return_value = $value;
            $return_type = $type;
        }elseif ($symbol->getAttribute('type') == "label"){
            $label_name = $symbol->nodeValue;

            if(!array_key_exists($label_name, $this->class_instr->all_labels)){
                throw new Interexception("ERROR: Nonexisting label", 52);
            }

            $return_type = $this->class_instr->all_labels[$label_name];
            $return_value = $label_name;
        }
        else{
            $return_value = $symbol->nodeValue;
            $return_type = $symbol->getAttribute('type');
        }

        return array($return_type, $return_value);
    }

    // DEFVAR
    public function instr_defvar() : void {
        $this->class_instr->check_arguments($this->instr);
        $arg = $this->instr->getElementsByTagName('arg1')->item(0);

        if (!($arg->hasAttribute('type'))) {
            throw new Interexception("ERROR: Tag is empty", 32);
        } 

        if($arg->getAttribute('type') != "var"){
            throw new Interexception("ERROR: Wrong type of structure", 32);
        }

        $var_value = $arg->nodeValue;

        $parts = explode("@", $var_value);
        $this->class_instr->stack->push_frame($parts);
        //$this->class_instr->stack->set_symbol($arg->nodeValue, "null", "null");
    }

    // WRITE
    public function instr_write() : void {
        $this->class_instr->check_arguments($this->instr);

        $arg = $this->instr->getElementsByTagName('arg1')->item(0);

        if (!($arg->hasAttribute('type'))) {
            throw new Interexception("ERROR: Tag is empty", 32);
        }

        if($arg->getAttribute('type') == "var"){
            $parts = explode("@", $arg->nodeValue);
            $value = $this->class_instr->stack->get_symbol($parts);
            $type = $this->class_instr->stack->get_type($parts[1]);
            //echo $value . " - " . $type . "\n";
            if($type == "int"){
                $this->class_instr->write_stdout->writeInt((int) $value);
            }else if($type == "nil"){
                $this->class_instr->write_stdout->writeString("");
            }else if($type == "string"){ //if string add converter
                $this->class_instr->write_stdout->writeString($value);
            }else if($type == "bool"){ //if string add converter
                $this->class_instr->write_stdout->writeString($value);
            }
             
        }
        else{
            if($arg->getAttribute('type') == "int"){
                $this->class_instr->write_stdout->writeInt((int) $arg->nodeValue);
            }else if($arg->getAttribute('type') == "nil"){
                $this->class_instr->write_stdout->writeString("");
            }else if($arg->getAttribute('type') == "string"){ //if string add converter
                $this->class_instr->write_stdout->writeString($arg->nodeValue);
            }   
        }
    }

    // MOVE
    public function instr_move() : void {
        $this->class_instr->check_arguments($this->instr);

        $arg_1 = $this->instr->getElementsByTagName('arg1')->item(0);
        //echo $arg_1->nodeValue . "\n";
        $arg_2 = $this->instr->getElementsByTagName('arg2')->item(0);
        //echo $arg_2->nodeValue . "\n";

        if(!($arg_1->hasAttribute('type'))){
            throw new Interexception("ERROR: Tag is empty", 32);
        }
        if(!($arg_2->hasAttribute('type'))){
            throw new Interexception("ERROR: Tag is empty", 32);
        }

        if($arg_1->getAttribute('type') != "var"){
            throw new Interexception("ERROR: Wrong type of structure", 32);
        }

        $arg2_symb = $this->check_type($arg_2);

        if($arg2_symb[0] == null){
            throw new Interexception("ERROR: No type", 56);
        }

        if($arg2_symb[0] == "string" && $arg2_symb[1] == null){
            $arg2_symb[1] = "";
        }

        /*echo $arg_1->nodeValue . "\n";
        echo $arg2_symb[0] . "\n";
        echo $arg2_symb[1] . "\n";*/

        $this->class_instr->stack->set_symbol($arg_1->nodeValue, $arg2_symb[1], $arg2_symb[0]);
    }

    // ADD, SUB, MUL, IDIV
    public function instr_math(string $opcode) : void {
        $this->class_instr->check_arguments($this->instr);

        $arg_1 = $this->instr->getElementsByTagName('arg1')->item(0);
        $arg_2 = $this->instr->getElementsByTagName('arg2')->item(0);
        $arg_3 = $this->instr->getElementsByTagName('arg3')->item(0);

        if(!($arg_1->hasAttribute('type'))){
            throw new Interexception("ERROR: Tag is empty", 32);
        }
        if(!($arg_2->hasAttribute('type'))){
            throw new Interexception("ERROR: Tag is empty", 32);
        }
        if(!($arg_3->hasAttribute('type'))){
            throw new Interexception("ERROR: Tag is empty", 32);
        }
        if($arg_1->getAttribute('type') != "var"){
            throw new Interexception("ERROR: Wrong type of structure", 32);
        }

        $arg2_symb = $this->check_type($arg_2);
        $arg3_symb = $this->check_type($arg_3);

        if(($arg2_symb[0] == null|| $arg3_symb[0] == null)){
            throw new Interexception("ERROR: No type", 56);
        }
        
        if(($arg2_symb[0] != "int" || $arg3_symb[0] != "int")){
            throw new Interexception("ERROR: Wrong operand type", 53);
        }

        if((!is_numeric($arg2_symb[1])|| !is_numeric($arg3_symb[1]))){
            throw new Interexception("ERROR: Wrong operand value", 32);
        }

        if($opcode == "ADD"){
            $sum = (int) $arg2_symb[1] + (int) $arg3_symb[1];
        }elseif($opcode == "SUB"){
            $sum = (int) $arg2_symb[1] - (int) $arg3_symb[1];
        }elseif($opcode == "MUL"){
            $sum = (int) $arg2_symb[1] * (int) $arg3_symb[1];
        }else{
            if((int) $arg3_symb[1] == 0){
                throw new Interexception("ERROR: Division by zero", 57);
            }
            $sum = intdiv((int) $arg2_symb[1], (int) $arg3_symb[1]);
        }

        $this->class_instr->stack->set_symbol($arg_1->nodeValue, $sum, "int");
    }

    // LT, EQ, GT
    public function instr_rel(string $opcode) : void {
        $this->class_instr->check_arguments($this->instr);

        $arg_1 = $this->instr->getElementsByTagName('arg1')->item(0);
        $arg_2 = $this->instr->getElementsByTagName('arg2')->item(0);
        $arg_3 = $this->instr->getElementsByTagName('arg3')->item(0);

        if(!($arg_1->hasAttribute('type'))){
            throw new Interexception("ERROR: Tag is empty", 32);
        }
        if(!($arg_2->hasAttribute('type'))){
            throw new Interexception("ERROR: Tag is empty", 32);
        }
        if(!($arg_3->hasAttribute('type'))){
            throw new Interexception("ERROR: Tag is empty", 32);
        }
        if($arg_1->getAttribute('type') != "var"){
            throw new Interexception("ERROR: Wrong type of structure", 32);
        }

        $arg2_symb = $this->check_type($arg_2);
        $arg3_symb = $this->check_type($arg_3);


        if($opcode == "EQ"){
            if($arg2_symb[0] == "nil" || $arg3_symb[0] == "nil"){
                if($arg2_symb[0] == $arg3_symb[0]){
                    $result = "true";
                }else{
                    $result = "false";
                }
                
                $this->class_instr->stack->set_symbol($arg_1->nodeValue, $result, "bool");
            }
            else{
                if($arg2_symb[0] != $arg3_symb[0]){
                    throw new Interexception("ERROR: Wrong type of argument", 53);
                }

                if($arg2_symb[0] == "int"){
                    if(!(is_numeric($arg2_symb[1]) && is_numeric($arg3_symb[1]))){
                        throw new Interexception("ERROR: Wrong value of argument", 57);
                    }

                    $tmp_result = (int) $arg2_symb[1] == (int) $arg3_symb[1];
                    $result = strtolower((string) $tmp_result);

                    $this->class_instr->stack->set_symbol($arg_1->nodeValue, $result, "bool");
                }
            }
        }
        elseif($opcode == "LT" || $opcode == "GT"){
            if($arg2_symb[0] == null || $arg3_symb[0] == null){
                throw new Interexception("ERROR: No type", 53);
            }

            if($arg2_symb[0] == "nil" || $arg3_symb[0] == "nil"){
                throw new Interexception("ERROR: Wrong operand type", 53);
            }
            if($arg2_symb[0] != $arg3_symb[0]){
                throw new Interexception("ERROR: Wrong operand type", 53);
            }

            $allowed_types = array("int", "bool", "string");

            if(!in_array($arg2_symb[0], $allowed_types) || !in_array($arg3_symb[0], $allowed_types)){
                throw new Interexception("ERROR: Wrong operand type", 53);
            }

            if($arg2_symb[0] == "bool"){
                if(($arg2_symb[1] != "true" || $arg2_symb[1] != "false") || ($arg3_symb[1] != "true" || $arg3_symb[1] != "false")){
                    throw new Interexception("ERROR: Wrong value of argument", 57);
                }

                if($arg2_symb[1] == "true"){
                    $arg2_symb[1] = true;
                }else{
                    $arg2_symb[1] = false;
                }
                if($arg3_symb[1] == "true"){
                    $arg3_symb[1] = true;
                }else{
                    $arg3_symb[1] = false;
                }
            }

            if($arg2_symb[0] == "int"){
                if(!(is_numeric($arg2_symb[1]) && is_numeric($arg3_symb[1]))){
                    throw new Interexception("ERROR: Wrong type of argument", 57);
                }

                $arg2_symb[1] = (int) $arg2_symb[1];
                $arg3_symb[1] = (int) $arg3_symb[1];
            }

            if($opcode == "GT"){
                $tmp_result = (string) ($arg2_symb[1] > $arg3_symb[1]);
                $result = strtolower((string) $tmp_result);

                $this->class_instr->stack->set_symbol($arg_1->nodeValue, $result, "bool");
            }
            else{
                $tmp_result = (string) ($arg2_symb[1] < $arg3_symb[1]);
                $result = strtolower((string) $tmp_result);

                $this->class_instr->stack->set_symbol($arg_1->nodeValue, $result, "bool");
            }   
        }
    }

    // ADD, OR, NOT
    public function instr_bool(string $opcode) : void {
        $this->class_instr->check_arguments($this->instr);
        $arg_1 = $this->instr->getElementsByTagName('arg1')->item(0);
        if($opcode == "NOT"){
            $arg_2 = $this->instr->getElementsByTagName('arg2')->item(0);
    
            if(!($arg_1->hasAttribute('type'))){
               throw new Interexception("ERROR: Tag is empty", 32);
            }
            if(!($arg_2->hasAttribute('type'))){
                throw new Interexception("ERROR: Tag is empty", 32);
            }
            if($arg_1->getAttribute('type') != "var"){
                throw new Interexception("ERROR: Wrong type of structure", 32);
            }
    
            $arg2_symb = $this->check_type($arg_2);

            if($arg2_symb[0] != "bool"){
                throw new Interexception("ERROR: No type", 53);
            }

            if($arg2_symb[0] == null){
                throw new Interexception("ERROR: No type", 56);
            }

            if($arg2_symb[1] != "true" && $arg2_symb[1] != "false"){
                throw new Interexception("ERROR: Wrong value of operand", 57);
            }

            if($arg2_symb[1] == "true"){
                $arg2_symb[1] = true;
            }else{
                $arg2_symb[1] = false;
            }

            $tmp_result = (string) (!$arg2_symb[1]);
            $result = strtolower((string) $tmp_result);

            if($result == "1"){
                $result = "true";
            }else{
                $result = "false";
            }
            $this->class_instr->stack->set_symbol($arg_1->nodeValue, $result, "bool");

        }
        elseif($opcode == "AND" || $opcode == "OR"){
            $arg_2 = $this->instr->getElementsByTagName('arg2')->item(0);
            $arg_3 = $this->instr->getElementsByTagName('arg3')->item(0);
    
            if(!($arg_1->hasAttribute('type'))){
                throw new Interexception("ERROR: Tag is empty", 32);
            }
            if(!($arg_2->hasAttribute('type'))){
                throw new Interexception("ERROR: Tag is empty", 32);
            }
            if(!($arg_3->hasAttribute('type'))){
                throw new Interexception("ERROR: Tag is empty", 32);
            }
            if($arg_1->getAttribute('type') != "var"){
                throw new Interexception("ERROR: Wrong type of structure", 32);
            }
    
            $arg2_symb = $this->check_type($arg_2);
            $arg3_symb = $this->check_type($arg_3);
            
            if($arg2_symb[0] != "bool" || $arg3_symb[0] != "bool"){
                throw new Interexception("ERROR: Wrong operand type", 53);
            }

            if($arg2_symb[0] == null || $arg3_symb[0] == null){
                throw new Interexception("ERROR: No type", 56);
            }

            if(($arg2_symb[1] != "true" && $arg2_symb[1] != "false") || ($arg3_symb[1] != "true" && $arg3_symb[1] != "false")){
                throw new Interexception("ERROR: Wrong value of operand", 57);
            }

            if($arg2_symb[1] == "true"){
                $arg2_symb[1] = true;
            }else{
                $arg2_symb[1] = false;
            }
            if($arg3_symb[1] == "true"){
                $arg3_symb[1] = true;
            }else{
                $arg3_symb[1] = false;
            }


            if($opcode == "AND"){
                $tmp_result = (string) ($arg2_symb[1] && $arg3_symb[1]);
                $result = strtolower((string) $tmp_result);
            }else{
                $tmp_result = (string) ($arg2_symb[1] || $arg3_symb[1]);
                $result = strtolower((string) $tmp_result);
            }

            if($result == "1"){
                $result = "true";
            }else{
                $result = "false";
            }
            $this->class_instr->stack->set_symbol($arg_1->nodeValue, $result, "bool");
        }
        
    }

    // INT2CHAR
    public function instr_int2char() : void {
        $this->class_instr->check_arguments($this->instr);

        $arg_1 = $this->instr->getElementsByTagName('arg1')->item(0);
        $arg_2 = $this->instr->getElementsByTagName('arg2')->item(0);

        if(!($arg_1->hasAttribute('type'))){
            throw new Interexception("ERROR: Tag is empty", 32);
        }
        if(!($arg_2->hasAttribute('type'))){
            throw new Interexception("ERROR: Tag is empty", 32);
        }

        if($arg_1->getAttribute('type') != "var"){
            throw new Interexception("ERROR: Wrong type of structure", 32);
        }

        $arg2_symb = $this->check_type($arg_2);

        if($arg2_symb[0] == null){
            throw new Interexception("ERROR: No type", 56);
        }

        if($arg2_symb[0] != "int"){
            throw new Interexception("ERROR: Wrong operand type", 53);
        }
        
        if(!(is_numeric($arg2_symb[1]))){
            throw new Interexception("ERROR: Wrong operand type for this operation", 58);
        }

        $tmp = (int)($arg2_symb[1]);
        if(!mb_check_encoding(mb_chr($tmp, 'UTF-8'), 'UTF-8')){
            throw new Interexception("ERROR: Wrong operand type for this operation", 58);
        }

        try {
            $result = mb_chr((int)$tmp);
        } catch (Exception $e) {
            throw new Interexception("ERROR: Wrong operand type for this operation", 58);
        }

        $this->class_instr->stack->set_symbol($arg_1->nodeValue, $result, "string");

    }

    // STR2INT
    public function instr_str2int() : void {
        $this->class_instr->check_arguments($this->instr);

        $arg_1 = $this->instr->getElementsByTagName('arg1')->item(0);
        $arg_2 = $this->instr->getElementsByTagName('arg2')->item(0);
        $arg_3 = $this->instr->getElementsByTagName('arg3')->item(0);

        if(!($arg_1->hasAttribute('type'))){
            throw new Interexception("ERROR: Tag is empty", 32);
        }
        if(!($arg_2->hasAttribute('type'))){
            throw new Interexception("ERROR: Tag is empty", 32);
        }
        if(!($arg_3->hasAttribute('type'))){
            throw new Interexception("ERROR: Tag is empty", 32);
        }
        if($arg_1->getAttribute('type') != "var"){
            throw new Interexception("ERROR: Wrong type of structure", 32);
        }

        $arg2_symb = $this->check_type($arg_2);
        $arg3_symb = $this->check_type($arg_3);

        if(($arg2_symb[0] == null || $arg3_symb[0] == null)){
            throw new Interexception("ERROR: No type", 56);
        }

        if(($arg2_symb[0] != "string" || $arg3_symb[0] != "int")){
            throw new Interexception("ERROR: Wrong operand type", 53);
        }

        if(!(is_numeric($arg3_symb[1])) || (int)$arg3_symb[1] < 0){
            throw new Interexception("ERROR: Wrong operand type for this operation", 58);
        }

        if((int)$arg3_symb[1] >= strlen($arg2_symb[1]) || $arg2_symb[1] == ""){
            throw new Interexception("ERROR: Wrong operand type for this operation", 58);
        }

        $position = (int) $arg3_symb[1];
        $result = mb_ord($arg2_symb[1][$position]);

        $this->class_instr->stack->set_symbol($arg_1->nodeValue, $result, "int");
    }

    // CONCAT
    public function instr_concat() : void {
        $this->class_instr->check_arguments($this->instr);

        $arg_1 = $this->instr->getElementsByTagName('arg1')->item(0);
        $arg_2 = $this->instr->getElementsByTagName('arg2')->item(0);
        $arg_3 = $this->instr->getElementsByTagName('arg3')->item(0);

        if(!($arg_1->hasAttribute('type'))){
            throw new Interexception("ERROR: Tag is empty", 32);
        }
        if(!($arg_2->hasAttribute('type'))){
            throw new Interexception("ERROR: Tag is empty", 32);
        }
        if(!($arg_3->hasAttribute('type'))){
            throw new Interexception("ERROR: Tag is empty", 32);
        }
        if($arg_1->getAttribute('type') != "var"){
            throw new Interexception("ERROR: Wrong type of structure", 32);
        }

        $arg2_symb = $this->check_type($arg_2);
        $arg3_symb = $this->check_type($arg_3);

        if(($arg2_symb[0] == null || $arg3_symb[0] == null)){
            throw new Interexception("ERROR:No type", 56);
        }

        if(($arg2_symb[0] != "string" || $arg3_symb[0] != "string")){
            throw new Interexception("ERROR: Wrong operand type", 53);
        }

        $result = $arg2_symb[1] . $arg3_symb[1];

        $this->class_instr->stack->set_symbol($arg_1->nodeValue, $result, "string");
    }

    // STRLEN
    public function instr_strlen() : void {
        $this->class_instr->check_arguments($this->instr);

        $arg_1 = $this->instr->getElementsByTagName('arg1')->item(0);
        $arg_2 = $this->instr->getElementsByTagName('arg2')->item(0);

        if(!($arg_1->hasAttribute('type'))){
            throw new Interexception("ERROR: Tag is empty", 32);
        }
        if(!($arg_2->hasAttribute('type'))){
            throw new Interexception("ERROR: Tag is empty", 32);
        }
        if($arg_1->getAttribute('type') != "var"){
            throw new Interexception("ERROR: Wrong type of structure", 32);
        }

        $arg2_symb = $this->check_type($arg_2);

        if($arg2_symb[0] == null){
            throw new Interexception("ERROR: No type", 56);
        }

        if($arg2_symb[1] == null){
            $arg1_symb[1] = "";
        }

        //add string converter!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!

        if($arg2_symb[0] != "string"){
            throw new Interexception("ERROR: Wrong operand type", 53);
        }

        $result = strlen($arg2_symb[1]);

        $this->class_instr->stack->set_symbol($arg_1->nodeValue, $result, "int");
    }

    // GETCHAR
    public function instr_getchar() : void {
        $this->class_instr->check_arguments($this->instr);

        $arg_1 = $this->instr->getElementsByTagName('arg1')->item(0);
        $arg_2 = $this->instr->getElementsByTagName('arg2')->item(0);
        $arg_3 = $this->instr->getElementsByTagName('arg3')->item(0);

        if(!($arg_1->hasAttribute('type'))){
            throw new Interexception("ERROR: Tag is empty", 32);
        }
        if(!($arg_2->hasAttribute('type'))){
            throw new Interexception("ERROR: Tag is empty", 32);
        }
        if(!($arg_3->hasAttribute('type'))){
            throw new Interexception("ERROR: Tag is empty", 32);
        }
        if($arg_1->getAttribute('type') != "var"){
            throw new Interexception("ERROR: Wrong operand type", 32);
        }

        $arg2_symb = $this->check_type($arg_2);
        $arg3_symb = $this->check_type($arg_3);

        if($arg2_symb[0] == null || $arg3_symb[0] == null){
            throw new Interexception("ERROR: No type", 56);
        }

        if(($arg2_symb[0] != "string" || $arg3_symb[0] != "int")){
            throw new Interexception("ERROR: Wrong operand type", 53);
        }

        if(!(is_numeric($arg3_symb[1])) || (int)$arg3_symb[1] < 0){
            throw new Interexception("ERROR: Wrong operand type for this operation", 58);
        }

        if((int)$arg3_symb[1] >= strlen($arg2_symb[1]) || $arg2_symb[1] == ""){
            throw new Interexception("ERROR: Wrong operand type for this operation", 58);
        }

        $position = (int) $arg3_symb[1];
        $result = $arg2_symb[1][$position];

        $this->class_instr->stack->set_symbol($arg_1->nodeValue, $result, "string");
    }

    // SETCHAR
    public function instr_setchar() : void {
        $this->class_instr->check_arguments($this->instr);

        $arg_1 = $this->instr->getElementsByTagName('arg1')->item(0);
        $arg_2 = $this->instr->getElementsByTagName('arg2')->item(0);
        $arg_3 = $this->instr->getElementsByTagName('arg3')->item(0);

        if(!($arg_1->hasAttribute('type'))){
            throw new Interexception("ERROR: Tag is empty", 32);
        }
        if(!($arg_2->hasAttribute('type'))){
            throw new Interexception("ERROR: Tag is empty", 32);
        }
        if(!($arg_3->hasAttribute('type'))){
            throw new Interexception("ERROR: Tag is empty", 32);
        }
        if($arg_1->getAttribute('type') != "var"){
            throw new Interexception("ERROR: Wrong type of structure", 32);
        }

        $arg1_symb = $this->check_type($arg_1);
        $arg2_symb = $this->check_type($arg_2);
        $arg3_symb = $this->check_type($arg_3);

        if($arg3_symb[1] == null){
            throw new Interexception("ERROR: No value", 58);
        }

        if($arg1_symb[0] == null || $arg2_symb[0] == null || $arg3_symb[0] == null){
            throw new Interexception("ERROR: No type", 56);
        }

        if($arg1_symb[0] != "string" ||  $arg2_symb[0] != "int" || $arg3_symb[0] != "string"){
            throw new Interexception("ERROR: Wrong operand type", 53);
        }

        if(!(is_numeric($arg2_symb[1])) || (int)$arg2_symb[1] < 0){
            throw new Interexception("ERROR: Wrong operand type for this operation", 58);
        }

        if((int)$arg2_symb[1] >= strlen($arg1_symb[1]) || $arg3_symb[1] == ""){
            throw new Interexception("ERROR: Wrong operand type for this operation", 58);
        }

        $result = substr($arg1_symb[1], 0, intval($arg2_symb[1])) . $arg3_symb[1][0] . substr($arg1_symb[1], intval($arg2_symb[1]) + 1);

        $this->class_instr->stack->set_symbol($arg_1->nodeValue, $result, "string");
    }

    // LABEL
    public function instr_label() : void {
        $this->class_instr->check_arguments($this->instr);
    }

    // JUMP
    public function instr_jump() : int {
        $this->class_instr->check_arguments($this->instr);

        $arg_1 = $this->instr->getElementsByTagName('arg1')->item(0);

        if(!($arg_1->hasAttribute('type'))){
            throw new Interexception("ERROR: Tag is empty", 32);
        }
        if($arg_1->getAttribute('type') != "label"){
            throw new Interexception("ERROR: Wrong type of structure", 32);
        }
        $arg1_symb = $this->check_type($arg_1);
        //echo $arg1_symb[0] . " " . $arg1_symb[1] . "\n";

        return $arg1_symb[0] - 1;

    }

    //JUMPIFEQ
    public function instr_jumpifeq(int $step_old) : int {
        $this->class_instr->check_arguments($this->instr);

        $arg_1 = $this->instr->getElementsByTagName('arg1')->item(0);
        $arg_2 = $this->instr->getElementsByTagName('arg2')->item(0);
        $arg_3 = $this->instr->getElementsByTagName('arg3')->item(0);

        if(!($arg_1->hasAttribute('type'))){
            throw new Interexception("ERROR: Tag is empty", 32);
        }
        if(!($arg_2->hasAttribute('type'))){
            throw new Interexception("ERROR: Tag is empty", 32);
        }
        if(!($arg_3->hasAttribute('type'))){
            throw new Interexception("ERROR: Tag is empty", 32);
        }
        if($arg_1->getAttribute('type') != "label"){
            throw new Interexception("ERROR: Wrong type of structure", 32);
        }

        $arg1_symb = $this->check_type($arg_1);
        $arg2_symb = $this->check_type($arg_2);
        $arg3_symb = $this->check_type($arg_3);

        if($arg2_symb[0] == null || $arg3_symb[0] == null){
            throw new Interexception("ERROR: No type", 56);
        }

        if($arg2_symb[0] == $arg3_symb[0] || $arg2_symb[0] == "nil" || $arg3_symb[0] == "nil"){
            if($arg2_symb[1] == $arg3_symb[1]){
                return $arg1_symb[0] - 1;
            }else{
                return $step_old;
            }
        }else{
            throw new Interexception("ERROR: Wrong arguments", 53);
        }
    }

    //JUMPIFNEQ
    public function instr_jumpifneq(int $step_old) : int {
        $this->class_instr->check_arguments($this->instr);

        $arg_1 = $this->instr->getElementsByTagName('arg1')->item(0);
        $arg_2 = $this->instr->getElementsByTagName('arg2')->item(0);
        $arg_3 = $this->instr->getElementsByTagName('arg3')->item(0);

        if(!($arg_1->hasAttribute('type'))){
            throw new Interexception("ERROR: Tag is empty", 32);
        }
        if(!($arg_2->hasAttribute('type'))){
            throw new Interexception("ERROR: Tag is empty", 32);
        }
        if(!($arg_3->hasAttribute('type'))){
            throw new Interexception("ERROR: Tag is empty", 32);
        }
        if($arg_1->getAttribute('type') != "label"){
            throw new Interexception("ERROR: Wrong type of structure", 32);
        }

        $arg1_symb = $this->check_type($arg_1);
        $arg2_symb = $this->check_type($arg_2);
        $arg3_symb = $this->check_type($arg_3);

        if($arg2_symb[0] == null || $arg3_symb[0] == null){
            throw new Interexception("ERROR: No value", 56);
        }

        if($arg2_symb[0] == $arg3_symb[0] || $arg2_symb[0] == "nil" || $arg3_symb[0] == "nil"){
            if($arg2_symb[1] != $arg3_symb[1]){
                return $arg1_symb[0] - 1;
            }else{
                return $step_old;
            }
        }else{
            throw new Interexception("ERROR: Wrong arguments", 53);
        }
    }

    // EXIT
    public function instr_exit() : void {
        $this->class_instr->check_arguments($this->instr);

        $arg_1 = $this->instr->getElementsByTagName('arg1')->item(0);

        if(!($arg_1->hasAttribute('type'))){
            throw new Interexception("ERROR: Tag is empty", 32);
        }
        
        $arg1_symb = $this->check_type($arg_1);

        if($arg1_symb[0] != "int"){
            throw new Interexception("ERROR: Wrong type of argument", 53);
        }

        if($arg1_symb[1] == null){
            throw new Interexception("ERROR: No value", 56);
        }

        if(!(is_numeric($arg1_symb[1])) || !($arg1_symb[1] >= 0 && $arg1_symb[1] <= 9)){
            throw new Interexception("ERROR: Wrong exit code", 57);
        }

        $exit_code = (int) $arg1_symb[1];
        exit($exit_code);
    }

    // TYPE
    public function instr_type() : void {
        $this->class_instr->check_arguments($this->instr);

        $arg_1 = $this->instr->getElementsByTagName('arg1')->item(0);
        $arg_2 = $this->instr->getElementsByTagName('arg2')->item(0);

        if(!($arg_1->hasAttribute('type'))){
            throw new Interexception("ERROR: Tag is empty", 32);
        }
        if(!($arg_2->hasAttribute('type'))){
            throw new Interexception("ERROR: Tag is empty", 32);
        }
        if($arg_1->getAttribute('type') != "var"){
            throw new Interexception("ERROR: Wrong type of structure", 32);
        }
        
        //$arg1_symb = $this->check_type($arg_1);
        $arg2_symb = $this->check_type($arg_2);

        if($arg2_symb[0] == "var"){
            if($arg2_symb[1] == null){
                $result = "";
            }else{
                $result = $arg2_symb[1];
            }
        }else{
            $result = $arg2_symb[0];
        }

        $this->class_instr->stack->set_symbol($arg_1->nodeValue, $result, "string");
    }

    // CREATEFRAME
    public function instr_createframe() : void {
        $this->class_instr->check_arguments($this->instr);
        $this->class_instr->stack->create_temp_frame();
    }

    // PUSHFRAME
    public function instr_pushframe() : void {
        $this->class_instr->check_arguments($this->instr);
        if($this->class_instr->stack->is_created == false){
            throw new Interexception("ERROR: frame is not defined", 55);
        }
        
        array_push($this->class_instr->stack->local_frames, $this->class_instr->stack->temp_frames);
        $this->class_instr->stack->temp_frames = array();
        $this->class_instr->stack->is_created = false;
    }

    // POPFRAME
    public function instr_popframe() : void {
        $this->class_instr->check_arguments($this->instr);
        //print_r($this->class_instr->stack->local_frames);
        //echo sizeof($this->class_instr->stack->local_frames) . "\n";
        if(sizeof($this->class_instr->stack->local_frames) == 0){
            throw new Interexception("ERROR: frame is not defined or empty", 55);
        }
        $tmp_value = array_pop($this->class_instr->stack->local_frames);
        $this->class_instr->stack->temp_frames = $tmp_value;
    }

    public function instr_call() : void {
        $this->class_instr->check_arguments($this->instr);

        $arg_1 = $this->instr->getElementsByTagName('arg1')->item(0);

        if(!($arg_1->hasAttribute('type'))){
            throw new Interexception("ERROR: Tag is empty", 32);
        }
        if($arg_1->getAttribute('type') != "label"){
            throw new Interexception("ERROR: Wrong type of structure", 32);
        }

        $arg1_symb = $this->check_type($arg_1);
        //echo $arg1_symb[0] . " - " . $arg1_symb[1] . "\n";
        $order = $arg1_symb[1];

        //array_push($this->call_instr->stack->callStack, )
    }

    public function execute(int $step) : int{
        $all_instr = array("MOVE", "CREATEFRAME", "PUSHFRAME", "POPFRAME", "DEFVAR", "CALL", "RETURN", 
                            "PUSHS", "POPS", 
                            "ADD", "SUB", "MUL", "IDIV", "LT", "GT", "EQ", "AND", "OR", "NOT", "INT2CHAR", "STR2INT", 
                            "READ", "WRITE", 
                            "CONCAT", "STRLEN", "GETCHAR", "SETCHAR", 
                            "TYPE", 
                            "LABEL", "JUMP", "JUMPIFEQ", "JUMPIFNEQ", "EXIT",
                            "DPRINT", "BREAK");
        if(in_array($this->opcode, $all_instr)){
            if($this->opcode == "DEFVAR"){
                $this->instr_defvar();
            }
            
            if($this->opcode == "WRITE"){
                $this->instr_write();
            }

            if($this->opcode == "MOVE"){
                $this->instr_move();
            }

            if($this->opcode == "ADD" || $this->opcode == "SUB" || $this->opcode == "MUL" || $this->opcode == "IDIV"){
                $this->instr_math($this->opcode);
            }
            
            if($this->opcode == "LT" || $this->opcode == "GT" || $this->opcode == "EQ"){
                $this->instr_rel($this->opcode);
            }

            if($this->opcode == "AND" || $this->opcode == "OR" || $this->opcode == "NOT"){
                $this->instr_bool($this->opcode);
            }

            if($this->opcode == "INT2CHAR"){
                $this->instr_int2char();
            }

            if($this->opcode == "STR2INT"){
                $this->instr_str2int();
            }

            if($this->opcode == "CONCAT"){
                $this->instr_concat();
            }

            if($this->opcode == "STRLEN"){
                $this->instr_strlen();
            }

            if($this->opcode == "GETCHAR"){
                $this->instr_getchar();
            }

            if($this->opcode == "SETCHAR"){
                $this->instr_setchar();
            }

            if($this->opcode == "LABEL"){
                $this->instr_label();
            }

            if($this->opcode == "JUMP"){
                $return_order = $this->instr_jump();
                $step = $return_order;
            }

            if($this->opcode == "JUMPIFEQ"){
                $return_order = $this->instr_jumpifeq($step);
                $step = $return_order;
            }

            if($this->opcode == "JUMPIFNEQ"){
                $return_order = $this->instr_jumpifneq($step);
                $step = $return_order;
            }

            if($this->opcode == "EXIT"){
                $this->instr_exit();
            }

            if($this->opcode == "TYPE"){
                $this->instr_type();
            }

            if($this->opcode == "CREATEFRAME"){
                $this->instr_createframe();
            }

            if($this->opcode == "PUSHFRAME"){
                $this->instr_pushframe();
            }

            if($this->opcode == "POPFRAME"){
                $this->instr_popframe();
            }

            if($this->opcode == "CALL"){
                $this->instr_call();
            }

        }
        else{
            throw new Interexception("ERROR: Unexpected opcode", 32);
        }

        return $step;
    } 

}

class Stack{
    /** @var array<string, array <string, string>> */
    public array $frames = array('GF' => array());
    /** @var array<string, string> */
    public array $types = array();

    public bool $is_created = false;

    public array $local_frames = array();

    public array $temp_frames;

    public array $callStack;

    public function __construct(){
    }

    public function push_frame(mixed $input) : void{
        if($input[0] == 'GF'){
            if(array_key_exists($input[1], $this->frames['GF'])){
                throw new Interexception("ERROR: Repeated definition of the variable", 52);
            }
            $this->frames['GF'][$input[1]] = null;
        }
        elseif($input[0] == 'TF'){
            if($this->is_created == false){
                throw new Interexception("ERROR: Cannot define variable with this type of frame", 55);
            }else{
                if(array_key_exists($input[1], $this->temp_frames['TF'])){
                    throw new Interexception("ERROR: Repeated definition of the variable", 52);
                }
                $this->temp_frames['TF'][$input[1]] = null;
            }
            
        }
    }

    public function create_temp_frame() : void{
        $this->temp_frames = array('TF' => array());
        $this->is_created = true;
    }

    public function get_symbol(mixed $input) : string {
        if($input[0] == 'GF'){
            if(array_key_exists($input[1], $this->frames['GF'])){
                if($this->frames['GF'][$input[1]] == null){
                    return "";
                }
                return $this->frames['GF'][$input[1]];
            }else{
                throw new Interexception("ERROR: Nonexisting variable", 54);
            }
        }elseif($input[0] == 'LF'){
            for ($i=sizeof($this->local_frames) - 1; $i >= 0; $i--) { 
                if(array_key_exists($input[1], $this->local_frames[$i]['TF'])){
                    if($this->local_frames[$i]['TF'][$input[1]] == null){
                        return "";
                    }
                    return $this->local_frames[$i]['TF'][$input[1]];
                }
            }
            throw new Interexception("ERROR: Nonexisting variable", 54);            
        }
        elseif($input[0] == 'TF'){
            if(sizeof($this->temp_frames) > 0){
                if(array_key_exists($input[1], $this->temp_frames['TF'])){
                    if($this->temp_frames['TF'][$input[1]] == null){
                        return "";
                    }
                    return $this->temp_frames['TF'][$input[1]];
                }else{
                    throw new Interexception("ERROR: Nonexisting variable", 54);
                }
            }else{
                throw new Interexception("ERROR: Nonexisting frame", 55);
            }
            
                        
        }
        

        throw new Interexception("ERROR: Trying to reach undefined variable", 52);
    }

    public function get_type(mixed $input) : string {
        if(array_key_exists($input, $this->types)){
            return $this->types[$input];
        }elseif(array_key_exists($input, $this->frames['GF'])){
            return "";
        }
        else{
            throw new Interexception("ERROR: Nonexisting type", 54);
        }    
    }

    public function set_symbol(string $variable, string $symbol, string $type) : void {
        $parts = explode("@", $variable);  
        if($parts[0] == 'GF'){
            if(!array_key_exists($parts[1], $this->frames['GF'])){
                throw new Interexception("ERROR: Nonexisting type", 54);
            }
            $this->frames['GF'][$parts[1]] = $symbol;
            $this->types[$parts[1]] = $type;
        } 
        if($parts[0] == 'TF'){
            if(!array_key_exists($parts[1], $this->temp_frames['TF'])){
                throw new Interexception("ERROR: Nonexisting type", 54);
            }
            $this->temp_frames['TF'][$parts[1]] = $symbol;
            $this->types[$parts[1]] = $type;
        }
    }

}



class Instructions{
    /** @var array<string, int> */
    public array $all_labels;
    public object $input_file;
    public mixed $stack;
    public mixed $write_stdout;
    public string $program_output;

    /** @var array<string> */
    public array $threeArgs = array("ADD", "SUB", "MUL", "IDIV", "LT", "GT", "EQ", "AND", "OR", "STRI2INT", 
        "CONCAT", "GETCHAR", "SETCHAR", "JUMPIFEQ", "JUMPIFNEQ");

    /** @var array<string> */
    public array $twoArgs = array("MOVE", "INT2CHAR", "READ", "STRLEN", "TYPE", "NOT");

    /** @var array<string> */
    public array $oneArgs = array("LABEL", "JUMP", "EXIT", "WRITE", "PUSHS", "POPS", "DEFVAR", "CALL", "DPRINT");

    /** @var array<string> */
    public array $zeroArgs = array("CREATEFRAME", "PUSHFRAME", "POPFRAME", "RETURN", "BREAK");
    /**
     * Constructor for the Instructions class.
     *
     * @param array<string, int> $all_labels An associative array containing keys and values.
     * @param object $input_file The input file object.
     */
    public function __construct(array $all_labels, object $input_file, $write_stdout){
        $this->all_labels = $all_labels;
        $this->input_file = $input_file;
        $this->stack = new Stack();
        $this->program_output = "";
        $this->write_stdout = $write_stdout;
    }

    public function check_arguments(mixed $opcode) : void {
        if(in_array($opcode->getAttribute('opcode'), $this->threeArgs)){
            $arg = $opcode->getElementsByTagName('*');
            if(sizeof($arg) != 3){
                throw new Interexception("ERROR: Wrong number of arguments", 32);
            }
            foreach ($arg as $i) {
                if (strpos($i->tagName, 'arg') === false) {
                    throw new Interexception("ERROR: Wrong tag name", 32);
                }
            }
        }
        elseif(in_array($opcode->getAttribute('opcode'), $this->twoArgs)){
            $arg = $opcode->getElementsByTagName('*');
            if(sizeof($arg) != 2){
                throw new Interexception("ERROR: Wrong number of arguments", 32);
            }
            foreach ($arg as $i) {
                if (strpos($i->tagName, 'arg') === false) {
                    throw new Interexception("ERROR: Wrong tag name", 32);
                }
            }
        }
        elseif(in_array($opcode->getAttribute('opcode'), $this->oneArgs)){
            $arg = $opcode->getElementsByTagName('*');
            if(sizeof($arg) != 1){
                throw new Interexception("ERROR: Wrong number of arguments", 32);
            }
            foreach ($arg as $i) {
                if (strpos($i->tagName, 'arg1') === false) {
                    throw new Interexception("ERROR: Wrong tag name", 32);
                }
            }
        }
        elseif(in_array($opcode->getAttribute('opcode'), $this->zeroArgs)){
            $arg = $opcode->getElementsByTagName('*');
            if(sizeof($arg) != 0){
                throw new Interexception("ERROR: Wrong number of arguments", 32);
            }
        }
    }

    function start_interpreter(mixed $instr, int $step) : int{
        $actual_instr = new Execute($instr->getAttribute('opcode'), $instr, $this);
        $step = $actual_instr->execute($step);
        //print_r($this->stack->frames);
        //print_r($this->stack->temp_frames);
        //print_r($this->stack->local_frames);
        //print_r($this->stack->types);
        return $step;
    }

}