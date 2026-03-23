# WordPress LinkListe

**Version 1.1**

Ein WordPress-Plugin zur Verwaltung einer kuratierten Linkliste – mit Frontend-Einreichung, Genehmigungsprozess, Kategorien, Bewertungen, Suche und einem vollständigen Admin-Backend.

---

## Funktionsumfang

### Frontend
- **Linkliste anzeigen** – Kartenansicht mit Vorschaubildern, Favicons und Kategorie-Badges
- **Echtzeit-Suche** – Links sofort filtern ohne Seitenreload (debounced, 300 ms)
- **Kategoriefilter** – Buttons mit individuellen Farben und Dashicon-Symbolen
- **Sortierung** – nach Datum, Name, Aufrufen oder Bewertung
- **„Mehr laden"** – AJAX-basierte Pagination (kein Seitenreload)
- **Link einreichen** – Formular in einem modalen Dialogfenster
- **Bewertungen** – 1–5 Sterne pro Link (einmalig pro Browser-Session)
- **Klick-Tracking** – Aufrufzähler für jeden Link
- **Link kopieren** – URL per Klick in die Zwischenablage
- **Bildvorschau** – Live-Vorschau der eingegebenen Bild-URL im Formular
- **Neue Kategorie vorschlagen** – direkt im Einreichungsformular
- **Top-Links-Widget** – Shortcode für die meistbesuchten Links
- **Toast-Benachrichtigungen** – dezente Status-Meldungen (Erfolg / Fehler)
- **Responsives Design** – optimiert für Desktop, Tablet und Smartphone

### Backend (WordPress-Admin)
- **Alle Links** – Übersichtstabelle mit Status-Tabs (Genehmigt / Ausstehend / Abgelehnt), Suche und Pagination
- **Ausstehende Links** – Badge-Anzeige im Menü, Schnellgenehmigung per AJAX
- **Kategorien verwalten** – Erstellen, Bearbeiten, Löschen; eigene Farbe und Dashicon-Klasse
- **Einstellungen** – Links pro Seite, Bilder erlauben, Admin-E-Mail-Benachrichtigung, Seitentitel
- **Shortcode-Übersicht** – alle verfügbaren Shortcodes in der Einstellungsseite

### Datenbank
| Tabelle | Inhalt |
|---|---|
| `{prefix}wll_links` | Alle Links (URL, Name, Kategorie, Bemerkung, Bild, Status, Aufrufe, Bewertung) |
| `{prefix}wll_categories` | Kategorien (Name, Slug, Beschreibung, Farbe, Dashicon, Sortierung) |

---

## Installation

1. Den Ordner `wordpress-linkliste` in `/wp-content/plugins/` kopieren.
2. Im WordPress-Admin unter **Plugins** → **Installierte Plugins** das Plugin **WordPress LinkListe** aktivieren.
3. Die Datenbanktabellen werden automatisch angelegt.
4. Shortcodes auf beliebigen Seiten oder Beiträgen einfügen (siehe unten).

---

## Shortcodes

| Shortcode | Beschreibung |
|---|---|
| `[wll_linkliste]` | Vollständige Linkliste mit Suche und Filter |
| `[wll_linkliste per_page="6" category_id="2"]` | Mit Parametern: Anzahl und Startkategorie |
| `[wll_submit_form]` | Einreichungsformular als eigenständige Seite |
| `[wll_top_links count="5"]` | Die meistaufgerufenen Links (für Sidebar-Widgets) |

---

## Dateistruktur

```
wordpress-linkliste/
├── wordpress-linkliste.php          ← Hauptdatei / Plugin-Header
├── includes/
│   ├── class-wll-database.php       ← Datenbank-Installation & Abfragen
│   ├── class-wll-post-type.php      ← Post-Type-Erweiterungspunkt
│   ├── class-wll-shortcodes.php     ← Shortcode-Registrierung
│   └── class-wll-ajax.php           ← Alle AJAX-Handler
├── admin/
│   ├── class-wll-admin.php          ← Admin-Menüs, Assets, Formularverarbeitung
│   └── views/
│       ├── page-all-links.php       ← Ansicht: Alle Links
│       ├── page-pending.php         ← Ansicht: Ausstehende Links
│       ├── page-categories.php      ← Ansicht: Kategorien
│       └── page-settings.php        ← Ansicht: Einstellungen
├── frontend/
│   ├── class-wll-frontend.php       ← Asset-Einbindung, Template-Loader
│   └── templates/
│       ├── linkliste.php            ← Haupttemplate mit Suche & Filter
│       ├── link-cards.php           ← Karten-Raster (wird auch per AJAX geladen)
│       ├── submit-form.php          ← Modal-Einreichungsformular
│       └── top-links.php            ← Top-Links-Widget
└── assets/
    ├── css/
    │   ├── frontend.css             ← Frontend-Styles
    │   └── admin.css                ← Backend-Styles
    └── js/
        ├── frontend.js              ← Frontend-Interaktivität (jQuery)
        └── admin.js                 ← Admin-AJAX-Aktionen (jQuery)
```

---

## Theme-Anpassungen

Templates können im aktiven Theme überschrieben werden:

```
{theme}/wll-templates/linkliste.php
{theme}/wll-templates/link-cards.php
{theme}/wll-templates/submit-form.php
{theme}/wll-templates/top-links.php
```

---

## Hinweise für KI / LLM

Dieser Abschnitt richtet sich an KI-Assistenten (Claude, GPT, Gemini usw.), die an diesem Projekt mitwirken.

### Versionierung
Die Version wird bei jedem Commit / Merge um **0.1** erhöht.

**Berechnung:**
Aktuelle Version in `README.md` ablesen → 0.1 addieren → auf eine Dezimalstelle runden.

Beispiele: `1.0 → 1.1`, `1.9 → 2.0`, `2.0 → 2.1`

**Betroffene Dateien bei Versionsbump:**
- `README.md` (Zeile: `**Version X.Y**`)
- `wordpress-linkliste/wordpress-linkliste.php` (Plugin-Header: `Version:` und `define( 'WLL_VERSION', ... )`)

### Codekonventionen
- PHP: WordPress Coding Standards
- JavaScript: ES5-kompatibel, jQuery-basiert, IIFE-Pattern
- CSS: BEM-ähnliche Klassen mit Präfix `wll-`
- Alle Benutzertexte im Frontend und Backend sind mit `__()` / `_e()` internationalisierbar

---

## Lizenz

GPL-2.0+
