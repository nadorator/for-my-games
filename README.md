# Video Game Content API

A RESTful API for managing video game-related content, built with Phalcon 5.8 and following a microservices architecture with Domain-Driven Design (DDD) principles.

## Architecture

The API is organized into three microservices:

1. **User Service**: Manages visitors and editors
2. **Content Service**: Handles creation, retrieval, and moderation of game-related content
3. **Auth Service**: Manages JWT issuance and validation

## Getting Started

### Prerequisites

- Docker and Docker Compose
- Git

### Installation

1. Clone the repository:
   ```bash
   git clone https://github.com/yourusername/videogamecontent-api.git
   cd videogamecontent-api
   ```

2. Create a `.env` file based on the `.env-dummy` template:
   ```bash
   cp .env-dummy .env
   ```

3. Edit the `.env` file with your desired configuration.

4. Start the services:
   ```bash
   docker-compose up -d
   ```

5. The API will be available at:
   - http://localhost:8909/v1/

## API Endpoints

### User Service

#### Visitor Endpoints (Unauthenticated)

- `POST /v1/visitors`: Register a new visitor
- `GET /v1/visitors/{visitor_id}`: Retrieve visitor details

#### Editor Endpoints (Authenticated via JWT)

- `GET /v1/editors`: List all editors (admin only)
- `POST /v1/editors`: Create a new editor (admin only)
- `DELETE /v1/editors/{editor_id}`: Delete an editor (admin only)

### Auth Service

- `POST /v1/auth/login`: Authenticate an editor and issue a JWT
- `POST /v1/auth/refresh`: Refresh an existing JWT

### Content Service

#### Game Endpoints (Unauthenticated)

- `GET /v1/games`: List all games
- `GET /v1/games/{game_id}`: Retrieve a specific game
- `POST /v1/games`: Create a new game

#### Content Endpoints (Mixed Authentication)

- `POST /v1/contents`: Create a new content item (unauthenticated)
- `GET /v1/contents/{content_id}`: Retrieve a specific content item (unauthenticated)
- `PUT /v1/contents/{content_id}`: Update a content item (visitor must match visitor_id)
- `GET /v1/contents`: List all content items (paginated, authenticated)
- `PATCH /v1/contents/{content_id}/moderate`: Moderate a content item (authenticated, admin/moderator)

## Authentication

The API uses JWT (JSON Web Tokens) for authentication. To access protected endpoints:

1. Obtain a token via the `/v1/auth/login` endpoint
2. Include the token in the Authorization header of your requests:
   ```
   Authorization: Bearer your-token-here
   ```

## User Roles

- **Visitors**: Can create, read, and update their own content (unauthenticated but tracked via UUID)
- **Editors**: Can read all content, moderate it, and manage users (authenticated via JWT)
  - **Moderator**: Can moderate content
  - **Admin**: Can moderate content and manage editors

## Content Types

The API supports three types of content:

- **Tests**: Testing information for games
- **Configurations**: Configuration settings for games
- **Soluces**: Solutions and guides for games

## Development

### Project Structure

```
├── app/
│   ├── Domain/
│   │   ├── Model/
│   │   └── Repository/
│   ├── Infrastructure/
│   │   ├── Auth/
│   │   ├── Database/
│   │   └── Middleware/
│   └── Services/
│       ├── Auth/
│       ├── Content/
│       └── User/
├── docker/
│   ├── mariadb/
│   └── nginx/
├── public/
│   ├── auth-service.php
│   ├── content-service.php
│   └── user-service.php
├── .env
├── docker-compose.yml
└── README.md
```

## License

This project is licensed under the MIT License - see the LICENSE file for details.