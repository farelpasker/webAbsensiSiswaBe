# Attendance Reminder System

## Apa itu?

Sistem otomatis yang mengirim notifikasi WhatsApp ke siswa & orang tua **setiap hari jam 07:55** (5 menit sebelum batas telat jam 08:00).

Notifikasi hanya dikirim ke siswa yang **belum melakukan absen**.

---

## Setup

### 1. Pastikan WhatsApp Token di .env

```
FONNTE_TOKEN=your_token_here
```

### 2. Pastikan User punya nomor

- Field `phone` di tabel `users` harus terisi
- Misal: `+628123456789`

### 3. Pastikan Parent terlink

- Student harus punya `parent_id` yang valid
- Parent adalah User lain dengan nomor yang terisi

---

## Testing

### Manual Test

```bash
php artisan app:send-attendance-reminder
```

Output jika ada siswa yang belum absen:

```
Mengirim notifikasi ke 2 siswa...
  ✓ Siswa (Ahmad)
  ✓ Ortu (Bu Lis)
✓ Selesai
```

---

## Menjalankan Otomatis

### Development

```bash
php artisan schedule:work
```

### Production (Crontab)

Tambahkan ke crontab:

```bash
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

---

## Pesan yang Dikirim

**Ke Siswa:**

```
Halo {nama}, kamu punya 5 menit lagi untuk absen sebelum dianggap telat! ⏰
Segera lakukan absen. 📱
```

**Ke Orang Tua:**

```
Halo {nama_ortu}, anak Anda {nama_siswa} belum absen. Waktu absen tutup dalam 5
menit, segera suruh absen atau dianggap telat! 👨‍👩‍👧
```

---

## Log

Setiap kali command jalan, log tersimpan di:

```
storage/logs/attendance-reminder.log
```

Bisa dilihat dengan:

```bash
tail -f storage/logs/attendance-reminder.log
```

---

## Files yang Dibuat

1. `app/Console/Commands/SendAttendanceReminder.php` - Command utama
2. `app/Console/Kernel.php` - Scheduler (jam 07:55 setiap hari)

---

## Catatan

- Command hanya jalan **jam 07:55** (tidak setiap menit)
- Otomatis cek ulang besok hari
- Gunakan `withoutOverlapping()` untuk cegah duplikasi
- Log bisa di-monitor real-time
