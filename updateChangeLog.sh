#!/bin/sh

echo "Este fichero has sido autom�ticamente generado a partir de svn2cl" > ChangeLog

#svn -v log >> ChangeLog

svn update
svn2cl -i --stdout >> ChangeLog
