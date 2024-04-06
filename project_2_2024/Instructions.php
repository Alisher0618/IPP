<?php


class Execute{
    public string $opcode;
    public mixed $instr;
    public mixed $class_instr;
    public function __construct(string $opcode, mixed $instr, mixed $class_instr){
        $this->opcode = $opcode;
        $this->instr = $instr;
        $this->class_instr = $class_instr;
    }

    public function check_type($symbol){
        if($symbol->getAttribute('type') == "var"){
            $parts = explode("@", $symbol->nodeValue);
            $value = $this->class_instr->stack->get_symbol($parts);
            //echo $value . "\n";
            if($value == null){
                echo "Variable has no value.\n";
                exit(56);
            }
            $type = $this->class_instr->stack->get_type($parts[1]);
            $return_value = $value;
            $return_type = $type;
        }else{
            $return_value = $symbol->nodeValue;
            $return_type = $symbol->getAttribute('type');
        }

        return array($return_type, $return_value);
    }

    // DEFVAR
    public function instr_defvar(){
        $this->class_instr->check_arguments($this->instr);
        $arg = $this->instr->getElementsByTagName('arg1')->item(0);

        if (!($arg->hasAttribute('type'))) {
            echo "tag is empty\n";
            exit(32);
        } 

        if($arg->getAttribute('type') != "var"){
            echo "wrong type of argument\n";
            exit(32);
        }

        $var_value = $arg->nodeValue;

        $parts = explode("@", $var_value);
        $this->class_instr->stack->push_frame($parts);
        
    }

    // WRITE
    public function instr_write(){
        $this->class_instr->check_arguments($this->instr);

        $arg = $this->instr->getElementsByTagName('arg1')->item(0);

        if (!($arg->hasAttribute('type'))) {
            echo "tag is empty\n";
            exit(32);
        }

        if($arg->getAttribute('type') == "var"){
            $parts = explode("@", $arg->nodeValue);
            $value = $this->class_instr->stack->get_symbol($parts);

            echo $value . "\n";
        }
        else{
            //if string add converter
            if($arg->getAttribute('type') == "int" || $arg->getAttribute('type') == "bool"){
                echo $arg->nodeValue . "\n";
            }else if($arg->getAttribute('type') == "nil"){
                echo "" . "\n";
            }   
        }
    }

    // MOVE
    public function instr_move(){
        $this->class_instr->check_arguments($this->instr);

        $arg_1 = $this->instr->getElementsByTagName('arg1')->item(0);
        //echo $arg_1->nodeValue . "\n";
        $arg_2 = $this->instr->getElementsByTagName('arg2')->item(0);
        //echo $arg_2->nodeValue . "\n";

        if(!($arg_1->hasAttribute('type'))){
            echo "tag is empty\n";
            exit(32);
        }
        if(!($arg_2->hasAttribute('type'))){
            echo "tag is empty\n";
            exit(32);
        }

        if($arg_1->getAttribute('type') != "var"){
            echo "bad operand type\n";
            exit(53);
        }

        $arg2_symb = $this->check_type($arg_2);

        if($arg2_symb[0] == "string" && $arg2_symb[1] == null){
            $arg2_symb[1] = "";
        }

        /*echo $arg_1->nodeValue . "\n";
        echo $arg2_symb[0] . "\n";
        echo $arg2_symb[1] . "\n";*/

        $this->class_instr->stack->set_symbol($arg_1->nodeValue, $arg2_symb[1], $arg2_symb[0]);
    }

    // ADD, SUB, MUL, IDIV
    public function instr_math($opcode){
        $this->class_instr->check_arguments($this->instr);

        $arg_1 = $this->instr->getElementsByTagName('arg1')->item(0);
        $arg_2 = $this->instr->getElementsByTagName('arg2')->item(0);
        $arg_3 = $this->instr->getElementsByTagName('arg3')->item(0);

        if(!($arg_1->hasAttribute('type'))){
            echo "tag is empty\n";
            exit(32);
        }
        if(!($arg_2->hasAttribute('type'))){
            echo "tag is empty\n";
            exit(32);
        }
        if(!($arg_3->hasAttribute('type'))){
            echo "tag is empty\n";
            exit(32);
        }
        if($arg_1->getAttribute('type') != "var"){
            echo "bad operand type\n";
            exit(53);
        }

        $arg2_symb = $this->check_type($arg_2);
        $arg3_symb = $this->check_type($arg_3);
        
        if(($arg2_symb[0] != "int" || $arg3_symb[0] != "int")){
            echo "bad operand type\n";
            exit(53);
        }

        if($opcode == "ADD"){
            $sum = (int) $arg2_symb[1] + (int) $arg3_symb[1];
        }elseif($opcode == "SUB"){
            $sum = (int) $arg2_symb[1] - (int) $arg3_symb[1];
        }elseif($opcode == "MUL"){
            $sum = (int) $arg2_symb[1] * (int) $arg3_symb[1];
        }elseif($opcode == "IDIV"){
            if((int) $arg3_symb[1] == 0){
                echo "division by zero\n";
                exit(57);
            }
            $sum = intdiv((int) $arg2_symb[1], (int) $arg3_symb[1]);
        }

        $this->class_instr->stack->set_symbol($arg_1->nodeValue, $sum, "int");
    }

    public function instr_rel($opcode){
        $arg_1 = $this->instr->getElementsByTagName('arg1')->item(0);
        $arg_2 = $this->instr->getElementsByTagName('arg2')->item(0);
        $arg_3 = $this->instr->getElementsByTagName('arg3')->item(0);

        if(!($arg_1->hasAttribute('type'))){
            echo "tag is empty\n";
            exit(32);
        }
        if(!($arg_2->hasAttribute('type'))){
            echo "tag is empty\n";
            exit(32);
        }
        if(!($arg_3->hasAttribute('type'))){
            echo "tag is empty\n";
            exit(32);
        }
        if($arg_1->getAttribute('type') != "var"){
            echo "bad operand type\n";
            exit(53);
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
            }else{
                if($arg2_symb[0] != $arg3_symb[0]){
                    echo " wrong type of arguments\n";
                    exit(53);
                }

                if($arg2_symb[0] == "int"){
                    if(!(is_numeric($arg2_symb[1]) && is_numeric($arg3_symb[1]))){
                        echo "wrong value of argument\n";
                        exit(32);
                    }

                    $tmp_result = (int) $arg2_symb[1] == (int) $arg3_symb[1];
                    $result = strtolower((string) $tmp_result);
                }
            }

        }elseif($opcode == "LT" || $opcode == "GT"){
            if($arg2_symb[0] == "nil" || $arg3_symb[0] == "nil"){
                echo "cant apply these operands on nil type\n";
                exit(53);
            }
            if($arg2_symb[0] != $arg3_symb[0]){
                echo "wrong types\n";
                exit(53);
            }

            $allowed_types = array("int", "bool", "string");

            if(!in_array($arg2_symb[0], $allowed_types) || !in_array($arg3_symb[0], $allowed_types)){
                echo "wrong types\n";
                exit(53);
            }

            if($arg2_symb[0] == "bool"){
                if(($arg2_symb[1] != "true" || $arg2_symb[1] != "false") || ($arg3_symb[1] != "true" || $arg3_symb[1] != "false")){
                    echo "wrong value of argument\n";
                    exit(32);
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
                    echo "wrong value of argument\n";
                    exit(32);
                }

                $arg2_symb[1] = (int) $arg2_symb[1];
                $arg3_symb[1] = (int) $arg3_symb[1];
            }

            if($opcode == "GT"){
                $tmp_result = (string) ($arg2_symb[1] > $arg3_symb[1]);
                $result = strtolower((string) $tmp_result);
            }elseif($opcode == "LT"){
                $tmp_result = (string) ($arg2_symb[1] < $arg3_symb[1]);
                $result = strtolower((string) $tmp_result);
            }
            
        }

        $this->class_instr->stack->set_symbol($arg_1->nodeValue, $result);
    }

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
        }else{
            exit(32);
        }

        return $step;
    } 

}

class Stack{
    public $frames = array('GF' => array()/*, 'LF' => array()*/);
    public $types = array();

    public function __construct(){
    }

    public function push_frame($input){
        if($input[0] == 'GF'){
            if(array_key_exists($input[1], $this->frames['GF'])){
                echo "Repeated definition of the variable.\n";
                exit(52);
            }
            $this->frames['GF'][$input[1]] = null;
        }
    }

    public function get_symbol($input){
        if($input[0] == 'GF'){
            if(array_key_exists($input[1], $this->frames['GF'])){
                return $this->frames['GF'][$input[1]];
            }else{
                echo "Nonexisting variable.\n";
                exit(54);
            }
        }
    }

    public function get_type($input){
        if(array_key_exists($input, $this->types)){
            return $this->types[$input];
        }else{
            echo "type does not exist\n";
            exit(54);
        }    
    }

    public function set_symbol($variable, $symbol, $type){
        $parts = explode("@", $variable);  
        if($parts[0] == 'GF'){
            if(!array_key_exists($parts[1], $this->frames['GF'])){
                echo "Nonexisting variable.\n";
                exit(54);
            }
            $this->frames['GF'][$parts[1]] = $symbol;
            $this->types[$parts[1]] = $type;
        } 
    }

}



class Instructions{
    /** @var array<string, int> */
    public array $all_labels;
    public object $input_file;
    public mixed $stack;

    public string $program_output;

    public array $threeArgs = array("ADD", "SUB", "MUL", "IDIV", "LT", "GT", "EQ", "AND", "OR", "STRI2INT", 
        "CONCAT", "GETCHAR", "SETCHAR", "JUMPIFEQ", "JUMPIFNEQ");

    public array $twoArgs = array("MOVE", "INT2CHAR", "READ", "STRLEN", "TYPE", "NOT");

    public array $oneArgs = array("LABEL", "JUMP", "EXIT", "WRITE", "PUSHS", "POPS", "DEFVAR", "CALL", "DPRINT");

    public array $zeroArgs = array("CREATEFRAME", "PUSHFRAME", "POPFRAME", "RETURN", "BREAK");
    /**
     * Constructor for the Instructions class.
     *
     * @param array<string, int> $all_labels An associative array containing keys and values.
     * @param object $input_file The input file object.
     */
    public function __construct(array $all_labels, object $input_file){
        $this->all_labels = $all_labels;
        $this->input_file = $input_file;
        $this->stack = new Stack();
        $this->program_output = "";
    }

    public function check_arguments(mixed $opcode) : void {
        if(in_array($opcode->getAttribute('opcode'), $this->threeArgs)){
            $arg = $opcode->getElementsByTagName('*');
            if(sizeof($arg) != 3){
                echo "wrong number of arguments\n";
                exit(32);
            }
        }
        elseif(in_array($opcode->getAttribute('opcode'), $this->twoArgs)){
            $arg = $opcode->getElementsByTagName('*');
            if(sizeof($arg) != 2){
                echo "wrong number of arguments\n";
                exit(32);
            }
        }
        elseif(in_array($opcode->getAttribute('opcode'), $this->oneArgs)){
            $arg = $opcode->getElementsByTagName('*');
            if(sizeof($arg) != 1){
                echo "wrong number of arguments\n";
                exit(32);
            }
        }
        elseif(in_array($opcode->getAttribute('opcode'), $this->zeroArgs)){
            $arg = $opcode->getElementsByTagName('*');
            if(sizeof($arg) != 0){
                echo "wrong number of arguments\n";
                exit(32);
            }
        }
    }

    function start_interpreter(mixed $instr, int $step) : int{
        $actual_instr = new Execute($instr->getAttribute('opcode'), $instr, $this);
        $step = $actual_instr->execute($step);
        //print_r($this->stack->frames);
        //print_r($this->stack->types);
        return $step;
    }

}