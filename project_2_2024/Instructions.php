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
    /**
    * Method for receiving and returning information(type and value) about variable
    */
    public function get_data(mixed $symbol) : mixed {
        if($symbol->getAttribute('type') == "var"){
            $parts = explode("@", $symbol->nodeValue);
            $value = $this->class_instr->stack->get_symbol($parts);
            $type = $this->class_instr->stack->get_type($parts);
            
            if($value == null){
                if($this->opcode != "TYPE"){
                    if($type != "string"){
                        throw new Interexception("ERROR: No value", 56);
                    }
                }else{
                    $return_value = null;
                    $return_type = "var";
                    return array($return_type, $return_value);
                }
            }
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
    }

    // WRITE
    public function instr_write() : void {
        $this->class_instr->check_arguments($this->instr);

        $arg_1 = $this->instr->getElementsByTagName('arg1')->item(0);

        if (!($arg_1->hasAttribute('type'))) {
            throw new Interexception("ERROR: Tag is empty", 32);
        }

        $arg1_symb = $this->get_data($arg_1);

        if($arg_1->getAttribute('type') == "var"){
            if($arg1_symb[0] == "int"){
                $this->class_instr->write_stdout->writeInt((int) $arg1_symb[1]);
            }else if($arg1_symb[0] == "nil"){
                $this->class_instr->write_stdout->writeString("");
            }else if($arg1_symb[0] == "string"){
                $new_value = $this->class_instr->convert_escape($arg1_symb[1]);
                $this->class_instr->write_stdout->writeString($new_value);
            }else if($arg1_symb[0] == "bool"){
                $this->class_instr->write_stdout->writeString($arg1_symb[1]);
            }
             
        }
        else{
            if($arg_1->getAttribute('type') == "int"){
                $this->class_instr->write_stdout->writeInt((int) $arg1_symb[1]);
            }else if($arg_1->getAttribute('type') == "nil"){
                $this->class_instr->write_stdout->writeString("");
            }else if($arg_1->getAttribute('type') == "string"){
                $new_value = $this->class_instr->convert_escape($arg1_symb[1]);
                $this->class_instr->write_stdout->writeString($new_value);
            }else if($arg_1->getAttribute('type') == "bool"){
                $this->class_instr->write_stdout->writeString($arg1_symb[1]);
            }
        }
    }

    // MOVE
    public function instr_move() : void {
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

        $arg2_symb = $this->get_data($arg_2);
        if($arg2_symb[0] == null){
            throw new Interexception("ERROR: No type", 56);
        }

        if($arg2_symb[0] == "string" && ($arg2_symb[1] == null || $arg2_symb[1] == "")){
            $arg2_symb[1] = "";
        }

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

        $arg2_symb = $this->get_data($arg_2);
        $arg3_symb = $this->get_data($arg_3);

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

        $arg2_symb = $this->get_data($arg_2);
        $arg3_symb = $this->get_data($arg_3);


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

                    if($result == "1"){
                        $result = "true";
                    }else{
                        $result = "false";
                    }
                    $this->class_instr->stack->set_symbol($arg_1->nodeValue, $result, "bool");
                }

                if($arg2_symb[0] == "bool"){
                    if(($arg2_symb[1] != "true" && $arg2_symb[1] != "false") || ($arg3_symb[1] != "true" && $arg3_symb[1] != "false")){
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

                    $tmp_result = $arg2_symb[1] == $arg3_symb[1];
                    $result = strtolower((string) $tmp_result);

                    if($result == "1"){
                        $result = "true";
                    }else{
                        $result = "false";
                    }

                    $this->class_instr->stack->set_symbol($arg_1->nodeValue, $result, "bool");
                }

                if($arg2_symb[0] == "string"){
                    $arg2_symb[1] = $this->class_instr->convert_escape($arg2_symb[1]);
                    $arg3_symb[1] = $this->class_instr->convert_escape($arg3_symb[1]);
                    $tmp_result = $arg2_symb[1] == $arg3_symb[1];
                    $result = strtolower((string) $tmp_result);

                    if($result == "1"){
                        $result = "true";
                    }else{
                        $result = "false";
                    }

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
                if(($arg2_symb[1] != "true" && $arg2_symb[1] != "false") || ($arg3_symb[1] != "true" && $arg3_symb[1] != "false")){
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

            if($arg2_symb[0] == "string"){
                $arg2_symb[1] = $this->class_instr->convert_escape($arg2_symb[1]);
                $arg3_symb[1] = $this->class_instr->convert_escape($arg3_symb[1]);
            }

            if($opcode == "GT"){
                $tmp_result = (string) ($arg2_symb[1] > $arg3_symb[1]);
                $result = strtolower((string) $tmp_result);
                if($result == "1"){
                    $result = "true";
                }else{
                    $result = "false";
                }
                $this->class_instr->stack->set_symbol($arg_1->nodeValue, $result, "bool");
            }
            else{
                $tmp_result = (string) ($arg2_symb[1] < $arg3_symb[1]);
                $result = strtolower((string) $tmp_result);
                if($result == "1"){
                    $result = "true";
                }else{
                    $result = "false";
                }
                $this->class_instr->stack->set_symbol($arg_1->nodeValue, $result, "bool");
            }   
        }
    }

    // AND, OR, NOT
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
    
            $arg2_symb = $this->get_data($arg_2);

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
    
            $arg2_symb = $this->get_data($arg_2);
            $arg3_symb = $this->get_data($arg_3);
            
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

        $arg2_symb = $this->get_data($arg_2);

        if($arg2_symb[0] == null){
            throw new Interexception("ERROR: No type", 56);
        }

        if($arg2_symb[0] != "int"){
            throw new Interexception("ERROR: Wrong operand type", 53);
        }
        
        if(!(is_numeric($arg2_symb[1]))){
            throw new Interexception("ERROR: Wrong operand type for this operation", 58);
        }

        if((int) $arg2_symb[1] < 0 || (int) $arg2_symb[1] > 1114111){
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

    // STRI2INT
    public function instr_stri2int() : void {
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

        $arg2_symb = $this->get_data($arg_2);
        $arg3_symb = $this->get_data($arg_3);

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

        $arg2_symb = $this->get_data($arg_2);
        $arg3_symb = $this->get_data($arg_3);

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

        $arg2_symb = $this->get_data($arg_2);

        if($arg2_symb[0] == null){
            throw new Interexception("ERROR: No type", 56);
        }

        if($arg2_symb[1] == null){
            $arg1_symb[1] = "";
        }

        $arg2_symb[1] = $this->class_instr->convert_escape($arg2_symb[1]);

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

        $arg2_symb = $this->get_data($arg_2);
        $arg3_symb = $this->get_data($arg_3);

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

        $arg1_symb = $this->get_data($arg_1);
        $arg2_symb = $this->get_data($arg_2);
        $arg3_symb = $this->get_data($arg_3);

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
        
        $arg3_symb[1] = $this->class_instr->convert_escape($arg3_symb[1]);

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
        $arg1_symb = $this->get_data($arg_1);

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

        $arg1_symb = $this->get_data($arg_1);
        $arg2_symb = $this->get_data($arg_2);
        $arg3_symb = $this->get_data($arg_3);

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

        $arg1_symb = $this->get_data($arg_1);
        $arg2_symb = $this->get_data($arg_2);
        $arg3_symb = $this->get_data($arg_3);

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
        
        $arg1_symb = $this->get_data($arg_1);

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
        
        $arg2_symb = $this->get_data($arg_2);

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
        if(sizeof($this->class_instr->stack->local_frames) == 0){
            throw new Interexception("ERROR: frame is not defined or empty", 55);
        }
        $tmp_value = array_pop($this->class_instr->stack->local_frames);
        $this->class_instr->stack->temp_frames = $tmp_value;
    }

    // CALL
    public function instr_call(int $step_old) : int {
        $this->class_instr->check_arguments($this->instr);

        $arg_1 = $this->instr->getElementsByTagName('arg1')->item(0);

        if(!($arg_1->hasAttribute('type'))){
            throw new Interexception("ERROR: Tag is empty", 32);
        }
        if($arg_1->getAttribute('type') != "label"){
            throw new Interexception("ERROR: Wrong type of structure", 32);
        }

        $arg1_symb = $this->get_data($arg_1);
        $order = $arg1_symb[0];
        array_push($this->class_instr->stack->callStack, $step_old + 1);
        return $order - 1;
    }

    // RETURN
    public function instr_return() : int {
        $this->class_instr->check_arguments($this->instr);

        if(sizeof($this->class_instr->stack->callStack) == 0){
            throw new Interexception("ERROR: Stack of types is empty", 56);
        }

        $order = end($this->class_instr->stack->callStack);
        array_pop($this->class_instr->stack->callStack);

        return $order - 1;
    }

    // PUSHS
    public function instr_pushs() : void{
        $this->class_instr->check_arguments($this->instr);

        $arg_1 = $this->instr->getElementsByTagName('arg1')->item(0);

        if(!($arg_1->hasAttribute('type'))){
            throw new Interexception("ERROR: Tag is empty", 32);
        }

        $arg1_symb = $this->get_data($arg_1);
        array_push($this->class_instr->stack->general_stack, $arg1_symb[1]);
    }

    // POPS
    public function instr_pops() : void{
        $this->class_instr->check_arguments($this->instr);

        $arg_1 = $this->instr->getElementsByTagName('arg1')->item(0);

        if(!($arg_1->hasAttribute('type'))){
            throw new Interexception("ERROR: Tag is empty", 32);
        }
        if($arg_1->getAttribute('type') != "var"){
            throw new Interexception("ERROR: Wrong type of structure", 32);
        }

        if(sizeof($this->class_instr->stack->general_stack) == 0){
            throw new Interexception("ERROR: Stack of types is empty", 56);
        }
        
        $item = end($this->class_instr->stack->general_stack);
        array_pop($this->class_instr->stack->general_stack);
        
        if(is_numeric($item) == true){
            $data_type = "int";
        }elseif($item == "true" || $item == "false"){
            $data_type = "bool";
        }elseif($item == "nil"){
            $data_type = "nil";
        }else{
            $data_type = "string";
        }
        
        $this->class_instr->stack->set_symbol($arg_1->nodeValue, $item, $data_type);
    }

    // READ
    public function instr_read() : void{
        $this->class_instr->check_arguments($this->instr);

        $arg_1 = $this->instr->getElementsByTagName('arg1')->item(0);
        $arg_2 = $this->instr->getElementsByTagName('arg2')->item(0);

        if(!($arg_1->hasAttribute('type'))){
            throw new Interexception("ERROR: Tag is empty", 32);
        }
        
        if($arg_1->getAttribute('type') != "var"){
            throw new Interexception("ERROR: Wrong type of structure", 32);
        }

        if(!($arg_2->hasAttribute('type'))){
            throw new Interexception("ERROR: Tag is empty", 32);
        }

        if($arg_2->getAttribute('type') != "type"){
            throw new Interexception("ERROR: Wrong type of structure", 32);
        }

        if($arg_2->nodeValue != "int" && $arg_2->nodeValue != "string" && $arg_2->nodeValue != "bool"){
            throw new Interexception("ERROR: Wrong value of argument", 57);
        }

        if($arg_2->nodeValue == "bool"){
            $tmp = $this->class_instr->input_file->readBool();
            if(gettype($tmp) == "boolean" || gettype($tmp) == "NULL"){
                if($tmp == "1"){
                    $tmp = "true";
                }else{
                    $tmp = "false";
                }
                $type = "bool";
            }else{
                $tmp = "nil";
                $type = "nil";
            }
            
        }
        elseif($arg_2->nodeValue == "int"){
            $tmp = $this->class_instr->input_file->readInt();
            if(gettype($tmp) == "integer"){
                $type = "int";
            }else{
                $tmp = "nil";
                $type = "nil";
            }
        }
        elseif($arg_2->nodeValue == "string"){
            $tmp = $this->class_instr->input_file->readString();
            if(strlen($tmp) > 0 && gettype($tmp) == "string"){
                $tmp = $this->class_instr->convert_escape($tmp);
                $type = "string";
            }elseif(strlen($tmp) == 0 && gettype($tmp) == "string"){
                $type = "string";
            }else{
                $tmp = "nil";
                $type = "nil";
            }
        }else{
            $tmp = "nil";
            $type = "nil";
        }

        $this->class_instr->stack->set_symbol($arg_1->nodeValue, $tmp, $type);

    }

    // DPRINT
    public function instr_dprint() : void{
        $this->class_instr->check_arguments($this->instr);

        $arg_1 = $this->instr->getElementsByTagName('arg1')->item(0);

        if(!($arg_1->hasAttribute('type'))){
            throw new Interexception("ERROR: Tag is empty", 32);
        }

        $arg1_symb = $this->get_data($arg_1);

        if($arg_1->getAttribute('type') == "var"){
            if($arg1_symb[0] == "int"){
                $this->class_instr->write_stderr->writeInt((int) $arg1_symb[1]);
            }else if($arg1_symb[0] == "nil"){
                $this->class_instr->write_stderr->writeString("");
            }else if($arg1_symb[0] == "string"){ 
                $new_value = $this->class_instr->convert_escape($arg1_symb[1]);
                $this->class_instr->write_stderr->writeString($new_value);
            }else if($arg1_symb[0] == "bool"){
                $this->class_instr->write_stderr->writeString($arg1_symb[1]);
            }
             
        }
        else{
            if($arg_1->getAttribute('type') == "int"){
                $this->class_instr->write_stderr->writeInt((int) $arg_1->nodeValue);
            }else if($arg_1->getAttribute('type') == "nil"){
                $this->class_instr->write_stderr->writeString("");
            }else if($arg_1->getAttribute('type') == "string"){
                $new_value = $this->class_instr->convert_escape($arg_1->nodeValue);
                $this->class_instr->write_stderr->writeString($new_value);
            }else if($arg_1->getAttribute('type') == "bool"){ 
                $this->class_instr->write_stderr->writeString($arg_1->nodeValue);
            }
        }
    }

    // BREAK
    public function instr_break(int $actual_step) : void{
        $this->class_instr->check_arguments($this->instr);
        $pos_code = "\nPosition in code: " . $actual_step . "\n";
        $global_frame = "GF: "  . print_r($this->class_instr->stack->frames['GF'], true);
        $tmp_frames = "TF: "  . print_r($this->class_instr->stack->temp_frames, true);
        $lcl_frames = "LF: "  . print_r($this->class_instr->stack->local_frames, true);

        $result = $pos_code . $global_frame . $tmp_frames . $lcl_frames;

        $this->class_instr->write_stderr->writeString($result);
    }

    /**
    * Function for processing the opcodes 
    */
    public function execute(int $step) : int{
        $all_instr = array("MOVE", "CREATEFRAME", "PUSHFRAME", "POPFRAME", "DEFVAR", "CALL", "RETURN", 
                            "PUSHS", "POPS", 
                            "ADD", "SUB", "MUL", "IDIV", "LT", "GT", "EQ", "AND", "OR", "NOT", "INT2CHAR", "STRI2INT", 
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

            if($this->opcode == "STRI2INT"){
                $this->instr_stri2int();
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
                $return_order = $this->instr_call($step);
                $step = $return_order;
            }

            if($this->opcode == "RETURN"){
                $return_order = $this->instr_return();
                $step = $return_order;
            }

            if($this->opcode == "PUSHS"){
                $this->instr_pushs();
            }

            if($this->opcode == "POPS"){
                $this->instr_pops();
            }

            if($this->opcode == "READ"){
                $this->instr_read();
            }

            if($this->opcode == "DPRINT"){
                $this->instr_dprint();
            }

            if($this->opcode == "BREAK"){
                $this->instr_break($step);
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

    /** @var array<array <string, array <string, string>>> */
    public array $local_frames = array();

    /** @var array<array <string, string>> */
    public array $temp_frames = [];

    /** @var array<int> */
    public array $callStack = [];

    /** @var array<array <string, string>> */
    public array $general_stack = [];
    

    public function __construct(){
    }

    /**
    * Method for pushing variables into a frame
    */
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
            
        }elseif($input[0] == 'LF'){
            if(sizeof($this->local_frames) > 0){
                for ($i=sizeof($this->local_frames) - 1; $i >= 0; $i--) { 
                    if(array_key_exists($input[1], $this->local_frames[$i]['TF'])){
                        throw new Interexception("ERROR: Repeated definition of the variable", 52);
                    }
                }
                $this->local_frames[sizeof($this->local_frames) - 1]['TF'][$input[1]] = null;
            }
            else{
                throw new Interexception("ERROR: Nonexisting frame", 55);
            }
        }
    }

    /**
    * Method for creating temporary frames
    */
    public function create_temp_frame() : void{
        $this->temp_frames = array('TF' => array());
        $this->is_created = true;
    }

    /**
    * Method for receiving values of variables in frames
    */
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
            if(sizeof($this->local_frames) > 0){
                for ($i=sizeof($this->local_frames) - 1; $i >= 0; $i--) { 
                    if(array_key_exists($input[1], $this->local_frames[$i]['TF'])){
                        if($this->local_frames[$i]['TF'][$input[1]] == null){
                            return "";
                        }
                        return $this->local_frames[$i]['TF'][$input[1]];
                    }
                }
                throw new Interexception("ERROR: Nonexisting variable", 54);  
            }else{
                throw new Interexception("ERROR: Nonexisting frame", 55);
            }
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

    /**
    * Method for receiving types of variables in frames
    */
    public function get_type(mixed $input) : string {
        if(array_key_exists($input[1], $this->types)){
            return $this->types[$input[1]];
        }
        elseif($input[0] == 'GF'){
            if(array_key_exists($input[1], $this->frames['GF'])){
                return "";
            }
        }
        elseif($input[0] == 'TF'){
            if($this->is_created == true){
                if(array_key_exists($input[1], $this->temp_frames['TF']) ){
                    return "";
                }   
            }  
        }
        elseif($input[0] == 'LF'){
            if(sizeof($this->local_frames) > 0){
                for ($i=sizeof($this->local_frames) - 1; $i >= 0; $i--) { 
                    if(array_key_exists($input[1], $this->local_frames[$i]['TF'])){
                        if($this->local_frames[$i]['TF'][$input[1]] == null){
                            return "";
                        }
                        return $this->local_frames[$i]['TF'][$input[1]];
                    }
                }
                throw new Interexception("ERROR: Nonexisting variable", 54);  
            }else{
                throw new Interexception("ERROR: Nonexisting frame", 55);
            }  
        }
        else{
            throw new Interexception("ERROR: Nonexisting type", 54);
        }
        

        return "";
    }

    /**
    * Method for saving variables in frames
    */
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
            if(sizeof($this->temp_frames) > 0){
                if(!array_key_exists($parts[1], $this->temp_frames['TF'])){
                    throw new Interexception("ERROR: Nonexisting type", 54);
                }
                $this->temp_frames['TF'][$parts[1]] = $symbol;
                $this->types[$parts[1]] = $type;
            }else{
                throw new Interexception("ERROR: Nonexisting frame", 55);
            }
        }
        if($parts[0] == 'LF'){
            $found = 0;
            $index = 0;
            if(sizeof($this->local_frames) > 0){
                for ($i=sizeof($this->local_frames) - 1; $i >= 0; $i--) { 
                    if(array_key_exists($parts[1], $this->local_frames[$i]['TF'])){
                        $found = 1;
                        $index = $i;
                        break;                        
                    }
                }
                if($found == 1){
                    $this->local_frames[$index]['TF'][$parts[1]] = $symbol;
                    $this->types[$parts[1]] = $type;
                }else{
                    throw new Interexception("ERROR: Nonexisting type", 54);
                }
                
            }else{
                throw new Interexception("ERROR: Nonexisting frame", 55);
            }
        }
    }

}



class Instructions{
    /** @var array<string, int> */
    public array $all_labels;
    public object $input_file;
    public mixed $stack;
    public mixed $write_stdout;
    public mixed $write_stderr;
    
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
    public function __construct(array $all_labels, object $input_file, mixed $write_stdout, mixed $write_stderr){
        $this->all_labels = $all_labels;
        $this->input_file = $input_file;
        $this->stack = new Stack();
        $this->write_stdout = $write_stdout;
        $this->write_stderr = $write_stderr;
    }

    /**
    * Method for checking number of arguments for different opcodes
    */
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
                $i->nodeValue = trim($i->nodeValue);
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
                $i->nodeValue = trim($i->nodeValue);
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
                $i->nodeValue = trim($i->nodeValue);
            }
        }
        elseif(in_array($opcode->getAttribute('opcode'), $this->zeroArgs)){
            $arg = $opcode->getElementsByTagName('*');
            if(sizeof($arg) != 0){
                throw new Interexception("ERROR: Wrong number of arguments", 32);
            }
        }
    }

    /**
    * Method for converting escape sequences
    */
    public function convert_escape(string $input_string) : string {
        $input_string = str_replace("&amp;", "&", $input_string);
        $input_string = str_replace("&lt;", "<", $input_string);
        $input_string = str_replace("&gt;", ">", $input_string);

        $regex = '/\\\\(\d{3})/';
        preg_match_all($regex, $input_string, $new_string);

        foreach ($new_string[1] as $i) {
            $decimal = (int)$i;
            $char = chr($decimal);
            $input_string = str_replace('\\' . $i, $char, $input_string);
        }

        return $input_string;
    }

    /**
    * Method for starting interpreter
    */
    function start_interpreter(mixed $instr, int $step) : int{
        $actual_instr = new Execute($instr->getAttribute('opcode'), $instr, $this);
        $step = $actual_instr->execute($step);

        return $step;
    }

}