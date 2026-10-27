param(
    [string]$ApkPath = ""
)

$ErrorActionPreference = "Stop"
$RepoDir = $PSScriptRoot

Write-Host "==================================================" -ForegroundColor Cyan
Write-Host " 📱 Exam Portal CSE - Mobile APK Auto-Updater" -ForegroundColor Cyan
Write-Host "==================================================" -ForegroundColor Cyan

# 1. Search candidate locations if path not explicitly provided
if ([string]::IsNullOrWhiteSpace($ApkPath)) {
    $searchPatterns = @(
        "C:\Users\shahi\Downloads\examportalcse*.apk",
        "C:\Users\shahi\Downloads\exam-portal-app\*.apk",
        "$env:USERPROFILE\Downloads\examportalcse*.apk"
    )

    $latestCandidate = $null
    foreach ($pattern in $searchPatterns) {
        $files = Get-ChildItem -Path $pattern -File -ErrorAction SilentlyContinue
        foreach ($file in $files) {
            if ($null -eq $latestCandidate -or $file.LastWriteTime -gt $latestCandidate.LastWriteTime) {
                $latestCandidate = $file
            }
        }
    }

    if ($null -eq $latestCandidate) {
        Write-Host "❌ No APK found in Downloads. Please specify path: .\update-apk.ps1 -ApkPath 'path\to\app.apk'" -ForegroundColor Red
        exit 1
    }
    $sourceApk = $latestCandidate.FullName
} else {
    if (-not (Test-Path $ApkPath)) {
        Write-Host "❌ Specified APK file does not exist: $ApkPath" -ForegroundColor Red
        exit 1
    }
    $sourceApk = (Resolve-Path $ApkPath).Path
}

$destApk = Join-Path $RepoDir "examportalcse.apk"
$indexPath = Join-Path $RepoDir "index.html"

$sourceItem = Get-Item $sourceApk
$sourceSizeMB = [math]::Round($sourceItem.Length / 1MB, 1)
$sourceTime = $sourceItem.LastWriteTime.ToString("yyyy-MM-dd HH:mm:ss")

Write-Host "🔍 Found latest APK source:" -ForegroundColor Yellow
Write-Host "   Path: $sourceApk"
Write-Host "   Size: $sourceSizeMB MB ($($sourceItem.Length) bytes)"
Write-Host "   Modified: $sourceTime"

# Check if target already has same file size and checksum
$needsUpdate = $true
if (Test-Path $destApk) {
    $destItem = Get-Item $destApk
    if ($destItem.Length -eq $sourceItem.Length) {
        $sourceHash = (Get-FileHash -Path $sourceApk -Algorithm SHA256).Hash
        $destHash = (Get-FileHash -Path $destApk -Algorithm SHA256).Hash
        if ($sourceHash -eq $destHash) {
            Write-Host "ℹ️ Target APK in repository is already identical. Forcing refresh..." -ForegroundColor DarkYellow
            $needsUpdate = $false
        }
    }
}

# 2. Copy the APK
Write-Host "📦 Copying APK to repository root: $destApk..." -ForegroundColor Yellow
Copy-Item -Path $sourceApk -Destination $destApk -Force

# 3. Update index.html file size display if needed
if (Test-Path $indexPath) {
    $indexContent = [System.IO.File]::ReadAllText($indexPath, [System.Text.Encoding]::UTF8)
    $formattedSize = "$sourceSizeMB MB"
    
    # Replace file size patterns like 70.6 MB with new size
    $newContent = [System.Text.RegularExpressions.Regex]::Replace($indexContent, 'Download APK \([0-9\.]+ MB\)', "Download APK ($formattedSize)")
    $newContent = [System.Text.RegularExpressions.Regex]::Replace($newContent, 'File Size</div>\s*<div class="[^"]*">[0-9\.]+ MB</div>', "File Size</div>`n                                <div class=`"text-xs sm:text-sm font-black font-mono text-slate-800 mt-0.5`">$formattedSize</div>")
    
    if ($newContent -ne $indexContent) {
        [System.IO.File]::WriteAllText($indexPath, $newContent, [System.Text.Encoding]::UTF8)
        Write-Host "📝 Updated index.html file size badge to: $formattedSize" -ForegroundColor Green
    }
}

# 4. Commit and push to GitHub
Set-Location $RepoDir
Write-Host "🚀 Staging and committing to Git..." -ForegroundColor Yellow

git add examportalcse.apk index.html
$commitMsg = "Update mobile APK to latest build ($sourceSizeMB MB - $sourceTime)"
git commit -m $commitMsg

Write-Host "🌐 Pushing to GitHub (origin/main)..." -ForegroundColor Yellow
git push origin main

if ($LASTEXITCODE -eq 0) {
    Write-Host "==================================================" -ForegroundColor Green
    Write-Host " ✅ SUCCESS! Latest APK is updated and pushed online!" -ForegroundColor Green
    Write-Host "    Version Size: $sourceSizeMB MB" -ForegroundColor Green
    Write-Host "    Your live website will automatically deploy the new APK!" -ForegroundColor Green
    Write-Host "==================================================" -ForegroundColor Green
} else {
    Write-Host "⚠️ Git push returned exit code $LASTEXITCODE. Please check your network or git credentials." -ForegroundColor Red
}
