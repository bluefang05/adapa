$ErrorActionPreference = "Stop"

$Root = Split-Path -Parent $PSScriptRoot
$KeyProps = Join-Path $Root "android\key.properties"

function To-PlainText([Security.SecureString]$Secure) {
    $ptr = [Runtime.InteropServices.Marshal]::SecureStringToBSTR($Secure)
    try {
        return [Runtime.InteropServices.Marshal]::PtrToStringBSTR($ptr)
    }
    finally {
        [Runtime.InteropServices.Marshal]::ZeroFreeBSTR($ptr)
    }
}

$StoreFile = Read-Host "Ruta absoluta al adapa-upload.jks"
if (-not (Test-Path $StoreFile)) {
    throw "No existe el keystore: $StoreFile"
}

$Alias = Read-Host "Alias [adapa-upload]"
if ([string]::IsNullOrWhiteSpace($Alias)) {
    $Alias = "adapa-upload"
}

$StorePasswordSecure = Read-Host "Store password" -AsSecureString
$SamePassword = Read-Host "¿Key password es igual? [S/n]"
$StorePassword = To-PlainText $StorePasswordSecure

if ([string]::IsNullOrWhiteSpace($SamePassword) -or $SamePassword.ToLower() -eq "s") {
    $KeyPassword = $StorePassword
}
else {
    $KeyPasswordSecure = Read-Host "Key password" -AsSecureString
    $KeyPassword = To-PlainText $KeyPasswordSecure
}

# Java properties on Windows expects escaped backslashes.
$EscapedStoreFile = $StoreFile -replace '\\', '\\\\'

@"
storePassword=$StorePassword
keyPassword=$KeyPassword
keyAlias=$Alias
storeFile=$EscapedStoreFile
"@ | Set-Content -Path $KeyProps -Encoding ascii

$StorePassword = $null
$KeyPassword = $null

Write-Host "Creado: $KeyProps" -ForegroundColor Green
Write-Host "Ese archivo está ignorado por Git y no debe compartirse." -ForegroundColor Yellow
