# Run with "zsh DeployNas.sh
sftp Charles@192.168.68.2 <<EOT
lcd "/Users/charlesdavies/documents/tennis web app"
cd /web
put -r src 
put settings.php
put composerNAS.json composer.json
put public/index.php public/
exit
EOT