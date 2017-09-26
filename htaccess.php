

ErrorDocument 401 /error.php?error=401
ErrorDocument 403 /error.php?error=403
ErrorDocument 404 /error.php?error=404
ErrorDocument 500 /error.php?error=500



FileETag none
php_flag zlib.output_compression On 
php_value zlib.output_compression_level 5
AddHandler application/x-httpd-php .css .js
php_value auto_prepend_file /var/www/vhosts/ichameleon.com/subdomains/csipu/httpdocs/treeline/includes/compressor.php






<IfModule mod_expires.c>
ExpiresActive On 
ExpiresByType image/gif A2592000 
ExpiresByType image/png A2592000 
ExpiresByType image/jpeg A2592000 
ExpiresByType image/x-icon A2592000 
ExpiresByType application/pdf A2592000 
ExpiresByType application/x-javascript A2592000 
ExpiresByType text/javascript A2592000 
#ExpiresByType text/html A2592000 
ExpiresByType text/plain A2592000 
ExpiresByType text/css A2592000 
ExpiresByType application/x-shockwave-flash A2592000 
</IfModule>



php_value error_reporting 6135
RewriteEngine On

# Reroute all files to /treeline/rewrite.php - except special files e.g. images/downloads extra
RewriteCond %{REQUEST_URI} !^/.*\.(css|jpg|gif|png|swf|ico|js|fla|jpeg|txt|doc|csv|xls|xml|rss|xlst|zip|gzip|rar|pdf|html)$ [NC]
RewriteCond %{REQUEST_URI} !^/extranet/ [NC]
RewriteRule ^([a-zA-Z0-9]+)? /treeline/rewrite.php [QSA]
