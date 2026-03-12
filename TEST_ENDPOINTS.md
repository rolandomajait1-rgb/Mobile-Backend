# Backend API Endpoint Tests

Base URL: `https://mobile-backend-84tg.onrender.com`

## Public Endpoints (No Auth Required)

### 1. Health Check
```bash
curl https://mobile-backend-84tg.onrender.com/up
```
**Expected:** HTML page with "Application up"

### 2. Latest Articles
```bash
curl https://mobile-backend-84tg.onrender.com/api/latest-articles
```
**Expected:** JSON array of articles (may be empty if no data)

### 3. Latest Articles with Limit
```bash
curl https://mobile-backend-84tg.onrender.com/api/latest-articles?limit=5
```
**Expected:** JSON array with max 5 articles

### 4. Public Articles
```bash
curl https://mobile-backend-84tg.onrender.com/api/articles/public?latest=true&limit=9
```
**Expected:** JSON array with max 9 articles

### 5. Category Articles - News
```bash
curl https://mobile-backend-84tg.onrender.com/api/categories/news/articles
```
**Expected:** JSON array of news articles

### 6. Category Articles - Sports
```bash
curl https://mobile-backend-84tg.onrender.com/api/categories/sports/articles
```
**Expected:** JSON array of sports articles

### 7. Category Articles - Opinion
```bash
curl https://mobile-backend-84tg.onrender.com/api/categories/opinion/articles
```
**Expected:** JSON array of opinion articles

### 8. Category Articles - Literary
```bash
curl https://mobile-backend-84tg.onrender.com/api/categories/literary/articles
```
**Expected:** JSON array of literary articles

### 9. Category Articles - Art
```bash
curl https://mobile-backend-84tg.onrender.com/api/categories/art/articles
```
**Expected:** JSON array of art articles

### 10. Category Articles - Features
```bash
curl https://mobile-backend-84tg.onrender.com/api/categories/features/articles
```
**Expected:** JSON array of features articles

### 11. Category Articles - Specials
```bash
curl https://mobile-backend-84tg.onrender.com/api/categories/specials/articles
```
**Expected:** JSON array of specials articles

---

## Protected Endpoints (Require Auth Token)

### 12. Register User
```bash
curl -X POST https://mobile-backend-84tg.onrender.com/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User",
    "email": "test@laverdad.edu.ph",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```
**Expected:** JSON with user data and token

### 13. Login
```bash
curl -X POST https://mobile-backend-84tg.onrender.com/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@laverdad.edu.ph",
    "password": "password123"
  }'
```
**Expected:** JSON with user data and token

### 14. Get User Details (with token)
```bash
curl https://mobile-backend-84tg.onrender.com/api/user \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```
**Expected:** JSON with user data

### 15. Get Categories (with token)
```bash
curl https://mobile-backend-84tg.onrender.com/api/categories \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```
**Expected:** JSON array of categories

### 16. Get Articles (with token)
```bash
curl https://mobile-backend-84tg.onrender.com/api/articles \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```
**Expected:** JSON array of all articles (including drafts)

---

## Test Results

Run each endpoint and record results:

| # | Endpoint | Status | Response |
|---|----------|--------|----------|
| 1 | /up | ⏳ | |
| 2 | /api/latest-articles | ⏳ | |
| 3 | /api/latest-articles?limit=5 | ⏳ | |
| 4 | /api/articles/public | ⏳ | |
| 5 | /api/categories/news/articles | ⏳ | |
| 6 | /api/categories/sports/articles | ⏳ | |
| 7 | /api/categories/opinion/articles | ⏳ | |
| 8 | /api/categories/literary/articles | ⏳ | |
| 9 | /api/categories/art/articles | ⏳ | |
| 10 | /api/categories/features/articles | ⏳ | |
| 11 | /api/categories/specials/articles | ⏳ | |
| 12 | POST /api/register | ⏳ | |
| 13 | POST /api/login | ⏳ | |
| 14 | /api/user | ⏳ | |
| 15 | /api/categories | ⏳ | |
| 16 | /api/articles | ⏳ | |

---

## Quick Test Script

Copy-paste this in PowerShell to test all public endpoints:

```powershell
$base = "https://mobile-backend-84tg.onrender.com"

Write-Host "Testing Backend Endpoints..." -ForegroundColor Cyan

# Test 1: Health
Write-Host "`n1. Health Check:" -ForegroundColor Yellow
curl "$base/up" -UseBasicParsing | Select-Object -ExpandProperty StatusCode

# Test 2: Latest Articles
Write-Host "`n2. Latest Articles:" -ForegroundColor Yellow
curl "$base/api/latest-articles" -UseBasicParsing | Select-Object StatusCode, Content

# Test 3: Public Articles
Write-Host "`n3. Public Articles:" -ForegroundColor Yellow
curl "$base/api/articles/public?latest=true&limit=9" -UseBasicParsing | Select-Object StatusCode, Content

# Test 4-10: Category Articles
$categories = @("news", "sports", "opinion", "literary", "art", "features", "specials")
foreach ($cat in $categories) {
    Write-Host "`n$cat Articles:" -ForegroundColor Yellow
    curl "$base/api/categories/$cat/articles" -UseBasicParsing | Select-Object StatusCode
}

Write-Host "`nDone!" -ForegroundColor Green
```

---

## Notes

- All public endpoints should return 200 OK (even if empty array)
- Protected endpoints without token should return 401 Unauthorized
- 404 errors mean route doesn't exist
- 500 errors mean server crash (check Render logs)
- Empty arrays `[]` are OK if no data in database yet
