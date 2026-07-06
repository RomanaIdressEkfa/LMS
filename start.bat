@echo off
REM ============================================================
REM  LMS - one-click local start
REM  Opens TWO windows: the API (Laravel) and the website (Next.js)
REM ============================================================

echo Starting LMS...
echo.
echo   API      -> http://127.0.0.1:8000   (Laravel - backend, JSON only)
echo   WEBSITE  -> http://localhost:3000    (Next.js - THIS is your site)
echo.

start "LMS API (Laravel)"   cmd /k "cd /d %~dp0backend && php artisan serve --host=127.0.0.1 --port=8000"
start "LMS Website (Next.js)" cmd /k "cd /d %~dp0frontend && npm run dev"

echo Two windows opened. Wait ~10 seconds, then open:
echo.
echo     http://localhost:3000
echo.
pause
