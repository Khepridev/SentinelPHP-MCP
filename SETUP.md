# MCP Server Setup Guide (Windows)

## Prerequisites

- PHP 7.4 or higher
- Composer (optional, for autoloading)
- An IDE that supports MCP (VS Code, Cursor, Windsurf, etc.)

## Installation Steps

### 1. Install Composer Dependencies (Optional)

```bash
cd c:\laragon\www\php-pdo-class\mcp
composer install
```

**Note**: Currently, the server has no external dependencies, so this step is optional. The autoloader is set up for future extensions.

### 2. Test Server

```bash
# Check syntax
php -l server.php

# Test run (will wait for MCP input)
php server.php
```

Press `Ctrl+C` to stop.

### 3. Configure Your IDE

#### Option A: Use the HTML Generator

1. Open `mcp.html` in your browser
2. Fill in your PHP path and server path
3. Click "Generate Configuration"
4. Copy the JSON output

#### Option B: Manual Configuration

Create/edit your IDE's MCP configuration file:

**For Cursor/Windsurf**: Usually in settings or config file

**Example Configuration**:
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

**Important**: 
- Use double backslashes (`\\`) in Windows paths
- Use absolute paths for both PHP and server.php

### 4. Common PHP Paths

- **Laragon**: `C:\laragon\bin\php\php-8.x.x-Win32-vs16-x64\php.exe`
- **XAMPP**: `C:\xampp\php\php.exe`
- **WAMP**: `C:\wamp64\bin\php\php8.x.x\php.exe`
- **Manual Install**: `C:\Program Files\PHP\php.exe`

### 5. Verify Installation

1. Restart your IDE
2. Open AI assistant
3. Try using MCP tools:
   - "Use the analyze_php_security tool"
   - "Show me the knowledge base"
   - "Get recent analyses"

## Directory Structure

```
mcp/
├── server.php              # Main MCP server
├── composer.json           # Composer config
├── mcp.html               # Config generator
├── PROTOCOL.md            # Security protocol
├── README.md              # Documentation
├── src/
│   └── LearningSystem.php # Learning system
├── prompts/               # Vulnerability patterns
│   ├── sql-injection.md
│   ├── xss.md
│   └── dos.md
├── logs/                  # JSON logs (auto-created)
├── analysis/              # HTML reports (auto-created)
└── knowledge/             # Knowledge base (auto-created)
```

## Usage Examples

### Analyze PHP Code

```
Analyze this code for SQL injection:
$db->where('id', $_GET['id'], $_GET['op']);
```

### View Knowledge Base

```
Show me the learned vulnerability patterns
```

### Get Recent Analyses

```
What are the last 5 security analyses?
```

## Troubleshooting

### Server Not Starting

1. Check PHP path is correct:
   ```bash
   C:\laragon\bin\php\php-8.2.0-Win32-vs16-x64\php.exe -v
   ```

2. Check server.php syntax:
   ```bash
   php -l server.php
   ```

3. Check file permissions

### IDE Not Detecting Server

1. Verify JSON syntax in config
2. Restart IDE completely
3. Check IDE's MCP logs
4. Ensure paths use double backslashes

### No Output

1. Server runs in background
2. Check `logs/` directory for activity
3. Use IDE's MCP debug mode

## Advanced Configuration

### Custom Paths

You can customize where logs and reports are saved by modifying `LearningSystem.php`:

```php
$this->logsDir = 'C:/custom/path/logs';
$this->analysisDir = 'C:/custom/path/analysis';
```

### Multiple Servers

You can run multiple MCP servers:

```json
{
  "mcpServers": {
    "php-security-audit": {
      "command": "C:\\laragon\\bin\\php\\php.exe",
      "args": ["c:\\path\\to\\mcp\\server.php"]
    },
    "another-server": {
      "command": "node",
      "args": ["c:\\path\\to\\another-server.js"]
    }
  }
}
```

## Support

- Check `PROTOCOL.md` for security audit methodology
- View `README.md` for feature documentation
- Open `mcp.html` for configuration help
- Check `analysis/` folder for HTML reports

## License

MIT
