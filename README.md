# helboregnskab.dk

Hjemmesiden for Helbo Regnskab ApS.

- Alle filer til siden ligger i `public_html/`.
- Når der kommer en ændring på `main`, lægger GitHub Actions de ændrede filer op hos Simply.com automatisk (se `.github/workflows/deploy.yml`).
- FTP-oplysningerne ligger som secrets i repoet: `FTP_SERVER`, `FTP_USERNAME`, `FTP_PASSWORD`.
- Kontaktformularen sender via `public_html/send.php` til kontakt@helboregnskab.dk.
