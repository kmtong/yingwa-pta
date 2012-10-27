#!/bin/sh

WPDIR=`find . -name 'wordpress' -type d | head -1`

if [ "$WPDIR"x == "x" ]
then
    WPDIR=`find .. -name 'wordpress' -type d | head -1`
    if [ "$WPDIR"x == "x" ]
    then
        echo "No WordPress folder found!"
        exit 1
    fi
fi

echo WordPress Directory: $WPDIR
echo "Fixing Perms..."

chgrp -R apache $WPDIR/wp-content
chmod -R g+rwx $WPDIR/wp-content

echo "DONE"
