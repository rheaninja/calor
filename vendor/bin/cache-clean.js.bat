@ECHO OFF
setlocal DISABLEDELAYEDEXPANSION
SET BIN_TARGET=%~dp0/../mage2tv/magento-cache-clean/bin/cache-clean.js
node "%BIN_TARGET%" %*
