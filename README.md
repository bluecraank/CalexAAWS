# 🏢 Room Display & Booking System

Ein modernes Raum-Display- und Buchungssystem basierend auf **Laravel**, **Filament Admin Panel** und **Microsoft Exchange (EWS)**.

Perfekt für Meetingräume mit Tablet-Displays an der Tür.

---

## ✨ Features

### 📺 Raum-Dashboard (Display)

- Dark Mode optimiert für Tablets
- Live Uhrzeit
- Raumstatus:
  - 🟢 Frei
  - 🟡 Nur noch kurz verfügbar
  - 🔴 Belegt
- Anzeige von:
  - aktuellem Termin
  - nächsten Terminen
  - vergangenen Terminen (kompakt)
- Hervorhebung des aktuellen Meetings
- Auto-Refresh alle 30 Sekunden
- Direktes Buchen über das Display

---

### 📅 Kalenderintegration (Exchange EWS)

- Abfrage von Raumkalendern via EWS
- NTLM Authentication
- Unterstützung mehrerer Räume
- Automatischer Sync per Scheduler
- Speicherung in lokaler Datenbank (`room_events`)

---

### 🧑‍💼 Admin Panel (Filament)

- Räume verwalten:
  - Name
  - Mailbox (SMTP)
  - Username / Passwort (Exchange)
  - Kapazität
  - Ausstattung
- Dashboard-Link pro Raum
- Anzeige letzter Sync-Zeit
- Übersicht aller Räume

---

### ⚡ Buchungssystem

- "Jetzt buchen" direkt am Display
- Auswahl:
  - 30 Minuten
  - 1 Stunde
  - 2 Stunden
- Optional:
  - direkte Erstellung im Exchange
  - sofortige Anzeige im Dashboard

---

## 🧱 Tech Stack

- **Laravel 11+**
- **Filament v3**
- **TailwindCSS**
- **Microsoft Exchange Web Services (EWS)**
- **Carbon**

---

## ⚙️ Installation

```bash
git clone <repo>
cd project

composer install
cp .env.example .env
php artisan key:generate