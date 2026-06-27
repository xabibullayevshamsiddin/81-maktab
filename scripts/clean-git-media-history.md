# Git tarixidan media fayllarni tozalash

`public/storage/` dagi fayllar git tarixida (~58 MB) qolgan bo'lishi mumkin.
Index tozalanganidan keyin tarixni qisqartirish uchun `git filter-repo` ishlating.

## Talablar

```bash
pip install git-filter-repo
```

## Buyruqlar (backup oling!)

```bash
git clone --mirror . ../81-maktab-backup.git
git filter-repo --path public/storage/ --invert-paths --force
git remote add origin <REMOTE_URL>
git push --force --all
git push --force --tags
```

## Index tozalash (allaqachon qilingan bo'lishi kerak)

```bash
git rm -r --cached public/storage
git rm --cached scratch/cleanup_grades.php
git rm --cached public/panel-assets/**/.DS_Store
git rm --cached .agent/skills/**/__pycache__/*.pyc
php artisan storage:link
```

`public/storage/.gitignore` fayli saqlanadi; media faqat `storage/app/public` da bo'ladi.
