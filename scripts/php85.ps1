$ErrorActionPreference = 'Stop'
$projectRoot = Split-Path -Parent $PSScriptRoot
$php85 = if ($env:SI_MELAYUR_PHP85_BINARY) {
    $env:SI_MELAYUR_PHP85_BINARY
} else {
    Join-Path $projectRoot '.codex-artifacts/php85/php.exe'
}

if (-not (Test-Path -LiteralPath $php85 -PathType Leaf)) {
    throw 'PHP 8.5 belum tersedia. Pasang PHP 8.5, lalu atur SI_MELAYUR_PHP85_BINARY ke lokasi php.exe. Lihat docs/php85-readiness.md.'
}

$versionOutput = (& $php85 --version 2>&1 | Out-String)
if ($LASTEXITCODE -ne 0 -or $versionOutput -notmatch '(?m)^PHP 8\.5\.') {
    throw 'Perintah ini memerlukan PHP 8.5.x. Periksa SI_MELAYUR_PHP85_BINARY.'
}

Push-Location -LiteralPath $projectRoot
try {
    & $php85 @args
    $phpExitCode = $LASTEXITCODE
} finally {
    Pop-Location
}
exit $phpExitCode
