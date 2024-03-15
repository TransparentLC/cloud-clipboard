FROM node:21.7.1-alpine3.19 AS builder
ENV NODE_OPTIONS=--openssl-legacy-provider
COPY . /app
WORKDIR /app/client
RUN npm install
RUN npm run build

FROM node:21.7.1-alpine3.19
ENV NODE_OPTIONS=--openssl-legacy-provider
COPY . /app
COPY --from=builder /app/client/dist/ /app/server-node/static/
WORKDIR /app/server-node
RUN npm install
EXPOSE 9501
CMD ["node", "main.js"]

