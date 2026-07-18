@echo off
REM ============================================================
REM  Nova LMS - one-click local start (single Laravel app)
REM  Forces the correct PHP 8.3 (Laragon's terminal may default
REM  to an older PHP that won't run Laravel 12).
REM ============================================================

set "PHP_DIR=C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64"

echo Starting Nova LMS...
echo   PHP  : %PHP_DIR%
echo   SITE -^> http://localhost:8000
echo.
echo   (Use localhost, NOT 127.0.0.1 - session cookies are scoped to it.)
echo.

start "Nova LMS (Laravel)" cmd /k "set "PATH=%PHP_DIR%;%PATH%" && cd /d %~dp0 && php artisan serve --host=127.0.0.1 --port=8000"

echo Server window opened. Wait a few seconds, then open:
echo.
echo     http://localhost:8000
echo.
pause
