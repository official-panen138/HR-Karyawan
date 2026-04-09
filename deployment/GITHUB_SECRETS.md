# GitHub Secrets — Required for CI/CD

Go to your repo **Settings > Secrets and variables > Actions** and add these:

## Backend Deploy (VPS)

| Secret | Description | Example |
|--------|-------------|---------|
| `VPS_HOST` | VPS IP address or hostname | `103.xxx.xxx.xxx` |
| `VPS_USER` | SSH username | `deploy` |
| `VPS_SSH_KEY` | Private SSH key (paste full key) | `-----BEGIN OPENSSH...` |

## Frontend Deploy (Cloudflare Pages)

| Secret | Description | How to get |
|--------|-------------|------------|
| `CLOUDFLARE_API_TOKEN` | API token with Pages edit permission | Cloudflare Dashboard > API Tokens |
| `CLOUDFLARE_ACCOUNT_ID` | Your Cloudflare account ID | Cloudflare Dashboard > Overview sidebar |

## Environment Variables (passed to frontend build)

| Secret | Description | Example |
|--------|-------------|---------|
| `API_URL` | Production API base URL | `https://api.yourdomain.com/api` |
| `REVERB_APP_KEY` | Reverb public key | Same as .env `REVERB_APP_KEY` |
| `REVERB_HOST` | Reverb WebSocket host | `ws.yourdomain.com` |
| `REVERB_PORT` | Reverb WebSocket port | `443` |
