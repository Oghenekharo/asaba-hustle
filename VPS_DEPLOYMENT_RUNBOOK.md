# Asaba Hustle VPS Deployment Runbook

This guide is written for a completely empty Ubuntu VPS.

It is intentionally step-by-step and assumes:

- you are new to VPS setup
- Apache is not installed yet
- Docker is not installed yet
- the project folder does not exist yet
- the domain is managed in Cloudflare

Use this exact order.

## What You Are Deploying

You are deploying:

- the main app at `https://hustle.currencyopts.com`
- websockets at `https://ws.hustle.currencyopts.com`
- Docker containers for:
    - app
    - queue
    - reverb
    - mysql
    - redis
- Apache as the public reverse proxy on ports `80` and `443`

## Before You Start

You need:

1. the VPS IP address
2. SSH access to the VPS
3. your GitHub repo already pushed
4. your domain in Cloudflare
5. these DNS names planned:
   - `currencyopts.com`
   - `hustle.currencyopts.com`
   - `ws.hustle.currencyopts.com`

## Step 1. SSH Into The VPS As Root

For a brand new VPS, you will usually start with `root`.

From your local machine:

```bash
ssh root@YOUR_VPS_IP
```

## Step 2. Create A New Deploy User

Replace `deploy` with any username you prefer:

```bash
adduser deploy
```

When prompted:

- enter a strong password
- fill the rest or just press `Enter`
- type `Y` to confirm

## Step 3. Grant The New User Sudo Access

```bash
usermod -aG sudo deploy
id deploy
```

You should see `sudo` in the groups list.

## Step 4. Set Up SSH Keys For The New User

On the VPS, create the SSH folder:

```bash
mkdir -p /home/deploy/.ssh
chmod 700 /home/deploy/.ssh
touch /home/deploy/.ssh/authorized_keys
chmod 600 /home/deploy/.ssh/authorized_keys
chown -R deploy:deploy /home/deploy/.ssh
```

Now, on your local machine, show your public key:

Windows PowerShell:

```powershell
Get-Content $env:USERPROFILE\.ssh\id_rsa.pub
```

or if you use ed25519:

```powershell
Get-Content $env:USERPROFILE\.ssh\id_ed25519.pub
```

Copy the full key text.

Back on the VPS, open the authorized keys file:

```bash
nano /home/deploy/.ssh/authorized_keys
```

Paste your public key, then save.

## Step 5. Test SSH Login With The New User

From your local machine:

```bash
ssh deploy@YOUR_VPS_IP
```

If that works, continue the rest of the setup as `deploy`.

## Step 6. Update The VPS

Run:

```bash
sudo apt update
sudo apt upgrade -y
```

## Step 7. Install Apache First

Since this is an empty VPS and you want Apache to handle the public domains, install Apache now.

```bash
sudo apt install -y apache2
```

Check that Apache is installed and running:

```bash
sudo systemctl status apache2 --no-pager
```

If it is not running:

```bash
sudo systemctl start apache2
sudo systemctl enable apache2
```

## Step 8. Install Required Base Tools

```bash
sudo apt install -y ca-certificates curl gnupg lsb-release git certbot python3-certbot-apache
```

## Step 9. Install Docker Engine And Docker Compose

Run these exactly:

```bash
sudo install -m 0755 -d /etc/apt/keyrings
curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo gpg --dearmor -o /etc/apt/keyrings/docker.gpg
sudo chmod a+r /etc/apt/keyrings/docker.gpg
echo "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] https://download.docker.com/linux/ubuntu $(. /etc/os-release && echo "$VERSION_CODENAME") stable" | sudo tee /etc/apt/sources.list.d/docker.list > /dev/null
sudo apt update
sudo apt install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin
```

Add your current user to the Docker group:

```bash
sudo usermod -aG docker $USER
newgrp docker
```

Check:

```bash
docker --version
docker compose version
```

## Step 10. Open The Needed Firewall Ports

If you use UFW:

```bash
sudo ufw allow 22
sudo ufw allow 'Apache Full'
sudo ufw enable
sudo ufw status
```

Important:

- do not open public MySQL port `3306`
- do not open public Redis port `6379`

`Apache Full` opens both:

- port `80`
- port `443`

## Step 11. Point DNS In Cloudflare

Do this before trying SSL.

Create these DNS records:

1. `A` record for `currencyopts.com` -> VPS IP
2. `A` record for `hustle.currencyopts.com` -> VPS IP
3. `A` record for `ws.hustle.currencyopts.com` -> VPS IP

Recommended:

- `currencyopts.com`: your normal choice
- `hustle.currencyopts.com`: can be proxied if you want
- `ws.hustle.currencyopts.com`: if websocket TLS gives trouble, switch this one to `DNS only`

## Step 12. Create The App Folder

Now create the directory structure.

```bash
mkdir -p ~/apps
cd ~/apps
```

Check:

```bash
pwd
ls
```

## Step 9. Clone The Project

Replace the repo URL with your own:

```bash
cd ~/apps
git clone https://github.com/YOUR_GITHUB_USERNAME/asaba-hustle.git
cd asaba-hustle
```

Check:

```bash
pwd
ls
```

At this point, the app folder now exists:

- `~/apps/asaba-hustle`

## Step 14. Create `.env.production`

Now that the folder exists, create the production env file.

```bash
cd ~/apps/asaba-hustle
cp .env.production.example .env.production
nano .env.production
```

Paste and edit these important values:

```env
APP_NAME="Asaba Hustle"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://hustle.currencyopts.com
APP_TIMEZONE=Africa/Lagos

APP_DOMAIN=hustle.currencyopts.com
REVERB_DOMAIN=ws.hustle.currencyopts.com

APP_IMAGE=yourdockerhubname/asaba-hustle
APP_IMAGE_TAG=latest
RUN_MIGRATIONS=true

ADMIN_USER_NAME="Platform Admin"
ADMIN_USER_EMAIL=admin@hustle.currencyopts.com
ADMIN_USER_PHONE=
ADMIN_USER_PASSWORD=

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=asaba_hustle
DB_USERNAME=asaba
DB_PASSWORD=change_this_db_password
MYSQL_ROOT_PASSWORD=change_this_root_password

LOG_CHANNEL=stack
LOG_LEVEL=warning

BROADCAST_CONNECTION=reverb
FILESYSTEM_DISK=public
QUEUE_CONNECTION=database
CACHE_STORE=file
SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_DOMAIN=hustle.currencyopts.com
SANCTUM_STATEFUL_DOMAINS=hustle.currencyopts.com

REDIS_CLIENT=phpredis
REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="no-reply@hustle.currencyopts.com"
MAIL_FROM_NAME="${APP_NAME}"

NIGERIABULKSMS_USERNAME=
NIGERIABULKSMS_PASSWORD=
NIGERIABULKSMS_SENDER=AsabaHustle
NIGERIABULKSMS_BASE_URL=https://portal.nigeriabulksms.com/api/

REVERB_APP_ID=398998
REVERB_APP_KEY=replace_this_reverb_key
REVERB_APP_SECRET=replace_this_reverb_secret
REVERB_HOST=reverb
REVERB_PORT=8080
REVERB_SCHEME=http
REVERB_SERVER_HOST=0.0.0.0
REVERB_SERVER_PORT=8080

VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST=ws.hustle.currencyopts.com
VITE_REVERB_PORT=443
VITE_REVERB_SCHEME=https
```

Generate an `APP_KEY` on the VPS:

```bash
php -r "echo 'base64:'.base64_encode(random_bytes(32)).PHP_EOL;"
```

Copy the output and paste it into `APP_KEY=` inside `.env.production`.

Save in `nano`:

1. `Ctrl + O`
2. press `Enter`
3. `Ctrl + X`

## Step 15. Build And Start Docker Containers

From the repo root:

```bash
cd ~/apps/asaba-hustle
docker compose -f docker-compose.prod.yml --env-file .env.production up -d --build
```

Check status:

```bash
docker compose -f docker-compose.prod.yml --env-file .env.production ps
```

## Step 16. Seed Production-Safe Data

Run migrations and seed only the safe production seeder:

```bash
cd ~/apps/asaba-hustle
docker compose -f docker-compose.prod.yml --env-file .env.production exec app php artisan migrate --force
docker compose -f docker-compose.prod.yml --env-file .env.production exec app php artisan db:seed --class=ProductionSeeder --force
docker compose -f docker-compose.prod.yml --env-file .env.production exec app php artisan optimize:clear
```

## Step 17. Enable Apache Modules Needed For Reverse Proxy And Websockets

```bash
sudo a2enmod proxy
sudo a2enmod proxy_http
sudo a2enmod proxy_wstunnel
sudo a2enmod rewrite
sudo a2enmod headers
sudo a2enmod ssl
sudo systemctl restart apache2
```

## Step 18. Create The Main App Apache Config

Create the file:

```bash
sudo nano /etc/apache2/sites-available/hustle.currencyopts.com.conf
```

Paste this:

```apache
<VirtualHost *:80>
    ServerAdmin admin@currencyopts.com
    ServerName hustle.currencyopts.com

    ProxyPreserveHost On
    ProxyRequests Off

    ProxyPass / http://127.0.0.1:8000/
    ProxyPassReverse / http://127.0.0.1:8000/

    ErrorLog ${APACHE_LOG_DIR}/hustle-error.log
    CustomLog ${APACHE_LOG_DIR}/hustle-access.log combined

    RewriteEngine on
    RewriteCond %{SERVER_NAME} =hustle.currencyopts.com
    RewriteRule ^ https://%{SERVER_NAME}%{REQUEST_URI} [END,NE,R=permanent]
</VirtualHost>
```

Save and exit.

## Step 19. Create The Websocket Apache Config

Create the file:

```bash
sudo nano /etc/apache2/sites-available/ws.hustle.currencyopts.com.conf
```

Paste this:

```apache
<VirtualHost *:80>
    ServerName ws.hustle.currencyopts.com
    RewriteEngine On
    RewriteRule ^ https://%{SERVER_NAME}%{REQUEST_URI} [END,NE,R=permanent]
</VirtualHost>
```

Save and exit.

## Step 20. Enable The New Apache Sites

```bash
sudo a2ensite hustle.currencyopts.com.conf
sudo a2ensite ws.hustle.currencyopts.com.conf
sudo apache2ctl configtest
sudo systemctl reload apache2
```

## Step 21. Generate SSL Certificates

```bash
sudo certbot --apache -d hustle.currencyopts.com
sudo certbot --apache -d ws.hustle.currencyopts.com
```

This will create SSL config files automatically, usually:

- `/etc/apache2/sites-available/hustle.currencyopts.com-le-ssl.conf`
- `/etc/apache2/sites-available/ws.hustle.currencyopts.com-le-ssl.conf`

## Step 22. Fix The Websocket SSL Vhost

Open the websocket SSL file:

```bash
sudo nano /etc/apache2/sites-available/ws.hustle.currencyopts.com-le-ssl.conf
```

Replace its contents with:

```apache
<IfModule mod_ssl.c>
<VirtualHost *:443>
    ServerName ws.hustle.currencyopts.com
    SSLEngine on

    ProxyPreserveHost On
    ProxyRequests Off
    RewriteEngine On

    RewriteCond %{HTTP:Upgrade} websocket [NC]
    RewriteCond %{HTTP:Connection} upgrade [NC]
    RewriteRule /(.*) ws://127.0.0.1:8080/$1 [P,L]

    ProxyPass / http://127.0.0.1:8080/
    ProxyPassReverse / http://127.0.0.1:8080/

    RequestHeader set X-Forwarded-Proto "https"

    ErrorLog ${APACHE_LOG_DIR}/hustle-ws-error.log
    CustomLog ${APACHE_LOG_DIR}/hustle-ws-access.log combined

    SSLCertificateFile /etc/letsencrypt/live/ws.hustle.currencyopts.com/fullchain.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/ws.hustle.currencyopts.com/privkey.pem
    Include /etc/letsencrypt/options-ssl-apache.conf
</VirtualHost>
</IfModule>
```

Save and exit.

Reload Apache:

```bash
sudo apache2ctl configtest
sudo systemctl reload apache2
```

## Step 23. Check The Running Stack

Check Docker:

```bash
cd ~/apps/asaba-hustle
docker compose -f docker-compose.prod.yml --env-file .env.production ps
```

Check app logs:

```bash
docker compose -f docker-compose.prod.yml --env-file .env.production logs -f app
```

Check queue logs:

```bash
docker compose -f docker-compose.prod.yml --env-file .env.production logs -f queue
```

Check reverb logs:

```bash
docker compose -f docker-compose.prod.yml --env-file .env.production logs -f reverb
```

## Step 24. Check Apache And SSL

```bash
sudo apache2ctl -S
curl -I https://hustle.currencyopts.com
curl -I https://ws.hustle.currencyopts.com
curl -I http://127.0.0.1:8080
```

Notes:

- `http://127.0.0.1:8080` can return `404` and still be okay
- what matters is that something is listening there

## Step 25. Open The Site

Open in your browser:

- `https://hustle.currencyopts.com`

If websockets are enabled and working, the frontend should also connect to:

- `wss://ws.hustle.currencyopts.com`

## Step 26. Optional Hardening After Login Works

Once you confirm the new `deploy` user works with SSH keys, you can harden SSH access further.

Open the SSH config:

```bash
sudo nano /etc/ssh/sshd_config
```

Common hardening changes:

```txt
PermitRootLogin no
PasswordAuthentication no
PubkeyAuthentication yes
```

Then restart SSH:

```bash
sudo systemctl restart ssh
```

Only do this after confirming key-based login works for your new user.

## Step 27. Future Deployment Updates

When you push new code to GitHub, update the VPS like this:

```bash
cd ~/apps/asaba-hustle
git fetch origin
git reset --hard origin/master
docker compose -f docker-compose.prod.yml --env-file .env.production up -d --build
docker compose -f docker-compose.prod.yml --env-file .env.production exec app php artisan migrate --force
docker compose -f docker-compose.prod.yml --env-file .env.production exec app php artisan optimize:clear
```

## Step 28. Useful Commands Later

Restart containers:

```bash
cd ~/apps/asaba-hustle
docker compose -f docker-compose.prod.yml --env-file .env.production up -d
```

Stop containers:

```bash
cd ~/apps/asaba-hustle
docker compose -f docker-compose.prod.yml --env-file .env.production down
```

Rebuild containers:

```bash
cd ~/apps/asaba-hustle
docker compose -f docker-compose.prod.yml --env-file .env.production up -d --build --force-recreate
```

Check app logs:

```bash
cd ~/apps/asaba-hustle
docker compose -f docker-compose.prod.yml --env-file .env.production logs -f app
```

## Step 29. HeidiSQL Connection To VPS Database

You do not need to open public MySQL port `3306`.

Use SSH tunnel in HeidiSQL with:

- SSH host: your VPS IP
- SSH port: `22`
- SSH user: your VPS username
- Database host: `127.0.0.1`
- Database port: `3306`
- Database user: value of `DB_USERNAME`
- Database password: value of `DB_PASSWORD`
- Database name: value of `DB_DATABASE`

## Step 30. Basic Live Test Checklist

After deployment, test:

1. register/login
2. phone verification
3. client creates a job
4. verified worker applies
5. negotiation works
6. worker is hired
7. chat opens after assignment
8. worker accepts and starts job
9. worker completes the job
10. client marks paid
11. worker confirms payment
12. notifications arrive
13. realtime chat updates work

## If Something Fails

Check app container:

```bash
cd ~/apps/asaba-hustle
docker compose -f docker-compose.prod.yml --env-file .env.production logs -f app
```

Check websocket Apache errors:

```bash
sudo tail -n 100 /var/log/apache2/hustle-ws-error.log
```

Check Apache loaded sites:

```bash
sudo apache2ctl -S
```

Force VPS repo to match GitHub exactly:

```bash
cd ~/apps/asaba-hustle
git fetch origin
git reset --hard origin/main
```
