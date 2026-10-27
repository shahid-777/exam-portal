$DownloadsPath = "C:\Users\shahi\Downloads"
$RepoDir = $PSScriptRoot

Write-Host "==================================================" -ForegroundColor Cyan
Write-Host " 👁️ Exam Portal CSE - Live APK File Watcher" -ForegroundColor Cyan
Write-Host " Monitoring: $DownloadsPath\examportalcse*.apk" -ForegroundColor Cyan
Write-Host " Whenever a new APK arrives, it will auto-update & push to GitHub!" -ForegroundColor Yellow
Write-Host " Keep this window open in the background (Press Ctrl+C to stop)." -ForegroundColor DarkGray
Write-Host "==================================================" -ForegroundColor Cyan

$watcher = New-Object System.IO.FileSystemWatcher
$watcher.Path = $DownloadsPath
$watcher.Filter = "*examportalcse*.apk"
$watcher.IncludeSubdirectories = $false
$watcher.EnableRaisingEvents = $true

$action = {
    $path = $Event.SourceEventArgs.FullPath
    $changeType = $Event.SourceEventArgs.ChangeType
    Write-Host "`n⚡ Change detected [$changeType]: $path" -ForegroundColor Yellow
    Write-Host "⏳ Waiting 3 seconds for file write to complete..." -ForegroundColor DarkGray
    Start-Sleep -Seconds 3
    
    try {
        & "$RepoDir\update-apk.ps1" -ApkPath $path
    } catch {
        Write-Host "⚠️ Sync error: $_" -ForegroundColor Red
    }
}

Register-ObjectEvent $watcher 'Created' -Action $action | Out-Null
Register-ObjectEvent $watcher 'Changed' -Action $action | Out-Null

while ($true) {
    Start-Sleep -Seconds 1
}
