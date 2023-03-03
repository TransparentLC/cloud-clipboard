FROM node:16.4
COPY . /app
WORKDIR /app/client
RUN npm install --registry=https://registry.npmmirror.com
RUN npm run build
WORKDIR /app/server-node
RUN npm install --registry=https://registry.npmmirror.com
EXPOSE 9501
CMD node main.js
