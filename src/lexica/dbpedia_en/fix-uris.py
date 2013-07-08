import sys

newURI = dict()
names = dict()

def genURI(oldURI):
    global names
    if oldURI.startswith("<http://github.com"):
        fragStart = oldURI.find("#")
        newFragStart = oldURI.find("/",fragStart)
        sec = oldURI[(fragStart-10):fragStart]
        if newFragStart > 0:
            name = oldURI[(fragStart+1):newFragStart]
            frag = "#" + oldURI[(newFragStart+1):(len(oldURI)-1)]
        else:
            name = oldURI[(fragStart+1):(len(oldURI)-1)]
            frag = ""
        if name == "":
            name = "Lexicon"
        if (sec + name) in names.keys():
            return "<http://lemon-model.net/lexica/dbpedia_en/" + names[sec+name] + frag + ">"
        else:
            if not name in names.values() or name == "Lexicon":
                names[sec+name] = name
                return "<http://lemon-model.net/lexica/dbpedia_en/" + name + frag + ">"
            else:
                i = 1
                while (name + str(i)) in names.values():
                    i += 1
                names[sec+name] = name + str(i)
                return "<http://lemon-model.net/lexica/dbpedia_en/" + name + str(i) + frag + ">"
    else:
        return oldURI

for line in sys.stdin:
    ss = line.strip().split(" ")
    subj = ss[0]
    pred = ss[1]
    if len(ss) == 4:
        obj = ss[2]
    else:
        obj = " ".join(ss[2:(len(ss)-1)])
    if subj not in newURI.keys():
        uri = genURI(subj)
        newURI[subj] = uri
    sys.stdout.write(newURI[subj] + " ")

    if pred not in newURI.keys():
        uri = genURI(pred)
        newURI[pred] = uri
    sys.stdout.write(newURI[pred] + " ")

    if not obj.startswith("<"):
        sys.stdout.write(obj + " .\n")
    else:
        if obj not in newURI.keys():
            uri = genURI(obj)
            newURI[obj] = uri
        sys.stdout.write(newURI[obj] + " .\n")



