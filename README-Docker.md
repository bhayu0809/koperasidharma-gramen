# Docker PHP App

Docker ini hanya menjalankan aplikasi PHP/Apache. Database tetap memakai MySQL dari MAMP.

## Jalankan

1. Pastikan MySQL MAMP aktif dan database `db_koperasi_gramen` sudah ada.
2. Pastikan Docker Desktop aktif.
3. Dari folder project, jalankan:

```sh
docker compose up -d --build
```

Aplikasi akan tersedia di:

```text
http://localhost:8080/
```

## Konfigurasi

Default konfigurasi ada di `.env`:

```env
APP_PORT=8080
APP_BASE_URL=http://localhost:8080/

DB_HOST=host.docker.internal
DB_PORT=3306
DB_USERNAME=root
DB_PASSWORD=root
DB_DATABASE=db_koperasi_gramen
```

Kalau MySQL MAMP kamu berjalan di port `8889`, ubah `DB_PORT=8889`.
