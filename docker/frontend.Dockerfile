FROM node:20-alpine

RUN apk add --no-cache mc

WORKDIR /app

COPY ./www_front/package*.json ./

RUN npm install

COPY ./www_front .

RUN npm run build
