# Autosave helper: snapshot uncommitted work to a local patch every N seconds
# so an interrupted session can always be recovered.
#
#   powershell -File scripts/autosave.ps1              # every 45s (default)
#   powershell -File scripts/autosave.ps1 -Once        # single snapshot
#
# Snapshots are written to storage/app/autosave/ and never touch git history.
# Recover with:  git apply storage/app/autosave/latest.patch

param(
    [int]$IntervalSeconds = 45,
    [switch]$Once
)

$ErrorActionPreference = 'Continue'
$repo = Split-Path -Parent $PSScriptRoot
$outDir = Join-Path $repo 'storage\app\autosave'
Set-Location $repo
New-Item -ItemType Directory -Path $outDir -Force | Out-Null

function Save-Snapshot {
    $dirty = git status --porcelain
    if (-not $dirty) { return }

    # Stage everything so new (untracked) files are included in the diff,
    # then read the diff without touching the index state other tools see.
    git add -A 2>&1 | Out-Null
    $patch = git diff --cached --binary 2>&1 | Out-String

    if ($patch.Trim()) {
        $stamp = Get-Date -Format 'yyyyMMdd-HHmmss'
        Set-Content -Path (Join-Path $outDir 'latest.patch') -Value $patch -Encoding UTF8
        Set-Content -Path (Join-Path $outDir "$stamp.patch") -Value $patch -Encoding UTF8
        Set-Content -Path (Join-Path $outDir 'latest.txt') -Value "saved $stamp`n$dirty" -Encoding UTF8
        Write-Host "[autosave] $stamp  ($($dirty.Count) change(s))"
    }

    # Keep the index as we found it so the working tree stays "modified".
    git reset -q 2>&1 | Out-Null
}

if ($Once) {
    Save-Snapshot
    return
}

Write-Host "[autosave] watching $repo every ${IntervalSeconds}s -> $outDir"
while ($true) {
    Save-Snapshot
    Start-Sleep -Seconds $IntervalSeconds
}
