# 🚀 Hyper PWA

[![WordPress Compatibility](https://img.shields.io/badge/WordPress-6.0%20or%20higher-blue.svg?style=flat-square&logo=wordpress)](https://wordpress.org)
[![PHP Version](https://img.shields.io/badge/PHP-7.4%20or%20higher-777bb4.svg?style=flat-square&logo=php)](https://php.net)
[![License](https://img.shields.io/badge/License-GPLv2-green.svg?style=flat-square)](https://www.gnu.org/licenses/gpl-2.0.html)

**Hyper PWA** is a high-performance WordPress plugin designed to easily transform your WordPress website into a fast, installable, and fully compliant Progressive Web App (PWA). Give your users a native app-like experience, push notifications support, and reliable offline accessibility.

---

## ✨ Features

* **Instant PWA Manifest**: Automatically generates a standards-compliant `manifest.json` file customizable via your dashboard.
* **Offline Caching & Fallbacks**: Utilizes service workers to cache crucial site assets, offering an offline-ready experience with customizable offline fallback pages.
* **Custom Install Prompts**: Add fully customizable install buttons for Android, iOS, and desktop web browsers.
* **Gutenberg & Elementor Integrations**: Place customizable install buttons inside editor layouts with the **PWA Install Button** Gutenberg block and Elementor widget.
* **RTL Layout Support**: Native Right-To-Left styling out of the box for writing scripts and layouts.
* **WooCommerce Compatible**: Automatically excludes dynamic checkout, cart routes, and dynamic requests from static caches.
* **Diagnostics & Health Check**: Instant dashboard alerts highlighting potential service worker, manifest, or HTTPS connection issues.

---

## 🛠️ Installation

1. Log in to your WordPress dashboard.
2. Go to **Plugins > Add New** and search for **Hyper PWA**.
3. Click **Install Now** and then **Activate**.
4. Go to the new **Hyper PWA** menu to configure your app name, theme color, icons, and offline fallback pages.

---

## 📦 Directory Structure

```text
hyper-pwa/
├── assets/                  # Frontend and admin CSS, JS, and image resources
├── feedback/                # Admin user feedback helper classes
├── includes/                # Core architecture files
│   ├── admin/               # Settings pages, form fields, and rendering classes
│   ├── class-hypwa.php      # Main core plugin controller class
│   └── class-hypwa-sw.php   # Service Worker generation and hooks controller
├── languages/               # Translation files (.pot, .po, .mo)
├── uninstall.php            # Plugin cleanup routines on uninstall
├── hyper-pwa.php            # Primary WordPress entry loader file
└── readme.txt               # WordPress.org standard metadata file
```

---

## 🤝 Need Support?
* **Documentation**: Visit our [Knowledge Base](https://hyperpwa.com/knowledge-base/) for detailed setup guides.
* **Support**: Get in touch with us via the [Contact Page](https://hyperpwa.com/contactus/).
