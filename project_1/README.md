# Introduction
The script reads the source code in IPPcode23 from the standard input, checks the lexical and syntactic correctness of the code and writes the XML representation of the program to the standard output

## Running
    $ php8.1 parse.php < test.IPPcode23 > result.xml

Example of `test.IPPcode23`:
```
.IPPcode23
DEFVAR GF@one
DEFVAR GF@two
DEFVAR GF@result
MOVE GF@one int@5
MOVE GF@two int@10
ADD GF@result GF@one GF@two
WRITE GF@result
```

XML representation:
```
<?xml version="1.0" encoding="UTF-8"?>
<program language="IPPcode23">
<instruction order="1" opcode="DEFVAR">
    <arg1 type="var">GF@one</arg1>
</instruction>
<instruction order="2" opcode="DEFVAR">
    <arg1 type="var">GF@two</arg1>
</instruction>
<instruction order="3" opcode="DEFVAR">
    <arg1 type="var">GF@result</arg1>
</instruction>
<instruction order="4" opcode="MOVE">
    <arg1 type="var">GF@one</arg1>
    <arg2 type="int">5</arg2>
</instruction>
<instruction order="5" opcode="MOVE">
    <arg1 type="var">GF@two</arg1>
    <arg2 type="int">10</arg2>
</instruction>
<instruction order="6" opcode="ADD">
    <arg1 type="var">GF@result</arg1>
    <arg2 type="var">GF@one</arg2>
    <arg3 type="var">GF@two</arg3>
</instruction>
<instruction order="7" opcode="WRITE">
    <arg1 type="var">GF@result</arg1>
</instruction>
</program>
```

## Evaluation
7.02/8  

Lexical analysis (error detection): 95%<br>
Syntactic analysis (error detection): 83%<br>
Instruction processing (including errors): 96%<br>
Non-trivial program processing: 87%<br>
Total without extensions: 91%

