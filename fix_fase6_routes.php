<?php
/**
 * fix_fase6_routes.php
 * 1) Añade rutas de fotos (usuario + admin)
 * 2) Añade rutas de perfil público
 * 3) Crea directorios de storage para fotos
 * 4) Añade botón "Mis Fotos" al dashboard
 */

// ══════════════════════════════════════════════════════
// 1. RUTAS en web.php
// ══════════════════════════════════════════════════════
$routesFile = __DIR__ . '/routes/web.php';
$routes = file_get_contents($routesFile);

if (strpos($routes, 'photos.index') !== false) {
    echo "ℹ️  Rutas de fotos ya existen\n";
} else {
    $newRoutes = <<<'ROUTES'

// ── FOTOS (usuario autenticado) ───────────────────────
Route::middleware(['auth', 'force.password.change', 'profile.completed', 'check.membership'])
    ->group(function () {

    Route::get('/mis-fotos',
        [\App\Http\Controllers\Photo\PhotoController::class, 'index'])
        ->name('photos.index');

    Route::post('/mis-fotos',
        [\App\Http\Controllers\Photo\PhotoController::class, 'store'])
        ->name('photos.store');

    Route::post('/mis-fotos/{id}/perfil',
        [\App\Http\Controllers\Photo\PhotoController::class, 'setProfilePhoto'])
        ->name('photos.profile');

    Route::delete('/mis-fotos/{id}',
        [\App\Http\Controllers\Photo\PhotoController::class, 'destroy'])
        ->name('photos.destroy');

    // Ver foto (con control de acceso por membresía)
    Route::get('/fotos/{id}',
        [\App\Http\Controllers\Photo\PhotoController::class, 'serve'])
        ->name('photos.serve');

    // Perfil público de un usuario
    Route::get('/perfil/{nickname}',
        [\App\Http\Controllers\Profile\ProfileController::class, 'publicShow'])
        ->name('profile.show');
});

// ── ADMIN: FOTOS ──────────────────────────────────────
Route::middleware(['auth', 'admin.only'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/fotos',
        [\App\Http\Controllers\Admin\AdminPhotoController::class, 'index'])
        ->name('photos.index');

    Route::post('/fotos/{id}/aprobar',
        [\App\Http\Controllers\Admin\AdminPhotoController::class, 'approve'])
        ->name('photos.approve');

    Route::post('/fotos/{id}/rechazar',
        [\App\Http\Controllers\Admin\AdminPhotoController::class, 'reject'])
        ->name('photos.reject');

    Route::get('/fotos/imagen/{id}',
        [\App\Http\Controllers\Admin\AdminPhotoController::class, 'serve'])
        ->name('photos.serve');
});

ROUTES;

    $routes = rtrim($routes) . "\n" . $newRoutes;
    file_put_contents($routesFile, $routes);
    echo "✅ Rutas de fotos y perfil público añadidas\n";
}

// ══════════════════════════════════════════════════════
// 2. AÑADIR publicShow() al ProfileController
// ══════════════════════════════════════════════════════
$profileCtrl = __DIR__ . '/app/Http/Controllers/Profile/ProfileController.php';
$ctrl = file_get_contents($profileCtrl);

if (strpos($ctrl, 'publicShow') === false) {
    $publicShowMethod = <<<'PHP'

    public function publicShow($nickname)
    {
        $profile = \Illuminate\Support\Facades\DB::table('profiles')
            ->where('nickname', $nickname)
            ->where('profile_completed', true)
            ->first();

        if (!$profile) abort(404, 'Perfil no encontrado.');

        $user = \Illuminate\Support\Facades\DB::table('users')
            ->whereRaw('id::text = ?', [$profile->user_id])
            ->first();

        if (!$user || in_array($user->membership_type, ['banned', 'suspended'])) {
            abort(404);
        }

        return view('profile.show', compact('profile', 'user'));
    }
PHP;

    $ctrl = preg_replace('/}\s*$/', $publicShowMethod . "\n}", $ctrl);
    file_put_contents($profileCtrl, $ctrl);
    echo "✅ Método publicShow() añadido al ProfileController\n";
} else {
    echo "ℹ️  publicShow() ya existe\n";
}

// ══════════════════════════════════════════════════════
// 3. CREAR DIRECTORIOS DE STORAGE
// ══════════════════════════════════════════════════════
$dirs = [
    __DIR__ . '/storage/app/private/photos',
];
foreach ($dirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
        echo "✅ Directorio creado: " . str_replace(__DIR__ . '/', '', $dir) . "\n";
    } else {
        echo "ℹ️  Ya existe: " . str_replace(__DIR__ . '/', '', $dir) . "\n";
    }
}

// .gitignore para no subir fotos al repo
$gitignore = __DIR__ . '/storage/app/private/photos/.gitignore';
if (!file_exists($gitignore)) {
    file_put_contents($gitignore, "*\n!.gitignore\n");
    echo "✅ .gitignore creado en photos/\n";
}

// ══════════════════════════════════════════════════════
// 4. ACTUALIZAR DASHBOARD con botón Mis Fotos
// ══════════════════════════════════════════════════════
$dashBlade = __DIR__ . '/resources/views/dashboard/index.blade.php';
$dash = file_get_contents($dashBlade);

if (strpos($dash, 'photos.index') === false) {
    // Añadir botón después del botón Editar Perfil
    $dash = str_replace(
        "@if(\$profile && \$profile->profile_completed)
                    {{-- Perfil completo: botón editar --}}
                    <a href=\"{{ route('profile.edit') }}\"
                       class=\"btn btn--primary\"
                       style=\"text-align:center;font-size:.9rem;\">
                        <i class=\"fas fa-user-edit\"></i> Editar Perfil
                    </a>",
        "@if(\$profile && \$profile->profile_completed)
                    {{-- Perfil completo: botón editar --}}
                    <a href=\"{{ route('profile.edit') }}\"
                       class=\"btn btn--primary\"
                       style=\"text-align:center;font-size:.9rem;\">
                        <i class=\"fas fa-user-edit\"></i> Editar Perfil
                    </a>
                    <a href=\"{{ route('photos.index') }}\"
                       class=\"btn btn--ghost\"
                       style=\"text-align:center;font-size:.9rem;\">
                        <i class=\"fas fa-images\"></i> Mis Fotos
                    </a>",
        $dash
    );
    file_put_contents($dashBlade, $dash);
    echo "✅ Botón 'Mis Fotos' añadido al dashboard\n";
} else {
    echo "ℹ️  Botón 'Mis Fotos' ya existe en dashboard\n";
}

// ══════════════════════════════════════════════════════
// 5. ACTUALIZAR setup.blade.php con campos físicos
// ══════════════════════════════════════════════════════
$setupBlade = __DIR__ . '/resources/views/profile/setup.blade.php';
$setup = file_get_contents($setupBlade);

if (strpos($setup, 'height') === false) {
    // Insertar sección de datos físicos antes de SECCION UBICACION
    $physicalSection = <<<'BLADE'

    {{-- SECCION DATOS FÍSICOS --}}
    <div style="background:white;border-radius:16px;box-shadow:0 4px 16px rgba(0,0,0,.08);padding:2rem;margin-bottom:1.5rem;">
      <h2 style="font-size:1.1rem;font-weight:700;margin-bottom:1.5rem;padding-bottom:.75rem;border-bottom:2px solid #f1f5f9;">
        📏 Datos Físicos <span style="font-size:.8rem;color:#9ca3af;font-weight:400;">(opcionales)</span>
      </h2>
      <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:1.25rem;">

        <div>
          <label style="display:block;font-weight:600;font-size:.9rem;color:#374151;margin-bottom:.4rem;">Altura (cm)</label>
          <input type="number" name="height" min="140" max="220" value="{{ old('height',$profile?->height??'') }}"
                 placeholder="170" style="width:100%;padding:.7rem 1rem;border:2px solid #e5e7eb;border-radius:10px;font-size:.95rem;box-sizing:border-box;">
        </div>

        <div>
          <label style="display:block;font-weight:600;font-size:.9rem;color:#374151;margin-bottom:.4rem;">Peso (kg)</label>
          <input type="number" name="weight" min="40" max="200" value="{{ old('weight',$profile?->weight??'') }}"
                 placeholder="70" style="width:100%;padding:.7rem 1rem;border:2px solid #e5e7eb;border-radius:10px;font-size:.95rem;box-sizing:border-box;">
        </div>

        <div>
          <label style="display:block;font-weight:600;font-size:.9rem;color:#374151;margin-bottom:.4rem;">Etnia</label>
          <select name="ethnicity" style="width:100%;padding:.7rem 1rem;border:2px solid #e5e7eb;border-radius:10px;font-size:.95rem;box-sizing:border-box;">
            <option value="">Seleccionar</option>
            @foreach(['Caucásica','Hispánica/Latina','Afrolatina','Asiática','Árabe','Indígena','Mixta','Otra'] as $e)
            <option value="{{ $e }}" {{ old('ethnicity',$profile?->ethnicity??'')===$e?'selected':'' }}>{{ $e }}</option>
            @endforeach
          </select>
        </div>

        <div>
          <label style="display:block;font-weight:600;font-size:.9rem;color:#374151;margin-bottom:.4rem;">Tatuajes</label>
          <select name="tattoos" style="width:100%;padding:.7rem 1rem;border:2px solid #e5e7eb;border-radius:10px;font-size:.95rem;box-sizing:border-box;">
            @foreach(['ninguno'=>'Ninguno','pocos'=>'Pocos','muchos'=>'Muchos'] as $v=>$l)
            <option value="{{ $v }}" {{ old('tattoos',$profile?->tattoos??'ninguno')===$v?'selected':'' }}>{{ $l }}</option>
            @endforeach
          </select>
        </div>

        <div>
          <label style="display:block;font-weight:600;font-size:.9rem;color:#374151;margin-bottom:.4rem;">Piercings</label>
          <select name="piercings" style="width:100%;padding:.7rem 1rem;border:2px solid #e5e7eb;border-radius:10px;font-size:.95rem;box-sizing:border-box;">
            @foreach(['ninguno'=>'Ninguno','pocos'=>'Pocos','muchos'=>'Muchos'] as $v=>$l)
            <option value="{{ $v }}" {{ old('piercings',$profile?->piercings??'ninguno')===$v?'selected':'' }}>{{ $l }}</option>
            @endforeach
          </select>
        </div>

        <div>
          <label style="display:block;font-weight:600;font-size:.9rem;color:#374151;margin-bottom:.4rem;">Nacionalidad</label>
          <input type="text" name="nationality" value="{{ old('nationality',$profile?->nationality??'México') }}"
                 placeholder="México" style="width:100%;padding:.7rem 1rem;border:2px solid #e5e7eb;border-radius:10px;font-size:.95rem;box-sizing:border-box;">
        </div>

        <div>
          <label style="display:block;font-weight:600;font-size:.9rem;color:#374151;margin-bottom:.4rem;">Fuma</label>
          <select name="smokes" style="width:100%;padding:.7rem 1rem;border:2px solid #e5e7eb;border-radius:10px;font-size:.95rem;box-sizing:border-box;">
            @foreach(['nunca'=>'Nunca','casi_nunca'=>'Casi nunca','socialmente'=>'Socialmente','si'=>'Sí'] as $v=>$l)
            <option value="{{ $v }}" {{ old('smokes',$profile?->smokes??'nunca')===$v?'selected':'' }}>{{ $l }}</option>
            @endforeach
          </select>
        </div>

        <div>
          <label style="display:block;font-weight:600;font-size:.9rem;color:#374151;margin-bottom:.4rem;">Bebe alcohol</label>
          <select name="drinks" style="width:100%;padding:.7rem 1rem;border:2px solid #e5e7eb;border-radius:10px;font-size:.95rem;box-sizing:border-box;">
            @foreach(['nunca'=>'Nunca','casi_nunca'=>'Casi nunca','socialmente'=>'Socialmente','si'=>'Sí'] as $v=>$l)
            <option value="{{ $v }}" {{ old('drinks',$profile?->drinks??'socialmente')===$v?'selected':'' }}>{{ $l }}</option>
            @endforeach
          </select>
        </div>

        <div>
          <label style="display:block;font-weight:600;font-size:.9rem;color:#374151;margin-bottom:.4rem;">Idiomas</label>
          <div style="display:flex;flex-direction:column;gap:.4rem;">
            @php $langs = json_decode($profile?->languages ?? '[]', true) ?? []; @endphp
            @foreach(['Español','Inglés','Francés','Portugués','Alemán','Otro'] as $lang)
            <label style="display:flex;align-items:center;gap:.5rem;font-size:.85rem;cursor:pointer;">
              <input type="checkbox" name="languages[]" value="{{ $lang }}"
                     {{ in_array($lang, old('languages',$langs))?'checked':'' }}
                     style="accent-color:#8b5cf6;">
              {{ $lang }}
            </label>
            @endforeach
          </div>
        </div>

      </div>

      {{-- Campos específicos por género --}}
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;margin-top:1.25rem;padding-top:1.25rem;border-top:1px solid #f1f5f9;">
        <div id="fieldPenisSize" style="{{ old('gender',$profile?->gender??'')!=='masculino'?'display:none':'' }}">
          <label style="display:block;font-weight:600;font-size:.9rem;color:#374151;margin-bottom:.4rem;">Longitud del pene (cm) <span style="color:#9ca3af;font-weight:400;font-size:.8rem;">opcional</span></label>
          <input type="number" name="penis_size" min="5" max="35" value="{{ old('penis_size',$profile?->penis_size??'') }}"
                 placeholder="15" style="width:100%;padding:.7rem 1rem;border:2px solid #e5e7eb;border-radius:10px;font-size:.95rem;box-sizing:border-box;">
        </div>
        <div id="fieldBreastSize" style="{{ old('gender',$profile?->gender??'')!=='femenino'?'display:none':'' }}">
          <label style="display:block;font-weight:600;font-size:.9rem;color:#374151;margin-bottom:.4rem;">Talla de pecho <span style="color:#9ca3af;font-weight:400;font-size:.8rem;">opcional</span></label>
          <input type="text" name="breast_size" value="{{ old('breast_size',$profile?->breast_size??'') }}"
                 placeholder="Ej: 36B" maxlength="10" style="width:100%;padding:.7rem 1rem;border:2px solid #e5e7eb;border-radius:10px;font-size:.95rem;box-sizing:border-box;">
        </div>
      </div>

      {{-- Visibilidad de nombre --}}
      <div style="margin-top:1.25rem;padding-top:1.25rem;border-top:1px solid #f1f5f9;">
        <label style="display:flex;align-items:center;gap:.75rem;cursor:pointer;">
          <input type="checkbox" name="show_name" value="1"
                 {{ old('show_name', $profile?->show_name ?? true) ? 'checked' : '' }}
                 style="width:18px;height:18px;accent-color:#8b5cf6;">
          <div>
            <span style="font-weight:600;font-size:.9rem;color:#374151;">Mostrar mi nombre real en el perfil</span>
            <p style="font-size:.8rem;color:#9ca3af;margin:.2rem 0 0;">Si lo desactivas, aparecerás como "-Nombre oculto-" en el directorio</p>
          </div>
        </label>
      </div>
    </div>

BLADE;

    $setup = str_replace(
        '{{-- SECCION UBICACION --}}',
        $physicalSection . '    {{-- SECCION UBICACION --}}',
        $setup
    );
    file_put_contents($setupBlade, $setup);
    echo "✅ Sección de datos físicos añadida a setup.blade.php\n";
} else {
    echo "ℹ️  Datos físicos ya existen en setup.blade.php\n";
}

// ══════════════════════════════════════════════════════
// 6. ACTUALIZAR ProfileController store() para
//    guardar los nuevos campos físicos
// ══════════════════════════════════════════════════════
$profileCtrl = __DIR__ . '/app/Http/Controllers/Profile/ProfileController.php';
$ctrl = file_get_contents($profileCtrl);

if (strpos($ctrl, 'height') === false) {
    // Añadir campos físicos al array de update en store()
    $ctrl = str_replace(
        "'profile_completed'     => true,",
        "'profile_completed'     => true,
                'height'              => $request->input('height'),
                'weight'              => $request->input('weight'),
                'ethnicity'           => $request->input('ethnicity'),
                'nationality'         => $request->input('nationality', 'México'),
                'penis_size'          => $request->input('penis_size'),
                'breast_size'         => $request->input('breast_size'),
                'tattoos'             => $request->input('tattoos', 'ninguno'),
                'piercings'           => $request->input('piercings', 'ninguno'),
                'smokes'              => $request->input('smokes', 'nunca'),
                'drinks'              => $request->input('drinks', 'socialmente'),
                'languages'           => json_encode($request->input('languages', [])),
                'show_name'           => $request->has('show_name') ? true : false,",
        $ctrl
    );
    file_put_contents($profileCtrl, $ctrl);
    echo "✅ ProfileController actualizado con campos físicos\n";
} else {
    echo "ℹ️  Campos físicos ya existen en ProfileController\n";
}

echo "\n✅ fix_fase6_routes.php completado\n";
echo "══════════════════════════════════════\n";
echo "Ejecuta:\n";
echo "  C:\\php\\php.exe artisan view:clear\n";
echo "  C:\\php\\php.exe artisan route:clear\n";
echo "  C:\\php\\php.exe artisan serve\n";
echo "\nPrueba:\n";
echo "  http://localhost:8000/mis-fotos\n";
echo "  http://localhost:8000/admin/fotos\n";
echo "  http://localhost:8000/perfil/Single_uno\n";
