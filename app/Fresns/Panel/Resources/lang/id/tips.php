<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Fresns Panel Tips Language Lines
    |--------------------------------------------------------------------------
    */

    'createSuccess' => 'Menciptakan kesuksesan',
    'deleteSuccess' => 'berhasil dihapus',
    'updateSuccess' => 'Berhasil dimodifikasi',
    'upgradeSuccess' => 'Pembaruan selesai',
    'installSuccess' => 'Instal Sukses',
    'installFailure' => 'Instal Kegagalan',
    'uninstallSuccess' => 'Uninstall Sukses',
    'uninstallFailure' => 'Copot Pemasangan Gagal',
    'copySuccess' => 'Salin kesuksesan',
    'viewLog' => 'Ada masalah dengan implementasi, silakan lihat log sistem Fresns untuk detailnya',
    // auth empty
    'auth_empty_title' => 'Harap gunakan portal yang benar untuk masuk ke panel',
    'auth_empty_description' => 'Anda telah keluar atau waktu login Anda telah habis, silakan kunjungi portal login untuk masuk kembali.',
    // request
    'request_in_progress' => 'permintaan sedang diproses...',
    'requestSuccess' => 'Permintaan Sukses',
    'requestFailure' => 'Permintaan Gagal',
    // install
    'install_not_entered_key' => 'Silakan masukkan kunci fresns',
    'install_not_entered_directory' => 'Silakan masukkan direktori',
    'install_not_upload_zip' => 'Silakan pilih paket instalasi',
    'install_in_progress' => 'Instal sedang berlangsung...',
    'install_end' => 'Akhir pemasangan',
    // upgrade
    'upgrade_none' => 'Tidak ada pembaruan',
    'upgrade_fresns' => 'Ada versi fresns baru yang tersedia untuk upgrade',
    'upgrade_fresns_tip' => 'Anda dapat meningkatkan ke',
    'upgrade_fresns_warning' => 'Harap buat cadangan database sebelum memutakhirkan untuk menghindari kehilangan data yang disebabkan oleh pemutakhiran yang tidak tepat.',
    'upgrade_confirm_tip' => 'Tentukan peningkatan?',
    'physical_upgrade_tip' => 'Upgrade ini tidak mendukung upgrade otomatis, silakan gunakan metode "upgrade fisik".',
    'physical_upgrade_version_guide' => 'Klik untuk membaca deskripsi pembaruan versi ini',
    'physical_upgrade_guide' => 'Panduan Peningkatan',
    'physical_upgrade_file_error' => 'Ketidakcocokan file peningkatan fisik',
    'physical_upgrade_confirm_tip' => 'Pastikan Anda telah membaca "Panduan Peningkatan" dan memproses file versi baru sesuai dengan panduan.',
    'upgrade_in_progress' => 'Peningkatan sedang berlangsung...',
    'upgrade_step_1' => 'Verifikasi inisialisasi',
    'upgrade_step_2' => 'Unduh Paket Aplikasi',
    'upgrade_step_3' => 'Paket aplikasi unzip',
    'upgrade_step_4' => 'Upgrade Aplikasi',
    'upgrade_step_5' => 'Mengosongkan cache',
    'upgrade_step_6' => 'Menyelesaikan',
    // uninstall
    'uninstall_in_progress' => 'Pencopotan sedang berlangsung...',
    'uninstall_step_1' => 'Verifikasi inisialisasi',
    'uninstall_step_2' => 'Pengolahan data',
    'uninstall_step_3' => 'Hapus file',
    'uninstall_step_4' => 'Cure cache',
    'uninstall_step_5' => 'Selesai',
    // website
    'website_path_empty_error' => 'Gagal menyimpan, parameter jalur tidak diperbolehkan kosong',
    'website_path_format_error' => 'Gagal disimpan, parameter jalur hanya didukung dalam huruf Inggris biasa',
    'website_path_reserved_error' => 'Simpan gagal, parameter jalur berisi nama parameter yang dicadangkan sistem',
    'website_path_unique_error' => 'Gagal menyimpan, parameter jalur duplikat, nama parameter jalur tidak diperbolehkan untuk saling mengulang',
    // theme
    'theme_error' => 'Temanya salah atau tidak ada',
    'theme_functions_file_error' => 'File tampilan untuk konfigurasi tema salah atau tidak ada',
    'theme_json_file_error' => 'File konfigurasi tema salah atau tidak ada',
    'theme_json_format_error' => 'Kesalahan format file konfigurasi tema',
    // others
    'account_not_found' => 'Akun tidak ada atau masukkan kesalahan',
    'account_login_limit' => 'Kesalahan telah melampaui batas sistem. Silakan masuk lagi 1 jam kemudian',
    'timezone_error' => 'Zona waktu basis data tidak cocok dengan zona waktu di file konfigurasi .env',
    'timezone_env_edit_tip' => 'Harap ubah item konfigurasi pengidentifikasi zona waktu di file .env',
    'secure_entry_route_conflicts' => 'Konflik perutean masuk keselamatan',
    'language_exists' => 'Bahasa sudah ada',
    'language_not_exists' => 'bahasa tidak ada',
    'plugin_not_exists' => 'plugin tidak ada',
    'map_exists' => 'Penyedia layanan peta telah digunakan dan tidak dapat dibuat ulang',
    'map_not_exists' => 'peta tidak ada',
    'required_user_role_name' => 'Silakan isi nama perannya',
    'required_sticker_category_name' => 'Silakan isi nama grup ekspresi',
    'required_group_category_name' => 'Silakan isi nama klasifikasi grup',
    'required_group_name' => 'Silakan isi nama grup',
    'delete_group_category_error' => 'Ada kelompok dalam klasifikasi, tidak memungkinkan penghapusan',
    'delete_default_language_error' => 'Bahasa default tidak dapat dihapus',
    'account_connect_services_error' => 'Dukungan interkoneksi pihak ketiga memiliki platform yang saling terhubung berulang',
    'post_datetime_select_error' => 'Rentang tanggal pengaturan pos tidak boleh kosong',
    'post_datetime_select_range_error' => 'Tanggal akhir pengaturan pos tidak boleh kurang dari tanggal mulai',
    'comment_datetime_select_error' => 'Rentang tanggal yang ditetapkan oleh komentar tidak boleh kosong',
    'comment_datetime_select_range_error' => 'Tanggal akhir pengaturan komentar tidak boleh kurang dari tanggal mulai',
];
