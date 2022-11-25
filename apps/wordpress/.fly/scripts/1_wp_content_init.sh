FOLDER=/var/www/html/wp-content
if [ ! -d "$FOLDER" ]; then
 echo "$FOLDER is not a directory, copying wp-content_ content to wp-content"
 cp -r /var/www/html/wp-content_/. /var/www/html/wp-content
 echo "Deleting wp-content_..."
 rm -rf /var/www/html/wp-content_
fi
