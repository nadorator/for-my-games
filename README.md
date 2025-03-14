# For-My-Games

> Development environment configuration using Podman with Phalcon 5.8 and PHP 8.2

## 📋 Overview
This project provides a ready-to-use Podman configuration for application development with Phalcon 5.8 and PHP 8.2.

## 🔧 Prerequisites
- Podman installed on your system
- Podman Compose
- At least 2GB RAM available
- Port 8909 available (or configurable)
- Local DNS configuration (see Hosts Configuration section)

## 🚀 Installation

### Initial Setup
The project uses Podman as a Docker alternative. Default configuration includes:
- PHP 8.2
- Phalcon 5.8
- Apache 2 web server
- MariaDB (latest stable version)

### Hosts Configuration
You need to configure your local hosts file to map the domains to localhost. Here's how to do it for different operating systems:

#### Linux
```bash
sudo nano /etc/hosts
```
Add these lines:
127.0.0.1 www.for-my.games 
127.0.0.1 api.for-my.games 
127.0.0.1 admin.for-my.games

Save with `CTRL + X`, then `Y`, then `Enter`

#### macOS
```bash
sudo nano /private/etc/hosts
```
Add these lines:
127.0.0.1 www.for-my.games
127.0.0.1 api.for-my.games
127.0.0.1 admin.for-my.games
Save with `CTRL + X`, then `Y`, then `Enter`

#### Windows
1. Open Notepad as Administrator
2. Open file: `C:\Windows\System32\drivers\etc\hosts`
3. Add these lines:
   127.0.0.1 www.for-my.games
   127.0.0.1 api.for-my.games
   127.0.0.1 admin.for-my.games
4. Save the file

### File Structure


## 💻 Usage

### Starting Containers
```bash
podman-compose -f docker-compose.yml up -d
```

### Accessing Container
```bash
podman exec -it api.for-my.games /bin/bash
```

## 🔍 Verifying Installation
After starting the containers, you can verify that everything is working correctly:

1. Open your browser
2. Navigate to `http://www.for-my.games:8909`
3. You should see the Phalcon welcome page

You can also verify the other domains:
- API: `http://api.for-my.games:8909`
- Admin: `http://admin.for-my.games:8909`

## 🛠 Troubleshooting

### Common Issues
- If containers won't start, check if port 8909 is already in use
- Check logs with: `podman-compose logs`
- If domains are not resolving:
    - Verify your hosts file configuration
    - Try flushing your DNS cache:
        - Linux: `sudo systemd-resolve --flush-caches` or `sudo service network-manager restart`
        - macOS: `sudo killall -HUP mDNSResponder`
        - Windows: `ipconfig /flushdns` in Command Prompt as Administrator
- If you can't edit hosts file on Windows:
    - Make sure you're running Notepad as Administrator
    - Check if the file is read-only and remove that attribute if needed

## 📝 Notes
- This configuration is optimized for development
- Additional security adjustments are required for production
- The port 8909 can be changed in the docker-compose.yml file if needed
- Make sure your firewall allows connections to port 8909

## 🤝 Contributing
Contributions are welcome! Please feel free to:
1. Fork the project
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📜 License
This project is licensed under the [MIT](LICENSE)