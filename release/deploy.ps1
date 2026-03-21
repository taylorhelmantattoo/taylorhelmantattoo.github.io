# Deploy changed files to InfinityFree via WinSCP (ReleaseDocs profile)
# Usage: .\deploy.ps1
# Usage (specific files): .\deploy.ps1 index.php configs/tattoo.php

$winscp = "C:\Program Files (x86)\WinSCP\WinSCP.com"
$localRoot = "C:\TaylorHelmanTattoo\release"
$remoteRoot = "/release.taylorhelmantattoo.com/htdocs"

# Default: deploy all files that changed vs the last git commit
if ($args.Count -gt 0) {
    $files = $args
} else {
    $files = git diff --name-only HEAD~1 HEAD 2>$null
    if (-not $files) {
        Write-Host "No changed files detected. Pass filenames explicitly or check git log."
        exit 1
    }
}

$puts = $files | ForEach-Object {
    $rel = $_ -replace '\\','/'
    "put `"$localRoot\$($_ -replace '/','\')`" `"$remoteRoot/$rel`""
}

$script = (@("open ReleaseDocs") + $puts + @("exit")) -join "`n"
$scriptPath = "$env:TEMP\winscp_deploy.txt"
$script | Out-File -FilePath $scriptPath -Encoding ASCII

Write-Host "Uploading:"
$files | ForEach-Object { Write-Host "  $_" }

& $winscp /log="$env:TEMP\winscp_deploy.log" /script="$scriptPath"

if ($LASTEXITCODE -eq 0) {
    Write-Host "`nDeploy successful." -ForegroundColor Green
} else {
    Write-Host "`nDeploy FAILED (exit $LASTEXITCODE). Check $env:TEMP\winscp_deploy.log" -ForegroundColor Red
    exit $LASTEXITCODE
}
