# MCP Server - Complete Package

## ✅ What's Included

### Core Files
- ✅ `server.php` - Main MCP server with learning system
- ✅ `composer.json` - Composer configuration
- ✅ `vendor/autoload.php` - Simple autoloader (no dependencies needed)
- ✅ `src/LearningSystem.php` - Learning and logging system

### Documentation
- ✅ `README.md` - Feature documentation
- ✅ `PROTOCOL.md` - Security audit protocol
- ✅ `SETUP.md` - Windows setup guide
- ✅ `mcp.html` - Interactive configuration generator

### Knowledge Base
- ✅ `prompts/sql-injection.md` - Real SQL injection patterns
- ✅ `prompts/xss.md` - Real XSS patterns
- ✅ `prompts/dos.md` - Real DoS patterns

### Auto-Created Directories
- 📁 `logs/` - JSON logs of all analyses
- 📁 `analysis/` - Beautiful HTML reports
- 📁 `knowledge/` - Learned patterns database

## 🚀 Quick Start (Windows)

### Step 1: Open Configuration Generator

Double-click `mcp.html` or open it in your browser.

### Step 2: Fill in Paths

**PHP Path** (find yours):
```
C:\laragon\bin\php\php-8.2.0-Win32-vs16-x64\php.exe
C:\xampp\php\php.exe
C:\wamp64\bin\php\php8.2.0\php.exe
```

**Server Path**:
```
c:\laragon\www\php-pdo-class\mcp\server.php
```

### Step 3: Generate & Copy

1. Click "Generate Configuration"
2. Click "📋 Copy"
3. Paste into your IDE's MCP settings

### Step 4: Restart IDE

Restart your IDE (VS Code, Cursor, Windsurf, etc.)

## 🎨 Features

### 1. Security Analysis
```
Analyze this PHP code for vulnerabilities:
$db->where('id', $_GET['id'], $_GET['op']);
```

**Output**:
- 🔴 Critical vulnerabilities
- 🟠 High severity issues
- 🟡 Medium risks
- 💣 Proof-of-concept exploits
- 📊 Analysis ID
- 📁 HTML report link

### 2. Knowledge Base
```
Show me the learned patterns
```

**Output**:
- Total analyses performed
- Unique vulnerability patterns
- Occurrence counts
- First seen dates
- Verified exploits

### 3. Analysis History
```
Get recent analyses
```

**Output**:
- Last 10 analyses
- Timestamps
- Vulnerability counts
- Severity breakdowns

### 4. Protocol Access
```
Show me the security audit protocol
```

**Output**:
- Complete PROTOCOL.md
- Audit methodology
- Testing checklist

## 📊 HTML Reports

Every analysis generates a beautiful HTML report in `analysis/`:

**Features**:
- 🎨 Modern gradient design (TweakCN colors)
- 📊 Statistics cards
- 🔴 Color-coded severity badges
- 💻 Syntax-highlighted code blocks
- 📱 Responsive layout
- ✨ Hover animations

**Example**: `analysis/2026-01-17_23-55-00_report.html`

## 🧠 Learning System

The server learns from every analysis:

1. **Pattern Recognition**
   - Tracks which vulnerabilities appear most
   - Builds pattern database
   - Improves detection over time

2. **False Positive Tracking**
   - Remembers what's NOT a vulnerability
   - Reduces noise
   - Focuses on real issues

3. **Verified Exploits**
   - Stores working proof-of-concepts
   - Builds exploit library
   - Enables better testing

## 🔧 Supported IDEs

- ✅ **VS Code** (with MCP extension)
- ✅ **Cursor** (built-in MCP support)
- ✅ **Windsurf** (built-in MCP support)
- ✅ **Any IDE** with MCP protocol support

## 📁 File Structure

```
mcp/
├── server.php              ← Main server
├── mcp.html               ← Config generator (OPEN THIS!)
├── composer.json          ← Composer config
├── SETUP.md              ← Setup guide
├── README.md             ← Features
├── PROTOCOL.md           ← Audit methodology
├── vendor/
│   └── autoload.php      ← Simple autoloader
├── src/
│   └── LearningSystem.php ← Learning system
├── prompts/
│   ├── sql-injection.md   ← Real patterns
│   ├── xss.md
│   └── dos.md
├── logs/                  ← Auto-created
├── analysis/              ← Auto-created
└── knowledge/             ← Auto-created
```

## 🎯 Example MCP Configuration

```json
{
  "mcpServers": {
    "php-security-audit": {
      "command": "C:\\laragon\\bin\\php\\php-8.2.0-Win32-vs16-x64\\php.exe",
      "args": [
        "c:\\laragon\\www\\php-pdo-class\\mcp\\server.php"
      ]
    }
  }
}
```

**Remember**: Use double backslashes (`\\`) in Windows paths!

## 🐛 Troubleshooting

### Server not starting?
1. Check PHP path: `php -v`
2. Check syntax: `php -l server.php`
3. Check paths use `\\` not `\`

### IDE not detecting?
1. Verify JSON syntax
2. Restart IDE completely
3. Check IDE's MCP settings location

### No analysis output?
1. Server runs in background
2. Check `logs/` directory
3. Check `analysis/` for HTML reports

## 📚 Learn More

- **PROTOCOL.md** - How the audit works
- **SETUP.md** - Detailed setup instructions
- **README.md** - All features explained
- **prompts/** - Real vulnerability examples

## 🎨 Color Theme

Uses TweakCN-inspired dark theme:
- Background: Deep blue-black
- Primary: Bright blue
- Accents: Muted blue-gray
- Success: Green
- Critical: Red
- High: Orange
- Medium: Yellow

## 🚀 Ready to Use!

1. Open `mcp.html`
2. Generate config
3. Add to IDE
4. Start analyzing!

**No installation needed** - Just configure and go! 🎉
