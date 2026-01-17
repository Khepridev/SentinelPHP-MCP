# SentinelPHP MCP

<div align="center">

![Version](https://img.shields.io/badge/version-1.1.0-blue.svg)
![PHP](https://img.shields.io/badge/PHP-%3E%3D7.4-777BB4.svg)
![License](https://img.shields.io/badge/license-MIT-green.svg)
[![Website](https://img.shields.io/badge/Website-khepridev.xyz-purple)](https://khepridev.xyz/)
[![X](https://img.shields.io/badge/Follow-%40Khepridev-black?logo=x&logoColor=white)](https://x.com/Khepridev)

**Intelligent Security Protocol for PHP Codebases**
<br>
*Zero Trust • Proof of Concept • Local Static Analysis*

[Features](#features) • [Installation](#installation) • [Usage](#usage) • [**Documentation**](https://khepridev.github.io/SentinelPHP-MCP/)

</div>

---

## 🚀 Overview

**SentinelPHP MCP** is a local Model Context Protocol (MCP) server designed to act as a security guardian explicitly for PHP development. Unlike generic linters, SentinelPHP operates on a **Zero Trust** architecture, assuming all inputs are malicious until proven otherwise.

It integrates seamlessly with AI assistants like **Cursor** and **Windsurf**, providing them with the capability to:
1.  **Analyze** PHP code for critical vulnerabilities (SQLi, XSS, DoS).
2.  **Verify** threats by generating Proof-of-Concept (PoC) payloads.
3.  **Learn** from custom vulnerability patterns you provide.

## 📖 Documentation

Full documentation is available in the `docs/` folder or online:
👉 **[View Full Documentation](https://khepridev.github.io/SentinelPHP-MCP/)**

- [Configuration Guide](docs/configuration.html)
- [Usage & Prompts](docs/usage.html)
- [Protocol Specification](PROTOCOL.md)

## ✨ Features

| Feature | Description |
| :--- | :--- |
| **🛡️ Zero Trust Core** | Validates every variable. No more "it looks safe" assumptions. |
| **🧠 Smart Analysis** | Detects complex patterns like `FIND_IN_SET` injection and unhashed passwords. |
| **💥 PoC Generator** | Creates safe, executable examples to prove a vulnerability exists. |
| **📚 Knowledge Base** | Learns from your previous audits to reduce false positives over time. |
| **⚡ Local & Fast** | Runs entirely on your machine. No code leaves your environment. |

## 📦 Installation

### Prerequisites
- PHP 7.4 or higher installed and in your system PATH.
- An MCP-compatible IDE (Cursor, Windsurf) or client.

### Quick Setup

1.  **Clone the Repository**
    ```bash
    git clone https://github.com/Khepridev/SentinelPHP-MCP.git
    cd SentinelPHP-MCP
    ```

2.  **Generate Configuration**
    Open `docs/configuration.html` in your browser to automatically generate the JSON config for your system.

3.  **Add to IDE**
    Paste the generated JSON into your IDE's MCP config file (e.g., `mcp_config.json`).

## 🎮 Usage

Once connected, you can ask your AI assistant naturally:

> "Analyze `User.php` for SQL injection vulnerabilities following the Sentinel protocol."

> "Check `login.php` for XSS and generate a PoC if you find any."

See [docs/usage.html](docs/usage.html) for advanced prompts.

## 🤝 Contribution

Contributions are welcome! Please feel free to submit a Pull Request.

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

---

<div align="center">
    Developed by <strong>Khepridev</strong>
</div>
