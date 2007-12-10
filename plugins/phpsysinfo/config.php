<?php
/* $Id: config.php,v 1.1 2007/12/10 18:23:20 gpowers Exp $
 * 
 * Copyright (c) 2006, 2007 by phpSysInfo
 * http://phpsysinfo.sourceforge.net/
 * 
 * This program is free software; you can redistribute it
 * and/or modify it under the terms of the
 * GNU General Public License version 2 (GPLv2)
 * as published by the Free Software Foundation.
 * See COPYING for details.
 *
 */

// define the default language and template here
define('lang', 'en');
define('template', 'jstyle_clean.css');

// display the virtual host name and address
// default is canonical host name and address
// Use define('useVhost', true); to display virtual host name.
define('useVhost', false);

// define the motherboard monitoring program here
// we support four programs so far
// 1. lmsensors  http://www.lm-sensors.org/
// 2. healthd    http://healthd.thehousleys.net/
// 3. hwsensors  http://www.openbsd.org/
// 4. mbmon      http://www.nt.phys.kyushu-u.ac.jp/shimizu/download/download.html
// 5. mbm5       http://mbm.livewiredev.com/

// Example: If you want to use lmsensors.
// define('sensorProgram', 'lmsensors');
define('sensorProgram', false);

// show mount point
// true = show mount point
// false = do not show mount point
define('showMountPoint', true);

// show bind
// true = display filesystems mounted with the bind options under Linux
// false = hide them
define('showBind', false);

// show inode usage
// true = display used inodes in percent
// false = hide them
define('showInodes', true);

// Hide mount(s). Example:
// define('hideMounts', '/home,/usr');
define('hideMounts', '');

// Hide filesystem typess. Example:
// define('hideFstypes', 'tmpfs,usbfs');
define('hideFstypes', '');

// if the hddtemp program is available we can read the temperature, if hdd is smart capable
// !!ATTENTION!! hddtemp might be a security issue
// define('hddTemp', 'tcp');	// read data from hddtemp deamon (localhost:7634)
// define('hddTemp', 'suid');     // read data from hddtemp programm (must be set suid)
define('hddTemp', false);

// show a graph for current cpuload
// true = displayed, but it's a performance hit (because we have to wait to get a value, 1 second)
// false = will not be displayed
define('loadBar', false);

// additional paths where to look for installed programs
// e.g. define('addPaths', '/opt/bin','/opt/sbin');
define('addPaths', false);


// format in which temperature is displayed (not implemented)
// 'c'    shown in celsius
// 'f'    shown in fahrenheit
// 'c-f'	both shown first celsius and fahrenheit in braces
// 'f-c'	both shown first fahrenheit and celsius in braces
define('tempFormat', 'c');
