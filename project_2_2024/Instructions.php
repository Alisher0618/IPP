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

    public function instr_write() {
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
            }
            
        }


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
        }else{
            exit(32);
        }

        return $step;
    } 

}

class Stack{
    public $frames = array('GF' => array(), 'LF' => array());

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
        return $step;
    }

}