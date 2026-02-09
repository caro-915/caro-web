# 📖 AutoDZ Developer Documentation

**START HERE!** Complete guide for setting up, developing, and deploying AutoDZ.

---

## 🚀 Quick Links

| Go To | Description | Time |
|-------|-------------|------|
| **[👨‍💻 Guide du Nouveau Développeur](docs_autodz/09_GUIDE_NOUVEAU_DEV.md)** | ← **START HERE** | 30 min |
| [⚡ Quick Start](QUICK_START.md) | Fast setup (15 min) | 15 min |
| [📊 Project Overview](docs_autodz/00_PRESENTATION_GENERALE.md) | What is AutoDZ? | 5 min |
| [🔧 Installation Guide](docs_autodz/01_INSTALLATION_ENVIRONNEMENT.md) | Setup from scratch | 15 min |
| [💾 Database Schema](docs_autodz/03_BASES_DE_DONNEES.md) | All 16 tables + relations | 15 min |
| [✨ Features Guide](docs_autodz/04_FONCTIONNALITES_DETAILLEES.md) | How features work | 20 min |
| [🔄 Git Workflow](docs_autodz/07_GIT_WORKFLOW.md) | How we branch/commit/deploy | 10 min |
| [🧪 Testing Guide](docs_autodz/08_TESTS_RECETTE.md) | Testing & QA procedures | 10 min |
| [🔒 Security Guide](docs_autodz/05_SECURITE.md) | Security best practices | 15 min |
| [📊 Monitoring Guide](docs_autodz/06_MONITORING_EXPLOITATION.md) | Production support | 10 min |

---

## 🎯 New Developer Quick Path (1 Hour)

1. **Clone & run** (15 min)
   ```bash
   cd c:\laragon\www
   git clone <repo> autodz
   cd autodz
   composer install && npm install
   cp .env.example .env
   php artisan key:generate
   php artisan migrate
   php artisan db:seed
   php artisan serve
   ```

2. **Read New Dev Guide** (30 min)
   - [👨‍💻 docs_autodz/09_GUIDE_NOUVEAU_DEV.md](docs_autodz/09_GUIDE_NOUVEAU_DEV.md)
   - Covers: Architecture, models, common tasks, security gotchas

3. **Make your first code change** (15 min)
   - Create feature branch: `git checkout -b feature/my-change`
   - Edit any file (small fix/improvement)
   - Commit: `git commit -m "✨ feat(scope): Change description"`
   - Push: `git push origin feature/my-change`
   - Open PR on GitHub

**After this:** You'll understand how to contribute!

---

## 🏗️ Project Structure at a Glance

```
📁 autodz/
├── app/
│   ├── Models/          ← Database models (User, Annonce, etc)
│   ├── Http/
│   │   ├── Controllers/ ← Request handlers
│   │   └── Requests/    ← Form validation
│   ├── Services/        ← Business logic
│   └── Jobs/            ← Async tasks
├── resources/
│   ├── views/           ← Blade templates
│   ├── js/              ← Alpine.js code
│   └── css/             ← Tailwind CSS
├── routes/
│   ├── web.php          ← Public URLs
│   └── api.php          ← Mobile app endpoints
├── database/
│   ├── migrations/      ← Schema changes
│   └── seeders/         ← Test data
├── tests/
│   ├── Feature/         ← Full feature tests
│   └── Unit/            ← Unit tests
├── docs_autodz/         ← All documentation
│   ├── 00_PRESENTATION_GENERALE.md
│   ├── 01_INSTALLATION_ENVIRONNEMENT.md
│   ├── 03_BASES_DE_DONNEES.md
│   ├── 04_FONCTIONNALITES_DETAILLEES.md
│   ├── 05_SECURITE.md
│   ├── 06_MONITORING_EXPLOITATION.md
│   ├── 07_GIT_WORKFLOW.md
│   ├── 08_TESTS_RECETTE.md
│   └── 09_GUIDE_NOUVEAU_DEV.md
└── ... (config, bootstrap, etc)
```

---

## 📚 Documentation Stack

### Getting Started (Read First)
- **09_GUIDE_NOUVEAU_DEV.md** - Your personal guide (30 min)
- **00_PRESENTATION_GENERALE.md** - What is AutoDZ (5 min)
- **01_INSTALLATION_ENVIRONNEMENT.md** - How to setup (15 min)

### Understanding the Code
- **03_BASES_DE_DONNEES.md** - Database schema + SQL examples
- **04_FONCTIONNALITES_DETAILLEES.md** - How features work (with code)
- **05_SECURITE.md** - How to code safely

### Working Together
- **07_GIT_WORKFLOW.md** - Branching strategy, commit format, deployment
- **08_TESTS_RECETTE.md** - How to test changes

### Production (Only When Needed)
- **06_MONITORING_EXPLOITATION.md** - Logs, backups, monitoring

### Future
- **10_ROADMAP.md** - What we're building next

---

## 🎯 Core Technologies

| Layer | Tech | Version |
|-------|------|---------|
| **Backend** | Laravel | 12 |
| **Framework** | Eloquent ORM | - |
| **Database** | SQLite (dev) / MySQL (prod) | - |
| **API Auth** | Sanctum | - |
| **Frontend** | Blade + Tailwind + Alpine | - |
| **Images** | Intervention Image | - |
| **Package Manager** | Composer | - |
| **Build Tool** | Vite | - |
| **Deployment** | Laravel Cloud | - |

---

## 🔗 Essential URLs

| Environment | URL | Credentials |
|-------------|-----|-------------|
| **Development** | http://localhost:8000 | admin@autodz.dz / password123 |
| **Production** | https://caro.laravel.cloud | Same admin account |
| **GitHub Repo** | [caro_bedro branch](docs_autodz/07_GIT_WORKFLOW.md) | Default branch (dev) |
| **GitHub Admin** | [main branch](docs_autodz/07_GIT_WORKFLOW.md) | Production only |

---

## 💻 Daily Developer Workflow

```bash
# 1. Start of day: get latest code
git checkout caro_bedro
git pull origin caro_bedro

# 2. Create feature branch for your task
git checkout -b feature/short-description

# 3. Make changes, commit with clear messages
git add app/Models/MyModel.php
git commit -m "✨ feat(scope): Add new field"

# 4. Run tests locally
php artisan test

# 5. Push to remote
git push origin feature/short-description

# 6. Create Pull Request on GitHub
# - Title: ✨ feat(scope): Description
# - Reference issue if applicable
# - Wait for approval

# 7. After approval, merge to caro_bedro
git checkout caro_bedro
git pull origin caro_bedro
git merge feature/short-description
git push origin caro_bedro

# 8. After 1 week of testing, merge to main (production)
git checkout main
git merge caro_bedro
git push origin main
```

**Full details:** [07_GIT_WORKFLOW.md](docs_autodz/07_GIT_WORKFLOW.md)

---

## 🧪 Testing Checklist

Before pushing to production:

```bash
# 1. Run tests
php artisan test --coverage

# 2. Check for errors
grep "ERROR\|CRITICAL" storage/logs/laravel.log

# 3. Test feature manually
# - Create test data
# - Use feature end-to-end
# - Check if images display
# - Check validation messages

# 4. Code review
# - Someone reads your code
# - Approves changes
# - Suggests improvements

# 5. PR merge
# - Click "Merge" on GitHub
# - Confirm deployment

# 6. Verify in production
curl -I https://caro.laravel.cloud  # Should be 200
```

**Test Guide:** [08_TESTS_RECETTE.md](docs_autodz/08_TESTS_RECETTE.md)

---

## 🔒 Security First Principles

**Always:**
- ✅ Validate user input
- ✅ Use parameter binding (not string concatenation)
- ✅ Hash passwords (`bcrypt`)
- ✅ Check user permissions (authorization)
- ✅ Log sensitive actions
- ✅ Use HTTPS (auto on Laravel Cloud)
- ✅ Never hardcode secrets (.env only)

**Never:**
- ❌ Trust user input
- ❌ Store passwords in plaintext
- ❌ Commit secrets to git
- ❌ Use `eval()` or `exec()`
- ❌ Allow SQL injection
- ❌ Store files publicly if sensitive
- ❌ Log passwords or tokens

**Read:** [05_SECURITE.md](docs_autodz/05_SECURITE.md)

---

## 🚀 Deploy to Production

**⚠️ ONLY AFTER APPROVAL!**

```bash
# 1. Ensure on caro_bedro, all tests pass
git checkout caro_bedro
php artisan test

# 2. Review changes one more time
git log --oneline --graph -10

# 3. Switch to main (production)
git checkout main
git pull origin main

# 4. Merge from caro_bedro
git merge caro_bedro

# 5. Push to production (triggers deployment)
git push origin main

# 6. Verify deployment
curl -I https://caro.laravel.cloud
# Should be 200 OK

# 7. Monitor for errors
# Check storage/logs/laravel.log for next 10 minutes
```

**Full deployment guide:** [06_MONITORING_EXPLOITATION.md](docs_autodz/06_MONITORING_EXPLOITATION.md)

---

## 🆘 Troubleshooting Quick Links

| Problem | Solution |
|---------|----------|
| "Database connection refused" | Run `php artisan migrate` |
| "Images showing 404" | Run `php artisan storage:link` |
| "Tests failing" | Run `php artisan test --filter=TestName` to debug |
| "Git merge conflict" | Read [07_GIT_WORKFLOW.md](docs_autodz/07_GIT_WORKFLOW.md#conflict-resolution) |
| "Google OAuth not working" | Check .env has `GOOGLE_CLIENT_ID` and `GOOGLE_CLIENT_SECRET` |
| "Can't login as admin" | Run `php artisan db:seed` to create test user |
| "Stuck on setup" | Read [01_INSTALLATION_ENVIRONNEMENT.md](docs_autodz/01_INSTALLATION_ENVIRONNEMENT.md) fully |

---

## 📞 Need Help?

1. **Check documentation first** - Most answers are in docs_autodz/
2. **Search codebase** - `grep -r "your search" app/`
3. **Use tinker** - `php artisan tinker` for interactive debugging
4. **Check logs** - `tail -50 storage/logs/laravel.log`
5. **Ask your tech lead** - Can help with architectural questions

---

## ✅ First Week Checklist

- [ ] Clone repo and run setup
- [ ] Read 09_GUIDE_NOUVEAU_DEV.md
- [ ] Create test annonce (verify feature works)
- [ ] Make first code change (small fix)
- [ ] Create feature branch and open PR
- [ ] Read 07_GIT_WORKFLOW.md (understand how we work)
- [ ] Run tests locally: `php artisan test`
- [ ] Read 03_BASES_DE_DONNEES.md (understand data)
- [ ] Read 04_FONCTIONNALITES_DETAILLEES.md (understand features)
- [ ] Ask for code review on your PR
- [ ] Merge your first PR to caro_bedro
- [ ] Celebrate! 🎉

---

## 🎓 Learning Path (By Role)

### 👨‍💻 Backend Developer
1. 09_GUIDE_NOUVEAU_DEV.md (25 min)
2. 01_INSTALLATION_ENVIRONNEMENT.md (15 min)
3. 03_BASES_DE_DONNEES.md (15 min)
4. 04_FONCTIONNALITES_DETAILLEES.md (20 min)
5. 07_GIT_WORKFLOW.md (10 min)
6. 05_SECURITE.md (15 min)

### 🎨 Frontend Developer
1. 09_GUIDE_NOUVEAU_DEV.md (25 min)
2. 01_INSTALLATION_ENVIRONNEMENT.md (15 min)
3. 04_FONCTIONNALITES_DETAILLEES.md (focus on UI parts) (20 min)
4. 07_GIT_WORKFLOW.md (10 min)

### 🚀 DevOps / Tech Lead
1. 00_PRESENTATION_GENERALE.md (5 min)
2. 01_INSTALLATION_ENVIRONNEMENT.md (15 min)
3. 06_MONITORING_EXPLOITATION.md (10 min)
4. 07_GIT_WORKFLOW.md (10 min)
5. 05_SECURITE.md (15 min)

### 🧪 QA / Test Engineer
1. 09_GUIDE_NOUVEAU_DEV.md (basic understanding) (25 min)
2. 04_FONCTIONNALITES_DETAILLEES.md (feature details) (20 min)
3. 08_TESTS_RECETTE.md (test procedures) (10 min)
4. 07_GIT_WORKFLOW.md (basic git) (10 min)

---

## 📊 Project Statistics

| Metric | Value |
|--------|-------|
| Total Models | 12 (User, Annonce, Message, etc) |
| Total Database Tables | 16 |
| API Endpoints | 20+ |
| Views (Blade templates) | 30+ |
| Lines of code (App) | ~5000 |
| Test cases | 50+ |
| Documentation pages | 10 |

---

## 📅 Support & Escalation

| Issue Type | Contact | Response Time |
|-----------|---------|----------------|
| Code review | Tech lead | 1-2 hours |
| Bug in production | On-call engineer | 15 min |
| Architecture question | Tech lead | Same day |
| Deployment help | DevOps | 30 min |
| Security question | Tech lead | Same day |

---

## 🎯 Your Mission

Build features that delight users while keeping code clean, secure, and maintainable.

**Questions?** Start with [09_GUIDE_NOUVEAU_DEV.md](docs_autodz/09_GUIDE_NOUVEAU_DEV.md).

Welcome to the team! 🚀

---

**Last updated:** February 2026  
**Version:** 2.1.0  
**Git:** [caro_bedro branch (dev)](docs_autodz/07_GIT_WORKFLOW.md) | [main branch (prod)](docs_autodz/07_GIT_WORKFLOW.md)

