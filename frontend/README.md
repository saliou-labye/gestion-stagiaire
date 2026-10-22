# Interncentral — Frontend (Nuxt 3)

Interface de gestion des stagiaires, encadreurs, services, affectations et évaluations.
Elle consomme l'API REST Laravel (authentification par jeton Sanctum) : aucun accès direct à PostgreSQL.

Palette : `#091F5C` `#334DAF` `#7096D1` `#D0E4FE` `#E8F2FE` `#F9FBFF` (variables CSS dans `assets/css/main.css`).

## Prérequis
- Node.js 18+ (20 recommandé) et npm
- Le backend Laravel lancé (`php artisan serve` → http://localhost:8000)

## Installation
```bash
cd frontend
cp .env.example .env        # ajuster NUXT_PUBLIC_API_BASE si besoin
npm install
npm run dev                 # http://localhost:3000
```
> L'origine `http://localhost:3000` est déjà autorisée dans `backend/config/cors.php`.

## Backend : mise en route rapide
```bash
cd backend
composer install
cp .env.example .env && php artisan key:generate   # renseigner DB_* (PostgreSQL), ADMIN_EMAIL, ADMIN_PASSWORD
php artisan migrate                                # inclut la migration des rôles (backend-ajouts/)
php artisan db:seed --class=AdminUserSeeder        # compte administrateur
php artisan db:seed --class=DemoSeeder             # données de démonstration (fichier fourni dans backend-ajouts/)
php artisan serve
```

## Structure
| Dossier | Rôle |
|---|---|
| `pages/` | Une page par écran : tableau de bord, stagiaires (+ fiche), encadreurs, services, affectations, évaluations, rapports, paramètres, connexion |
| `components/` | Composants réutilisables : modale, confirmation, champ de formulaire, badge de statut, carte KPI, états vide/chargement/erreur |
| `composables/` | `useApi` (client HTTP + gestion des erreurs 401/422), `useAuth`, `useCrud` (CRUD générique), `useToast` |
| `middleware/auth.global.js` | Redirige vers `/login` si non connecté |
| `utils/format.js` | Formatage des dates, noms, notes, statuts |

## Gestion des erreurs de validation
Laravel renvoie un code **422** avec `{ errors: { champ: ["message"] } }`. `parseApiError` (composable `useApi`) extrait ce tableau
et chaque `FormField` affiche le message sous le champ concerné. La validation côté interface (champs obligatoires, notes 0–20,
date de fin ≥ date de début) est un confort : la validation Laravel reste la référence.

## Ressources externes
Aucune bibliothèque d'interface : seuls Nuxt/Vue sont utilisés. Icônes SVG intégrées.

## Rôles et accès
| Rôle | Accès |
|---|---|
| **admin** | Toute l'application (CRUD, tableau de bord, rapports) |
| **encadreur** | « Mes stagiaires » : sa fiche + les stagiaires qui lui sont attribués (lecture seule) |
| **stagiaire** | « Mon espace » : sa fiche, affectations et évaluations (lecture seule) |

Les droits sont appliqués **côté Laravel** (middleware `role:`) : un stagiaire qui appelle `/api/stagiaires` reçoit un 403, même sans passer par l'interface.
Pour créer un accès, l'administrateur renseigne le champ « Mot de passe du compte de connexion » dans le formulaire du stagiaire ou de l'encadreur
(l'email de la fiche sert d'identifiant). Comptes de démonstration (après `DemoSeeder`, mot de passe `Demo@12345`) :
`fatima.sylla@demo.test`, `africoa.amadou@demo.test` (stagiaires), `koffi.mensah@demo.test`, `amina.diop@demo.test` (encadreurs).
