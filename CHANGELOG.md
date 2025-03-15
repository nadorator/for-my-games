# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.1.7] - 2024-03-19

### Fixed
- Fixed adding php8.4-dom for pest support

## [1.1.6] - 2024-03-19

## Fixed
- Phpmyadmin installation & apache config update

## [1.1.5] - 2024-03-19

## Added
- Integration of Pest PHP while maintaining all existing libraries
- New testing functionality with Pest framework

### Maintained
Preserved all existing dependencies:
- Firebase JWT
- Symfony (Console, HTTP Foundation, Dotenv)
- Predis
- PHPMailer
- Monolog
- Flysystem
- Intervention Image
- Phalcon components
- Guzzle HTTP
- All other original libraries

### Development
- Kept all existing development tools
- Added new testing capabilities with Pest
- Maintained development tools:
  - PHPUnit
  - Mockery
  - Faker

### Changed
- Updated Composer configuration
- Added additional test scripts

### Technical
- Added new Composer scripts for testing:
  - `composer test`
  - `composer test:parallel`

## [1.1.4] - 2024-03-19

### Fixed
- Fixed set mysql root password before installing phpmyadmin

## [1.1.3] - 2024-03-19

### Fixed
- Fixed symbolic links for vendor, data, public, api folder
- Fixed composer install in proper /data/vendor folder
- Fixed proper container name
- Fixed phpmyadmin installation and configuration

## [1.1.2] - 2024-03-19

### Fixed
- Fixed MariaDB initialization issues in Docker build
- Moved database configuration to entrypoint script
- Added proper service startup sequence
- Added database connection verification
- Improved error handling for MariaDB user creation

### Changed
- Restructured entrypoint script for better service management
- Added wait mechanism for MariaDB startup

## [1.1.1] - 2024-03-19

### Fixed
- Added missing PHP extensions required by composer.json:
    - ext-soap
    - ext-imagick
    - ext-intl
    - ext-xdebug
- Added ImageMagick and its development libraries
- Enabled all required PHP extensions after installation

## [1.1.0] - 2024-03-19

### Removed
- Complete removal of Cursor-AI integration
- Removed Node.js dependencies and related configurations

### Optimized
- Significant reduction in Docker image size
- Docker layer optimization by combining RUN commands
- Improved cache management with composer-cache volume

### Changed
- Updated configuration for better Podman compatibility
- Added SELinux tags `:z` for volumes
- Restructured installation commands in Dockerfile

### Added
- Container health check monitoring
- Explicit network configuration in docker-compose.yml
- Environment variables with default values

### Security
- Optimized permissions and access
- Proactive cleanup of temporary files and caches
- Using `--no-install-recommends` flag to reduce attack surface

## [1.0.0] - 2024-03-19

### Added
- Initial project configuration
- PHP 8.2 environment setup
- Phalcon 5.8.0 installation
- Apache and MariaDB configuration
- Build system with Dockerfile and docker-compose.yml

[1.1.1]: https://github.com/username/project/compare/v1.1.0...v1.1.1
[1.1.0]: https://github.com/username/project/compare/v1.0.0...v1.1.0
[1.0.0]: https://github.com/username/project/releases/tag/v1.0.0