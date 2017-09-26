#!/bin/bash

#------------------------------------------------#
# Search web files for potential malicious code. #
#------------------------------------------------#

SEARCH_DIR="$HOME/httpdocs"
PATTERNS="passthru|shell_exec|system|phpinfo|base64_decode|popen|exec|proc_open|pcntl_exec|python_eval|readfile"
echo "Searching for malicious code.." 
grep -RP --include=*.{php,txt} "($PATTERNS)" $SEARCH_DIR > backdoor_check.txt
echo "Done! Results saved in backdoor_check.txt" 
exit 0

