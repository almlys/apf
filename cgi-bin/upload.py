#!/usr/bin/python
# -*- coding: iso-8859-15 -*-
#
#  Copyright (c) 2005-2006 Alberto Montañola Lacort.
#  Licensed under the GNU GPL. For full terms see the file COPYING.
#
#  Id: $Id: test.php 35 2006-05-23 13:37:00Z alberto $
#

#Configuración
upload_dir="/tmp/apf_upload"
rpc_path="/tfc/ajaxrpc.php"


################################################################################
#### NO TOCAR NADA DEBAJO DE LA BARRA A NO SER QUE SEPAS LO QUE HACES ##########
################################################################################
########## THE BAR #############################################################
################################################################################

#Global (idealmente tendria que ser una propiedad miembro de la clase cgiFiles
# pero parece ser que al añadir el constructor da problemas)"
dpath=""

import os,time,cgi,cgitb,sys,re,urllib2
import os.path
cgitb.enable()


class slowFile:
    def __init__(self,path):
        self.f=file(path + "/upload.raw","wb")
        self.quota=0
        self.write_quota=0
    def __del__(self):
        self.f.close()
    def read(self,len):
        return self.f.read(len)
    def write(self,input):
        w=len(input)
        self.quota=self.quota+w
        self.write_quota=self.write_quota+w
        #flushear el buffer cada 1K
        if self.write_quota>1024:
            self.f.flush()
            self.write_quota=0
        #Limitar la velocidad de subida
        if self.quota>1024*250:
            print self.quota
            sys.stdout.flush()
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
        f = urllib2.urlopen(self.path + "?" + petition)
        response = f.read()
        f.close()
        return response

def error(msg,head=True,die=True):
    if head:
        print """
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
</head>
<body>
"""
    print "DBG: Upload failed - "
    print msg
    print """
    <script language="JavaScript" type="text/javascript">
        parent.abortUpload();
    </script>
</body>
</html>
    """
    if die:
        sys.exit()    

def main():
    global dpath
    print "Content-Type: text/html\n\n"

    xsid=""
    auth_hash=""
    server_name=""
    server_port=""
    if not os.environ.has_key("SERVER_NAME"):
        error("Cannot get SERVER_NAME")
    server_name=os.environ["SERVER_NAME"]
    if not os.environ.has_key("SERVER_PORT"):
        error("Cannot get SERVER_PORT")
    server_port=os.environ["SERVER_PORT"]
    if server_port==80:
        server_port=""
    else:
        server_port=":" + str(server_port)
    server_root_path="http://" + server_name + server_port
    rpc_server_path=server_root_path + rpc_path

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
            break

    #Limpiar xsid (No podemos confiar en su valor)
    if not re.match(r"^[a-f0-9]{32}$",xsid):
        error("xsid validation failed")

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
    print "<br>RPC Response: "
    print rpc.request("cmd=auth_verify&hash=%s" % auth_hash)
    print "<br>"

    dpath=upload_dir + "/" + xsid
    if os.path.isdir(dpath):
        error("xsid already exists")

    os.makedirs(dpath)

    file=cgiFiles()

    print """
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
</head>
<body>
"""
    print os.environ
    print geta
    
    print "DBG: Upload finished"
    time.sleep(1)

    #Realizar llamada RPC indicando el fichero subido al VoD handler

    print """
    <script language="JavaScript" type="text/javascript">
        parent.abortUpload();
    </script>
</body>
</html>
    """

try:
    main()
except SystemExit:
    pass
except:
    error("Unhadled Exception",False,False)
    cgitb.handler()

