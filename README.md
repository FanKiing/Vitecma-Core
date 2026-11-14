# 🚗 Système de Gestion - Centre de Visite Technique
> **Plateforme ultra-rapide en temps réel pour la gestion et le suivi des inspections techniques.**

![Laravel](https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![Laravel Reverb](https://img.shields.io/badge/Laravel_Reverb-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![WebSockets](https://img.shields.io/badge/WebSockets-010101?style=for-the-badge&logo=socketdotio&logoColor=white)
![TailwindCSS](https://img.shields.io/badge/Tailwind_CSS-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)
![SQLite](https://img.shields.io/badge/SQLite-07405E?style=for-the-badge&logo=sqlite&logoColor=white)

---

## 🌍 Langues / Languages / اللغات
* [Français](#français)
* [English](#english)
* [العربية](#العربية)

---

<a name="français"></a>
## 🇫🇷 Français

### 📝 Description
Ce projet est une solution numérique de pointe optimisée pour la gestion d'un centre de visite technique. Il repose sur une architecture **Laravel Blade**, boostée par **Laravel Reverb (WebSockets)** et **Fetch API** pour offrir une synchronisation bidirectionnelle en temps réel absolu entre les administrateurs et les écrans d'affichage, assurant une fluidité maximale et zéro rechargement de page.

### ✨ Fonctionnalités
* **Mises à Jour en Temps Réel Actif :** Synchronisation instantanée des statuts de véhicules et des lignes de tableau via WebSockets (Laravel Reverb & Echo).
* **Tableau de Bord Haute Performance :** Interactions asynchrones fluides gérées par Fetch API.
* **Gestion des Rôles et Sécurité :** Distinction stricte et sécurisée entre Administrateur et Utilisateur (Contrôle d'accès côté serveur).
* **Chronométrage Intelligent Synchronisé :** Compte à rebours dynamique calculé automatiquement par catégorie : Véhicule Léger - VL (20 min) / Poids Lourd - PL (30 min).
* **Interface Moderne & Mode Sombre :** Design élégant, responsive et adaptatif avec persistance du choix utilisateur.

---

<a name="english"></a>
## 🇬🇧 English

### 📝 Description
A cutting-edge, high-performance digital solution for technical vehicle inspection centers. Built using **Laravel Blade**, **Laravel Reverb (WebSockets)**, and **Vanilla JavaScript (Fetch API)**. The platform provides continuous, absolute real-time state synchronization between the admin control panel and large display screens without any full-page overhead.

### ✨ Key Features
* **True Real-Time Synchronization:** Instant updates of inspection statuses, button layouts, and rows powered by WebSockets.
* **High-Performance Asynchronous UI:** Smooth dashboard updates without reloading via Fetch API.
* **Role-Based Access Control (RBAC):** Rigid, secure segregation between Admin and Operator roles.
* **Smart Synchronized Timer:** Automated live countdown tracking based on vehicle class: VL (20 min) / PL (30 min).
* **Modern Interface & Dark Mode:** Clean, responsive UI with stateful native dark mode support.

---

<a name="العربية"></a>
## 🇲🇦 العربية

### 📝 وصف المشروع
نظام رقمي متطور عالي الأداء مصمم لإدارة وتتبع تدفق العمل داخل مراكز الفحص التقني للمركبات. يعتمد المشروع على بنية **Laravel Blade** المدعومة بتقنية البث اللاسلكي الفوري **Laravel Reverb (WebSockets)** والـ **Fetch API**، مما يتيح مزامنة البيانات والعد التنازلي حياً وثنائياً بين لوحة تحكم المدير وشاشات العرض الكبيرة في نفس الأجزاء من الثانية دون أي حاجة لإعادة تحميل الصفحة.

### ✨ الخصائص الرئيسية
* **مزامنة فورية حية (Real-Time):** تحديث لحظي لحالات المركبات، الألوان، الأزرار، والصفوف بفضل تقنيات الـ WebSockets.
* **لوحة تحكم فائقة السرعة:** تفاعلات ذكية وغير متزامنة ومستقرة تماماً باستخدام Fetch API.
* **نظام صلاحيات صارم:** فصل برمي وآمن بين صلاحيات المدير والموظفين على مستوى السيرفر.
* **عداد زمني ذكي ومتزامن:** تتبع تنازلي آلي ومباشر لوقت الفحص حسب صنف المركبة: سيارة خفيفة VL (20 دقيقة) / وزن ثقيل PL (30 دقيقة).
* **واجهة عصرية مرنة:** تصميم مستوحى من أحدث المعايير يدعم الوضع الليلي التلقائي بالكامل.

---

### 🛠️ Stack Technique
* **Backend:** Laravel 13 / PHP 8.4+
* **Real-time Server:** Laravel Reverb & Laravel Echo (WebSockets)
* **Frontend:** Blade Templates, Tailwind CSS & Vanilla JavaScript (Fetch API)
* **Database:** SQLite

---

### 🚀 Démarrage Rapide / Quick Start / التشغيل السريع

1. **Installer les dépendances PHP et Node :**
   ```
   composer install
   npm ci
   ```
2. **Configurer l'environnement (```.env```) :** 
   ```
   cp .env.example .env
   php artisan key:generate
   php artisan migrate
   npm run build
   ```
3. **Lancer les serveurs simultanément :**
   ```
   # Server PHP
   php artisan serve
   # Server WebSockets (Reverb)
   php artisan reverb:start
   # Asset Compilation (Vite)
   npm run dev
   ```


### Authentication and centre branding

Authentication uses Laravel Fortify (Breeze is removed). Sign in with a username and password. Existing username-only accounts remain valid; the additive email migration preserves them. New registrations receive the standard user role, never administrator privileges. An email can be added in `/profile` for password recovery. Email verification is optional and does not block the existing centre accounts.

Login attempts are limited to five per minute per username/IP pair and twenty per minute per IP. Registration, recovery and password confirmation are rate limited. Fortify regenerates the session after login; CSRF, HttpOnly and SameSite cookies remain enabled. HTTPS-only session cookies default on in production. Configure a real mail transport before using email recovery in production; the local mailer logs mail.

Administrators see **Nom et logo** in the navbar. The button opens a modal on the dashboard, profile, archives or trash page. It supports changing the centre name, previewing/uploading a PNG/JPEG/WebP logo (2 MB and 2000×2000 maximum), and restoring the default logo. Changes appear on public authentication pages, dashboard headers and page icons. The update endpoint checks the administrator role on the server. Logos live in private local storage and are served through `/branding/logo`; this feature does not require a storage symlink. `/settings/branding` opens the dashboard with the modal displayed.

Initial accounts are opt-in: set `SEED_ADMIN_PASSWORD` and/or `SEED_SCREEN_PASSWORD` to unique passwords of at least 12 characters, then run `php artisan db:seed`. Existing accounts and passwords are preserved, and no inspection data is deleted. Demo data is disabled by default; it is restricted to the local environment. There are no default account passwords.

Validation: `php artisan test`, `composer validate --no-check-publish`, `composer check-platform-reqs`, and `npm run build`. Tests use an isolated SQLite database.

### Livewire + Reverb

Active inspections, archives and trash use the `App\Livewire\InspectionTable` component. Inspection commands go through explicit Livewire actions, retaining the application's existing server validation and admin checks. Private Reverb events refresh the component; Livewire is the sole owner of table/pagination DOM. Existing REST endpoints remain available for integrations. The current dialogs are retained as a bridge during the gradual migration.

Alpine is imported from Livewire's ESM bundle exactly once; layouts supply `@livewireScriptConfig`. Do not add a second Alpine script or assign table `innerHTML`. Timers run locally in ignored text spans, using server time and persisted `started_at`; they do not make per-second requests. Visible components reconcile every 30 seconds and after reconnect/resume.
