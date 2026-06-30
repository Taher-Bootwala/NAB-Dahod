@echo off
REM ============================================================
REM  Blind School Dahod - dev server launcher (Windows / XAMPP)
REM ============================================================
SET PHP=C:\xampp\php\php.exe
IF NOT EXIST "%PHP%" SET PHP=php

IF NOT EXIST ".env" (
  echo Creating .env from .env.example ...
  copy ".env.example" ".env" >nul
)

echo.
echo  National Association for the Blind, Dahod is starting...
echo  Open  http://localhost:8000  in your browser.
echo  Press Ctrl+C to stop.
echo.
"%PHP%" -S localhost:8000 -t public public\router.php
