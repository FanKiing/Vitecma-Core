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
* **Backend:** Laravel 11 / PHP 8.2+
* **Real-time Server:** Laravel Reverb & Laravel Echo (WebSockets)
* **Frontend:** Blade Templates, Tailwind CSS & Vanilla JavaScript (Fetch API)
* **Database:** SQLite

---

### 🚀 Démarrage Rapide / Quick Start / التشغيل السريع

1. **Installer les dépendances PHP et Node :**
   ```
   composer install
   npm install
   ```
2. **Configurer l'environnement (```.env```) :** 
   ```
   cp .env.example .env
   php artisan key:generate
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
