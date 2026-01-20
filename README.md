# Webkernel™ Foundation

Webkernel™ is a modular, open-core ecosystem engineered for building multi-brand and multi-platform applications. By merging Laravel’s bootstrap layer with a proprietary source code, it provides a reference-grade foundation where initialization, dependencies, and runtime services are pre-configured for immediate deployment.

### Philosophy & Sovereignty

The project is held by **Yassine El Moumen** to address the need for digital sovereignty. Webkernel™ serves as a strategic lever, allowing businesses and organizations to master their own software infrastructure. It is built on the principle that software should act as a reliable, automated workforce under your direct command, free from the restrictive roadmaps or pricing models of major cloud conglomerates.

**Connect with the Architect:** [LinkedIn](https://www.linkedin.com/in/elmoumenyassine/) | [Website](https://webkernelphp.com) | [+212 6 2099 0692](tel:00212620990692)

<img src="https://raw.githubusercontent.com/numerimondes/.github/refs/heads/main/assets/brands/webkernel/identity/webkernel-small-banner-FOSS.png" width="100%">

---

## Getting Started

### Quick Installation

Developers can initialize a new project immediately via Composer:

```bash
composer create-project webkernel/webkernel my-app
cd my-app
cp .env.example .env
php artisan key:generate

```

### Module Management

Webkernel™ includes a dedicated Module Manager to handle extensions from both public and private repositories. You can use the built-in CLI or the binary shortcut to require new modules:

```bash
# Using the artisan command
php artisan webkernel:require-module https://github.com/webkernelphp/test-public-module

# Using the binary shortcut
./webkernel require-module https://github.com/webkernelphp/test-private-module

```

*Note: The `install` command serves as an alias for `require-module`.*

---

## Core Integrity & Governance

The Webkernel™ Core resides within the `bootstrap/` directory and is governed by **Numerimondes**. This folder contains critical identity markers such as `LICENCE-SERIAL` and `RELEASE`.

Maintaining an unmodified core is essential; any alterations to the `bootstrap/` directory void official guarantees, terminate access to the module marketplace, and risk system regressions. Numerimondes remains the sole authority for core evolution to ensure global stability and security.

---

## Licensing & Collaboration

### Dual-Licensing Model

Webkernel™ is available under two distinct paths. Without a commercial agreement, the software is governed by the **Mozilla Public License 2.0 (MPL-2.0)**, which allows for open-source use and modification provided that modified files remain under the same license. For enterprises requiring official support, updates, and certifications, a **Webkernel™ Commercial License** is issued by Numerimondes.

### Join the Ecosystem

Contributions are welcome and encouraged. To maintain the integrity of the foundation, all contributions are accepted via official pull requests and undergo a validation process by Numerimondes. Whether you are looking to integrate new features or build custom modules, we invite you to reach out and help expand the ecosystem.

**Official Documentation:** [webkernelphp.com](https://webkernelphp.com)
