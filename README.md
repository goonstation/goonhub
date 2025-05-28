# Goonhub

[![Analyse](https://github.com/goonstation/goonhub/actions/workflows/analyse.yml/badge.svg)](https://github.com/goonstation/goonhub/actions/workflows/analyse.yml)

## Development

Uses Docker via Laravel Sail. https://laravel.com/docs/11.x/installation#laravel-and-docker

On Windows, I highly suggest doing all development on WSL 2, using VSCode with the remote development extension.

Also install the VSCode devcontainers extension for the easiest possible development environment setup.

Automatically generated docs will be available at http://localhost/docs/api, and the Laravel development assistance thing will be at http://localhost/telescope.

### Environment

Docker should be installed on your host machine. On Windows, install "Docker Desktop".

### Initial setup

- Open the project root in VSCode.
- When prompted, reopen the project in a devcontainer.

  This might take a while as it builds the docker containers.

- Open a VSCode terminal and initiate the first setup commands to bootstrap the database:

```bash
bun install
php artisan gh:setup
```

### Tasks

To build the frontend in development mode, press F5 to run the "Serve Frontend" debug task. This boots the Vite server and opens the debug console. Given the default `.env` file, you can now visit http://localhost.

### Limitations

There are some external services that the API relies on that are not yet public/integrated. As such, the following features are unavailable during development:

- Map switches
- VPN checking (unless you configure with your own IPQualityScore credentials)
