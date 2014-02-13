  upstream cronrat_server{
        server 127.0.0.1:8082;
    }

    upstream http_server{
        server 127.0.0.1:8082;
    }


# the nginx server instance
server {
    listen 0.0.0.0:80 default deferred;
    server_name ix.cronrat.net;
    access_log /var/log/nginx/cronrat.log;

    root /var/www/cronrat/public/;
    index index.php index.html index.htm;

    # pass the request to the cronrat server on 8082
    location /rat/ {
       proxy_set_header X-Real-IP $remote_addr;
       proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
       proxy_set_header Host $http_host;
       proxy_set_header X-NginX-Proxy true;
       proxy_pass http://cronrat_server;
       proxy_redirect off;
    }

    # pass the request to the cronrat server on 8082
    location /health {
       proxy_set_header X-Real-IP $remote_addr;
       proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
       proxy_set_header Host $http_host;
       proxy_set_header X-NginX-Proxy true;
       proxy_pass http://cronrat_server;
       proxy_redirect off;
    }

    #now the website

    location / {
             try_files $uri $uri/ /index.php$is_args$args;
       }

    # pass the PHP scripts to FastCGI server listening on /var/run/php5-fpm.sock
    location ~ \.php$ {
                try_files $uri /index.php =404;
                fastcgi_pass unix:/var/run/php5-fpm.sock;
                fastcgi_index index.php;
                fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
                include fastcgi_params;
        }
 }

    