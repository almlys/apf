#!/bin/sh

echo "Este fichero has sido autom�ticamente generado a partir de svn -v log" > ChangeLog

svn -v log >> ChangeLog
