Berikut **konsep project aplikasi manajemen tugas + jadwal kuliah** berdasarkan tampilan SIAKAD yang kamu kirim. Konsep ini cocok untuk **project skripsi / portfolio / project kampus** karena cukup kompleks tapi tetap realistis dibuat.

---

# Konsep Project

# 📚 Smart Student Task & Schedule Manager

Aplikasi untuk membantu mahasiswa **mengelola tugas, jadwal kuliah, deadline, dan aktivitas akademik** dalam satu dashboard.

Terintegrasi dengan **jadwal kuliah seperti pada SIAKAD** dan membantu mahasiswa mengatur prioritas tugas.

---

# 1. Tujuan Aplikasi

Membantu mahasiswa agar:

* Tidak lupa tugas
* Melihat jadwal kuliah dengan mudah
* Mengatur deadline
* Mengelola aktivitas akademik
* Mengingatkan jadwal kelas

---

# 2. Fitur Utama

## 1️⃣ Dashboard

Menampilkan ringkasan:

* Jadwal hari ini
* Tugas yang belum selesai
* Deadline terdekat
* Statistik tugas

Contoh:

```
Hari ini:
08:00 - Statistik & Probabilitas
10:00 - Matematika Diskrit

Deadline:
Deep Learning Assignment - Besok
Robotika Project - 3 hari lagi
```

---

# 2️⃣ Jadwal Kuliah

Menampilkan jadwal seperti pada SIAKAD.

Data:

```
Mata Kuliah
Hari
Jam
Ruangan
Dosen
```

Tampilan bisa berupa:

### Table

| Mata Kuliah | Hari  | Jam   |
| ----------- | ----- | ----- |
| Statistik   | Senin | 07:30 |
| Matdis      | Senin | 09:20 |
| AI          | Senin | 13:40 |

atau

### Calendar View

---

# 3️⃣ Manajemen Tugas

Mahasiswa bisa menambahkan tugas dari mata kuliah.

Contoh:

| Tugas            | Mata Kuliah       | Deadline | Status   |
| ---------------- | ----------------- | -------- | -------- |
| Makalah AI       | Kecerdasan Buatan | 12 Mei   | Belum    |
| Project Robotika | Robotika          | 20 Mei   | Progress |

---

# 4️⃣ Reminder Deadline

Sistem akan memberi notifikasi jika:

* Deadline 3 hari lagi
* Deadline besok
* Deadline hari ini

Contoh:

```
⚠ Deadline Deep Learning besok!
```

---

# 5️⃣ Kalender Akademik

Menampilkan:

* Jadwal kuliah
* Deadline tugas
* UTS
* UAS

---

# 6️⃣ Progress Tugas

Tracking progress tugas.

Contoh:

```
Deep Learning Project

[#####-----] 50%
```

---

# 7️⃣ Integrasi Jadwal dari SIAKAD (Opsional)

Bisa:

* Import manual
* Upload CSV
* Web scraping SIAKAD

---

# 3. Role User

### Mahasiswa

Bisa:

* Melihat jadwal
* Menambahkan tugas
* Mengatur deadline
* Mengatur reminder

---

# 4. Struktur Database

### User

```
id
nama
nim
email
password
```

---

### MataKuliah

```
id
kode
nama
dosen
ruangan
hari
jam_mulai
jam_selesai
```

---

### Tugas

```
id
user_id
matakuliah_id
judul
deskripsi
deadline
status
progress
```

---

### Reminder

```
id
tugas_id
tanggal_notifikasi
status
```

---

# 5. UI/UX Halaman

### Login

```
NIM
Password
```

---

### Dashboard

```
Total Tugas
Tugas Selesai
Deadline Terdekat
Jadwal Hari Ini
```

---

### Jadwal Kuliah

```
Filter Semester
Table Jadwal
Calendar View
```

---

### Tugas

```
Tambah Tugas
Edit
Delete
Status
```

---

# 6. Teknologi yang Bisa Dipakai

### Backend

* Node.js + Express
  atau
* Laravel

---

### Frontend

* React
  atau
* Vue

---

### Database

* MySQL
  atau
* PostgreSQL

---

### Mobile (opsional)

* Flutter

---

# 7. Fitur Tambahan (Supaya Project Lebih Keren)

### Notifikasi

Email / push notification

---

###  Statistik

```
Total tugas
Tugas selesai
Produktivitas minggu ini
```

---

###  AI Assistant (opsional)

Contoh:

```
AI: Tugas apa yang harus saya kerjakan hari ini?
```

---

### Smart Priority

Sistem menentukan prioritas tugas.

---

# 8. Struktur Menu

```
Dashboard
Jadwal Kuliah
Tugas
Kalender
Statistik
Pengaturan
```

---

# 9. Flow Sistem

```
Login
 ↓
Dashboard
 ↓
Import Jadwal
 ↓
Tambah Tugas
 ↓
Sistem memberi reminder
 ↓
User menyelesaikan tugas
```

---
