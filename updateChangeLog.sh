#!/bin/sh

echo "Este fichero has sido automáticamente generado a partir de svn -v log" > ChangeLog

svn -v log >> ChangeLog
