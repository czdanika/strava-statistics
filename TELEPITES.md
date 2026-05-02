# Telepítési útmutató – Magyar fork

Ez a branch az eredeti [robiningelbrecht/strava-statistics](https://github.com/robiningelbrecht/strava-statistics) app személyre szabott verziója.

## Mi változott az eredetihez képest?

| Módosítás | Leírás |
|---|---|
| Magyar fordítás | Teljes `hu_HU` lokalizáció |
| Fejléc | Buy me a coffee / Discord / GitHub gombok eltávolítva |
| Strava szinkron gomb | Profil menüből manuálisan indítható import, progress kijelzéssel |
| Dátum oszlop | `whitespace-nowrap` – a dátum nem törik két sorba |
| Intro widget | `<kbd>` helyett `<strong>` tag, emoji nélkül |

---

## Előfeltételek

- Docker + Docker Compose
- Strava fiók
- Strava API alkalmazás (Client ID + Client Secret + Refresh Token)

Ha még nincs Strava API alkalmazásod, az eredeti repo leírása alapján hozd létre:
👉 [https://github.com/robiningelbrecht/statistics-for-strava#strava-api](https://github.com/robiningelbrecht/statistics-for-strava)

---

## Telepítés

### 1. Mappák létrehozása

```bash
mkdir -p /opt/strava/{config,build,storage/database,storage/files,translations,patches/Controller,patches/Infrastructure/Localisation,patches/dashboard/widget,patches/activity}
```

### 2. Fájlok letöltése ebből a repóból

```bash
# Fordítás
curl -L -o /opt/strava/translations/messages+intl-icu.hu_HU.yaml \
  https://raw.githubusercontent.com/czdanika/strava-statistics/personal/translations/messages+intl-icu.hu_HU.yaml

# Patch fájlok
curl -L -o /opt/strava/patches/Infrastructure/Localisation/Locale.php \
  https://raw.githubusercontent.com/czdanika/strava-statistics/personal/src/Infrastructure/Localisation/Locale.php

curl -L -o /opt/strava/patches/Controller/TriggerImportRequestHandler.php \
  https://raw.githubusercontent.com/czdanika/strava-statistics/personal/src/Controller/TriggerImportRequestHandler.php

curl -L -o /opt/strava/patches/Controller/ImportStatusRequestHandler.php \
  https://raw.githubusercontent.com/czdanika/strava-statistics/personal/src/Controller/ImportStatusRequestHandler.php

curl -L -o /opt/strava/patches/top-nav-bar.html.twig \
  https://raw.githubusercontent.com/czdanika/strava-statistics/personal/templates/html/navigation/top-nav-bar.html.twig

curl -L -o /opt/strava/patches/dashboard/widget/widget--intro-text.html.twig \
  https://raw.githubusercontent.com/czdanika/strava-statistics/personal/templates/html/dashboard/widget/widget--intro-text.html.twig

curl -L -o /opt/strava/patches/dashboard/widget/widget--most-recent-activities.html.twig \
  https://raw.githubusercontent.com/czdanika/strava-statistics/personal/templates/html/dashboard/widget/widget--most-recent-activities.html.twig

curl -L -o /opt/strava/patches/activity/activity-data-table-row.html.twig \
  https://raw.githubusercontent.com/czdanika/strava-statistics/personal/templates/html/activity/activity-data-table-row.html.twig
```

### 3. Konfiguráció

Hozd létre a `/opt/strava/config/config.yaml` fájlt:

```yaml
general:
  appUrl: 'http://SAJAT_IP:8094/'
  appSubTitle: 'Strava dashboard'
  profilePictureUrl: null      # opcionális: Strava profilkép URL-je
  athlete:
    birthday: 'YYYY-MM-DD'
    maxHeartRateFormula: 'tanaka'
    weightHistory: {}
    ftpHistory: {}

appearance:
  locale: 'hu_HU'
  unitSystem: 'metric'
  timeFormat: 24
  dateFormat:
    short: 'Y. m. d.'
    normal: 'Y. m. d.'

import:
  activityVisibilitiesToImport: []
  numberOfNewActivitiesToProcessPerImport: 250
  activitiesToSkipDuringImport: []
  skipActivitiesRecordedBefore: null
  sportTypesToImport: []

daemon:
  cron:
    - action: 'importDataAndBuildApp'
      expression: '5 * * * *'
      enabled: true
```

### 4. Docker Compose

Hozd létre a `docker-compose.yml` fájlt:

```yaml
name: strava

services:
  app:
    image: robiningelbrecht/strava-statistics:latest
    container_name: statistics-for-strava
    restart: unless-stopped
    ports:
      - "8094:8080"
    environment:
      TZ: Europe/Budapest
      STRAVA_CLIENT_ID: "IDE_A_CLIENT_ID"
      STRAVA_CLIENT_SECRET: "IDE_A_CLIENT_SECRET"
      STRAVA_REFRESH_TOKEN: "IDE_A_REFRESH_TOKEN"
      LOCALE: "hu_HU"
      UNIT_SYSTEM: "metric"
    volumes:
      - /opt/strava/config:/var/www/config/app
      - /opt/strava/build:/var/www/build
      - /opt/strava/storage/database:/var/www/storage/database
      - /opt/strava/storage/files:/var/www/storage/files
      - /opt/strava/translations:/var/www/translations
      - /opt/strava/patches/Infrastructure/Localisation/Locale.php:/var/www/src/Infrastructure/Localisation/Locale.php
      - /opt/strava/patches/top-nav-bar.html.twig:/var/www/templates/html/navigation/top-nav-bar.html.twig
      - /opt/strava/patches/dashboard/widget/widget--intro-text.html.twig:/var/www/templates/html/dashboard/widget/widget--intro-text.html.twig
      - /opt/strava/patches/dashboard/widget/widget--most-recent-activities.html.twig:/var/www/templates/html/dashboard/widget/widget--most-recent-activities.html.twig
      - /opt/strava/patches/activity/activity-data-table-row.html.twig:/var/www/templates/html/activity/activity-data-table-row.html.twig
      - /opt/strava/patches/Controller/TriggerImportRequestHandler.php:/var/www/src/Controller/TriggerImportRequestHandler.php
      - /opt/strava/patches/Controller/ImportStatusRequestHandler.php:/var/www/src/Controller/ImportStatusRequestHandler.php
      - /etc/localtime:/etc/localtime:ro

  daemon:
    image: robiningelbrecht/strava-statistics:latest
    container_name: statistics-for-strava-daemon
    restart: unless-stopped
    command: ["bin/console", "app:daemon:run"]
    environment:
      TZ: Europe/Budapest
      STRAVA_CLIENT_ID: "IDE_A_CLIENT_ID"
      STRAVA_CLIENT_SECRET: "IDE_A_CLIENT_SECRET"
      STRAVA_REFRESH_TOKEN: "IDE_A_REFRESH_TOKEN"
      LOCALE: "hu_HU"
      UNIT_SYSTEM: "metric"
    volumes:
      - /opt/strava/config:/var/www/config/app
      - /opt/strava/build:/var/www/build
      - /opt/strava/storage/database:/var/www/storage/database
      - /opt/strava/storage/files:/var/www/storage/files
      - /opt/strava/translations:/var/www/translations
      - /opt/strava/patches/Infrastructure/Localisation/Locale.php:/var/www/src/Infrastructure/Localisation/Locale.php
      - /opt/strava/patches/top-nav-bar.html.twig:/var/www/templates/html/navigation/top-nav-bar.html.twig
      - /opt/strava/patches/dashboard/widget/widget--intro-text.html.twig:/var/www/templates/html/dashboard/widget/widget--intro-text.html.twig
      - /opt/strava/patches/dashboard/widget/widget--most-recent-activities.html.twig:/var/www/templates/html/dashboard/widget/widget--most-recent-activities.html.twig
      - /opt/strava/patches/activity/activity-data-table-row.html.twig:/var/www/templates/html/activity/activity-data-table-row.html.twig

volumes: {}
```

### 5. Indítás

```bash
docker compose up -d
```

Az első indítás után az app elérhető: `http://SAJAT_IP:8094`

Az első adatimport automatikusan elindul. Manuálisan is indítható a profil menüből a **Strava szinkronizálás** gombbal.

---

## Frissítés

Ha az upstream kiad egy új verziót:

```bash
# Új image letöltése
docker compose pull

# Újraindítás
docker compose up -d
```

A patch fájlok (volume mountok) nem változnak frissítéskor – csak az eredeti Docker image-en belüli fájlokat írják felül.

---

## Köszönet

Az eredeti alkalmazást [Robin Ingelbrecht](https://github.com/robiningelbrecht) készítette.
