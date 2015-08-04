# bantaypanahon
[DISCLAIMER] all rights reserved to the owner/author of the third party libraries used, list to be updated.

Western Visayas Philippines Weather Monitoring Tool for ASTI Devices
A project by Department of Science and Technology Regional Office No. VI

-- Php Notes

PDO sqlite module must be enabled

-- Install notes on Mac OS X

run the following commands
    
  sudo chmod -R 775 /bantaypanahon
    //set dbfile owner and permission to _www
    sudo chown _www /bantaypanahon/database/
    sudo chown _www /bantaypanahon/database/sqlite.db
    sudo chmod u+w /bantaypanahon/database/
    sudo chmod u+w /bantaypanahon/database/sqlite.db 
  
