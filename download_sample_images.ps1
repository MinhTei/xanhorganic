# download_sample_images.ps1
#
# Download sample images for development/testing into the project
# Usage (PowerShell 5.1):
#   cd C:\wamp64\www\xanhorganic
#   .\download_sample_images.ps1
#
# This script fetches images from source.unsplash.com (free-to-use sample photos)
# and places them into `assets/images/products/` and `assets/images/categories/`.

$BaseDir = Join-Path $PSScriptRoot 'assets\images'
New-Item -Path (Join-Path $BaseDir 'products') -ItemType Directory -Force | Out-Null
New-Item -Path (Join-Path $BaseDir 'categories') -ItemType Directory -Force | Out-Null

$files = @(
    @{ Url = 'https://source.unsplash.com/800x600/?apple,fruit'; File = 'products\apple.jpg' },
    @{ Url = 'https://source.unsplash.com/800x600/?banana,fruit'; File = 'products\banana.jpg' },
    @{ Url = 'https://source.unsplash.com/800x600/?cabbage,vegetable'; File = 'products\cabbage.jpg' },
    @{ Url = 'https://source.unsplash.com/800x600/?carrot,vegetable'; File = 'products\carrot.jpg' },
    @{ Url = 'https://source.unsplash.com/800x600/?salmon,fish'; File = 'products\salmon.jpg' },
    @{ Url = 'https://source.unsplash.com/800x600/?chicken,meat'; File = 'products\chicken.jpg' },
    @{ Url = 'https://source.unsplash.com/800x600/?strawberry,fruit'; File = 'products\strawberry.jpg' },
    @{ Url = 'https://source.unsplash.com/800x600/?rice,grain'; File = 'products\rice.jpg' },

    # category images (larger)
    @{ Url = 'https://source.unsplash.com/1200x800/?vegetables'; File = 'categories\vegetables.jpg' },
    @{ Url = 'https://source.unsplash.com/1200x800/?fruits'; File = 'categories\fruits.jpg' },
    @{ Url = 'https://source.unsplash.com/1200x800/?meat'; File = 'categories\meat.jpg' },
    @{ Url = 'https://source.unsplash.com/1200x800/?dairy'; File = 'categories\dairy.jpg' },
    @{ Url = 'https://source.unsplash.com/1200x800/?grains'; File = 'categories\grains.jpg' }
)

Write-Host "Downloading sample images into: $BaseDir" -ForegroundColor Cyan
foreach ($f in $files) {
    $out = Join-Path $BaseDir $f.File
    Write-Host " -> $($f.Url)  ->  $out"
    try {
        # Use BasicParsing for PS 5.1
        Invoke-WebRequest -Uri $f.Url -OutFile $out -UseBasicParsing -ErrorAction Stop
    } catch {
        Write-Warning "Failed to download $($f.Url): $_"
    }
}

Write-Host "Done. Verify images in $BaseDir\products and $BaseDir\categories" -ForegroundColor Green
