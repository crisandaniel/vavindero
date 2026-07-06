# vavinde.ro

## Unde rulezi comenzile `wp`

**Local (Docker Compose, pe Mac):** din rădăcina proiectului, prin
serviciul `wpcli` din `docker-compose.yml`:

```bash
docker compose exec -T wpcli wp <comanda> --url=<subdomeniu>.lvh.me
```

**Producție (Hetzner, prin Coolify):** SSH pe server, apoi găsești
containerul `wpcli` al stack-ului `vavindero` și rulezi comanda direct
în el:

```bash
docker ps --filter name=wpcli   # gasesti ID-ul/numele containerului
docker exec <container> wp <comanda> --url=<subdomeniu>.vavinde.ro
```

Comenzile de mai jos folosesc doar `wp <comanda> --url=...` — completează
tu prefixul potrivit (`docker compose exec -T wpcli` local, `docker exec
<container>` în producție) după mediul în care lucrezi.

## Tier-ul unui site (basic / pro)

Fiecare subdomeniu din rețea are un tier — `basic` (implicit) sau `pro` —
stocat în opțiunea `vavinde_site_tier`, gestionată de pluginul
`vavinde-site-tiers`. Tier-ul controlează accesul la Add Page/Post,
Appearance, Plugins, reviews, Analytics/Marketing și header-ul/footer-ul
custom (`vavinde-storefront-template`, doar pe `basic`).

**Verifică tier-ul curent al unui site:**

```bash
wp option get vavinde_site_tier --url=<subdomeniu>.vavinde.ro
```

Dacă nu apare nimic, site-ul e pe `basic` (valoarea implicită).

**Trece un site pe `pro`:**

```bash
wp option update vavinde_site_tier pro --url=<subdomeniu>.vavinde.ro
```

**Trece un site înapoi pe `basic`:**

```bash
wp option update vavinde_site_tier basic --url=<subdomeniu>.vavinde.ro
```

Efectul e imediat, fără nevoie de reactivare de plugin. Super adminul
(tu) este mereu exceptat de la aceste restricții, indiferent de tier.

Site-ul principal (vavinde.ro) e ținut pe `pro`, ca să nu i se aplice
header-ul/footer-ul custom construit pentru magazine.
