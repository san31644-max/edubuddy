# EduBuddy Sri Lanka

Mobile-first PHP/MySQL Grade 6–10 learning application with English, Sinhala and Tamil interfaces. The seeded lessons are **sample curriculum content**, not an official complete Sri Lankan government syllabus.

## Grade 10 curriculum

After MySQL is writable, create Grade 10 and its core multilingual subjects:

```powershell
C:\xampp\php\php.exe database\add_grade10.php
```

Download official Grade 10 resources from the Sinhala, Tamil and English e-Thaksalawa categories, then import their searchable textbook chunks:

```powershell
py scripts\download_curriculum.py --grade 10
C:\xampp\php\php.exe database\import_curriculum.php --grade=10
```

Use **Admin → Catalog** to create Grade 10 units and textbook lessons for the downloaded books. Set each lesson medium to `Sinhala`, `Tamil`, `English`, or `All`. Then use **Admin → Curriculum** and **Assessments** to import lesson notes, papers, quizzes and Practice Lab questions.

## 1. Requirements

Install XAMPP with Apache, MySQL and PHP 8.0 or newer. Enable PHP extensions `mysqli`, `mbstring`, `fileinfo` and `curl` (curl is needed only for a future AI API).

## 2. Project location

The project must remain at `C:\xampp\htdocs\educhat`.

## 3. Start services

Open XAMPP Control Panel and start Apache and MySQL. Resolve port conflicts before continuing.

## 4. Import the database

Open `http://localhost/phpmyadmin`, choose Import, and import `C:\xampp\htdocs\educhat\database\educhat.sql`. The script creates and selects the `educhat` database and inserts Grade 6 multilingual demonstration data.

## 5. Database configuration

Edit `includes/config.php` only if your MySQL host, database name, username or password differs. Never commit production passwords or API keys. XAMPP's common local defaults are already present: user `root` and an empty password.

## 6. Open the project

Visit `http://localhost/educhat/`. Select a language, register a student, and log in. There is deliberately no seeded student password; registration is the test-login setup.

## 7. Create the first admin

Generate a hash (replace the example password):

```powershell
C:\xampp\php\php.exe -r "echo password_hash('Choose-A-Strong-Password', PASSWORD_DEFAULT), PHP_EOL;"
```

Copy the hash and run this in phpMyAdmin SQL, substituting the hash:

```sql
INSERT INTO educhat.admins(full_name,username,email,password_hash,role)
VALUES ('Administrator','admin','admin@example.com','PASTE_HASH_HERE','super_admin');
```

Then visit `http://localhost/educhat/admin/login.php`.

## 8. Configure AI later

Run `powershell -ExecutionPolicy Bypass -File C:\xampp\htdocs\educhat\setup-openai.ps1`. Enter the key only in the secure local prompt. Completely exit and reopen XAMPP Control Panel afterward, then restart Apache. EduBuddy reads `OPENAI_API_KEY`, `OPENAI_API_ENDPOINT`, and `OPENAI_MODEL` from the server environment and uses the OpenAI Responses API. With no key, the tutor safely uses stored lesson summaries and examples.

## 9. Test all languages

Start at `index.php` and test English, සිංහල and தமிழ். Registration stores the preferred language; profile changes it later. Use a UTF-8 database connection and UTF-8 editor.

## 10. Install the PWA on Android

Serve the production site over HTTPS, open it in Chrome on Android, use **Add to Home screen / Install app**, and confirm the EduBuddy icon. Localhost is accepted for development. The service worker caches public shell pages only; authenticated pages, chat messages, API responses and student data are not cached.

## 11. Namecheap shared hosting

Upload the project to `public_html` (adjust `APP_URL` and manifest/service-worker paths if it is not under `/educhat`), create a MySQL database/user in cPanel, import the SQL, update database settings, enable HTTPS, protect configuration, and select PHP 8+. Verify upload directory permissions and cron/expiry handling if later added.

## 12. Subscription plan

Premium costs LKR 250 for 30 days. Free students can browse subjects/sample lessons, attempt one quiz, and send five tutor messages per day. Premium unlocks unlimited tutor usage, quiz attempts, answer review and expanded progress use. The initial payment flow accepts a bank/cash reference and requires admin verification; configure real payment instructions before launch. It does not claim payment succeeded until an administrator approves it. Add a certified Sri Lankan payment gateway later using server-side signed callbacks and transaction verification.

## 13. Current limitations

- Demonstration content covers 12 Mathematics, Science and ICT lessons; it must be replaced/expanded using verified curriculum sources.
- Admin catalog creation is intentionally compact; multilingual quiz questions can currently be seeded/imported in `quiz_questions` with phpMyAdmin.
- No live payment gateway or automated refund/receipt process is included.
- AI endpoint compatibility may require adapting the response parser for the chosen provider.
- Learning streak is structurally supported but not scheduled/recalculated by a cron job yet.
- The SVG PWA icon is a placeholder; create 192×192 and 512×512 PNG production icons for widest install compatibility.

## 14. Add Grades 7–11

Insert each grade in `grades`; then add subjects with its `grade_id`, followed by units, lessons, quizzes and questions. Student and admin queries are grade-keyed, so no main-system rewrite is required. Update registration to expose newly activated grades after their verified content is ready.

## Security notes

Prepared MySQLi statements, password hashing, session ID regeneration, CSRF checks, authentication/role guards, escaped output, throttled login attempts, chat limits and validated image uploads are included. For production, move secrets to environment variables, use HTTPS, set restrictive filesystem permissions, add persistent IP/account rate limiting, malware scanning, audit logs, backups, and a Content Security Policy.

## Automatic Contabo deployment

Pushes to `main` run `.github/workflows/deploy.yml`. Configure these GitHub
Actions environment secrets under the `production` environment:

- `SERVER_HOST`: the Contabo IP address or hostname
- `SERVER_USER`: a restricted deployment user
- `SERVER_PORT`: normally `22`
- `REMOTE_PATH`: the absolute website document root
- `SSH_PRIVATE_KEY`: the deployment user's private key
- `SERVER_HOST_KEY`: the server's complete `ssh-keyscan` output

The deployment checks PHP syntax and synchronizes the application using SSH.
It deliberately preserves the production `includes/config.php`, all uploaded
files, and backups. Database schema changes must be applied as reviewed migrations;
the deployment never imports `database/educhat.sql` automatically.
