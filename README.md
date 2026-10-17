# Mizan AI — Backend

Laravel 11 REST API for Pakistan Court Judgment Research Agent.

## Tech Stack
- PHP 8.4 / Laravel 13
- PostgreSQL 16 + pgvector
- Laravel AI SDK (Gemini embeddings)
- Laravel Sanctum (API auth)

## Features
- Hybrid Search (BM25 + Semantic via RRF)
- Case Treatment Tracker
- AI Research Memo Generator
- Alerts & Monitoring
- Evaluation Metrics

## Setup
git clone https://github.com/yourusername/pakistan-legal-backend
cd pakistan-legal-backend
composer install
cp .env.example .env
php artisan key:generate

## Database
sudo service postgresql start
php artisan migrate

## Run
php artisan serve
