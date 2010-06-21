# CampFireManager #

CampFireManager is designed to help you organise talks at BarCamp and
Unconference Style events. By automatically sorting rooms by number of
attendees, dynamically finding time slots when your selected time slot is full
and making full use of external sources to help streamline attendees
interaction, CampFireManager is a simple-to-use system to make the most of
your event.

## License ##

All code, unless otherwise noted, is released under :

    GNU Affero General Public License, version 3.0

Author: Jon Spriggs (jon@spriggs.org.uk) 

Date: 2010-01-28

Version: 0.1-ALPHA

## Requirements ##

These packages are based on requirements in Ubuntu:

    gammu-smsd
    apache2
    php5-mysql
    mysql-server
    php5-cli
    php5-gmp (for the OpenID packages)

## Installation ##

Install the above packages, then create the MySQL Users and Tables for both 
Gammu (the SMS engine) and CampFireManager. There's no technical reason why
these can't both be within the same database space, but for clarity, I have
separated the presentation database from the command databases. Gammu's 
database structure (on Ubuntu at least) is in 
<tt>/usr/share/doc/gammu/examples/sql/mysql.sql.gz</tt>

Firstly, create the users and the databases - substitute username, password
and hostname with appropriate values. Perform these steps for both Gammu and
CampFireManager.

<pre>
echo "CREATE USER 'username'@'localhost' IDENTIFIED BY 'password';
GRANT USAGE ON *.* TO 'username'@'localhost' IDENTIFIED BY 'password' WITH 
  MAX_QUERIES_PER_HOUR 0 MAX_CONNECTIONS_PER_HOUR 0 MAX_UPDATES_PER_HOUR 0 
  MAX_USER_CONNECTIONS 0;
CREATE DATABASE IF NOT EXISTS databasename;
GRANT ALL PRIVILEGES ON databasename.* TO 'username'@'localhost';" | mysql -u root -p
</pre>

Next, import the table configuration for Gammu

    gunzip -c /usr/share/doc/gammu/examples/sql/mysql.sql.gz | mysql -u username -p databasename

Then the table configuration for CampFireManager

    mysql -u username -p databasename < sql

For each phone or phone-dongle you are connecting, you need to create a text
file, containing the following data:

<pre>
[gammu]
port = <path_to_usb_serial>
Connection = at19200

[smsd]
PhoneID = <phone_name>
CommTimeout = 5
DeliveryReport = sms

service = mysql
user = <database_username>
password = <database_password>
pc = <database_host>
database = <database_data_store>

LogFormat = textall
logfile = stdout
debuglevel = 1
</pre>

For each of those text files, run the following command (you must edit
<tt>/path/to/config</tt>, to match what you have):

    sudo gammu-smsd -c /path/to/config -U gammu

This will confirm that your configuration file is correct for your phone
device. Next, run

    sudo /path/to/CampFireManager/run_svc.sh gammu-smsd -c /path/to/config -U gammu

This will start the script running, even if the script self-terminates.

Configure the CampFireManager database settings by editing the file
/path/to/CampFireManager/db.php and adding the appropriate database settings.

Lastly, run

    /path/to/CampFireManager/run_svc.sh php -q /path/to/CampFireManager/daemon.php

Again, this will keep the script running.

If your instance of CampFireManager is at http://localhost/ then your
administration interface is at http://localhost/admin.php - it will force you 
to log in using OpenID first. Once you've authenticated, you'll have to click
on the "Modify config values" link on the main page.
