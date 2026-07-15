@echo off
REM ============================================================
REM  LMS - one-click local start
REM  Forces the CORRECT PHP 8.2 and Node 25 (your Laragon
REM  terminal defaults to old PHP 8.1 / Node 16 which won't work).
REM ============================================================

set "PHP_DIR=C:\laragon\bin\php\php-8.2.29-Win32-vs16-x64"
set "NODE_DIR=C:\Program Files\nodejs"

echo Starting LMS with the correct versions...
echo   PHP  : %PHP_DIR%
echo   NODE : %NODE_DIR%
echo.
echo   API      -^> http://127.0.0.1:8000   (backend engine)
echo   WEBSITE  -^> http://localhost:3000    (THIS is your site)
echo.

start "LMS API (Laravel)"    cmd /k "set "PATH=%PHP_DIR%;%PATH%" && cd /d %~dp0backend && php artisan serve --host=127.0.0.1 --port=8000"
start "LMS Website (Next.js)" cmd /k "set "PATH=%NODE_DIR%;%PATH%" && cd /d %~dp0frontend && npm run dev"

echo Two windows opened. Wait ~10 seconds, then open:
echo.
echo     http://localhost:3000
echo.
pause
