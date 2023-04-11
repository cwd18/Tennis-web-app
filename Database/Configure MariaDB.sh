# Configure MariaDB
# mysql.server start
# Just press Enter for root password on Mac
sudo mysql -u root
USE mysql
CREATE USER 'tennisapp'@'localhost' IDENTIFIED BY 'put-password-here';
GRANT ALL PRIVILEGES ON *.* TO 'tennisapp'@'localhost';
FLUSH PRIVILEGES;
SELECT Host, User FROM User;
CREATE DATABASE Tennis;