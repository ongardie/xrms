echo "This script should install WebCalendar and patch it to work with XRMS."
echo "If you have a problem, please contact gpowers@users.sourceforge.net"
echo ""
echo "Trying to get WebCalendar from easynews mirror."
echo "If this fails, get the current download link from"
echo "http://sourceforge.net/projects/webcalendar/"
wget http://easynews.dl.sourceforge.net/sourceforge/webcalendar/WebCalendar-0.9.43.tgz
echo "Untarring Archive:"
tar xzvf WebCalendar-0.9.43.tgz
echo "Renaming WebCalendar-0.9.43 to src"
mv WebCalendar-0.9.43 src
echo ""
echo "your php binary is at:"
which php
echo "check php binary location (on the first line) in tools/send_reminders.php"echo "it should match the location above. Launching vi for you."
echo "If you don't know how to use vi, presss : q ! to exit vi and use pico"
echo "Waiting for you to read this."
sleep 5
vi src/tools/send_reminders.php
echo "Copying patched files to src. I'm not making a backup. You have the tarball."
cp config.php src/includes
cp user-xrms.php src/includes
cp init.php src/includes
