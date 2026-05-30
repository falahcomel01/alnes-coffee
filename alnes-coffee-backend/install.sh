#!/bin/bash
# ============================================================
# Script instalasi widget baru - Alnes Coffee Dashboard
# Jalankan dari root project: bash install.sh
# ============================================================

BACKEND="."  # ganti jika dijalankan dari luar folder backend

# 1. Widget PHP
cp TopProductsWidget.php  "$BACKEND/app/Filament/Widgets/TopProductsWidget.php"
cp TableStatusWidget.php  "$BACKEND/app/Filament/Widgets/TableStatusWidget.php"
cp RevenueChartWidget.php "$BACKEND/app/Filament/Widgets/RevenueChartWidget.php"

# 2. Dashboard Page
cp Dashboard.php "$BACKEND/app/Filament/Pages/Dashboard.php"

# 3. Blade Views
mkdir -p "$BACKEND/resources/views/filament/widgets"
cp top-products-widget.blade.php   "$BACKEND/resources/views/filament/widgets/top-products-widget.blade.php"
cp table-status-widget.blade.php   "$BACKEND/resources/views/filament/widgets/table-status-widget.blade.php"

# 4. Clear cache
php artisan config:clear
php artisan view:clear
php artisan cache:clear

echo ""
echo "✅ Selesai! Buka dashboard dan refresh browser."