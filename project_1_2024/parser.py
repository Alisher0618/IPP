from xml.dom import minidom
import sys, re

"""

prog = xml.createElement('program')
xml.appendChild(prog)
prog.setAttribute('language', 'IPPcode24')

progChild = xml.createElement('child')
progChild.setAttribute('name', 'test_test')
progCtest = xml.createTextNode("child_test")
progChild.appendChild(progCtest)
prog.appendChild(progChild)


xml_str = xml.toprettyxml(indent='\t', encoding="UTF-8").decode("utf-8")"""


xml = minidom.Document()
order = 0

instructions = ["MOVE", "CREATEFRAME", "PUSHFRAME", "POPFRAME", "DEFVAR", "CALL", "RETURN",
            "PUSHS", "POPS",
            "ADD", "SUB", "MUL", "IDIV", "LT", "GT", "EQ", "AND", "OR", "NOT", "INT2CHAR", "STRI2INT",
            "READ", "WRITE", 
            "CONCAT", "STRLEN", "GETCHAR", "SETCHAR",
            "TYPE", 
            "LABEL", "JUMP", "JUMPIFEQ", "JUMPIFNEQ", "EXIT",
            "DPRINT", "BREAK",
            ".IPPCODE24", ".IPPcode24"]

threeArgs = ["ADD", "SUB", "MUL", "IDIV", "LT", "GT", "EQ", "AND", "OR", "STRI2INT", 
    "CONCAT", "GETCHAR", "SETCHAR", "JUMPIFEQ", "JUMPIFNEQ"]

twoArgs = ["MOVE", "INT2CHAR", "READ", "STRLEN", "TYPE", "NOT"]

oneArgs = ["LABEL", "JUMP", "EXIT", "WRITE", "PUSHS", "POPS", "DEFVAR", "CALL", "DPRINT"]

zeroArgs = ["CREATEFRAME", "PUSHFRAME", "POPFRAME", "RETURN", "BREAK"]

regType   = "^(int|string|bool)$"
regVar    = "^(GF|LF|TF)@([a-zA-Z]|[_\-$%&*?!])(\w|[_\-$%&*?!])*$"
regLabel  = "^(\w|[_\-$%&*?!])*$"
regInt    = "^int@(([-\+]?[0-9]+$)|(0[xX][0-9a-fA-F]+$)|(0[oO][0-7]+$))"
regString = "^string@(([^\s\#\\\\]|\\\\[0-9]{3})*$)"
regNil    = "^nil@nil$"
regBool   = "^bool@(true|false)$"

firstIsVar = ["MOVE", "DEFVAR", "POPS", "ADD", "SUB", "MUL", "IDIV", "LT", "GT", "EQ", "AND", "OR", "NOT", 
"INT2CHAR", "STRI2INT", "READ", "CONCAT", "STRLEN", "GETCHAR", "SETCHAR", "TYPE" ]

firstIsLabel = ["JUMPIFEQ", "JUMPIFNEQ", "CALL", "LABEL", "JUMP"]

firstIsSymb = ["PUSHS", "WRITE", "EXIT", "DPRINT"]

sndArgIsSymb = ["MOVE", "ADD", "SUB", "MUL", "IDIV", "LT", "GT", "EQ", "AND", "OR", "NOT", 
"INT2CHAR", "STRI2INT", "CONCAT", "STRLEN", "GETCHAR", "SETCHAR", "TYPE", "JUMPIFEQ", "JUMPIFNEQ"]

def checkArgumentOne(instrName, argName):
    global regVar
    global regInt
    global regString
    global regBool
    global regNil
    global regLabel
    
    global firstIsVar
    global firstIsLabel
    global firstIsSymb
    
    if(instrName in firstIsVar and re.search(regVar, argName)):
        var = argName.split('@')
        return var
    elif(instrName in firstIsLabel and re.search(regLabel, argName)):
        #argName = "label"
        #return argName
        return "label"
    elif(instrName in firstIsSymb and re.match(regVar, argName)):
        var = argName.split('@')
        return var
    elif(instrName in firstIsSymb and re.match(regInt, argName)):
        var = argName.split('@')
        return var
    elif(instrName in firstIsSymb and re.match(regString, argName)):
        var = argName.split('@')
        return var
    elif(instrName in firstIsSymb and re.match(regBool, argName)):
        var = argName.split('@')
        return var
    elif(instrName in firstIsSymb and re.match(regNil, argName)):
        var = argName.split('@')
        return var
    else:
        sys.exit(23)
        
        
def checkArgumentTwo(instrName, argName):
    global regVar
    global regInt
    global regString
    global regBool
    global regNil
    global regType
    
    global sndArgIsSymb
    
    if(instrName in sndArgIsSymb and re.match(regVar, argName)):
        var = argName.split('@')
        return var
    elif(instrName in sndArgIsSymb and re.match(regInt, argName)):
        var = argName.split('@')
        return var
    elif(instrName in sndArgIsSymb and re.match(regString, argName)):
        var = argName.split('@')
        return var
    elif(instrName in sndArgIsSymb and re.match(regBool, argName)):
        var = argName.split('@')
        return var
    elif(instrName in sndArgIsSymb and re.match(regNil, argName)):
        var = argName.split('@')
        return var
    elif(instrName == "READ" and re.match(regType, argName)):
        return "type"
    else:
        sys.exit(23)
        
def specialSymbol(argName):  
    for elem in argName:
        if('&' in elem):
            elem = elem.replace('&', "&amp;")
        elif('&' in elem):
            elem = elem.replace('<', "&lt;")
        elif('>' in elem):
            elem = elem.replace('>', "&gt;")
        else:
            continue;
    return argName    

def createXML_for3(line, order, num):
    pass

def createXML_for2(line, order, num):
    pass

def createXML_for1(line, order, num):
    global xml
    global prog
    
    instr = xml.createElement("instruction")
    prog.appendChild(instr)
    instr.setAttribute("order", str(order))
    instr.setAttribute("opcode", line[0])
    
    argument = checkArgumentOne(line[0], line[1])
    if(argument[0] == 'string'):
        argument = specialSymbol(argument)
    
    if(num == 1):
        if(argument[0] == "GF" or argument[0] == "LF" or argument[0] == "TF"):
            newArg1 = '@'.join(map(str, argument))
            node = "var"
            arg1 = xml.createElement("arg1")
            arg1_text = xml.createTextNode(newArg1)
            arg1.appendChild(arg1_text)
        else:
            if(argument[0] == "string"):
                node = "string"
                merge = ""
                for i in argument:
                    if(merge == "" and i != "string"):
                        merge = merge + i
                    elif(i == "string"):
                        continue
                    else:
                        merge = merge + '@' + i
                arg1 = xml.createElement("arg1")
                arg1_text = xml.createTextNode(merge)
                print(argument)
                arg1.appendChild(arg1_text)
            else:
                arg1 = xml.createElement("arg1")
                arg1_text = xml.createTextNode(argument[1])                
                arg1.appendChild(arg1_text)
                       
        instr.appendChild(arg1)
        arg1.setAttribute("type", node)
    else:
        arg1 = xml.createElement("arg1");
        arg1_text = xml.createTextNode(line[1])
        arg1.appendChild(arg1_text)
        instr.appendChild(arg1)
        arg1.setAttribute("type", argument)
    

def createXML_for0(line, order):
    global xml
    global prog

    instr = xml.createElement("instruction")
    prog.appendChild(instr)
    instr.setAttribute("order", str(order))
    instr.setAttribute("opcode", line[0])

def checkline(line):
    isnotvar = 0
    isvar = 1
    global order
    args = len(line) - 1
    
    if(line[0] in threeArgs and args == 3):
        if(line[0] == "JUMPIFEQ" or line[0] == "JUMPIFNEQ"):    # label symb symb
            order += 1
            createXML_for3(line, order, isnotvar)
        else:                                                   # var symb symb
            order += 1
            createXML_for3(line, order, isvar)
    
    elif(line[0] in twoArgs and args == 2):
        order += 1
        createXML_for2(line, order, isvar)
        
    elif(line[0] in oneArgs and args == 1):
        if(line[0] == "CALL" or line[0] == "LABEL" or line[0] == "JUMP"):   # label
            order += 1
            createXML_for1(line, order, isnotvar)
        else:                                                               # var or symb
            order += 1
            createXML_for1(line, order, isvar)
            
    elif(line[0] in zeroArgs and args == 0):
        order += 1
        print("this")
        createXML_for0(line, order)
        
    elif(line[0] not in instructions):
        print("err 22")
        sys.exit(22)
    else:
        print("err 23")
        sys.exit(23)
    
    

def scanner():
    global xml
    global prog
    
    counterheader = 0
    
    for line in sys.stdin:
        if(re.search(r"#[^\r\n]*", line)):
            line = re.sub(r"#[^\r\n]*", "", line)
        
        line = re.sub(r'\s+', ' ', line)
        
        if not line.strip():
            continue
        
        line_arr = line.strip().split()
        line_arr[0] = line_arr[0].upper()
        #print(line_arr[0])
        if(re.match(r'^\.IPPCODE24$', line_arr[0])):
            prog = xml.createElement('program')
            xml.appendChild(prog)
            prog.setAttribute('language', 'IPPcode24')
        
        if(line_arr[0] == '.IPPCODE24'):
            counterheader += 1
        
        if(counterheader > 1):
            sys.exit(22)
            
        if(counterheader != 1):
            sys.exit(21)
        
        if(line_arr[0] == '.IPPCODE24'):
            continue;
        
        #print(line_arr)
        checkline(line_arr)
        
        
            
    xml_str = xml.toprettyxml(indent='\t', encoding="UTF-8").decode("utf-8")
    sys.stdout.write(xml_str)
    sys.exit(0)
            
        
scanner()

