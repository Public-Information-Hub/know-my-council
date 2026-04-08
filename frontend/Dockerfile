FROM node:22-bookworm-slim AS frontend

WORKDIR /app

COPY frontend/package.json frontend/package-lock.json ./
RUN npm install --no-fund --no-audit

COPY frontend ./
RUN npm run build
RUN chown -R node:node /app

USER node

EXPOSE 3000

CMD ["sh", "-lc", "PORT=3000 HOST=0.0.0.0 node .output/server/index.mjs"]
