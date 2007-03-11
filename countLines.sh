#!/bin/sh

one=`find -name "*.php" -exec cat \{\} \; | wc -l`
two=`find -name "*.py" -exec cat \{\} \; | wc -l`
three=`find -name "*.sh" -exec cat \{\} \; | wc -l`
let res=$one+$two+$three
echo "PHP lines: $one"
echo "Python lines: $two"
echo "Script lines: $three"
echo "Total: $res"
