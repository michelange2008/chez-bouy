# Gestion des utilisateurs — Chez Bouy

Cette archive est à intégrer dans le projet complet sur sapajou. Elle n'est pas une nouvelle installation Laravel. Elle contient un contrôleur, les vues du tableau et d'édition, un menu admin ajusté, un module JavaScript, une feuille CSS, les traductions FR/EN/IT et les tests. Elle ne remplace pas votre layout global, votre modèle User, vos routes ou vos points d'entrée Vite.

## 1. Copier les fichiers

Depuis la racine `/srv/chez-bouy-base/chez-bouy`, après y avoir placé l'archive :

```bash
unzip -o chez-bouy-utilisateurs.zip
```

L'extraction remplace `resources/views/admin/users.blade.php` et `resources/views/admin/partials/menu.blade.php`, et ajoute les autres fichiers. Si vous aviez personnalisé ces deux vues, conserver vos modifications avant extraction.

Les migrations précédentes doivent déjà avoir été appliquées : `name`, `first_name`, `email`, `password`, `role`, `is_active`, `login_count` et `last_login_at` existent. Aucune nouvelle migration n'est nécessaire.

Le modèle User doit conserver les casts `password => hashed`, `is_active => boolean`, `login_count => integer` ainsi que sa valeur par défaut `is_active => true`. Le contrôleur affecte les champs explicitement : inutile d'ajouter `role` ou `is_active` à Fillable.

## 2. Routes

Dans `routes/web.php`, ajouter l'import :

```php
use App\Http\Controllers\Admin\UserController;
```

À l'intérieur du groupe existant `prefix('admin')`, protégé par `['auth', RequireRole::class.':admin']`, remplacer uniquement l'ancienne route Utilisateurs par :

```php
Route::get('/utilisateurs', [UserController::class, 'index'])
    ->name('admin.users');
Route::post('/utilisateurs', [UserController::class, 'save'])
    ->name('admin.users.save');
Route::get('/utilisateurs/{user}/modifier', [UserController::class, 'edit'])
    ->name('admin.users.edit');
Route::patch('/utilisateurs/{user}', [UserController::class, 'update'])
    ->name('admin.users.update');
Route::delete('/utilisateurs/{user}', [UserController::class, 'destroy'])
    ->name('admin.users.destroy');
```

Ne pas conserver l'ancienne `Route::view('/utilisateurs', ...)`. Conserver les routes de l'accueil admin, des photos et des fichiers. Le middleware RequireRole doit toujours refuser les comptes inactifs.

## 3. CSS et JavaScript avec Vite

Ajouter cet import aux autres imports en haut de `resources/css/app.css` :

```css
@import './admin-users.css';
```

Ajouter cette ligne dans `resources/js/app.js`, sans supprimer le code existant :

```js
import './admin-users.js';
```

Dans `resources/views/layout.blade.php`, remplacer l'appel qui ne charge que le CSS par :

```blade
@vite(['resources/css/app.css', 'resources/js/app.js'])
```

Ne pas ajouter un deuxième appel si les deux fichiers sont déjà chargés. Dans `vite.config.js`, vérifier que les deux points d'entrée figurent dans l'option `input` du plugin Laravel :

```js
input: ['resources/css/app.css', 'resources/js/app.js'],
```

Puis garder `npm run dev` actif, ou lancer :

```bash
npm run build
```

Le JavaScript est nécessaire pour les boîtes de dialogue. Si les boutons Ajouter et Supprimer restent grisés, vérifier le chargement de `app.js` et les éventuelles erreurs dans la console du navigateur. Les boutons sont activés seulement une fois le module chargé.

## 4. Fonctionnement

- Liste de tous les utilisateurs, paginée par 20, triée par nom puis prénom.
- Bouton Ajouter : boîte de dialogue native accessible, nom obligatoire, prénom facultatif, e-mail obligatoire et unique, rôle et statut activé. Valeurs initiales : locataire et activé.
- La méthode `save` crée un utilisateur avec un mot de passe aléatoire haché par le modèle. Aucun e-mail n'est envoyé lors de l'ajout. L'utilisateur demandera son code depuis la page de connexion.
- En cas d'erreur de création, la boîte se rouvre avec les valeurs saisies et les erreurs traduites.
- Icône crayon : page d'édition. Seuls nom, prénom, rôle et statut sont modifiés. L'e-mail est affiché en lecture seule sans attribut `name`. Le serveur refuse également une tentative d'envoi d'un autre e-mail.
- Icône corbeille : boîte de confirmation affichant l'e-mail du compte. Annuler ou Échap n'envoie aucune requête de suppression. Confirmer envoie un formulaire DELETE avec CSRF.
- Suppression définitive du compte. Le code de connexion éventuel en cache est également supprimé ; même suppression du code lors d'une désactivation.
- L'administrateur connecté ne peut pas supprimer ou désactiver son propre compte, ni retirer son propre rôle admin. Les contrôles sont côté serveur. Il peut changer ses nom et prénom.
- Si vous ajoutez plus tard des réservations ou documents liés aux utilisateurs, définir alors les règles de conservation ou de suppression de ces relations.

Les nouvelles traductions sont isolées dans `lang/fr/users.php`, `lang/en/users.php`, `lang/it/users.php`. Les données personnelles saisies ne sont pas traduites.

## 5. Vérifications

```bash
php artisan route:clear
php artisan view:clear
php artisan route:list --path=admin/utilisateurs
php artisan test --filter=AdminUsersTest
php artisan test
```

Conserver les réglages SQLite en mémoire et `APP_ENV=testing` de phpunit.xml. Les tests désactivent Vite, mais ne désactivent pas les contrôles d'accès.

Les 11 tests couvrent la création, la normalisation et l'unicité de l'e-mail, la validation, les quatre champs éditables, l'e-mail immuable, les suppressions, l'interdiction des actions non autorisées, la protection du compte courant, la pagination et l'échappement HTML. Ils ne remplacent pas un test navigateur des fenêtres de dialogue.

À vérifier manuellement avec un compte admin : créer un compte temporaire ; modifier son prénom/rôle/statut ; annuler une suppression et vérifier qu'il existe encore ; confirmer la suppression ; essayer la création d'une adresse en doublon ; changer la langue. Vérifier aussi le clavier (Tab, Échap) et le retour du focus au bouton après fermeture.

Vérifications effectuées sur les fichiers fournis : syntaxe PHP, syntaxe JavaScript, cohérence des clés de traduction et intégrité de l'archive. L'application complète avec ses dépendances étant sur sapajou, les tests Laravel et le rendu navigateur restent à exécuter là-bas.
