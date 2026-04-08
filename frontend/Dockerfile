FROM node:22-bookworm-slim AS frontend

WORKDIR /app

COPY frontend/package.json frontend/package-lock.json ./
RUN npm ci

COPY frontend ./
RUN npm run build

EXPOSE 3000

CMD ["sh", "-lc", "npm run preview -- --host 0.0.0.0 --port 3000"]
