# Introduction
The script reads the source code in IPPcode23 from the standard input, checks the lexical and syntactic correctness of the code and writes the XML representation of the program to the standard output

## Running
    $ php8.1 parse.php < test.IPPcode23 > result.xml

Example of `test.IPPcode23`:<br>
    .IPPcode23<br>
    DEFVAR GF@one<br>
    DEFVAR GF@two<br>
    DEFVAR GF@result<br>
    MOVE GF@one int@5<br>
    MOVE GF@two int@10<br>
    ADD GF@result GF@one GF@two<br>
    WRITE GF@result

XML representation:
    <?xml version="1.0" encoding="UTF-8"?><br>
    <program language="IPPcode23"><br>
    <instruction order="1" opcode="DEFVAR"><br>
        <arg1 type="var">GF@one</arg1><br>
    </instruction><br>
    <instruction order="2" opcode="DEFVAR"><br>
        <arg1 type="var">GF@two</arg1><br>
    </instruction><br>
    <instruction order="3" opcode="DEFVAR"><br>
        <arg1 type="var">GF@result</arg1><br>
    </instruction><br>
    <instruction order="4" opcode="MOVE"><br>
        <arg1 type="var">GF@one</arg1><br>
        <arg2 type="int">5</arg2><br>
    </instruction><br>
    <instruction order="5" opcode="MOVE"><br>
        <arg1 type="var">GF@two</arg1><br>
        <arg2 type="int">10</arg2><br>
    </instruction><br>
    <instruction order="6" opcode="ADD"><br>
        <arg1 type="var">GF@result</arg1><br>
        <arg2 type="var">GF@one</arg2><br>
        <arg3 type="var">GF@two</arg3><br>
    </instruction><br>
    <instruction order="7" opcode="WRITE"><br>
        <arg1 type="var">GF@result</arg1><br>
    </instruction><br>
    </program>

## Evaluation
    7.02/8  

Lexical analysis (error detection): 95%<br>
Syntactic analysis (error detection): 83%<br>
Instruction processing (including errors): 96%<br>
Non-trivial program processing: 87%<br>
Total without extensions: 91%

