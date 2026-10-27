@echo off
title Exam Portal - Update Mobile APK to Website & GitHub
echo ==================================================
echo   Exam Portal CSE - Mobile APK Auto-Updater
echo ==================================================
echo.
echo Searching for the newest APK in Downloads and updating website & GitHub...
echo.

powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0update-apk.ps1"

echo.
echo Press any key to exit...
pause >nul
