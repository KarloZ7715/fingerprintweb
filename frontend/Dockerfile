# syntax=docker/dockerfile:1.7
FROM node:22-alpine AS base
WORKDIR /app
ENV PNPM_HOME=/pnpm
ENV PATH=$PNPM_HOME:$PATH
RUN corepack enable
COPY package.json tsconfig.json vite.config.* tailwind.config.js postcss.config.js index.html ./
COPY src ./src
RUN --mount=type=cache,id=pnpm-store,target=/pnpm/store \
    pnpm install

FROM base AS dev
CMD ["pnpm","dev","--","--host","0.0.0.0"]

FROM base AS build
RUN pnpm run build

FROM nginx:1.27-alpine AS prod
COPY --from=build /app/dist /usr/share/nginx/html
EXPOSE 80
CMD ["nginx","-g","daemon off;"]
