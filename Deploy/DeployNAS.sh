sftp Charles@192.168.68.2 <<EOT
lcd "/Users/charlesdavies/documents/tennis web app/WebPages"
cd /web/tennis
put Home/Home.html 
put Series/*.php
put Series/*.html
put Users/*.php
put Users/*.html
put Fixture/*.php
lcd "/Users/charlesdavies/documents/tennis web app"
put Library/*.php
exit
EOT