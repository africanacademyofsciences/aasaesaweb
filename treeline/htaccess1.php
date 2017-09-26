ErrorDocument 401 /treeline/error.php?error=401
ErrorDocument 403 /treeline/error.php?error=403
ErrorDocument 404 /treeline/error.php?error=404
ErrorDocument 500 /treeline/error.php?error=500

RewriteEngine Off
RewriteEngine On


# PLUGINS/EXTRAS

RewriteRule ^(events|members|newsletters|landingpages)$ $1/ [R]
RewriteRule ^(events|members|newsletters|landingpages)/?$ $1/index.php [QSA]

# NEWSLETTERS

RewriteRule ^newsletters/subscribers/(browse|download)$ /treeline/newsletters/susbscribers/$1/ [R]
RewriteRule ^newsletters/subscribers/(browse|download)/?$ /treeline/newsletters/subs$1.php [QSA]

RewriteRule ^newsletters/subscribers$ /treeline/newsletters/susbscribers/ [R]
RewriteRule ^newsletters/subscribers/?$ /treeline/newsletters/subsbrowse.php [QSA]

RewriteRule ^newsletters/preferences/(browse|edit)$ /treeline/newsletters/preferences/$1/ [R]
RewriteRule ^newsletters/preferences/(browse|edit)/?$ /treeline/newsletters/pref$1.php [QSA]

RewriteRule ^newsletters/preferences$ /treeline/newsletters/preferences/ [R]
RewriteRule ^newsletters/preferences/?$ /treeline/newsletters/prefbrowse.php [QSA]

RewriteRule ^newsletters/(edit|browse)$ /treeline/newsletters/$1/ [R]
RewriteRule ^newsletters/(edit|browse)/?$ /treeline/newsletters/news$1.php [QSA]

RewriteRule ^newsletters/(news|subs|pref|digest)(edit|send|browse|browse|browse|edit|download|edit|edit)$ /treeline/newsletters/$1$2/ [R]
RewriteRule ^newsletters/(news|subs|pref|digest)(edit|send|browse|browse|browse|edit|download|edit|edit)/?$ /treeline/newsletters/$1$2.php [QSA]

# MENU MANGER
RewriteRule ^menus$ menus/ [R]
RewriteRule ^menus/?$ menumanager.php [QSA]



# ALL OTHER PAGES
RewriteRule ^([a-zA-Z0-9]+)$ $1/ [R]
RewriteRule ^([a-zA-Z0-9]+)/?$ $1.php [QSA]
