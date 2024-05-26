# Introduction
The script reads the source code in IPPcode24 from the standard input, checks the lexical and syntactic correctness of the code and writes the XML representation of the program to the standard output

## Running
    $ python3 parse.php < test.IPPcode24

Example of `test.IPPcode24`:
```
.IPPcode24
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
<program language="IPPcode24">
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
    7/7 + 1 point for STATP

Lexical analysis (error detection): 95%
Parsing (error detection): 91%
Instruction processing (including errors): 98%
Non-trivial program processing: 87%
STATP extension 80%
Total without extensions: 94%