# Laravel Queue Worker - Auto-restart
$projectPath = "C:\web\lobby69-v3"
$phpExe      = "C:\php\php.exe"
$logFile     = "$projectPath\storage\logs\worker.log"

while ($true) {
    $timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
    Add-Content $logFile "[$timestamp] Worker iniciando..."

    & $phpExe "$projectPath\artisan" queue:work `
        --queue=default `
        --tries=3 `
        --backoff=10 `
        --sleep=3 `
        --max-time=3600

    $timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
    Add-Content $logFile "[$timestamp] Worker detenido, reiniciando en 5s..."
    Start-Sleep -Seconds 5
}