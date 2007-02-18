#!/usr/bin/python
# -*- coding: iso-8859-15 -*-
#
#  Copyright (c) 2005-2006 Alberto Montañola Lacort.
#  Licensed under the GNU GPL. For full terms see the file COPYING.
#
#  Id: $Id: test.php 35 2006-05-23 13:37:00Z alberto $
#

#Configuración
#upload_dir="/tmp/apf_upload"
upload_dir="/home/apf_upload"
###rpc_path="/tfc/ajaxrpc.php"
rpc_server_path="http://localhost/tfc/ajaxrpc.php"


################################################################################
#### NO TOCAR NADA DEBAJO DE LA BARRA A NO SER QUE SEPAS LO QUE HACES ##########
################################################################################
########## THE BAR #############################################################
################################################################################

#Global (idealmente tendria que ser una propiedad miembro de la clase cgiFiles
# pero parece ser que al añadir el constructor da problemas)"
dpath=""

#Esto queda un poco mal y feo, para hacerlo bien habria que reescribir la
# clase FieldStorage, actualmente esta intenta mantener el fichero entero en
# memoria, y esto pues tiene consequencias grabes cuando los ficheros a subir
# són del orden de cientos de megabytes
file_size=0

import os,time,cgi,cgitb,sys,re,urllib2,glob
import os.path
cgitb.enable()


class mlog:
    def __init__(self,handle,filename,mode="w"):
        self.file=file(filename,mode)
        self.handle=handle
    def write(self,x):
        self.handle.write(x)
        self.file.write(x)
    def flush(self):
        self.handle.flush()
        self.file.flush()
    def close(self):
        self.file.close()

class slowFile:
    def __init__(self,path):
        self.f=file(path + "/upload.raw","wb+")
        self.quota=0
        self.write_quota=0
    def __del__(self):
        self.f.close()
    def read(self,len=None):
        if len==None:
            return self.f.read()
        else:
            return self.f.read(len)
    def write(self,input):
        global file_size
        w=len(input)
        #print w
        file_size=file_size + w
        self.quota=self.quota+w
        self.write_quota=self.write_quota+w
        #flushear el buffer cada 16K
        if self.write_quota>1024*16:
            self.f.flush()
            self.write_quota=0
        #Limitar la velocidad de subida
        if 0 and self.quota>1024*1024*10:
            #print self.quota
            #sys.stdout.flush()
            self.quota=0
            time.sleep(1)
        return self.f.write(input)
    def flush(self):
        return self.f.flush()
    def seek(self,pos):
        return self.f.seek(pos)
    def close(self):
        return self.f.close()

class cgiFiles(cgi.FieldStorage):
    def make_file(self, binary=None):
        global dpath
        return slowFile(dpath)


class rpcClient:
    def __init__(self,rpcserver):
        self.path=rpcserver
    def request(self,petition):
        print "Opening %s" %(self.path)
        f = urllib2.urlopen(self.path + "?" + petition)
        response = f.read()
        f.close()
        return response

def cleanUpOldTempFiles():
    cur_stamp=int(time.time())
    for d in glob.glob(upload_dir + "/*"):
        try:
            mstat=os.stat(d + "/upload.raw")
        except OSError:
            continue
        if mstat[8]<(cur_stamp-(5*60)): #5 minutos
            for f in glob.glob(d + "/*"):
                #print "removing %s" %(f)
                os.remove(f)
            os.rmdir(d)

def deleteTempFiles():
    #Borrar los ficheros si existen
    if dpath:
        if os.path.isfile(dpath + "/lenght.txt"):
            os.remove(dpath + "/lenght.txt")
        if os.path.isfile(dpath + "/upload.raw"):
            os.remove(dpath + "/upload.raw")
        os.rmdir(dpath)

def error(msg,head=True,die=True,delete=False):
    if delete:
        deleteTempFiles()
    if head:
        print """
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Upload</title>
</head>
<body>
"""
    print "DBG: Upload failed - "
    print msg
    print """
    <script language="JavaScript" type="text/javascript">
        parent.abortUpload("%s");
    </script>
</body>
</html>
    """ %(msg)
    if die:
        sys.exit()    

def main():
    global dpath
    global file_size
    print "Content-Type: text/html\n\n"

    uid=0
    xsid=""
    resource_type=""
    auth_hash=""
    server_name=""
    server_port=""
    filesize=0
    if not os.environ.has_key("SERVER_NAME"):
        error("Cannot get SERVER_NAME")
    server_name=os.environ["SERVER_NAME"]
    if not os.environ.has_key("SERVER_PORT"):
        error("Cannot get SERVER_PORT")
    server_port=int(os.environ["SERVER_PORT"])
    if server_port==80:
        server_port=""
    else:
        server_port=":" + str(server_port)
    server_root_path="http://" + server_name + server_port
    ##rpc_server_path=server_root_path + rpc_path

    #Tamaño aproximado del fichero
    if not os.environ.has_key("CONTENT_LENGTH"):
        error("Cannot get CONTENT_LENGTH")
    filesize=int(os.environ["CONTENT_LENGTH"])

    #Obtener xsid
    if not os.environ.has_key("QUERY_STRING"):
        error("Cannot get Query_string")
    q=os.environ["QUERY_STRING"]
    get=q.split("&")
    geta=[]
    for g in get:
        geta.append(g.split("="))
    del get,q

    for g in geta:
        if g[0]=="xsid":
            xsid=g[1]
        elif g[0]=="uid":
            uid=int(g[1])
        elif g[0]=="type":
            resource_type=g[1]

    #Limpiar xsid (No podemos confiar en su valor)
    if not re.match(r"^[a-f0-9]{32}$",xsid):
        error("xsid validation failed")
    #print "res-%s-" % (resource_type)
    if not re.match(r"^\w+$",resource_type):
        error("malformed resource_type")

    #Obtener AuthHash
    if not os.environ.has_key("HTTP_COOKIE"):
        error("Cannot get HTTP_COOKIE")
    q=os.environ["HTTP_COOKIE"]
    get=q.split(";")
    cookies=[]
    for g in get:
        cookies.append(g.split("="))
    del get,q

    for g in cookies:
        if g[0].strip()=="ApfVoDAuthHash":
            auth_hash=g[1].strip()
            break
    #print "hash-%s-endhash" %auth_hash
    if not re.match(r"^[a-f0-9]{40}$",auth_hash):
        error("auth validation failed")

    #Realizar llamada RPC al la aplicación, y verificar el auth_hash
    rpc=rpcClient(rpc_server_path)
    rpc_reply=rpc.request("cmd=auth_verify&hash=%s&uid=%i" % (auth_hash,uid))
    #print "<br>RPC Response:-%s-<br>" %(rpc_reply)
    if not rpc_reply=="OK":
        error("rpc auth validation failed")

    dpath=upload_dir + "/" + xsid
    if os.path.isdir(dpath):
        error("xsid already exists")

    #Borrar ficheros viejos
    cleanUpOldTempFiles()

    os.makedirs(dpath)

    #Guardar tamaño del fichero
    f=file(dpath + "/lenght.txt","w")
    f.write(str(filesize))
    f.close()

    #La instanciación de la clase provoca la lectura del stdin
    cfile=cgiFiles()

    # Este código, la simple llamada has_key, proboca que la implemetación
    # FieldStorage actual carge el fichero entero en memoria, y esto no lo
    # podemos permitir.
    #if cfile.has_key("sourcefile"):
    #    f=cfile["sourcefile"]
    #    #print f.value, f.filename
    #    if f.filename=="" or f.filename==None or f==None or len(f.value)==0:
    #        error("No File was uploaded",False)
    #else:
    #    error("No File data was uploaded",False)
    if file_size==0:
        error("No File data was uploaded %i" %(file_size),False,True,True)
    elif filesize-file_size>500:
        error("Uploaded file data seems to be incomplete",False,True,True)

    print """
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
</head>
<body>
"""
    #print os.environ
    #print geta
    
    print "DBG: Upload finished"
    time.sleep(1)

    #Realizar llamada RPC indicando el fichero subido al VoD handler
    #Esta la hace el cliente

    print """
    <script language="JavaScript" type="text/javascript">
        parent.finishUpload();
    </script>
</body>
</html>
    """
    #deleteTempFiles()

log=mlog(sys.stdout,"/tmp/python_stdout.txt")
log2=mlog(sys.stderr,"/tmp/python_stderr.txt")
old_stdout=sys.stdout
old_stderr=sys.stderr
sys.stdout=log
sys.stderr=log2

try:
    main()
except SystemExit:
    pass
except:
    error("Unhadled Exception",False,False,True)
    cgitb.handler()

sys.stdout=old_stdout
sys.stderr=old_stderr
