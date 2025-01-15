FROM patrykswietlik/project-prestashop:1.2
COPY config/backup /tmp
WORKDIR /tmp
ENTRYPOINT /bin/sh ./load_backup2.sh && apache2-foreground