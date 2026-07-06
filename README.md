# vavinde.ro

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
