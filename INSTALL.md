# GovSpendingContracts Project Setup Remote instance

This README outlines the steps to set up the GovSpendingContracts project on an AWS EC2 Ubuntu instance.

## Prerequisites
- An AWS EC2 instance (e.g., t2.medium or higher for sufficient RAM).
- SSH access with a key pair (e.g., `.pem` file).
- Basic knowledge of terminal commands.

## Steps

### 1. Connect to EC2 Instance
SSH into your instance:
```bash
ssh -i <your-key.pem> ubuntu@<your-ec2-ip>
```

### 2. Update and Install Dependencies
Update the package list and install required packages:
```bash
sudo apt update
sudo apt install -y php php-cli php-fpm php-mysql php-xml php-mbstring php-curl php-zip unzip composer nginx
```

### 3. Install Node.js with nvm
Install Node Version Manager (nvm) to manage Node.js versions:
```bash
curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.40.1/install.sh | bash
source ~/.bashrc
```
Install and use Node.js 20.x (required for Vite):
```bash
nvm install 20
nvm use 20
nvm alias default 20
```
Verify the version:
```bash
node -v
```
(Should show 20.19+ e.g., 20.19.4)

### 4. Navigate to Project Directory
Change to your project directory:
```bash
cd ~/GovSpendingContracts
```

### 5. Clear and Reinstall Node Dependencies
Clear npm cache and remove existing dependencies:
```bash
npm cache clean --force
rm -rf node_modules package-lock.json
```
Reinstall dependencies:
```bash
npm install
```

### 6. Configure Laravel Environment
Install PHP dependencies with Composer:
```bash
composer install
```
Copy the example environment file and generate the application key:
```bash
cp .env.example .env
php artisan key:generate
```

### 7. Run Development Server
Start the Vite development server:
```bash
npm run dev
```
- Access locally at `http://localhost:5173`.
- To expose to network, use: `npm run dev -- --host` (secure with firewall if public).

### 8. Troubleshoot (if needed)
- Check memory: `free -m`.
- Check swap: `swapon --show` (add swap with `sudo fallocate -l 2G /swapfile` if low).
- View logs for OOM issues: `dmesg | grep -i "killed"`.

### 9. Configure `.env` with `APP_URL`
Configure the `.env` file with the application URL:
```bash
nano .env
```
Add or update:
```
APP_URL=http://localhost:5173
```
Save and exit (`Ctrl+O`, `Enter`, `Ctrl+X` in nano), then restart the dev server with `npm run dev`.

## Notes
- Ensure sufficient RAM (e.g., 4 GB) to avoid `Killed` errors during `npm install`.
- Test the app locally or via SSH tunneling before public exposure.
