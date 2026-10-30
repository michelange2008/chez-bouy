# Menu d'administration Chez Bouy

Décompresser `chez-bouy-menu-admin.zip` à la racine du projet Laravel. L'archive contient les vues admin, les traductions, le CSS dédié et un test. Elle remplace `resources/views/admin.blade.php` ; elle ne contient ni le layout global ni web.php ni app.css. Si vous avez modifié la page admin, conserver vos modifications avant de la remplacer.

## Routes

Dans `routes/web.php`, remplacer l'ancienne déclaration de `/admin` par ce groupe. Conserver les imports existants, notamment `App\Http\Middleware\RequireRole` et `Illuminate\Support\Facades\Route`.

```php
Route::middleware(['auth', RequireRole::class.':admin'])
    ->prefix('admin')->group(function () {
        Route::view('/', 'admin')->name('admin');
        Route::view('/utilisateurs', 'admin.users')->name('admin.users');
        Route::view('/photos', 'admin.photos')->name('admin.photos');
        Route::view('/fichiers', 'admin.files')->name('admin.files');
    });
```

Le nom `admin` reste inchangé pour les liens et la redirection de connexion existants. Les routes sont toutes protégées par le middleware de rôle, qui vérifie également `is_active` depuis notre précédente modification.

## CSS

Ajouter cet import parmi les imports de `resources/css/app.css`, avant les éventuelles règles CSS :

```css
@import './admin.css';
```

Conserver les autres imports. Garder `npm run dev` actif ou exécuter `npm run build`.

## Vues

- `resources/views/admin.blade.php` : accueil admin, avec menu.
- `resources/views/admin/layout.blade.php` : structure commune, étend le layout global.
- `resources/views/admin/partials/menu.blade.php` : les trois liens.
- `resources/views/admin/users.blade.php` : Utilisateurs.
- `resources/views/admin/photos.blade.php` : Photos.
- `resources/views/admin/files.blade.php` : Fichiers.

Chaque rubrique contient uniquement un titre et le menu pour le moment. Le contenu futur s'ajoute à la section `admin-content`. L'item actif est indiqué par `aria-current="page"` et mis en évidence par le CSS. Les textes utilisent `lang/fr/admin.php`, `lang/en/admin.php` et `lang/it/admin.php`, sans modifier vos traductions existantes dans `site.php`.

## Vérifier sur sapajou

```bash
php artisan route:clear
php artisan view:clear
php artisan route:list --path=admin
php artisan test --filter=AdminPagesTest
```

Puis ouvrir `/admin` avec un compte admin actif et cliquer sur les trois rubriques. Aucune migration n'est nécessaire. Les tests contrôlent les accès visiteurs, locataires et admins inactifs, les vues, les liens et la rubrique active. La syntaxe PHP est vérifiée dans les fichiers livrés ; les tests Laravel et le rendu visuel restent à exécuter dans votre projet complet.
