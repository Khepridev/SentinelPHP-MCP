// Store config globally to avoid escaping hell in inline onclicks
let currentConfigJson = '';

function detectPhp() {
    const commonPaths = [
        'C:\\laragon\\bin\\php\\php-8.3.19-Win32-vs16-x64\\php.exe',
        'C:\\laragon\\bin\\php\\php-8.2.0-Win32-vs16-x64\\php.exe',
        'C:\\xampp\\php\\php.exe',
        'C:\\wamp64\\bin\\php\\php8.2.0\\php.exe'
    ];

    const input = document.getElementById('phpPath');
    if (input && !input.value) {
        input.value = commonPaths[0];
    }

    const btn = document.getElementById('detectBtn');
    if (btn) {
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check"></i> Found';
        setTimeout(() => { btn.innerHTML = originalHtml; }, 1000);
    }
}

function generateConfig() {
    const phpPath = document.getElementById('phpPath').value || 'php';
    const serverPath = document.getElementById('serverPath').value;

    const cleanPhp = phpPath.replace(/\\/g, '\\\\');
    const cleanServer = serverPath.replace(/\\/g, '\\\\');

    const config = {
        "mcpServers": {
            "sentinel-php": {
                "command": cleanPhp,
                "args": [cleanServer]
            }
        }
    };

    // Store raw JSON for copying
    currentConfigJson = JSON.stringify(config, null, 2);

    // Highlighted HTML for display
    const highlighted = currentConfigJson
        .replace(/"(.*?)":/g, '<span class="json-key">"$1"</span>:')
        .replace(/: "(.*?)"/g, ': <span class="json-string">"$1"</span>');

    const outputDiv = document.getElementById('configOutput');
    const container = document.getElementById('jsonContainer');

    outputDiv.style.display = 'block';

    // FIX: Remove whitespace inside the template literal to prevent indentation issues in <pre>
    container.innerHTML = `<code id="jsonContent">${highlighted}</code><button id="generatedCopyBtn" class="copy-btn" onclick="copyGeneratedConfig(this)"><i class="fas fa-copy"></i> Copy</button>`;

    outputDiv.scrollIntoView({ behavior: 'smooth' });
}

function copyGeneratedConfig(btn) {
    if (!currentConfigJson) return;

    navigator.clipboard.writeText(currentConfigJson).then(() => {
        const originalHtml = btn.innerHTML;

        // Add success class
        btn.classList.add('copied');
        btn.innerHTML = '<i class="fas fa-check"></i> COPIED';

        setTimeout(() => {
            btn.classList.remove('copied');
            btn.innerHTML = originalHtml;
        }, 2000);
    }).catch(err => {
        console.error('Copy failed:', err);
        btn.innerHTML = '<i class="fas fa-times"></i> Error';
    });
}
