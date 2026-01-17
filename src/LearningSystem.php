<?php

/**
 * Learning System for MCP Server
 * 
 * Tracks all analyses, learns from real vulnerabilities,
 * and improves detection over time
 */

class LearningSystem
{
    private $logsDir;
    private $analysisDir;
    private $knowledgeDir;
    private $knowledgeBase;

    public function __construct($baseDir)
    {
        $this->logsDir = $baseDir . '/logs';
        $this->analysisDir = $baseDir . '/analysis';
        $this->knowledgeDir = $baseDir . '/knowledge';

        // Create directories if they don't exist
        foreach ([$this->logsDir, $this->analysisDir, $this->knowledgeDir] as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
        }

        $this->loadKnowledgeBase();
    }

    /**
     * Log an analysis session
     */
    public function logAnalysis($code, $vulnerabilities, $metadata = [])
    {
        $timestamp = date('Y-m-d_H-i-s');
        $logId = uniqid('analysis_', true);

        // Create log entry
        $logEntry = [
            'id' => $logId,
            'timestamp' => date('Y-m-d H:i:s'),
            'code_hash' => md5($code),
            'code_length' => strlen($code),
            'vulnerabilities_found' => count($vulnerabilities),
            'severity_breakdown' => $this->getSeverityBreakdown($vulnerabilities),
            'metadata' => $metadata,
            'real_vulnerabilities' => array_filter($vulnerabilities, function ($v) {
                return isset($v['verified']) && $v['verified'] === true;
            })
        ];

        // Save JSON log
        $logFile = $this->logsDir . '/' . $timestamp . '_' . substr($logId, -8) . '.json';
        file_put_contents($logFile, json_encode($logEntry, JSON_PRETTY_PRINT));

        // Generate HTML report
        $htmlFile = $this->analysisDir . '/' . $timestamp . '_report.html';
        $this->generateHtmlReport($logEntry, $code, $vulnerabilities, $htmlFile);

        // Update knowledge base
        $this->updateKnowledgeBase($vulnerabilities);

        return $logId;
    }

    /**
     * Generate beautiful HTML report
     */
    private function generateHtmlReport($logEntry, $code, $vulnerabilities, $filename)
    {
        $severityColors = [
            'CRITICAL' => '#ff6467',
            'HIGH' => '#ea580c',
            'MEDIUM' => '#ca8a04',
            'LOW' => '#737373'
        ];

        $html = <<<HTML
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Security Analysis Report - {$logEntry['timestamp']}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Geist+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Geist Mono', monospace;
            background: #0a0a0a;
            color: #fafafa;
            min-height: 100vh;
            padding: 2rem;
            font-size: 14px;
            line-height: 1.6;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: #191919;
            border: 1px solid #383838;
            border-radius: 0;
            overflow: hidden;
        }
        
        .header {
            background: #171717;
            color: #fafafa;
            padding: 2rem;
            border-bottom: 1px solid #383838;
        }
        
        .header h1 {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 600;
            letter-spacing: -0.02em;
        }
        
        .header h1 i {
            color: #737373;
        }
        
        .header .timestamp {
            font-size: 0.75rem;
            color: #a1a1a1;
            font-weight: 400;
            margin-top: 0.5rem;
            display: block;
        }
        
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1px;
            background: #383838;
            border-bottom: 1px solid #383838;
        }
        
        .stat-card {
            background: #262626;
            padding: 1.5rem;
            border: 1px solid #383838;
        }
        
        .stat-card h3 {
            font-size: 0.6875rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: #a1a1a1;
            margin-bottom: 0.75rem;
            font-weight: 600;
        }
        
        .stat-card .value {
            font-size: 1.75rem;
            font-weight: 700;
            color: #fafafa;
        }
        
        .stat-card .value.small {
            font-size: 0.875rem;
            word-break: break-all;
        }
        
        .severity-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.375rem 0.75rem;
            border-radius: 0;
            font-size: 0.6875rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border: 1px solid;
        }
        
        .content {
            padding: 2rem;
        }
        
        .vulnerability {
            background: #262626;
            border: 1px solid #383838;
            border-radius: 0;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            transition: all 0.15s;
        }
        
        .vulnerability:hover {
            border-color: #737373;
        }
        
        .vulnerability-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 1rem;
            gap: 1rem;
        }
        
        .vulnerability-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: #fafafa;
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .vulnerability-title i {
            color: #737373;
            font-size: 1rem;
        }
        
        .vulnerability-description {
            color: #a1a1a1;
            line-height: 1.7;
            margin-bottom: 1rem;
            font-size: 0.875rem;
        }
        
        .code-block {
            background: #171717;
            color: #fafafa;
            padding: 1rem;
            border-radius: 0;
            overflow-x: auto;
            font-family: 'Geist Mono', monospace;
            font-size: 0.8125rem;
            line-height: 1.5;
            margin: 1rem 0;
            border: 1px solid #383838;
        }
        
        .pattern {
            background: #262626;
            border-left: 2px solid #737373;
            padding: 1rem;
            border-radius: 0;
            margin: 1rem 0;
            border: 1px solid #383838;
            border-left-width: 2px;
        }
        
        .pattern-title {
            font-weight: 600;
            color: #fafafa;
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .pattern-title i {
            color: #737373;
        }
        
        .pattern code {
            color: #a1a1a1;
            font-size: 0.8125rem;
        }
        
        .footer {
            background: #171717;
            padding: 1.5rem 2rem;
            border-top: 1px solid #383838;
            text-align: center;
            color: #a1a1a1;
            font-size: 0.75rem;
        }
        
        .footer i {
            color: #737373;
            margin: 0 0.25rem;
        }
        
        .footer a {
            color: #737373;
            text-decoration: none;
            transition: color 0.15s;
        }
        
        .footer a:hover {
            color: #fafafa;
        }
        
        .signature {
            margin-top: 0.75rem;
            padding-top: 0.75rem;
            border-top: 1px solid #383838;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
            flex-wrap: wrap;
            font-size: 0.6875rem;
        }
        
        .signature-link {
            display: flex;
            align-items: center;
            gap: 0.375rem;
        }
        
        .no-vulnerabilities {
            text-align: center;
            padding: 4rem 2rem;
        }
        
        .no-vulnerabilities-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            color: #737373;
        }
        
        .no-vulnerabilities-text {
            font-size: 1.25rem;
            color: #fafafa;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .no-vulnerabilities-subtext {
            color: #a1a1a1;
            font-size: 0.875rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>
                <i class="fas fa-shield-halved"></i>
                SECURITY ANALYSIS REPORT
            </h1>
            <span class="timestamp"><i class="fas fa-clock"></i> {$logEntry['timestamp']}</span>
        </div>
        
        <div class="stats">
            <div class="stat-card">
                <h3><i class="fas fa-bug"></i> VULNERABILITIES</h3>
                <div class="value">{$logEntry['vulnerabilities_found']}</div>
            </div>
            <div class="stat-card">
                <h3><i class="fas fa-code"></i> CODE SIZE</h3>
                <div class="value">{$logEntry['code_length']}</div>
            </div>
            <div class="stat-card">
                <h3><i class="fas fa-fingerprint"></i> ANALYSIS ID</h3>
                <div class="value small">{$logEntry['id']}</div>
            </div>
        </div>
        
        <div class="content">
HTML;

        if (empty($vulnerabilities)) {
            $html .= <<<HTML
            <div class="no-vulnerabilities">
                <div class="no-vulnerabilities-icon"><i class="fas fa-circle-check"></i></div>
                <div class="no-vulnerabilities-text">NO VULNERABILITIES DETECTED</div>
                <p class="no-vulnerabilities-subtext">The analyzed code passed all security checks.</p>
            </div>
HTML;
        } else {
            foreach ($vulnerabilities as $vuln) {
                $severity = strtoupper($vuln['severity']);
                $color = $severityColors[$severity] ?? '#737373';

                $icon = match ($severity) {
                    'CRITICAL' => 'fa-circle-exclamation',
                    'HIGH' => 'fa-triangle-exclamation',
                    'MEDIUM' => 'fa-circle-info',
                    'LOW' => 'fa-circle',
                    default => 'fa-circle'
                };

                $html .= <<<HTML
            <div class="vulnerability">
                <div class="vulnerability-header">
                    <div>
                        <div class="vulnerability-title">
                            <i class="fas $icon"></i>
                            {$vuln['name']}
                        </div>
                        <span class="severity-badge" style="background: {$color}; color: white; border-color: {$color};">{$severity}</span>
                    </div>
                </div>
                <div class="vulnerability-description">{$vuln['description']}</div>
HTML;

                if (isset($vuln['pattern'])) {
                    $html .= <<<HTML
                <div class="pattern">
                    <div class="pattern-title"><i class="fas fa-magnifying-glass"></i> PATTERN DETECTED</div>
                    <code>{$vuln['pattern']}</code>
                </div>
HTML;
                }

                if (isset($vuln['test'])) {
                    $testCode = htmlspecialchars($vuln['test']);
                    $html .= <<<HTML
                <div class="pattern-title" style="margin-top: 1rem;"><i class="fas fa-bomb"></i> PROOF OF CONCEPT</div>
                <div class="code-block">{$testCode}</div>
HTML;
                }

                $html .= "</div>";
            }
        }

        $html .= <<<HTML
        </div>
        
        <div class="footer">
            <div>
                <i class="fas fa-shield-halved"></i> Generated by PHP Security Audit MCP Server <i class="fas fa-circle"></i> Protocol-Based Analysis
            </div>
            <div class="signature">
                <span>Developed by <strong>Khepridev</strong></span>
                <a href="https://github.com/Khepridev" target="_blank" class="signature-link">
                    <i class="fab fa-github"></i> GitHub
                </a>
                <a href="https://khepridev.xyz/" target="_blank" class="signature-link">
                    <i class="fas fa-globe"></i> Website
                </a>
                <a href="https://x.com/Khepridev" target="_blank" class="signature-link">
                    <i class="fab fa-x-twitter"></i> X.com
                </a>
            </div>
        </div>
    </div>
</body>
</html>
HTML;

        file_put_contents($filename, $html);
    }

    /**
     * Load knowledge base from previous analyses
     */
    private function loadKnowledgeBase()
    {
        $knowledgeFile = $this->knowledgeDir . '/knowledge_base.json';

        if (file_exists($knowledgeFile)) {
            $this->knowledgeBase = json_decode(file_get_contents($knowledgeFile), true);
        } else {
            $this->knowledgeBase = [
                'total_analyses' => 0,
                'total_vulnerabilities' => 0,
                'patterns' => [],
                'false_positives' => [],
                'verified_exploits' => [],
                'last_updated' => null
            ];
        }
    }

    /**
     * Update knowledge base with new findings
     */
    private function updateKnowledgeBase($vulnerabilities)
    {
        $this->knowledgeBase['total_analyses']++;
        $this->knowledgeBase['total_vulnerabilities'] += count($vulnerabilities);
        $this->knowledgeBase['last_updated'] = date('Y-m-d H:i:s');

        foreach ($vulnerabilities as $vuln) {
            $pattern = $vuln['pattern'] ?? 'unknown';

            if (!isset($this->knowledgeBase['patterns'][$pattern])) {
                $this->knowledgeBase['patterns'][$pattern] = [
                    'count' => 0,
                    'severity' => $vuln['severity'],
                    'first_seen' => date('Y-m-d H:i:s'),
                    'examples' => []
                ];
            }

            $this->knowledgeBase['patterns'][$pattern]['count']++;

            // Store verified exploits
            if (isset($vuln['verified']) && $vuln['verified'] === true) {
                $this->knowledgeBase['verified_exploits'][] = [
                    'pattern' => $pattern,
                    'test' => $vuln['test'] ?? null,
                    'timestamp' => date('Y-m-d H:i:s')
                ];
            }
        }

        // Save knowledge base
        $knowledgeFile = $this->knowledgeDir . '/knowledge_base.json';
        file_put_contents($knowledgeFile, json_encode($this->knowledgeBase, JSON_PRETTY_PRINT));
    }

    /**
     * Get patterns from knowledge base
     */
    public function getLearnedPatterns()
    {
        return $this->knowledgeBase['patterns'] ?? [];
    }

    /**
     * Get statistics
     */
    public function getStatistics()
    {
        return [
            'total_analyses' => $this->knowledgeBase['total_analyses'],
            'total_vulnerabilities' => $this->knowledgeBase['total_vulnerabilities'],
            'unique_patterns' => count($this->knowledgeBase['patterns']),
            'verified_exploits' => count($this->knowledgeBase['verified_exploits']),
            'last_updated' => $this->knowledgeBase['last_updated']
        ];
    }

    /**
     * Get recent analyses
     */
    public function getRecentAnalyses($limit = 10)
    {
        $logs = glob($this->logsDir . '/*.json');
        rsort($logs);
        $logs = array_slice($logs, 0, $limit);

        $analyses = [];
        foreach ($logs as $logFile) {
            $data = json_decode(file_get_contents($logFile), true);
            $analyses[] = $data;
        }

        return $analyses;
    }

    /**
     * Get severity breakdown
     */
    private function getSeverityBreakdown($vulnerabilities)
    {
        $breakdown = [
            'CRITICAL' => 0,
            'HIGH' => 0,
            'MEDIUM' => 0,
            'LOW' => 0
        ];

        foreach ($vulnerabilities as $vuln) {
            $severity = strtoupper($vuln['severity']);
            if (isset($breakdown[$severity])) {
                $breakdown[$severity]++;
            }
        }

        return $breakdown;
    }
}
