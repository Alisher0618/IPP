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
        self.labelsStack = []
        self.jumpsStack = []
        self.loc = []
        self.files = ""
    
    
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
    
    def getLabelMap(self):
        return self.labelsStack
    
    def getJumpMap(self):
        return self.jumpsStack
    
    
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
        
        
    @staticmethod
    def get_instance():
        if Stats._instance is None:
            Stats._instance = Stats()
        return Stats._instance
    
    def inputFile(self, filename):
        self.files = filename
     
    def writeFile(self):
        file = open(self.files, 'w')
        
        if not file:
            print("error with file")
            exit(10)
        
        return file
            
    
    
    
regStats = "^--stats=.+"

def parseparams():
    params = sys.argv
    allparams = ["--loc", "--comments", "--labels",
                 "--jumps", "--fwjumps", "--backjumps", "--badjumps",
                 "--frequent"]
    find = False
    ret = False
    
    statistics = Stats.get_instance()
    
    for i in params:
        if(re.search(regStats, i)):
            find = True
            mainparam = i.split('=')
            statistics.inputFile(mainparam[1])
            ret = True
            break
        
    #add mupltiple count of --stats in params
        
    if(not find and len(sys.argv) > 1): # если есть параметры для статистики, но нет --stats
        exit(10)
    
    return ret
    
    
# add --eol param support
def writeStats():
    statistics = Stats.get_instance()
    file = statistics.writeFile();
    
    for i in range(2, len(sys.argv)):
        if(sys.argv[i] == "--loc"):
            file.write(str(statistics.getInstr()) + '\n')
        elif(sys.argv[i] == "--comments"):
            file.write(str(statistics.getComment()) + '\n')

        
    