#!/usr/bin/perl

# don't let the web server run this program:
exit;

while ($line = <STDIN>) {
    chop $line;
    @fields=split(",",$line);
    print 'insert into dunfinder values ("' . $fields[0] . '","' . $fields[1] . '","'
        . substr($fields[2],0,3) . '","' . $fields[2] . '","' . $fields[4] . '","' . $fields[3] . '");' . "\n";
    };
