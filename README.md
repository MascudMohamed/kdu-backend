# KDU Backend — PHP 8 + MySQL (Portfolio API)

REST-style JSON APIs for the KDU Global frontend. **Plain PHP, PDO, no framework** — easy to explain in interviews.

## 1) Recommended folder structure

```
Kdu_backend/
├── public/                 ← Point Apache DocumentRoot HERE (web root)
│   ├── index.php           Health check (PHP + DB)
│   ├── .htaccess           Security headers
│   └── api/                One endpoint file per resource (simple routing)
│       ├── programs.php
│       ├── news.php
│       ├── events.php
│       ├── contact.php
│       ├── newsletter.php
│       └── upload.php
├── config/
│   ├── env.example.php     Commit this
│   ├── env.local.php       Real secrets (gitignored)
│   └── database.php        Builds DSN + PDO options
├── includes/
│   ├── bootstrap.php       CORS, JSON headers, exception handler
│   ├── Database.php        PDO singleton
│   ├── Response.php        Consistent JSON + HTTP codes
│   ├── Validator.php       Input validation helpers
│   ├── helpers.php         Pagination, JSON body, LIKE escaping
│   └── *Repository.php     SQL lives here (thin data layer)
├── sql/
│   └── schema.sql
├── storage/
│   ├── uploads/            User uploads (not publicly listed)
│   └── logs/
└── README.md
```

**Why `public/` as web root:** `config/` and `storage/` never touch the internet if Apache is configured correctly — classic production pattern.

---

## 2) Database schema

See `sql/schema.sql` — tables: `programs`, `news`, `events`, `contact_messages`, `newsletter_subscribers`.

- **JSON `tags`** on programs maps cleanly to your frontend cards.
- **FULLTEXT** indexes are optional helpers; APIs use **LIKE + prepared statements** for portability.

---

## 3) Step-by-step setup (XAMPP)

1. Place project at `C:\xampp\htdocs\Kdu_backend` (already your path).
2. **Create DB** — open phpMyAdmin → Import `sql/schema.sql`.
3. **Configure** — edit `config/env.local.php` (created for you; update password if needed).
4. **Apache DocumentRoot** — set to `C:/xampp/htdocs/Kdu_backend/public` (recommended).

   **httpd-vhosts.conf** example:

   ```apache
   <VirtualHost *:80>
     ServerName kdu-api.local
     DocumentRoot "C:/xampp/htdocs/Kdu_backend/public"
     <Directory "C:/xampp/htdocs/Kdu_backend/public">
       AllowOverride All
       Require all granted
     </Directory>
   </VirtualHost>
   ```

   Add `127.0.0.1 kdu-api.local` to `C:\Windows\System32\drivers\etc\hosts`.

5. **Smoke test**
   - `http://kdu-api.local/` → JSON health
   - `http://kdu-api.local/api/programs.php` → JSON list

---

## 4) Security practices (what to say in interviews)

| Practice | Why it matters |
|----------|----------------|
| **PDO prepared statements** | Stops **SQL injection** — user input is never concatenated into SQL. |
| **Bind integers explicitly** | `LIMIT`/`OFFSET` as `PDO::PARAM_INT` avoids edge-case type bugs. |
| **Server-side validation** | Frontend validation is UX only; attackers can call APIs directly. |
| **Pagination caps** | `MAX_PER_PAGE` prevents someone requesting 1,000,000 rows and melting DB/RAM. |
| **CORS allowlist** | Reduces abuse from random sites using a victim’s browser to hit your API. |
| **No secrets in Git** | `env.local.php` gitignored — recruiters check for this. |
| **`json_encode` for API output** | Avoids accidentally emitting broken JSON; pair with frontend escaping for XSS. |
| **Upload MIME check with `finfo`** | Never trust `$_FILES['type']` — it’s client-controlled. |
| **Random filenames** | Prevents **path traversal** / overwriting / guessing URLs. |
| **Security headers** | `X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy` — baseline hardening. |

---

## 5) API examples

### Programs (search + filter + pagination)

`GET /api/programs.php?q=ai&level=Bachelor&page=1&per_page=10`

```json
{
  "ok": true,
  "data": [ { "id": 1, "slug": "...", "title": "...", "desc": "...", "level": "...", "tags": [] } ],
  "meta": { "page": 1, "per_page": 10, "total": 2, "total_pages": 1 }
}
```

### News

`GET /api/news.php?q=welcome&page=1&per_page=10`

### Events

`GET /api/events.php?limit=20`

### Contact form

`POST /api/contact.php`  
`Content-Type: application/json`

### Newsletter

`POST /api/newsletter.php`  
`Content-Type: application/json`

```json
{ "email": "you@example.com", "source": "homepage" }
```

```json
{ "name": "Asha", "email": "a@example.com", "subject": "Visa question", "message": "Hello..." }
```

**422** validation:

```json
{ "ok": false, "errors": { "email": "Please enter a valid email." } }
```

### Upload (optional)

`POST /api/upload.php` as `multipart/form-data`, field name `file`.

---

## 6) Frontend `fetch()` integration

```javascript
const API_BASE = "http://kdu-api.local"; // or http://localhost/Kdu_backend/public

async function loadPrograms() {
  const params = new URLSearchParams({ page: "1", per_page: "10", q: "business" });
  const res = await fetch(`${API_BASE}/api/programs.php?${params}`, {
    headers: { Accept: "application/json" },
  });
  if (!res.ok) throw new Error(`HTTP ${res.status}`);
  const payload = await res.json();
  if (!payload.ok) throw new Error(payload.error || "API error");
  return payload.data;
}

async function submitContact(body) {
  const res = await fetch(`${API_BASE}/api/contact.php`, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      Accept: "application/json",
    },
    body: JSON.stringify(body),
  });
  const payload = await res.json();
  if (!res.ok || !payload.ok) {
    throw new Error(payload.error || "Request failed");
  }
  return payload;
}
```

**CORS:** add your frontend origin (e.g. Live Server) to `CORS_ORIGINS` in `config/env.local.php`.

---

## 7) Deployment checklist (production)

- [ ] `APP_DEBUG = false` in `env.local.php` on server
- [ ] **HTTPS** only (Let’s Encrypt)
- [ ] Strong DB user (not `root`), least privilege
- [ ] `public/` is the only web root
- [ ] Disable PHP error display; log to file/syslog
- [ ] Rate limit `/api/contact.php` (reverse proxy / Cloudflare / fail2ban)
- [ ] Backups + migrations process for schema changes
- [ ] **Never** commit `env.local.php`

---

## 8) Junior / full-stack signals recruiters like

- Clear separation: **HTTP layer** (`public/api/*.php`) vs **data layer** (`*Repository.php`)
- Predictable JSON shape + **correct status codes** (`422` validation, `405` method, `201` created)
- Comments that explain **why**, not what
- Small functions, no copy-paste SQL in every file
- A README that shows you understand **security** and **deployment**

---

## License

University / portfolio use.
