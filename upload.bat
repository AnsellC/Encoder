@echo off
SET file=%1
setlocal
set "lock=%temp%\wait%random%.lock"

ECHO 
ECHO Uploading %file%

start "Uploading to Facebook" 9>"%lock%1" php -f %cd%\facebook.php "%file%"
start "Uploading to Rapidvideo" 9>"%lock%2" php -f %cd%\rapidvideo.php "%file%"

:Wait
1>nul 2>nul ping /n 2 ::1
for %%N in (1 2) do (
  (call ) 9>"%lock%%%N" || goto :Wait
) 2>nul

::delete the lock files
del "%lock%*"

:: Finish up
echo DONE

