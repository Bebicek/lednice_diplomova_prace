# Lednička — diplomová práce

Webová aplikace pro správu firemní/kancelářské lednice, sdílených nákupů a obědů. Aplikace vznikla jako praktická část diplomové práce a pokrývá celý životní cyklus zboží — od příjmu na sklad, přes přesun do lednice, prodej zaměstnancům, evidenci dluhů (včetně QR plateb pomocí SPAYD), hlídání data spotřeby, až po reporting prodejů a administraci uživatelů.

## Klíčové funkce

- **Autentizace a role** — session-based přihlášení, role *zaměstnanec* a *administrátor* oddělené middleware (Spatie Permissions).
- **Reaktivní katalog a košík** — průchod nabídkou s filtrováním a vyhledáváním, session-based košík, transakční dokončení objednávky s pesimistickým zamykáním zásob.
- **Dvouúrovňový sklad** — produkty jsou evidovány zvlášť ve skladu a v lednici, veškeré pohyby (příjem, přesun, ztráta) jsou zaznamenány do auditní tabulky.
- **Hlídání data spotřeby** — přehled produktů v lednici rozdělený do stavů (po expiraci, kritická, varování, bez data).
- **Správa dluhů** — evidence systémových i osobních dluhů, agregace podle dvojice dlužník–věřitel, export do CSV, generování QR kódů pro platbu mobilním bankovnictvím (formát SPAYD).
- **Správa obědů** — tříkrokový průvodce pro hromadnou objednávku, dva režimy dělení nákladů (rovnoměrné / podle ceny položky) a dvoufázová akceptace dluhů účastníky.
- **Reporting prodejů** — agregované přehledy prodejů za zvolené období s exportem do CSV a XML Spreadsheet (otevíratelné v MS Excel bez závislosti na externí knihovně).
- **Systémová nastavení** — konfigurace bankovních údajů firmy, hranice nízkého skladu, počtu dní pro upozornění na expiraci atd. přes administrátorské UI.
- **Lokalizace a tmavý režim** — kompletní česká lokalizace a podpora tmavého motivu.

## Technologie

- **PHP 8.2+**, **Laravel 12**
- **Livewire 4** (reaktivní komponenty)
- **Tailwind CSS v4**, **Alpine.js**, **Vite**
- **PostgreSQL** (vývoj i produkce)
- **Spatie Laravel Permission** — role a oprávnění
- **endroid/qr-code** — generování SPAYD QR kódů
- **Docker** + **Docker Compose** — produkční nasazení

UI je postavené na šabloně [TailAdmin Laravel](https://tailadmin.com/laravel).

## Požadavky

- PHP 8.2 nebo novější
- Composer
- Node.js 18+ a npm
- PostgreSQL (případně jiná databáze podporovaná Laravelem)

## Instalace

```bash
git clone <URL_REPOZITARE>
cd lednice_diplomova_prace

composer install
npm install

cp .env.example .env
php artisan key:generate
```

V souboru `.env` nastavte připojení k databázi:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=lednice
DB_USERNAME=postgres
DB_PASSWORD=
```

Spusťte migrace a (volitelně) seedery s ukázkovými daty:

```bash
php artisan migrate --seed
php artisan storage:link
```

## Vývojové prostředí

Nejjednodušší způsob spuštění (Laravel server, Vite, queue worker a sledování logů v jednom příkazu):

```bash
php artisan serve     # backend
npm run dev           # frontend assets
php artisan queue:work
```

Aplikace bude dostupná na [http://localhost:8000](http://localhost:8000).

## Produkční nasazení (Docker)

Repozitář obsahuje `Dockerfile` a `docker-compose.yml` připravené pro nasazení.

```bash
cp env.production .env
docker compose up -d --build
```

Detailní popis nasazení je součástí diplomové práce (kapitola *Nasazení*).

## Struktura projektu

```
app/
├── Livewire/             # Livewire komponenty (21)
│   ├── Admin/            # Administrátorská část (/admin)
│   └── ...               # Komponenty pro zaměstnance
├── Models/               # Eloquent modely (12)
└── Http/                 # Controllery, middleware, requesty
database/
├── migrations/           # Databázové migrace (21)
├── seeders/
└── factories/
lang/cs*                  # Česká lokalizace
resources/
├── views/livewire/       # Blade šablony Livewire komponent
├── css/                  # Tailwind CSS v4
└── js/
docker/                   # Konfigurace Docker image
```

## Licence

Aplikace je publikována pod licencí MIT — viz [LICENSE](LICENSE).
