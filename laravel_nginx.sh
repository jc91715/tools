#!/bin/bash

if [ -n "$3" ]; then
  https="
    ssl on;
    listen 443 ssl;
     ssl_certificate   /etc/nginx/cert/$1/$1.pem;
     ssl_certificate_key  /etc/nginx/cert/$1/$1.key;
     ssl_session_timeout 5m;
     ssl_ciphers ECDHE-RSA-AES128-GCM-SHA256:ECDHE:ECDH:AES:HIGH:!NULL:!aNULL:!MD5:!ADH:!RC4;
     ssl_protocols TLSv1 TLSv1.1 TLSv1.2;
     ssl_prefer_server_ciphers on;

    if (\$scheme != \"https\") {
         return 301 https://\$host\$request_uri;
    }

  "
fi
block="server {
    listen *:80;
    server_name $1;

    root \"$2\";
    index index.php index.html index.htm;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location ~ \.php$ {
        try_files \$uri /index.php =404;
        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        fastcgi_pass unix:/run/php/php7.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
        include fastcgi_params;
            fastcgi_read_timeout 600;
    }

    location ~ /.well-known {
        allow all;
    }

    access_log /var/log/nginx/$1.access.log;
    error_log  /var/log/nginx/$1.error.log;
    client_max_body_size 200M;

    $https

}
"
echo "$block" > "/etc/nginx/sites-available/$1"
ln -fs "/etc/nginx/sites-available/$1" "/etc/nginx/sites-enabled/$1"
