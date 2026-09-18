#Requires -Version 5.1
<#
    Local development helper for Windows/Laragon.
    Usage: .\deploy\local.ps1 <command>

    Commands:
      setup    Install dependencies, prepare .env, run migrations, build assets.
      dev      Start the Vite dev server (run `php artisan serve` separately, or use Laragon's vhost).
      build    Compile frontend assets for local verification.
      fresh    Drop and re-run all migrations (with seeders).
      test     Run the Pest test suite.
      lint     Run Laravel Pint against changed files.
      all      setup + lint + test, in order.
#>

param(
    [Parameter(Position = 0)]
    [ValidateSet('setup', 'dev', 'build', 'fresh', 'test', 'lint', 'all')]
    [string]$Command = 'setup'
)

$ErrorActionPreference = 'Stop'
$root = Split-Path -Parent $PSScriptRoot
Set-Location $root

function Invoke-Step {
    param([string]$Description, [scriptblock]$Action)
    Write-Host "==> $Description" -ForegroundColor Cyan
    & $Action
    if ($LASTEXITCODE -and $LASTEXITCODE -ne 0) {
        throw "Step failed: $Description"
    }
}

function Invoke-Setup {
    Invoke-Step 'Installing PHP dependencies' { composer install }

    if (-not (Test-Path '.env')) {
        Invoke-Step 'Creating .env from .env.example' { Copy-Item '.env.example' '.env' }
        Invoke-Step 'Generating application key' { php artisan key:generate }
    }

    Invoke-Step 'Installing Node dependencies' { npm install }
    Invoke-Step 'Running database migrations' { php artisan migrate }
    Invoke-Step 'Building frontend assets' { npm run build }
    Invoke-Step 'Linking public storage' { php artisan storage:link }
}

function Invoke-Fresh {
    Invoke-Step 'Refreshing database (fresh + seed)' { php artisan migrate:fresh --seed }
}

function Invoke-Test {
    Invoke-Step 'Clearing config cache' { php artisan config:clear }
    Invoke-Step 'Running Pest test suite' { php artisan test }
}

function Invoke-Lint {
    Invoke-Step 'Running Laravel Pint' { vendor\bin\pint }
}

switch ($Command) {
    'setup' { Invoke-Setup }
    'dev'   { Invoke-Step 'Starting dev servers (php artisan serve + vite)' { composer run dev } }
    'build' { Invoke-Step 'Building frontend assets' { npm run build } }
    'fresh' { Invoke-Fresh }
    'test'  { Invoke-Test }
    'lint'  { Invoke-Lint }
    'all'   {
        Invoke-Setup
        Invoke-Lint
        Invoke-Test
    }
}

Write-Host "==> Done: $Command" -ForegroundColor Green
