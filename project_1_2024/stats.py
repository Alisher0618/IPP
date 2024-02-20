import sys, re


class Stats:
    _instance = None
    
    def __init__(self):
        self.instr = 0
        self.header = False
        self.comments = 0
        self.labels = 0
        self.jumps = 0
        self.fwjumps = 0
        self.backjumps = 0
        self.badjumps = 0
        self.label_dict = {}
        self.jumps_dict = {}
        self.frequent = []
        self.countInstr = {}
        #self.loc = []
        self.files = []
        self.special_string = []
    
    
    #GET 
    
    
    def getInstr(self):
        return self.instr
    
    def getComment(self):
        return self.comments
    
    def getLabel(self):
        return self.labels
    
    def getJump(self):
        return self.jumps
    
    def getFwJump(self):
        return self.fwjumps
    
    def getBackJump(self):
        return self.backjumps
    
    def getBadJump(self):
        return self.badjumps
    
    def getHeader(self):
        return self.header
    
    def getLabelDict(self):
        return self.label_dict
    
    def getJumpDict(self):
        return self.jumps_dict
    
    def getFiles(self):
        return self.files
    
    def getSpecString(self):
        return self.special_string
    
    def getFrec(self):
        return self.frequent

    def getCountInstr(self): # for frequent
        return self.countInstr
     
    #SET
    
    def setInstr(self):
        self.instr += 1
    
    def setComment(self):
        self.comments += 1
        
    def setJump(self):
        self.jumps += 1
    
    def setFwJump(self):
        self.fwjumps += 1  
        
    def setBackJump(self):
        self.backjumps += 1
        
    def setBadJump(self):
        self.badjumps += 1
    
    def setLabel(self):
        self.labels += 1
        
    def setHeader(self):
        self.header = True
        
    def setSpecialString(self, spec_str):
        self.special_string.append(spec_str)
        
    def setJumpDict(self, key, value):
        self.jumps_dict[key] = value
        
    def setLabelDict(self, key, value):
        self.label_dict[key] = value
        
    def setFreq(self, opcode):
        self.frequent.append(opcode)
        
    def setCountInstr(self, key, value): # for frequent
        self.countInstr[key] = value
    
    #singleton pattern
    @staticmethod
    def get_instance():
        if Stats._instance is None:
            Stats._instance = Stats()
        return Stats._instance
    
    def inputFile(self, filename):
        self.files.append(filename)
     
    def writeFile(self):
        file = open(self.files[0], 'w')
        del self.files[0]
        
        if not file:
            print("error with file")
            exit(12)
        
        return file
            
regStats = "^--stats=.+"
regPrint = "^--print=.+"

def parseparams():
    params = sys.argv
    allparams = ["--loc", "--comments", "--labels",
                 "--jumps", "--fwjumps", "--backjumps", "--badjumps",
                 "--frequent", "--eol", "parse.py"]
    find = False
    ret = False
    unique_instr = []
    tmpFileName1 = ""
    tmpFileName2 = ""
    statistics = Stats.get_instance()
    
    for i in params:
        if(i not in allparams and not re.search(regStats, i) and not re.search(regPrint, i)):
            sys.exit(10)
        if(re.search(regStats, i)):
            find = True
            mainparam = i.split('=')
            statistics.inputFile(mainparam[1])
            if(len(unique_instr) != 0):
                if(len(unique_instr) != len(set(unique_instr))):
                    sys.exit(12)
                else:
                    unique_instr = []
            ret = True
        if(re.search(regPrint, i)):
            spec_string = i.split('=')
            statistics.setSpecialString(spec_string[1])
        else:
            if(i in allparams and i != "parse.py"):
                unique_instr.append(i)
                    
    
    if(len(unique_instr) != len(set(unique_instr))):
        sys.exit(12)
        
    if(not find and len(sys.argv) > 1): # если есть параметры для статистики, но нет --stats
        sys.exit(10)

    return ret

def find_key(label, value):
    key = [key for key, val in label.items() if val == value]
    
    if(key):
        return key[0]
    else:
        return -1

def countJumps():
    getStats = Stats.get_instance()
    label = getStats.getLabelDict()
    jump = getStats.getJumpDict()
    
    for key1, value in jump.items():
        key2 = find_key(label, value)
            
        if(key2 < key1):
            getStats.setBackJump()
        if(key2 > key1):
            getStats.setFwJump()
        elif(key2 == -1):
            getStats.setBackJump()
        
    
# add --eol param support
def writeStats():
    statistics = Stats.get_instance()
    file = statistics.writeFile();
    countJumps()

    
    for i in range(2, len(sys.argv)):
        
        if(re.search(regStats, sys.argv[i])):
            file.close()
            file = statistics.writeFile()
            print(file)
            continue    
        
        if(sys.argv[i] == "--eol"):
            file.write('\n')
        if(sys.argv[i] == "--loc"):
            file.write(str(statistics.getInstr()) + '\n')
        elif(sys.argv[i] == "--comments"):
            file.write(str(statistics.getComment()) + '\n')
        elif(sys.argv[i] == "--jumps"):
            file.write(str(statistics.getJump()) + '\n')
        elif(sys.argv[i] == "--labels"):
            file.write(str(statistics.getLabel()) + '\n')
        elif(sys.argv[i] == "--frequent"):            
            for item in statistics.getFrec():
                if item in statistics.getCountInstr():
                    count = statistics.getCountInstr()[item]
                    statistics.setCountInstr(item, count + 1)
                else:
                    statistics.setCountInstr(item, 1)
            
            max_count = max(statistics.getCountInstr().values())

            most_common_elements = [key for key, value in statistics.getCountInstr().items() if value == max_count]

            file.write(", ".join(map(str, most_common_elements)) + '\n')
        elif(re.search(regPrint, sys.argv[i])):
            file.write(str(statistics.getSpecString()[0]) + '\n')
            del statistics.getSpecString()[0]
        elif(sys.argv[i] == "--fwjumps"):
            file.write(str(statistics.getFwJump()) + '\n')
        elif(sys.argv[i] == "--backjumps"):
            file.write(str(statistics.getFwJump()) + '\n')
        elif(sys.argv[i] == "--badjumps"):
            file.write(str(statistics.getFwJump()) + '\n')
            