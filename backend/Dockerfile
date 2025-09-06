# syntax=docker/dockerfile:1.7
FROM node:22-alpine AS base
WORKDIR /app
ENV PNPM_HOME=/pnpm
ENV PATH=$PNPM_HOME:$PATH
RUN corepack enable
COPY package.json tsconfig*.json ./
COPY src ./src
RUN --mount=type=cache,id=pnpm-store,target=/pnpm/store \
    pnpm install

FROM base AS dev
CMD ["pnpm","dev"]

FROM base AS build
RUN pnpm run build

FROM node:22-alpine AS prod
WORKDIR /app
ENV NODE_ENV=production
COPY --from=build /app/package.json ./
COPY --from=build /app/dist ./dist
EXPOSE 4000
CMD ["node","dist/index.js"]
