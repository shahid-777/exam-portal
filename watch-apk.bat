@echo off
title Exam Portal - Live APK File Watcher
echo ==================================================
echo   Starting Live APK Watcher...
echo ==================================================
powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0watch-apk.ps1"
pause
