FROM node:20-bookworm

ARG UID=1000
ARG GID=1000

# Chromium para rodar os testes unitários do Angular (Karma) em modo headless.
RUN apt-get update \
    && apt-get install -y --no-install-recommends chromium \
    && rm -rf /var/lib/apt/lists/*

ENV CHROME_BIN=/usr/bin/chromium \
    NPM_CONFIG_UPDATE_NOTIFIER=false

# Alinha o usuário "node" ao UID/GID do host para os arquivos do bind mount.
RUN groupmod -o -g ${GID} node && usermod -o -u ${UID} -g ${GID} node

COPY entrypoint.sh /usr/local/bin/entrypoint.sh

WORKDIR /app/frontend
USER node

ENTRYPOINT ["entrypoint.sh"]

EXPOSE 4200
CMD ["npm", "start"]
