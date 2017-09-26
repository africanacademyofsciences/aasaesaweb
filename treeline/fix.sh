#!/bin/bash
# This script will fix the sql dumps so views are created

sql_file=$1

if [ ! -f $sql_file ]
then
	echo "$sql_file was not found"
else
	grep -v "CREATE ALGORITHM=UNDEFINED" $sql_file | grep -v "!50013 DEFINER=" > $sql_file.new
	sed -i 's/!50001 VIEW/!50001 CREATE VIEW/g' $sql_file.new
	echo "New SQL file: $sql_file.new"
fi
