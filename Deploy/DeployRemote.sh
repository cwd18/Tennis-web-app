sftp Charles@192.168.68.2 <<EOT
lcd "/Users/charlesdavies/documents/tennis web app/WebPages"
cd /web/tennis
put Home/Home.html 
put ListSeries/*.php
put ListSeries/*.html
put ListUsers/*.php
put DeleteUser/*.php
put Users/*.php
put Users/*.html
exit
EOT