#!/bin/sh

one=`find -name "*.php" -exec cat \{\} \; | wc -l`
two=`find -name "*.py" -exec cat \{\} \; | wc -l`
let res=$one+$two
echo "PHP lines: $one"
echo "Python lines: $two"
echo "Total: $res"
