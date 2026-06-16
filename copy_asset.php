<?php
// ============================================================
// NexaBank – Copy Asset Script
// Copies the generated banner image to the assets folder.
// ============================================================

$source = 'C:\Users\91738\.gemini\antigravity\brain\6afdba06-eaa0-4b81-b5b1-f0f138f4f4e3\nexabank_banner_1781629720354.png';
$destDir = __DIR__ . '/assets';
$destFile = $destDir . '/banner.png';

echo "<!DOCTYPE html>
<html>
<head>
    <title>Asset Setup – NexaBank</title>
    <style>
        body {
            font-family: sans-serif;
            background: #060c1a;
            color: #e8edf8;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
        }
        .card {
            background: #0b1428;
            border: 1px solid rgba(212,170,82,0.3);
            border-radius: 14px;
            padding: 32px;
            text-align: center;
            max-width: 450px;
            box-shadow: 0 4px 32px rgba(0,0,0,0.5);
        }
        h1 { color: #d4aa52; margin-top: 0; }
        p { color: #c8d0e4; line-height: 1.5; font-size: 0.95rem; }
        .success { color: #26c97e; font-weight: bold; }
        .error { color: #f05a6e; font-weight: bold; }
    </style>
</head>
<body>
    <div class='card'>";

if (!file_exists($source)) {
    echo "<h1>Setup Error</h1>
          <p class='error'>Source banner image not found at:</p>
          <p style='font-family: monospace; font-size: 0.8rem; background: rgba(255,255,255,0.05); padding: 10px; border-radius: 6px; word-break: break-all;'>$source</p>
          <p>Please make sure the file is located at the specified location.</p>";
} else {
    if (!is_dir($destDir)) {
        mkdir($destDir, 0777, true);
    }
    
    if (copy($source, $destFile)) {
        echo "<h1>Setup Successful!</h1>
              <p class='success'>✓ Banner copied successfully to assets/banner.png</p>
              <p>The image will now render in your README.md. This script has been removed for security.</p>";
        // Remove the setup file after successful copy
        @unlink(__FILE__);
    } else {
        echo "<h1>Setup Failed</h1>
              <p class='error'>✕ Could not copy the file.</p>
              <p>Check if the web server has write permissions for the project directory.</p>";
    }
}

echo "    </div>
</body>
</html>";
?>
