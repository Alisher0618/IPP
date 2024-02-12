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
        self.labels_map = []
        self.jumps_map = []
        self.loc = []
        self.files = ""
        
        
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
        
    #add return code 10
    
    if(not find):
        return False
    
    return ret
    
def writeStats():
    statistics = Stats.get_instance()
    file = statistics.writeFile();
    
    for i in sys.argv:
        file.write(i + '\n')
    
    
        
    