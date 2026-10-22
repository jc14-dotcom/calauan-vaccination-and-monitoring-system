# Public Repository Sanitization and Publication Plan

## Objective

Prepare `C:\laragon\www\infantsSystem-public` as a safe, sanitized source snapshot for a new GitHub repository with a completely new Git history.

The original private repository and its history must remain private and unchanged. This plan assumes that the copied directory may contain:

- A copied `.git` directory and remote-tracking history
- Environment files and local runtime data
- Production or test database exports
- Firebase, VAPID, SMS, mail, or GitHub credentials
- Archived documentation containing old credentials
- Personal, patient, account, session, token, or activity data
- Deployment workflows tied to the old private repository

This plan intentionally separates **removing repository connectivity**, **sanitizing the working tree**, and **removing old history**. Changing only the Git remote is not sufficient.

## Safety principles

1. Do not make the original repository public.
2. Do not rewrite or delete anything in the original private repository as part of this process.
3. Do not push the copied directory until secret and personal-data reviews pass.
4. Treat every credential found in current files or old history as compromised.
5. Do not commit replacement credentials. Use placeholders and deployment secrets.
6. Keep the new GitHub repository private until the online review is complete.
7. Make backups outside both repositories before deleting or removing anything.
8. Prefer omitting uncertain files over publishing them.

## Phase 0: Freeze and document the starting state

Open PowerShell and work only in the copied directory:

```powershell
Set-Location C:\laragon\www\infantsSystem-public

git status --short --branch
git remote -v
git branch --all
git tag --list
git log --oneline --all -20
```

Record the output in a private note if needed, but do not add an audit note containing secrets to the repository.

Confirm that the original private checkout is a different directory:

```powershell
Test-Path C:\laragon\www\infantsSystem\.git
Test-Path C:\laragon\www\infantsSystem-public\.git
```

Do not run destructive commands until the path is confirmed.

## Phase 1: Make a safe backup and remove local-only runtime material

Create a backup outside the copied repository, using an approved private location. Do not include that backup in the new repository.

At minimum, keep the following outside the public workspace:

- `.env` and all environment variants
- Firebase service-account JSON files
- Private keys and certificates
- Local SQLite databases
- `storage\app\firebase\`
- `storage\app\public\` if it contains uploaded records or images
- `storage\logs\`
- `storage\framework\`
- `bootstrap\cache\`
- `.phase0-backups\`
- `node_modules\` and `vendor\`

Do not copy production data into the backup unless it is required for a separate private retention policy.

## Phase 2: Audit the complete working tree

Inspect the complete file list, including hidden files:

```powershell
Get-ChildItem -Force -Recurse -File |
    Where-Object { $_.FullName -notmatch '\\(node_modules|vendor|\.git)\\' } |
    Select-Object -ExpandProperty FullName
```

Pay particular attention to:

- `.env`, `.env.*`, `.env.backup`, and deployment-specific environment files
- `*.json`, `*.sql`, `*.csv`, `*.log`, `*.dump`, `*.zip`, and `*.bak`
- `*.pem`, `*.key`, `*.p12`, `*.pfx`, and certificate files
- Firebase and Google service-account files
- `storage\`, `database\exports\`, `_archive\`, `test_files\`, and backups
- CI/CD workflows and deployment scripts
- JavaScript bundles, service workers, and generated frontend files
- Documentation that reproduces credential values or private infrastructure details

Use targeted scans that print matching lines only for review and never paste secret values into chat:

```powershell
git grep -n -I -E `
    "PRIVATE KEY|BEGIN RSA|BEGIN OPENSSH|BEGIN EC|AIza|AKIA[0-9A-Z]{16}|"`
    "API_KEY|SECRET|TOKEN|PASSWORD|VAPID_PRIVATE_KEY|CLIENT_SECRET|"`
    "DATABASE_URL|MAIL_PASSWORD|FCM_CREDENTIALS"
```

Also scan untracked files before they are staged:

```powershell
Get-ChildItem -Force -Recurse -File |
    Where-Object { $_.FullName -notmatch '\\(node_modules|vendor|\.git)\\' } |
    Select-String -Pattern `
      'PRIVATE KEY|BEGIN RSA|BEGIN OPENSSH|AIza|AKIA[0-9A-Z]{16}|'
      'API_KEY|SECRET|TOKEN|PASSWORD|VAPID_PRIVATE_KEY|CLIENT_SECRET' `
      -CaseSensitive:$false
```

Review every match. A variable name in a configuration example may be safe; a value that authenticates to a service is not.

## Phase 3: Revoke and rotate exposed credentials

Before publication, revoke or regenerate every credential found in the copied files or the original history:

- Firebase service-account keys
- Firebase API keys if exposed beyond the intended restricted browser use
- VAPID public/private key pairs
- Semaphore or other SMS API keys
- SMTP credentials and Google app passwords
- AWS, cloud-storage, database, SSH, and deployment credentials
- GitHub personal-access tokens or workflow tokens
- Session tokens, password-reset tokens, and FCM registration tokens if real data was exposed

Restrict replacement browser keys by project, API, referrer, and API scope where supported. Store server credentials only in the deployment provider's secret store or environment variables.

Rotation is required even if a credential is later removed from the working tree, because the old Git history may still contain it.

## Phase 4: Define the public repository boundary

The public repository should contain source code, migrations that contain schema only, safe configuration examples, tests with synthetic data, and documentation that does not disclose secrets or personal data.

Exclude:

- `.env` and all environment variants except a reviewed `.env.example`
- Firebase service-account JSON and private keys
- Production database dumps and exports
- Patient, parent, child, health-worker, account, session, token, SMS, or activity-log data
- Uploaded files and generated runtime files
- Old backups and obsolete secret-bearing archives
- Real credentials copied into documentation or test fixtures
- Hostinger paths, passwords, SSH details, and private deployment topology unless intentionally generalized

Files requiring review in the current copy include:

- `public\sw.js` because it contains Firebase browser configuration
- `database\exports\infantssystem-clean.sql`
- `_archive\test_files\vapid_keys_clean.txt`
- `_archive\documentation\GOOGLE_APP_PASSWORD_GUIDE.md`
- `_archive\documentation\FCM_SETUP_STEPS.md`
- `_archive\documentation\NOTIFICATION_ALTERNATIVES_ANALYSIS.md`
- `.github\workflows\deploy.yml`

Delete or omit a file when its purpose is obsolete or its contents cannot be reliably sanitized.

## Phase 5: Strengthen ignore rules and examples

Update `.gitignore` to cover all local secrets and generated data used by this application:

```gitignore
.env
.env.*
!.env.example

storage/app/firebase/
storage/app/public/
storage/logs/
bootstrap/cache/
database/*.sqlite
database/exports/
.phase0-backups/

*.pem
*.key
*.p12
*.pfx
*.jks
```

Review the application layout before adding or changing an ignore rule. Do not ignore source files merely because they are inconvenient to review.

Review `.env.example` and retain only empty values or unmistakable placeholders:

```dotenv
APP_KEY=
DB_PASSWORD=
SEMAPHORE_API_KEY=
VAPID_PUBLIC_KEY=
VAPID_PRIVATE_KEY=
FCM_API_KEY=
FCM_APP_ID=
FCM_CREDENTIALS_PATH=
```

Keep safe defaults only when they do not identify private infrastructure or grant access.

## Phase 6: Sanitize source, frontend configuration, documentation, and tests

### Application configuration

Ensure server-side configuration reads credentials from environment variables, for example:

- `config\services.php`
- `config\sms.php`
- mail and database configuration
- notification and Firebase services

Do not put fallback credential values into PHP, JavaScript, Blade templates, workflow files, or documentation.

### Frontend and service worker

Remove real Firebase configuration values from `public\sw.js` and any generated frontend source. Use placeholders or a documented build-time environment mechanism. Browser-visible Firebase values are not service-account secrets, but they must be restricted and should not identify an unintended production project in a public sample.

### Documentation

Rewrite setup instructions to use placeholders such as:

```text
YOUR_FIREBASE_PROJECT_ID
YOUR_SEMAPHORE_API_KEY
YOUR_VAPID_PRIVATE_KEY
```

Remove real email addresses, phone numbers, usernames, server paths, hostnames, database names, and deployment tokens unless they are deliberately public and generic.

### Tests and fixtures

Use fictional names, synthetic phone numbers, fake email addresses, and non-reusable test tokens. Remove fixtures that contain production records or hashes derived from real accounts.

## Phase 7: Remove the old Git history from the copied directory

Only after the working tree has been sanitized and backed up, remove the copied repository metadata:

```powershell
Set-Location C:\laragon\www\infantsSystem-public
Remove-Item -Recurse -Force .git

git init
git branch -M main
git remote -v
```

The final command must print no remotes. This operation affects only `C:\laragon\www\infantsSystem-public`; it does not alter `C:\laragon\www\infantsSystem`.

Do not copy old branches, tags, reflogs, or remote-tracking references into the new repository.

## Phase 8: Stage and review the new snapshot

Before staging, verify that ignored secrets are not accidentally forced:

```powershell
git status --short --ignored
git add .
git diff --cached --name-status
git diff --cached --stat
```

Review the staged file names for:

- `.env` or environment variants
- service-account JSON
- private-key and certificate files
- SQL exports or real data files
- backups and archives
- logs and generated runtime content
- real credential-bearing documentation

Inspect staged text without printing sensitive values broadly:

```powershell
git grep -n -I -E `
  "PRIVATE KEY|BEGIN RSA|BEGIN OPENSSH|AIza|API_KEY=|SECRET=|TOKEN=|"`
  "PASSWORD=|VAPID_PRIVATE_KEY|private_key|client_secret"
```

Investigate every result and unstage or sanitize anything unsafe:

```powershell
git reset -- <path>
```

Do not use `git add -f` for a secret or data file.

## Phase 9: Validate the application snapshot

Run the smallest project-appropriate checks after sanitization:

```powershell
composer validate --no-check-publish
php artisan config:clear
php artisan route:list
php artisan test
npm run build
```

If a command requires private services or production credentials, do not add those credentials to the repository. Use a local test environment with placeholders or document the unavailable external dependency.

Check that the application fails explicitly and safely when optional credentials are absent. It must not silently use a production credential or a success-shaped fallback.

## Phase 10: Create and inspect the clean initial commit

After the staged review and validation:

```powershell
git commit -m "Initial public release"
git log --oneline --all
git branch --all
git tag --list
git fsck --full --no-reflogs --unreachable
```

The new repository should have one initial commit, no tags, no old branches, and no unexpected unreachable objects.

Do not run `git filter-branch` or history-rewrite tools as a substitute for removing the copied `.git` directory. A fresh initialization is simpler and safer for this separate public snapshot.

## Phase 11: Publish privately for an online review

Create a new empty GitHub repository. Set it to **private** initially. Do not initialize it with a README, license, `.gitignore`, or imported history.

Add only the new remote:

```powershell
git remote add origin https://github.com/YOUR_ACCOUNT/YOUR_NEW_REPOSITORY.git
git remote -v
git push -u origin main
```

Confirm the URL is the new repository before pushing. Never use the old private repository URL or an access token embedded in the URL.

Review the private GitHub repository:

- Repository file tree
- Commit and branch list
- Tags and releases
- Actions workflows and logs
- Secret-scanning alerts
- Dependency and code-scanning alerts
- Downloadable artifacts
- README and documentation

If a secret is detected, treat it as exposed, rotate it, remove it from the public snapshot, and create a replacement clean history before making the repository public.

## Phase 12: Final publication decision

Make the repository public only when every item below is true:

- [ ] The original repository remains private.
- [ ] The copied directory has no connection to the old remote.
- [ ] The copied directory has no old `.git` history.
- [ ] The new repository has only the intended `main` branch.
- [ ] No `.env` or environment file with values is tracked.
- [ ] No service-account JSON, private key, certificate, or token is tracked.
- [ ] No real patient, account, session, token, SMS, or activity data is tracked.
- [ ] No production SQL export is tracked.
- [ ] Archived documentation has been reviewed or omitted.
- [ ] `public\sw.js` and generated frontend files contain no unintended production configuration.
- [ ] `.env.example` contains placeholders only.
- [ ] Deployment workflows use public-safe configuration and GitHub secrets.
- [ ] All exposed credentials have been revoked or rotated.
- [ ] Secret scanning has no unresolved alerts.
- [ ] Tests and build checks pass or their external limitations are documented.
- [ ] The private GitHub review found no unsafe files, branches, tags, releases, or artifacts.

## If a mistake is discovered after pushing

1. Immediately set the GitHub repository back to private.
2. Revoke or rotate the affected credential.
3. Remove the unsafe file or value from the working tree.
4. Remove the copied `.git` directory again.
5. Reinitialize and recreate the clean initial commit.
6. Force-pushing a rewritten public history is not a substitute for credential rotation.
7. Review GitHub Actions artifacts, caches, forks, clones, and downloads as potentially exposed.

## Definition of done

The copied system is ready for public release only when it is an independently initialized repository containing reviewed source code and safe examples, with no old Git objects, secrets, private data, production exports, or private infrastructure credentials.
