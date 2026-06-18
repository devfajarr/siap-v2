-- =============================================================================
-- SIAP - Sistem Informasi Akademik POLSA
-- PostgreSQL Initialization Script
-- =============================================================================
-- Script ini dijalankan SEKALI saat volume PostgreSQL pertama kali dibuat.
-- Selanjutnya, migrasi dihandle oleh Laravel Artisan.
-- =============================================================================

-- Pastikan encoding dan timezone benar
SET client_encoding = 'UTF8';
SET timezone = 'Asia/Jakarta';

-- Aktifkan ekstensi yang mungkin dibutuhkan Laravel
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
CREATE EXTENSION IF NOT EXISTS "pg_trgm";  -- Untuk full-text search yang lebih baik

-- Log inisialisasi
DO $$
BEGIN
    RAISE NOTICE 'SIAP Database initialized at %', NOW();
END $$;
