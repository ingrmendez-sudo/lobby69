

"""
Script para generar datos de prueba (15 perfiles + fotos reales)
Ejecutar: python seed_data.py
"""
import os
import django
from datetime import datetime, timedelta
import uuid
from pathlib import Path

os.environ.setdefault('DJANGO_SETTINGS_MODULE', 'config.settings.local')
django.setup()

# User se cargará dinámicamente en create_test_user
from supabase import create_client
from django.conf import settings

# Conectar a Supabase
supabase = create_client(settings.SUPABASE_URL, settings.SUPABASE_KEY)

# Password para todos los perfiles de prueba
TEST_PASSWORD = 'Prueba123'

# Ruta de imágenes locales
SEED_IMAGES_DIR = Path(__file__).parent / 'seed_images'

# Datos de perfiles a crear
PROFILES_DATA = [
    {
        'nick': 'pareja_sexy_cdmx',
        'display_name': 'Pareja Sexy CDMX',
        'age': 32,
        'gender': 'Pareja',
        'city': 'Ciudad de México',
        'state': 'CDMX',
        'bio': 'Pareja joven buscando compartir experiencias. Discreción garantizada.',
        'profile_type': 'pareja',
    },
    {
        'nick': 'carolina_gdl',
        'display_name': 'Carolina',
        'age': 28,
        'gender': 'Mujer',
        'city': 'Guadalajara',
        'state': 'Jalisco',
        'bio': 'Mujer bisexual explorando nuevas experiencias.',
        'profile_type': 'individual',
    },
    {
        'nick': 'juan_monterrey',
        'display_name': 'Juan',
        'age': 35,
        'gender': 'Hombre',
        'city': 'Monterrey',
        'state': 'Nuevo León',
        'bio': 'Hombre soltero, abierto y discreto.',
        'profile_type': 'individual',
    },
    {
        'nick': 'pareja_abierta_cancun',
        'display_name': 'Pareja Cancún',
        'age': 30,
        'gender': 'Pareja',
        'city': 'Cancún',
        'state': 'Quintana Roo',
        'bio': 'Pareja en relación abierta. Buscamos conexiones genuinas.',
        'profile_type': 'pareja',
    },
    {
        'nick': 'lucia_veracruz',
        'display_name': 'Lucía',
        'age': 26,
        'gender': 'Mujer',
        'city': 'Veracruz',
        'state': 'Veracruz',
        'bio': 'Mujer aventurera y sin prejuicios.',
        'profile_type': 'individual',
    },
    {
        'nick': 'pareja_bi_guadalajara',
        'display_name': 'Pareja Bisexual',
        'age': 31,
        'gender': 'Pareja',
        'city': 'Guadalajara',
        'state': 'Jalisco',
        'bio': 'Pareja bisexual buscando parejas o individuos.',
        'profile_type': 'pareja',
    },
    {
        'nick': 'sofia_mexico',
        'display_name': 'Sofía',
        'age': 29,
        'gender': 'Mujer',
        'city': 'Estado de México',
        'state': 'Estado de México',
        'bio': 'Soy curiosa y me encanta explorar.',
        'profile_type': 'individual',
    },
    {
        'nick': 'carlos_leon',
        'display_name': 'Carlos',
        'age': 38,
        'gender': 'Hombre',
        'city': 'León',
        'state': 'Guanajuato',
        'bio': 'Profesional discreto buscando aventuras.',
        'profile_type': 'individual',
    },
    {
        'nick': 'pareja_hot_puebla',
        'display_name': 'Pareja Hot Puebla',
        'age': 33,
        'gender': 'Pareja',
        'city': 'Puebla',
        'state': 'Puebla',
        'bio': 'Pareja sin tabúes lista para nuevas experiencias.',
        'profile_type': 'pareja',
    },
    {
        'nick': 'valentina_tampico',
        'display_name': 'Valentina',
        'age': 27,
        'gender': 'Mujer',
        'city': 'Tampico',
        'state': 'Tamaulipas',
        'bio': 'Mujer sensual y extrovertida.',
        'profile_type': 'individual',
    },
    {
        'nick': 'pareja_cuckold_leon',
        'display_name': 'Pareja Cuckold',
        'age': 36,
        'gender': 'Pareja',
        'city': 'León',
        'state': 'Guanajuato',
        'bio': 'Pareja interesada en dinámicas cuckold.',
        'profile_type': 'pareja',
    },
    {
        'nick': 'isabella_acapulco',
        'display_name': 'Isabella',
        'age': 24,
        'gender': 'Mujer',
        'city': 'Acapulco',
        'state': 'Guerrero',
        'bio': 'Joven entusiasta de nuevas amistades.',
        'profile_type': 'individual',
    },
    {
        'nick': 'pareja_trios_merida',
        'display_name': 'Pareja Abierta Mérida',
        'age': 34,
        'gender': 'Pareja',
        'city': 'Mérida',
        'state': 'Yucatán',
        'bio': 'Abiertos a tríos y nuevas experiencias.',
        'profile_type': 'pareja',
    },
    {
        'nick': 'diego_toluca',
        'display_name': 'Diego',
        'age': 40,
        'gender': 'Hombre',
        'city': 'Toluca',
        'state': 'Estado de México',
        'bio': 'Hombre maduro y con experiencia.',
        'profile_type': 'individual',
    },
    {
        'nick': 'pareja_discreta_saltillo',
        'display_name': 'Pareja Discreta',
        'age': 37,
        'gender': 'Pareja',
        'city': 'Saltillo',
        'state': 'Coahuila',
        'bio': 'Pareja que valora discreción y respeto.',
        'profile_type': 'pareja',
    },
]

def get_local_images():
    """Obtener lista de imágenes locales"""
    if not SEED_IMAGES_DIR.exists():
        print(f"✗ Carpeta de imágenes no encontrada: {SEED_IMAGES_DIR}")
        return []

    image_files = list(SEED_IMAGES_DIR.glob('*.jpg')) + list(SEED_IMAGES_DIR.glob('*.JPG'))
    print(f"✓ {len(image_files)} imágenes encontradas en {SEED_IMAGES_DIR}")
    return sorted(image_files)

def upload_image_to_supabase(image_path, bucket_name='gallery'):
    """Subir imagen a Supabase Storage"""
    try:
        with open(image_path, 'rb') as f:
            file_name = f"{uuid.uuid4()}_{image_path.name}"
            response = supabase.storage.from_(bucket_name).upload(file_name, f.read())

        # Obtener URL pública
        url = supabase.storage.from_(bucket_name).get_public_url(file_name)
        print(f"  ✓ Imagen subida: {image_path.name}")
        return url
    except Exception as e:
        print(f"  ✗ Error subiendo imagen: {e}")
        return None

def create_test_user(username, email):
    """Crear usuario en Django"""
    try:
        from django.apps import apps
        User = apps.get_model('users', 'User')

        user = User.objects.create_user(
            username=username,
            email=email,
            password=TEST_PASSWORD
        )
        print(f"✓ Usuario Django creado: {username} (ID: {user.id})")
        return str(user.id)
    except Exception as e:
        print(f"✗ Error creando usuario Django: {e}")
        import traceback
        traceback.print_exc()
        return None


def create_profile_in_supabase(account_id, profile_data):
    """Crear perfil en Supabase"""
    try:
        profile = {
            'account_id': account_id,
            'nick': profile_data['nick'],
            'display_name': profile_data['display_name'],
            'age': profile_data['age'],
            'gender': profile_data['gender'],
            'city': profile_data['city'],
            'state': profile_data['state'],
            'bio': profile_data['bio'],
            'profile_type': profile_data['profile_type'],
            'is_complete': True,
            'is_active': True,
            'created_at': datetime.now().isoformat(),
            'updated_at': datetime.now().isoformat(),
        }

        resp = supabase.table('profiles').insert(profile).execute()
        profile_id = resp.data[0]['id'] if resp.data else None
        print(f"✓ Perfil Supabase creado: {profile_data['nick']}")
        return profile_id
    except Exception as e:
        print(f"✗ Error creando perfil Supabase: {e}")
        return None

def create_photos_for_profile(account_id, user_nick, image_files, start_index):
    """Crear fotos para un perfil usando imágenes reales"""
    try:
        photos_count = 0
        for i in range(4):  # 4 fotos por perfil
            image_index = (start_index + i) % len(image_files)
            image_path = image_files[image_index]

            print(f"    Subiendo foto {i+1}/4: {image_path.name}...", end=" ")

            # Subir imagen a Supabase
            image_url = upload_image_to_supabase(image_path)
            if not image_url:
                print("✗")
                continue

            print("✓")
            # Convertir account_id a UUID válido si es necesario
            try:
                account_id_uuid = str(uuid.UUID(account_id))
            except:
                account_id_uuid = account_id

            # Crear registro en galería
            photo = {
                'account_id': account_id,  # Debe ser string (text)
                'image_url': image_url,
                'caption': f'Foto {i+1} de {user_nick}',
                'visibility': 'public' if i < 3 else 'private',
                'status': 'approved',
                'user_nick': user_nick,
                'likes_count': 0,
                'comments_count': 0,
                'uploaded_at': (datetime.now() - timedelta(days=i)).isoformat(),
            }

            try:
                resp = supabase.table('gallery').insert(photo).execute()
                if resp.data:
                    photos_count += 1
                    print(f"    ✓ Foto {i+1} guardada en BD")
            except Exception as photo_err:
                print(f"    ✗ Error guardando foto en BD: {photo_err}")

        print(f"  ✓ Total: {photos_count} fotos creadas para {user_nick}\n")
        return photos_count
    except Exception as e:
        print(f"✗ Error creando fotos: {e}")
        import traceback
        traceback.print_exc()
        return 0

def main():
    """Función principal"""
    print("=" * 70)
    print("SCRIPT DE SEEDING - LOBBY69")
    print("=" * 70)

    # Obtener imágenes locales
    image_files = get_local_images()
    if not image_files:
        print("\n✗ No hay imágenes disponibles. Abortando...")
        return

    print(f"\n[1/3] Creando {len(PROFILES_DATA)} perfiles con fotos reales...\n")

    image_index = 0
    total_photos = 0

    for idx, profile_data in enumerate(PROFILES_DATA):
        username = profile_data['nick']
        email = f"{username}@lobby69.test"

        print(f"\n--- Perfil {idx+1}/{len(PROFILES_DATA)}: {username} ---")

        # Crear usuario Django
        account_id = create_test_user(username, email)
        if not account_id:
            continue

        # Crear perfil en Supabase
        profile_id = create_profile_in_supabase(account_id, profile_data)
        if not profile_id:
            continue

        # Crear fotos
        print(f"\n[2/3] Subiendo fotos para {username}...")
        photos_count = create_photos_for_profile(account_id, profile_data['nick'], image_files, image_index)
        total_photos += photos_count
        image_index += 4

    print("\n" + "=" * 70)
    print("✓ SEEDING COMPLETADO EXITOSAMENTE")
    print("=" * 70)
    print(f"\nDatos creados:")
    print(f"  - 15 perfiles")
    print(f"  - {total_photos} fotos (desde imágenes reales)")
    print(f"  - Password para todos: {TEST_PASSWORD}")
    print(f"\nCuenta Admin:")
    print(f"  - Usuario: Lobby69")
    print(f"  - Password: SW1NG3R26")
    print("=" * 70)

if __name__ == '__main__':
    main()
